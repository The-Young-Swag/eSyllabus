<?php
// Paginated modal tables for all analytics tabs.
// Architecture: aggregators → renderers → core paginator → handlers → dispatch.
include '../../db/dbconnection.php';
include 'bk_libReports.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['status' => 'error', 'message' => 'Invalid request method.']);
}

const ROWS_PER_PAGE     = 10;
const MODAL_TABLE_CLASS = 'table table-sm table-striped table-hover align-middle mb-0';

// ── AGGREGATORS ───────────────────────────────────────────────────────────────
// Each function aggregates raw logs into a pageable result: { rows[], total }.

function viewLogsPage(array $logs, int $offset, int $limit): array {
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

function viewUsersPage(array $logs, int $offset, int $limit): array {
    $users = [];
    foreach ($logs as $log) {
        $userId = $log['id_number'];
        if (!isset($users[$userId])) {
            $users[$userId] = [
                'display_label' => getUserDisplayLabel($log),
                'name'          => $log['name']                 ?? '',
                'college'       => $log['college']              ?? '',
                'course'        => $log['course']               ?? '',
                'type'          => $log['classification'],
                'library'       => $log['library_section_name'] ?? '',
                'checkins'      => 0,
                'duration'      => 0.0,
                'last_checkin'  => $log['checkin_time'],
            ];
        }
        $users[$userId]['checkins']++;
        $users[$userId]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $users[$userId]['last_checkin']) {
            $users[$userId]['last_checkin'] = $log['checkin_time'];
        }
    }
    uasort($users, fn($alpha, $bravo) => $bravo['checkins'] <=> $alpha['checkins']);

    return ['rows' => array_values(array_slice($users, $offset, $limit, true)), 'total' => count($users)];
}

function viewCollegesPage(array $logs, int $offset, int $limit): array {
    $studentLogs = array_filter($logs, fn($log) => strcasecmp($log['classification'] ?? '', 'student') === 0);
    $colleges    = [];

    foreach ($studentLogs as $log) {
        $college = $log['college'] ?: 'Unknown';
        if (!isset($colleges[$college])) {
            $colleges[$college] = ['name' => $college, 'unique_visitors' => [], 'duration' => 0.0, 'last_checkin' => $log['checkin_time']];
        }
        $colleges[$college]['unique_visitors'][$log['id_number']] = true;
        $colleges[$college]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $colleges[$college]['last_checkin']) {
            $colleges[$college]['last_checkin'] = $log['checkin_time'];
        }
    }

    $rows = array_map(fn($data) => [
        'name'         => $data['name'],
        'checkins'     => count($data['unique_visitors']),
        'duration'     => $data['duration'],
        'last_checkin' => $data['last_checkin'],
    ], array_values($colleges));

    usort($rows, fn($alpha, $bravo) => $bravo['checkins'] <=> $alpha['checkins']);

    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function viewCoursesPage(array $logs, int $offset, int $limit): array {
    $studentLogs = array_filter($logs, fn($log) => strcasecmp($log['classification'] ?? '', 'student') === 0);
    $courses     = [];

    foreach ($studentLogs as $log) {
        $college  = $log['college'] ?: 'Unknown';
        $course   = $log['course']  ?: 'Unknown';
        $key      = "{$college}|{$course}";
        if (!isset($courses[$key])) {
            $courses[$key] = ['college' => $college, 'course' => $course, 'unique_visitors' => [], 'duration' => 0.0, 'last_checkin' => $log['checkin_time']];
        }
        $courses[$key]['unique_visitors'][$log['id_number']] = true;
        $courses[$key]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $courses[$key]['last_checkin']) {
            $courses[$key]['last_checkin'] = $log['checkin_time'];
        }
    }

    $rows = array_map(fn($data) => [
        'college'      => $data['college'],
        'course'       => $data['course'],
        'checkins'     => count($data['unique_visitors']),
        'duration'     => $data['duration'],
        'last_checkin' => $data['last_checkin'],
    ], array_values($courses));

    usort($rows, fn($alpha, $bravo) => $bravo['checkins'] <=> $alpha['checkins']);

    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function viewDemographicsPage(array $logs, int $offset, int $limit): array {
    $rows = array_map(fn($log) => [
        'display_label' => getUserDisplayLabel($log),
        'sex'           => $log['sex'],
        'checkin_time'  => $log['checkin_time'],
        'checkout_time' => $log['checkout_time'],
        'duration'      => calcDurationMinutes($log['checkin_time'], $log['checkout_time']),
    ], $logs);

    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

// ── RENDERERS ─────────────────────────────────────────────────────────────────

function renderModalTable(string $tab, array $rows): string {
    $badge = fn($text) =>
        '<span class="badge bg-secondary-subtle text-secondary rounded-pill small">' . escHtml($text) . '</span>';

    $configs = [
        'logs' => [
            'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Section', 'Sex', 'Check-in', 'Check-out', 'Agency / Organization', 'Duration (min)'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">'    . escHtml($row['id_number'])                                                . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['name']                ?: '—')                              . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['college']             ?: '—')                              . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['course']              ?: '—')                              . '</td>' .
                '<td>'                              . $badge($row['classification']        ?: '—')                             . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['library']             ?: '—')                              . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['sex']                 ?: '—')                              . '</td>' .
                '<td class="text-muted small">'     . ($row['checkin_time']  ? formatDateTime($row['checkin_time'])  : '—')    . '</td>' .
                '<td class="text-muted small">'     . ($row['checkout_time'] ? formatDateTime($row['checkout_time']) : '—')    . '</td>' .
                '<td class="text-muted small">'     . escHtml($row['agency_organization'] ?: '—')                              . '</td>' .
                '<td class="text-end pe-3">'        . (isset($row['duration']) ? (int) round($row['duration']) : '—')          . '</td>',
        ],
        'users' => [
            'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">'                 . escHtml($row['display_label'])              . '</td>' .
                '<td class="text-muted small">'                  . escHtml($row['name'])                       . '</td>' .
                '<td class="text-muted small">'                  . escHtml($row['college'] ?: '—')             . '</td>' .
                '<td class="text-muted small">'                  . escHtml($row['course']  ?: '—')             . '</td>' .
                '<td>'                                           . $badge($row['type'])                         . '</td>' .
                '<td class="text-muted small">'                  . escHtml($row['library'] ?? '—')             . '</td>' .
                '<td class="text-end fw-semibold text-primary">' . (int) $row['checkins']                      . '</td>' .
                '<td class="text-end">'                          . (int) round($row['duration'])                . '</td>' .
                '<td class="text-muted small pe-3">'             . formatDateTime($row['last_checkin'])         . '</td>',
        ],
        'colleges' => [
            'headers' => ['College', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">'     . escHtml($row['name'])                    . '</td>' .
                '<td class="text-end">'              . (int) $row['checkins']                   . '</td>' .
                '<td class="text-end">'              . (int) round($row['duration'])             . '</td>' .
                '<td class="text-muted small pe-3">' . formatDateTime($row['last_checkin'])      . '</td>',
        ],
        'courses' => [
            'headers' => ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 text-muted small">' . escHtml($row['college'])                . '</td>' .
                '<td class="fw-semibold">'            . escHtml($row['course'])                 . '</td>' .
                '<td class="text-end">'               . (int) $row['checkins']                  . '</td>' .
                '<td class="text-end">'               . (int) round($row['duration'])            . '</td>' .
                '<td class="text-muted small pe-3">'  . formatDateTime($row['last_checkin'])     . '</td>',
        ],
        'demographics' => [
            'headers' => ['ID Number', 'Sex', 'Check-in', 'Check-out', 'Duration (min)'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">' . escHtml($row['display_label'])                                                    . '</td>' .
                '<td>'                           . escHtml($row['sex'])                                                              . '</td>' .
                '<td class="text-muted small">'  . formatDateTime($row['checkin_time'])                                              . '</td>' .
                '<td class="text-muted small">'  . ($row['checkout_time'] ? formatDateTime($row['checkout_time']) : '—')             . '</td>' .
                '<td class="text-end pe-3">'     . (int) round($row['duration'])                                                     . '</td>',
        ],
    ];

    if (!isset($configs[$tab])) return '';

    $config      = $configs[$tab];
    $headerCells = implode('', array_map(fn($header) => "<th class=\"small fw-semibold\">{$header}</th>", $config['headers']));
    $bodyRows    = implode('', array_map(fn($row) => '<tr>' . $config['rowFn']($row) . '</tr>', $rows));

    return '<div class="table-responsive">'
         . '<table class="' . MODAL_TABLE_CLASS . '">'
         . "<thead class=\"table-dark\"><tr>{$headerCells}</tr></thead>"
         . "<tbody class=\"small\">{$bodyRows}</tbody>"
         . '</table></div>';
}

function renderModalPagination(int $totalPages, int $currentPage, int $total, int $perPage): string {
    if ($totalPages <= 1) return '';

    $isFirst     = $currentPage === 1;
    $isLast      = $currentPage === $totalPages;
    $windowSize  = 5;
    $windowStart = max(1, min($currentPage - intdiv($windowSize, 2), $totalPages - $windowSize + 1));
    $windowEnd   = min($totalPages, $windowStart + $windowSize - 1);

    $pageItem = fn(string $label, int $targetPage, string $extraClass = '') =>
        "<li class=\"page-item {$extraClass}\"><a class=\"page-link\" href=\"#\" data-page=\"{$targetPage}\">{$label}</a></li>";

    $items  = $pageItem('«', 1,                $isFirst ? 'disabled' : '')
            . $pageItem('‹', $currentPage - 1, $isFirst ? 'disabled' : '');
    for ($pageNum = $windowStart; $pageNum <= $windowEnd; $pageNum++) {
        $items .= $pageItem((string) $pageNum, $pageNum, $pageNum === $currentPage ? 'active' : '');
    }
    $items .= $pageItem('›', $currentPage + 1, $isLast ? 'disabled' : '')
            . $pageItem('»', $totalPages,       $isLast ? 'disabled' : '');

    $from = ($currentPage - 1) * $perPage + 1;
    $to   = min($currentPage * $perPage, $total);

    return "<small class=\"text-muted\">Showing {$from}–{$to} of {$total} records</small>"
         . "<nav class=\"mt-1\"><ul class=\"pagination pagination-sm mb-0 flex-wrap justify-content-center\">{$items}</ul></nav>";
}

// ── CORE ──────────────────────────────────────────────────────────────────────

function paginateAndRespond(string $tab, array $logs): void {
    $page   = max(1, (int) trim($_POST['page'] ?? '1'));
    $offset = ($page - 1) * ROWS_PER_PAGE;

    $pageData = match ($tab) {
        'logs'         => viewLogsPage($logs, $offset, ROWS_PER_PAGE),
        'users'        => viewUsersPage($logs, $offset, ROWS_PER_PAGE),
        'colleges'     => viewCollegesPage($logs, $offset, ROWS_PER_PAGE),
        'courses'      => viewCoursesPage($logs, $offset, ROWS_PER_PAGE),
        'demographics' => viewDemographicsPage($logs, $offset, ROWS_PER_PAGE),
        default        => ['rows' => [], 'total' => 0],
    };

    $total      = $pageData['total'];
    $totalPages = $total > 0 ? (int) ceil($total / ROWS_PER_PAGE) : 1;
    $page       = min($page, $totalPages);

    sendResponse([
        'status'     => 'success',
        'tableHtml'  => renderModalTable($tab, $pageData['rows']),
        'pagination' => renderModalPagination($totalPages, $page, $total, ROWS_PER_PAGE),
        'total'      => $total,
        'totalPages' => $totalPages,
        'page'       => $page,
    ]);
}

// ── HANDLERS ──────────────────────────────────────────────────────────────────

function ViewAllLogs(): void {
    [$where, $params] = buildWhereClause($_POST);
    paginateAndRespond('logs', fetchVisitLogs($where, $params));
}

function ViewAllUsers(): void {
    [$where, $params] = buildWhereClause($_POST);
    paginateAndRespond('users', fetchVisitLogs($where, $params));
}

function ViewAllColleges(): void {
    [$where, $params] = buildWhereClause($_POST);
    paginateAndRespond('colleges', fetchVisitLogs($where, $params));
}

function ViewAllCourses(): void {
    [$where, $params] = buildWhereClause($_POST);
    paginateAndRespond('courses', fetchVisitLogs($where, $params));
}

function ViewAllDemographics(): void {
    [$where, $params] = buildWhereClause($_POST);
    paginateAndRespond('demographics', fetchVisitLogs($where, $params));
}

// ── DISPATCH ──────────────────────────────────────────────────────────────────

switch (trim($_POST['request'] ?? '')) {
    case 'viewAllLogs':         ViewAllLogs();         break;
    case 'viewAllUsers':        ViewAllUsers();         break;
    case 'viewAllColleges':     ViewAllColleges();     break;
    case 'viewAllCourses':      ViewAllCourses();      break;
    case 'viewAllDemographics': ViewAllDemographics(); break;
    default: sendResponse(['status' => 'error', 'message' => "Unknown request: '" . trim($_POST['request'] ?? '') . "'."]);
}