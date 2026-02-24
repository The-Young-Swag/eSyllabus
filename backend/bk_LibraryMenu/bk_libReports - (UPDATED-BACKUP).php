<?php
include "../../db/dbconnection.php";
header('Content-Type: application/json');

$request = $_POST['request'] ?? '';

switch ($request) {
    case 'getTabData': getTabData(); break;
    case 'viewAll':    viewAll();    break;
    default:           send(['status' => 'error', 'message' => 'Invalid request']);
}


// ============================================================
// SEND RESPONSE
// ============================================================

function send(array $data): void {
    echo json_encode($data);
    exit;
}


// ============================================================
// FILTERS & FETCHING
// ============================================================

function getFilters(): array {
    return [
        'startDate'      => $_POST['startDate']      ?? null,
        'endDate'        => $_POST['endDate']         ?? null,
        'classification' => $_POST['classification']  ?? 'All',
        'library'        => $_POST['library']         ?? 'All',
    ];
}

function fetchLogs(array $filters): array {
    $where  = "WHERE 1=1";
    $params = [];

    if (!empty($filters['startDate'])) {
        $where .= " AND CAST(l.checkin_time AS DATE) >= :startDate";
        $params[':startDate'] = $filters['startDate'];
    }
    if (!empty($filters['endDate'])) {
        $where .= " AND CAST(l.checkin_time AS DATE) <= :endDate";
        $params[':endDate'] = $filters['endDate'];
    }
    if ($filters['classification'] !== 'All') {
        $where .= " AND l.classification = :classification";
        $params[':classification'] = $filters['classification'];
    }
    if ($filters['library'] !== 'All') {
        $where .= " AND l.library = :library";
        $params[':library'] = $filters['library'];
    }

    return execsqlSRS("
        SELECT
            l.id_number, l.name, l.college, l.course,
            l.checkin_time, l.checkout_time,
            l.sex, l.classification,
            s.SectionName AS libraryName
        FROM Library_logs l
        LEFT JOIN LibrarySection s ON l.library = s.SectionID
        $where
        ORDER BY l.checkin_time DESC
    ", 'Select', $params);
}


// ============================================================
// HELPERS
// ============================================================

function duration(?string $checkin, ?string $checkout): float {
    if (!$checkout) return 0;
    return max(0, (strtotime($checkout) - strtotime($checkin)) / 60);
}

function topN(array $data, int $n = 3): array {
    arsort($data);
    return array_slice($data, 0, $n, true);
}

function isGuest(array $log): bool {
    return strtolower($log['classification']) === 'guest';
}

function fmtDate(string $datetime): string {
    return date('M j, Y g:i A', strtotime($datetime));
}

function h(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}


// ============================================================
// KPIs
// ============================================================

function buildKPIs(array $logs, ?string $endDate): array {
    $totalMinutes = 0;
    $uniqueIds    = [];

    foreach ($logs as $log) {
        $totalMinutes += duration($log['checkin_time'], $log['checkout_time']);
        $uniqueIds[$log['id_number']] = true;
    }

    $total       = count($logs);
    $avgDuration = $total ? round($totalMinutes / $total / 60, 1) : 0;

    $endDateCheckins = 0;
    if ($endDate) {
        foreach ($logs as $log) {
            if (substr($log['checkin_time'], 0, 10) === $endDate) $endDateCheckins++;
        }
    }

    return [
        'totalVisits'     => $total,
        'uniqueUsers'     => count($uniqueIds),
        'avgDuration'     => $avgDuration,
        'endDateCheckins' => $endDateCheckins,
    ];
}


// ============================================================
// AGGREGATIONS
// ============================================================

function aggregateUsers(array $logs): array {
    $byClass = [];

    foreach ($logs as $log) {
        $class = $log['classification'];
        $id    = $log['id_number'];

        if (!isset($byClass[$class][$id])) {
            $byClass[$class][$id] = [
                'name'         => $log['name'],
                'library'      => $log['libraryName'],
                'checkins'     => 0,
                'minutes'      => 0,
                'last_checkin' => $log['checkin_time'],
            ];
        }

        $byClass[$class][$id]['checkins']++;
        $byClass[$class][$id]['minutes'] += duration($log['checkin_time'], $log['checkout_time']);

        if ($log['checkin_time'] > $byClass[$class][$id]['last_checkin']) {
            $byClass[$class][$id]['last_checkin'] = $log['checkin_time'];
        }
    }

    $topCheckins = [];
    $topDuration = [];

    foreach ($byClass as $class => $users) {
        uasort($users, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
        $topCheckins[$class] = array_slice($users, 0, 3, true);

        uasort($users, fn($a, $b) => $b['minutes'] <=> $a['minutes']);
        $topDuration[$class] = array_slice($users, 0, 3, true);
    }

    return [$topCheckins, $topDuration];
}

function aggregateColleges(array $logs): array {
    $checkins    = [];
    $minutes     = [];
    $uniqueUsers = [];
    $lastCheckin = [];

    foreach ($logs as $log) {
        if (isGuest($log)) continue;

        $college = $log['college'] ?: 'Unknown';
        $id      = $log['id_number'];

        if (!isset($uniqueUsers[$college][$id])) {
            $uniqueUsers[$college][$id] = true;
            $checkins[$college] = ($checkins[$college] ?? 0) + 1;
        }

        $minutes[$college] = ($minutes[$college] ?? 0) + duration($log['checkin_time'], $log['checkout_time']);

        if (!isset($lastCheckin[$college]) || $log['checkin_time'] > $lastCheckin[$college]) {
            $lastCheckin[$college] = $log['checkin_time'];
        }
    }

    $topCheckins = [];
    foreach (topN($checkins) as $college => $count) {
        $topCheckins[$college] = ['count' => $count, 'last_checkin' => $lastCheckin[$college]];
    }

    $topDuration = [];
    foreach (topN($minutes) as $college => $mins) {
        $topDuration[$college] = ['minutes' => round($mins), 'last_checkin' => $lastCheckin[$college]];
    }

    return [$topCheckins, $topDuration];
}

function aggregateCourses(array $logs): array {
    $checkins    = [];
    $minutes     = [];
    $uniqueUsers = [];
    $lastCheckin = [];

    foreach ($logs as $log) {
        if (isGuest($log)) continue;

        $college = $log['college'] ?: 'Unknown';
        $course  = $log['course']  ?: 'Unknown';
        $key     = "$college|$course";
        $id      = $log['id_number'];

        if (!isset($uniqueUsers[$college][$course][$id])) {
            $uniqueUsers[$college][$course][$id] = true;
            $checkins[$college][$course] = ($checkins[$college][$course] ?? 0) + 1;
        }

        $minutes[$college][$course] = ($minutes[$college][$course] ?? 0)
            + duration($log['checkin_time'], $log['checkout_time']);

        if (!isset($lastCheckin[$key]) || $log['checkin_time'] > $lastCheckin[$key]) {
            $lastCheckin[$key] = $log['checkin_time'];
        }
    }

    $topCheckins = [];
    $topDuration = [];

    foreach ($checkins as $college => $courses) {
        foreach (topN($courses) as $course => $count) {
            $key = "$college|$course";
            $topCheckins[$college][$course] = ['count' => $count, 'last_checkin' => $lastCheckin[$key]];
        }
    }

    foreach ($minutes as $college => $courses) {
        foreach (topN($courses) as $course => $mins) {
            $key = "$college|$course";
            $topDuration[$college][$course] = ['minutes' => round($mins), 'last_checkin' => $lastCheckin[$key]];
        }
    }

    return [$topCheckins, $topDuration];
}

function aggregateSex(array $logs): array {
    $dist = [];
    foreach ($logs as $log) {
        $sex = $log['sex'] ?: 'Unknown';
        $dist[$sex] = ($dist[$sex] ?? 0) + 1;
    }
    return $dist;
}


// ============================================================
// HTML BUILDERS
// ============================================================

function buildUsersHtml(array $topCheckins, array $topDuration): string {
    ob_start(); ?>
    <div class="row g-4">

        <div class="col-xl-8">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="fw-semibold">Top Users by Check-ins</div>
                    <small class="text-muted">Most frequent visitors</small>
                </div>
                <div class="card-body">
                    <div style="height:300px;"><canvas id="chartUsersCheckin"></canvas></div>
                    <div class="table-responsive mt-4">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr><th>User</th><th>Type</th><th>Library</th><th class="text-end">Check-ins</th><th class="text-end">Last Check-in</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($topCheckins as $class => $users): ?>
                                <?php foreach ($users as $data): ?>
                                <tr>
                                    <td class="fw-semibold"><?= h($data['name']) ?></td>
                                    <td><span class="badge bg-light text-dark"><?= h($class) ?></span></td>
                                    <td><?= h($data['library']) ?></td>
                                    <td class="text-end"><?= $data['checkins'] ?></td>
                                    <td class="text-end"><?= fmtDate($data['last_checkin']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-end">
                        <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="users">View All Users</button>
                    </div>
                </div>
            </div>
        </div>

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
                                <tr><th>User</th><th>Type</th><th>Library</th><th class="text-end">Duration (min)</th><th class="text-end">Last Check-in</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($topDuration as $class => $users): ?>
                                <?php foreach ($users as $data): ?>
                                <tr>
                                    <td class="fw-semibold"><?= h($data['name']) ?></td>
                                    <td><span class="badge bg-light text-dark"><?= h($class) ?></span></td>
                                    <td><?= h($data['library']) ?></td>
                                    <td class="text-end"><?= round($data['minutes']) ?></td>
                                    <td class="text-end"><?= fmtDate($data['last_checkin']) ?></td>
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
    <?php return ob_get_clean();
}

function buildCollegesHtml(array $topCheckins, array $topDuration): string {
    ob_start(); ?>
    <div class="row g-4">

        <div class="col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="fw-semibold">Top Colleges by Check-ins</div>
                    <small class="text-muted">Unique visitors per college</small>
                </div>
                <div class="card-body">
                    <div style="height:300px;"><canvas id="chartCollegeCheckin"></canvas></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr><th>College</th><th class="text-end">Unique Users</th><th class="text-end">Last Check-in</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($topCheckins as $college => $data): ?>
                                <tr>
                                    <td class="fw-semibold"><?= h($college) ?></td>
                                    <td class="text-end"><?= $data['count'] ?></td>
                                    <td class="text-end"><?= fmtDate($data['last_checkin']) ?></td>
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

        <div class="col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="fw-semibold">Top Colleges by Duration</div>
                    <small class="text-muted">Total time spent per college</small>
                </div>
                <div class="card-body">
                    <div style="height:300px;"><canvas id="chartCollegeDuration"></canvas></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr><th>College</th><th class="text-end">Duration (min)</th><th class="text-end">Last Check-in</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($topDuration as $college => $data): ?>
                                <tr>
                                    <td class="fw-semibold"><?= h($college) ?></td>
                                    <td class="text-end"><?= $data['minutes'] ?></td>
                                    <td class="text-end"><?= fmtDate($data['last_checkin']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php return ob_get_clean();
}

function buildCoursesHtml(array $topCheckins, array $topDuration): string {
    ob_start();
    foreach ($topCheckins as $college => $courses):
        $id = preg_replace('/[^a-zA-Z0-9]/', '', $college);
    ?>
    <div class="mb-5">
        <h6 class="fw-semibold mb-3"><?= h($college) ?></h6>
        <div class="row g-4">

            <div class="col-md-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <div class="fw-semibold">Top Courses by Check-ins</div>
                        <small class="text-muted">Unique visitors per course</small>
                    </div>
                    <div class="card-body">
                        <div style="height:250px;"><canvas id="chartCourseCheckin_<?= $id ?>"></canvas></div>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr><th>Course</th><th class="text-end">Unique Users</th><th class="text-end">Last Check-in</th></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($courses as $course => $data): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= h($course) ?></td>
                                        <td class="text-end"><?= $data['count'] ?></td>
                                        <td class="text-end"><?= fmtDate($data['last_checkin']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <div class="fw-semibold">Top Courses by Duration</div>
                        <small class="text-muted">Total time spent per course</small>
                    </div>
                    <div class="card-body">
                        <div style="height:250px;"><canvas id="chartCourseDuration_<?= $id ?>"></canvas></div>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr><th>Course</th><th class="text-end">Duration (min)</th><th class="text-end">Last Check-in</th></tr>
                                </thead>
                                <tbody>
                                <?php foreach (($topDuration[$college] ?? []) as $course => $data): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= h($course) ?></td>
                                        <td class="text-end"><?= $data['minutes'] ?></td>
                                        <td class="text-end"><?= fmtDate($data['last_checkin']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php endforeach;
    echo '<div class="text-end mt-2">
        <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="courses">View All Courses</button>
    </div>';
    return ob_get_clean();
}

function buildDemographicsHtml(array $sexDist, int $total): string {
    ob_start(); ?>
    <div class="row g-4">

        <div class="col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="fw-semibold">Sex Distribution</div>
                    <small class="text-muted">Visitors breakdown by sex</small>
                </div>
                <div class="card-body">
                    <div style="height:300px;"><canvas id="chartSexCheckin"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card border shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-muted small mb-1">Total Check-ins</div>
                            <h3 class="fw-bold mb-0"><?= $total ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-muted small mb-1">Male</div>
                            <h3 class="fw-bold mb-0"><?= $sexDist['Male'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-muted small mb-1">Female</div>
                            <h3 class="fw-bold mb-0"><?= $sexDist['Female'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="text-end mt-3">
        <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="demographics">View All Logs</button>
    </div>
    <?php return ob_get_clean();
}


// ============================================================
// CHART DATA BUILDERS
// ============================================================

function userChartData(array $byClass, string $valueKey): array {
    $labels = [];
    $values = [];
    foreach ($byClass as $users) {
        foreach ($users as $data) {
            $labels[] = $data['name'];
            $values[] = $data[$valueKey] ?? 0;
        }
    }
    return compact('labels', 'values');
}

function courseChartData(array $topCheckins, array $topDuration): array {
    $charts = [];
    foreach ($topCheckins as $college => $courses) {
        $cleanId = preg_replace('/[^a-zA-Z0-9]/', '', $college);
        $charts[$cleanId] = [
            'checkins' => ['labels' => array_keys($courses),                    'values' => array_column($courses, 'count')],
            'duration' => ['labels' => array_keys($topDuration[$college] ?? []), 'values' => array_column($topDuration[$college] ?? [], 'minutes')],
        ];
    }
    return $charts;
}


// ============================================================
// MAIN HANDLERS
// ============================================================

function getTabData(): void {
    $tab     = $_POST['tab']     ?? 'users';
    $filters = getFilters();
    $logs    = fetchLogs($filters);
    $kpis    = buildKPIs($logs, $filters['endDate']);

    $validTabs = ['users', 'colleges', 'courses', 'demographics'];
    if (!in_array($tab, $validTabs)) {
        send(['status' => 'error', 'message' => 'Invalid tab']);
    }

    switch ($tab) {

        case 'users':
            [$topCheckins, $topDuration] = aggregateUsers($logs);
            send([
                'status'    => 'success',
                'html'      => buildUsersHtml($topCheckins, $topDuration),
                'chartData' => [
                    'checkins' => userChartData($topCheckins, 'checkins'),
                    'duration' => userChartData($topDuration, 'minutes'),
                ],
                'kpis' => $kpis,
            ]);
            break;

        case 'colleges':
            [$topCheckins, $topDuration] = aggregateColleges($logs);
            send([
                'status'    => 'success',
                'html'      => buildCollegesHtml($topCheckins, $topDuration),
                'chartData' => [
                    'checkins' => ['labels' => array_keys($topCheckins), 'values' => array_column($topCheckins, 'count')],
                    'duration' => ['labels' => array_keys($topDuration), 'values' => array_column($topDuration, 'minutes')],
                ],
                'kpis' => $kpis,
            ]);
            break;

        case 'courses':
            [$topCheckins, $topDuration] = aggregateCourses($logs);
            send([
                'status'    => 'success',
                'html'      => buildCoursesHtml($topCheckins, $topDuration),
                'chartData' => courseChartData($topCheckins, $topDuration),
                'kpis'      => $kpis,
            ]);
            break;

        case 'demographics':
            $sexDist = aggregateSex($logs);
            send([
                'status'    => 'success',
                'html'      => buildDemographicsHtml($sexDist, count($logs)),
                'chartData' => [
                    'labels' => array_keys($sexDist),
                    'values' => array_values($sexDist),
                ],
                'kpis' => $kpis,
            ]);
            break;
    }
}

function viewAll(): void {
    $tab     = $_POST['tab']  ?? 'users';
    $page    = max(1, (int)($_POST['page'] ?? 1));
    $limit   = 10;
    $offset  = ($page - 1) * $limit;
    $filters = getFilters();
    $logs    = fetchLogs($filters);

    switch ($tab) {

        case 'users':
            $users = [];
            foreach ($logs as $log) {
                $id = $log['id_number'];
                if (!isset($users[$id])) {
                    $users[$id] = ['id' => $log['id_number'], 'type' => $log['classification'], 'library' => $log['libraryName'], 'checkins' => 0, 'minutes' => 0, 'last_checkin' => $log['checkin_time']];
                }
                $users[$id]['checkins']++;
                $users[$id]['minutes'] += duration($log['checkin_time'], $log['checkout_time']);
                if ($log['checkin_time'] > $users[$id]['last_checkin']) $users[$id]['last_checkin'] = $log['checkin_time'];
            }
            uasort($users, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
            $rows = array_values(array_map(fn($u) => ['id' => $u['id_number'], 'type' => $u['type'], 'library' => $u['library'], 'checkins' => $u['checkins'], 'duration' => round($u['minutes']), 'last_checkin' => $u['last_checkin']], array_slice($users, $offset, $limit, true)));
            send(['status' => 'success', 'data' => ['rows' => $rows, 'total' => count($users)]]);
            break;

        case 'colleges':
            $colleges = []; $unique = []; $last = [];
            foreach ($logs as $log) {
                if (isGuest($log)) continue;
                $college = $log['college'] ?: 'Unknown';
                $id      = $log['id_number'];
                if (!isset($unique[$college][$id])) { $unique[$college][$id] = true; $colleges[$college]['checkins'] = ($colleges[$college]['checkins'] ?? 0) + 1; }
                $colleges[$college]['minutes'] = ($colleges[$college]['minutes'] ?? 0) + duration($log['checkin_time'], $log['checkout_time']);
                if (!isset($last[$college]) || $log['checkin_time'] > $last[$college]) $last[$college] = $log['checkin_time'];
            }
            $list = [];
            foreach ($colleges as $name => $d) $list[] = ['name' => $name, 'checkins' => $d['checkins'], 'duration' => round($d['minutes'] ?? 0), 'last_checkin' => $last[$name]];
            usort($list, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
            send(['status' => 'success', 'data' => ['rows' => array_slice($list, $offset, $limit), 'total' => count($list)]]);
            break;

        case 'courses':
            $courses = []; $unique = []; $last = [];
            foreach ($logs as $log) {
                if (isGuest($log)) continue;
                $key = ($log['college'] ?: 'Unknown') . '|' . ($log['course'] ?: 'Unknown');
                $id  = $log['id_number'];
                if (!isset($unique[$key][$id])) { $unique[$key][$id] = true; $courses[$key]['checkins'] = ($courses[$key]['checkins'] ?? 0) + 1; }
                $courses[$key]['minutes'] = ($courses[$key]['minutes'] ?? 0) + duration($log['checkin_time'], $log['checkout_time']);
                if (!isset($last[$key]) || $log['checkin_time'] > $last[$key]) $last[$key] = $log['checkin_time'];
            }
            $list = [];
            foreach ($courses as $key => $d) { [$college, $course] = explode('|', $key, 2); $list[] = ['college' => $college, 'course' => $course, 'checkins' => $d['checkins'], 'duration' => round($d['minutes'] ?? 0), 'last_checkin' => $last[$key]]; }
            usort($list, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
            send(['status' => 'success', 'data' => ['rows' => array_slice($list, $offset, $limit), 'total' => count($list)]]);
            break;

        case 'demographics':
            $list = array_map(fn($log) => ['name' => $log['name'], 'sex' => $log['sex'] ?: 'Unknown', 'checkin' => $log['checkin_time'], 'checkout' => $log['checkout_time'] ?: null, 'duration' => round(duration($log['checkin_time'], $log['checkout_time']))], $logs);
            send(['status' => 'success', 'data' => ['rows' => array_slice($list, $offset, $limit), 'total' => count($list)]]);
            break;

        default:
            send(['status' => 'error', 'message' => 'Invalid tab']);
    }
}
?>
