<?php
// Go up TWO levels from: backend/bk_Library_Menu/ to reach eSyllabus/
include __DIR__ . "/../../db/dbconnection.php";

$request = $_POST['request'] ?? '';

switch ($request) {

    // ==========================
    // Return TABLE ROWS (HTML)
    // ==========================
    case "getLogsTable":

        $sql = "
            SELECT student_number, name, college, course, library, checkin_time, checkout_time
            FROM Library_logs
            WHERE CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
            ORDER BY checkin_time DESC
        ";

        $logs = execsqlSRS($sql, "Search", []);

        if (empty($logs)) {
            echo "<tr><td colspan='7' class='text-center text-muted'>No logs for today</td></tr>";
        } else {
            foreach ($logs as $log) {
                echo "<tr>
                    <td>{$log['student_number']}</td>
                    <td>{$log['name']}</td>
                    <td>{$log['college']}</td>
                    <td>{$log['course']}</td>
                    <td>{$log['library']}</td>
                    <td>" . date("Y-m-d H:i:s", strtotime($log['checkin_time'])) . "</td>

						 <td>" . (
						$log['checkout_time']
							? date("Y-m-d H:i:s", strtotime($log['checkout_time']))
							: "<span class='text-muted'>—</span>"
					) . "</td>

                </tr>";
            }
        }

        break;

    // ==========================
    // Return KPIs (JSON)
    // ==========================
case "getKPIs":
    header('Content-Type: application/json');

    $sql = "
        SELECT student_number, college, course
        FROM Library_logs
        WHERE CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
    ";

    $logs = execsqlSRS($sql, "Search", []);

    if (empty($logs)) {
        echo json_encode([
            'students' => [],
            'colleges' => [],
            'courses'  => []
        ]);
    } else {
        $students = array_unique(array_column($logs, 'student_number'));
        $colleges = array_unique(array_column($logs, 'college'));
        $courses  = array_unique(array_column($logs, 'course'));

        echo json_encode([
            'students' => array_values($students),
            'colleges' => array_values($colleges),
            'courses'  => array_values($courses)
        ]);
    }
break;

case "getNewLogs":
    header('Content-Type: application/json');
    $after = $_POST['after'] ?? null;

    $sql = "
        SELECT student_number, name, college, course, library, checkin_time, checkout_time
        FROM Library_logs
        WHERE CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
    ";

    if ($after) {
        // Only fetch logs after the last timestamp
        $sql .= " AND checkin_time > ?";
        $logs = execsqlSRS($sql, "Search", [$after]);
    } else {
        $logs = execsqlSRS($sql, "Search", []);
    }

    echo json_encode($logs);
    break;


    // ==========================
    // Add Log
    // ==========================
    case "addLog":

        header('Content-Type: application/json');

        $student_number = $_POST['student_number'] ?? '';
        $library        = $_POST['library'] ?? '';

        if (!$student_number || !$library) {
            echo json_encode(['success' => false, 'message' => 'Missing fields']);
            exit;
        }

        // Use absolute path for students.json
        $studentsJsonPath = __DIR__ . '/../../API_requests/students.json';
        
        if (!file_exists($studentsJsonPath)) {
            echo json_encode(['success' => false, 'message' => 'Student data file not found. Create API_requests/students.json']);
            exit;
        }
        
        $students = json_decode(
            file_get_contents($studentsJsonPath),
            true
        );

        if (!isset($students[$student_number])) {
			echo json_encode(['success' => false, 'message' => 'Student not found']);
            exit;
        }

        $s = $students[$student_number];

		$sql = "
			INSERT INTO Library_logs
			(student_number, name, college, course, library, checkin_time)
			VALUES (?, ?, ?, ?, ?, GETDATE())
		";


        execsqlSRS($sql, "Insert", [
            $student_number,
            $s['name'],
            $s['college'],
            $s['course'],
            $library
        ]);

        echo json_encode(['success' => true]);
        break;
		
		// ==========================
// Checkout Log
// ==========================
case "checkoutLog":

    header('Content-Type: application/json');

    $student_number = $_POST['student_number'] ?? '';
    $library        = $_POST['library'] ?? '';

    if (!$student_number || !$library) {
        echo json_encode(['success' => false, 'message' => 'Missing fields']);
        exit;
    }

    // Find the latest check-in for today without checkout
    $sql = "
        SELECT TOP 1 id
        FROM Library_logs
        WHERE student_number = ?
          AND library = ?
          AND CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
          AND checkout_time IS NULL
        ORDER BY checkin_time DESC
    ";
    $logs = execsqlSRS($sql, "Search", [$student_number, $library]);

    if (empty($logs)) {
        echo json_encode(['success' => false, 'message' => 'No active check-in found for this student.']);
        exit;
    }

    $logId = $logs[0]['id'];

    // Update checkout time
    $sqlUpdate = "
        UPDATE Library_logs
        SET checkout_time = GETDATE()
        WHERE id = ?
    ";
    execsqlSRS($sqlUpdate, "Update", [$logId]);

    echo json_encode(['success' => true]);
    break;


    default:
        echo "Invalid request";
}