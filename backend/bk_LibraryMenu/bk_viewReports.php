<?php
// Paginated modal tables for all analytics tabs.
// Each handler directly fetches logs, paginates, and returns modal HTML.
include '../../db/dbconnection.php';
include 'bk_libReports.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']); exit;
}

const ROWS_PER_PAGE     = 10;
const MODAL_TABLE_CLASS = 'table table-sm table-striped table-hover align-middle mb-0';

// -------------------------------------------------------------------
//  Modal table and pagination renderers
// -------------------------------------------------------------------
function renderModalTable(string $tab, array $rows): string
{
    $badge = fn($text) => getTypeBadge($text);  // use shared function

    $configs = [
        'logs' => [
            'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Section', 'Sex', 'Check-in', 'Check-out', 'Agency / Organization', 'Duration (min)'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">' . htmlspecialchars((string) ($row['display_id'] ?? $row['id_number'] ?? ''),  ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-muted small">' . htmlspecialchars((string) ($row['name'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-muted small">' . htmlspecialchars((string) ($row['college'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-muted small">' . htmlspecialchars((string) ($row['course'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td>' . $badge($row['classification'] ?: '—') . '</td>' .
                '<td class="text-muted small">' . htmlspecialchars((string) ($row['library'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-muted small">' . htmlspecialchars((string) ($row['sex'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-muted small">' . ($row['checkin_time']  ? date('M j, Y g:i A', strtotime($row['checkin_time']))  : '—') . '</td>' .
                '<td class="text-muted small">' . ($row['checkout_time'] ? date('M j, Y g:i A', strtotime($row['checkout_time'])) : '—') . '</td>' .
                '<td class="text-muted small">' . htmlspecialchars((string) ($row['agency_organization'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-end pe-3">' . (isset($row['duration']) ? (int) round($row['duration']) : '—') . '</td>',
        ],
        'users' => [
            'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">' . htmlspecialchars((string) ($row['display_label'] ?? ''),  ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-muted small">' . htmlspecialchars((string) ($row['name'] ?? ''),  ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-muted small">' . htmlspecialchars((string) ($row['college'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-muted small">' . htmlspecialchars((string) ($row['course'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td>' . $badge($row['type']) . '</td>' .
                '<td class="text-muted small">' . htmlspecialchars((string) ($row['library'] ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-end fw-semibold text-primary">' . (int) $row['checkins'] . '</td>' .
                '<td class="text-end">' . (int) round($row['duration']) . '</td>' .
                '<td class="text-muted small pe-3">' . date('M j, Y g:i A', strtotime($row['last_checkin'])) . '</td>',
        ],
        'colleges' => [
            'headers' => ['College', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">' . htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8')  . '</td>' .
                '<td class="text-end">' . (int) $row['visitors'] . '</td>' .
                '<td class="text-end">' . (int) round($row['duration']) . '</td>' .
                '<td class="text-muted small pe-3">' . date('M j, Y g:i A', strtotime($row['last_checkin'])) . '</td>',
        ],
        'courses' => [
            'headers' => ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 text-muted small">' . htmlspecialchars((string) ($row['college'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="fw-semibold">' . htmlspecialchars((string) ($row['course']  ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-end">' . (int) $row['visitors'] . '</td>' .
                '<td class="text-end">' . (int) round($row['duration']) . '</td>' .
                '<td class="text-muted small pe-3">'  . date('M j, Y g:i A', strtotime($row['last_checkin'])) . '</td>',
        ],
        'demographics' => [
            'headers' => ['ID Number', 'Sex', 'Check-in', 'Check-out', 'Duration (min)'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">' . htmlspecialchars((string) ($row['display_label'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td>' . htmlspecialchars((string) ($row['sex'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
                '<td class="text-muted small">' . date('M j, Y g:i A', strtotime($row['checkin_time'])) . '</td>' .
                '<td class="text-muted small">' . ($row['checkout_time'] ? date('M j, Y g:i A', strtotime($row['checkout_time'])) : '—') . '</td>' .
                '<td class="text-end pe-3">' . (int) round($row['duration']) . '</td>',
        ],
    ];

    if (!isset($configs[$tab])) return '';

    $config      = $configs[$tab];
    $headerCells = implode('', array_map(fn($header) => "<th class=\"small fw-semibold\">{$header}</th>", $config['headers']));

    // Build rows with alternating classes
    $bodyRows = '';
    foreach ($rows as $idx => $row) {
        $rowClass = $idx % 2 === 0 ? 'table-success' : '';
        $bodyRows .= '<tr class="' . $rowClass . '">' . $config['rowFn']($row) . '</tr>';
    }

    return '<div class="table-responsive">'
         . '<table class="table table-sm table-striped table-hover align-middle mb-0">'
         . "<thead class=\"table-dark\"><tr>{$headerCells}</tr></thead>"
         . "<tbody class=\"small\">{$bodyRows}</tbody>"
         . '</table></div>';
}

function renderModalPagination(int $totalPages, int $currentPage, int $total, int $perPage): string
{
    if ($totalPages <= 1) return '';

    $isFirst = $currentPage === 1;
    $isLast = $currentPage === $totalPages;
    $windowSize  = 5;
    $windowStart = max(1, min($currentPage - intdiv($windowSize, 2), $totalPages - $windowSize + 1));
    $windowEnd = min($totalPages, $windowStart + $windowSize - 1);

    $pageItem = fn(string $label, int $targetPage, string $extraClass = '') =>
        "<li class=\"page-item {$extraClass}\"><a class=\"page-link\" href=\"#\" data-page=\"{$targetPage}\">{$label}</a></li>";

    $items  = $pageItem('«', 1, $isFirst ? 'disabled' : '')
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

// -------------------------------------------------------------------
//  Handlers (each does its own pagination)
// -------------------------------------------------------------------
function ViewAllLogs(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);
    $page = max(1, (int) trim($_POST['page'] ?? '1'));

    // Build rows for the page
    $rows = array_map(function($log) {
        $checkout = $log['checkout_time'] ?? null;
        $cStart = $checkout ? strtotime($log['checkin_time']) : 0;
        $cEnd = $checkout ? strtotime($checkout) : 0;
        return [
            'id_number' => $log['id_number'] ?? '',
            'name' => $log['name'] ?? '',
            'college' => $log['college'] ?? '',
            'course' => $log['course'] ?? '',
            'classification' => $log['classification'] ?? '',
            'library' => $log['library_section_name'] ?? '',
            'sex' => $log['sex'] ?? '',
            'checkin_time' => $log['checkin_time'] ?? '',
            'checkout_time' => $checkout,
            'agency_organization' => $log['agency_organization'] ?? '',
            'duration' => ($checkout && $cStart && $cEnd) ? ($cEnd - $cStart) / 60 : 0.0,
        ];
    }, $logs);

    $total = count($rows);
    $totalPages = $total > 0 ? (int) ceil($total / ROWS_PER_PAGE) : 1;
    $page = min($page, $totalPages);
    $offset = ($page - 1) * ROWS_PER_PAGE;
    $pageRows = array_slice($rows, $offset, ROWS_PER_PAGE);

    echo json_encode([
        'status' => 'success',
        'tableHtml' => renderModalTable('logs', $pageRows),
        'pagination' => renderModalPagination($totalPages, $page, $total, ROWS_PER_PAGE),
        'total' => $total,
        'totalPages' => $totalPages,
        'page' => $page,
    ]); exit;
}

function ViewAllUsers(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);
    $page = max(1, (int) trim($_POST['page'] ?? '1'));

    // Aggregate per user
    $users = [];
    foreach ($logs as $log) {
        $uid = $log['id_number'];
        if (!isset($users[$uid])) {
            $users[$uid] = [
                'display_label' => ($uid === '0') ? ($log['name'] ?? 'Guest') : $uid,
                'name' => $log['name'] ?? '',
                'college' => $log['college'] ?? '',
                'course' => $log['course'] ?? '',
                'type' => $log['classification'],
                'library' => $log['library_section_name'] ?? '',
                'checkins' => 0,
                'duration' => 0.0,
                'last_checkin' => $log['checkin_time'],
            ];
        }
        $users[$uid]['checkins']++;
        if ($log['checkout_time']) {
            $s = strtotime($log['checkin_time']);
            $e = strtotime($log['checkout_time']);
            $users[$uid]['duration'] += ($s && $e) ? ($e - $s) / 60 : 0.0;
        }
        if ($log['checkin_time'] > $users[$uid]['last_checkin']) {
            $users[$uid]['last_checkin'] = $log['checkin_time'];
        }
    }
    uasort($users, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    $users = array_values($users);

    $total = count($users);
    $totalPages = $total > 0 ? (int) ceil($total / ROWS_PER_PAGE) : 1;
    $page = min($page, $totalPages);
    $offset = ($page - 1) * ROWS_PER_PAGE;
    $pageUsers = array_slice($users, $offset, ROWS_PER_PAGE);

    echo json_encode([
        'status' => 'success',
        'tableHtml' => renderModalTable('users', $pageUsers),
        'pagination' => renderModalPagination($totalPages, $page, $total, ROWS_PER_PAGE),
        'total' => $total,
        'totalPages' => $totalPages,
        'page' => $page,
    ]); exit;
}

function ViewAllColleges(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);
    $page = max(1, (int) trim($_POST['page'] ?? '1'));

    // Student logs only
    $studentLogs = array_filter($logs, fn($log) => strcasecmp($log['classification'] ?? '', 'student') === 0);
    $colleges = [];
    foreach ($studentLogs as $log) {
        $college = $log['college'] ?: 'Unknown';
        if (!isset($colleges[$college])) {
            $colleges[$college] = [
                'name' => $college,
                'unique_visitors' => [],
                'duration' => 0.0,
                'last_checkin' => $log['checkin_time'],
            ];
        }
        $colleges[$college]['unique_visitors'][$log['id_number']] = true;
        if ($log['checkout_time']) {
            $s = strtotime($log['checkin_time']);
            $e = strtotime($log['checkout_time']);
            $colleges[$college]['duration'] += ($s && $e) ? ($e - $s) / 60 : 0.0;
        }
        if ($log['checkin_time'] > $colleges[$college]['last_checkin']) {
            $colleges[$college]['last_checkin'] = $log['checkin_time'];
        }
    }

    $rows = array_map(fn($data) => [
        'name' => $data['name'],
        'visitors' => count($data['unique_visitors']),
        'duration' => $data['duration'],
        'last_checkin' => $data['last_checkin'],
    ], array_values($colleges));

    usort($rows, fn($a, $b) => $b['visitors'] <=> $a['visitors']);

    $total = count($rows);
    $totalPages = $total > 0 ? (int) ceil($total / ROWS_PER_PAGE) : 1;
    $page = min($page, $totalPages);
    $offset = ($page - 1) * ROWS_PER_PAGE;
    $pageRows = array_slice($rows, $offset, ROWS_PER_PAGE);

    echo json_encode([
        'status' => 'success',
        'tableHtml' => renderModalTable('colleges', $pageRows),
        'pagination' => renderModalPagination($totalPages, $page, $total, ROWS_PER_PAGE),
        'total' => $total,
        'totalPages' => $totalPages,
        'page' => $page,
    ]); exit;
}

function ViewAllCourses(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);
    $page = max(1, (int) trim($_POST['page'] ?? '1'));

    $studentLogs = array_filter($logs, fn($log) => strcasecmp($log['classification'] ?? '', 'student') === 0);
    $courses = [];
    foreach ($studentLogs as $log) {
        $college = $log['college'] ?: 'Unknown';
        $course = $log['course'] ?: 'Unknown';
        $key = "{$college}|{$course}";
        if (!isset($courses[$key])) {
            $courses[$key] = [
                'college' => $college,
                'course' => $course,
                'unique_visitors' => [],
                'duration' => 0.0,
                'last_checkin' => $log['checkin_time'],
            ];
        }
        $courses[$key]['unique_visitors'][$log['id_number']] = true;
        if ($log['checkout_time']) {
            $s = strtotime($log['checkin_time']);
            $e = strtotime($log['checkout_time']);
            $courses[$key]['duration'] += ($s && $e) ? ($e - $s) / 60 : 0.0;
        }
        if ($log['checkin_time'] > $courses[$key]['last_checkin']) {
            $courses[$key]['last_checkin'] = $log['checkin_time'];
        }
    }

    $rows = array_map(fn($data) => [
        'college' => $data['college'],
        'course' => $data['course'],
        'visitors' => count($data['unique_visitors']),
        'duration' => $data['duration'],
        'last_checkin' => $data['last_checkin'],
    ], array_values($courses));

    usort($rows, fn($a, $b) => $b['visitors'] <=> $a['visitors']);

    $total = count($rows);
    $totalPages = $total > 0 ? (int) ceil($total / ROWS_PER_PAGE) : 1;
    $page = min($page, $totalPages);
    $offset = ($page - 1) * ROWS_PER_PAGE;
    $pageRows = array_slice($rows, $offset, ROWS_PER_PAGE);

    echo json_encode([
        'status' => 'success',
        'tableHtml' => renderModalTable('courses', $pageRows),
        'pagination' => renderModalPagination($totalPages, $page, $total, ROWS_PER_PAGE),
        'total' => $total,
        'totalPages' => $totalPages,
        'page' => $page,
    ]); exit;
}

function ViewAllDemographics(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);
    $page = max(1, (int) trim($_POST['page'] ?? '1'));

    $rows = array_map(function($log) {
        $idNum = $log['id_number'] ?? '';
        $checkout = $log['checkout_time'] ?? null;
        $cStart = $checkout ? strtotime($log['checkin_time']) : 0;
        $cEnd = $checkout ? strtotime($checkout) : 0;
        return [
            'display_label' => ($idNum === '' || $idNum === '0') ? ($log['name'] ?? 'Guest') : $idNum,
            'sex' => $log['sex'],
            'checkin_time' => $log['checkin_time'],
            'checkout_time' => $checkout,
            'duration' => ($checkout && $cStart && $cEnd) ? ($cEnd - $cStart) / 60 : 0.0,
        ];
    }, $logs);

    $total = count($rows);
    $totalPages = $total > 0 ? (int) ceil($total / ROWS_PER_PAGE) : 1;
    $page = min($page, $totalPages);
    $offset = ($page - 1) * ROWS_PER_PAGE;
    $pageRows = array_slice($rows, $offset, ROWS_PER_PAGE);

    echo json_encode([
        'status' => 'success',
        'tableHtml' => renderModalTable('demographics', $pageRows),
        'pagination' => renderModalPagination($totalPages, $page, $total, ROWS_PER_PAGE),
        'total' => $total,
        'totalPages' => $totalPages,
        'page' => $page,
    ]); exit;
}

// -------------------------------------------------------------------
//  Dispatch
// -------------------------------------------------------------------
switch (trim($_POST['request'] ?? '')) {
    case 'viewAllLogs':         ViewAllLogs();         break;
    case 'viewAllUsers':        ViewAllUsers();        break;
    case 'viewAllColleges':     ViewAllColleges();     break;
    case 'viewAllCourses':      ViewAllCourses();      break;
    case 'viewAllDemographics': ViewAllDemographics(); break;
    default: echo json_encode(['status' => 'error', 'message' => "Unknown request: '" . trim($_POST['request'] ?? '') . "'."]);
}