<?php
/**
 * Paginated modal tables for all analytics tabs.
 *
 * Each handler fetches logs, delegates aggregation to bk_libReports.php,
 * slices to the requested page, and returns modal HTML + pagination metadata.
 */
include '../../db/dbconnection.php';
include 'bk_libReports.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']); exit;
}

const ROWS_PER_PAGE = 10;

// ---------------------------------------------------------------------------
//  Modal table renderer
// ---------------------------------------------------------------------------

function renderModalTable(string $tab, array $rows): string
{
    $configs = [
        'logs' => [
            'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Section', 'Sex', 'Check-in', 'Check-out', 'Agency / Organization', 'Duration (min)'],
            'rowFn'   => fn($row) =>
                td('ps-3 fw-semibold', $row['id_number'] ?? '') .
                td('text-muted small', $row['name']               ?: '—') .
                td('text-muted small', $row['college']            ?: '—') .
                td('text-muted small', $row['course']             ?: '—') .
                '<td>' . getTypeBadge($row['classification'] ?: '—') . '</td>' .
                td('text-muted small', $row['library']            ?: '—') .
                td('text-muted small', $row['sex']                ?: '—') .
                td('text-muted small', $row['checkin_time']  ? date('M j, Y g:i A', strtotime($row['checkin_time']))  : '—') .
                td('text-muted small', $row['checkout_time'] ? date('M j, Y g:i A', strtotime($row['checkout_time'])) : '—') .
                td('text-muted small', $row['agency_organization'] ?: '—') .
                '<td class="text-end pe-3">' . (int) round($row['duration']) . '</td>',
        ],
        'users' => [
            'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                td('ps-3 fw-semibold', $row['display_label'] ?? '') .
                td('text-muted small', $row['name']    ?? '') .
                td('text-muted small', $row['college'] ?: '—') .
                td('text-muted small', $row['course']  ?: '—') .
                '<td>' . getTypeBadge($row['classification']) . '</td>' .
                td('text-muted small', $row['library'] ?? '—') .
                '<td class="text-end fw-semibold text-primary">' . (int) $row['checkins'] . '</td>' .
                '<td class="text-end">' . (int) round($row['duration']) . '</td>' .
                td('text-muted small pe-3', date('M j, Y g:i A', strtotime($row['last_checkin']))),
        ],
        'colleges' => [
            'headers' => ['College', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                td('ps-3 fw-semibold', $row['name']) .
                '<td class="text-end">' . (int) $row['visitors'] . '</td>' .
                '<td class="text-end">' . (int) round($row['duration']) . '</td>' .
                td('text-muted small pe-3', date('M j, Y g:i A', strtotime($row['last_checkin']))),
        ],
        'courses' => [
            'headers' => ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                td('ps-3 text-muted small', $row['college']) .
                td('fw-semibold', $row['course']) .
                '<td class="text-end">' . (int) $row['visitors'] . '</td>' .
                '<td class="text-end">' . (int) round($row['duration']) . '</td>' .
                td('text-muted small pe-3', date('M j, Y g:i A', strtotime($row['last_checkin']))),
        ],
        'demographics' => [
            'headers' => ['ID Number', 'Sex', 'Check-in', 'Check-out', 'Duration (min)'],
            'rowFn'   => fn($row) =>
                td('ps-3 fw-semibold', $row['display_label'] ?? '') .
                td('', $row['sex'] ?? '') .
                td('text-muted small', date('M j, Y g:i A', strtotime($row['checkin_time']))) .
                td('text-muted small', $row['checkout_time'] ? date('M j, Y g:i A', strtotime($row['checkout_time'])) : '—') .
                '<td class="text-end pe-3">' . (int) round($row['duration']) . '</td>',
        ],
    ];

    if (!isset($configs[$tab])) return '';
    $config = $configs[$tab];

    $headerCells = implode('', array_map(
        fn($h) => '<th class="small fw-semibold">' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</th>',
        $config['headers']
    ));

    $bodyRows = '';
    foreach ($rows as $i => $row) {
        $bodyRows .= '<tr class="' . ($i % 2 === 0 ? 'table-success' : '') . '">' . $config['rowFn']($row) . '</tr>';
    }

    return '<div class="table-responsive">'
         . '<table class="table table-sm table-striped table-hover align-middle mb-0">'
         . '<thead class="table-dark"><tr>' . $headerCells . '</tr></thead>'
         . '<tbody class="small">' . $bodyRows . '</tbody>'
         . '</table></div>';
}

/** Renders a safe, escaped <td> cell. */
function td(string $classes, string $value): string
{
    $class = $classes ? ' class="' . $classes . '"' : '';
    return '<td' . $class . '>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</td>';
}

// ---------------------------------------------------------------------------
//  Pagination renderer
// ---------------------------------------------------------------------------

function renderModalPagination(int $totalPages, int $currentPage, int $total, int $perPage): string
{
    if ($totalPages <= 1) return '';

    $isFirst     = $currentPage === 1;
    $isLast      = $currentPage === $totalPages;
    $windowSize  = 5;
    $windowStart = max(1, min($currentPage - intdiv($windowSize, 2), $totalPages - $windowSize + 1));
    $windowEnd   = min($totalPages, $windowStart + $windowSize - 1);

    $item = fn(string $label, int $page, bool $disabled, bool $active) =>
        '<li class="page-item ' . ($disabled ? 'disabled' : '') . ' ' . ($active ? 'active' : '') . '">'
        . '<a class="page-link" href="#" data-page="' . $page . '">' . $label . '</a></li>';

    $items = $item('«', 1, $isFirst, false) . $item('‹', $currentPage - 1, $isFirst, false);
    for ($p = $windowStart; $p <= $windowEnd; $p++) {
        $items .= $item((string) $p, $p, false, $p === $currentPage);
    }
    $items .= $item('›', $currentPage + 1, $isLast, false) . $item('»', $totalPages, $isLast, false);

    $from = ($currentPage - 1) * $perPage + 1;
    $to   = min($currentPage * $perPage, $total);

    return '<small class="text-muted">Showing ' . $from . '–' . $to . ' of ' . $total . ' records</small>'
         . '<nav class="mt-1"><ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center">' . $items . '</ul></nav>';
}

/** Sends a paginated JSON response; shared by all ViewAll* handlers. */
function respond(string $tab, array $allRows, int $requestedPage): void
{
    $paged = paginate($allRows, $requestedPage, ROWS_PER_PAGE);

    echo json_encode([
        'status'     => 'success',
        'tableHtml'  => renderModalTable($tab, $paged['items']),
        'pagination' => renderModalPagination($paged['totalPages'], $paged['page'], $paged['total'], ROWS_PER_PAGE),
        'total'      => $paged['total'],
        'totalPages' => $paged['totalPages'],
        'page'       => $paged['page'],
    ]);
    exit;
}

// ---------------------------------------------------------------------------
//  Handlers
// ---------------------------------------------------------------------------

function ViewAllLogs(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);

    $rows = array_map(function ($log) {
        $checkout = $log['checkout_time'] ?? null;
        return [
            'id_number'           => $log['id_number'] ?? '',
            'name'                => $log['name'] ?? '',
            'college'             => $log['college'] ?? '',
            'course'              => $log['course'] ?? '',
            'classification'      => $log['classification'] ?? '',
            'library'             => $log['library_section_name'] ?? '',
            'sex'                 => $log['sex'] ?? '',
            'checkin_time'        => $log['checkin_time'] ?? '',
            'checkout_time'       => $checkout,
            'agency_organization' => $log['agency_organization'] ?? '',
            'duration'            => minutesBetween($log['checkin_time'] ?? null, $checkout),
        ];
    }, $logs);

    respond('logs', $rows, (int) ($_POST['page'] ?? 1));
}

function ViewAllUsers(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $users = aggregateUsers(fetchVisitLogs($where, $params));
    uasort($users, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    respond('users', array_values($users), (int) ($_POST['page'] ?? 1));
}

function ViewAllColleges(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $colleges = aggregateColleges(fetchVisitLogs($where, $params));
    uasort($colleges, fn($a, $b) => $b['visitors'] <=> $a['visitors']);

    $rows = array_map(fn($name, $rec) => [
        'name'         => $name,
        'visitors'     => $rec['visitors'],
        'duration'     => $rec['duration'],
        'last_checkin' => $rec['last_checkin'],
    ], array_keys($colleges), array_values($colleges));

    respond('colleges', $rows, (int) ($_POST['page'] ?? 1));
}

function ViewAllCourses(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $courses = aggregateCourses(fetchVisitLogs($where, $params));
    uasort($courses, fn($a, $b) => $b['visitors'] <=> $a['visitors']);
    respond('courses', array_values($courses), (int) ($_POST['page'] ?? 1));
}

function ViewAllDemographics(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);

    $rows = array_map(function ($log) {
        $uid      = $log['id_number'] ?? '';
        $checkout = $log['checkout_time'] ?? null;
        return [
            'display_label' => ($uid === '' || $uid === '0') ? ($log['name'] ?? 'Guest') : $uid,
            'sex'           => $log['sex'] ?? '',
            'checkin_time'  => $log['checkin_time'] ?? '',
            'checkout_time' => $checkout,
            'duration'      => minutesBetween($log['checkin_time'] ?? null, $checkout),
        ];
    }, $logs);

    respond('demographics', $rows, (int) ($_POST['page'] ?? 1));
}

// ---------------------------------------------------------------------------
//  Dispatch
// ---------------------------------------------------------------------------

switch (trim($_POST['request'] ?? '')) {
    case 'viewAllLogs':         ViewAllLogs();         break;
    case 'viewAllUsers':        ViewAllUsers();        break;
    case 'viewAllColleges':     ViewAllColleges();     break;
    case 'viewAllCourses':      ViewAllCourses();      break;
    case 'viewAllDemographics': ViewAllDemographics(); break;
    default: echo json_encode(['status' => 'error', 'message' => "Unknown request: '" . trim($_POST['request'] ?? '') . "'."]);
}