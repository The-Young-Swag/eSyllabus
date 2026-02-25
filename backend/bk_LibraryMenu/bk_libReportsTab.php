
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