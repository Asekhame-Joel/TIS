<?php
declare(strict_types=1);

namespace TIS\Payment;

use PDO;

/** Handles admission for paid online tickets without changing payment records. */
final class OnlineTicketCheckInService
{
    public function __construct(private readonly PDO $db)
    {
    }

    /** @return array<string,mixed>|null */
    public function find(int $orderId): ?array
    {
        $query = $this->db->prepare(
            "SELECT o.*, staff.display_name AS checked_in_by_name FROM tis_orders o " .
            "LEFT JOIN tis_staff_users staff ON staff.id = o.checked_in_by " .
            "WHERE o.id = ? AND o.payment_method IN ('online', 'bank_transfer') LIMIT 1"
        );
        $query->execute([$orderId]);
        $order = $query->fetch();

        return is_array($order) ? $order : null;
    }

    /**
     * Atomically checks in one successful, issued online ticket.
     *
     * @return array{order:array<string,mixed>|null,checked_in_now:bool}
     */
    public function checkIn(int $orderId, int $staffId): array
    {
        $update = $this->db->prepare(
            "UPDATE tis_orders SET checked_in_at = CURRENT_TIMESTAMP, checked_in_by = ?, updated_at = CURRENT_TIMESTAMP " .
            "WHERE id = ? AND payment_method IN ('online', 'bank_transfer') " .
            "AND status = 'success' AND ticket_number IS NOT NULL AND checked_in_at IS NULL"
        );
        $update->execute([$staffId, $orderId]);

        return [
            'order' => $this->find($orderId),
            'checked_in_now' => $update->rowCount() === 1,
        ];
    }
}
