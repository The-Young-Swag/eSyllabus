<?php
header('Content-Type: application/json');
include 'db_connection.php';

$college = $_POST['college'] ?? 'all';
$course  = $_POST['course'] ?? 'all';
$dateRange = $_POST['dateRange'] ?? 'monthly';

$where = [];
$params = [];

if($college != 'all') { $where[] = "college = ?"; $params[] = $college; }
if($course != 'all') { $where[] = "course = ?"; $params[] = $course; }

$filterSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

// Table data
$sql = "SELECT FORMAT(checkin_time,'MMMM') AS month, college, course, COUNT(DISTINCT student_number) AS student_count
        FROM Library_logs
        $filterSql
        GROUP BY FORMAT(checkin_time,'MMMM'), college, course
        ORDER BY MIN(checkin_time)";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$tableData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Chart data (monthly total)
$sql2 = "SELECT FORMAT(checkin_time,'MMMM') AS month, COUNT(DISTINCT student_number) AS count
         FROM Library_logs
         $filterSql
         GROUP BY FORMAT(checkin_time,'MMMM')
         ORDER BY MIN(checkin_time)";
$stmt2 = $db->prepare($sql2);
$stmt2->execute($params);
$chartRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$chartData = [
    'labels' => array_column($chartRows, 'month'),
    'values' => array_column($chartRows, 'count')
];

echo json_encode([
    'table' => $tableData,
    'chart' => $chartData
]);
