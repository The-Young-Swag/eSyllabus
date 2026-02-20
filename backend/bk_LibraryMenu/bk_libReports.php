<?php
include "../../db/dbconnection.php";
header('Content-Type: application/json');

$action = $_POST['action'] ?? 'tab';          // 'tab' or 'viewAll'
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// ------------------- POST PARAMETERS -------------------
$requestedTab = $_POST['tab'] ?? 'users';
$startDate    = $_POST['startDate'] ?? null;
$endDate      = $_POST['endDate'] ?? null;
$classification = $_POST['classification'] ?? 'All';
$libraryFilter = $_POST['library'] ?? 'All';   // new library filter

// ------------------- VALIDATION -------------------
$validTabs = ['users','colleges','courses','demographics'];
if(!in_array($requestedTab, $validTabs)){
    echo json_encode(['status'=>'error','message'=>'Invalid tab']);
    exit;
}

// ------------------- BUILD FILTERS -------------------
$dateFilter = '';
$params = [];

// Date range (inclusive full day)
if($startDate){
    $dateFilter .= " AND CAST(checkin_time AS DATE) >= :startDate";
    $params[':startDate'] = $startDate;
}
if($endDate){
    $dateFilter .= " AND CAST(checkin_time AS DATE) <= :endDate";
    $params[':endDate'] = $endDate;
}

// Classification filter
if($classification !== 'All'){
    $dateFilter .= " AND classification = :classification";
    $params[':classification'] = $classification;
}

// Library filter (SectionID)
if($libraryFilter !== 'All'){
    $dateFilter .= " AND l.library = :library";
    $params[':library'] = $libraryFilter;
}

// ------------------- FETCH LOGS -------------------
$sqlLogs = "
    SELECT 
        l.id, l.id_number, l.name, l.college, l.course, l.library,
        s.SectionName AS libraryName,
        l.checkin_time, l.checkout_time,
        l.sex, l.classification
    FROM Library_logs l
    LEFT JOIN LibrarySection s ON l.library = s.SectionID
    WHERE 1=1 $dateFilter
    ORDER BY l.checkin_time DESC
";

// IMPORTANT: Pass the $params array as the third argument
$libraryLogs = execsqlSRS($sqlLogs, 'Select', $params);
$totalVisits = count($libraryLogs);

// ------------------- GLOBAL KPI METRICS -------------------
$totalVisits = count($libraryLogs);
$totalDuration = array_sum(array_map(function($log){
    return calculateDuration($log['checkin_time'], $log['checkout_time']);
}, $libraryLogs));
$uniqueUsers = count(array_unique(array_column($libraryLogs,'id_number')));
$avgDuration = $totalVisits ? round($totalDuration / $totalVisits, 1) : 0;


// Check‑ins on the selected end date (or last date in range)
$endDateCheckins = 0;
if ($endDate) {
    $endDateCheckins = count(array_filter($libraryLogs, function($log) use ($endDate) {
        return substr($log['checkin_time'], 0, 10) === $endDate;
    }));
}
// ------------------- HELPER FUNCTIONS -------------------
function calculateDuration($checkin, $checkout){
    if(!$checkout) return 0;
    return (strtotime($checkout) - strtotime($checkin)) / 60; // minutes
}

function filterLogsByClassification(array $logs, string $classification): array {
    return array_filter($logs, fn($log) => strtolower($log['classification']) === strtolower($classification));
}

function topN(array $array, int $n = 3): array {
    arsort($array);
    return array_slice($array, 0, $n, true);
}


// ------------------- ACTION DISPATCH -------------------
if ($action === 'viewAll') {
    // Return paginated data for the current tab
    $responseData = [];
    switch ($requestedTab) {
        case 'users':
            $allUsers = [];
            foreach ($libraryLogs as $log) {
                $key = $log['id_number'];
                if (!isset($allUsers[$key])) {
                    $allUsers[$key] = [
                        'name'       => $log['name'],
                        'type'       => $log['classification'],
                        'library'    => $log['libraryName'],
                        'checkins'   => 0,
                        'duration'   => 0,
                        'last_checkin' => $log['checkin_time']
                    ];
                }
                $allUsers[$key]['checkins']++;
                $allUsers[$key]['duration'] += calculateDuration($log['checkin_time'], $log['checkout_time']);
                if ($log['checkin_time'] > $allUsers[$key]['last_checkin']) {
                    $allUsers[$key]['last_checkin'] = $log['checkin_time'];
                }
            }
            // Sort by checkins desc, then paginate
            uasort($allUsers, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
            $total = count($allUsers);
            $paginated = array_slice($allUsers, $offset, $limit, true);
            $responseData = ['rows' => array_values($paginated), 'total' => $total];
            break;

        case 'colleges':
            $colleges = [];
            foreach ($libraryLogs as $log) {
                if (strtolower($log['classification']) === 'guest') continue;
                $college = $log['college'] ?: 'Unknown';
                if (!isset($colleges[$college])) {
                    $colleges[$college] = [
                        'name'         => $college,
                        'unique_users' => [],
                        'duration'     => 0,
                        'last_checkin' => $log['checkin_time']
                    ];
                }
                $colleges[$college]['unique_users'][$log['id_number']] = true;
                $colleges[$college]['duration'] += calculateDuration($log['checkin_time'], $log['checkout_time']);
                if ($log['checkin_time'] > $colleges[$college]['last_checkin']) {
                    $colleges[$college]['last_checkin'] = $log['checkin_time'];
                }
            }
            // Build final array with checkin counts
            $collegeList = [];
            foreach ($colleges as $name => $data) {
                $collegeList[] = [
                    'name'         => $name,
                    'checkins'     => count($data['unique_users']),
                    'duration'     => $data['duration'],
                    'last_checkin' => $data['last_checkin']
                ];
            }
            usort($collegeList, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
            $total = count($collegeList);
            $paginated = array_slice($collegeList, $offset, $limit);
            $responseData = ['rows' => $paginated, 'total' => $total];
            break;

        case 'courses':
            $courses = [];
            foreach ($libraryLogs as $log) {
                if (strtolower($log['classification']) === 'guest') continue;
                $college = $log['college'] ?: 'Unknown';
                $course  = $log['course'] ?: 'Unknown';
                $key = $college . '|' . $course;
                if (!isset($courses[$key])) {
                    $courses[$key] = [
                        'college'      => $college,
                        'course'       => $course,
                        'unique_users' => [],
                        'duration'     => 0,
                        'last_checkin' => $log['checkin_time']
                    ];
                }
                $courses[$key]['unique_users'][$log['id_number']] = true;
                $courses[$key]['duration'] += calculateDuration($log['checkin_time'], $log['checkout_time']);
                if ($log['checkin_time'] > $courses[$key]['last_checkin']) {
                    $courses[$key]['last_checkin'] = $log['checkin_time'];
                }
            }
            $courseList = [];
            foreach ($courses as $data) {
                $courseList[] = [
                    'college'     => $data['college'],
                    'course'      => $data['course'],
                    'checkins'    => count($data['unique_users']),
                    'duration'    => $data['duration'],
                    'last_checkin'=> $data['last_checkin']
                ];
            }
            usort($courseList, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
            $total = count($courseList);
            $paginated = array_slice($courseList, $offset, $limit);
            $responseData = ['rows' => $paginated, 'total' => $total];
            break;

        case 'demographics':
            // For demographics we simply list all logs paginated (no aggregation)
            $allLogs = array_map(function($log) {
                return [
                    'name'       => $log['name'],
                    'sex'        => $log['sex'],
                    'checkin'    => $log['checkin_time'],
                    'checkout'   => $log['checkout_time'],
                    'duration'   => calculateDuration($log['checkin_time'], $log['checkout_time'])
                ];
            }, $libraryLogs);
            $total = count($allLogs);
            $paginated = array_slice($allLogs, $offset, $limit);
            $responseData = ['rows' => $paginated, 'total' => $total];
            break;
    }
    echo json_encode(['status'=>'success', 'data'=>$responseData]);
    exit;
}

// ------------------- USERS TAB -------------------
$classifications = ['Student','Employee','Guest'];
$topCheckins = []; $topDuration = [];

foreach($classifications as $class){
    $logsByClass = filterLogsByClassification($libraryLogs, $class);
    $userCheckins = [];   // id_number => count
    $userDuration = [];   // id_number => total minutes
    $userData = [];       // id_number => ['name'=>..., 'library'=>..., 'last_checkin'=>...]

    foreach($logsByClass as $log){
        $id = $log['id_number'];
        $name = $log['name'];
        $library = $log['libraryName'];
        $checkinTime = $log['checkin_time'];

        // Accumulate check-ins (count visits, not unique days)
        $userCheckins[$id] = ($userCheckins[$id] ?? 0) + 1;
        $userDuration[$id] = ($userDuration[$id] ?? 0) + calculateDuration($log['checkin_time'], $log['checkout_time']);

        // Store user details (first encountered, but last checkin time updated)
        if (!isset($userData[$id])) {
            $userData[$id] = [
                'name' => $name,
                'library' => $library,
                'last_checkin' => $checkinTime
            ];
        } else {
            if ($checkinTime > $userData[$id]['last_checkin']) {
                $userData[$id]['last_checkin'] = $checkinTime;
            }
        }
    }

    // Build top check-ins (sorted by count)
    arsort($userCheckins);
    $topCheckins[$class] = [];
    $count = 0;
    foreach ($userCheckins as $id => $cnt) {
        if ($count >= 3) break;
        $topCheckins[$class][$id] = [
            'count' => $cnt,
            'name' => $userData[$id]['name'],
            'library' => $userData[$id]['library'],
            'last_checkin' => $userData[$id]['last_checkin']
        ];
        $count++;
    }

    // Build top duration (sorted by minutes)
    arsort($userDuration);
    $topDuration[$class] = [];
    $count = 0;
    foreach ($userDuration as $id => $mins) {
        if ($count >= 3) break;
        $topDuration[$class][$id] = [
            'minutes' => $mins,
            'name' => $userData[$id]['name'],
            'library' => $userData[$id]['library'],
            'last_checkin' => $userData[$id]['last_checkin']
        ];
        $count++;
    }
}


// ------------------- COLLEGES TAB -------------------
$collegeLogs = array_filter($libraryLogs, fn($log) => strtolower($log['classification']) !== 'guest');

$collegesCheckin = []; $collegesDuration = []; $collegeUsers = []; $collegeLastCheckin = [];
foreach($collegeLogs as $log){
    $college = $log['college'] ?: 'Unknown';
    $userId = $log['id_number'];
    $duration = calculateDuration($log['checkin_time'], $log['checkout_time']);

    if(!isset($collegeUsers[$college][$userId])){
        $collegeUsers[$college][$userId] = true;
        $collegesCheckin[$college] = ($collegesCheckin[$college] ?? 0) + 1;
    }
    $collegesDuration[$college] = ($collegesDuration[$college] ?? 0) + $duration;
    // track last checkin
    if (!isset($collegeLastCheckin[$college]) || $log['checkin_time'] > $collegeLastCheckin[$college]) {
        $collegeLastCheckin[$college] = $log['checkin_time'];
    }
}
$top3CollegesCheckin = [];
foreach (topN($collegesCheckin, 3) as $college => $count) {
    $top3CollegesCheckin[$college] = ['count' => $count, 'last_checkin' => $collegeLastCheckin[$college]];
}
$top3CollegesDuration = [];
foreach (topN($collegesDuration, 3) as $college => $mins) {
    $top3CollegesDuration[$college] = ['minutes' => $mins, 'last_checkin' => $collegeLastCheckin[$college]];
}
// After building $collegesCheckin and $collegeLastCheckin:
arsort($collegesCheckin);
$top3CollegesCheckin = [];
$count = 0;
foreach ($collegesCheckin as $college => $checkins) {
    if ($count >= 3) break;
    $top3CollegesCheckin[$college] = [
        'count' => $checkins,
        'last_checkin' => $collegeLastCheckin[$college]
    ];
    $count++;
}

arsort($collegesDuration);
$top3CollegesDuration = [];
$count = 0;
foreach ($collegesDuration as $college => $mins) {
    if ($count >= 3) break;
    $top3CollegesDuration[$college] = [
        'minutes' => $mins,
        'last_checkin' => $collegeLastCheckin[$college]
    ];
    $count++;
}

// ------------------- COURSES TAB -------------------
$coursesCheckin = []; $coursesDuration = []; $courseUsers = []; $courseLastCheckin = [];
foreach($collegeLogs as $log){
    $college = $log['college'] ?: 'Unknown';
    $course  = $log['course'] ?: 'Unknown';
    $userId  = $log['id_number'];
    $duration = calculateDuration($log['checkin_time'], $log['checkout_time']);

    if(!isset($courseUsers[$college][$course][$userId])){
        $courseUsers[$college][$course][$userId] = true;
        $coursesCheckin[$college][$course] = ($coursesCheckin[$college][$course] ?? 0) + 1;
    }
    $coursesDuration[$college][$course] = ($coursesDuration[$college][$course] ?? 0) + $duration;
    // last checkin per (college,course)
    $key = $college.'|'.$course;
    if (!isset($courseLastCheckin[$key]) || $log['checkin_time'] > $courseLastCheckin[$key]) {
        $courseLastCheckin[$key] = $log['checkin_time'];
    }
}
$topCoursesCheckin = []; $topCoursesDuration = [];
foreach($coursesCheckin as $college => $courseList){
    $topCoursesCheckin[$college] = [];
    foreach (topN($courseList, 3) as $course => $count) {
        $key = $college.'|'.$course;
        $topCoursesCheckin[$college][$course] = ['count' => $count, 'last_checkin' => $courseLastCheckin[$key]];
    }
}
foreach($coursesDuration as $college => $courseList){
    $topCoursesDuration[$college] = [];
    foreach (topN($courseList, 3) as $course => $mins) {
        $key = $college.'|'.$course;
        $topCoursesDuration[$college][$course] = ['minutes' => $mins, 'last_checkin' => $courseLastCheckin[$key]];
    }
}
// After building $coursesCheckin and $courseLastCheckin:
$topCoursesCheckin = [];
$topCoursesDuration = [];

foreach ($coursesCheckin as $college => $courseList) {
    arsort($courseList);
    $topCoursesCheckin[$college] = [];
    $count = 0;
    foreach ($courseList as $course => $checkins) {
        if ($count >= 3) break;
        $key = $college.'|'.$course;
        $topCoursesCheckin[$college][$course] = [
            'count' => $checkins,
            'last_checkin' => $courseLastCheckin[$key]
        ];
        $count++;
    }
}

foreach ($coursesDuration as $college => $courseList) {
    arsort($courseList);
    $topCoursesDuration[$college] = [];
    $count = 0;
    foreach ($courseList as $course => $mins) {
        if ($count >= 3) break;
        $key = $college.'|'.$course;
        $topCoursesDuration[$college][$course] = [
            'minutes' => $mins,
            'last_checkin' => $courseLastCheckin[$key]
        ];
        $count++;
    }
}

// ------------------- DEMOGRAPHICS -------------------
$sexDistribution = [];
foreach($libraryLogs as $log){
    $sex = $log['sex'] ?: 'Unknown';
    $sexDistribution[$sex] = ($sexDistribution[$sex] ?? 0) + 1;
}

// ------------------- HTML GENERATION -------------------
ob_start();

switch($requestedTab){
   case 'users':
?>
<div class="tab-pane fade show active" id="users" role="tabpanel">
    <!-- header unchanged -->
    <div class="row g-4">
    <!-- Top Users Check-ins -->
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
                                <th>User</th>
                                <th>Type</th>
                                <th>Library</th>
                                <th class="text-end">Check-ins</th>
                                <th class="text-end">Last Check-in</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($topCheckins as $class => $users): ?>
                            <?php foreach($users as $id => $data): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($data['name']) ?></td>
                                    <td><span class="badge bg-light text-dark"><?= $class ?></span></td>
                                    <td><?= htmlspecialchars($data['library']) ?></td>
                                    <td class="text-end"><?= $data['count'] ?></td>
                                    <td class="text-end"><?= date('M j, Y g:i A', strtotime($data['last_checkin'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-end">
                    <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="users" data-type="checkins">View All Users</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Users Duration -->
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
                                <th>User</th>
                                <th>Type</th>
                                <th>Library</th>
                                <th class="text-end">Duration (min)</th>
                                <th class="text-end">Last Check-in</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($topDuration as $class => $users): ?>
                            <?php foreach($users as $id => $data): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($data['name']) ?></td>
                                    <td><span class="badge bg-light text-dark"><?= $class ?></span></td>
                                    <td><?= htmlspecialchars($data['library']) ?></td>
                                    <td class="text-end"><?= round($data['minutes']) ?></td>
                                    <td class="text-end"><?= date('M j, Y g:i A', strtotime($data['last_checkin'])) ?></td>
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
<?php
break;

case 'colleges':
?>
<!-- Colleges Tab Content -->
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
                                <th class="text-end">Check-ins</th>
                                <th class="text-end">Last Check-in</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($top3CollegesCheckin as $college => $data): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($college) ?></td>
                                <td class="text-end"><?= $data['count'] ?></td>
                                <td class="text-end"><?= date('M j, Y g:i A', strtotime($data['last_checkin'])) ?></td>
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
                        <?php foreach($top3CollegesDuration as $college => $data): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($college) ?></td>
                                <td class="text-end"><?= round($data['minutes']) ?></td>
                                <td class="text-end"><?= date('M j, Y g:i A', strtotime($data['last_checkin'])) ?></td>
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
break;

case 'courses':
?>
<!-- Courses Tab Content -->
<?php foreach($topCoursesCheckin as $college => $courses): ?>
<div class="col-12 mb-4">
    <h6 class="fw-semibold"><?= htmlspecialchars($college) ?></h6>
    <div class="row g-4">
        <!-- Top Courses by Check-ins -->
        <div class="col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header">Top Courses by Check-ins</div>
                <div class="card-body">
                    <div style="height:250px;"><canvas id="chartCourseCheckin_<?= preg_replace('/[^a-zA-Z0-9]/','', $college) ?>"></canvas></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Course</th>
                                    <th class="text-end">Check-ins</th>
                                    <th class="text-end">Last Check-in</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($courses as $course => $data): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($course) ?></td>
                                    <td class="text-end"><?= $data['count'] ?></td>
                                    <td class="text-end"><?= date('M j, Y g:i A', strtotime($data['last_checkin'])) ?></td>
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
                    <div style="height:250px;"><canvas id="chartCourseDuration_<?= md5($college) ?>"></canvas></div>
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
                            <?php foreach($topCoursesDuration[$college] as $course => $data): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($course) ?></td>
                                    <td class="text-end"><?= round($data['minutes']) ?></td>
                                    <td class="text-end"><?= date('M j, Y g:i A', strtotime($data['last_checkin'])) ?></td>
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
<?php endforeach; ?>
<div class="text-end">
    <button class="btn btn-sm btn-outline-primary view-all-btn" data-tab="courses">View All Courses</button>
</div>
<?php
break;

case 'demographics':
?>
<!-- Demographics Tab Content -->
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
                        <div class="text-muted small">Total Users</div>
                        <h3 class="fw-bold mb-0"><?= count($libraryLogs) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small">Male Users</div>
                        <h3 class="fw-bold mb-0"><?= $sexDistribution['Male'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small">Female Users</div>
                        <h3 class="fw-bold mb-0"><?= $sexDistribution['Female'] ?? 0 ?></h3>
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
break;

default:
    echo "<div class='text-center text-danger p-4'>Tab not found.</div>";
}

$html = ob_get_clean();


// ------------------- JSON RESPONSE -------------------
echo json_encode([
    'status' => 'success',
    'html'   => $html,

    // Global KPIs (from unfiltered logs)
    'totalVisits'   => $totalVisits,
    'totalDuration' => round($totalDuration),
    'avgDuration'   => $avgDuration,
    'uniqueUsers'   => $uniqueUsers,


    // Tab-specific data (filtered)
    'logs'   => $libraryLogs,
    'topCheckins' => $topCheckins,
    'topDuration' => $topDuration,
    'top3CollegesCheckin' => $top3CollegesCheckin,
    'top3CollegesDuration' => $top3CollegesDuration,
    'topCoursesCheckin' => $topCoursesCheckin,
    'topCoursesDuration' => $topCoursesDuration,
    'sexDistribution' => $sexDistribution,
    'endDateCheckins' => $endDateCheckins
]);
?>