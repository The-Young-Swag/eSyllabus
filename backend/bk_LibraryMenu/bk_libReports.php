<?php
/**
 * Library Analytics — Backend Handler
 */

include '../../db/dbconnection.php';
header('Content-Type: application/json');

// ── CONSTANTS ─────────────────────────────────────────────────────────────────

const COLLEGE_COLOR_FALLBACK = 'rgba(139,92,246,0.88)';
const COLLEGE_COLOR_MAP = [
    'CAF' => 'rgba(22,163,74,0.88)',
    'CAS' => 'rgba(234,88,12,0.88)',
    'CBM' => 'rgba(202,138,4,0.88)',
    'CET' => 'rgba(220,38,38,0.88)',
    'CED' => 'rgba(37,99,235,0.88)',
    'CVM' => 'rgba(107,114,128,0.88)',
];
const VALID_TABS        = ['logs', 'users', 'colleges', 'courses', 'demographics'];
const ROWS_PER_PAGE     = 10;
const MODAL_TABLE_CLASS = 'table table-sm table-striped table-hover align-middle mb-0';

// ── UTILITIES ─────────────────────────────────────────────────────────────────

function sendResponse(array $payload): void { echo json_encode($payload); exit; }

function calcDurationMinutes(string $checkinTime, ?string $checkoutTime): float
{
    return $checkoutTime ? (strtotime($checkoutTime) - strtotime($checkinTime)) / 60 : 0.0;
}

function formatDateTime(string $datetime): string
{
    return date('M j, Y g:i A', strtotime($datetime));
}

function escHtml(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function getUserDisplayLabel(array $log): string
{
    $idNumber = $log['id_number'] ?? '';
    return ($idNumber === '0' || $idNumber === '') ? ($log['name'] ?? 'Guest') : $idNumber;
}

function resolveCollegeColor(string $collegeName): string
{
    $upperName = strtoupper($collegeName);
    foreach (COLLEGE_COLOR_MAP as $abbreviation => $color) {
        if (str_contains($upperName, $abbreviation)) return $color;
    }
    return COLLEGE_COLOR_FALLBACK;
}

/**
 * Annotates a ranked list with rank number and tie flags.
 * Items must already be sorted descending by $valueKey.
 */
function annotateRanks(array $items, string $valueKey): array
{
    $firstRank = $tieCount = [];
    foreach ($items as $index => $item) {
        $value = $item[$valueKey];
        $firstRank[$value] ??= $index + 1;
        $tieCount[$value]    = ($tieCount[$value] ?? 0) + 1;
    }
    return array_map(function ($item) use ($firstRank, $tieCount, $valueKey) {
        $value = $item[$valueKey];
        return $item + ['rank' => $firstRank[$value], 'tied' => $tieCount[$value] > 1, 'tiedCount' => $tieCount[$value]];
    }, $items);
}

// ── FILTER & FETCH ────────────────────────────────────────────────────────────

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
        $params[':libraryId'] = $postData['library'];
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

// ── TAB REQUEST ───────────────────────────────────────────────────────────────

function HandleTabRequest(): void
{
    $tab = trim($_POST['tab'] ?? 'users');
    if (!in_array($tab, VALID_TABS)) sendResponse(['status' => 'error', 'message' => 'Invalid tab.']);

    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);

    // ── KPIs ──────────────────────────────────────────────────────────────
    $totalVisits  = count($logs);
    $totalMinutes = array_sum(array_map(
        fn($log) => calcDurationMinutes($log['checkin_time'], $log['checkout_time']),
        $logs
    ));
    $endDate = trim($_POST['endDate'] ?? '');
    $kpis = [
        'totalVisits'     => $totalVisits,
        'totalDuration'   => round($totalMinutes),
        'uniqueUsers'     => count(array_unique(array_column($logs, 'id_number'))),
        'avgDuration'     => $totalVisits ? round($totalMinutes / $totalVisits, 1) : 0,
        'endDateCheckins' => $endDate
            ? count(array_filter($logs, fn($log) => substr($log['checkin_time'], 0, 10) === $endDate))
            : 0,
    ];

    // ── Top Users ─────────────────────────────────────────────────────────
    $topCheckins = $topDuration = [];
    foreach (['Student', 'Employee', 'Guest'] as $classification) {
        $classLogs = array_filter($logs, fn($log) => strcasecmp($log['classification'], $classification) === 0);
        $counts = $durations = $meta = [];
        foreach ($classLogs as $log) {
            $userId = $log['id_number'];
            $counts[$userId]    = ($counts[$userId]    ?? 0) + 1;
            $durations[$userId] = ($durations[$userId] ?? 0) + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
            if (!isset($meta[$userId])) {
                $meta[$userId] = [
                    'display_label'       => getUserDisplayLabel($log),
                    'name'                => $log['name']                 ?? '',
                    'college'             => $log['college']              ?? '',
                    'course'              => $log['course']               ?? '',
                    'library'             => $log['library_section_name'] ?? '—',
                    'agency_organization' => $log['agency_organization']  ?? '—',
                    'last_checkin'        => $log['checkin_time'],
                ];
            } elseif ($log['checkin_time'] > $meta[$userId]['last_checkin']) {
                $meta[$userId]['last_checkin'] = $log['checkin_time'];
            }
        }
        arsort($counts);
        foreach (array_slice($counts, 0, 3, true) as $userId => $count) {
            $topCheckins[$classification][$userId] = array_merge($meta[$userId], ['count' => $count]);
        }
        arsort($durations);
        foreach (array_slice($durations, 0, 3, true) as $userId => $minutes) {
            $topDuration[$classification][$userId] = array_merge($meta[$userId], ['minutes' => $minutes]);
        }
    }

    // ── Top Colleges ──────────────────────────────────────────────────────
    $studentLogs  = array_filter($logs, fn($log) => strcasecmp($log['classification'] ?? '', 'student') === 0);
    $seenStudents = $counts = $durations = $lastVisit = [];
    foreach ($studentLogs as $log) {
        $college   = $log['college'] ?: 'Unknown';
        $studentId = $log['id_number'];
        if (!isset($seenStudents[$college][$studentId])) {
            $seenStudents[$college][$studentId] = true;
            $counts[$college] = ($counts[$college] ?? 0) + 1;
        }
        $durations[$college] = ($durations[$college] ?? 0) + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if (!isset($lastVisit[$college]) || $log['checkin_time'] > $lastVisit[$college]) {
            $lastVisit[$college] = $log['checkin_time'];
        }
    }
    $top3CollegesCheckin = $top3CollegesDuration = [];
    arsort($counts);
    foreach (array_slice($counts, 0, 3, true) as $college => $count) {
        $top3CollegesCheckin[$college] = ['count' => $count, 'last_checkin' => $lastVisit[$college], 'color' => resolveCollegeColor($college)];
    }
    arsort($durations);
    foreach (array_slice($durations, 0, 3, true) as $college => $minutes) {
        $top3CollegesDuration[$college] = ['minutes' => $minutes, 'last_checkin' => $lastVisit[$college], 'color' => resolveCollegeColor($college)];
    }

    // ── Top Courses ───────────────────────────────────────────────────────
    $seenCourseStudents = $counts = $durations = $lastVisit = [];
    foreach ($studentLogs as $log) {
        $college   = $log['college'] ?: 'Unknown';
        $course    = $log['course']  ?: 'Unknown';
        $studentId = $log['id_number'];
        $courseKey = "{$college}|{$course}";
        if (!isset($seenCourseStudents[$college][$course][$studentId])) {
            $seenCourseStudents[$college][$course][$studentId] = true;
            $counts[$college][$course] = ($counts[$college][$course] ?? 0) + 1;
        }
        $durations[$college][$course] = ($durations[$college][$course] ?? 0) + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if (!isset($lastVisit[$courseKey]) || $log['checkin_time'] > $lastVisit[$courseKey]) {
            $lastVisit[$courseKey] = $log['checkin_time'];
        }
    }
    $topCoursesCheckin = $topCoursesDuration = [];
    foreach ($counts as $college => $courses) {
        arsort($courses);
        foreach (array_slice($courses, 0, 3, true) as $course => $total) {
            $topCoursesCheckin[$college][$course] = ['count' => $total, 'last_checkin' => $lastVisit["{$college}|{$course}"] ?? null];
        }
    }
    foreach ($durations as $college => $courses) {
        arsort($courses);
        foreach (array_slice($courses, 0, 3, true) as $course => $minutes) {
            $topCoursesDuration[$college][$course] = ['minutes' => $minutes, 'last_checkin' => $lastVisit["{$college}|{$course}"] ?? null];
        }
    }

    // ── KPI Top 3 (derived from already-computed data — no re-scan needed) ─
    // Top students = $topCheckins['Student'] is already sorted top-3 students.
    $top3Students = array_values(array_map(fn($user) => [
        'id_number' => $user['display_label'],
        'name'      => $user['name'],
        'college'   => $user['college'],
        'course'    => $user['course'],
        'count'     => $user['count'],
    ], $topCheckins['Student'] ?? []));

    // Top colleges = first 3 entries of $top3CollegesCheckin (already sorted).
    $top3Colleges = array_map(
        fn($name, $data) => ['name' => $name, 'count' => $data['count']],
        array_keys($top3CollegesCheckin),
        array_values($top3CollegesCheckin)
    );

    // Top courses = flatten $topCoursesCheckin, sort, slice to 3.
    $top3Courses = [];
    foreach ($topCoursesCheckin as $college => $courses) {
        foreach ($courses as $course => $data) {
            $top3Courses[] = ['college' => $college, 'course' => $course, 'count' => $data['count']];
        }
    }
    usort($top3Courses, fn($a, $b) => $b['count'] <=> $a['count']);
    $top3Courses = array_slice($top3Courses, 0, 3);

    // ── Pre-built chart arrays (reduces client-side data reshaping) ────────
    $chartTopCheckins = $chartTopDuration = [];
    foreach ($topCheckins as $users) {
        foreach ($users as $user) {
            $chartTopCheckins[] = ['label' => $user['display_label'], 'value' => $user['count']];
        }
    }
    usort($chartTopCheckins, fn($a, $b) => $b['value'] <=> $a['value']);
    $chartTopCheckins = array_slice($chartTopCheckins, 0, 3);

    foreach ($topDuration as $users) {
        foreach ($users as $user) {
            $chartTopDuration[] = ['label' => $user['display_label'], 'value' => $user['minutes']];
        }
    }
    usort($chartTopDuration, fn($a, $b) => $b['value'] <=> $a['value']);
    $chartTopDuration = array_slice($chartTopDuration, 0, 3);

    $courseChartData = [];
    foreach ($topCoursesCheckin as $college => $courses) {
        foreach ($courses as $course => $data) {
            $courseChartData[] = [
                'label'    => "{$college} · {$course}",
                'checkins' => $data['count'],
                'duration' => round($topCoursesDuration[$college][$course]['minutes'] ?? 0),
            ];
        }
    }

    // ── Other aggregations ────────────────────────────────────────────────
    $sexDistribution = $classificationDistribution = [];
    foreach ($logs as $log) {
        $sexKey  = $log['sex']            ?: 'Unknown';
        $typeKey = $log['classification'] ?: 'Unknown';
        $sexDistribution[$sexKey]             = ($sexDistribution[$sexKey]             ?? 0) + 1;
        $classificationDistribution[$typeKey] = ($classificationDistribution[$typeKey] ?? 0) + 1;
    }

    $allLogsFlat = array_map(fn($log) => [
        'id_number'           => $log['id_number']            ?? '',
        'name'                => $log['name']                 ?? '',
        'college'             => $log['college']              ?? '',
        'course'              => $log['course']               ?? '',
        'classification'      => $log['classification']       ?? '',
        'library'             => $log['library_section_name'] ?? '',
        'sex'                 => $log['sex']                  ?? '',
        'checkin_time'        => $log['checkin_time']         ?? '',
        'checkout_time'       => $log['checkout_time']        ?? '',
        'agency_organization' => $log['agency_organization']  ?? '',
        'duration_minutes'    => calcDurationMinutes($log['checkin_time'], $log['checkout_time'] ?? null),
    ], $logs);

    $html = match ($tab) {
        'logs'         => renderLogsTab($allLogsFlat),
        'users'        => renderUsersTab($topCheckins, $topDuration),
        'colleges'     => renderCollegesTab($top3CollegesCheckin, $top3CollegesDuration),
        'courses'      => renderCoursesTab($topCoursesCheckin, $topCoursesDuration),
        'demographics' => renderDemographicsTab($sexDistribution, count($allLogsFlat)),
        default        => '',
    };

    sendResponse([
        'status'                     => 'success',
        'html'                       => $html,
        'totalVisits'                => $kpis['totalVisits'],
        'totalDuration'              => $kpis['totalDuration'],
        'avgDuration'                => $kpis['avgDuration'],
        'uniqueUsers'                => $kpis['uniqueUsers'],
        'endDateCheckins'            => $kpis['endDateCheckins'],
        'top3Students'               => annotateRanks($top3Students, 'count'),
        'top3Colleges'               => annotateRanks($top3Colleges, 'count'),
        'top3Courses'                => annotateRanks($top3Courses,  'count'),
        'topCheckins'                => $topCheckins,
        'topDuration'                => $topDuration,
        'classificationDistribution' => $classificationDistribution,
        'top3CollegesCheckin'        => $top3CollegesCheckin,
        'top3CollegesDuration'       => $top3CollegesDuration,
        'topCoursesCheckin'          => $topCoursesCheckin,
        'topCoursesDuration'         => $topCoursesDuration,
        'sexDistribution'            => $sexDistribution,
        'allLogs'                    => $allLogsFlat,
        'chartTopCheckins'           => $chartTopCheckins,
        'chartTopDuration'           => $chartTopDuration,
        'courseChartData'            => $courseChartData,
    ]);
}

// ── VIEW ALL ──────────────────────────────────────────────────────────────────

function HandleViewAll(): void
{
    $tab  = trim($_POST['tab'] ?? 'users');
    $page = max(1, (int)trim($_POST['page'] ?? '1'));

    if (!in_array($tab, VALID_TABS)) sendResponse(['status' => 'error', 'message' => 'Invalid tab.']);

    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);

    $offset   = ($page - 1) * ROWS_PER_PAGE;
    $pageData = match ($tab) {
        'logs'         => buildViewAllLogs($logs, $offset, ROWS_PER_PAGE),
        'users'        => buildViewAllUsers($logs, $offset, ROWS_PER_PAGE),
        'colleges'     => buildViewAllColleges($logs, $offset, ROWS_PER_PAGE),
        'courses'      => buildViewAllCourses($logs, $offset, ROWS_PER_PAGE),
        'demographics' => buildViewAllDemographics($logs, $offset, ROWS_PER_PAGE),
        default        => ['rows' => [], 'total' => 0],
    };

    $totalRecords = $pageData['total'];
    $totalPages   = $totalRecords > 0 ? (int)ceil($totalRecords / ROWS_PER_PAGE) : 1;
    $page         = min($page, $totalPages);

    sendResponse([
        'status'     => 'success',
        'tableHtml'  => renderModalTable($tab, $pageData['rows']),
        'pagination' => renderModalPagination($totalPages, $page, $totalRecords, ROWS_PER_PAGE),
        'total'      => $totalRecords,
        'totalPages' => $totalPages,
        'page'       => $page,
    ]);
}

// ── VIEW-ALL BUILDERS ─────────────────────────────────────────────────────────

function buildViewAllUsers(array $logs, int $offset, int $limit): array
{
    $aggregated = [];
    foreach ($logs as $log) {
        $userId = $log['id_number'];
        if (!isset($aggregated[$userId])) {
            $aggregated[$userId] = [
                'display_label' => getUserDisplayLabel($log),
                'name'          => $log['name']                 ?? '',
                'college'       => $log['college']              ?? '',
                'course'        => $log['course']               ?? '',
                'type'          => $log['classification'],
                'library'       => $log['library_section_name'],
                'checkins'      => 0,
                'duration'      => 0,
                'last_checkin'  => $log['checkin_time'],
            ];
        }
        $aggregated[$userId]['checkins']++;
        $aggregated[$userId]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $aggregated[$userId]['last_checkin']) {
            $aggregated[$userId]['last_checkin'] = $log['checkin_time'];
        }
    }
    uasort($aggregated, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_values(array_slice($aggregated, $offset, $limit, true)), 'total' => count($aggregated)];
}

function buildViewAllColleges(array $logs, int $offset, int $limit): array
{
    $aggregated  = [];
    $studentLogs = array_filter($logs, fn($log) => strcasecmp($log['classification'] ?? '', 'student') === 0);
    foreach ($studentLogs as $log) {
        $college = $log['college'] ?: 'Unknown';
        $aggregated[$college] ??= ['college_name' => $college, 'unique_visitors' => [], 'duration' => 0, 'last_checkin' => $log['checkin_time']];
        $aggregated[$college]['unique_visitors'][$log['id_number']] = true;
        $aggregated[$college]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $aggregated[$college]['last_checkin']) {
            $aggregated[$college]['last_checkin'] = $log['checkin_time'];
        }
    }
    $rows = array_map(fn($data) => [
        'name'         => $data['college_name'],
        'checkins'     => count($data['unique_visitors']),
        'duration'     => $data['duration'],
        'last_checkin' => $data['last_checkin'],
    ], array_values($aggregated));
    usort($rows, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function buildViewAllCourses(array $logs, int $offset, int $limit): array
{
    $aggregated  = [];
    $studentLogs = array_filter($logs, fn($log) => strcasecmp($log['classification'] ?? '', 'student') === 0);
    foreach ($studentLogs as $log) {
        $courseKey = ($log['college'] ?: 'Unknown') . '|' . ($log['course'] ?: 'Unknown');
        $aggregated[$courseKey] ??= [
            'college'         => $log['college'] ?: 'Unknown',
            'course'          => $log['course']  ?: 'Unknown',
            'unique_visitors' => [],
            'duration'        => 0,
            'last_checkin'    => $log['checkin_time'],
        ];
        $aggregated[$courseKey]['unique_visitors'][$log['id_number']] = true;
        $aggregated[$courseKey]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $aggregated[$courseKey]['last_checkin']) {
            $aggregated[$courseKey]['last_checkin'] = $log['checkin_time'];
        }
    }
    $rows = array_map(fn($data) => [
        'college'      => $data['college'],
        'course'       => $data['course'],
        'checkins'     => count($data['unique_visitors']),
        'duration'     => $data['duration'],
        'last_checkin' => $data['last_checkin'],
    ], array_values($aggregated));
    usort($rows, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function buildViewAllLogs(array $logs, int $offset, int $limit): array
{
    $rows = array_map(fn($log) => [
        'id_number'           => $log['id_number']            ?? '',
        'name'                => $log['name']                 ?? '',
        'college'             => $log['college']              ?? '',
        'course'              => $log['course']               ?? '',
        'classification'      => $log['classification']       ?? '',
        'library'             => $log['library_section_name'] ?? '',
        'sex'                 => $log['sex']                  ?? '',
        'checkin_time'        => $log['checkin_time']         ?? '',
        'checkout_time'       => $log['checkout_time']        ?? null,
        'agency_organization' => $log['agency_organization']  ?? '',
        'duration'            => calcDurationMinutes($log['checkin_time'], $log['checkout_time'] ?? null),
    ], $logs);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function buildViewAllDemographics(array $logs, int $offset, int $limit): array
{
    $rows = array_map(fn($log) => [
        'display_label' => getUserDisplayLabel($log),
        'sex'           => $log['sex'],
        'checkin'       => $log['checkin_time'],
        'checkout'      => $log['checkout_time'],
        'duration'      => calcDurationMinutes($log['checkin_time'], $log['checkout_time']),
    ], $logs);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

// ── MODAL TABLE ───────────────────────────────────────────────────────────────

function renderModalTable(string $tab, array $rows): string
{
    $tableConfigs = [
        'logs' => [
            'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Section', 'Sex', 'Check-in', 'Check-out', 'Agency / Organization', 'Duration (min)'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">'    . escHtml($row['id_number']) . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['name']           ?: '—') . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['college']         ?: '—') . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['course']          ?: '—') . '</td>' .
                '<td><span class="badge bg-secondary-subtle text-secondary rounded-pill small">' . escHtml($row['classification'] ?: '—') . '</span></td>' .
                '<td class="text-muted small">'     . escHtml($row['library']         ?: '—') . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['sex']             ?: '—') . '</td>' .
                '<td class="text-muted small">'     . ($row['checkin_time']  ? formatDateTime($row['checkin_time'])  : '—') . '</td>' .
                '<td class="text-muted small">'     . ($row['checkout_time'] ? formatDateTime($row['checkout_time']) : '—') . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['agency_organization'] ?: '—') . '</td>' .
                '<td class="text-end pe-3">'        . (isset($row['duration']) ? (int)round($row['duration']) : '—') . '</td>',
        ],
        'users' => [
            'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">'                  . escHtml($row['display_label']) . '</td>' .
                '<td class="text-muted small">'                   . escHtml($row['name']) . '</td>' .
                '<td class="text-muted small">'                   . escHtml($row['college'] ?: '—') . '</td>' .
                '<td class="text-muted small">'                   . escHtml($row['course']  ?: '—') . '</td>' .
                '<td><span class="badge bg-secondary-subtle text-secondary rounded-pill small">' . escHtml($row['type']) . '</span></td>' .
                '<td class="text-muted small">'                   . escHtml($row['library'] ?? '—') . '</td>' .
                '<td class="text-end fw-semibold text-primary">'  . (int)$row['checkins'] . '</td>' .
                '<td class="text-end">'                           . (int)round($row['duration']) . '</td>' .
                '<td class="text-muted small pe-3">'              . formatDateTime($row['last_checkin']) . '</td>',
        ],
        'colleges' => [
            'headers' => ['College', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">'     . escHtml($row['name']) . '</td>' .
                '<td class="text-end">'              . (int)$row['checkins'] . '</td>' .
                '<td class="text-end">'              . (int)round($row['duration']) . '</td>' .
                '<td class="text-muted small pe-3">' . formatDateTime($row['last_checkin']) . '</td>',
        ],
        'courses' => [
            'headers' => ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 text-muted small">' . escHtml($row['college']) . '</td>' .
                '<td class="fw-semibold">'            . escHtml($row['course'])  . '</td>' .
                '<td class="text-end">'               . (int)$row['checkins'] . '</td>' .
                '<td class="text-end">'               . (int)round($row['duration']) . '</td>' .
                '<td class="text-muted small pe-3">'  . formatDateTime($row['last_checkin']) . '</td>',
        ],
        'demographics' => [
            'headers' => ['ID Number', 'Sex', 'Check-in', 'Check-out', 'Duration (min)'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">' . escHtml($row['display_label']) . '</td>' .
                '<td>'                           . escHtml($row['sex']) . '</td>' .
                '<td class="text-muted small">'  . formatDateTime($row['checkin']) . '</td>' .
                '<td class="text-muted small">'  . ($row['checkout'] ? formatDateTime($row['checkout']) : '—') . '</td>' .
                '<td class="text-end pe-3">'     . (int)round($row['duration']) . '</td>',
        ],
    ];

    if (!isset($tableConfigs[$tab])) return '';

    $config      = $tableConfigs[$tab];
    $headerCells = implode('', array_map(fn($header) => "<th class=\"small fw-semibold\">{$header}</th>", $config['headers']));
    $bodyRows    = implode('', array_map(fn($row) => '<tr>' . $config['rowFn']($row) . '</tr>', $rows));

    return "<div class=\"table-responsive\"><table class=\"" . MODAL_TABLE_CLASS . "\">"
         . "<thead class=\"table-dark\"><tr>{$headerCells}</tr></thead>"
         . "<tbody class=\"small\">{$bodyRows}</tbody>"
         . "</table></div>";
}

// ── MODAL PAGINATION ──────────────────────────────────────────────────────────

function renderModalPagination(int $totalPages, int $currentPage, int $totalRecords, int $perPage): string
{
    if ($totalPages <= 1) return '';

    $isFirstPage = $currentPage === 1;
    $isLastPage  = $currentPage === $totalPages;
    $windowSize  = 5;
    $windowStart = max(1, min($currentPage - intdiv($windowSize, 2), $totalPages - $windowSize + 1));
    $windowEnd   = min($totalPages, $windowStart + $windowSize - 1);

    $buildItem = fn(string $label, int $targetPage, string $extraClass = '', bool $isText = false) =>
        "<li class=\"page-item {$extraClass}\"><a class=\"page-link\" href=\"#\""
        . ($isText ? '' : " data-page=\"{$targetPage}\"")
        . ">{$label}</a></li>";

    $items  = $buildItem('«', 1,                $isFirstPage ? 'disabled' : '');
    $items .= $buildItem('‹', $currentPage - 1, $isFirstPage ? 'disabled' : '');

    if ($windowStart > 1) {
        $items .= $buildItem('1', 1, $currentPage === 1 ? 'active' : '');
        if ($windowStart > 2) $items .= $buildItem('…', 0, 'disabled', true);
    }
    for ($pageNum = $windowStart; $pageNum <= $windowEnd; $pageNum++) {
        $items .= $buildItem((string)$pageNum, $pageNum, $pageNum === $currentPage ? 'active' : '');
    }
    if ($windowEnd < $totalPages) {
        if ($windowEnd < $totalPages - 1) $items .= $buildItem('…', 0, 'disabled', true);
        $items .= $buildItem((string)$totalPages, $totalPages, $currentPage === $totalPages ? 'active' : '');
    }
    $items .= $buildItem('›', $currentPage + 1, $isLastPage ? 'disabled' : '');
    $items .= $buildItem('»', $totalPages,       $isLastPage ? 'disabled' : '');

    $fromRecord = ($currentPage - 1) * $perPage + 1;
    $toRecord   = min($currentPage * $perPage, $totalRecords);

    return "<small class=\"text-muted\">Showing {$fromRecord}–{$toRecord} of {$totalRecords} records</small>"
         . "<nav class=\"mt-1\"><ul class=\"pagination pagination-sm mb-0 flex-wrap justify-content-center\">{$items}</ul></nav>";
}

// ── TAB HTML RENDERERS ────────────────────────────────────────────────────────

function renderLogsTab(array $allLogsFlat): string
{
    ob_start(); ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
            <div>
                <span class="fw-semibold small">All Visit Logs</span>
                <p class="text-muted mb-0" style="font-size:.72rem;">Every check-in within selected date range</p>
            </div>
            <button class="btn btn-sm btn-outline-primary py-0 px-2 view-all-btn"
                    data-tab="logs" style="font-size:.75rem;">
                <i class="bi bi-arrow-up-right-square me-1"></i>View All
            </button>
        </div>
        <div class="card-body p-0"
             id="allLogsCard"
             data-rows="<?= htmlspecialchars(json_encode($allLogsFlat), ENT_QUOTES) ?>"
             data-per-page="10">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th class="ps-3 small">ID Number</th>
                        <th class="small">Name</th>
                        <th class="small">College</th>
                        <th class="small">Course</th>
                        <th class="small">Type</th>
                        <th class="small">Section</th>
                        <th class="small">Sex</th>
                        <th class="small">Check-in</th>
                        <th class="small">Check-out</th>
                        <th class="small">Agency / Organization</th>
                        <th class="text-end pe-3 small">Duration (min)</th>
                    </tr></thead>
                    <tbody id="allLogsTbody" class="small"></tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2 px-3">
            <div class="d-flex flex-column align-items-center gap-1" id="allLogsPager"></div>
        </div>
    </div>
    <?php return ob_get_clean();
}

function renderUsersTab(array $topCheckins, array $topDuration): string
{
    $flatCheckins = [];
    foreach ($topCheckins as $classification => $users) {
        foreach ($users as $user) {
            $flatCheckins[] = [
                'display_label'       => $user['display_label'],
                'college'             => $user['college']             ?: '—',
                'course'              => $user['course']              ?: '—',
                'type'                => $classification,
                'library'             => $user['library']             ?? '—',
                'count'               => $user['count'],
                'agency_organization' => $user['agency_organization'] ?? '—',
                'last_checkin'        => date('M j', strtotime($user['last_checkin'])),
            ];
        }
    }

    $flatDuration = [];
    foreach ($topDuration as $classification => $users) {
        foreach ($users as $user) {
            $flatDuration[] = [
                'display_label'       => $user['display_label'],
                'college'             => $user['college']             ?: '—',
                'course'              => $user['course']              ?: '—',
                'type'                => $classification,
                'minutes'             => (int)round($user['minutes']),
                'agency_organization' => $user['agency_organization'] ?? '—',
            ];
        }
    }
    usort($flatDuration, fn($a, $b) => $b['minutes'] <=> $a['minutes']);

    ob_start(); ?>
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <p class="fw-semibold small mb-0">Top Visitors by Check-ins</p>
                                <p class="text-muted mb-0" style="font-size:.72rem;">Most frequent visitors this period</p>
                            </div>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2">Top 3</span>
                        </div>
                        <div class="card-body px-3 pt-3 pb-2">
                            <div style="height:180px;position:relative;"><canvas id="chartTopUserCheckins"></canvas></div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <p class="fw-semibold small mb-0">Top Visitors by Duration</p>
                                <p class="text-muted mb-0" style="font-size:.72rem;">Longest cumulative time in library</p>
                            </div>
                            <span class="badge bg-success-subtle text-success rounded-pill px-2">Top 3</span>
                        </div>
                        <div class="card-body px-3 pt-3 pb-2">
                            <div style="height:180px;position:relative;"><canvas id="chartTopUserDuration"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Visitor Type</p>
                    <p class="text-muted mb-0" style="font-size:.72rem;">Breakdown by classification</p>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center px-3">
                    <div style="height:320px;width:100%;position:relative;"><canvas id="chartVisitorTypeDonut"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="fw-semibold small">Check-in Details</span>
                    <button class="btn btn-sm btn-outline-primary py-0 px-2 view-all-btn"
                            data-tab="users" style="font-size:.75rem;">
                        <i class="bi bi-arrow-up-right-square me-1"></i>View All
                    </button>
                </div>
                <div class="card-body p-0" style="min-height:175px;"
                     id="checkinDetailsCard"
                     data-rows="<?= htmlspecialchars(json_encode($flatCheckins), ENT_QUOTES) ?>"
                     data-page="1" data-per-page="3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light"><tr>
                                <th class="ps-3 small">ID Number</th>
                                <th class="small">College</th>
                                <th class="small">Course</th>
                                <th class="small">Type</th>
                                <th class="small">Section</th>
                                <th class="text-end small">Check-ins</th>
                                <th class="small">Agency Organization</th>
                                <th class="text-end pe-3 small">Last Visit</th>
                            </tr></thead>
                            <tbody id="checkinDetailsTbody" class="small"></tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-2 px-3">
                    <div class="d-flex flex-column align-items-center gap-1" id="checkinDetailsPager"></div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <span class="fw-semibold small">Duration Details</span>
                </div>
                <div class="card-body p-0" style="min-height:175px;"
                     id="durationDetailsCard"
                     data-rows="<?= htmlspecialchars(json_encode($flatDuration), ENT_QUOTES) ?>"
                     data-page="1" data-per-page="3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light"><tr>
                                <th class="ps-3 small">ID Number</th>
                                <th class="small">College</th>
                                <th class="small">Course</th>
                                <th class="small">Type</th>
                                <th class="text-end pe-3 small">Minutes</th>
                                <th class="small">Agency Organization</th>
                            </tr></thead>
                            <tbody id="durationDetailsTbody" class="small"></tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-2 px-3">
                    <div class="d-flex flex-column align-items-center gap-1" id="durationDetailsPager"></div>
                </div>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
}

function renderCollegesTab(array $topByCheckins, array $topByDuration): string
{
    $panels = [
        [
            'title'    => 'Top Colleges — Check-ins',
            'subtitle' => 'Unique visitors per college',
            'canvas'   => 'chartCollegeCheckin',
            'data'     => $topByCheckins,
            'valueKey' => 'count',
            'label'    => 'Visitors',
            'class'    => 'text-primary',
            'isCount'  => true,
        ],
        [
            'title'    => 'Top Colleges — Duration',
            'subtitle' => 'Total session time per college',
            'canvas'   => 'chartCollegeDuration',
            'data'     => $topByDuration,
            'valueKey' => 'minutes',
            'label'    => 'Duration (min)',
            'class'    => 'text-success',
            'isCount'  => false,
        ],
    ];

    ob_start(); ?>
    <div class="row g-4">
        <?php foreach ($panels as $panel): ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0"><?= $panel['title'] ?></p>
                    <p class="text-muted mb-0" style="font-size:.72rem;"><?= $panel['subtitle'] ?></p>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="height:260px;position:relative;"><canvas id="<?= $panel['canvas'] ?>"></canvas></div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr>
                            <th>College</th>
                            <th class="text-end"><?= $panel['label'] ?></th>
                            <th class="text-end">Last Visit</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($panel['data'] as $collegeName => $data): ?>
                        <tr>
                            <td class="fw-semibold"><?= escHtml($collegeName) ?></td>
                            <td class="text-end fw-semibold <?= $panel['class'] ?>">
                                <?= $panel['isCount'] ? $data[$panel['valueKey']] : round($data[$panel['valueKey']]) ?>
                            </td>
                            <td class="text-end text-muted"><?= date('M j, Y', strtotime($data['last_checkin'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($panel['data'])): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if (!$panel['isCount']): ?>
                    <div class="text-end mt-2">
                        <button class="btn btn-sm btn-outline-secondary view-all-btn"
                                data-tab="colleges" style="font-size:.75rem;">View All Colleges</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php return ob_get_clean();
}

function renderCoursesTab(array $topByCheckins, array $topByDuration): string
{
    $flattenCourses = fn(array $data) => array_merge(...array_map(
        fn($college, $courses) => array_map(
            fn($course, $courseData) => array_merge(['college' => $college, 'course' => $course], $courseData),
            array_keys($courses), array_values($courses)
        ),
        array_keys($data), array_values($data)
    ));

    $flatCheckins = $flattenCourses($topByCheckins);
    usort($flatCheckins, fn($first, $second) => $second['count'] <=> $first['count']);

    $flatDuration = $flattenCourses($topByDuration);
    usort($flatDuration, fn($first, $second) => $second['minutes'] <=> $first['minutes']);

    $panels = [
        ['title' => 'Check-ins', 'canvas' => 'chartCoursesCheckin',  'valueKey' => 'count',   'rows' => $flatCheckins, 'subtitle' => 'Unique visitors per course'],
        ['title' => 'Duration',  'canvas' => 'chartCoursesDuration', 'valueKey' => 'minutes', 'rows' => $flatDuration, 'subtitle' => 'Total session time per course'],
    ];

    ob_start(); ?>
    <div class="row g-4">
        <?php foreach ($panels as $panel): ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Top Courses — <?= $panel['title'] ?></p>
                    <p class="text-muted mb-0" style="font-size:.72rem;"><?= $panel['subtitle'] ?></p>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="height:260px;position:relative;"><canvas id="<?= $panel['canvas'] ?>"></canvas></div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr>
                            <th>College</th>
                            <th>Course</th>
                            <th class="text-end"><?= $panel['title'] === 'Check-ins' ? 'Visitors' : 'Duration (min)' ?></th>
                            <th class="text-end">Last Visit</th>
                        </tr></thead>
                        <tbody>
                        <?php if ($panel['rows']): foreach ($panel['rows'] as $row): ?>
                        <tr>
                            <td class="text-muted"><?= escHtml($row['college']) ?></td>
                            <td class="fw-semibold"><?= escHtml($row['course']) ?></td>
                            <td class="text-end"><?= round($row[$panel['valueKey']] ?? 0) ?></td>
                            <td class="text-end text-muted">
                                <?= !empty($row['last_checkin']) ? date('M j', strtotime($row['last_checkin'])) : '—' ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if ($panel['title'] === 'Duration'): ?>
                    <div class="text-end mt-2">
                        <button class="btn btn-sm btn-outline-secondary view-all-btn"
                                data-tab="courses" style="font-size:.75rem;">View All Courses</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php return ob_get_clean();
}

function renderDemographicsTab(array $countBySex, int $totalVisitors): string
{
    $sexBreakdown = [
        'Male'    => ['icon' => 'bi-gender-male',    'bg' => 'info',      'count' => $countBySex['Male']    ?? 0],
        'Female'  => ['icon' => 'bi-gender-female',  'bg' => 'danger',    'count' => $countBySex['Female']  ?? 0],
        'Unknown' => ['icon' => 'bi-question-circle', 'bg' => 'secondary', 'count' => $countBySex['Unknown'] ?? 0],
    ];
    foreach ($sexBreakdown as &$data) {
        $data['pct'] = $totalVisitors ? round($data['count'] / $totalVisitors * 100, 1) : 0;
    }

    ob_start(); ?>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Sex Distribution</p>
                    <p class="text-muted mb-0" style="font-size:.72rem;">Visitor breakdown by sex</p>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center px-3">
                    <div style="height:300px;width:100%;position:relative;"><canvas id="chartSexDonut"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-primary-subtle d-flex align-items-center justify-content-center"
                                 style="width:46px;height:46px;flex-shrink:0;">
                                <i class="bi bi-people-fill text-primary"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">Total Visitors</p>
                                <h3 class="fw-bold mb-0"><?= number_format($totalVisitors) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <?php foreach ($sexBreakdown as $label => $data): if ($data['count'] > 0 || $label !== 'Unknown'): ?>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-<?= $data['bg'] ?>-subtle d-flex align-items-center justify-content-center"
                                 style="width:46px;height:46px;flex-shrink:0;">
                                <i class="bi <?= $data['icon'] ?> text-<?= $data['bg'] ?>"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0"><?= $label ?></p>
                                <h4 class="fw-bold mb-0"><?= number_format($data['count']) ?></h4>
                                <?php if ($label !== 'Unknown'): ?>
                                <small class="text-muted"><?= $data['pct'] ?>% of total</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; endforeach; ?>
            </div>
        </div>
        <div class="col-12 text-end">
            <button class="btn btn-sm btn-outline-secondary view-all-btn"
                    data-tab="demographics" style="font-size:.75rem;">View All Logs</button>
        </div>
    </div>
    <?php return ob_get_clean();
}

// ── DISPATCH ──────────────────────────────────────────────────────────────────

$request = trim($_POST['action'] ?? 'tab');

switch ($request) {
    case 'tab':     HandleTabRequest(); break;
    case 'viewAll': HandleViewAll();    break;
    default: sendResponse(['status' => 'error', 'message' => "Unknown request: '{$request}'."]);
}