<?php
// Shared utilities for both tab and view-all handlers.
// Included by bk_viewReports.php and bk_tabReports.php.

function buildWhereClause(array $postData): array
{
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

function fetchVisitLogs(string $where, array $params): array
{
    return execsqlSRS("
        SELECT l.id,
               l.id_number,
               l.name,
               l.college,
               l.course,
               l.library AS library_section_id,
               s.SectionName AS library_section_name,
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