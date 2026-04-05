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
    $upperCollegeName = strtoupper($collegeName);

    foreach (COLLEGE_COLOR_MAP as $abbreviation => $colorValue) {
        if (str_contains($upperCollegeName, $abbreviation)) return $colorValue;
    }

    return COLLEGE_COLOR_FALLBACK;
}

// ---------------------------------------------------------------------------
//  Row renderers for client-side-paginated tables
// ---------------------------------------------------------------------------

function renderLogRows(array $logEntries): array
{
    return array_map(fn($logEntry) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">' . htmlspecialchars((string) ($logEntry['id_number'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($logEntry['name'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($logEntry['college'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($logEntry['course'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td>' . getTypeBadge($logEntry['classification'] ?: '—') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($logEntry['library'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($logEntry['sex'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($logEntry['checkin_formatted']  ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($logEntry['checkout_formatted'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($logEntry['agency_organization']?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-end pe-3">' . (int) round($logEntry['duration_minutes']) . '</td>' .
        '</tr>',
    $logEntries);
}

function renderCheckinRows(array $checkinRows): array
{
    return array_map(fn($rowData) =>
        '<tr>' .
        '<td class="ps-3 fw-semibold">' . htmlspecialchars((string) ($rowData['display_label'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($rowData['college'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($rowData['course'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td>' . getTypeBadge($rowData['type']) . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($rowData['library'] ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-end fw-semibold text-primary">'  . number_format($rowData['count']) . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($rowData['agency_organization'] ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-end text-muted pe-3">' . htmlspecialchars((string) ($rowData['last_checkin'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
        '</tr>',
    $checkinRows);
}

function renderDurationRows(array $durationRows): array
{
    return array_map(fn($rowData) =>
        '<tr>' . 
        '<td class="ps-3 fw-semibold">' . htmlspecialchars((string) ($rowData['display_label'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($rowData['college'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td class="text-muted">' . htmlspecialchars((string) ($rowData['course'] ?: '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '<td>' . getTypeBadge($rowData['type']) . '</td>' .
        '<td class="text-end fw-semibold text-success">' . number_format($rowData['minutes']) . '</td>' .
        '<td class="text-muted pe-3">' . htmlspecialchars((string) ($rowData['agency_organization'] ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>' .
        '</tr>',
    $durationRows);
}

// ---------------------------------------------------------------------------
//  KPI sidebar
// ---------------------------------------------------------------------------

function annotateTies(array $items): array
{
    $countFrequency = array_count_values(array_column($items, 'count'));

    return array_map(
        fn($item) => array_merge($item, ['tied' => $countFrequency[$item['count']] > 1]),
        $items
    );
}

function renderKpiSections(array $topStudents, array $topColleges, array $topCourses): array
{
    $medalIcons = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
    $noDataHtml = '<div class="text-muted small fst-italic">No data</div>';

    $computeRanks = function(array $items): array {
        $ranks = [];
        $previousCount = null;
        $currentRank   = 0;

        foreach ($items as $item) {
            if ($item['count'] !== $previousCount) $currentRank++;
            $ranks[] = $currentRank;
            $previousCount = $item['count'];
        }

        return $ranks;
    };

    $buildMedalHtml = fn(int $rank, bool $isTied): string =>
        ($medalIcons[$rank] ?? "{$rank}.")
        . ($isTied ? '<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1" style="font-size:.55rem;vertical-align:middle;">tied</span>' : '');

    $buildRowHtml = fn(string $medal, string $leftHtml, string $rightHtml, bool $isLast): string =>
        '<div class="d-flex align-items-center justify-content-between gap-2 py-1 ' . ($isLast ? '' : 'border-bottom') . '">' .
            '<div class="d-flex align-items-center gap-2 min-w-0">' .
                '<span style="font-size:.9rem;flex-shrink:0;">' . $medal . '</span>' . $leftHtml .
            '</div>' .
            '<div class="d-flex flex-column align-items-end" style="flex-shrink:0;">' . $rightHtml . '</div>' .
        '</div>';

    // Students
    $studentsHtml = $noDataHtml;
    if ($topStudents) {
        $ranks = $computeRanks($topStudents);
        $studentsHtml = '';

        foreach ($topStudents as $index => $studentData) {
            $leftHtml  = '<div class="min-w-0">'
                       . '<div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">' . htmlspecialchars($studentData['id_number'], ENT_QUOTES, 'UTF-8') . '</div>'
                       . '<div class="text-muted" style="font-size:.68rem;">' . htmlspecialchars($studentData['college'] ?: '—', ENT_QUOTES, 'UTF-8') . ($studentData['course'] ? ' · ' . htmlspecialchars($studentData['course'], ENT_QUOTES, 'UTF-8') : '') . '</div>'
                       . '</div>';

            $rightHtml = '<span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold" style="font-size:.72rem;">' . number_format($studentData['count']) . '</span>'
                       . '<span class="text-muted" style="font-size:.62rem;">check-ins</span>';

            $studentsHtml .= $buildRowHtml(
                $buildMedalHtml($ranks[$index], $studentData['tied']),
                $leftHtml,
                $rightHtml,
                $index === count($topStudents) - 1
            );
        }
    }

    // Colleges
    $collegesHtml = $noDataHtml;
    if ($topColleges) {
        $ranks = $computeRanks($topColleges);
        $collegesHtml = '';

        foreach ($topColleges as $index => $collegeData) {
            $leftHtml  = '<div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">' . htmlspecialchars($collegeData['name'], ENT_QUOTES, 'UTF-8') . '</div>';
            $rightHtml = '<span class="badge rounded-pill bg-success-subtle text-success fw-semibold" style="font-size:.72rem;">' . number_format($collegeData['count']) . '</span>'
                       . '<span class="text-muted" style="font-size:.62rem;">students</span>';

            $collegesHtml .= $buildRowHtml(
                $buildMedalHtml($ranks[$index], $collegeData['tied']),
                $leftHtml,
                $rightHtml,
                $index === count($topColleges) - 1
            );
        }
    }

    // Courses
    $coursesHtml = $noDataHtml;
    if ($topCourses) {
        $ranks = $computeRanks($topCourses);
        $coursesHtml = '';

        foreach ($topCourses as $index => $courseData) {
            $leftHtml  = '<div class="min-w-0">'
                       . '<div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">' . htmlspecialchars($courseData['course'], ENT_QUOTES, 'UTF-8') . '</div>'
                       . '<div style="font-size:.68rem;"><span class="badge rounded-pill bg-secondary-subtle text-secondary px-2 py-0">' . htmlspecialchars($courseData['college'] ?: '—', ENT_QUOTES, 'UTF-8') . '</span></div>'
                       . '</div>';

            $rightHtml = '<span class="badge rounded-pill bg-warning-subtle text-warning fw-semibold" style="font-size:.72rem;">' . number_format($courseData['count']) . '</span>'
                       . '<span class="text-muted" style="font-size:.62rem;">students</span>';

            $coursesHtml .= $buildRowHtml(
                $buildMedalHtml($ranks[$index], $courseData['tied']),
                $leftHtml,
                $rightHtml,
                $index === count($topCourses) - 1
            );
        }
    }

    return [
        'kpiStudentsHtml' => $studentsHtml,
        'kpiCollegesHtml' => $collegesHtml,
        'kpiCoursesHtml' => $coursesHtml,
        'kpiLastUpdatedHtml' => '<i class="fas fa-sync-alt me-1"></i>Last updated: ' . date('g:i A'),
    ];
}

// ---------------------------------------------------------------------------
//  Common KPI computation
// ---------------------------------------------------------------------------

function getKpiData(array $visitLogs, ?string $endDate): array
{
    $totalMinutes = 0.0;
    $seenUserIds = [];
    $endDateCheckins = 0;

    foreach ($visitLogs as $logEntry) {
        $seenUserIds[$logEntry['id_number']] = true;

        $totalMinutes += minutesBetween(
            $logEntry['checkin_time'],
            $logEntry['checkout_time'] ?? null
        );

        if ($endDate && substr($logEntry['checkin_time'], 0, 10) === $endDate) {
            $endDateCheckins++;
        }
    }

    $totalVisits = count($visitLogs);

    $studentStats = aggregateUsers(array_filter($visitLogs, 'isStudent'));
    uasort($studentStats, fn($userA, $userB) => $userB['checkins'] <=> $userA['checkins']);

    $topStudents = [];
    foreach (array_slice($studentStats, 0, 3, true) as $userData) {
        $topStudents[] = [
            'id_number' => $userData['display_label'],
            'college' => $userData['college'],
            'course' => $userData['course'],
            'count' => $userData['checkins']
        ];
    }

    $collegeStats = aggregateColleges($visitLogs);
    uasort($collegeStats, fn($collegeA, $collegeB) => $collegeB['visitors'] <=> $collegeA['visitors']);

    $topColleges = [];
    foreach (array_slice($collegeStats, 0, 3, true) as $collegeName => $collegeData) {
        $topColleges[] = ['name' => $collegeName, 'count' => $collegeData['visitors']];
    }

    $courseStats = aggregateCourses($visitLogs);
    uasort($courseStats, fn($courseA, $courseB) => $courseB['visitors'] <=> $courseA['visitors']);

    $topCourses = [];
    foreach (array_slice($courseStats, 0, 3) as $courseData) {
        $topCourses[] = [
            'college' => $courseData['college'],
            'course' => $courseData['course'],
            'count' => $courseData['visitors']
        ];
    }

    $topStudents = annotateTies($topStudents);
    $topColleges = annotateTies($topColleges);
    $topCourses = annotateTies($topCourses);

    $kpiSections = renderKpiSections($topStudents, $topColleges, $topCourses);

    return [
        'totalVisits' => $totalVisits,
        'totalDuration' => round($totalMinutes),
        'uniqueUsers' => count($seenUserIds),
        'avgDuration' => $totalVisits ? round($totalMinutes / $totalVisits, 1) : 0,
        'endDateCheckins' => $endDateCheckins,
        'top3Students' => $topStudents,
        'top3Colleges' => $topColleges,
        'top3Courses' => $topCourses,
        ...$kpiSections,
    ];
}

// ---------------------------------------------------------------------------
//  Tab HTML renderers
// ---------------------------------------------------------------------------

function renderLogsTab(array $flatLogs): string
{
    $encodedLogRows = htmlspecialchars(json_encode(renderLogRows($flatLogs)), ENT_QUOTES);
    ob_start();
    ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-2 px-3">
            <span class="fw-semibold small">All Visit Logs</span>
            <p class="text-muted mb-0" style="font-size:.72rem;">Every check-in within selected date range</p>
        </div>
        <div class="card-body p-0"
             id="allLogsCard"
             data-rows="<?= $encodedLogRows ?>"
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
    $checkinTableRows = [];
    foreach ($topCheckins as $classification => $users) {
        foreach ($users as $user) {
            $checkinTableRows[] = [
                'display_label' => $user['display_label'],
                'college' => $user['college'],
                'course' => $user['course'],
                'type' => $classification,
                'library' => $user['library'],
                'count' => $user['count'],
                'agency_organization' => $user['agency_organization'],
                'last_checkin' => date('M j', strtotime($user['last_checkin'])),
            ];
        }
    }
    usort($checkinTableRows, fn($rowA, $rowB) => $rowB['count'] <=> $rowA['count']);

    $durationTableRows = [];
    foreach ($topDuration as $classification => $users) {
        foreach ($users as $user) {
            $durationTableRows[] = [
                'display_label' => $user['display_label'],
                'college' => $user['college'],
                'course' => $user['course'],
                'type' => $classification,
                'minutes' => $user['minutes'],
                'agency_organization' => $user['agency_organization'],
            ];
        }
    }
    usort($durationTableRows, fn($rowA, $rowB) => $rowB['minutes'] <=> $rowA['minutes']);

    $encodedCheckinRows  = htmlspecialchars(json_encode(renderCheckinRows($checkinTableRows)),  ENT_QUOTES);
    $encodedDurationRows = htmlspecialchars(json_encode(renderDurationRows($durationTableRows)), ENT_QUOTES);
    ob_start();
    ?>
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
                     data-rows="<?= $encodedCheckinRows ?>"
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
                     data-rows="<?= $encodedDurationRows ?>"
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
    ob_start();
    ?>
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
                        <?php if ($panel['data']): foreach ($panel['data'] as $collegeName => $collegeData): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars((string) $collegeName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end fw-semibold <?= $panel['valueClass'] ?>"><?= round($collegeData[$panel['valueKey']]) ?></td>
                            <td class="text-end text-muted"><?= date('M j, Y', strtotime($collegeData['last_checkin'])) ?></td>
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
    $flattenNestedCourses = function(array $collegeCourseMap): array {

        $rows = [];
        foreach ($collegeCourseMap as $college => $courses) {
            foreach ($courses as $course => $courseData) {
                $rows[] = array_merge(['college' => $college, 'course' => $course], $courseData);
            }
        }
        return $rows;
    };

    $checkinTableRows = $flattenNestedCourses($topByCheckins);
    usort($checkinTableRows, fn($rowA, $rowB) => $rowB['count'] <=> $rowA['count']);

    $durationTableRows = $flattenNestedCourses($topByDuration);
    usort($durationTableRows, fn($rowA, $rowB) => $rowB['minutes'] <=> $rowA['minutes']);

    $panels = [
        ['title' => 'Check-ins', 'canvas' => 'chartCoursesCheckin',  'subtitle' => 'Unique visitors per course',    'valueKey' => 'count',   'columnLabel' => 'Visitors',       'rows' => $checkinTableRows, 'showViewAll' => false],
        ['title' => 'Duration',  'canvas' => 'chartCoursesDuration', 'subtitle' => 'Total session time per course', 'valueKey' => 'minutes', 'columnLabel' => 'Duration (min)', 'rows' => $durationTableRows, 'showViewAll' => true],
    ];
    ob_start();
    ?>
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
        'Male' => ['icon' => 'bi-gender-male', 'colorVariant' => 'info', 'count' => $sexDistribution['Male']    ?? 0],
        'Female'  => ['icon' => 'bi-gender-female',  'colorVariant' => 'danger',    'count' => $sexDistribution['Female']  ?? 0],
        'Unknown' => ['icon' => 'bi-question-circle', 'colorVariant' => 'secondary', 'count' => $sexDistribution['Unknown'] ?? 0],
    ];
    foreach ($sexBreakdown as &$sexEntry) {
        $sexEntry['percentage'] = $totalVisitors ? round($sexEntry['count'] / $totalVisitors * 100, 1) : 0;
    }
    unset($sexEntry);
    ob_start();
    ?>
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
                <?php foreach ($sexBreakdown as $label => $sexEntry): if ($sexEntry['count'] > 0 || $label !== 'Unknown'): ?>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-<?= $sexEntry['colorVariant'] ?>-subtle d-flex align-items-center justify-content-center"
                                 style="width:46px;height:46px;flex-shrink:0;">
                                <i class="bi <?= $sexEntry['icon'] ?> text-<?= $sexEntry['colorVariant'] ?>"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0"><?= $label ?></p>
                                <h4 class="fw-bold mb-0"><?= number_format($sexEntry['count']) ?></h4>
                                <?php if ($label !== 'Unknown'): ?>
                                <small class="text-muted"><?= $sexEntry['percentage'] ?>% of total</small>
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
    <script>
    window.sexDistribution = <?= json_encode($sexDistribution) ?>;
    </script>
    <?php return ob_get_clean();
}

// ---------------------------------------------------------------------------
//  Handlers
// ---------------------------------------------------------------------------

function tabLogs(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);

    $visitLogs = fetchVisitLogs($whereClause, $queryParams);
    $kpiData   = getKpiData($visitLogs, $_POST['endDate'] ?? null);

    $flatLogs = [];

    foreach ($visitLogs as $logEntry) {
        $checkoutTime = $logEntry['checkout_time'] ?? null;
    
        $flatLogs[] = [
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
            'duration_minutes' => minutesBetween($logEntry['checkin_time'] ?? null, $checkoutTime),
            'checkin_formatted' => date('M j, Y g:i A', strtotime($logEntry['checkin_time'])),
            'checkout_formatted'  => $checkoutTime ? date('M j, Y g:i A', strtotime($checkoutTime)) : '—',
        ];
    }

    echo json_encode(array_merge([
        'status' => 'success',
        'html' => renderLogsTab($flatLogs),
        'flatLogs' => $flatLogs,
    ], $kpiData));

    exit;
}

function tabUsers(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);

    $visitLogs = fetchVisitLogs($whereClause, $queryParams);
    $kpiData = getKpiData($visitLogs, $_POST['endDate'] ?? null);

    // Classification distribution for the donut chart.
    $classificationDistribution = [];
    foreach ($visitLogs as $logEntry) {
        $classification = $logEntry['classification'] ?? 'Unknown';
        $classificationDistribution[$classification] = ($classificationDistribution[$classification] ?? 0) + 1;
    }

    // Aggregate all users, then split by classification for per-type top-3 tables.
    $userStats = aggregateUsers($visitLogs);
    $usersByClassification = [];

    foreach ($userStats as $userData) {
        $usersByClassification[$userData['classification']][] = $userData;
    }

    $topUsersByCheckins = [];
    $topUsersByDuration = [];

    foreach ($usersByClassification as $classification => $typeUsers) {
        usort($typeUsers, fn($userA, $userB) => $userB['checkins'] <=> $userA['checkins']);

        $topUsersByCheckins[$classification] = array_map(fn($userData) => [
            'display_label' => $userData['display_label'],
            'name' => $userData['name'],
            'college' => $userData['college'],
            'course' => $userData['course'],
            'type' => $userData['classification'],
            'library' => $userData['library'],
            'agency_organization' => $userData['agency_organization'],
            'count' => $userData['checkins'],
            'last_checkin' => $userData['last_checkin'],
        ], array_slice($typeUsers, 0, 3));

        usort($typeUsers, fn($userA, $userB) => $userB['duration'] <=> $userA['duration']);

        $topUsersByDuration[$classification] = array_map(fn($userData) => [
            'display_label' => $userData['display_label'],
            'name' => $userData['name'],
            'college' => $userData['college'],
            'course' => $userData['course'],
            'type' => $userData['classification'],
            'library' => $userData['library'],
            'agency_organization' => $userData['agency_organization'],
            'minutes' => (int) round($userData['duration']),
            'last_checkin' => $userData['last_checkin'],
        ], array_slice($typeUsers, 0, 3));
    }

    // Chart data: top 3 overall (across all types).
    $allUsers = array_values($userStats);

    usort($allUsers, fn($userA, $userB) => $userB['checkins'] <=> $userA['checkins']);
    $chartTopCheckins = array_map(
        fn($userData) => ['label' => $userData['display_label'], 'value' => $userData['checkins']],
        array_slice($allUsers, 0, 3)
    );

    usort($allUsers, fn($userA, $userB) => $userB['duration'] <=> $userA['duration']);
    $chartTopDuration = array_map(
        fn($userData) => ['label' => $userData['display_label'], 'value' => round($userData['duration'])],
        array_slice($allUsers, 0, 3)
    );

    // Top 3 student courses by check-ins (for the course chart).
    $courseStats = aggregateCourses($visitLogs);
    uasort($courseStats, fn($courseA, $courseB) => $courseB['visitors'] <=> $courseA['visitors']);

    $courseChartData = array_map(
        fn($courseData) => [
            'label' => "{$courseData['college']} · {$courseData['course']}",
            'checkins' => $courseData['visitors'],
            'duration' => round($courseData['duration'])
        ],
        array_slice(array_values($courseStats), 0, 3)
    );

    // Flat user list for export.
    $flatUsers = array_values(array_map(
        fn($userData) => array_merge(
            $userData,
            ['last_checkin_formatted' => date('M j, Y g:i A', strtotime($userData['last_checkin']))]
        ),
        $userStats
    ));

    echo json_encode(array_merge([
        'status' => 'success',
        'html' => renderUsersTab(
            $topUsersByCheckins,
            $topUsersByDuration,
            $classificationDistribution,
            $chartTopCheckins,
            $chartTopDuration,
            $courseChartData
        ),
        'classificationDistribution' => $classificationDistribution,
        'chartTopCheckins' => $chartTopCheckins,
        'chartTopDuration' => $chartTopDuration,
        'courseChartData' => $courseChartData,
        'flatUsers' => $flatUsers,
    ], $kpiData));

    exit;
}

function tabColleges(): void 
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);

    $visitLogs = fetchVisitLogs($whereClause, $queryParams);
    $kpiData = getKpiData($visitLogs, $_POST['endDate'] ?? null);

    $collegeStats = aggregateColleges($visitLogs);

    // Two independent sorted views of the same data.
    $collegesByVisitors = $collegeStats;
    uasort($collegesByVisitors, fn($collegeA, $collegeB) => $collegeB['visitors'] <=> $collegeA['visitors']);

    $collegesByDuration = $collegeStats;
    uasort($collegesByDuration, fn($collegeA, $collegeB) => $collegeB['duration'] <=> $collegeA['duration']);

    // Build the top-3 dicts in the shape the render function and JS charts expect.
    $topCollegesByCheckins = [];
    foreach (array_slice($collegesByVisitors, 0, 3, true) as $collegeName => $collegeData) {
        $topCollegesByCheckins[$collegeName] = [
            'count' => $collegeData['visitors'],
            'last_checkin' => $collegeData['last_checkin'],
            'color' => resolveCollegeColor($collegeName)
        ];
    }

    $topCollegesByDuration = [];
    foreach (array_slice($collegesByDuration, 0, 3, true) as $collegeName => $collegeData) {
        $topCollegesByDuration[$collegeName] = [
            'minutes' => round($collegeData['duration']),
            'last_checkin' => $collegeData['last_checkin'],
            'color' => resolveCollegeColor($collegeName)
        ];
    }

    // Flat list for export (all colleges, sorted by unique visitors).
    $exportableColleges = [];
    foreach ($collegesByVisitors as $collegeName => $collegeData) {
        $exportableColleges[] = [
            'name' => $collegeName,
            'visitors' => $collegeData['visitors'],
            'duration' => round($collegeData['duration']),
            'last_checkin' => !empty($collegeData['last_checkin']) ? date('M j, Y g:i A', strtotime($collegeData['last_checkin'])) : '—',
        ];
    }

    echo json_encode(array_merge([
        'status' => 'success',
        'html' => renderCollegesTab($topCollegesByCheckins, $topCollegesByDuration),
        'top3CollegesCheckin' => $topCollegesByCheckins,
        'top3CollegesDuration' => $topCollegesByDuration,
        'flatColleges' => $exportableColleges,
    ], $kpiData));

    exit;
}

function tabCourses(): void 
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);

    $visitLogs = fetchVisitLogs($whereClause, $queryParams);
    $kpiData = getKpiData($visitLogs, $_POST['endDate'] ?? null);

    $courseStats = aggregateCourses($visitLogs);

    $coursesByVisitors = $courseStats;
    uasort($coursesByVisitors, fn($courseA, $courseB) => $courseB['visitors'] <=> $courseA['visitors']);

    $coursesByDuration = $courseStats;
    uasort($coursesByDuration, fn($courseA, $courseB) => $courseB['duration'] <=> $courseA['duration']);

    // Rebuild the nested [college][course] structure the render function and JS charts expect.
    $topCoursesByCheckins = [];
    foreach ($coursesByVisitors as $courseData) {
        $topCoursesByCheckins[$courseData['college']][$courseData['course']] = [
            'count' => $courseData['visitors'],
            'last_checkin' => $courseData['last_checkin']
        ];
    }

    $topCoursesByDuration = [];
    foreach ($coursesByDuration as $courseData) {
        $topCoursesByDuration[$courseData['college']][$courseData['course']] = [
            'minutes' => round($courseData['duration']),
            'last_checkin' => $courseData['last_checkin']
        ];
    }

    // Top 3 courses by unique visitors for the chart.
    $courseChartData = array_map(
        fn($courseData) => [
            'label' => "{$courseData['college']} · {$courseData['course']}",
            'checkins' => $courseData['visitors'],
            'duration' => round($courseData['duration'])
        ],
        array_slice(array_values($coursesByVisitors), 0, 3)
    );

    // Flat list for export.
    $exportableCourses = [];
    foreach ($coursesByVisitors as $courseData) {
        $exportableCourses[] = [
            'college' => $courseData['college'],
            'course' => $courseData['course'],
            'visitors' => $courseData['visitors'],
            'duration' => round($courseData['duration']),
            'last_checkin' => !empty($courseData['last_checkin']) ? date('M j, Y g:i A', strtotime($courseData['last_checkin'])) : '—',
        ];
    }

    echo json_encode(array_merge([
        'status' => 'success',
        'html' => renderCoursesTab($topCoursesByCheckins, $topCoursesByDuration, $courseChartData),
        'topCoursesCheckin'  => $topCoursesByCheckins,
        'topCoursesDuration' => $topCoursesByDuration,
        'courseChartData' => $courseChartData,
        'flatCourses' => $exportableCourses,
    ], $kpiData));

    exit;
}

function tabDemographics(): void
{
    [$whereClause, $queryParams] = buildWhereClause($_POST);

    $visitLogs = fetchVisitLogs($whereClause, $queryParams);
    $kpiData = getKpiData($visitLogs, $_POST['endDate'] ?? null);

    $sexDistribution = [];
    foreach ($visitLogs as $logEntry) {
        $sex = $logEntry['sex'] ?: 'Unknown';
        $sexDistribution[$sex] = ($sexDistribution[$sex] ?? 0) + 1;
    }

    $totalLogCount = count($visitLogs);

    $flatDemographics = array_map(
        fn($sex, $count) => [
            'sex' => $sex,
            'count' => $count,
            'percentage' => $totalLogCount ? round($count / $totalLogCount * 100, 1) : 0
        ],
        array_keys($sexDistribution),
        array_values($sexDistribution)
    );

    $flatLogs = [];

    foreach ($visitLogs as $logEntry) {
        $checkoutTime = $logEntry['checkout_time'] ?? null;
    
        $flatLogs[] = [
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
            'duration' => minutesBetween($logEntry['checkin_time'] ?? null, $checkoutTime),
        ];
    }

    echo json_encode(array_merge([
        'status' => 'success',
        'html' => renderDemographicsTab($sexDistribution, $totalLogCount),
        'sexDistribution'  => $sexDistribution,
        'flatDemographics' => $flatDemographics,
        'flatLogs' => $flatLogs,
    ], $kpiData));

    exit;
}

// ---------------------------------------------------------------------------
//  Dispatch
// ---------------------------------------------------------------------------

switch (trim($_POST['request'] ?? '')) {
    case 'getTabLogs': 
        tabLogs(); 
    break;

    case 'getTabUsers': 
        tabUsers(); 
    break;

    case 'getTabColleges': 
        tabColleges(); 
    break;

    case 'getTabCourses': 
        tabCourses(); 
    break;

    case 'getTabDemographics': 
        tabDemographics(); 
    break;

    default: echo json_encode(['status' => 'error', 'message' => "Unknown request: '" . trim($_POST['request'] ?? '') . "'."]);
}