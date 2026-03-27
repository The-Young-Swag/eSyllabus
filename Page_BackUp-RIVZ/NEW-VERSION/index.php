<?php
session_start();
include "db/dbconnection.php";

// ── Pull sync helper ──────────────────────────────────────────────────────────
include "syncAPIToDatabase.php";   // adjust path to wherever you placed the file

// ── Fetch from upstream APIs ──────────────────────────────────────────────────
$student  = curl_init();
$employee = curl_init();

curl_setopt_array($student, [
    CURLOPT_URL            => 'http://tau.edu.ph:8087/ProxyTAUService/studentLibrary',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => 'POST',
    CURLOPT_POSTFIELDS     => json_encode([
        "UserAccount" => "LibrarySys",
        "Password"    => "libraryAPI",
        "deviceUUID"  => "LibSys",
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer accessLibrary',
    ],
]);

curl_setopt_array($employee, [
    CURLOPT_URL            => 'http://tau.edu.ph:8087/ProxyTAUService/employeeLibrary',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => 'POST',
    CURLOPT_POSTFIELDS     => json_encode([
        "UserAccount" => "LibrarySys",
        "Password"    => "libraryAPI",
        "deviceUUID"  => "LibSys",
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer accessLibrary',
    ],
]);

$studentResponse  = curl_exec($student);
$employeeResponse = curl_exec($employee);

$studentOk  = $studentResponse  !== false;
$employeeOk = $employeeResponse !== false;

if (!$studentOk)  error_log("Student API cURL Error: "  . curl_error($student));
if (!$employeeOk) error_log("Employee API cURL Error: " . curl_error($employee));

curl_close($student);
curl_close($employee);

// ── Cache in session (keep whatever was there if the call failed) ─────────────
if ($studentOk)  $_SESSION["studentAPI"]  = $studentResponse;
if ($employeeOk) $_SESSION["employeeAPI"] = $employeeResponse;

// ── Sync fresh API data to DB for offline fallback ────────────────────────────
// Only runs when at least one API actually returned something.
// Uses the freshly-fetched strings, not the session, so stale session data
// from a previous load can never overwrite newer DB rows.
if ($studentOk || $employeeOk) {
    syncAPIToDatabase(
        $studentOk  ? $studentResponse  : "[]",
        $employeeOk ? $employeeResponse : "[]",
        dbconES()   // your existing PDO factory
    );
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Library Attendance Monitoring</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="dist/css/source-sans-pro.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="dist/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- TAU Custom Theme style -->
  <link rel="stylesheet" href="dist/css/taucustom.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
  <script src="js/jquery.min.js"></script>
  <script src="ajax/ajax.js?v=<?= filemtime('ajax/ajax.js'); ?>"></script>
  <script src="dist/js/solid.js"></script>
  <script src="dist/js/brands.js"></script>
  <script src="dist/js/fontawesome.js"></script>
</head>

<body class="hold-transition sidebar-mini layout-fixed" id="container">

</body>

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js">
</script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>

<script>autocall("login","","");</script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
</html>