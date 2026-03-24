<?php
// Tab content + KPI sidebar data.
// Architecture: helpers → aggregators → top-N computers → payload builder → renderers → handlers → dispatch.
include '../../db/dbconnection.php';
include 'bk_libReports.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['status' => 'error', 'message' => 'Invalid request method.']);
}

const COLLEGE_COLOR_FALLBACK = 'rgba(139,92,246,0.88)';
const COLLEGE_COLOR_MAP = [
    'CAF' => 'rgba(22,163,74,0.88)',
    'CAS' => 'rgba(234,88,12,0.88)',
    'CBM' => 'rgba(202,138,4,0.88)',
    'CET' => 'rgba(220,38,38,0.88)',
    'CED' => 'rgba(37,99,235,0.88)',
    'CVM' => 'rgba(107,114,128,0.88)',
];

// ── HELPERS ───────────────────────────────────────────────────────────────────

function resolveCollegeColor(string $collegeName): string {
    $upperName = strtoupper($collegeName);
    foreach (COLLEGE_COLOR_MAP as $abbreviation => $color) {
        if (str_contains($upperName, $abbreviation)) return $color;
    }
    return COLLEGE_COLOR_FALLBACK;
}

/** Renders a pill badge. Mirrors the JS renderTypeBadge() that was on the frontend. */
function typeBadge(string $text): string {
    return '<span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">' . escHtml($text) . '</span>';
}

/**
 * Returns a rank medal (🥇🥈🥉) or rank number for the 0-based index,
 * with an optional "tied" badge. Mirrors the JS resolveRankMedal() that was on the frontend.
 */
function resolveRankMedal(int $index, bool $isTied): string {
    $medals = ['🥇', '🥈', '🥉'];
    $medal  = $medals[$index] ?? ($index + 1) . '.';
    return $isTied
        ? $medal . '<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1" style="font-size:.55rem;vertical-align:middle;">tied</span>'
        : $medal;
}

/**
 * Adds a `tied` flag to each item — true when two or more items share the same value.
 */
function annotateRanks(array $items, string $valueKey): array {
    $valueCounts = array_count_values(array_column($items, $valueKey));
    return array_map(
        fn($item) => array_merge($item, ['tied' => $valueCounts[$item[$valueKey]] > 1]),
        $items
    );
}

// ── ROW RENDERERS ─────────────────────────────────────────────────────────────
// Each function returns an array of pre-rendered <tr> HTML strings.
// The frontend paginateInlineTable() simply slices and joins them — no JS rendering.

function renderLogRows(array $flatLogs): array {
    return array_map(fn($log) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">'    . escHtml($log['id_number'])                                                              . '</td>' .
        '<td class="text-muted">'           . escHtml($log['name']                ?: '—')                                             . '</td>' .
        '<td class="text-muted">'           . escHtml($log['college']             ?: '—')                                             . '</td>' .
        '<td class="text-muted">'           . escHtml($log['course']              ?: '—')                                             . '</td>' .
        '<td>'                              . typeBadge($log['classification']     ?: '—')                                             . '</td>' .
        '<td class="text-muted">'           . escHtml($log['library']             ?: '—')                                             . '</td>' .
        '<td class="text-muted">'           . escHtml($log['sex']                 ?: '—')                                             . '</td>' .
        '<td class="text-muted">'           . escHtml($log['checkin_formatted']   ?: '—')                                             . '</td>' .
        '<td class="text-muted">'           . escHtml($log['checkout_formatted']  ?: '—')                                             . '</td>' .
        '<td class="text-muted">'           . escHtml($log['agency_organization'] ?: '—')                                             . '</td>' .
        '<td class="text-end pe-3">'        . ($log['duration_minutes'] !== null ? (int) round($log['duration_minutes']) : '—')       . '</td>' .
        '</tr>',
    $flatLogs);
}

function renderCheckinRows(array $flatCheckins): array {
    return array_map(fn($row) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">'                 . escHtml($row['display_label'])              . '</td>' .
        '<td class="text-muted">'                        . escHtml($row['college']             ?: '—') . '</td>' .
        '<td class="text-muted">'                        . escHtml($row['course']              ?: '—') . '</td>' .
        '<td>'                                           . typeBadge($row['type'])                      . '</td>' .
        '<td class="text-muted">'                        . escHtml($row['library']             ?? '—') . '</td>' .
        '<td class="text-end fw-semibold text-primary">' . number_format($row['count'])                . '</td>' .
        '<td class="text-muted">'                        . escHtml($row['agency_organization'] ?? '—') . '</td>' .
        '<td class="text-end text-muted pe-3">'          . escHtml($row['last_checkin'])               . '</td>' .
        '</tr>',
    $flatCheckins);
}

function renderDurationRows(array $flatDuration): array {
    return array_map(fn($row) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">'                  . escHtml($row['display_label'])              . '</td>' .
        '<td class="text-muted">'                         . escHtml($row['college']             ?: '—') . '</td>' .
        '<td class="text-muted">'                         . escHtml($row['course']              ?: '—') . '</td>' .
        '<td>'                                            . typeBadge($row['type'])                      . '</td>' .
        '<td class="text-end fw-semibold text-success">'  . number_format($row['minutes'])              . '</td>' .
        '<td class="text-muted pe-3">'                    . escHtml($row['agency_organization'] ?? '—') . '</td>' .
        '</tr>',
    $flatDuration);
}

// ── KPI SIDEBAR RENDERER ──────────────────────────────────────────────────────
// Mirrors updateKpi() that was on the frontend.
// Returns 4 HTML strings keyed to the exact element IDs the frontend injects into.

function renderKpiSections(array $shared): array {
    $noData = '<div class="text-muted small fst-italic">No data</div>';

    $kpiRow = fn(int $index, int $total, string $medal, string $leftHtml, string $rightHtml) =>
        '<div class="d-flex align-items-center justify-content-between gap-2 py-1 ' . ($index < $total - 1 ? 'border-bottom' : '') . '">' .
            '<div class="d-flex align-items-center gap-2 min-w-0">' .
                '<span style="font-size:.9rem;flex-shrink:0;">' . $medal . '</span>' . $leftHtml .
            '</div>' .
            '<div class="d-flex flex-column align-items-end" style="flex-shrink:0;">' . $rightHtml . '</div>' .
        '</div>';

    // ── Top Students ──────────────────────────────────────────────
    $students     = $shared['top3Students'];
    $studentsHtml = !count($students) ? $noData : implode('', array_map(
        fn($student, $index) => $kpiRow(
            $index, count($students),
            resolveRankMedal($index, $student['tied']),
            '<div class="min-w-0">' .
                '<div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">' . escHtml($student['id_number']) . '</div>' .
                '<div class="text-muted" style="font-size:.68rem;">' . escHtml($student['college'] ?: '—') . ($student['course'] ? ' · ' . escHtml($student['course']) : '') . '</div>' .
            '</div>',
            '<span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold" style="font-size:.72rem;">' . number_format($student['count']) . '</span>' .
            '<span class="text-muted" style="font-size:.62rem;">check-ins</span>'
        ),
        $students, array_keys($students)
    ));

    // ── Top Colleges ──────────────────────────────────────────────
    $colleges     = $shared['top3Colleges'];
    $collegesHtml = !count($colleges) ? $noData : implode('', array_map(
        fn($college, $index) => $kpiRow(
            $index, count($colleges),
            resolveRankMedal($index, $college['tied']),
            '<div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">' . escHtml($college['name']) . '</div>',
            '<span class="badge rounded-pill bg-success-subtle text-success fw-semibold" style="font-size:.72rem;">' . number_format($college['count']) . '</span>' .
            '<span class="text-muted" style="font-size:.62rem;">students</span>'
        ),
        $colleges, array_keys($colleges)
    ));

    // ── Top Courses ───────────────────────────────────────────────
    $courses     = $shared['top3Courses'];
    $coursesHtml = !count($courses) ? $noData : implode('', array_map(
        fn($course, $index) => $kpiRow(
            $index, count($courses),
            resolveRankMedal($index, $course['tied']),
            '<div class="min-w-0">' .
                '<div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">' . escHtml($course['course']) . '</div>' .
                '<div style="font-size:.68rem;"><span class="badge rounded-pill bg-secondary-subtle text-secondary px-2 py-0">' . escHtml($course['college'] ?: '—') . '</span></div>' .
            '</div>',
            '<span class="badge rounded-pill bg-warning-subtle text-warning fw-semibold" style="font-size:.72rem;">' . number_format($course['count']) . '</span>' .
            '<span class="text-muted" style="font-size:.62rem;">students</span>'
        ),
        $courses, array_keys($courses)
    ));

    return [
        'kpiStudentsHtml'    => $studentsHtml,
        'kpiCollegesHtml'    => $collegesHtml,
        'kpiCoursesHtml'     => $coursesHtml,
        'kpiLastUpdatedHtml' => '<i class="fas fa-sync-alt me-1"></i>Last updated: ' . date('g:i A'),
    ];
}

// ── AGGREGATORS ───────────────────────────────────────────────────────────────

function aggregateUsers(array $logs): array {
    $users = [];
    foreach ($logs as $log) {
        $userId = $log['id_number'];
        if (!isset($users[$userId])) {
            $users[$userId] = [
                'display_label'       => getUserDisplayLabel($log),
                'name'                => $log['name']                 ?? '',
                'college'             => $log['college']              ?? '',
                'course'              => $log['course']               ?? '',
                'type'                => $log['classification'],
                'library'             => $log['library_section_name'] ?? '—',
                'agency_organization' => $log['agency_organization']  ?? '—',
                'checkins'            => 0,
                'duration'            => 0.0,
                'last_checkin'        => $log['checkin_time'],
            ];
        }
        $users[$userId]['checkins']++;
        $users[$userId]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $users[$userId]['last_checkin']) {
            $users[$userId]['last_checkin'] = $log['checkin_time'];
        }
    }
    return array_map(
        fn($user) => array_merge($user, ['last_checkin_formatted' => formatDateTime($user['last_checkin'])]),
        array_values($users)
    );
}

function aggregateColleges(array $studentLogs): array {
    $colleges = [];
    foreach ($studentLogs as $log) {
        $college = $log['college'] ?: 'Unknown';
        if (!isset($colleges[$college])) {
            $colleges[$college] = ['name' => $college, 'unique_students' => [], 'duration' => 0.0, 'last_checkin' => $log['checkin_time']];
        }
        $colleges[$college]['unique_students'][$log['id_number']] = true;
        $colleges[$college]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $colleges[$college]['last_checkin']) {
            $colleges[$college]['last_checkin'] = $log['checkin_time'];
        }
    }
    return array_map(fn($data) => [
        'name'         => $data['name'],
        'visitors'     => count($data['unique_students']),
        'duration'     => round($data['duration']),
        'last_checkin' => formatDateTime($data['last_checkin']),
    ], array_values($colleges));
}

function aggregateCourses(array $studentLogs): array {
    $courses = [];
    foreach ($studentLogs as $log) {
        $college = $log['college'] ?: 'Unknown';
        $course  = $log['course']  ?: 'Unknown';
        $key     = "{$college}|{$course}";
        if (!isset($courses[$key])) {
            $courses[$key] = ['college' => $college, 'course' => $course, 'unique_students' => [], 'duration' => 0.0, 'last_checkin' => $log['checkin_time']];
        }
        $courses[$key]['unique_students'][$log['id_number']] = true;
        $courses[$key]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $courses[$key]['last_checkin']) {
            $courses[$key]['last_checkin'] = $log['checkin_time'];
        }
    }
    return array_map(fn($data) => [
        'college'      => $data['college'],
        'course'       => $data['course'],
        'visitors'     => count($data['unique_students']),
        'duration'     => round($data['duration']),
        'last_checkin' => formatDateTime($data['last_checkin']),
    ], array_values($courses));
}

function aggregateDemographics(array $logs): array {
    $sexCounts = [];
    foreach ($logs as $log) {
        $sex = $log['sex'] ?: 'Unknown';
        $sexCounts[$sex] = ($sexCounts[$sex] ?? 0) + 1;
    }
    $total  = array_sum($sexCounts);
    $result = [];
    foreach ($sexCounts as $sex => $count) {
        $result[] = ['sex' => $sex, 'count' => $count, 'pct' => $total ? round($count / $total * 100, 1) : 0];
    }
    return $result;
}

function flattenLogs(array $logs): array {
    return array_map(fn($log) => [
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
        'checkin_formatted'   => formatDateTime($log['checkin_time']),
        'checkout_formatted'  => $log['checkout_time'] ? formatDateTime($log['checkout_time']) : '—',
    ], $logs);
}

// ── TOP-N COMPUTERS ───────────────────────────────────────────────────────────

function computeTopUsers(array $logs): array {
    $topCheckins = [];
    $topDuration = [];

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

    return [$topCheckins, $topDuration];
}

function computeTopColleges(array $studentLogs): array {
    $seenStudents = $collegeCounts = $collegeDurations = $lastVisit = [];

    foreach ($studentLogs as $log) {
        $college   = $log['college'] ?: 'Unknown';
        $studentId = $log['id_number'];
        if (!isset($seenStudents[$college][$studentId])) {
            $seenStudents[$college][$studentId] = true;
            $collegeCounts[$college] = ($collegeCounts[$college] ?? 0) + 1;
        }
        $collegeDurations[$college] = ($collegeDurations[$college] ?? 0) + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if (!isset($lastVisit[$college]) || $log['checkin_time'] > $lastVisit[$college]) {
            $lastVisit[$college] = $log['checkin_time'];
        }
    }

    $topByCheckins = $topByDuration = [];
    arsort($collegeCounts);
    foreach (array_slice($collegeCounts, 0, 3, true) as $college => $count) {
        $topByCheckins[$college] = ['count' => $count, 'last_checkin' => $lastVisit[$college], 'color' => resolveCollegeColor($college)];
    }
    arsort($collegeDurations);
    foreach (array_slice($collegeDurations, 0, 3, true) as $college => $minutes) {
        $topByDuration[$college] = ['minutes' => $minutes, 'last_checkin' => $lastVisit[$college], 'color' => resolveCollegeColor($college)];
    }

    return [$topByCheckins, $topByDuration];
}

function computeTopCourses(array $studentLogs): array {
    $seenStudents = $courseCounts = $courseDurations = $lastVisit = [];

    foreach ($studentLogs as $log) {
        $college   = $log['college'] ?: 'Unknown';
        $course    = $log['course']  ?: 'Unknown';
        $studentId = $log['id_number'];
        $courseKey = "{$college}|{$course}";

        if (!isset($seenStudents[$college][$course][$studentId])) {
            $seenStudents[$college][$course][$studentId] = true;
            $courseCounts[$college][$course] = ($courseCounts[$college][$course] ?? 0) + 1;
        }
        $courseDurations[$college][$course] = ($courseDurations[$college][$course] ?? 0) + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if (!isset($lastVisit[$courseKey]) || $log['checkin_time'] > $lastVisit[$courseKey]) {
            $lastVisit[$courseKey] = $log['checkin_time'];
        }
    }

    $topByCheckins = $topByDuration = [];
    foreach ($courseCounts as $college => $courses) {
        arsort($courses);
        foreach (array_slice($courses, 0, 3, true) as $course => $total) {
            $topByCheckins[$college][$course] = ['count' => $total, 'last_checkin' => $lastVisit["{$college}|{$course}"] ?? null];
        }
    }
    foreach ($courseDurations as $college => $courses) {
        arsort($courses);
        foreach (array_slice($courses, 0, 3, true) as $course => $minutes) {
            $topByDuration[$college][$course] = ['minutes' => $minutes, 'last_checkin' => $lastVisit["{$college}|{$course}"] ?? null];
        }
    }

    return [$topByCheckins, $topByDuration];
}

// ── PAYLOAD BUILDER ───────────────────────────────────────────────────────────

/**
 * Single pass over logs — computes everything all tab handlers need.
 * Sex/classification distributions are computed inline (no buildDistributions() dependency).
 */
function buildSharedPayload(array $logs): array {
    $studentLogs = array_filter($logs, fn($log) => strcasecmp($log['classification'] ?? '', 'student') === 0);

    [$topCheckins, $topDuration]                  = computeTopUsers($logs);
    [$top3CollegesCheckin, $top3CollegesDuration] = computeTopColleges($studentLogs);
    [$topCoursesCheckin,   $topCoursesDuration]   = computeTopCourses($studentLogs);

    // ── KPI top-3s ─────────────────────────────────────────────────

    $top3Students = array_values(array_map(fn($user) => [
        'id_number' => $user['display_label'],
        'name'      => $user['name'],
        'college'   => $user['college'],
        'course'    => $user['course'],
        'count'     => $user['count'],
    ], $topCheckins['Student'] ?? []));

    $top3Colleges = array_map(
        fn($name, $data) => ['name' => $name, 'count' => $data['count']],
        array_keys($top3CollegesCheckin), array_values($top3CollegesCheckin)
    );

    $top3CoursesFlat = [];
    foreach ($topCoursesCheckin as $college => $courses) {
        foreach ($courses as $course => $data) {
            $top3CoursesFlat[] = ['college' => $college, 'course' => $course, 'count' => $data['count']];
        }
    }
    usort($top3CoursesFlat, fn($alpha, $bravo) => $bravo['count'] <=> $alpha['count']);

    // ── Chart arrays ───────────────────────────────────────────────

    $chartTopCheckins = [];
    foreach ($topCheckins as $users) {
        foreach ($users as $user) {
            $chartTopCheckins[] = ['label' => $user['display_label'], 'value' => $user['count']];
        }
    }
    usort($chartTopCheckins, fn($alpha, $bravo) => $bravo['value'] <=> $alpha['value']);

    $chartTopDuration = [];
    foreach ($topDuration as $users) {
        foreach ($users as $user) {
            $chartTopDuration[] = ['label' => $user['display_label'], 'value' => $user['minutes']];
        }
    }
    usort($chartTopDuration, fn($alpha, $bravo) => $bravo['value'] <=> $alpha['value']);

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

    // ── Distributions (inlined) ────────────────────────────────────

    $sexDistribution = $classificationDistribution = [];
    foreach ($logs as $log) {
        $sex            = $log['sex']            ?: 'Unknown';
        $classification = $log['classification'] ?: 'Unknown';
        $sexDistribution[$sex]                       = ($sexDistribution[$sex]                       ?? 0) + 1;
        $classificationDistribution[$classification] = ($classificationDistribution[$classification] ?? 0) + 1;
    }

    // ── KPIs ───────────────────────────────────────────────────────

    $totalVisits  = count($logs);
    $totalMinutes = array_sum(array_map(
        fn($log) => calcDurationMinutes($log['checkin_time'], $log['checkout_time']),
        $logs
    ));
    $endDate = trim($_POST['endDate'] ?? '');
    $kpis    = [
        'totalVisits'     => $totalVisits,
        'totalDuration'   => round($totalMinutes),
        'uniqueUsers'     => count(array_unique(array_column($logs, 'id_number'))),
        'avgDuration'     => $totalVisits ? round($totalMinutes / $totalVisits, 1) : 0,
        'endDateCheckins' => $endDate
            ? count(array_filter($logs, fn($log) => substr($log['checkin_time'], 0, 10) === $endDate))
            : 0,
    ];

    return [
        // Raw computed data (used by tab renderers)
        'topCheckins'                => $topCheckins,
        'topDuration'                => $topDuration,
        'top3CollegesCheckin'        => $top3CollegesCheckin,
        'top3CollegesDuration'       => $top3CollegesDuration,
        'topCoursesCheckin'          => $topCoursesCheckin,
        'topCoursesDuration'         => $topCoursesDuration,

        // Chart-ready arrays (sent to frontend for Chart.js)
        'chartTopCheckins'           => array_slice($chartTopCheckins, 0, 3),
        'chartTopDuration'           => array_slice($chartTopDuration, 0, 3),
        'courseChartData'            => $courseChartData,
        'sexDistribution'            => $sexDistribution,
        'classificationDistribution' => $classificationDistribution,

        // Annotated KPI top-3s (consumed by renderKpiSections)
        'top3Students'               => annotateRanks($top3Students, 'count'),
        'top3Colleges'               => annotateRanks($top3Colleges, 'count'),
        'top3Courses'                => annotateRanks(array_slice($top3CoursesFlat, 0, 3), 'count'),

        // KPI numeric values
        'kpis'                       => $kpis,

        // Flat arrays for Excel export
        'flatUsers'                  => aggregateUsers($logs),
        'flatColleges'               => aggregateColleges($studentLogs),
        'flatCourses'                => aggregateCourses($studentLogs),
        'flatDemographics'           => aggregateDemographics($logs),
        'flatLogs'                   => flattenLogs($logs),
    ];
}

/**
 * Returns the KPI fields every tab response must include — numeric KPIs + pre-rendered HTML.
 * Uses array_merge() — NOT the `...` spread operator — for PHP 8.0 compatibility.
 */
function kpiFields(array $shared): array {
    return array_merge($shared['kpis'], renderKpiSections($shared));
}

// ── TAB HTML RENDERERS ────────────────────────────────────────────────────────

function renderLogsTab(array $flatLogs): string {
    $rowsJson = htmlspecialchars(json_encode(renderLogRows($flatLogs)), ENT_QUOTES);
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
             data-rows="<?= $rowsJson ?>"
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

function renderUsersTab(array $topCheckins, array $topDuration): string {
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
    usort($flatCheckins, fn($alpha, $bravo) => $bravo['count'] <=> $alpha['count']);

    $flatDuration = [];
    foreach ($topDuration as $classification => $users) {
        foreach ($users as $user) {
            $flatDuration[] = [
                'display_label'       => $user['display_label'],
                'college'             => $user['college']             ?: '—',
                'course'              => $user['course']              ?: '—',
                'type'                => $classification,
                'minutes'             => (int) round($user['minutes']),
                'agency_organization' => $user['agency_organization'] ?? '—',
            ];
        }
    }
    usort($flatDuration, fn($alpha, $bravo) => $bravo['minutes'] <=> $alpha['minutes']);

    $checkinRowsJson  = htmlspecialchars(json_encode(renderCheckinRows($flatCheckins)),  ENT_QUOTES);
    $durationRowsJson = htmlspecialchars(json_encode(renderDurationRows($flatDuration)), ENT_QUOTES);

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
                     data-rows="<?= $checkinRowsJson ?>"
                     data-per-page="3">
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
                     data-rows="<?= $durationRowsJson ?>"
                     data-per-page="3">
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

function renderCollegesTab(array $topByCheckins, array $topByDuration): string {
    $panels = [
        ['title' => 'Top Colleges — Check-ins', 'subtitle' => 'Unique visitors per college',     'canvas' => 'chartCollegeCheckin',  'data' => $topByCheckins, 'valueKey' => 'count',   'label' => 'Visitors',       'valueClass' => 'text-primary', 'isCheckins' => true],
        ['title' => 'Top Colleges — Duration',   'subtitle' => 'Total session time per college', 'canvas' => 'chartCollegeDuration', 'data' => $topByDuration, 'valueKey' => 'minutes', 'label' => 'Duration (min)', 'valueClass' => 'text-success', 'isCheckins' => false],
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
                        <?php if ($panel['data']): foreach ($panel['data'] as $collegeName => $data): ?>
                        <tr>
                            <td class="fw-semibold"><?= escHtml($collegeName) ?></td>
                            <td class="text-end fw-semibold <?= $panel['valueClass'] ?>"><?= round($data[$panel['valueKey']]) ?></td>
                            <td class="text-end text-muted"><?= date('M j, Y', strtotime($data['last_checkin'])) ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if (!$panel['isCheckins']): ?>
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

function renderCoursesTab(array $topByCheckins, array $topByDuration): string {
    $flattenCourses = function (array $data): array {
        $rows = [];
        foreach ($data as $college => $courses) {
            foreach ($courses as $course => $courseData) {
                $rows[] = array_merge(['college' => $college, 'course' => $course], $courseData);
            }
        }
        return $rows;
    };

    $flatCheckins = $flattenCourses($topByCheckins);
    usort($flatCheckins, fn($alpha, $bravo) => $bravo['count'] <=> $alpha['count']);

    $flatDuration = $flattenCourses($topByDuration);
    usort($flatDuration, fn($alpha, $bravo) => $bravo['minutes'] <=> $alpha['minutes']);

    $panels = [
        ['title' => 'Check-ins', 'canvas' => 'chartCoursesCheckin',  'subtitle' => 'Unique visitors per course',    'valueKey' => 'count',   'columnLabel' => 'Visitors',       'rows' => $flatCheckins, 'showViewAll' => false],
        ['title' => 'Duration',  'canvas' => 'chartCoursesDuration', 'subtitle' => 'Total session time per course', 'valueKey' => 'minutes', 'columnLabel' => 'Duration (min)', 'rows' => $flatDuration, 'showViewAll' => true],
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
                            <th class="text-end"><?= $panel['columnLabel'] ?></th>
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
                    <?php if ($panel['showViewAll']): ?>
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

function renderDemographicsTab(array $sexDistribution, int $totalVisitors): string {
    $sexBreakdown = [
        'Male'    => ['icon' => 'bi-gender-male',    'bg' => 'info',      'count' => $sexDistribution['Male']    ?? 0],
        'Female'  => ['icon' => 'bi-gender-female',  'bg' => 'danger',    'count' => $sexDistribution['Female']  ?? 0],
        'Unknown' => ['icon' => 'bi-question-circle', 'bg' => 'secondary', 'count' => $sexDistribution['Unknown'] ?? 0],
    ];
    foreach ($sexBreakdown as &$data) {
        $data['pct'] = $totalVisitors ? round($data['count'] / $totalVisitors * 100, 1) : 0;
    }
    unset($data);

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

// ── HANDLERS ──────────────────────────────────────────────────────────────────

function TabLogs(): void {
    [$where, $params] = buildWhereClause($_POST);
    $logs   = fetchVisitLogs($where, $params);
    $shared = buildSharedPayload($logs);

    sendResponse(array_merge(
        ['status' => 'success', 'html' => renderLogsTab($shared['flatLogs']), 'flatLogs' => $shared['flatLogs']],
        kpiFields($shared)
    ));
}

function TabUsers(): void {
    [$where, $params] = buildWhereClause($_POST);
    $logs   = fetchVisitLogs($where, $params);
    $shared = buildSharedPayload($logs);

    sendResponse(array_merge(
        [
            'status'                     => 'success',
            'html'                       => renderUsersTab($shared['topCheckins'], $shared['topDuration']),
            'classificationDistribution' => $shared['classificationDistribution'],
            'chartTopCheckins'           => $shared['chartTopCheckins'],
            'chartTopDuration'           => $shared['chartTopDuration'],
            'courseChartData'            => $shared['courseChartData'],
            'flatUsers'                  => $shared['flatUsers'],
        ],
        kpiFields($shared)
    ));
}

function TabColleges(): void {
    [$where, $params] = buildWhereClause($_POST);
    $logs   = fetchVisitLogs($where, $params);
    $shared = buildSharedPayload($logs);

    sendResponse(array_merge(
        [
            'status'               => 'success',
            'html'                 => renderCollegesTab($shared['top3CollegesCheckin'], $shared['top3CollegesDuration']),
            'top3CollegesCheckin'  => $shared['top3CollegesCheckin'],
            'top3CollegesDuration' => $shared['top3CollegesDuration'],
            'flatColleges'         => $shared['flatColleges'],
        ],
        kpiFields($shared)
    ));
}

function TabCourses(): void {
    [$where, $params] = buildWhereClause($_POST);
    $logs   = fetchVisitLogs($where, $params);
    $shared = buildSharedPayload($logs);

    sendResponse(array_merge(
        [
            'status'             => 'success',
            'html'               => renderCoursesTab($shared['topCoursesCheckin'], $shared['topCoursesDuration']),
            'topCoursesCheckin'  => $shared['topCoursesCheckin'],
            'topCoursesDuration' => $shared['topCoursesDuration'],
            'courseChartData'    => $shared['courseChartData'],
            'flatCourses'        => $shared['flatCourses'],
        ],
        kpiFields($shared)
    ));
}

function TabDemographics(): void {
    [$where, $params] = buildWhereClause($_POST);
    $logs   = fetchVisitLogs($where, $params);
    $shared = buildSharedPayload($logs);

    sendResponse(array_merge(
        [
            'status'           => 'success',
            'html'             => renderDemographicsTab($shared['sexDistribution'], count($shared['flatLogs'])),
            'sexDistribution'  => $shared['sexDistribution'],
            'flatDemographics' => $shared['flatDemographics'],
            'flatLogs'         => $shared['flatLogs'],
        ],
        kpiFields($shared)
    ));
}

// ── DISPATCH ──────────────────────────────────────────────────────────────────

switch (trim($_POST['request'] ?? '')) {
    case 'getTabLogs':         TabLogs();         break;
    case 'getTabUsers':        TabUsers();        break;
    case 'getTabColleges':     TabColleges();     break;
    case 'getTabCourses':      TabCourses();      break;
    case 'getTabDemographics': TabDemographics(); break;
    default: sendResponse(['status' => 'error', 'message' => "Unknown request: '" . trim($_POST['request'] ?? '') . "'."]);
}