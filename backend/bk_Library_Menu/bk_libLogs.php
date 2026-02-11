<?php
include __DIR__ . "/../../db/dbconnection.php";

header('Content-Type: application/json');

function sendJson($data) {
    echo json_encode($data);
    exit;
}

function sendError($message, $status = 400) {
    http_response_code($status);
    sendJson(['success' => false, 'message' => $message]);
}

$request = $_POST['request'] ?? '';

try {

    switch ($request) {

        // ==========================
        // Get Active Libraries
        // ==========================
        case "getLibraries":
            $sql = "
                SELECT SectionID, SectionCode, SectionName
                FROM LibrarySection
                WHERE IsActive = 1
                ORDER BY SectionName
            ";
            $libraries = execsqlSRS($sql, "Search", []);
            sendJson($libraries);
            break;

        // ==========================
        // Get Today's Logs
        // ==========================
        case "getNewLogs":
            $after = $_POST['after'] ?? null;
            $params = [];
            $sql = "
                SELECT student_number, name, college, course, library, checkin_time, checkout_time
                FROM Library_logs
                WHERE CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
            ";
            if ($after) {
                $sql .= " AND checkin_time > ?";
                $params[] = $after;
            }
            $logs = execsqlSRS($sql, "Search", $params);
            sendJson($logs);
            break;

        // ==========================
        // Add New Log
        // ==========================
        case "addLog":
            $student_number = $_POST['student_number'] ?? '';
            $library        = $_POST['library'] ?? '';

            if (!$student_number || !$library) {
                sendError("Missing student number or library");
            }

            $studentsJsonPath = __DIR__ . '/../../API_requests/students.json';
            if (!file_exists($studentsJsonPath)) sendError("Student data file not found");

            $students = json_decode(file_get_contents($studentsJsonPath), true);
            if (!isset($students[$student_number])) sendError("Student not found");

            $s = $students[$student_number];

            $sql = "
                INSERT INTO Library_logs (student_number, name, college, course, library, checkin_time)
                VALUES (?, ?, ?, ?, ?, GETDATE())
            ";
            execsqlSRS($sql, "Insert", [$student_number, $s['name'], $s['college'], $s['course'], $library]);

            sendJson(['success' => true]);
            break;

        // ==========================
        // Checkout Log
        // ==========================
        case "checkoutLog":
            $student_number = $_POST['student_number'] ?? '';
            $library        = $_POST['library'] ?? '';
            if (!$student_number || !$library) sendError("Missing student number or library");

            $sql = "
                SELECT TOP 1 id
                FROM Library_logs
                WHERE student_number = ? AND library = ? AND CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
                  AND checkout_time IS NULL
                ORDER BY checkin_time DESC
            ";
            $logs = execsqlSRS($sql, "Search", [$student_number, $library]);
            if (empty($logs)) sendError("No active check-in found");

            $logId = $logs[0]['id'];
            execsqlSRS("UPDATE Library_logs SET checkout_time = GETDATE() WHERE id = ?", "Update", [$logId]);

            sendJson(['success' => true]);
            break;

        default:
            sendError("Invalid request", 404);
    }

} catch (Exception $e) {
    sendError("Server error: " . $e->getMessage(), 500);
}
