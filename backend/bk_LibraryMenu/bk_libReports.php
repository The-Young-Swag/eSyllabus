<?php
/**
 * Library Analytics - Backend Handler
 * Handles tab rendering and viewAll paginated data for the Library Reports dashboard.
 */

include "../../db/dbconnection.php";
header('Content-Type: application/json');

// ============================================================
//  DISPLAY CONFIGURATION
// ============================================================

define('USER_DISPLAY_FIELD', 'id_number');


// ============================================================
//  COLLEGE COLOR MAP
//  Easy to maintain: add/edit entries here to change college colors.
//  Keys are matched case-insensitively against the college field.
// ============================================================

define('COLLEGE_COLOR_MAP', [
    'CAF' => 'rgba(22,163,74,0.88)',    // green
    'CAS' => 'rgba(234,88,12,0.88)',    // orange
    'CBM' => 'rgba(202,138,4,0.88)',    // yellow
    'CET' => 'rgba(220,38,38,0.88)',    // red
    'CED' => 'rgba(37,99,235,0.88)',    // blue
    'CVM' => 'rgba(107,114,128,0.88)',  // grey
]);

define('COLLEGE_COLOR_FALLBACK', 'rgba(139,92,246,0.88)'); // violet for unknown colleges


// ============================================================
//  UTILITY FUNCTIONS
// ============================================================

function calcDurationMinutes(string $checkinTime, ?string $checkoutTime): float
{
    if (!$checkoutTime) return 0;
    return (strtotime($checkoutTime) - strtotime($checkinTime)) / 60;
}

function filterByClassification(array $logs, string $classification): array
{
    return array_filter(
        $logs,
        fn($log) => strtolower($log['classification']) === strtolower($classification)
    );
}

function excludeGuests(array $logs): array
{
    return array_filter(
        $logs,
        fn($log) => strtolower($log['classification']) !== 'guest'
    );
}

function formatDateTime(string $datetime): string
{
    return date('M j, Y g:i A', strtotime($datetime));
}

function safe(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function getUserDisplayLabel(array $log): string
{
    return $log[USER_DISPLAY_FIELD] ?? $log['id_number'];
}

/**
 * Resolves a college abbreviation to its assigned color.
 * Detects abbreviation from the college string by checking if any key
 * from COLLEGE_COLOR_MAP appears as a word in the college name.
 */
function resolveCollegeColor(string $collegeName): string
{
    $upperCollege = strtoupper($collegeName);
    foreach (COLLEGE_COLOR_MAP as $abbreviation => $color) {
        if (strpos($upperCollege, strtoupper($abbreviation)) !== false) {
            return $color;
        }
    }
    return COLLEGE_COLOR_FALLBACK;
}


// ============================================================
//  QUERY FUNCTIONS
// ============================================================

function buildWhereClauseFromFilters(array $postData): array
{
    $where       = '';
    $boundParams = [];

    if (!empty($postData['startDate'])) {
        $where .= " AND CAST(l.checkin_time AS DATE) >= :startDate";
        $boundParams[':startDate'] = $postData['startDate'];
    }

    if (!empty($postData['endDate'])) {
        $where .= " AND CAST(l.checkin_time AS DATE) <= :endDate";
        $boundParams[':endDate'] = $postData['endDate'];
    }

    if (!empty($postData['classification']) && $postData['classification'] !== 'All') {
        $where .= " AND l.classification = :classification";
        $boundParams[':classification'] = $postData['classification'];
    }

    if (!empty($postData['library']) && $postData['library'] !== 'All') {
        $where .= " AND l.library = :libraryId";
        $boundParams[':libraryId'] = $postData['library'];
    }

    return [$where, $boundParams];
}

function fetchFilteredVisitLogs(string $where, array $boundParams): array
{
    $sql = "
        SELECT
            l.id,
            l.id_number,
            l.name,
            l.college,
            l.course,
            l.library         AS library_section_id,
            s.SectionName     AS library_section_name,
            l.checkin_time,
            l.checkout_time,
            l.sex,
            l.classification
        FROM  Library_logs   l
        LEFT JOIN LibrarySection s ON l.library = s.SectionID
        WHERE 1=1 {$where}
        ORDER BY l.checkin_time DESC
    ";

    return execsqlSRS($sql, 'Select', $boundParams);
}


// ============================================================
//  KPI AGGREGATION
// ============================================================

function computeDashboardKpis(array $visitLogs, string $selectedEndDate): array
{
    $totalVisitCount   = count($visitLogs);

    $totalDurationMins = array_sum(array_map(
        fn($log) => calcDurationMinutes($log['checkin_time'], $log['checkout_time']),
        $visitLogs
    ));

    $uniqueVisitorCount = count(array_unique(array_column($visitLogs, 'id_number')));

    $avgDurationMins = $totalVisitCount
        ? round($totalDurationMins / $totalVisitCount, 1)
        : 0;

    $endDateVisitCount = 0;
    if ($selectedEndDate) {
        $endDateVisitCount = count(array_filter(
            $visitLogs,
            fn($log) => substr($log['checkin_time'], 0, 10) === $selectedEndDate
        ));
    }

    return [
        'totalVisits'     => $totalVisitCount,
        'totalDuration'   => round($totalDurationMins),
        'uniqueUsers'     => $uniqueVisitorCount,
        'avgDuration'     => $avgDurationMins,
        'endDateCheckins' => $endDateVisitCount,
    ];
}


// ============================================================
//  TAB DATA AGGREGATION
// ============================================================

function aggregateTopUsersByClassification(array $visitLogs): array
{
    $classifications = ['Student', 'Employee', 'Guest'];
    $topByCheckins   = [];
    $topByDuration   = [];

    foreach ($classifications as $classification) {
        $logsForClass    = filterByClassification($visitLogs, $classification);
        $visitCountById  = [];
        $durationSumById = [];
        $userMetaById    = [];

        foreach ($logsForClass as $log) {
            $userId    = $log['id_number'];
            $checkinAt = $log['checkin_time'];

            $visitCountById[$userId]  = ($visitCountById[$userId]  ?? 0) + 1;
            $durationSumById[$userId] = ($durationSumById[$userId] ?? 0)
                + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);

            if (!isset($userMetaById[$userId])) {
                $userMetaById[$userId] = [
                    'display_label' => getUserDisplayLabel($log),
                    'library'       => $log['library_section_name'],
                    'last_checkin'  => $checkinAt,
                ];
            } elseif ($checkinAt > $userMetaById[$userId]['last_checkin']) {
                $userMetaById[$userId]['last_checkin'] = $checkinAt;
            }
        }

        arsort($visitCountById);
        $topByCheckins[$classification] = [];
        $rank = 0;
        foreach ($visitCountById as $userId => $visitCount) {
            if ($rank >= 3) break;
            $topByCheckins[$classification][$userId] = array_merge(
                $userMetaById[$userId],
                ['count' => $visitCount]
            );
            $rank++;
        }

        arsort($durationSumById);
        $topByDuration[$classification] = [];
        $rank = 0;
        foreach ($durationSumById as $userId => $totalMinutes) {
            if ($rank >= 3) break;
            $topByDuration[$classification][$userId] = array_merge(
                $userMetaById[$userId],
                ['minutes' => $totalMinutes]
            );
            $rank++;
        }
    }

    return ['topCheckins' => $topByCheckins, 'topDuration' => $topByDuration];
}

/**
 * Counts visits grouped by user classification (Student, Employee, Guest).
 * Used for the visitor type donut chart in the Users tab.
 */
function aggregateClassificationDistribution(array $visitLogs): array
{
    $countByClassification = [];
    foreach ($visitLogs as $log) {
        $classification = $log['classification'] ?: 'Unknown';
        $countByClassification[$classification] = ($countByClassification[$classification] ?? 0) + 1;
    }
    return $countByClassification;
}

function aggregateTopColleges(array $visitLogs): array
{
    $nonGuestLogs            = excludeGuests($visitLogs);
    $uniqueVisitorsByCollege = [];
    $visitCountByCollege     = [];
    $durationSumByCollege    = [];
    $lastCheckinByCollege    = [];

    foreach ($nonGuestLogs as $log) {
        $college = $log['college'] ?: 'Unknown';
        $userId  = $log['id_number'];

        if (!isset($uniqueVisitorsByCollege[$college][$userId])) {
            $uniqueVisitorsByCollege[$college][$userId] = true;
            $visitCountByCollege[$college] = ($visitCountByCollege[$college] ?? 0) + 1;
        }

        $durationSumByCollege[$college] = ($durationSumByCollege[$college] ?? 0)
            + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);

        if (!isset($lastCheckinByCollege[$college]) || $log['checkin_time'] > $lastCheckinByCollege[$college]) {
            $lastCheckinByCollege[$college] = $log['checkin_time'];
        }
    }

    arsort($visitCountByCollege);
    $topByCheckins = [];
    $rank = 0;
    foreach ($visitCountByCollege as $college => $visitCount) {
        if ($rank >= 3) break;
        $topByCheckins[$college] = [
            'count'        => $visitCount,
            'last_checkin' => $lastCheckinByCollege[$college],
            'color'        => resolveCollegeColor($college),
        ];
        $rank++;
    }

    arsort($durationSumByCollege);
    $topByDuration = [];
    $rank = 0;
    foreach ($durationSumByCollege as $college => $totalMinutes) {
        if ($rank >= 3) break;
        $topByDuration[$college] = [
            'minutes'      => $totalMinutes,
            'last_checkin' => $lastCheckinByCollege[$college],
            'color'        => resolveCollegeColor($college),
        ];
        $rank++;
    }

    return [
        'top3CollegesCheckin'  => $topByCheckins,
        'top3CollegesDuration' => $topByDuration,
    ];
}

function aggregateTopCoursesByCollege(array $visitLogs): array
{
    $nonGuestLogs               = excludeGuests($visitLogs);
    $uniqueVisitorsByCourse     = [];
    $visitCountByCollegeCourse  = [];
    $durationSumByCollegeCourse = [];
    $lastCheckinByCollegeCourse = [];

    foreach ($nonGuestLogs as $log) {
        $college          = $log['college'] ?: 'Unknown';
        $course           = $log['course']  ?: 'Unknown';
        $userId           = $log['id_number'];
        $collegeCourseKey = "{$college}|{$course}";

        if (!isset($uniqueVisitorsByCourse[$college][$course][$userId])) {
            $uniqueVisitorsByCourse[$college][$course][$userId] = true;
            $visitCountByCollegeCourse[$college][$course] =
                ($visitCountByCollegeCourse[$college][$course] ?? 0) + 1;
        }

        $durationSumByCollegeCourse[$college][$course] =
            ($durationSumByCollegeCourse[$college][$course] ?? 0)
            + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);

        if (!isset($lastCheckinByCollegeCourse[$collegeCourseKey])
            || $log['checkin_time'] > $lastCheckinByCollegeCourse[$collegeCourseKey]) {
            $lastCheckinByCollegeCourse[$collegeCourseKey] = $log['checkin_time'];
        }
    }

    $topByCheckins = [];
    foreach ($visitCountByCollegeCourse as $college => $courseVisitCounts) {
        arsort($courseVisitCounts);
        $topByCheckins[$college] = [];
        $rank = 0;
        foreach ($courseVisitCounts as $course => $visitCount) {
            if ($rank >= 3) break;
            $collegeCourseKey = "{$college}|{$course}";
            $topByCheckins[$college][$course] = [
                'count'        => $visitCount,
                'last_checkin' => $lastCheckinByCollegeCourse[$collegeCourseKey] ?? null,
            ];
            $rank++;
        }
    }

    $topByDuration = [];
    foreach ($durationSumByCollegeCourse as $college => $courseDurations) {
        arsort($courseDurations);
        $topByDuration[$college] = [];
        $rank = 0;
        foreach ($courseDurations as $course => $totalMinutes) {
            if ($rank >= 3) break;
            $collegeCourseKey = "{$college}|{$course}";
            $topByDuration[$college][$course] = [
                'minutes'      => $totalMinutes,
                'last_checkin' => $lastCheckinByCollegeCourse[$collegeCourseKey] ?? null,
            ];
            $rank++;
        }
    }

    return [
        'topCoursesCheckin'  => $topByCheckins,
        'topCoursesDuration' => $topByDuration,
    ];
}

function aggregateSexDistribution(array $visitLogs): array
{
    $countBySex = [];
    foreach ($visitLogs as $log) {
        $sex = $log['sex'] ?: 'Unknown';
        $countBySex[$sex] = ($countBySex[$sex] ?? 0) + 1;
    }
    return $countBySex;
}

/**
 * Builds a full color-annotated college distribution for donut charts.
 * Returns all colleges (not just top 3) with their visit counts and resolved colors.
 */
function aggregateCollegeDistribution(array $visitLogs): array
{
    $nonGuestLogs            = excludeGuests($visitLogs);
    $uniqueVisitorsByCollege = [];
    $visitCountByCollege     = [];

    foreach ($nonGuestLogs as $log) {
        $college = $log['college'] ?: 'Unknown';
        $userId  = $log['id_number'];
        if (!isset($uniqueVisitorsByCollege[$college][$userId])) {
            $uniqueVisitorsByCollege[$college][$userId] = true;
            $visitCountByCollege[$college] = ($visitCountByCollege[$college] ?? 0) + 1;
        }
    }

    arsort($visitCountByCollege);

    $result = [];
    foreach ($visitCountByCollege as $college => $count) {
        $result[$college] = [
            'count' => $count,
            'color' => resolveCollegeColor($college),
        ];
    }
    return $result;
}


// ============================================================
//  VIEWALL PAGINATED DATA BUILDERS
// ============================================================

function buildViewAllUsers(array $visitLogs, int $offset, int $limit): array
{
    $aggregatedByUser = [];

    foreach ($visitLogs as $log) {
        $userId = $log['id_number'];

        if (!isset($aggregatedByUser[$userId])) {
            $aggregatedByUser[$userId] = [
                'display_label' => getUserDisplayLabel($log),
                'type'          => $log['classification'],
                'library'       => $log['library_section_name'],
                'checkins'      => 0,
                'duration'      => 0,
                'last_checkin'  => $log['checkin_time'],
            ];
        }

        $aggregatedByUser[$userId]['checkins']++;
        $aggregatedByUser[$userId]['duration'] += calcDurationMinutes(
            $log['checkin_time'],
            $log['checkout_time']
        );

        if ($log['checkin_time'] > $aggregatedByUser[$userId]['last_checkin']) {
            $aggregatedByUser[$userId]['last_checkin'] = $log['checkin_time'];
        }
    }

    uasort($aggregatedByUser, fn($userA, $userB) => $userB['checkins'] <=> $userA['checkins']);

    $totalUsers = count($aggregatedByUser);
    $pageRows   = array_values(array_slice($aggregatedByUser, $offset, $limit, true));

    return ['rows' => $pageRows, 'total' => $totalUsers];
}

function buildViewAllColleges(array $visitLogs, int $offset, int $limit): array
{
    $aggregatedByCollege = [];

    foreach (excludeGuests($visitLogs) as $log) {
        $college = $log['college'] ?: 'Unknown';

        if (!isset($aggregatedByCollege[$college])) {
            $aggregatedByCollege[$college] = [
                'college_name'    => $college,
                'unique_visitors' => [],
                'duration'        => 0,
                'last_checkin'    => $log['checkin_time'],
            ];
        }

        $aggregatedByCollege[$college]['unique_visitors'][$log['id_number']] = true;
        $aggregatedByCollege[$college]['duration'] += calcDurationMinutes(
            $log['checkin_time'],
            $log['checkout_time']
        );

        if ($log['checkin_time'] > $aggregatedByCollege[$college]['last_checkin']) {
            $aggregatedByCollege[$college]['last_checkin'] = $log['checkin_time'];
        }
    }

    $rows = [];
    foreach ($aggregatedByCollege as $collegeData) {
        $rows[] = [
            'name'         => $collegeData['college_name'],
            'checkins'     => count($collegeData['unique_visitors']),
            'duration'     => $collegeData['duration'],
            'last_checkin' => $collegeData['last_checkin'],
        ];
    }

    usort($rows, fn($rowA, $rowB) => $rowB['checkins'] <=> $rowA['checkins']);

    $totalColleges = count($rows);
    $pageRows      = array_slice($rows, $offset, $limit);

    return ['rows' => $pageRows, 'total' => $totalColleges];
}

function buildViewAllCourses(array $visitLogs, int $offset, int $limit): array
{
    $aggregatedByCourse = [];

    foreach (excludeGuests($visitLogs) as $log) {
        $college          = $log['college'] ?: 'Unknown';
        $course           = $log['course']  ?: 'Unknown';
        $collegeCourseKey = "{$college}|{$course}";

        if (!isset($aggregatedByCourse[$collegeCourseKey])) {
            $aggregatedByCourse[$collegeCourseKey] = [
                'college'         => $college,
                'course'          => $course,
                'unique_visitors' => [],
                'duration'        => 0,
                'last_checkin'    => $log['checkin_time'],
            ];
        }

        $aggregatedByCourse[$collegeCourseKey]['unique_visitors'][$log['id_number']] = true;
        $aggregatedByCourse[$collegeCourseKey]['duration'] += calcDurationMinutes(
            $log['checkin_time'],
            $log['checkout_time']
        );

        if ($log['checkin_time'] > $aggregatedByCourse[$collegeCourseKey]['last_checkin']) {
            $aggregatedByCourse[$collegeCourseKey]['last_checkin'] = $log['checkin_time'];
        }
    }

    $rows = [];
    foreach ($aggregatedByCourse as $courseData) {
        $rows[] = [
            'college'      => $courseData['college'],
            'course'       => $courseData['course'],
            'checkins'     => count($courseData['unique_visitors']),
            'duration'     => $courseData['duration'],
            'last_checkin' => $courseData['last_checkin'],
        ];
    }

    usort($rows, fn($rowA, $rowB) => $rowB['checkins'] <=> $rowA['checkins']);

    $totalCourses = count($rows);
    $pageRows     = array_slice($rows, $offset, $limit);

    return ['rows' => $pageRows, 'total' => $totalCourses];
}

function buildViewAllDemographics(array $visitLogs, int $offset, int $limit): array
{
    $rows = array_map(fn($log) => [
        'display_label' => getUserDisplayLabel($log),
        'sex'           => $log['sex'],
        'checkin'       => $log['checkin_time'],
        'checkout'      => $log['checkout_time'],
        'duration'      => calcDurationMinutes($log['checkin_time'], $log['checkout_time']),
    ], $visitLogs);

    $totalLogs = count($rows);
    $pageRows  = array_slice($rows, $offset, $limit);

    return ['rows' => $pageRows, 'total' => $totalLogs];
}


// ============================================================
//  VIEWALL HTML RENDER FUNCTIONS
// ============================================================

function renderModalTable(string $tab, array $rows): string
{
    $columnsByTab = [
        'users' => [
            'headers' => ['ID Number', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) => '
                <td class="fw-semibold">' . safe($row['display_label'])         . '</td>
                <td>'                     . safe($row['type'])                   . '</td>
                <td>'                     . safe($row['library'])                . '</td>
                <td class="text-end">'    . (int)$row['checkins']               . '</td>
                <td class="text-end">'    . (int)round($row['duration'])         . '</td>
                <td>'                     . formatDateTime($row['last_checkin']) . '</td>
            ',
        ],
        'colleges' => [
            'headers' => ['College', 'Unique Visitors', 'Total Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) => '
                <td class="fw-semibold">' . safe($row['name'])                  . '</td>
                <td class="text-end">'    . (int)$row['checkins']               . '</td>
                <td class="text-end">'    . (int)round($row['duration'])         . '</td>
                <td>'                     . formatDateTime($row['last_checkin']) . '</td>
            ',
        ],
        'courses' => [
            'headers' => ['College', 'Course', 'Unique Visitors', 'Total Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) => '
                <td>'                     . safe($row['college'])                . '</td>
                <td class="fw-semibold">' . safe($row['course'])                 . '</td>
                <td class="text-end">'    . (int)$row['checkins']               . '</td>
                <td class="text-end">'    . (int)round($row['duration'])         . '</td>
                <td>'                     . formatDateTime($row['last_checkin']) . '</td>
            ',
        ],
        'demographics' => [
            'headers' => ['ID Number', 'Sex', 'Check-in', 'Check-out', 'Duration (min)'],
            'rowFn'   => fn($row) => '
                <td class="fw-semibold">' . safe($row['display_label'])                                 . '</td>
                <td>'                     . safe($row['sex'])                                           . '</td>
                <td>'                     . formatDateTime($row['checkin'])                             . '</td>
                <td>'                     . ($row['checkout'] ? formatDateTime($row['checkout']) : '—') . '</td>
                <td class="text-end">'    . (int)round($row['duration'])                                . '</td>
            ',
        ],
    ];

    if (!isset($columnsByTab[$tab])) return '';

    $tabConfig   = $columnsByTab[$tab];
    $headerCells = implode('', array_map(fn($heading) => "<th>{$heading}</th>", $tabConfig['headers']));
    $bodyRows    = implode('', array_map(fn($row) => '<tr>' . ($tabConfig['rowFn'])($row) . '</tr>', $rows));

    return "
        <div class=\"table-responsive\">
            <table class=\"table table-sm table-striped table-hover align-middle\">
                <thead class=\"table-dark\"><tr>{$headerCells}</tr></thead>
                <tbody>{$bodyRows}</tbody>
            </table>
        </div>
    ";
}

function renderModalPagination(int $totalPages, int $currentPage): string
{
    $pageItems = '';
    for ($pageNum = 1; $pageNum <= $totalPages; $pageNum++) {
        $activeClass = ($pageNum === $currentPage) ? 'active' : '';
        $pageItems  .= "<li class=\"page-item {$activeClass}\">
                            <a class=\"page-link\" href=\"#\" data-page=\"{$pageNum}\">{$pageNum}</a>
                        </li>";
    }

    return "<nav><ul class=\"pagination pagination-sm mb-0\">{$pageItems}</ul></nav>";
}


// ============================================================
//  TAB HTML RENDER FUNCTIONS
// ============================================================

/**
 * Renders the Users tab.
 * Includes: top visitors table, visitor type donut, top duration table.
 */
function renderUsersTab(array $topByCheckins, array $topByDuration): string
{
    ob_start(); ?>

    <div class="row g-4">

        <!-- ── Left column: Check-ins ──────────────────────────────────── -->
        <div class="col-xl-8 d-flex flex-column gap-4">

            <!-- Check-ins bar chart -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                    <div>
                        <h6 class="fw-semibold mb-0">Top Users by Check-ins</h6>
                        <small class="text-muted">Most frequent visitors this period</small>
                    </div>
                    <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">Top 3</span>
                </div>
                <div class="card-body px-4 pt-3 pb-2">
                    <!-- Height 160px gives comfortable spacing for 3 horizontal bars -->
                    <div style="height:160px; position:relative;">
                        <canvas id="chartTopUserCheckins"></canvas>
                    </div>
                </div>
            </div>

            <!-- Check-ins detail table -->
            <div class="card border-0 shadow-sm flex-grow-1">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                    <h6 class="fw-semibold mb-0">Check-in Details</h6>
                    <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="users">
                        <i class="bi bi-arrow-up-right-square me-1"></i> View All
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ID Number</th>
                                    <th>Type</th>
                                    <th>Library Section</th>
                                    <th class="text-end">Check-ins</th>
                                    <th class="text-end pe-4">Last Check-in</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($topByCheckins as $classification => $userList): ?>
                                <?php foreach ($userList as $userData): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold"><?= safe($userData['display_label']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">
                                                <?= safe($classification) ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small"><?= safe($userData['library'] ?? '—') ?></td>
                                        <td class="text-end fw-semibold text-primary"><?= number_format($userData['count']) ?></td>
                                        <td class="text-end text-muted small pe-4"><?= formatDateTime($userData['last_checkin']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /left column -->


        <!-- ── Right column ────────────────────────────────────────────── -->
        <div class="col-xl-4 d-flex flex-column gap-4">

            <!-- Visitor type donut -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-semibold mb-0">Visitor Type Breakdown</h6>
                    <small class="text-muted">Distribution by user classification</small>
                </div>
                <div class="card-body px-4 py-3">
                    <div style="height:260px; position:relative;">
                        <canvas id="chartVisitorTypeDonut"></canvas>
                    </div>
                </div>
            </div>

            <!-- Duration bar chart -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                    <div>
                        <h6 class="fw-semibold mb-0">Top Users by Duration</h6>
                        <small class="text-muted">Longest cumulative sessions</small>
                    </div>
                    <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">Top 3</span>
                </div>
                <div class="card-body px-4 pt-3 pb-2">
                    <div style="height:160px; position:relative;">
                        <canvas id="chartTopUserDuration"></canvas>
                    </div>
                </div>
            </div>

            <!-- Duration detail table -->
            <div class="card border-0 shadow-sm flex-grow-1">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-semibold mb-0">Duration Details</h6>
                    <small class="text-muted">Total session time per user</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ID Number</th>
                                    <th>Type</th>
                                    <th class="text-end pe-4">Minutes</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($topByDuration as $classification => $userList): ?>
                                <?php foreach ($userList as $userData): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold"><?= safe($userData['display_label']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">
                                                <?= safe($classification) ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-semibold text-success pe-4">
                                            <?= number_format(round($userData['minutes'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /right column -->

    </div><!-- /row -->

    <?php
    return ob_get_clean();
}

/**
 * Renders the Colleges tab — top colleges by unique visitors and total duration.
 * Uses donut charts instead of bar charts.
 */
function renderCollegesTab(array $topByCheckins, array $topByDuration): string
{
    ob_start(); ?>
    <div class="row g-4 mb-4">

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0">Top Colleges by Check-ins</h6>
                    <small class="text-muted">Unique visitors per college</small>
                </div>
                <div class="card-body">
                    <div style="height:280px;" class="d-flex align-items-center justify-content-center">
                        <canvas id="chartCollegeCheckin"></canvas>
                    </div>
                    <hr class="my-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>College</th>
                                    <th class="text-end">Unique Visitors</th>
                                    <th class="text-end">Last Check-in</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($topByCheckins as $collegeName => $collegeData): ?>
                                <tr>
                                    <td class="fw-semibold"><?= safe($collegeName) ?></td>
                                    <td class="text-end"><?= $collegeData['count'] ?></td>
                                    <td class="text-end text-muted small"><?= formatDateTime($collegeData['last_checkin']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0">Top Colleges by Duration</h6>
                    <small class="text-muted">Total session time per college</small>
                </div>
                <div class="card-body">
                    <div style="height:280px;" class="d-flex align-items-center justify-content-center">
                        <canvas id="chartCollegeDuration"></canvas>
                    </div>
                    <hr class="my-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>College</th>
                                    <th class="text-end">Duration (min)</th>
                                    <th class="text-end">Last Check-in</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($topByDuration as $collegeName => $collegeData): ?>
                                <tr>
                                    <td class="fw-semibold"><?= safe($collegeName) ?></td>
                                    <td class="text-end"><?= round($collegeData['minutes']) ?></td>
                                    <td class="text-end text-muted small"><?= formatDateTime($collegeData['last_checkin']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-end">
                        <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="colleges">View All Colleges</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Courses tab — top courses per college using donut charts.
 */
function renderCoursesTab(array $topByCheckins, array $topByDuration): string
{
    // ── Flatten all courses from all colleges into a single sorted list ──────

    // Checkins: College → Course → { count, last_checkin }
    $allCheckins = [];
    foreach ($topByCheckins as $college => $courses) {
        foreach ($courses as $course => $data) {
            $allCheckins[] = [
                'college'      => $college,
                'course'       => $course,
                'count'        => $data['count'],
                'last_checkin' => $data['last_checkin'],
            ];
        }
    }
    usort($allCheckins, fn($a, $b) => $b['count'] <=> $a['count']);

    // Duration: College → Course → { minutes, last_checkin }
    $allDuration = [];
    foreach ($topByDuration as $college => $courses) {
        foreach ($courses as $course => $data) {
            $allDuration[] = [
                'college'      => $college,
                'course'       => $course,
                'minutes'      => $data['minutes'],
                'last_checkin' => $data['last_checkin'],
            ];
        }
    }
    usort($allDuration, fn($a, $b) => $b['minutes'] <=> $a['minutes']);

    ob_start(); ?>
    <div class="row g-4 mb-4">

        <!-- Courses by Check-ins -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0">Top Courses by Check-ins</h6>
                    <small class="text-muted">Unique visitors per course across all colleges</small>
                </div>
                <div class="card-body">
                    <div style="height:280px;" class="d-flex align-items-center justify-content-center">
                        <canvas id="chartCoursesCheckin"></canvas>
                    </div>
                    <hr class="my-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>College</th>
                                    <th>Course</th>
                                    <th class="text-end">Visitors</th>
                                    <th class="text-end">Last Check-in</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($allCheckins as $row): ?>
                                <tr>
                                    <td class="text-muted small"><?= safe($row['college']) ?></td>
                                    <td class="fw-semibold"><?= safe($row['course']) ?></td>
                                    <td class="text-end"><?= $row['count'] ?></td>
                                    <td class="text-end text-muted small"><?= formatDateTime($row['last_checkin']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($allCheckins)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No data available</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses by Duration -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0">Top Courses by Duration</h6>
                    <small class="text-muted">Total session time per course across all colleges</small>
                </div>
                <div class="card-body">
                    <div style="height:280px;" class="d-flex align-items-center justify-content-center">
                        <canvas id="chartCoursesDuration"></canvas>
                    </div>
                    <hr class="my-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>College</th>
                                    <th>Course</th>
                                    <th class="text-end">Duration (min)</th>
                                    <th class="text-end">Last Check-in</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($allDuration as $row): ?>
                                <tr>
                                    <td class="text-muted small"><?= safe($row['college']) ?></td>
                                    <td class="fw-semibold"><?= safe($row['course']) ?></td>
                                    <td class="text-end"><?= round($row['minutes']) ?></td>
                                    <td class="text-end text-muted small"><?= formatDateTime($row['last_checkin']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($allDuration)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No data available</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-end">
                        <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="courses">View All Courses</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Demographics tab.
 * Includes: sex distribution donut chart + visitor type donut + metric cards.
 */
function renderDemographicsTab(array $countBySex, int $totalVisitCount): string
{
    ob_start(); ?>
    <div class="row g-4 mb-4">

        <!-- Sex Distribution Donut Chart -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0">Sex Distribution</h6>
                    <small class="text-muted">Visitor breakdown by sex</small>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="height:280px;width:100%;"><canvas id="chartSexDonut"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="col-lg-6">
            <div class="row g-3 align-content-start h-100">

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-primary-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-people-fill text-primary fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Visitors</div>
                                <h4 class="fw-bold mb-0"><?= number_format($totalVisitCount) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-info-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-gender-male text-info fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Male</div>
                                <h4 class="fw-bold mb-0"><?= number_format($countBySex['Male'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-danger-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-gender-female text-danger fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Female</div>
                                <h4 class="fw-bold mb-0"><?= number_format($countBySex['Female'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($countBySex['Unknown'])): ?>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-secondary-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-question-circle text-secondary fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Unknown</div>
                                <h4 class="fw-bold mb-0"><?= number_format($countBySex['Unknown']) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
    <div class="text-end">
        <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="demographics">View All Logs</button>
    </div>
    <?php
    return ob_get_clean();
}


// ============================================================
//  REQUEST BOOTSTRAP
// ============================================================

$requestedAction  = $_POST['action'] ?? 'tab';
$requestedTab     = $_POST['tab']    ?? 'users';
$requestedPage    = max(1, (int)($_POST['page'] ?? 1));
$rowsPerPage      = 10;
$paginationOffset = ($requestedPage - 1) * $rowsPerPage;

$validTabs = ['users', 'colleges', 'courses', 'demographics'];
if (!in_array($requestedTab, $validTabs)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid tab specified.']);
    exit;
}

[$where, $boundParams] = buildWhereClauseFromFilters($_POST);
$visitLogs = fetchFilteredVisitLogs($where, $boundParams);


// ============================================================
//  ACTION SWITCH
// ============================================================

switch ($requestedAction) {

    case 'viewAll':

        switch ($requestedTab) {
            case 'users':        $pageData = buildViewAllUsers($visitLogs, $paginationOffset, $rowsPerPage);        break;
            case 'colleges':     $pageData = buildViewAllColleges($visitLogs, $paginationOffset, $rowsPerPage);     break;
            case 'courses':      $pageData = buildViewAllCourses($visitLogs, $paginationOffset, $rowsPerPage);      break;
            case 'demographics': $pageData = buildViewAllDemographics($visitLogs, $paginationOffset, $rowsPerPage); break;
            default:             $pageData = ['rows' => [], 'total' => 0];
        }

        $totalPages = $pageData['total'] > 0 ? (int)ceil($pageData['total'] / $rowsPerPage) : 1;

        echo json_encode([
            'status'     => 'success',
            'tableHtml'  => renderModalTable($requestedTab, $pageData['rows']),
            'pagination' => renderModalPagination($totalPages, $requestedPage),
            'total'      => $pageData['total'],
            'totalPages' => $totalPages,
            'page'       => $requestedPage,
        ]);
        break;


    case 'tab':
    default:

        $kpis                        = computeDashboardKpis($visitLogs, $_POST['endDate'] ?? '');
        $usersData                   = aggregateTopUsersByClassification($visitLogs);
        $classificationDistribution  = aggregateClassificationDistribution($visitLogs);
        $collegesData                = aggregateTopColleges($visitLogs);
        $collegeDistribution         = aggregateCollegeDistribution($visitLogs);
        $coursesData                 = aggregateTopCoursesByCollege($visitLogs);
        $sexCounts                   = aggregateSexDistribution($visitLogs);

        switch ($requestedTab) {
            case 'users':
                $tabHtml = renderUsersTab($usersData['topCheckins'], $usersData['topDuration']);
                break;
            case 'colleges':
                $tabHtml = renderCollegesTab($collegesData['top3CollegesCheckin'], $collegesData['top3CollegesDuration']);
                break;
            case 'courses':
                $tabHtml = renderCoursesTab($coursesData['topCoursesCheckin'], $coursesData['topCoursesDuration']);
                break;
            case 'demographics':
                $tabHtml = renderDemographicsTab($sexCounts, count($visitLogs));
                break;
        }

        echo json_encode([
            'status'                     => 'success',
            'html'                       => $tabHtml,

            // KPI metrics
            'totalVisits'                => $kpis['totalVisits'],
            'totalDuration'              => $kpis['totalDuration'],
            'avgDuration'                => $kpis['avgDuration'],
            'uniqueUsers'                => $kpis['uniqueUsers'],
            'endDateCheckins'            => $kpis['endDateCheckins'],

            // Chart data
            'topCheckins'                => $usersData['topCheckins'],
            'topDuration'                => $usersData['topDuration'],
            'classificationDistribution' => $classificationDistribution,
            'top3CollegesCheckin'        => $collegesData['top3CollegesCheckin'],
            'top3CollegesDuration'       => $collegesData['top3CollegesDuration'],
            'collegeDistribution'        => $collegeDistribution,
            'topCoursesCheckin'          => $coursesData['topCoursesCheckin'],
            'topCoursesDuration'         => $coursesData['topCoursesDuration'],
            'sexDistribution'            => $sexCounts,
        ]);
        break;
}
?>