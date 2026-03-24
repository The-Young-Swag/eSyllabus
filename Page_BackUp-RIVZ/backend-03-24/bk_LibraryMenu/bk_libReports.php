<?php
// Shared utilities for both tab and view‑all handlers.
// Included by bk_viewReports and bk_tabReports.php.

function sendResponse(array $payload): void {
    echo json_encode($payload);
    exit;
}

function calcDurationMinutes(string $checkinTime, ?string $checkoutTime): float {
    if (!$checkoutTime) return 0.0;
    $start = strtotime($checkinTime);
    $end   = strtotime($checkoutTime);
    return ($start && $end) ? ($end - $start) / 60 : 0.0;
}

function formatDateTime(string $datetime): string {
    return date('M j, Y g:i A', strtotime($datetime));
}

function escHtml(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

}

function getUserDisplayLabel(array $log): string {
    $idNumber = $log['id_number'] ?? '';
    // id_number is stored as '0' for walk-in guests with no system account
    return ($idNumber === '' || $idNumber === '0') ? ($log['name'] ?? 'Guest') : $idNumber;
}

function buildWhereClause(array $postData): array {
    $clauses = [];
    $params  = [];

    if (!empty($postData['startDate'])) {
        $clauses[] = 'CAST(l.checkin_time AS DATE) >= :startDate';
        $params[':startDate'] = $postData['startDate'];
    }
    if (!empty($postData['endDate'])) {
        $clauses[] = 'CAST(l.checkin_time AS DATE) <= :endDate';
        $params[':endDate'] = $postData['endDate'];
    }
    if (!empty($postData['classification']) && $postData['classification'] !== 'All') {
        $clauses[] = 'l.classification = :classification';
        $params[':classification'] = $postData['classification'];
    }
    if (!empty($postData['library']) && $postData['library'] !== 'All') {
        $clauses[] = 'l.library = :libraryId';
        $params[':library'] = $postData['library'];
        }

    return [$clauses ? ' AND ' . implode(' AND ', $clauses) : '', $params];
}

function fetchVisitLogs(string $where, array $params): array {
    return execsqlSRS("
        SELECT l.id,
               l.id_number,
               l.name,
               l.college,
               l.course,
               l.library               AS library_section_id,
               s.SectionName           AS library_section_name,
               l.checkin_time,
               l.checkout_time,
               l.sex,
               l.classification,
               l.agency_organization
        FROM   Library_logs l
        LEFT JOIN LibrarySection s ON l.library = s.SectionID
        WHERE  1=1 {$where}
        ORDER  BY l.checkin_time DESC
    ", 'Select', $params);
}