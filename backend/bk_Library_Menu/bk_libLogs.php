<?php
include __DIR__ . "/../../db/dbconnection.php";

header('Content-Type: application/json');

function sendJson($data)
{
    echo json_encode($data);
    exit;
}

function sendError($message, $status = 400)
{
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
              SELECT id, student_number, name, college, course, library, checkin_time, checkout_time
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
    $specialKey     = $_POST['specialKey'] ?? null;

    if (!$student_number || !$library) {
        sendError("Missing student number or library");
    }

    $studentsJsonPath = __DIR__ . '/../../API_requests/students.json';
    if (!file_exists($studentsJsonPath)) sendError("Student data file not found");

    $students = json_decode(file_get_contents($studentsJsonPath), true);

    // Handle duplicates: check secretKey if multiple entries
    $matchedStudent = null;

    $possibleStudents = array_filter($students, fn($s) => $s['student_number'] ?? '' === $student_number);

    if (count($possibleStudents) > 1) {
        // require special key
        foreach ($possibleStudents as $s) {
            if ($specialKey && $s['secretKey'] === $specialKey) {
                $matchedStudent = $s;
                break;
            }
        }
        if (!$matchedStudent) sendError("Duplicate student number detected. Please enter correct special key.");
    } else {
        $matchedStudent = $students[$student_number] ?? null;
    }

    if (!$matchedStudent) sendError("Student not found");

    $sql = "
        INSERT INTO Library_logs (student_number, name, college, course, library, checkin_time, sex)
        VALUES (?, ?, ?, ?, ?, GETDATE(), ?)
    ";
    execsqlSRS($sql, "Insert", [
        $student_number,
        $matchedStudent['name'],
        $matchedStudent['college'],
        $matchedStudent['course'],
        $library,
        $matchedStudent['sex'] ?? null
    ]);

    sendJson(['success' => true]);
    break;



        // ==========================
        // Checkout Log
        // ==========================
        case "checkoutLog":
            $id = $_POST['id'] ?? null;
            if (!$id) sendError("Missing log ID");

            $sql = "
        UPDATE Library_logs
        SET checkout_time = GETDATE()
        WHERE id = ? AND checkout_time IS NULL
    ";

            execsqlSRS($sql, "Update", [$id]);

            sendJson(['success' => true]);
            break;


        default:
            sendError("Invalid request", 404);
    }
} catch (Exception $e) {
    sendError("Server error: " . $e->getMessage(), 500);
}
