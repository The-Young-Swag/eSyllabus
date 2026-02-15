<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Library Ranking Analytics</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- FontAwesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    background-color: #f4f6f9;
}
.section-title {
    font-weight: 600;
    margin-top: 50px;
    margin-bottom: 20px;
}
.card {
    border-radius: 12px;
}
.chart-container {
    height: 320px;
}
</style>
</head>

<body>

<div class="container-fluid py-4 px-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Library Ranking Analytics</h4>
        <small class="text-muted">Top 3 insights based on selected date range</small>
    </div>
    <button class="btn btn-outline-danger btn-sm">
        <i class="fas fa-file-pdf me-1"></i> Export PDF
    </button>
</div>

<!-- FILTERS -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">Start Date</label>
                <input type="date" class="form-control" id="startDate">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">End Date</label>
                <input type="date" class="form-control" id="endDate">
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-primary" id="generateBtn">
                    Generate Report
                </button>
            </div>
            <div class="col-md-3 text-end">
                <small class="text-muted" id="dateRangeLabel">
                    Showing data for: All Dates
                </small>
            </div>
        </div>
    </div>
</div>

<!-- STUDENTS -->
<h5 class="section-title">Students Ranking</h5>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Top 3 Highest Check-Ins</small>
                <div class="chart-container">
                    <canvas id="chartStudentsCheckin"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Top 3 Longest Stay Duration (Hours)</small>
                <div class="chart-container">
                    <canvas id="chartStudentsDuration"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- COLLEGES -->
<h5 class="section-title">Colleges Ranking</h5>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Top 3 Colleges by Check-Ins</small>
                <div class="chart-container">
                    <canvas id="chartCollegesCheckin"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Top 3 Colleges by Longest Stay</small>
                <div class="chart-container">
                    <canvas id="chartCollegesDuration"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- COURSES -->
<h5 class="section-title">Courses Ranking</h5>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Top 3 Courses by Check-Ins</small>
                <div class="chart-container">
                    <canvas id="chartCoursesCheckin"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Top 3 Courses by Longest Stay</small>
                <div class="chart-container">
                    <canvas id="chartCoursesDuration"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SEX -->
<h5 class="section-title">Demographics</h5>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Most Check-Ins by Sex</small>
                <div class="chart-container">
                    <canvas id="chartSexCheckin"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Longest Stay Duration by Sex</small>
                <div class="chart-container">
                    <canvas id="chartSexDuration"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<script>
const charts = {};

/* STUDENT MASTER DATA */
const students = {
    "2022100114": {name: "Rivz Rivera", sex: "Male", college: "CET", course: "BSIT"},
    "2022100114_1": {name: "Gojo Satoru", sex: "Male", college: "CET", course: "BSIT"},
    "2022100115": {name: "Nobara Kugisaki", sex: "Female", college: "CVM", course: "DVM"},
    "2022100116": {name: "Shoko Ieiri", sex: "Female", college: "CAS", course: "BSPsych"},
    "2022100117": {name: "Yuji Itadori", sex: "Male", college: "CED", course: "BSIEE"},
    "2022100118": {name: "Megumi Fushiguro", sex: "Male", college: "CET", course: "BSCS"},
    "2022100119": {name: "Maki Zenin", sex: "Female", college: "CVM", course: "BSBio"},
    "2022100120": {name: "Toge Inumaki", sex: "Male", college: "CAS", course: "BSChem"}
};

/* SAMPLE LOGS — for demo, each student has 1–3 check-ins with duration in hours */
const logs = [
    {date:"2026-02-01", student:"Rivz Rivera", college:"CET", course:"BSIT", sex:"Male", duration:4},
    {date:"2026-02-01", student:"Gojo Satoru", college:"CET", course:"BSIT", sex:"Male", duration:3},
    {date:"2026-02-02", student:"Nobara Kugisaki", college:"CVM", course:"DVM", sex:"Female", duration:5},
    {date:"2026-02-03", student:"Shoko Ieiri", college:"CAS", course:"BSPsych", sex:"Female", duration:6},
    {date:"2026-02-03", student:"Yuji Itadori", college:"CED", course:"BSIEE", sex:"Male", duration:2},
    {date:"2026-02-04", student:"Megumi Fushiguro", college:"CET", course:"BSCS", sex:"Male", duration:8},
    {date:"2026-02-05", student:"Maki Zenin", college:"CVM", course:"BSBio", sex:"Female", duration:4},
    {date:"2026-02-06", student:"Toge Inumaki", college:"CAS", course:"BSChem", sex:"Male", duration:3},
    {date:"2026-02-06", student:"Rivz Rivera", college:"CET", course:"BSIT", sex:"Male", duration:5},
    {date:"2026-02-07", student:"Gojo Satoru", college:"CET", course:"BSIT", sex:"Male", duration:6}
];

/* =============================
   UTILITIES
============================= */
function renderChart(id, type, labels, data, color){
    if(charts[id]) charts[id].destroy();
    const options = {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{legend:{display:type==='bar'?false:true}},
    };
    if(type==='bar'){
        options.scales = {
            y:{
                beginAtZero:true,
                ticks:{precision:0, stepSize:1},
                title:{display:true, text:'Total Check-Ins / Hours'}
            }
        }
    }
    charts[id] = new Chart(document.getElementById(id), {
        type,
        data:{labels,datasets:[{data,backgroundColor:color,borderRadius:6}]},
        options
    });
}

function groupAndRank(data, key, metric){
    const grouped = {};
    data.forEach(item=>{
        if(!grouped[item[key]]) grouped[item[key]]={count:0,duration:0};
        grouped[item[key]].count++;
        grouped[item[key]].duration+=item.duration;
    });
    return Object.entries(grouped)
        .map(([key,val])=>({label:key,value:metric==="count"?val.count:val.duration}))
        .sort((a,b)=>b.value-a.value)
        .slice(0,3);
}

function filterByDate(){
    const start=document.getElementById("startDate").value;
    const end=document.getElementById("endDate").value;
    if(!start||!end) return logs;
    return logs.filter(log=>log.date>=start && log.date<=end);
}

/* =============================
   GENERATE REPORT
============================= */
document.getElementById("generateBtn").addEventListener("click",()=>{
    const filtered = filterByDate();

    document.getElementById("dateRangeLabel").innerText =
        `Showing data for: ${document.getElementById("startDate").value || "All"} to ${document.getElementById("endDate").value || "All"}`;

    const studentsCheckin = groupAndRank(filtered,"student","count");
    const studentsDuration = groupAndRank(filtered,"student","duration");
    const collegesCheckin = groupAndRank(filtered,"college","count");
    const collegesDuration = groupAndRank(filtered,"college","duration");
    const coursesCheckin = groupAndRank(filtered,"course","count");
    const coursesDuration = groupAndRank(filtered,"course","duration");
    const sexCheckin = groupAndRank(filtered,"sex","count");
    const sexDuration = groupAndRank(filtered,"sex","duration");

    renderChart("chartStudentsCheckin","bar",
        studentsCheckin.map(x=>x.label),
        studentsCheckin.map(x=>x.value),
        "#0d6efd");

    renderChart("chartStudentsDuration","bar",
        studentsDuration.map(x=>x.label),
        studentsDuration.map(x=>x.value),
        "#6610f2");

    renderChart("chartCollegesCheckin","bar",
        collegesCheckin.map(x=>x.label),
        collegesCheckin.map(x=>x.value),
        "#198754");

    renderChart("chartCollegesDuration","bar",
        collegesDuration.map(x=>x.label),
        collegesDuration.map(x=>x.value),
        "#20c997");

    renderChart("chartCoursesCheckin","bar",
        coursesCheckin.map(x=>x.label),
        coursesCheckin.map(x=>x.value),
        "#ffc107");

    renderChart("chartCoursesDuration","bar",
        coursesDuration.map(x=>x.label),
        coursesDuration.map(x=>x.value),
        "#fd7e14");

    renderChart("chartSexCheckin","pie",
        sexCheckin.map(x=>x.label),
        sexCheckin.map(x=>x.value),
        ["#0d6efd","#d63384"]);

    renderChart("chartSexDuration","doughnut",
        sexDuration.map(x=>x.label),
        sexDuration.map(x=>x.value),
        ["#20c997","#6610f2"]);
});

/* =============================
   AUTO LOAD
============================= */
document.getElementById("generateBtn").click();
</script>


</body>
</html>
