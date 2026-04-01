<?php
/**
 * Tab content and KPI sidebar data.
 *
 * Each handler fetches only the logs it needs, delegates aggregation to
 * bk_libReports.php, and emits a single JSON response.
 */
include '../../db/dbconnection.php';
include 'bk_libReports.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']); exit;
}

// ---------------------------------------------------------------------------
//  College colour mapping
// ---------------------------------------------------------------------------

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
    $upper = strtoupper($collegeName);
    foreach (COLLEGE_COLOR_MAP as $abbreviation => $color) {
        if (str_contains($upper, $abbreviation)) return $color;
    }
    return COLLEGE_COLOR_FALLBACK;
}

// ---------------------------------------------------------------------------
//  Row renderers for client-side-paginated tables
// ---------------------------------------------------------------------------

function renderLogRows(array $logs): array
{
    return array_map(fn($log) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">'     . htmlspecialchars((string) ($log['id_number']          ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'            . htmlspecialchars((string) ($log['name']               ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'            . htmlspecialchars((string) ($log['college']            ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'            . htmlspecialchars((string) ($log['course']             ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td>'                               . getTypeBadge($log['classification'] ?: '—')                                         . '</td>' .
        '<td class="text-muted">'            . htmlspecialchars((string) ($log['library']            ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'            . htmlspecialchars((string) ($log['sex']                ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'            . htmlspecialchars((string) ($log['checkin_formatted']  ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'            . htmlspecialchars((string) ($log['checkout_formatted'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'            . htmlspecialchars((string) ($log['agency_organization']?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-end pe-3">'         . (int) round($log['duration_minutes'])                                               . '</td>' .
        '</tr>',
    $logs);
}

function renderCheckinRows(array $rows): array
{
    return array_map(fn($row) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">'                  . htmlspecialchars((string) ($row['display_label']       ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'                         . htmlspecialchars((string) ($row['college']             ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'                         . htmlspecialchars((string) ($row['course']              ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td>'                                            . getTypeBadge($row['type'])                                                            . '</td>' .
        '<td class="text-muted">'                         . htmlspecialchars((string) ($row['library']             ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-end fw-semibold text-primary">'  . number_format($row['count'])                                                         . '</td>' .
        '<td class="text-muted">'                         . htmlspecialchars((string) ($row['agency_organization'] ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-end text-muted pe-3">'           . htmlspecialchars((string) ($row['last_checkin']        ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
        '</tr>',
    $rows);
}

function renderDurationRows(array $rows): array
{
    return array_map(fn($row) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">'                 . htmlspecialchars((string) ($row['display_label']       ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'                        . htmlspecialchars((string) ($row['college']             ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">'                        . htmlspecialchars((string) ($row['course']              ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td>'                                           . getTypeBadge($row['type'])                                                            . '</td>' .
        '<td class="text-end fw-semibold text-success">' . number_format($row['minutes'])                                                        . '</td>' .
        '<td class="text-muted pe-3">'                   . htmlspecialchars((string) ($row['agency_organization'] ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '</tr>',
    $rows);
}

// ---------------------------------------------------------------------------
//  KPI sidebar
// ---------------------------------------------------------------------------

/** Marks each item 'tied' when its count is shared by at least one other item. */
function annotateTies(array $items): array
{
    $freq = array_count_values(array_column($items, 'count'));
    return array_map(fn($item) => array_merge($item, ['tied' => $freq[$item['count']] > 1]), $items);
}

function renderKpiSections(array $top3Students, array $top3Colleges, array $top3Courses): array
{
    $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
    $noData = '<div class="text-muted small fst-italic">No data</div>';

    $rankOf = function(array $items): array {
        $ranks = [];
        $prev  = null;
        $rank  = 0;
        foreach ($items as $item) {
            if ($item['count'] !== $prev) $rank++;
            $ranks[] = $rank;
            $prev    = $item['count'];
        }
        return $ranks;
    };

    $medalHtml = fn(int $rank, bool $tied): string =>
        ($medals[$rank] ?? "{$rank}.")
        . ($tied ? '<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1" style="font-size:.55rem;vertical-align:middle;">tied</span>' : '');

    $row = fn(string $medal, string $left, string $right, bool $isLast): string =>
        '<div class="d-flex align-items-center justify-content-between gap-2 py-1 ' . ($isLast ? '' : 'border-bottom') . '">' .
            '<div class="d-flex align-items-center gap-2 min-w-0">' .
                '<span style="font-size:.9rem;flex-shrink:0;">' . $medal . '</span>' . $left .
            '</div>' .
            '<div class="d-flex flex-column align-items-end" style="flex-shrink:0;">' . $right . '</div>' .
        '</div>';

    // Students
    $studentsHtml = $noData;
    if ($top3Students) {
        $ranks        = $rankOf($top3Students);
        $studentsHtml = '';
        foreach ($top3Students as $i => $s) {
            $left  = '<div class="min-w-0">'
                   . '<div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">' . htmlspecialchars($s['id_number'], ENT_QUOTES, 'UTF-8') . '</div>'
                   . '<div class="text-muted" style="font-size:.68rem;">' . htmlspecialchars($s['college'] ?: '—', ENT_QUOTES, 'UTF-8') . ($s['course'] ? ' · ' . htmlspecialchars($s['course'], ENT_QUOTES, 'UTF-8') : '') . '</div>'
                   . '</div>';
            $right = '<span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold" style="font-size:.72rem;">' . number_format($s['count']) . '</span>'
                   . '<span class="text-muted" style="font-size:.62rem;">check-ins</span>';
            $studentsHtml .= $row($medalHtml($ranks[$i], $s['tied']), $left, $right, $i === count($top3Students) - 1);
        }
    }

    // Colleges
    $collegesHtml = $noData;
    if ($top3Colleges) {
        $ranks        = $rankOf($top3Colleges);
        $collegesHtml = '';
        foreach ($top3Colleges as $i => $c) {
            $left  = '<div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">' . htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') . '</div>';
            $right = '<span class="badge rounded-pill bg-success-subtle text-success fw-semibold" style="font-size:.72rem;">' . number_format($c['count']) . '</span>'
                   . '<span class="text-muted" style="font-size:.62rem;">students</span>';
            $collegesHtml .= $row($medalHtml($ranks[$i], $c['tied']), $left, $right, $i === count($top3Colleges) - 1);
        }
    }

    // Courses
    $coursesHtml = $noData;
    if ($top3Courses) {
        $ranks       = $rankOf($top3Courses);
        $coursesHtml = '';
        foreach ($top3Courses as $i => $cr) {
            $left  = '<div class="min-w-0">'
                   . '<div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">' . htmlspecialchars($cr['course'], ENT_QUOTES, 'UTF-8') . '</div>'
                   . '<div style="font-size:.68rem;"><span class="badge rounded-pill bg-secondary-subtle text-secondary px-2 py-0">' . htmlspecialchars($cr['college'] ?: '—', ENT_QUOTES, 'UTF-8') . '</span></div>'
                   . '</div>';
            $right = '<span class="badge rounded-pill bg-warning-subtle text-warning fw-semibold" style="font-size:.72rem;">' . number_format($cr['count']) . '</span>'
                   . '<span class="text-muted" style="font-size:.62rem;">students</span>';
            $coursesHtml .= $row($medalHtml($ranks[$i], $cr['tied']), $left, $right, $i === count($top3Courses) - 1);
        }
    }

    return [
        'kpiStudentsHtml'    => $studentsHtml,
        'kpiCollegesHtml'    => $collegesHtml,
        'kpiCoursesHtml'     => $coursesHtml,
        'kpiLastUpdatedHtml' => '<i class="fas fa-sync-alt me-1"></i>Last updated: ' . date('g:i A'),
    ];
}

// ---------------------------------------------------------------------------
//  Common KPI computation (shared across all tabs)
// ---------------------------------------------------------------------------

function getKpiData(array $logs, ?string $endDate): array
{
    $totalMinutes    = 0.0;
    $uniqueUsers     = [];
    $endDateCheckins = 0;

    foreach ($logs as $log) {
        $uniqueUsers[$log['id_number']] = true;
        $totalMinutes += minutesBetween($log['checkin_time'], $log['checkout_time'] ?? null);

        if ($endDate && substr($log['checkin_time'], 0, 10) === $endDate) {
            $endDateCheckins++;
        }
    }

    $totalVisits = count($logs);

    // Top 3 students by check-in count
    $students = aggregateUsers(array_filter($logs, 'isStudent'));
    uasort($students, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    $top3Students = [];
    foreach (array_slice($students, 0, 3, true) as $rec) {
        $top3Students[] = ['id_number' => $rec['display_label'], 'college' => $rec['college'], 'course' => $rec['course'], 'count' => $rec['checkins']];
    }

    // Top 3 colleges by unique student visitors
    $colleges = aggregateColleges($logs);
    uasort($colleges, fn($a, $b) => $b['visitors'] <=> $a['visitors']);
    $top3Colleges = [];
    foreach (array_slice($colleges, 0, 3, true) as $name => $rec) {
        $top3Colleges[] = ['name' => $name, 'count' => $rec['visitors']];
    }

    // Top 3 courses by unique student visitors
    $courses = aggregateCourses($logs);
    uasort($courses, fn($a, $b) => $b['visitors'] <=> $a['visitors']);
    $top3Courses = [];
    foreach (array_slice($courses, 0, 3) as $rec) {
        $top3Courses[] = ['college' => $rec['college'], 'course' => $rec['course'], 'count' => $rec['visitors']];
    }

    $top3Students = annotateTies($top3Students);
    $top3Colleges = annotateTies($top3Colleges);
    $top3Courses  = annotateTies($top3Courses);
    $kpiSections  = renderKpiSections($top3Students, $top3Colleges, $top3Courses);

    return [
        'totalVisits'     => $totalVisits,
        'totalDuration'   => round($totalMinutes),
        'uniqueUsers'     => count($uniqueUsers),
        'avgDuration'     => $totalVisits ? round($totalMinutes / $totalVisits, 1) : 0,
        'endDateCheckins' => $endDateCheckins,
        'top3Students'    => $top3Students,
        'top3Colleges'    => $top3Colleges,
        'top3Courses'     => $top3Courses,
        ...$kpiSections,
    ];
}

// ---------------------------------------------------------------------------
//  Tab HTML renderers
// ---------------------------------------------------------------------------

function renderLogsTab(array $flatLogs): string
{
    $rowsJson = htmlspecialchars(json_encode(renderLogRows($flatLogs)), ENT_QUOTES);
    ob_start(); ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-2 px-3">
            <span class="fw-semibold small">All Visit Logs</span>
            <p class="text-muted mb-0" style="font-size:.72rem;">Every check-in within selected date range</p>
        </div>
        <div class="card-body p-0"
             id="allLogsCard"
             data-rows="<?= $rowsJson ?>"
             data-per-page="10">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
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

function renderUsersTab(
    array $topCheckins,
    array $topDuration,
    array $classificationDistribution,
    array $chartTopCheckins,
    array $chartTopDuration,
    array $courseChartData
): string {
    // Flatten top-N per-type lists into a single sorted list for the limited tables.
    $flatCheckins = [];
    foreach ($topCheckins as $classification => $users) {
        foreach ($users as $user) {
            $flatCheckins[] = [
                'display_label'       => $user['display_label'],
                'college'             => $user['college'],
                'course'              => $user['course'],
                'type'                => $classification,
                'library'             => $user['library'],
                'count'               => $user['count'],
                'agency_organization' => $user['agency_organization'],
                'last_checkin'        => date('M j', strtotime($user['last_checkin'])),
            ];
        }
    }
    usort($flatCheckins, fn($a, $b) => $b['count'] <=> $a['count']);

    $flatDuration = [];
    foreach ($topDuration as $classification => $users) {
        foreach ($users as $user) {
            $flatDuration[] = [
                'display_label'       => $user['display_label'],
                'college'             => $user['college'],
                'course'              => $user['course'],
                'type'                => $classification,
                'minutes'             => $user['minutes'],
                'agency_organization' => $user['agency_organization'],
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
        window.chartTopCheckins           = <?= json_encode($chartTopCheckins) ?>;
        window.chartTopDuration           = <?= json_encode($chartTopDuration) ?>;
        window.courseChartData            = <?= json_encode($courseChartData) ?>;
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
    // Flatten the nested [college][course] structure for table display.
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
    <script>window.sexDistribution = <?= json_encode($sexDistribution) ?>;</script>
    <?php return ob_get_clean();
}

// ---------------------------------------------------------------------------
//  Handlers
// ---------------------------------------------------------------------------

function TabLogs(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);
    $kpi  = getKpiData($logs, $_POST['endDate'] ?? null);

    $flatLogs = array_map(function ($log) {
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
            'duration_minutes'    => minutesBetween($log['checkin_time'] ?? null, $checkout),
            'checkin_formatted'   => date('M j, Y g:i A', strtotime($log['checkin_time'])),
            'checkout_formatted'  => $checkout ? date('M j, Y g:i A', strtotime($checkout)) : '—',
        ];
    }, $logs);

    echo json_encode(array_merge([
        'status'   => 'success',
        'html'     => renderLogsTab($flatLogs),
        'flatLogs' => $flatLogs,
    ], $kpi));
    exit;
}

function TabUsers(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);
    $kpi  = getKpiData($logs, $_POST['endDate'] ?? null);

    // Classification distribution for the donut chart.
    $classificationDistribution = [];
    foreach ($logs as $log) {
        $type = $log['classification'] ?? 'Unknown';
        $classificationDistribution[$type] = ($classificationDistribution[$type] ?? 0) + 1;
    }

    // Aggregate all users, then split by classification for per-type top-3 tables.
    $users  = aggregateUsers($logs);
    $byType = [];
    foreach ($users as $rec) {
        $byType[$rec['classification']][] = $rec;
    }

    $topCheckins = [];
    $topDuration = [];

    foreach ($byType as $type => $typeUsers) {
        usort($typeUsers, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
        $topCheckins[$type] = array_map(fn($r) => [
            'display_label'       => $r['display_label'],
            'name'                => $r['name'],
            'college'             => $r['college'],
            'course'              => $r['course'],
            'type'                => $r['classification'],
            'library'             => $r['library'],
            'agency_organization' => $r['agency_organization'],
            'count'               => $r['checkins'],
            'last_checkin'        => $r['last_checkin'],
        ], array_slice($typeUsers, 0, 3));

        usort($typeUsers, fn($a, $b) => $b['duration'] <=> $a['duration']);
        $topDuration[$type] = array_map(fn($r) => [
            'display_label'       => $r['display_label'],
            'name'                => $r['name'],
            'college'             => $r['college'],
            'course'              => $r['course'],
            'type'                => $r['classification'],
            'library'             => $r['library'],
            'agency_organization' => $r['agency_organization'],
            'minutes'             => (int) round($r['duration']),
            'last_checkin'        => $r['last_checkin'],
        ], array_slice($typeUsers, 0, 3));
    }

    // Chart data: top 3 overall (across all types).
    $allUsers = array_values($users);

    usort($allUsers, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    $chartTopCheckins = array_map(
        fn($r) => ['label' => $r['display_label'], 'value' => $r['checkins']],
        array_slice($allUsers, 0, 3)
    );

    usort($allUsers, fn($a, $b) => $b['duration'] <=> $a['duration']);
    $chartTopDuration = array_map(
        fn($r) => ['label' => $r['display_label'], 'value' => round($r['duration'])],
        array_slice($allUsers, 0, 3)
    );

    // Top 3 student courses by check-ins (for the course chart).
    $courses = aggregateCourses($logs);
    uasort($courses, fn($a, $b) => $b['visitors'] <=> $a['visitors']);
    $courseChartData = array_map(
        fn($r) => ['label' => "{$r['college']} · {$r['course']}", 'checkins' => $r['visitors'], 'duration' => round($r['duration'])],
        array_slice(array_values($courses), 0, 3)
    );

    // Flat user list for export.
    $flatUsers = array_values(array_map(
        fn($r) => array_merge($r, ['last_checkin_formatted' => date('M j, Y g:i A', strtotime($r['last_checkin']))]),
        $users
    ));

    echo json_encode(array_merge([
        'status'                     => 'success',
        'html'                       => renderUsersTab($topCheckins, $topDuration, $classificationDistribution, $chartTopCheckins, $chartTopDuration, $courseChartData),
        'classificationDistribution' => $classificationDistribution,
        'chartTopCheckins'           => $chartTopCheckins,
        'chartTopDuration'           => $chartTopDuration,
        'courseChartData'            => $courseChartData,
        'flatUsers'                  => $flatUsers,
    ], $kpi));
    exit;
}

function TabColleges(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);
    $kpi  = getKpiData($logs, $_POST['endDate'] ?? null);

    $colleges = aggregateColleges($logs);

    // Two independent sorted views of the same data.
    $sortedByVisitors = $colleges;
    uasort($sortedByVisitors, fn($a, $b) => $b['visitors'] <=> $a['visitors']);

    $sortedByDuration = $colleges;
    uasort($sortedByDuration, fn($a, $b) => $b['duration'] <=> $a['duration']);

    // Build the top-3 dicts in the shape the render function and JS charts expect.
    $topByCheckins = [];
    foreach (array_slice($sortedByVisitors, 0, 3, true) as $name => $rec) {
        $topByCheckins[$name] = ['count' => $rec['visitors'], 'last_checkin' => $rec['last_checkin'], 'color' => resolveCollegeColor($name)];
    }

    $topByDuration = [];
    foreach (array_slice($sortedByDuration, 0, 3, true) as $name => $rec) {
        $topByDuration[$name] = ['minutes' => round($rec['duration']), 'last_checkin' => $rec['last_checkin'], 'color' => resolveCollegeColor($name)];
    }

    // Flat list for export (all colleges, sorted by unique visitors).
    $flatColleges = [];
    foreach ($sortedByVisitors as $name => $rec) {
        $flatColleges[] = [
            'name'         => $name,
            'visitors'     => $rec['visitors'],
            'duration'     => round($rec['duration']),
            'last_checkin' => date('M j, Y g:i A', strtotime($rec['last_checkin'])),
        ];
    }

    echo json_encode(array_merge([
        'status'               => 'success',
        'html'                 => renderCollegesTab($topByCheckins, $topByDuration),
        'top3CollegesCheckin'  => $topByCheckins,
        'top3CollegesDuration' => $topByDuration,
        'flatColleges'         => $flatColleges,
    ], $kpi));
    exit;
}

function TabCourses(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);
    $kpi  = getKpiData($logs, $_POST['endDate'] ?? null);

    $courses = aggregateCourses($logs);

    $sortedByVisitors = $courses;
    uasort($sortedByVisitors, fn($a, $b) => $b['visitors'] <=> $a['visitors']);

    $sortedByDuration = $courses;
    uasort($sortedByDuration, fn($a, $b) => $b['duration'] <=> $a['duration']);

    // Rebuild the nested [college][course] structure the render function and JS charts expect.
    $topByCheckins = [];
    foreach ($sortedByVisitors as $rec) {
        $topByCheckins[$rec['college']][$rec['course']] = ['count' => $rec['visitors'], 'last_checkin' => $rec['last_checkin']];
    }

    $topByDuration = [];
    foreach ($sortedByDuration as $rec) {
        $topByDuration[$rec['college']][$rec['course']] = ['minutes' => round($rec['duration']), 'last_checkin' => $rec['last_checkin']];
    }

    // Top 3 courses by unique visitors for the chart.
    $courseChartData = array_map(
        fn($r) => ['label' => "{$r['college']} · {$r['course']}", 'checkins' => $r['visitors'], 'duration' => round($r['duration'])],
        array_slice(array_values($sortedByVisitors), 0, 3)
    );

    // Flat list for export.
    $flatCourses = [];
    foreach ($sortedByVisitors as $rec) {
        $flatCourses[] = [
            'college'      => $rec['college'],
            'course'       => $rec['course'],
            'visitors'     => $rec['visitors'],
            'duration'     => round($rec['duration']),
            'last_checkin' => date('M j, Y g:i A', strtotime($rec['last_checkin'])),
        ];
    }

    echo json_encode(array_merge([
        'status'             => 'success',
        'html'               => renderCoursesTab($topByCheckins, $topByDuration, $courseChartData),
        'topCoursesCheckin'  => $topByCheckins,
        'topCoursesDuration' => $topByDuration,
        'courseChartData'    => $courseChartData,
        'flatCourses'        => $flatCourses,
    ], $kpi));
    exit;
}

function TabDemographics(): void
{
    [$where, $params] = buildWhereClause($_POST);
    $logs = fetchVisitLogs($where, $params);
    $kpi  = getKpiData($logs, $_POST['endDate'] ?? null);

    $sexDistribution = [];
    foreach ($logs as $log) {
        $sex = $log['sex'] ?: 'Unknown';
        $sexDistribution[$sex] = ($sexDistribution[$sex] ?? 0) + 1;
    }

    $totalLogs = count($logs);

    $flatDemographics = array_map(
        fn($sex, $count) => ['sex' => $sex, 'count' => $count, 'pct' => $totalLogs ? round($count / $totalLogs * 100, 1) : 0],
        array_keys($sexDistribution),
        array_values($sexDistribution)
    );

    $flatLogs = array_map(function ($log) {
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

    echo json_encode(array_merge([
        'status'           => 'success',
        'html'             => renderDemographicsTab($sexDistribution, $totalLogs),
        'sexDistribution'  => $sexDistribution,
        'flatDemographics' => $flatDemographics,
        'flatLogs'         => $flatLogs,
    ], $kpi));
    exit;
}

// ---------------------------------------------------------------------------
//  Dispatch
// ---------------------------------------------------------------------------

switch (trim($_POST['request'] ?? '')) {
    case 'getTabLogs':         TabLogs();         break;
    case 'getTabUsers':        TabUsers();        break;
    case 'getTabColleges':     TabColleges();     break;
    case 'getTabCourses':      TabCourses();      break;
    case 'getTabDemographics': TabDemographics(); break;
    default: echo json_encode(['status' => 'error', 'message' => "Unknown request: '" . trim($_POST['request'] ?? '') . "'."]);
}