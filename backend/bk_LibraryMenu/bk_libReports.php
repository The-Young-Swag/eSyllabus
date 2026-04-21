<?php
/**
 * Shared utilities for library report handlers.
 *
 * Included by bk_tabReports.php and bk_viewReports.php.
 * Depends on execsqlSRS() provided by dbconnection.php.
 */

//  Query helpers


function buildWhereClause(array $post): array
{
    $conditions  = [];
    $queryParams = [];

    if (!empty($post['startDate'])) {
        $conditions[] = 'CAST(l.checkin_time AS DATE) >= :startDate';
        $queryParams[':startDate'] = $post['startDate'];
    }
    if (!empty($post['endDate'])) {
        $conditions[] = 'CAST(l.checkin_time AS DATE) <= :endDate';
        $queryParams[':endDate'] = $post['endDate'];
    }
    if (!empty($post['classification']) && $post['classification'] !== 'All') {
        $conditions[] = 'l.classification = :classification';
        $queryParams[':classification'] = $post['classification'];
    }
    if (!empty($post['library']) && $post['library'] !== 'All') {
        $conditions[] = 'l.library = :library';
        $queryParams[':library']  = $post['library'];
    }

    return [$conditions ? ' AND ' . implode(' AND ', $conditions) : '', $queryParams];
}

function fetchVisitLogs(string $whereClause, array $queryParams): array
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
        FROM   LibraryLogs l
        LEFT JOIN LibrarySection s ON l.library = s.SectionID
        WHERE  1=1 {$whereClause}
        ORDER  BY l.checkin_time DESC
    ", 'Select', $queryParams);
}

//  Primitive helpers

/** Minutes elapsed between two timestamp strings; 0.0 if either is absent or unparseable. */
function minutesBetween(?string $start, ?string $end): float
{
    if (!$start || !$end) return 0.0;
    $startTimestamp = strtotime($start);
    $endTimestamp = strtotime($end);
    return ($startTimestamp && $endTimestamp && $endTimestamp > $startTimestamp)
        ? ($endTimestamp - $startTimestamp) / 60.0
        : 0.0;
}

/** Renders a muted classification badge. */
function getTypeBadge(string $text): string
{
    return '<span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">'
         . htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
         . '</span>';
}


//  Domain aggregators


/**
 * Aggregates visit logs into one record per unique visitor (keyed by id_number).
 *
 * @return array<string, array{
 *   display_label: string, name: string, college: string, course: string,
 *   classification: string, library: string, agency_organization: string,
 *   checkins: int, duration: float, last_checkin: string
 * }>
 */
function summarizeUsers(array $logs): array
{
    $users = [];

    foreach ($logs as $logEntry) {
        $idNumber = $logEntry['id_number'];

        if (!isset($users[$idNumber])) {
            $users[$idNumber] = [
                'display_label' => ($idNumber === '0') ? ($logEntry['name'] ?? 'Guest') : $idNumber,
                'name' => $logEntry['name'] ?? '',
                'college' => $logEntry['college'] ?? '',
                'course' => $logEntry['course'] ?? '',
                'classification' => $logEntry['classification'] ?? '',
                'library' => $logEntry['library_section_name'] ?? '',
                'agency_organization' => $logEntry['agency_organization'] ?? '',
                'checkins' => 0,
                'duration' => 0.0,
                'last_checkin' => $logEntry['checkin_time'],
            ];
        }

        $users[$idNumber]['checkins']++;
        $users[$idNumber]['duration'] += minutesBetween($logEntry['checkin_time'], $logEntry['checkout_time'] ?? null);

        if ($logEntry['checkin_time'] > $users[$idNumber]['last_checkin']) {
            $users[$idNumber]['last_checkin'] = $logEntry['checkin_time'];
        }
    }

    return $users;
}

/**
 * Aggregates student visit logs into one record per college (keyed by college name).
 * Non-student records are silently skipped.
 *
 * @return array<string, array{visitors: int, duration: float, last_checkin: string}>
 */
function summarizeColleges(array $logs): array
{
    $collegeStats     = [];
    $uniqueVisitorIds = [];

    foreach ($logs as $logEntry) {
        if (strcasecmp($logEntry['classification'] ?? '', 'student') !== 0) continue;

        $college = $logEntry['college'] ?: 'Unknown';

        if (!isset($collegeStats[$college])) {
            $collegeStats[$college] = ['duration' => 0.0, 'last_checkin' => $logEntry['checkin_time']];
            $uniqueVisitorIds[$college] = [];
        }

        $uniqueVisitorIds[$college][$logEntry['id_number']] = true;
        $collegeStats[$college]['duration'] += minutesBetween($logEntry['checkin_time'], $logEntry['checkout_time'] ?? null);

        if ($logEntry['checkin_time'] > $collegeStats[$college]['last_checkin']) {
            $collegeStats[$college]['last_checkin'] = $logEntry['checkin_time'];
        }
    }

    $result = [];
    foreach ($collegeStats as $college => $collegeData) {
        $result[$college] = array_merge($collegeData, ['visitors' => count($uniqueVisitorIds[$college])]);
    }

    return $result;
}

/**
 * Aggregates student visit logs into one record per college–course pair
 * (keyed by "college|course"). Non-student records are silently skipped.
 *
 * @return array<string, array{college: string, course: string, visitors: int, duration: float, last_checkin: string}>
 */
function summarizeCourses(array $logs): array
{
    $courseStats = [];
    $uniqueVisitorIds = [];

    foreach ($logs as $logEntry) {
        if (strcasecmp($logEntry['classification'] ?? '', 'student') !== 0) continue;

        $college = $logEntry['college'] ?: 'Unknown';
        $course  = $logEntry['course']  ?: 'Unknown';
        $courseKey = "{$college}|{$course}";

        if (!isset($courseStats[$courseKey])) {
            $courseStats[$courseKey] = ['college' => $college, 'course' => $course, 'duration' => 0.0, 'last_checkin' => $logEntry['checkin_time']];
            $uniqueVisitorIds[$courseKey] = [];
        }

        $uniqueVisitorIds[$courseKey][$logEntry['id_number']] = true;
        $courseStats[$courseKey]['duration'] += minutesBetween($logEntry['checkin_time'], $logEntry['checkout_time'] ?? null);

        if ($logEntry['checkin_time'] > $courseStats[$courseKey]['last_checkin']) {
            $courseStats[$courseKey]['last_checkin'] = $logEntry['checkin_time'];
        }
    }

    $result = [];
    foreach ($courseStats as $courseKey => $courseData) {
        $result[$courseKey] = array_merge($courseData, ['visitors' => count($uniqueVisitorIds[$courseKey])]);
    }

    return $result;
}

//  Pagination
/**
 * Slices $items to the requested page and returns pagination metadata.
 *
 * @param  list<mixed> $items
 * @return array{items: list<mixed>, page: int, totalPages: int, total: int}
 */
function paginate(array $items, int $requestedPage, int $perPage): array
{
    $total = count($items);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $requestedPage), $totalPages);

    return [
        'items' => array_slice($items, ($page - 1) * $perPage, $perPage),
        'page' => $page,
        'totalPages' => $totalPages,
        'total' => $total,
    ];
}