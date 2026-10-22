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
            'SELECT t.*, u.display_name AS checked_in_by_name FROM tis_physical_tickets t ' .
            'LEFT JOIN tis_staff_users u ON u.id = t.checked_in_by WHERE t.code = ? AND t.event_key = ? LIMIT 1'
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
}
