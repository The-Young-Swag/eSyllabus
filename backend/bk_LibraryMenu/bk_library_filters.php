<?php
header('Content-Type: application/json');
include 'db_connection.php'; // your DB connection

$colleges = [];
$courses = [];

$res = $db->query("SELECT DISTINCT college FROM Library_logs");
while($row = $res->fetch(PDO::FETCH_ASSOC)) $colleges[] = $row['college'];

$res = $db->query("SELECT DISTINCT course FROM Library_logs");
while($row = $res->fetch(PDO::FETCH_ASSOC)) $courses[] = $row['course'];

echo json_encode(['colleges' => $colleges, 'courses' => $courses]);
