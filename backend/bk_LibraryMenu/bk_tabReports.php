<?php
// Tab content + KPI sidebar data.
// Handlers compute only what they need; KPI sidebar is a reusable function.
include '../../db/dbconnection.php';
include 'bk_libReports.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']); exit;
}


//  Constants & simple helpers

const COLLEGE_COLOR_FALLBACK = 'rgba(139,92,246,0.88)';
const COLLEGE_COLOR_MAP = [
    'CAF' => 'rgba(22,163,74,0.88)',
    'CAS' => 'rgba(234,88,12,0.88)',
    'CBM' => 'rgba(202,138,4,0.88)',
    'CET' => 'rgba(220,38,38,0.88)',
    'CED' => 'rgba(37,99,235,0.88)',
    'CVM' => 'rgba(107,114,128,0.88)',
];



function resolveCollegeColor(string $collegeName): string
{
    $upperName = strtoupper($collegeName);
    foreach (COLLEGE_COLOR_MAP as $abbreviation => $color) {
        if (str_contains($upperName, $abbreviation)) return $color;
    }
    return COLLEGE_COLOR_FALLBACK;
}

function typeBadge(string $text): string
{
    return '<span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">'
         . htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8')
         . '</span>';
}


//  Row renderers for limited tables (used inside tab renderers)

function renderLogRows(array $flatLogs): array
{
    return array_map(fn($log) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">' . htmlspecialchars((string) ($log['id_number'] ?? ''),  ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($log['name'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($log['college'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($log['course'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td>' . typeBadge($log['classification'] ?: '—') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($log['library'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($log['sex'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($log['checkin_formatted'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($log['checkout_formatted'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($log['agency_organization'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-end pe-3">' . ($log['duration_minutes'] !== null ? (int) round($log['duration_minutes']) : '—')    . '</td>' .
        '</tr>',
    $flatLogs);
}

function renderCheckinRows(array $flatCheckins): array
{
    return array_map(fn($row) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">' . htmlspecialchars((string) ($row['display_label'] ?? ''),  ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($row['college'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($row['course'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td>' . typeBadge($row['type']) . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($row['library'] ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-end fw-semibold text-primary">' . number_format($row['count']) . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($row['agency_organization'] ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-end text-muted pe-3">' . htmlspecialchars((string) ($row['last_checkin'] ?? ''),  ENT_QUOTES, 'UTF-8') . '</td>' .
        '</tr>',
    $flatCheckins);
}

function renderDurationRows(array $flatDuration): array
{
    return array_map(fn($row) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">' . htmlspecialchars((string) ($row['display_label'] ?? ''),  ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($row['college'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($row['course'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td>' . typeBadge($row['type'])                                                              . '</td>' .
        '<td class="text-end fw-semibold text-success">'  . number_format($row['minutes']) . '</td>' .
        '<td class="text-muted pe-3">' . htmlspecialchars((string) ($row['agency_organization'] ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '</tr>',
    $flatDuration);
}


//  KPI sidebar rendering (common across all tabs)

function renderKpiSections(array $top3Students, array $top3Colleges, array $top3Courses): array
{
    $noData = '<div class="text-muted small fst-italic">No data</div>';

    // Row template (now uses rank and isLast instead of index)
    $kpiRow = fn(int $rank, bool $isLast, string $medal, string $leftHtml, string $rightHtml) =>
        '<div class="d-flex align-items-center justify-content-between gap-2 py-1 ' . ($isLast ? '' : 'border-bottom') . '">' .
            '<div class="d-flex align-items-center gap-2 min-w-0">' .
                '<span style="font-size:.9rem;flex-shrink:0;">' . $medal . '</span>' . $leftHtml .
            '</div>' .
            '<div class="d-flex flex-column align-items-end" style="flex-shrink:0;">' . $rightHtml . '</div>' .
        '</div>';

    // Medal helper: rank based
    $medal = function(int $rank, bool $tied): string {
        $symbols = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
        $medal = $symbols[$rank] ?? $rank . '.';
        return $tied
            ? $medal . '<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1" style="font-size:.55rem;vertical-align:middle;">tied</span>'
            : $medal;
    };

    // Helper to compute ranks for a sorted list (by count, descending)
    $computeRanks = function(array $items): array {
        $ranks = [];
        $prevCount = null;
        $currentRank = 0;
        foreach ($items as $item) {
            if ($item['count'] !== $prevCount) {
                $currentRank++;
            }
            $ranks[] = $currentRank;
            $prevCount = $item['count'];
        }
        return $ranks;
    };

    // ---- Top Students ----
    $studentsHtml = $noData;
    if (count($top3Students)) {
        $ranks = $computeRanks($top3Students);
        $lastRank = end($ranks);
        $studentsHtml = '';
        foreach ($top3Students as $idx => $student) {
            $isLast = ($ranks[$idx] === $lastRank && $idx === count($top3Students)-1);
            $studentsHtml .= $kpiRow(
                $ranks[$idx],
                $isLast,
                $medal($ranks[$idx], $student['tied']),
                '<div class="min-w-0">' .
                    '<div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">' . htmlspecialchars((string) ($student['id_number'] ?? ''), ENT_QUOTES, 'UTF-8') . '</div>' .
                    '<div class="text-muted" style="font-size:.68rem;">' . htmlspecialchars((string) ($student['college'] ?: '—'), ENT_QUOTES, 'UTF-8') . ($student['course'] ? ' · ' . htmlspecialchars((string) $student['course'], ENT_QUOTES, 'UTF-8') : '') . '</div>' .
                '</div>',
                '<span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold" style="font-size:.72rem;">' . number_format($student['count']) . '</span>' .
                '<span class="text-muted" style="font-size:.62rem;">check-ins</span>'
            );
        }
    }

    // ---- Top Colleges ----
    $collegesHtml = $noData;
    if (count($top3Colleges)) {
        $ranks = $computeRanks($top3Colleges);
        $lastRank = end($ranks);
        $collegesHtml = '';
        foreach ($top3Colleges as $idx => $college) {
            $isLast = ($ranks[$idx] === $lastRank && $idx === count($top3Colleges)-1);
            $collegesHtml .= $kpiRow(
                $ranks[$idx],
                $isLast,
                $medal($ranks[$idx], $college['tied']),
                '<div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">' . htmlspecialchars((string) ($college['name'] ?? ''), ENT_QUOTES, 'UTF-8') . '</div>',
                '<span class="badge rounded-pill bg-success-subtle text-success fw-semibold" style="font-size:.72rem;">' . number_format($college['count']) . '</span>' .
                '<span class="text-muted" style="font-size:.62rem;">students</span>'
            );
        }
    }

    // ---- Top Courses ----
    $coursesHtml = $noData;
    if (count($top3Courses)) {
        $ranks = $computeRanks($top3Courses);
        $lastRank = end($ranks);
        $coursesHtml = '';
        foreach ($top3Courses as $idx => $course) {
            $isLast = ($ranks[$idx] === $lastRank && $idx === count($top3Courses)-1);
            $coursesHtml .= $kpiRow(
                $ranks[$idx],
                $isLast,
                $medal($ranks[$idx], $course['tied']),
                '<div class="min-w-0">' .
                    '<div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">' . htmlspecialchars((string) ($course['course']  ?? ''), ENT_QUOTES, 'UTF-8') . '</div>' .
                    '<div style="font-size:.68rem;"><span class="badge rounded-pill bg-secondary-subtle text-secondary px-2 py-0">' . htmlspecialchars((string) ($course['college'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</span></div>' .
                '</div>',
                '<span class="badge rounded-pill bg-warning-subtle text-warning fw-semibold" style="font-size:.72rem;">' . number_format($course['count']) . '</span>' .
                '<span class="text-muted" style="font-size:.62rem;">students</span>'
            );
        }
    }

    return [
        'kpiStudentsHtml' => $studentsHtml,
        'kpiCollegesHtml' => $collegesHtml,
        'kpiCoursesHtml'  => $coursesHtml,
        'kpiLastUpdatedHtml' => '<i class="fas fa-sync-alt me-1"></i>Last updated: ' . date('g:i A'),
    ];
}


//  Common KPI computation (metrics + top 3 lists)

function getKpiData(array $logs, ?string $endDate): array
{
    $totalVisits = count($logs);
    $totalMinutes = 0.0;
    $uniqueUsers = [];
    $endDateCheckins = 0;

    // For top3 Students
    $studentCounts = [];
    $studentMeta = [];
    // For top3 Colleges (students only)
    $collegeUnique = [];
    // For top3 Courses (students only)
    $courseUnique = [];

    foreach ($logs as $log) {
        $uid = $log['id_number'];
        $uniqueUsers[$uid] = true;

        // Duration
        if ($log['checkout_time']) {
            $student = strtotime($log['checkin_time']);
            $employee = strtotime($log['checkout_time']);
            if ($student && $employee) $totalMinutes += ($employee - $student) / 60;
        }

        // End date check ins
        if ($endDate && substr($log['checkin_time'], 0, 10) === $endDate) {
            $endDateCheckins++;
        }

        // Student only aggregations
        if (strcasecmp($log['classification'] ?? '', 'student') === 0) {
            // For top students
            $studentCounts[$uid] = ($studentCounts[$uid] ?? 0) + 1;
            if (!isset($studentMeta[$uid])) {
                $studentMeta[$uid] = [
                    'id_number' => ($uid === '0') ? ($log['name'] ?? 'Guest') : $uid,
                    'college' => $log['college'] ?? '',
                    'course' => $log['course'] ?? '',
                ];
            }

            // For top colleges (unique students per college)
            $college = $log['college'] ?: 'Unknown';
            if (!isset($collegeUnique[$college][$uid])) {
                $collegeUnique[$college][$uid] = true;
            }

            // For top courses (college|course)
            $course = $log['course'] ?: 'Unknown';
            $key = "{$college}|{$course}";
            if (!isset($courseUnique[$key][$uid])) {
                $courseUnique[$key][$uid] = true;
            }
        }
    }

    // Build top3 students
    arsort($studentCounts);
    $topStudents = [];
    foreach (array_slice($studentCounts, 0, 3, true) as $uid => $count) {
        $topStudents[] = array_merge($studentMeta[$uid], ['count' => $count]);
    }

    // Build top3 colleges
    $collegeTotals = [];
    foreach ($collegeUnique as $college => $students) {
        $collegeTotals[$college] = count($students);
    }
    arsort($collegeTotals);
    $topColleges = [];
    foreach (array_slice($collegeTotals, 0, 3, true) as $college => $count) {
        $topColleges[] = ['name' => $college, 'count' => $count];
    }

    // Build top3 courses
    $courseTotals = [];
    foreach ($courseUnique as $key => $students) {
        [$college, $course] = explode('|', $key, 2);
        $courseTotals[$key] = ['college' => $college, 'course' => $course, 'count' => count($students)];
    }
    uasort($courseTotals, fn($a, $b) => $b['count'] <=> $a['count']);
    $topCourses = array_slice($courseTotals, 0, 3, true);
    $topCourses = array_values($topCourses);

    // Annotate ties
    $annotate = function(array $items, string $countKey) {
        $valueCounts = array_count_values(array_column($items, $countKey));
        return array_map(fn($item) => array_merge($item, ['tied' => $valueCounts[$item[$countKey]] > 1]), $items);
    };

    $topStudents = $annotate($topStudents, 'count');
    $topColleges = $annotate($topColleges, 'count');
    $topCourses  = $annotate($topCourses,  'count');

    $kpiSections = renderKpiSections($topStudents, $topColleges, $topCourses);

    $uniqueUsersCount = count($uniqueUsers);
    return [
        'totalVisits' => $totalVisits,
        'totalDuration' => round($totalMinutes),
        'uniqueUsers' => $uniqueUsersCount,
        'avgDuration' => $totalVisits ? round($totalMinutes / $totalVisits, 1) : 0,
        'endDateCheckins' => $endDateCheckins,
        'top3Students' => $topStudents,
        'top3Colleges' => $topColleges,
        'top3Courses' => $topCourses,
        'kpiStudentsHtml' => $kpiSections['kpiStudentsHtml'],
        'kpiCollegesHtml' => $kpiSections['kpiCollegesHtml'],
        'kpiCoursesHtml' => $kpiSections['kpiCoursesHtml'],
        'kpiLastUpdatedHtml' => $kpiSections['kpiLastUpdatedHtml'],
    ];
}


//  Tab HTML renderers

function renderLogsTab(array $flatLogs): string
{
    $rowsJson = htmlspecialchars(json_encode(renderLogRows($flatLogs)), ENT_QUOTES);
    ob_start(); ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-2 px-3">
            <div>
                <span class="fw-semibold small">All Visit Logs</span>
                <p class="text-muted mb-0" style="font-size:.72rem;">Every check-in within selected date range</p>
            </div>
        </div>
        <div class="card-body p-0"
             id="allLogsCard"
             data-rows="<?= $rowsJson ?>"
             data-per-page="10">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light"> <!-- kept light for logs, but you could change to table-dark if desired -->
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
                    </thead>
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

function renderUsersTab(array $topCheckins, array $topDuration, array $classificationDistribution, array $chartTopCheckins, array $chartTopDuration, array $courseChartData, array $flatUsers): string
{
    // Prepare flat arrays for the limited tables
    $flatCheckins = [];
    foreach ($topCheckins as $classification => $users) {
        foreach ($users as $user) {
            $flatCheckins[] = [
                'display_label' => $user['display_label'],
                'college' => $user['college'] ?: '—',
                'course' => $user['course'] ?: '—',
                'type' => $classification,
                'library' => $user['library'] ?? '—',
                'count' => $user['count'],
                'agency_organization' => $user['agency_organization'] ?? '—',
                'last_checkin' => date('M j', strtotime($user['last_checkin'])),
            ];
        }
    }
    usort($flatCheckins, fn($a, $b) => $b['count'] <=> $a['count']);

    $flatDuration = [];
    foreach ($topDuration as $classification => $users) {
        foreach ($users as $user) {
            $flatDuration[] = [
                'display_label' => $user['display_label'],
                'college' => $user['college'] ?: '—',
                'course' => $user['course'] ?: '—',
                'type' => $classification,
                'minutes' => (int) round($user['minutes']),
                'agency_organization' => $user['agency_organization'] ?? '—',
            ];
        }
    }
    usort($flatDuration, fn($a, $b) => $b['minutes'] <=> $a['minutes']);

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
    <script>
        // Pass chart data to JavaScript
        window.chartTopCheckins = <?= json_encode($chartTopCheckins) ?>;
        window.chartTopDuration = <?= json_encode($chartTopDuration) ?>;
        window.courseChartData = <?= json_encode($courseChartData) ?>;
        window.classificationDistribution = <?= json_encode($classificationDistribution) ?>;
    </script>
    <?php return ob_get_clean();
}

function renderCollegesTab(array $topByCheckins, array $topByDuration): string
{
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
                            <td class="fw-semibold"><?= htmlspecialchars((string) $collegeName, ENT_QUOTES, 'UTF-8') ?></td>
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

function renderCoursesTab(array $topByCheckins, array $topByDuration, array $courseChartData): string
{
    // Flatten for tables
    $flatten = function(array $data): array {
        $rows = [];
        foreach ($data as $college => $courses) {
            foreach ($courses as $course => $courseData) {
                $rows[] = array_merge(['college' => $college, 'course' => $course], $courseData);
            }
        }
        return $rows;
    };
    $flatCheckins = $flatten($topByCheckins);
    usort($flatCheckins, fn($a, $b) => $b['count'] <=> $a['count']);

    $flatDuration = $flatten($topByDuration);
    usort($flatDuration, fn($a, $b) => $b['minutes'] <=> $a['minutes']);

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
                            <td class="text-muted"><?= htmlspecialchars((string) $row['college'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars((string) $row['course'],  ENT_QUOTES, 'UTF-8') ?></td>
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
    <script>window.courseChartData = <?= json_encode($courseChartData) ?>;</script>
    <?php return ob_get_clean();
}

function renderDemographicsTab(array $sexDistribution, int $totalVisitors): string
{
    $sexBreakdown = [
        'Male' => ['icon' => 'bi-gender-male', 'bg' => 'info', 'count' => $sexDistribution['Male'] ?? 0],
        'Female' => ['icon' => 'bi-gender-female', 'bg' => 'danger', 'count' => $sexDistribution['Female'] ?? 0],
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
    <script>window.sexDistribution = <?= json_encode($sexDistribution) ?>;</script>
    <?php return ob_get_clean();
}


//  Handlers

function TabLogs(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $visitLogs = fetchVisitLogs($whereClause, $queryParams);
    $kpi = getKpiData($visitLogs, $_POST['endDate'] ?? null);

    // Flatten logs for the tab display
    $flatLogs = array_map(function ($logEntry) {

        $checkoutTime = $logEntry['checkout_time'] ?? null;

        $checkinTimestamp = $checkoutTime ? strtotime($logEntry['checkin_time']) : 0;
        $checkoutTimestamp = $checkoutTime ? strtotime($checkoutTime) : 0;

        return [
            'id_number' => $logEntry['id_number'] ?? '',
            'name' => $logEntry['name'] ?? '',
            'college' => $logEntry['college'] ?? '',
            'course' => $logEntry['course'] ?? '',
            'classification' => $logEntry['classification'] ?? '',
            'library' => $logEntry['library_section_name'] ?? '',
            'sex' => $logEntry['sex'] ?? '',
            'checkin_time' => $logEntry['checkin_time'] ?? '',
            'checkout_time' => $checkoutTime,
            'agency_organization' => $logEntry['agency_organization'] ?? '',

            'duration_minutes' => ($checkoutTime && $checkinTimestamp && $checkoutTimestamp)
                ? ($checkoutTimestamp - $checkinTimestamp) / 60
                : 0.0,

            'checkin_formatted' => date('M j, Y g:i A', strtotime($logEntry['checkin_time'])),

            'checkout_formatted' => $checkoutTime
                ? date('M j, Y g:i A', strtotime($checkoutTime))
                : '—',
        ];

    }, $visitLogs);

    echo json_encode(array_merge(
        [
            'status' => 'success',
            'html' => renderLogsTab($flatLogs),
            'flatLogs' => $flatLogs
        ],
        $kpi
    ));
    exit;
}

function TabUsers(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $visitLogs = fetchVisitLogs($whereClause, $queryParams);
    $kpi = getKpiData($visitLogs, $_POST['endDate'] ?? null);

    // Classification distribution (for the donut chart)
    $classificationDistribution = [];
    foreach ($visitLogs as $logEntry) {
        $classification = $logEntry['classification'] ?? 'Unknown';
        $classificationDistribution[$classification] = ($classificationDistribution[$classification] ?? 0) + 1;
    }

    // Aggregate per user
    $usersById = [];
    foreach ($visitLogs as $logEntry) {
        $uid = $logEntry['id_number'];

        if (!isset($usersById[$uid])) {
            $usersById[$uid] = [
                'display_label' => ($uid === '0') ? ($logEntry['name'] ?? 'Guest') : $uid,
                'name' => $logEntry['name'] ?? '',
                'college' => $logEntry['college'] ?? '',
                'course' => $logEntry['course'] ?? '',
                'type' => $logEntry['classification'],
                'library' => $logEntry['library_section_name'] ?? '—',
                'agency_organization' => $logEntry['agency_organization'] ?? '—',
                'checkins' => 0,
                'duration' => 0.0,
                'last_checkin' => $logEntry['checkin_time'],
            ];
        }

        $usersById[$uid]['checkins']++;

        if ($logEntry['checkout_time']) {
            $checkinTimestamp = strtotime($logEntry['checkin_time']);
            $checkoutTimestamp = strtotime($logEntry['checkout_time']);

            $usersById[$uid]['duration'] += ($checkinTimestamp && $checkoutTimestamp)
                ? ($checkoutTimestamp - $checkinTimestamp) / 60
                : 0.0;
        }

        if ($logEntry['checkin_time'] > $usersById[$uid]['last_checkin']) {
            $usersById[$uid]['last_checkin'] = $logEntry['checkin_time'];
        }
    }

    // Split by classification
    $usersGroupedByType = [];
    foreach ($usersById as $uid => $userRecord) {
        $userType = $userRecord['type'];
        $usersGroupedByType[$userType][] = $userRecord;
    }

    $topCheckins = [];
    $topDuration = [];

    foreach ($usersGroupedByType as $userType => $usersList) {

        // Top check-ins
        usort($usersList, fn($userA, $userB) => $userB['checkins'] <=> $userA['checkins']);

        $topCheckins[$userType] = array_map(function ($userRecord) {
            return [
                'display_label' => $userRecord['display_label'],
                'name' => $userRecord['name'],
                'college' => $userRecord['college'],
                'course' => $userRecord['course'],
                'type' => $userRecord['type'],
                'library' => $userRecord['library'],
                'agency_organization' => $userRecord['agency_organization'],
                'count' => $userRecord['checkins'],
                'last_checkin' => $userRecord['last_checkin'],
            ];
        }, array_slice($usersList, 0, 3));

        // Top duration
        usort($usersList, fn($userA, $userB) => $userB['duration'] <=> $userA['duration']);

        $topDuration[$userType] = array_map(function ($userRecord) {
            return [
                'display_label' => $userRecord['display_label'],
                'name' => $userRecord['name'],
                'college' => $userRecord['college'],
                'course' => $userRecord['course'],
                'type' => $userRecord['type'],
                'library' => $userRecord['library'],
                'agency_organization' => $userRecord['agency_organization'],
                'minutes' => (int) round($userRecord['duration']),
                'last_checkin' => $userRecord['last_checkin'],
            ];
        }, array_slice($usersList, 0, 3));
    }

    // Prepare flat users for export
    $flatUsers = array_values($usersById);

    foreach ($flatUsers as &$userRecord) {
        $userRecord['last_checkin_formatted'] = date(
            'M j, Y g:i A',
            strtotime($userRecord['last_checkin'])
        );
    }
    unset($userRecord);

    // Chart data (top 3 overall)
    $allUsersList = array_values($usersById);

    usort($allUsersList, fn($userA, $userB) => $userB['checkins'] <=> $userA['checkins']);
    $chartTopCheckins = array_map(
        fn($userRecord) => ['label' => $userRecord['display_label'], 'value' => $userRecord['checkins']],
        array_slice($allUsersList, 0, 3)
    );

    usort($allUsersList, fn($userA, $userB) => $userB['duration'] <=> $userA['duration']);
    $chartTopDuration = array_map(
        fn($userRecord) => ['label' => $userRecord['display_label'], 'value' => round($userRecord['duration'])],
        array_slice($allUsersList, 0, 3)
    );

    // Course chart data
    $courseAggregates = [];

    foreach ($visitLogs as $logEntry) {
        if (strcasecmp($logEntry['classification'] ?? '', 'student') !== 0) continue;

        $collegeName = $logEntry['college'] ?: 'Unknown';
        $courseName  = $logEntry['course']  ?: 'Unknown';
        $courseKey = "{$collegeName}|{$courseName}";

        if (!isset($courseAggregates[$courseKey])) {
            $courseAggregates[$courseKey] = [
                'label' => "{$collegeName} · {$courseName}",
                'checkins' => 0,
                'duration' => 0.0
            ];
        }

        $courseAggregates[$courseKey]['checkins']++;

        if ($logEntry['checkout_time']) {
            $checkinTimestamp = strtotime($logEntry['checkin_time']);
            $checkoutTimestamp = strtotime($logEntry['checkout_time']);

            $courseAggregates[$courseKey]['duration'] += ($checkinTimestamp && $checkoutTimestamp)
                ? ($checkoutTimestamp - $checkinTimestamp) / 60
                : 0.0;
        }
    }

    usort($courseAggregates, fn($courseA, $courseB) => $courseB['checkins'] <=> $courseA['checkins']);

    $courseChartData = array_map(
        fn($courseRecord) => [
            'label' => $courseRecord['label'],
            'checkins' => $courseRecord['checkins'],
            'duration' => round($courseRecord['duration'])
        ],
        array_slice($courseAggregates, 0, 3)
    );

    $html = renderUsersTab(
        $topCheckins,
        $topDuration,
        $classificationDistribution,
        $chartTopCheckins,
        $chartTopDuration,
        $courseChartData,
        $flatUsers
    );

    echo json_encode(array_merge(
        [
            'status' => 'success',
            'html' => $html,
            'classificationDistribution' => $classificationDistribution,
            'chartTopCheckins' => $chartTopCheckins,
            'chartTopDuration' => $chartTopDuration,
            'courseChartData' => $courseChartData,
            'flatUsers' => $flatUsers,
        ],
        $kpi
    ));
    exit;
}

function TabColleges(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $visitLogs = fetchVisitLogs($whereClause, $queryParams);
    $kpi = getKpiData($visitLogs, $_POST['endDate'] ?? null);

    // Student logs only
    $studentVisitLogs = array_filter(
        $visitLogs,
        fn($logEntry) => strcasecmp($logEntry['classification'] ?? '', 'student') === 0
    );

    // Compute college data
    $collegeAggregates = [];

    foreach ($studentVisitLogs as $logEntry) {

        $collegeName = $logEntry['college'] ?: 'Unknown';

        if (!isset($collegeAggregates[$collegeName])) {
            $collegeAggregates[$collegeName] = [
                'unique_students' => [],
                'duration'        => 0.0,
                'last_checkin'    => $logEntry['checkin_time'],
            ];
        }

        $collegeAggregates[$collegeName]['unique_students'][$logEntry['id_number']] = true;

        if ($logEntry['checkout_time']) {
            $checkinTimestamp  = strtotime($logEntry['checkin_time']);
            $checkoutTimestamp = strtotime($logEntry['checkout_time']);

            $collegeAggregates[$collegeName]['duration'] += ($checkinTimestamp && $checkoutTimestamp)
                ? ($checkoutTimestamp - $checkinTimestamp) / 60
                : 0.0;
        }

        if ($logEntry['checkin_time'] > $collegeAggregates[$collegeName]['last_checkin']) {
            $collegeAggregates[$collegeName]['last_checkin'] = $logEntry['checkin_time'];
        }
    }

    // Top by check-ins (unique visitors)
    $topCollegesByCheckins = [];

    foreach ($collegeAggregates as $collegeName => $collegeData) {
        $topCollegesByCheckins[$collegeName] = [
            'count'        => count($collegeData['unique_students']),
            'last_checkin' => $collegeData['last_checkin'],
            'color'        => resolveCollegeColor($collegeName),
        ];
    }

    arsort($topCollegesByCheckins);
    $topCollegesByCheckins = array_slice($topCollegesByCheckins, 0, 3, true);

    // Top by duration
    $topCollegesByDuration = [];

    foreach ($collegeAggregates as $collegeName => $collegeData) {
        $topCollegesByDuration[$collegeName] = [
            'minutes'      => $collegeData['duration'],
            'last_checkin' => $collegeData['last_checkin'],
            'color'        => resolveCollegeColor($collegeName),
        ];
    }

    arsort($topCollegesByDuration);
    $topCollegesByDuration = array_slice($topCollegesByDuration, 0, 3, true);

    // Flat colleges for export
    $flatColleges = [];

    foreach ($collegeAggregates as $collegeName => $collegeData) {
        $flatColleges[] = [
            'name'         => $collegeName,
            'visitors'     => count($collegeData['unique_students']),
            'duration'     => round($collegeData['duration']),
            'last_checkin' => date('M j, Y g:i A', strtotime($collegeData['last_checkin'])),
        ];
    }

    usort($flatColleges, fn($collegeA, $collegeB) => $collegeB['visitors'] <=> $collegeA['visitors']);

    $html = renderCollegesTab($topCollegesByCheckins, $topCollegesByDuration);

    echo json_encode(array_merge(
        [
            'status' => 'success',
            'html' => $html,
            'top3CollegesCheckin' => $topCollegesByCheckins,
            'top3CollegesDuration' => $topCollegesByDuration,
            'flatColleges' => $flatColleges,
        ],
        $kpi
    ));
    exit;
}

function TabCourses(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $visitLogs = fetchVisitLogs($whereClause, $queryParams);
    $kpi = getKpiData($visitLogs, $_POST['endDate'] ?? null);

    $studentVisitLogs = array_filter(
        $visitLogs,
        fn($logEntry) => strcasecmp($logEntry['classification'] ?? '', 'student') === 0
    );

    $courseAggregates = [];

    foreach ($studentVisitLogs as $logEntry) {

        $collegeName = $logEntry['college'] ?: 'Unknown';
        $courseName  = $logEntry['course']  ?: 'Unknown';
        $courseKey   = "{$collegeName}|{$courseName}";

        if (!isset($courseAggregates[$courseKey])) {
            $courseAggregates[$courseKey] = [
                'college' => $collegeName,
                'course' => $courseName,
                'unique_students' => [],
                'duration' => 0.0,
                'last_checkin' => $logEntry['checkin_time'],
            ];
        }

        $courseAggregates[$courseKey]['unique_students'][$logEntry['id_number']] = true;

        if ($logEntry['checkout_time']) {
            $checkinTimestamp  = strtotime($logEntry['checkin_time']);
            $checkoutTimestamp = strtotime($logEntry['checkout_time']);

            $courseAggregates[$courseKey]['duration'] += ($checkinTimestamp && $checkoutTimestamp)
                ? ($checkoutTimestamp - $checkinTimestamp) / 60
                : 0.0;
        }

        if ($logEntry['checkin_time'] > $courseAggregates[$courseKey]['last_checkin']) {
            $courseAggregates[$courseKey]['last_checkin'] = $logEntry['checkin_time'];
        }
    }

    // Top by check-ins (unique visitors)
    $topCoursesByCheckins = [];

    foreach ($courseAggregates as $courseKey => $courseData) {
        $topCoursesByCheckins[$courseData['college']][$courseData['course']] = [
            'count' => count($courseData['unique_students']),
            'last_checkin' => $courseData['last_checkin'],
        ];
    }

    // Top by duration
    $topCoursesByDuration = [];

    foreach ($courseAggregates as $courseKey => $courseData) {
        $topCoursesByDuration[$courseData['college']][$courseData['course']] = [
            'minutes' => $courseData['duration'],
            'last_checkin' => $courseData['last_checkin'],
        ];
    }

    // Course chart data (top 3 by check-ins)
    $allCoursesList = [];

    foreach ($courseAggregates as $courseData) {
        $allCoursesList[] = [
            'label' => "{$courseData['college']} · {$courseData['course']}",
            'checkins' => count($courseData['unique_students']),
            'duration' => round($courseData['duration']),
        ];
    }

    usort($allCoursesList, fn($courseA, $courseB) => $courseB['checkins'] <=> $courseA['checkins']);
    $courseChartData = array_slice($allCoursesList, 0, 3);

    // Flat courses (all, sorted by check-ins)
    $flatCourses = [];

    foreach ($courseAggregates as $courseData) {
        $flatCourses[] = [
            'college' => $courseData['college'],
            'course' => $courseData['course'],
            'visitors' => count($courseData['unique_students']),
            'duration' => round($courseData['duration']),
            'last_checkin' => date('M j, Y g:i A', strtotime($courseData['last_checkin'])),
        ];
    }

    usort($flatCourses, fn($courseA, $courseB) => $courseB['visitors'] <=> $courseA['visitors']);

    $html = renderCoursesTab($topCoursesByCheckins, $topCoursesByDuration, $courseChartData);

    echo json_encode(array_merge(
        [
            'status' => 'success',
            'html' => $html,
            'topCoursesCheckin' => $topCoursesByCheckins,
            'topCoursesDuration' => $topCoursesByDuration,
            'courseChartData' => $courseChartData,
            'flatCourses' => $flatCourses,
        ],
        $kpi
    ));
    exit;
}

function TabDemographics(): void 
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);
    $visitLogs = fetchVisitLogs($whereClause, $queryParams);
    $kpi = getKpiData($visitLogs, $_POST['endDate'] ?? null);

    // Sex distribution
    $sexDistribution = [];

    foreach ($visitLogs as $logEntry) {
        $sex = $logEntry['sex'] ?: 'Unknown';
        $sexDistribution[$sex] = ($sexDistribution[$sex] ?? 0) + 1;
    }

    // Aggregated demographics rows for export
    $totalLogs = count($visitLogs);
    $flatDemographics = [];

    foreach ($sexDistribution as $sex => $count) {
        $flatDemographics[] = [
            'sex'   => $sex,
            'count' => $count,
            'pct'   => $totalLogs ? round(($count / $totalLogs) * 100, 1) : 0,
        ];
    }

    // Flat logs for the "View All Logs" modal
    $flatLogs = array_map(function ($logEntry) {

        $checkoutTime = $logEntry['checkout_time'] ?? null;

        $checkinTimestamp  = $checkoutTime ? strtotime($logEntry['checkin_time']) : 0;
        $checkoutTimestamp = $checkoutTime ? strtotime($checkoutTime) : 0;

        return [
            'id_number' => $logEntry['id_number'] ?? '',
            'name' => $logEntry['name'] ?? '',
            'college' => $logEntry['college'] ?? '',
            'course' => $logEntry['course'] ?? '',
            'classification' => $logEntry['classification'] ?? '',
            'library' => $logEntry['library_section_name'] ?? '',
            'sex' => $logEntry['sex'] ?? '',
            'checkin_time' => $logEntry['checkin_time'] ?? '',
            'checkout_time' => $checkoutTime,
            'agency_organization' => $logEntry['agency_organization'] ?? '',

            'duration' => ($checkoutTime && $checkinTimestamp && $checkoutTimestamp)
                ? ($checkoutTimestamp - $checkinTimestamp) / 60
                : 0.0,
        ];

    }, $visitLogs);

    $html = renderDemographicsTab($sexDistribution, $totalLogs);

    echo json_encode(array_merge(
        [
            'status' => 'success',
            'html' => $html,
            'sexDistribution' => $sexDistribution,
            'flatDemographics' => $flatDemographics,
            'flatLogs' => $flatLogs,
        ],
        $kpi
    ));

    exit;
}

//  Dispatch

switch (trim($_POST['request'] ?? '')) {
    case 'getTabLogs':
        TabLogs();
        break;
    case 'getTabUsers':
        TabUsers();
        break;
    case 'getTabColleges':
        TabColleges();
        break;
    case 'getTabCourses':
        TabCourses();
        break;
    case 'getTabDemographics':
        TabDemographics();
        break;
    default: echo json_encode(['status' => 'error', 'message' => "Unknown request: '" . trim($_POST['request'] ?? '') . "'."]);
}