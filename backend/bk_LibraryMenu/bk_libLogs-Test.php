<?php
include "../../db/dbconnection.php";

date_default_timezone_set("Asia/Manila");
header("Content-Type: application/json");

function send($data){
    echo json_encode($data);
    exit;
}

if($_SERVER["REQUEST_METHOD"] !== "POST"){
    send(["error" => "Invalid request method"]);
}

$request = $_POST["request"] ?? '';

try {

switch($request){

    /* ==========================================================
       1️⃣ GET ACTIVE LIBRARIES
    ========================================================== */
    case "getLibraries":
        $sql = "
            SELECT SectionID, SectionName
            FROM LibrarySection
            WHERE IsActive = 1
            ORDER BY SectionID ASC
        ";
        $libraries = execsqlSRS($sql, "Query", []);
        send(["success"=>true, "data"=>$libraries]);
    break;

    /* ==========================================================
       2️⃣ VALIDATE USER (FROM JSON API)
    ========================================================== */
    case "validateUser":
        $studentNumber = trim($_POST["studentNumber"] ?? '');
        if($studentNumber === ''){
            send(["error"=>"Identification number required"]);
        }

        $jsonPath = "../../API_requests/students.json";
        if(!file_exists($jsonPath)){
            send(["error"=>"Student API not found"]);
        }

        $students = json_decode(file_get_contents($jsonPath), true);
        $matches = [];

        foreach($students as $student){
            if($student["student_number"] === $studentNumber){
                $matches[] = $student;
            }
        }

        if(count($matches) === 0){
            send(["error"=>"ID not found"]);
        }

        // SINGLE MATCH
        if(count($matches) === 1){
            send(["success"=>true, "data"=>$matches[0]]);
        }

        // DUPLICATE MATCH
        send(["duplicate"=>true, "matches"=>$matches]);
    break;

    /* ==========================================================
       3️⃣ CHECK STATUS TODAY (FOR AUTO CHECKOUT)
    ========================================================== */
    case "checkStatusToday":
        $studentNumber = trim($_POST["studentNumber"]);
        $today = date("Y-m-d");

        $sql = "
            SELECT id, library
            FROM Library_logs
            WHERE student_number = ?
              AND CAST(checkin_time AS DATE) = ?
              AND checkout_time IS NULL
        ";

        $res = execsqlSRS($sql, "Query", [$studentNumber, $today]);

        if(!empty($res)){
            send([
                "checkedIn" => true,
                "sectionID" => intval($res[0]["library"]),
                "logID" => intval($res[0]["id"])
            ]);
        } else {
            send(["checkedIn" => false]);
        }
    break;

    /* ==========================================================
       4️⃣ FORCE CHECKOUT
    ========================================================== */
    case "forceCheckout":
        $studentNumber = trim($_POST["studentNumber"]);
        $sectionID     = intval($_POST["sectionID"]);
        $now = date("Y-m-d H:i:s");

        $pdo = dbconES();
        $updateSql = "
            UPDATE Library_logs
            SET checkout_time = ?
            WHERE student_number = ?
              AND library = ?
              AND checkout_time IS NULL
        ";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([$now, $studentNumber, $sectionID]);

        send(["success"=>true]);
    break;

    /* ==========================================================
       5️⃣ SAVE ATTENDANCE (AUTO CHECKIN / CHECKOUT)
    ========================================================== */
    case "saveAttendance":
        $studentNumber = trim($_POST["studentNumber"] ?? '');
        $sectionID     = intval($_POST["sectionID"] ?? 0);
        $action        = trim($_POST["action"] ?? '');

        if(!$studentNumber || !$sectionID || !$action){
            send(["error" => "Missing data"]);
        }

        $now   = date("Y-m-d H:i:s");
        $today = date("Y-m-d");
        
        try {
            $pdo = dbconES();
            $pdo->beginTransaction();

            if($action === "checkin") {
                /* Get student info from JSON */
                $jsonPath = "../../API_requests/students.json";
                if(!file_exists($jsonPath)){
                    $pdo->rollBack();
                    send(["error" => "Student API not found"]);
                }

                $students = json_decode(file_get_contents($jsonPath), true);
                $studentData = null;
                
                foreach($students as $s){
                    if($s["student_number"] === $studentNumber){
                        $studentData = $s;
                        break;
                    }
                }

                if(!$studentData){
                    $pdo->rollBack();
                    send(["error" => "Student not found in database"]);
                }

                // Insert new checkin
                $insertSql = "
                    INSERT INTO Library_logs
                    (student_number, name, college, course, library, checkin_time, sex)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = $pdo->prepare($insertSql);
                $result = $stmt->execute([
                    $studentData["student_number"],
                    $studentData["name"],
                    $studentData["college"] ?? '',
                    $studentData["course"] ?? '',
                    $sectionID,
                    $now,
                    $studentData["sex"] ?? ''
                ]);

                if(!$result) {
                    $pdo->rollBack();
                    send(["error" => "Failed to insert record"]);
                }
            } 
            else if($action === "checkout") {
                // Update checkout time
                $updateSql = "
                    UPDATE Library_logs
                    SET checkout_time = ?
                    WHERE student_number = ?
                      AND library = ?
                      AND checkout_time IS NULL
                      AND CAST(checkin_time AS DATE) = ?
                ";
                $stmt = $pdo->prepare($updateSql);
                $stmt->execute([$now, $studentNumber, $sectionID, $today]);
            }

            /* Get Updated KPI for this library */
            $kpi = getLibraryKPI($pdo, $sectionID, $today);

            $pdo->commit();

            send([
                "success" => true,
                "action" => $action,
                "kpi" => $kpi
            ]);

        } catch(Exception $e) {
            if(isset($pdo)) {
                $pdo->rollBack();
            }
            send(["error" => "Database error: " . $e->getMessage()]);
        }
    break;

    /* ==========================================================
       6️⃣ KPI DATA (LIVE PER LIBRARY)
    ========================================================== */
    case "getKPI":
        $sectionID = intval($_POST["sectionID"]);
        $today = date("Y-m-d");
        $pdo = dbconES();
        
        $kpi = getLibraryKPI($pdo, $sectionID, $today);
        send(["success"=>true, "data"=>$kpi]);
    break;

    default:
        send(["error"=>"Invalid request"]);
}

}catch(Exception $e){
    send(["error"=>$e->getMessage()]);
}

/**
 * Helper function to get KPI data for a library
 */
function getLibraryKPI($pdo, $sectionID, $today) {
    try {
        // Total check-ins and currently inside
        $sql = "
            SELECT
                COUNT(*) AS totalToday,
                SUM(CASE WHEN checkout_time IS NULL THEN 1 ELSE 0 END) AS currentlyInside
            FROM Library_logs
            WHERE library = ?
              AND CAST(checkin_time AS DATE) = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sectionID, $today]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $result = [
            "totalToday" => (int)($data["totalToday"] ?? 0),
            "currentlyInside" => (int)($data["currentlyInside"] ?? 0)
        ];

        // Top 3 Colleges
        $sqlColleges = "
            SELECT TOP 3 college, COUNT(*) AS cnt
            FROM Library_logs
            WHERE library = ? 
              AND CAST(checkin_time AS DATE) = ?
              AND college IS NOT NULL 
              AND college != ''
            GROUP BY college
            ORDER BY cnt DESC
        ";
        $stmt = $pdo->prepare($sqlColleges);
        $stmt->execute([$sectionID, $today]);
        $colleges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $topColleges = array_column($colleges, 'college');

        // Top 3 Courses
        $sqlCourses = "
            SELECT TOP 3 course, COUNT(*) AS cnt
            FROM Library_logs
            WHERE library = ? 
              AND CAST(checkin_time AS DATE) = ?
              AND course IS NOT NULL 
              AND course != ''
            GROUP BY course
            ORDER BY cnt DESC
        ";
        $stmt = $pdo->prepare($sqlCourses);
        $stmt->execute([$sectionID, $today]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $topCourses = array_column($courses, 'course');

        // Fill missing values with "-"
        $result["topColleges"] = array_pad($topColleges, 3, "-");
        $result["topCourses"] = array_pad($topCourses, 3, "-");

        return $result;

    } catch (Exception $e) {
        error_log("Error in getLibraryKPI: " . $e->getMessage());
        return [
            "totalToday" => 0,
            "currentlyInside" => 0,
            "topColleges" => ["-", "-", "-"],
            "topCourses" => ["-", "-", "-"]
        ];
    }
}

?>