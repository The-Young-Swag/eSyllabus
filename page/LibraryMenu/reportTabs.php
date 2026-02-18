<?php
/**
 * reportTabs.php
 * Generates tab content dynamically for AJAX requests
 * POST { tab: string }
 */

$tab = $_POST['tab'] ?? 'users';

// Define tabs, cards, and charts dynamically
$tabs = [
    "users" => [
        ["title" => "Top Colleges by Check-ins", "id" => "chartCollegesCheckin"],
        ["title" => "Top Colleges by Duration", "id" => "chartCollegesDuration"],
    ],
    "colleges" => [
        ["title" => "Top Colleges by Check-ins", "id" => "chartCollegesCheckin2"],
        ["title" => "Top Colleges by Duration", "id" => "chartCollegesDuration2"],
    ],
    "courses" => [
        ["title" => "Top Courses by Check-ins", "id" => "chartCoursesCheckin"],
        ["title" => "Top Courses by Duration", "id" => "chartCoursesDuration"],
    ],
    "demographics" => [
        ["title" => "Check-ins by Gender", "id" => "chartSexCheckin"],
        ["title" => "Duration by Gender", "id" => "chartSexDuration"],
    ],
];

// Generate HTML dynamically
if (!isset($tabs[$tab])) {
    echo "<div class='text-center p-4 text-danger'>Invalid tab selected.</div>";
    exit;
}

echo '<div class="row g-4">';
foreach ($tabs[$tab] as $card) {
    $title = htmlspecialchars($card["title"]);
    $canvasId = htmlspecialchars($card["id"]);
    echo <<<HTML
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3">
                <h6 class="fw-semibold mb-0">$title</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="$canvasId"></canvas>
                </div>
            </div>
        </div>
    </div>
HTML;
}
echo '</div>';



<!-- USERS TAB -->
	        <div class="tab-pane fade" id="users" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Top Colleges by Check-ins</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartCollegesCheckin"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Top Colleges by Duration</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartCollegesDuration"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		
		
        <!-- COLLEGES TAB -->
        <div class="tab-pane fade" id="colleges" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Top Colleges by Check-ins</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartCollegesCheckin"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Top Colleges by Duration</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartCollegesDuration"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		
		<!-- COURSES TAB -->
        <div class="tab-pane fade" id="courses" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Top Courses by Check-ins</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartCoursesCheckin"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Top Courses by Duration</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartCoursesDuration"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		
		        <!-- DEMOGRAPHICS TAB -->
        <div class="tab-pane fade" id="demographics" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Check-ins by Gender</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartSexCheckin"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Duration by Gender</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartSexDuration"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>