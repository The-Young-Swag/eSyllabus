<?php
include "../../db/dbconnection.php";
header('Content-Type: application/json');

// ------------------- POST PARAMETERS -------------------
$requestedTab = $_POST['tab'] ?? 'users';
$startDate    = $_POST['startDate'] ?? null;
$endDate      = $_POST['endDate'] ?? null;
$classification = $_POST['classification'] ?? 'All';

// ------------------- VALIDATION -------------------
$validTabs = ['users','colleges','courses','demographics'];
if(!in_array($requestedTab, $validTabs)){
    echo json_encode(['status'=>'error','message'=>'Invalid tab']);
    exit;
}

// ------------------- DATE FILTER ONLY (classification removed) -------------------
$dateFilter = '';
$params = [];
if($startDate){
    $dateFilter .= " AND checkin_time >= :startDate";
    $params[':startDate'] = $startDate;
}
if($endDate){
    $dateFilter .= " AND checkin_time <= :endDate";
    $params[':endDate'] = $endDate;
}
// No classification in SQL anymore – it will be applied in PHP

// ------------------- FETCH ALL LOGS (UNFILTERED BY CLASSIFICATION) -------------------
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

$allLogs = execsqlSRS($sqlLogs, 'Select', $params);   // Renamed to $allLogs

// ------------------- GLOBAL KPI METRICS (from unfiltered logs) -------------------
$totalVisits = count($allLogs);
$totalDuration = array_sum(array_map(function($log){
    return calculateDuration($log['checkin_time'], $log['checkout_time']);
}, $allLogs));
$uniqueUsers = count(array_unique(array_column($allLogs,'id_number')));
$avgDuration = $totalVisits ? round($totalDuration / $totalVisits, 1) : 0;
$activeColleges = count(array_unique(array_filter(array_column($allLogs, 'college'))));
$uniqueCourses  = count(array_unique(array_filter(array_column($allLogs, 'course'))));

// ------------------- APPLY CLASSIFICATION FILTER FOR TAB CONTENT -------------------
if ($classification !== 'All') {
    $filteredLogs = array_filter($allLogs, fn($log) => strtolower($log['classification']) === strtolower($classification));
} else {
    $filteredLogs = $allLogs;
}

// Overwrite $libraryLogs for the rest of the code (tabs use filtered data)
$libraryLogs = $filteredLogs;

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

// ------------------- USERS TAB -------------------
$classifications = ['Student','Employee','Guest'];
$topCheckins = [];
$topDuration = [];

foreach($classifications as $class){
    $logsByClass = filterLogsByClassification($libraryLogs, $class);

    $checkinCounts = [];
    $durationCounts = [];
    foreach($logsByClass as $log){
        $key = $log['id_number'].'|'.$log['name'];
        $checkinCounts[$key] = ($checkinCounts[$key] ?? 0) + 1;
        $durationCounts[$key] = ($durationCounts[$key] ?? 0) + calculateDuration($log['checkin_time'], $log['checkout_time']);
    }

    $topCheckins[$class] = topN($checkinCounts, 3);
    $topDuration[$class] = topN($durationCounts, 3);
}

// ------------------- COLLEGES TAB -------------------
$collegeLogs = array_filter($libraryLogs, fn($log) => strtolower($log['classification']) !== 'guest');

$collegesCheckin = [];
$collegesDuration = [];
$collegeUsers = [];

foreach($collegeLogs as $log){
    $college = $log['college'] ?: 'Unknown';
    $userId = $log['id_number'];
    $duration = calculateDuration($log['checkin_time'], $log['checkout_time']);

    if(!isset($collegeUsers[$college][$userId])){
        $collegeUsers[$college][$userId] = true;
        $collegesCheckin[$college] = ($collegesCheckin[$college] ?? 0) + 1;
    }
    $collegesDuration[$college] = ($collegesDuration[$college] ?? 0) + $duration;
}

$top3CollegesCheckin = topN($collegesCheckin, 3);
$top3CollegesDuration = topN($collegesDuration, 3);

// ------------------- COURSES TAB -------------------
$coursesCheckin = [];
$coursesDuration = [];
$courseUsers = [];

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
}

$topCoursesCheckin = [];
$topCoursesDuration = [];
foreach($coursesCheckin as $college => $courseList){
    $topCoursesCheckin[$college] = topN($courseList, 3);
}
foreach($coursesDuration as $college => $courseList){
    $topCoursesDuration[$college] = topN($courseList, 3);
}

// ------------------- DEMOGRAPHICS -------------------
$sexDistribution = [];
foreach($libraryLogs as $log){
    $sex = $log['sex'] ?: 'Unknown';
    $sexDistribution[$sex] = ($sexDistribution[$sex] ?? 0) + 1;
}

// ------------------- HTML GENERATION (unchanged) -------------------
ob_start();

switch($requestedTab){
    case 'users':
        ?>
        <div class="tab-pane fade show active" id="users" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-1">User Performance Overview</h5>
                    <small class="text-muted">Engagement metrics and behavioral insights</small>
                </div>
                <div class="badge bg-light text-dark px-3 py-2">
                    <i class="fas fa-users me-1 text-muted"></i> <?= count($libraryLogs) ?> Active Users
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-3">
                <label class="small fw-semibold me-1">Classification:</label>
                <select class="form-select form-select-sm" id="userClassificationFilter" style="width: auto;">
                    <option value="All" <?= $classification == 'All' ? 'selected' : '' ?>>All</option>
                    <option value="Student" <?= $classification == 'Student' ? 'selected' : '' ?>>Student</option>
                    <option value="Employee" <?= $classification == 'Employee' ? 'selected' : '' ?>>Employee</option>
                    <option value="Guest" <?= $classification == 'Guest' ? 'selected' : '' ?>>Guest</option>
                </select>
            </div>
            <div class="row g-4 mb-4">
                <?php foreach($classifications as $class): 
                    $count = count(filterLogsByClassification($libraryLogs, $class));
                ?>
                <div class="col-md-4">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small"><?= $class ?>s</div>
                            <div class="d-flex justify-content-between align-items-end mt-2">
                                <h3 class="fw-bold mb-0"><?= $count ?></h3>
                                <span class="text-success small"><i class="fas fa-arrow-up me-1"></i>+0%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

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
                                            <th class="text-end">Check-ins</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($topCheckins as $class => $users):
                                            foreach($users as $key => $count):
                                                [$idNumber,$name] = explode('|',$key);
                                        ?>
                                        <tr>
                                            <td class="fw-semibold"><?= htmlspecialchars($name) ?></td>
                                            <td><span class="badge bg-light text-dark"><?= $class ?></span></td>
                                            <td class="text-end"><?= $count ?></td>
                                        </tr>
                                        <?php endforeach; endforeach; ?>
                                    </tbody>
                                </table>
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
                                            <th class="text-end">Duration (min)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($topDuration as $class => $users):
                                            foreach($users as $key => $minutes):
                                                [$idNumber,$name] = explode('|',$key);
                                        ?>
                                        <tr>
                                            <td class="fw-semibold"><?= htmlspecialchars($name) ?></td>
                                            <td><span class="badge bg-light text-dark"><?= $class ?></span></td>
                                            <td class="text-end"><?= round($minutes) ?></td>
                                        </tr>
                                        <?php endforeach; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        break;

case 'colleges':
?>
<div class="tab-pane fade show active" id="college" role="tabpanel">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1">College Analytics Overview</h5>
            <small class="text-muted">Top performing colleges based on user check-ins and duration</small>
        </div>
        <div class="badge bg-light text-dark px-3 py-2">
            <i class="fas fa-university me-1 text-muted"></i> <?= count($collegeLogs) ?> Active Users (Excluding Guests)
        </div>
    </div>

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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top3CollegesCheckin as $college => $count): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($college) ?></td>
                                    <td class="text-end"><?= $count ?></td>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top3CollegesDuration as $college => $minutes): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($college) ?></td>
                                    <td class="text-end"><?= round($minutes) ?></td>
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
<?php
break;

case 'courses':
?>
<div class="tab-pane fade show active" id="course" role="tabpanel">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1">Course Analytics Overview</h5>
            <small class="text-muted">Top performing courses under each college</small>
        </div>
        <div class="badge bg-light text-dark px-3 py-2">
            <i class="fas fa-book me-1 text-muted"></i> <?= count($collegeLogs) ?> Active Users
        </div>
    </div>

    <?php foreach($topCoursesCheckin as $college => $courses): ?>
    <div class="col-12 mb-4">
        <h6 class="fw-semibold"><?= htmlspecialchars($college) ?></h6>
        <div class="row g-4">

            <!-- Top Courses by Check-ins -->
            <div class="col-md-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header">Top Courses by Check-ins</div>
                    <div class="card-body">
                        <div style="height:250px;"><canvas id="chartCourseCheckin_<?= preg_replace('/[^a-zA-Z0-9]/','', $college) ?>"></canvas>
</div>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Course</th>
                                        <th class="text-end">Check-ins</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($courses as $course => $count): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($course) ?></td>
                                        <td class="text-end"><?= $count ?></td>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($topCoursesDuration[$college] as $course => $minutes): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($course) ?></td>
                                        <td class="text-end"><?= round($minutes) ?></td>
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
</div>
<?php
break;

case 'demographics':
?>
<div class="tab-pane fade show active" id="demographics" role="tabpanel">

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

</div>
<?php
break;


      default:
        $html = "<div class='text-center text-danger p-4'>Tab not found.</div>";
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
    'activeColleges' => $activeColleges,
    'uniqueCourses'  => $uniqueCourses,

    // Tab-specific data (filtered)
    'logs'   => $libraryLogs,
    'topCheckins' => $topCheckins,
    'topDuration' => $topDuration,
    'top3CollegesCheckin' => $top3CollegesCheckin,
    'top3CollegesDuration' => $top3CollegesDuration,
    'topCoursesCheckin' => $topCoursesCheckin,
    'topCoursesDuration' => $topCoursesDuration,
    'sexDistribution' => $sexDistribution
]);
?>