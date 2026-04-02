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
    $tabConfig = $configs[$tab];

    $headerCells = implode('', array_map(
        fn($header) => '<th class="small fw-semibold">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>',
        $tabConfig['headers']
    ));

    $bodyRows = '';
    foreach ($rows as $rowIndex => $row) {
        $bodyRows .= '<tr class="' . ($rowIndex % 2 === 0 ? 'table-success' : '') . '">' . $tabConfig['rowFn']($row) . '</tr>';
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

    $isFirst      = $currentPage === 1;
    $isLast       = $currentPage === $totalPages;
    $windowSize   = 5;
    $windowStart  = max(1, min($currentPage - intdiv($windowSize, 2), $totalPages - $windowSize + 1));
    $windowEnd    = min($totalPages, $windowStart + $windowSize - 1);

    $buildPageItem = fn(string $label, int $page, bool $disabled, bool $active) =>
        '<li class="page-item ' . ($disabled ? 'disabled' : '') . ' ' . ($active ? 'active' : '') . '">'
        . '<a class="page-link" href="#" data-page="' . $page . '">' . $label . '</a></li>';

    $pageItems = $buildPageItem('«', 1, $isFirst, false) . $buildPageItem('‹', $currentPage - 1, $isFirst, false);
    for ($pageNumber = $windowStart; $pageNumber <= $windowEnd; $pageNumber++) {
        $pageItems .= $buildPageItem((string) $pageNumber, $pageNumber, false, $pageNumber === $currentPage);
    }
    $pageItems .= $buildPageItem('›', $currentPage + 1, $isLast, false) . $buildPageItem('»', $totalPages, $isLast, false);

    $firstRecord = ($currentPage - 1) * $perPage + 1;
    $lastRecord  = min($currentPage * $perPage, $total);

    return '<small class="text-muted">Showing ' . $firstRecord . '–' . $lastRecord . ' of ' . $total . ' records</small>'
         . '<nav class="mt-1"><ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center">' . $pageItems . '</ul></nav>';
}

/** Sends a paginated JSON response; shared by all ViewAll* handlers. */
function respond(string $tab, array $allRows, int $requestedPage): void
{
    $paginatedResult = paginate($allRows, $requestedPage, ROWS_PER_PAGE);

    echo json_encode([
        'status'     => 'success',
        'tableHtml'  => renderModalTable($tab, $paginatedResult['items']),
        'pagination' => renderModalPagination($paginatedResult['totalPages'], $paginatedResult['page'], $paginatedResult['total'], ROWS_PER_PAGE),
        'total'      => $paginatedResult['total'],
        'totalPages' => $paginatedResult['totalPages'],
        'page'       => $paginatedResult['page'],
    ]);
    exit;
}

// ---------------------------------------------------------------------------
//  Handlers
// ---------------------------------------------------------------------------

function ViewAllLogs(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $visitLogs = fetchVisitLogs($whereClause, $queryParams);

    $rows = array_map(function ($logEntry) {
        $checkoutTime = $logEntry['checkout_time'] ?? null;
        return [
            'id_number'           => $logEntry['id_number'] ?? '',
            'name'                => $logEntry['name'] ?? '',
            'college'             => $logEntry['college'] ?? '',
            'course'              => $logEntry['course'] ?? '',
            'classification'      => $logEntry['classification'] ?? '',
            'library'             => $logEntry['library_section_name'] ?? '',
            'sex'                 => $logEntry['sex'] ?? '',
            'checkin_time'        => $logEntry['checkin_time'] ?? '',
            'checkout_time'       => $checkoutTime,
            'agency_organization' => $logEntry['agency_organization'] ?? '',
            'duration'            => minutesBetween($logEntry['checkin_time'] ?? null, $checkoutTime),
        ];
    }, $visitLogs);

    respond('logs', $rows, (int) ($_POST['page'] ?? 1));
}

function ViewAllUsers(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $userStats = aggregateUsers(fetchVisitLogs($whereClause, $queryParams));
    uasort($userStats, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    respond('users', array_values($userStats), (int) ($_POST['page'] ?? 1));
}

function ViewAllColleges(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $collegeStats = aggregateColleges(fetchVisitLogs($whereClause, $queryParams));
    uasort($collegeStats, fn($a, $b) => $b['visitors'] <=> $a['visitors']);

    $rows = array_map(fn($collegeName, $collegeData) => [
        'name'         => $collegeName,
        'visitors'     => $collegeData['visitors'],
        'duration'     => $collegeData['duration'],
        'last_checkin' => $collegeData['last_checkin'],
    ], array_keys($collegeStats), array_values($collegeStats));

    respond('colleges', $rows, (int) ($_POST['page'] ?? 1));
}

function ViewAllCourses(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $courseStats = aggregateCourses(fetchVisitLogs($whereClause, $queryParams));
    uasort($courseStats, fn($a, $b) => $b['visitors'] <=> $a['visitors']);
    respond('courses', array_values($courseStats), (int) ($_POST['page'] ?? 1));
}

function ViewAllDemographics(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $visitLogs = fetchVisitLogs($whereClause, $queryParams);

    $rows = array_map(function ($logEntry) {
        $idNumber     = $logEntry['id_number'] ?? '';
        $checkoutTime = $logEntry['checkout_time'] ?? null;
        return [
            'display_label' => ($idNumber === '' || $idNumber === '0') ? ($logEntry['name'] ?? 'Guest') : $idNumber,
            'sex'           => $logEntry['sex'] ?? '',
            'checkin_time'  => $logEntry['checkin_time'] ?? '',
            'checkout_time' => $checkoutTime,
            'duration'      => minutesBetween($logEntry['checkin_time'] ?? null, $checkoutTime),
        ];
    }, $visitLogs);

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