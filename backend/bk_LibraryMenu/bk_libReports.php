<?php
/**
 * Shared utilities for library report handlers.
 *
 * Included by bk_tabReports.php and bk_viewReports.php.
 * Depends on execsqlSRS() provided by dbconnection.php.
 */

// ---------------------------------------------------------------------------
//  Query helpers
// ---------------------------------------------------------------------------

function buildWhereClause(array $post): array
{
    $clauses = [];
    $params  = [];

    if (!empty($post['startDate'])) {
        $clauses[]            = 'CAST(l.checkin_time AS DATE) >= :startDate';
        $params[':startDate'] = $post['startDate'];
    }
    if (!empty($post['endDate'])) {
        $clauses[]          = 'CAST(l.checkin_time AS DATE) <= :endDate';
        $params[':endDate'] = $post['endDate'];
    }
    if (!empty($post['classification']) && $post['classification'] !== 'All') {
        $clauses[]                 = 'l.classification = :classification';
        $params[':classification'] = $post['classification'];
    }
    if (!empty($post['library']) && $post['library'] !== 'All') {
        $clauses[]          = 'l.library = :library';   // fix: was :libraryId (placeholder mismatch)
        $params[':library'] = $post['library'];
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
               l.library              AS library_section_id,
               s.SectionName          AS library_section_name,
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

// ---------------------------------------------------------------------------
//  Primitive helpers
// ---------------------------------------------------------------------------

/** Minutes elapsed between two timestamp strings; 0.0 if either is absent or unparseable. */
function minutesBetween(?string $start, ?string $end): float
{
    if (!$start || !$end) return 0.0;
    $s = strtotime($start);
    $e = strtotime($end);
    return ($s && $e && $e > $s) ? ($e - $s) / 60.0 : 0.0;
}

/** True when the log record belongs to a student. */
function isStudent(array $log): bool
{
    return strcasecmp($log['classification'] ?? '', 'student') === 0;
}

/** Renders a muted classification badge. */
function getTypeBadge(string $text): string
{
    return '<span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">'
         . htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
         . '</span>';
}

// ---------------------------------------------------------------------------
//  Domain aggregators
// ---------------------------------------------------------------------------

/**
 * Aggregates visit logs into one record per unique visitor (keyed by id_number).
 *
 * @return array<string, array{
 *   display_label: string, name: string, college: string, course: string,
 *   classification: string, library: string, agency_organization: string,
 *   checkins: int, duration: float, last_checkin: string
 * }>
 */
function aggregateUsers(array $logs): array
{
    $users = [];

    foreach ($logs as $log) {
        $uid = $log['id_number'];

        if (!isset($users[$uid])) {
            $users[$uid] = [
                'display_label'       => ($uid === '0') ? ($log['name'] ?? 'Guest') : $uid,
                'name'                => $log['name'] ?? '',
                'college'             => $log['college'] ?? '',
                'course'              => $log['course'] ?? '',
                'classification'      => $log['classification'] ?? '',
                'library'             => $log['library_section_name'] ?? '',
                'agency_organization' => $log['agency_organization'] ?? '',
                'checkins'            => 0,
                'duration'            => 0.0,
                'last_checkin'        => $log['checkin_time'],
            ];
        }

        $users[$uid]['checkins']++;
        $users[$uid]['duration'] += minutesBetween($log['checkin_time'], $log['checkout_time'] ?? null);

        if ($log['checkin_time'] > $users[$uid]['last_checkin']) {
            $users[$uid]['last_checkin'] = $log['checkin_time'];
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
function aggregateColleges(array $logs): array
{
    $data      = [];
    $uniqueIds = [];

    foreach ($logs as $log) {
        if (!isStudent($log)) continue;

        $name = $log['college'] ?: 'Unknown';

        if (!isset($data[$name])) {
            $data[$name]      = ['duration' => 0.0, 'last_checkin' => $log['checkin_time']];
            $uniqueIds[$name] = [];
        }

        $uniqueIds[$name][$log['id_number']] = true;
        $data[$name]['duration'] += minutesBetween($log['checkin_time'], $log['checkout_time'] ?? null);

        if ($log['checkin_time'] > $data[$name]['last_checkin']) {
            $data[$name]['last_checkin'] = $log['checkin_time'];
        }
    }

    $result = [];
    foreach ($data as $name => $rec) {
        $result[$name] = array_merge($rec, ['visitors' => count($uniqueIds[$name])]);
    }

    return $result;
}

/**
 * Aggregates student visit logs into one record per college–course pair
 * (keyed by "college|course"). Non-student records are silently skipped.
 *
 * @return array<string, array{college: string, course: string, visitors: int, duration: float, last_checkin: string}>
 */
function aggregateCourses(array $logs): array
{
    $data      = [];
    $uniqueIds = [];

    foreach ($logs as $log) {
        if (!isStudent($log)) continue;

        $college = $log['college'] ?: 'Unknown';
        $course  = $log['course']  ?: 'Unknown';
        $key     = "{$college}|{$course}";

        if (!isset($data[$key])) {
            $data[$key]      = ['college' => $college, 'course' => $course, 'duration' => 0.0, 'last_checkin' => $log['checkin_time']];
            $uniqueIds[$key] = [];
        }

        $uniqueIds[$key][$log['id_number']] = true;
        $data[$key]['duration'] += minutesBetween($log['checkin_time'], $log['checkout_time'] ?? null);

        if ($log['checkin_time'] > $data[$key]['last_checkin']) {
            $data[$key]['last_checkin'] = $log['checkin_time'];
        }
    }

    $result = [];
    foreach ($data as $key => $rec) {
        $result[$key] = array_merge($rec, ['visitors' => count($uniqueIds[$key])]);
    }

    return $result;
}

// ---------------------------------------------------------------------------
//  Pagination
// ---------------------------------------------------------------------------

/**
 * Slices $items to the requested page and returns pagination metadata.
 *
 * @param  list<mixed> $items
 * @return array{items: list<mixed>, page: int, totalPages: int, total: int}
 */
function paginate(array $items, int $requestedPage, int $perPage): array
{
    $total      = count($items);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = min(max(1, $requestedPage), $totalPages);

    return [
        'items'      => array_slice($items, ($page - 1) * $perPage, $perPage),
        'page'       => $page,
        'totalPages' => $totalPages,
        'total'      => $total,
    ];
}