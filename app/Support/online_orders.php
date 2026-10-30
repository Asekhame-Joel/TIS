<?php
declare(strict_types=1);

/** @return list<string> */
function tis_online_ticket_statuses(): array
{
    return ['success', 'pending', 'failed', 'abandoned', 'cancelled'];
}

/**
 * Builds the validated filters shared by the online-ticket dashboard and CSV export.
 *
 * @param array<string,mixed> $input
 * @param array<string,mixed> $config
 * @return array{where:list<string>,params:list<string>,q:string,status:string,tier:string,method:string,from:string,to:string}
 */
function tis_online_ticket_filters(array $input, array $config): array
{
    $search = trim((string) ($input['q'] ?? ''));
    $status = strtolower(trim((string) ($input['status'] ?? '')));
    $tier = strtolower(trim((string) ($input['tier'] ?? '')));
    $method = strtolower(trim((string) ($input['method'] ?? '')));
    $from = trim((string) ($input['from'] ?? ''));
    $to = trim((string) ($input['to'] ?? ''));

    if (!in_array($status, array_merge([''], tis_online_ticket_statuses()), true)) {
        $status = '';
    }
    if ($tier !== '' && tis_ticket_tier($tier, $config) === null) {
        $tier = '';
    }
    if (!in_array($method, ['', 'online', 'bank_transfer'], true)) {
        $method = '';
    }
    if ($from !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        $from = '';
    }
    if ($to !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        $to = '';
    }

    $where = ["o.payment_method IN ('online', 'bank_transfer')"];
    $params = [];
    if ($search !== '') {
        $where[] = '(o.full_name LIKE ? OR o.email LIKE ? OR o.phone LIKE ? OR o.reference LIKE ? OR o.ticket_number LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like, $like);
    }
    if ($status !== '') {
        $where[] = 'o.status = ?';
        $params[] = $status;
    }
    if ($tier !== '') {
        $where[] = 'o.tier = ?';
        $params[] = $tier;
    }
    if ($method !== '') {
        $where[] = 'o.payment_method = ?';
        $params[] = $method;
    }
    if ($from !== '') {
        $where[] = 'o.created_at >= ?';
        $params[] = $from . ' 00:00:00';
    }
    if ($to !== '') {
        $where[] = 'o.created_at <= ?';
        $params[] = $to . ' 23:59:59';
    }

    return compact('where', 'params', 'search', 'status', 'tier', 'method', 'from', 'to') + ['q' => $search];
}
