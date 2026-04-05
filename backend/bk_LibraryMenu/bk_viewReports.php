<?php
/**
 * Paginated modal tables for all analytics tabs.
 *
 * Each handler fetches logs, delegates aggregation to bk_libReports.php,
 * slices to the requested page, and returns modal HTML + pagination metadata.
 */

require '../../db/dbconnection.php';
require 'bk_libReports.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']); exit;
}

const ROWS_PER_PAGE = 10;

//  Modal table renderer

function renderLogsTable(array $rows): string
{
    $headers = ['ID Number', 'Name', 'College', 'Course', 'Type', 'Section', 'Sex', 'Check-in', 'Check-out', 'Agency / Organization', 'Duration (min)'];

    $html  = '<div class="table-responsive">';
    $html .= '<table class="table table-sm table-striped table-hover align-middle mb-0">';
    $html .= '<thead class="table-dark"><tr>';

    foreach ($headers as $header) {
        $html .= '<th class="small fw-semibold">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
    }

    $html .= '</tr></thead>';
    $html .= '<tbody class="small">';

    foreach ($rows as $index => $row) {
        $html .= '<tr class="' . ($index % 2 === 0 ? 'table-success' : '') . '">';
        $html .= '<td class="ps-3 fw-semibold">' . htmlspecialchars($row['id_number'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-muted small">' . htmlspecialchars($row['name'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-muted small">' . htmlspecialchars($row['college'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-muted small">' . htmlspecialchars($row['course'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . getTypeBadge($row['classification'] ?: '—') . '</td>';
        $html .= '<td class="text-muted small">' . htmlspecialchars($row['library'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-muted small">' . htmlspecialchars($row['sex'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-muted small">' . ($row['checkin_time']  ? date('M j, Y g:i A', strtotime($row['checkin_time']))  : '—') . '</td>';
        $html .= '<td class="text-muted small">' . ($row['checkout_time'] ? date('M j, Y g:i A', strtotime($row['checkout_time'])) : '—') . '</td>';
        $html .= '<td class="text-muted small">' . htmlspecialchars($row['agency_organization'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-end pe-3">' . (int) round($row['duration']) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';

    return $html;
}

function renderUsersTable(array $rows): string
{
    $headers = ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'];

    $html  = '<div class="table-responsive">';
    $html .= '<table class="table table-sm table-striped table-hover align-middle mb-0">';
    $html .= '<thead class="table-dark"><tr>';

    foreach ($headers as $header) {
        $html .= '<th class="small fw-semibold">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
    }

    $html .= '</tr></thead>';
    $html .= '<tbody class="small">';

    foreach ($rows as $index => $row) {
        $html .= '<tr class="' . ($index % 2 === 0 ? 'table-success' : '') . '">';
        $html .= '<td class="ps-3 fw-semibold">' . htmlspecialchars($row['display_label'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-muted small">' . htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-muted small">' . htmlspecialchars($row['college'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-muted small">' . htmlspecialchars($row['course'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . getTypeBadge($row['classification']) . '</td>';
        $html .= '<td class="text-muted small">' . htmlspecialchars($row['library'] ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-end fw-semibold text-primary">' . (int) $row['checkins'] . '</td>';
        $html .= '<td class="text-end">' . (int) round($row['duration']) . '</td>';
        $html .= '<td class="text-muted small pe-3">'     . date('M j, Y g:i A', strtotime($row['last_checkin'])) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';

    return $html;
}

function renderCollegesTable(array $rows): string
{
    $headers = ['College', 'Unique Visitors', 'Duration (min)', 'Last Check-in'];

    $html  = '<div class="table-responsive">';
    $html .= '<table class="table table-sm table-striped table-hover align-middle mb-0">';
    $html .= '<thead class="table-dark"><tr>';

    foreach ($headers as $header) {
        $html .= '<th class="small fw-semibold">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
    }

    $html .= '</tr></thead>';
    $html .= '<tbody class="small">';

    foreach ($rows as $index => $row) {
        $html .= '<tr class="' . ($index % 2 === 0 ? 'table-success' : '') . '">';
        $html .= '<td class="ps-3 fw-semibold">' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-end">' . (int) $row['visitors'] . '</td>';
        $html .= '<td class="text-end">' . (int) round($row['duration']) . '</td>';
        $html .= '<td class="text-muted small pe-3">' . date('M j, Y g:i A', strtotime($row['last_checkin'])) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';

    return $html;
}

function renderCoursesTable(array $rows): string
{
    $headers = ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'];

    $html  = '<div class="table-responsive">';
    $html .= '<table class="table table-sm table-striped table-hover align-middle mb-0">';
    $html .= '<thead class="table-dark"><tr>';

    foreach ($headers as $header) {
        $html .= '<th class="small fw-semibold">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
    }

    $html .= '</tr></thead>';
    $html .= '<tbody class="small">';

    foreach ($rows as $index => $row) {
        $html .= '<tr class="' . ($index % 2 === 0 ? 'table-success' : '') . '">';
        $html .= '<td class="ps-3 text-muted small">' . htmlspecialchars($row['college'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="fw-semibold">' . htmlspecialchars($row['course'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-end">' . (int) $row['visitors'] . '</td>';
        $html .= '<td class="text-end">' . (int) round($row['duration']) . '</td>';
        $html .= '<td class="text-muted small pe-3">' . date('M j, Y g:i A', strtotime($row['last_checkin'])) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';

    return $html;
}

function renderDemographicsTable(array $rows): string
{
    $headers = ['ID Number', 'Sex', 'Check-in', 'Check-out', 'Duration (min)'];

    $html  = '<div class="table-responsive">';
    $html .= '<table class="table table-sm table-striped table-hover align-middle mb-0">';
    $html .= '<thead class="table-dark"><tr>';

    foreach ($headers as $header) {
        $html .= '<th class="small fw-semibold">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
    }

    $html .= '</tr></thead>';
    $html .= '<tbody class="small">';

    foreach ($rows as $index => $row) {
        $html .= '<tr class="' . ($index % 2 === 0 ? 'table-success' : '') . '">';
        $html .= '<td class="ps-3 fw-semibold">' . htmlspecialchars($row['display_label'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['sex'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="text-muted small">' . date('M j, Y g:i A', strtotime($row['checkin_time'])) . '</td>';
        $html .= '<td class="text-muted small">' . ($row['checkout_time'] ? date('M j, Y g:i A', strtotime($row['checkout_time'])) : '—') . '</td>';
        $html .= '<td class="text-end pe-3">' . (int) round($row['duration']) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';

    return $html;
}

//  Pagination renderer

function renderModalPagination(int $totalPages, int $currentPage, int $total, int $perPage): string
{
    if ($totalPages <= 1) return '';

    $isFirst = $currentPage === 1;
    $isLast = $currentPage === $totalPages;
    $windowSize = 5;
    $windowStart  = max(1, min($currentPage - intdiv($windowSize, 2), $totalPages - $windowSize + 1));
    $windowEnd = min($totalPages, $windowStart + $windowSize - 1);

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

//  Handlers (respond() inlined)

function ViewAllLogs(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $visitLogs = fetchVisitLogs($whereClause, $queryParams);

    $tableRows = [];

    foreach ($visitLogs as $logEntry) {
        $checkoutTime = $logEntry['checkout_time'] ?? null;

        $tableRows[] = [
            'id_number' => $logEntry['id_number'] ?? '',
            'name' => $logEntry['name'] ?? '',
            'college' => $logEntry['college'] ?? '',
            'course' => $logEntry['course'] ?? '',
            'classification' => $logEntry['classification'] ?? '',
            'library' => $logEntry['library_section_name'] ?? '',
            'sex' => $logEntry['sex'] ?? '',
            'checkin_time' => $logEntry['checkin_time'] ?? '',
            'checkout_time' => $checkoutTime,
            'agency_organization'=> $logEntry['agency_organization'] ?? '',
            'duration' => minutesBetween($logEntry['checkin_time'] ?? null, $checkoutTime),
        ];
    }

    $page   = (int) ($_POST['page'] ?? 1);
    $result = paginate($tableRows, $page, ROWS_PER_PAGE);

    echo json_encode([
        'status' => 'success',
        'tableHtml'  => renderLogsTable($result['items']),
        'pagination' => renderModalPagination($result['totalPages'], $result['page'], $result['total'], ROWS_PER_PAGE),
        'total' => $result['total'],
        'totalPages' => $result['totalPages'],
        'page' => $result['page'],
    ]);
    exit;
}

function ViewAllUsers(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $userStats = summarizeUsers(fetchVisitLogs($whereClause, $queryParams));
    uasort($userStats, fn($userA, $userB) => $userB['checkins'] <=> $userA['checkins']);

    $tableRows = array_values($userStats);
    $page = (int) ($_POST['page'] ?? 1);
    $result = paginate($tableRows, $page, ROWS_PER_PAGE);

    echo json_encode([
        'status' => 'success',
        'tableHtml'  => renderUsersTable($result['items']),
        'pagination' => renderModalPagination($result['totalPages'], $result['page'], $result['total'], ROWS_PER_PAGE),
        'total' => $result['total'],
        'totalPages' => $result['totalPages'],
        'page' => $result['page'],
    ]);
    exit;
}

function ViewAllColleges(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $collegeStats = summarizeColleges(fetchVisitLogs($whereClause, $queryParams));
    uasort($collegeStats, fn($collegeA, $collegeB) => $collegeB['visitors'] <=> $collegeA['visitors']);

    $tableRows = array_map(fn($collegeName, $collegeData) => [
        'name' => $collegeName,
        'visitors' => $collegeData['visitors'],
        'duration' => $collegeData['duration'],
        'last_checkin' => $collegeData['last_checkin'],
    ], array_keys($collegeStats), array_values($collegeStats));

    $page   = (int) ($_POST['page'] ?? 1);
    $result = paginate($tableRows, $page, ROWS_PER_PAGE);

    echo json_encode([
        'status' => 'success',
        'tableHtml'  => renderCollegesTable($result['items']),
        'pagination' => renderModalPagination($result['totalPages'], $result['page'], $result['total'], ROWS_PER_PAGE),
        'total' => $result['total'],
        'totalPages' => $result['totalPages'],
        'page' => $result['page'],
    ]);
    exit;
}

function ViewAllCourses(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $courseStats = summarizeCourses(fetchVisitLogs($whereClause, $queryParams));
    uasort($courseStats, fn($courseA, $courseB) => $courseB['visitors'] <=> $courseA['visitors']);

    $tableRows = array_values($courseStats);
    $page      = (int) ($_POST['page'] ?? 1);
    $result    = paginate($tableRows, $page, ROWS_PER_PAGE);

    echo json_encode([
        'status'     => 'success',
        'tableHtml'  => renderCoursesTable($result['items']),
        'pagination' => renderModalPagination($result['totalPages'], $result['page'], $result['total'], ROWS_PER_PAGE),
        'total'      => $result['total'],
        'totalPages' => $result['totalPages'],
        'page'       => $result['page'],
    ]);
    exit;
}

function ViewAllDemographics(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $visitLogs = fetchVisitLogs($whereClause, $queryParams);

    $tableRows = [];

    foreach ($visitLogs as $logEntry) {
        $idNumber = $logEntry['id_number'] ?? '';
        $checkoutTime = $logEntry['checkout_time'] ?? null;

        $tableRows[] = [
            'display_label' => ($idNumber === '' || $idNumber === '0') ? ($logEntry['name'] ?? 'Guest') : $idNumber,
            'sex' => $logEntry['sex'] ?? '',
            'checkin_time'  => $logEntry['checkin_time'] ?? '',
            'checkout_time' => $checkoutTime,
            'duration' => minutesBetween($logEntry['checkin_time'] ?? null, $checkoutTime),
        ];
    }

    $page   = (int) ($_POST['page'] ?? 1);
    $result = paginate($tableRows, $page, ROWS_PER_PAGE);

    echo json_encode([
        'status' => 'success',
        'tableHtml'  => renderDemographicsTable($result['items']),
        'pagination' => renderModalPagination($result['totalPages'], $result['page'], $result['total'], ROWS_PER_PAGE),
        'total' => $result['total'],
        'totalPages' => $result['totalPages'],
        'page' => $result['page'],
    ]);
    exit;
}

//  Dispatch

$request = trim($_POST['request'] ?? '');

switch ($request) {
    case 'viewAllLogs':
        ViewAllLogs();
        break;
    case 'viewAllUsers':
        ViewAllUsers();
        break;
    case 'viewAllColleges':
        ViewAllColleges();
        break;
    case 'viewAllCourses':
        ViewAllCourses();
        break;
    case 'viewAllDemographics': 
        ViewAllDemographics(); 
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => "Unknown request: '{$request}'."]);
}