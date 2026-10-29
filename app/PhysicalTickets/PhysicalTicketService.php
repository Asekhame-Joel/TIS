<?php
declare(strict_types=1);

namespace TIS\PhysicalTickets;

use PDO;
use PDOException;
use RuntimeException;

final class PhysicalTicketService
{
    public function __construct(private PDO $db)
    {
    }

    public static function normalizeCode(string $code): string
    {
        return strtoupper((string) preg_replace('/\s+/', '', trim($code)));
    }

    public static function randomCode(): string
    {
        return 'TIS-' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /** @return list<string> */
    public function generate(int $quantity, string $eventKey, string $eventName, int $staffId): array
    {
        if ($quantity < 1 || $quantity > 1000) {
            throw new RuntimeException('Generate between 1 and 1,000 tickets at a time.');
        }

        $insert = $this->db->prepare(
            "INSERT INTO tis_physical_tickets (code, event_key, event_name, status, created_by) " .
            "VALUES (?, ?, ?, 'unsold', ?)"
        );
        $codes = [];
        $attempts = 0;
        $maximumAttempts = max(25, $quantity * 10);

        $this->db->beginTransaction();
        try {
            while (count($codes) < $quantity) {
                if (++$attempts > $maximumAttempts) {
                    throw new RuntimeException('Unique ticket codes could not be generated. Please try again.');
                }

                $code = self::randomCode();
                try {
                    $insert->execute([$code, $eventKey, $eventName, $staffId]);
                    $codes[] = $code;
                } catch (PDOException $error) {
                    if ((string) $error->getCode() !== '23000') {
                        throw $error;
                    }
                    // The unique database index detected a rare collision; generate another code.
                }
            }
            $this->db->commit();
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }

        return $codes;
    }

    /** @return array<string,mixed>|null */
    public function findByCode(string $code, string $eventKey): ?array
    {
        $query = $this->db->prepare(
            'SELECT t.*, u.display_name AS checked_in_by_name, o.full_name AS attendee_name, ' .
            'o.email AS attendee_email, o.tier AS attendee_tier FROM tis_physical_tickets t ' .
            'LEFT JOIN tis_staff_users u ON u.id = t.checked_in_by LEFT JOIN tis_orders o ON o.id = t.order_id ' .
            'WHERE t.code = ? AND t.event_key = ? LIMIT 1'
        );
        $query->execute([self::normalizeCode($code), $eventKey]);
        $ticket = $query->fetch();
        return is_array($ticket) ? $ticket : null;
    }

    /**
     * Atomically checks in a sold ticket. Only one concurrent request can change
     * the row from sold to checked_in.
     *
     * @return array<string,mixed>|null
     */
    public function checkIn(string $code, string $eventKey, int $staffId): ?array
    {
        $normalized = self::normalizeCode($code);
        $update = $this->db->prepare(
            "UPDATE tis_physical_tickets SET status = 'checked_in', checked_in_at = CURRENT_TIMESTAMP, " .
            "checked_in_by = ?, updated_at = CURRENT_TIMESTAMP " .
            "WHERE code = ? AND event_key = ? AND status = 'sold'"
        );
        $update->execute([$staffId, $normalized, $eventKey]);
        $changed = $update->rowCount() === 1;
        $ticket = $this->findByCode($normalized, $eventKey);
        if ($ticket !== null) {
            $ticket['_checked_in_now'] = $changed;
        }
        return $ticket;
    }

    /**
     * Atomically returns a checked-in ticket to Sold.
     *
     * @return array<string,mixed>|null
     */
    public function undoCheckIn(string $code, string $eventKey): ?array
    {
        $normalized = self::normalizeCode($code);
        $update = $this->db->prepare(
            "UPDATE tis_physical_tickets SET status = 'sold', checked_in_at = NULL, checked_in_by = NULL, " .
            "sold_at = COALESCE(sold_at, CURRENT_TIMESTAMP), updated_at = CURRENT_TIMESTAMP " .
            "WHERE code = ? AND event_key = ? AND status = 'checked_in'"
        );
        $update->execute([$normalized, $eventKey]);
        $changed = $update->rowCount() === 1;
        $ticket = $this->findByCode($normalized, $eventKey);
        if ($ticket !== null) {
            $ticket['_unchecked_now'] = $changed;
        }
        return $ticket;
    }

    /**
     * Apply one guarded status transition to selected tickets.
     *
     * @param list<int> $ids
     */
    public function transitionSelected(array $ids, string $eventKey, string $action, int $staffId): int
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));
        if ($ids === [] || count($ids) > 1000) {
            throw new RuntimeException('Select between 1 and 1,000 tickets.');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'mark_unsold') {
            return $this->reverseSelectedSales($ids, $eventKey, $placeholders);
        }

        if ($action === 'mark_sold') {
            $sql = "UPDATE tis_physical_tickets SET status = 'sold', sold_at = CURRENT_TIMESTAMP, " .
                "updated_at = CURRENT_TIMESTAMP WHERE event_key = ? AND status = 'unsold' AND id IN ({$placeholders})";
            $params = array_merge([$eventKey], $ids);
        } elseif ($action === 'check_in') {
            $sql = "UPDATE tis_physical_tickets SET status = 'checked_in', checked_in_at = CURRENT_TIMESTAMP, " .
                "checked_in_by = ?, updated_at = CURRENT_TIMESTAMP WHERE event_key = ? AND status = 'sold' " .
                "AND id IN ({$placeholders})";
            $params = array_merge([$staffId, $eventKey], $ids);
        } elseif ($action === 'undo_check_in') {
            $sql = "UPDATE tis_physical_tickets SET status = 'sold', checked_in_at = NULL, checked_in_by = NULL, " .
                "sold_at = COALESCE(sold_at, CURRENT_TIMESTAMP), updated_at = CURRENT_TIMESTAMP " .
                "WHERE event_key = ? AND status = 'checked_in' AND id IN ({$placeholders})";
            $params = array_merge([$eventKey], $ids);
        } else {
            throw new RuntimeException('Unknown ticket status action.');
        }

        $update = $this->db->prepare($sql);
        $update->execute($params);
        return $update->rowCount();
    }

    /** @param list<int> $ids */
    private function reverseSelectedSales(array $ids, string $eventKey, string $placeholders): int
    {
        $lockClause = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? '' : ' FOR UPDATE';
        $params = array_merge([$eventKey], $ids);

        $this->db->beginTransaction();
        try {
            $ordersQuery = $this->db->prepare(
                "SELECT order_id FROM tis_physical_tickets WHERE event_key = ? AND status = 'sold' " .
                "AND id IN ({$placeholders}){$lockClause}"
            );
            $ordersQuery->execute($params);
            $orderIds = array_values(array_filter(array_map(
                'intval',
                array_column($ordersQuery->fetchAll(), 'order_id')
            ), static fn (int $id): bool => $id > 0));

            $update = $this->db->prepare(
                "UPDATE tis_physical_tickets SET status = 'unsold', sold_at = NULL, sold_by = NULL, " .
                "checked_in_at = NULL, checked_in_by = NULL, order_id = NULL, manual_payment_reference = NULL, " .
                "updated_at = CURRENT_TIMESTAMP WHERE event_key = ? AND status = 'sold' AND id IN ({$placeholders})"
            );
            $update->execute($params);
            $changed = $update->rowCount();

            if ($orderIds !== []) {
                $orderPlaceholders = implode(',', array_fill(0, count($orderIds), '?'));
                $cancel = $this->db->prepare(
                    "UPDATE tis_orders SET status = 'cancelled', ticket_number = NULL, ticket_token = NULL, " .
                    "failure_reason = 'Manual sale reversed by staff', updated_at = CURRENT_TIMESTAMP " .
                    "WHERE id IN ({$orderPlaceholders}) AND payment_method = 'manual'"
                );
                $cancel->execute($orderIds);
            }

            $this->db->commit();
            return $changed;
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }
}
