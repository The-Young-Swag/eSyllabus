<?php
/**
 * Library Analytics - Backend Handler
 * Handles tab rendering and viewAll paginated data for the Library Reports dashboard.
 */

include "../../db/dbconnection.php";
header('Content-Type: application/json');

// ============================================================
//  DISPLAY CONFIGURATION
//  To switch what identifies a user across all tables and charts,
//  change USER_DISPLAY_FIELD to any column available in Library_logs.
//  e.g. 'name', 'id_number', 'email', etc.
// ============================================================

define('USER_DISPLAY_FIELD', 'id_number');


// ============================================================
//  UTILITY FUNCTIONS
// ============================================================

/**
 * Calculates the duration in minutes between a check-in and check-out time.
 */
function calcDurationMinutes(string $checkinTime, ?string $checkoutTime): float
{
    if (!$checkoutTime) return 0;
    return (strtotime($checkoutTime) - strtotime($checkinTime)) / 60;
}

/**
 * Filters logs to only those matching the given classification (case-insensitive).
 */
function filterByClassification(array $logs, string $classification): array
{
    return array_filter(
        $logs,
        fn($log) => strtolower($log['classification']) === strtolower($classification)
    );
}

/**
 * Filters out guest logs (used for college/course aggregation).
 */
function excludeGuests(array $logs): array
{
    return array_filter(
        $logs,
        fn($log) => strtolower($log['classification']) !== 'guest'
    );
}

/**
 * Formats a datetime string into a human-readable format: "Jan 1, 2024 1:00 PM"
 */
function formatDateTime(string $datetime): string
{
    return date('M j, Y g:i A', strtotime($datetime));
}

/**
 * Safely escapes a value for HTML output.
 */
function safe(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Returns the display label for a log entry based on USER_DISPLAY_FIELD.
 * Change USER_DISPLAY_FIELD at the top of this file to switch what is shown.
 */
function getUserDisplayLabel(array $log): string
{
    return $log[USER_DISPLAY_FIELD] ?? $log['id_number'];
}


// ============================================================
//  QUERY FUNCTIONS
// ============================================================

/**
 * Builds the SQL WHERE clause and named parameter map from the active dashboard filters.
 */
function buildWhereClauseFromFilters(array $postData): array
{
    $where = '';
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

/**
 * Fetches all library visit logs matching the active filters,
 * joined with their library section names for display.
 */
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

/**
 * Computes the four global KPI metric values from the filtered visit logs.
 * Returns totals for visits, duration, unique users, average duration,
 * and the check-in count specifically on the selected end date.
 */
function computeDashboardKpis(array $visitLogs, string $selectedEndDate): array
{
    $totalVisitCount   = count($visitLogs);

    $totalDurationMins = array_sum(array_map(
        fn($log) => calcDurationMinutes($log['checkin_time'], $log['checkout_time']),
        $visitLogs
    ));

    $uniqueVisitorCount = count(array_unique(array_column($visitLogs, 'id_number')));

    $avgDurationMins    = $totalVisitCount
        ? round($totalDurationMins / $totalVisitCount, 1)
        : 0;

    $endDateVisitCount  = 0;
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

/**
 * Aggregates the top 3 users by visit count and total session duration,
 * grouped by classification (Student, Employee, Guest).
 */
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

        // Top 3 by visit count
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

        // Top 3 by total duration
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
 * Aggregates the top 3 colleges by unique visitor count and total session duration.
 * Guests are excluded — only Students and Employees are counted.
 */
function aggregateTopColleges(array $visitLogs): array
{
    $nonGuestLogs             = excludeGuests($visitLogs);
    $uniqueVisitorsByCollege  = [];
    $visitCountByCollege      = [];
    $durationSumByCollege     = [];
    $lastCheckinByCollege     = [];

    foreach ($nonGuestLogs as $log) {
        $college = $log['college'] ?: 'Unknown';
        $userId  = $log['id_number'];

        // Count each unique visitor only once per college
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

    // Build top 3 by visit count
    arsort($visitCountByCollege);
    $topByCheckins = [];
    $rank = 0;
    foreach ($visitCountByCollege as $college => $visitCount) {
        if ($rank >= 3) break;
        $topByCheckins[$college] = [
            'count'        => $visitCount,
            'last_checkin' => $lastCheckinByCollege[$college],
        ];
        $rank++;
    }

    // Build top 3 by total duration
    arsort($durationSumByCollege);
    $topByDuration = [];
    $rank = 0;
    foreach ($durationSumByCollege as $college => $totalMinutes) {
        if ($rank >= 3) break;
        $topByDuration[$college] = [
            'minutes'      => $totalMinutes,
            'last_checkin' => $lastCheckinByCollege[$college],
        ];
        $rank++;
    }

    return [
        'top3CollegesCheckin'  => $topByCheckins,
        'top3CollegesDuration' => $topByDuration,
    ];
}

/**
 * Aggregates the top 3 courses per college by unique visitor count and total session duration.
 * Guests are excluded — only Students and Employees are counted.
 */
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

        // Count each unique visitor only once per college + course combination
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

    // Build top 3 courses per college by visit count
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

    // Build top 3 courses per college by total duration
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

/**
 * Counts visit log entries grouped by sex (Male, Female, Unknown).
 */
function aggregateSexDistribution(array $visitLogs): array
{
    $countBySex = [];
    foreach ($visitLogs as $log) {
        $sex = $log['sex'] ?: 'Unknown';
        $countBySex[$sex] = ($countBySex[$sex] ?? 0) + 1;
    }
    return $countBySex;
}


// ============================================================
//  VIEWALL PAGINATED DATA BUILDERS
// ============================================================

/**
 * Builds paginated user rows for the View All modal, aggregated by id_number.
 * Each row represents one unique visitor with their cumulative stats.
 */
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

/**
 * Builds paginated college rows for the View All modal, aggregated by college name.
 * Each row shows the college's unique visitor count, total duration, and last visit.
 */
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

/**
 * Builds paginated course rows for the View All modal, aggregated by college + course.
 * Each row shows the course's unique visitor count, total duration, and last visit.
 */
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

/**
 * Builds paginated raw visit log rows for the demographics View All modal.
 * Each row is one individual visit entry with its duration calculated.
 */
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

/**
 * Renders the full paginated modal table HTML for a given tab and its row data.
 * Column headers and row cell mappings are defined per-tab in one central place,
 * making it easy to add, remove, or reorder columns with minimal changes.
 */
function renderModalTable(string $tab, array $rows): string
{
    $columnsByTab = [
        'users' => [
            'headers' => ['ID Number', 'Type', 'Library', 'Check-ins', 'Duration (min)', 'Last Check-in'],
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
            <table class=\"table table-sm table-striped\">
                <thead><tr>{$headerCells}</tr></thead>
                <tbody>{$bodyRows}</tbody>
            </table>
        </div>
    ";
}

/**
 * Renders Bootstrap pagination HTML for the View All modal.
 */
function renderModalPagination(int $totalPages, int $currentPage): string
{
    $pageItems = '';
    for ($pageNum = 1; $pageNum <= $totalPages; $pageNum++) {
        $activeClass = ($pageNum === $currentPage) ? 'active' : '';
        $pageItems  .= "<li class=\"page-item {$activeClass}\">
                            <a class=\"page-link\" href=\"#\" data-page=\"{$pageNum}\">{$pageNum}</a>
                        </li>";
    }

    return "<nav><ul class=\"pagination mb-0\">{$pageItems}</ul></nav>";
}


// ============================================================
//  TAB HTML RENDER FUNCTIONS
// ============================================================

/**
 * Renders the Users tab — top visitors by check-in count and session duration,
 * each grouped into classification sections (Student, Employee, Guest).
 */
function renderUsersTab(array $topByCheckins, array $topByDuration): string
{
    ob_start(); ?>
    <div class="tab-pane fade show active" id="users" role="tabpanel">
        <div class="row g-4">

            <!-- Top Users by Check-ins -->
            <div class="col-xl-8">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Top Users by Check-ins</div>
                            <small class="text-muted">Most frequent visitors</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height:300px;"><canvas id="chartUsersCheckin"></canvas></div>
                        <div class="table-responsive mt-4">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID Number</th>
                                        <th>Type</th>
                                        <th>Library</th>
                                        <th class="text-end">Check-ins</th>
                                        <th class="text-end">Last Check-in</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($topByCheckins as $classification => $usersInClass): ?>
                                    <?php foreach ($usersInClass as $userId => $userData): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= safe($userData['display_label']) ?></td>
                                            <td><span class="badge bg-light text-dark"><?= safe($classification) ?></span></td>
                                            <td><?= safe($userData['library']) ?></td>
                                            <td class="text-end"><?= $userData['count'] ?></td>
                                            <td class="text-end"><?= formatDateTime($userData['last_checkin']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-end">
                            <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="users">
                                View All Users
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Users by Duration -->
            <div class="col-xl-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <div class="fw-semibold">Top Users by Duration</div>
                        <small class="text-muted">Longest sessions</small>
                    </div>
                    <div class="card-body">
                        <div style="height:300px;"><canvas id="chartUsersDuration"></canvas></div>
                        <div class="table-responsive mt-4">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID Number</th>
                                        <th>Type</th>
                                        <th>Library</th>
                                        <th class="text-end">Duration (min)</th>
                                        <th class="text-end">Last Check-in</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($topByDuration as $classification => $usersInClass): ?>
                                    <?php foreach ($usersInClass as $userId => $userData): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= safe($userData['display_label']) ?></td>
                                            <td><span class="badge bg-light text-dark"><?= safe($classification) ?></span></td>
                                            <td><?= safe($userData['library']) ?></td>
                                            <td class="text-end"><?= round($userData['minutes']) ?></td>
                                            <td class="text-end"><?= formatDateTime($userData['last_checkin']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Colleges tab — top colleges by unique visitor count and total session duration.
 */
function renderCollegesTab(array $topByCheckins, array $topByDuration): string
{
    ob_start(); ?>
    <div class="row g-4 mb-4">

        <!-- Top Colleges by Check-ins -->
        <div class="col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header">Top Colleges by Check-ins</div>
                <div class="card-body">
                    <div style="height:300px;"><canvas id="chartCollegeCheckin"></canvas></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle">
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
                                    <td class="text-end"><?= formatDateTime($collegeData['last_checkin']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Colleges by Duration -->
        <div class="col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header">Top Colleges by Duration</div>
                <div class="card-body">
                    <div style="height:300px;"><canvas id="chartCollegeDuration"></canvas></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle">
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
                                    <td class="text-end"><?= formatDateTime($collegeData['last_checkin']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-end">
                        <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="colleges">
                            View All Colleges
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Courses tab — top courses per college by unique visitor count and session duration.
 * Each college gets its own card pair (check-ins + duration).
 */
function renderCoursesTab(array $topByCheckins, array $topByDuration): string
{
    ob_start();
    foreach ($topByCheckins as $collegeName => $topCoursesByCheckins):
        $safeCollegeId = preg_replace('/[^a-zA-Z0-9]/', '', $collegeName);
    ?>
    <div class="col-12 mb-4">
        <h6 class="fw-semibold"><?= safe($collegeName) ?></h6>
        <div class="row g-4">

            <!-- Top Courses by Check-ins -->
            <div class="col-md-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header">Top Courses by Check-ins</div>
                    <div class="card-body">
                        <div style="height:250px;">
                            <canvas id="chartCourseCheckin_<?= $safeCollegeId ?>"></canvas>
                        </div>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Course</th>
                                        <th class="text-end">Unique Visitors</th>
                                        <th class="text-end">Last Check-in</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($topCoursesByCheckins as $courseName => $courseData): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= safe($courseName) ?></td>
                                        <td class="text-end"><?= $courseData['count'] ?></td>
                                        <td class="text-end"><?= formatDateTime($courseData['last_checkin']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Courses by Duration -->
            <div class="col-md-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header">Top Courses by Duration</div>
                    <div class="card-body">
                        <div style="height:250px;">
                            <canvas id="chartCourseDuration_<?= $safeCollegeId ?>"></canvas>
                        </div>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Course</th>
                                        <th class="text-end">Duration (min)</th>
                                        <th class="text-end">Last Check-in</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (isset($topByDuration[$collegeName])): ?>
                                    <?php foreach ($topByDuration[$collegeName] as $courseName => $courseData): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= safe($courseName) ?></td>
                                            <td class="text-end"><?= round($courseData['minutes']) ?></td>
                                            <td class="text-end"><?= formatDateTime($courseData['last_checkin']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php endforeach; ?>
    <div class="text-end">
        <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="courses">View All Courses</button>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Demographics tab — sex distribution chart and total/male/female visitor counts.
 */
function renderDemographicsTab(array $countBySex, int $totalVisitCount): string
{
    ob_start(); ?>
    <div class="row g-4 mb-4">

        <!-- Sex Distribution Chart -->
        <div class="col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header">Sex Distribution</div>
                <div class="card-body">
                    <div style="height:300px;"><canvas id="chartSexCheckin"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="col-md-6">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted small">Total Visitors</div>
                            <h3 class="fw-bold mb-0"><?= $totalVisitCount ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted small">Male Visitors</div>
                            <h3 class="fw-bold mb-0"><?= $countBySex['Male'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted small">Female Visitors</div>
                            <h3 class="fw-bold mb-0"><?= $countBySex['Female'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
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

    // ----------------------------------------------------------
    // viewAll — Returns rendered modal HTML + pagination for a tab
    // ----------------------------------------------------------
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


    // ----------------------------------------------------------
    // tab — Returns rendered HTML + chart data for a dashboard tab
    // ----------------------------------------------------------
    case 'tab':
    default:

        $kpis         = computeDashboardKpis($visitLogs, $_POST['endDate'] ?? '');
        $usersData    = aggregateTopUsersByClassification($visitLogs);
        $collegesData = aggregateTopColleges($visitLogs);
        $coursesData  = aggregateTopCoursesByCollege($visitLogs);
        $sexCounts    = aggregateSexDistribution($visitLogs);

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
            'status'               => 'success',
            'html'                 => $tabHtml,

            // KPI metrics
            'totalVisits'          => $kpis['totalVisits'],
            'totalDuration'        => $kpis['totalDuration'],
            'avgDuration'          => $kpis['avgDuration'],
            'uniqueUsers'          => $kpis['uniqueUsers'],
            'endDateCheckins'      => $kpis['endDateCheckins'],

            // Chart data
            'topCheckins'          => $usersData['topCheckins'],
            'topDuration'          => $usersData['topDuration'],
            'top3CollegesCheckin'  => $collegesData['top3CollegesCheckin'],
            'top3CollegesDuration' => $collegesData['top3CollegesDuration'],
            'topCoursesCheckin'    => $coursesData['topCoursesCheckin'],
            'topCoursesDuration'   => $coursesData['topCoursesDuration'],
            'sexDistribution'      => $sexCounts,
        ]);
        break;
}
?>