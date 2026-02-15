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

    $studentNumber = trim($_POST["studentNumber"]);
    $sectionID     = intval($_POST["sectionID"]);

    if(!$studentNumber || !$sectionID){
        send(["error"=>"Missing data"]);
    }

    $now   = date("Y-m-d H:i:s");
    $today = date("Y-m-d");
    $pdo = dbconES();
    $pdo->beginTransaction();

    /* Check existing active log */
    $checkSql = "
        SELECT id, library
        FROM Library_logs
        WHERE student_number = ?
          AND CAST(checkin_time AS DATE) = ?
          AND checkout_time IS NULL
    ";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute([$studentNumber, $today]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    /* Checkout if already inside */
    if($existing && $existing["library"] == $sectionID){

        $updateSql = "
            UPDATE Library_logs
            SET checkout_time = ?
            WHERE id = ?
        ";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([$now, $existing["id"]]);

        $action = "checkout";

    } else {

        /* Auto checkout previous library */
        if($existing){
            $updateSql = "
                UPDATE Library_logs
                SET checkout_time = ?
                WHERE id = ?
            ";
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute([$now, $existing["id"]]);
        }

        /* Get student info */
        $students = json_decode(
            file_get_contents("../../API_requests/students.json"),
            true
        );

        $studentData = null;
        foreach($students as $s){
            if($s["student_number"] === $studentNumber){
                $studentData = $s;
                break;
            }
        }

        if(!$studentData){
            $pdo->rollBack();
            send(["error"=>"Student not found"]);
        }

        $insertSql = "
            INSERT INTO Library_logs
            (student_number, name, college, course, library, checkin_time, sex)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $pdo->prepare($insertSql);
        $stmt->execute([
            $studentData["student_number"],
            $studentData["name"],
            $studentData["college"],
            $studentData["course"],
            $sectionID,
            $now,
            $studentData["sex"]
        ]);

        $action = "checkin";
    }

    /* Get Updated KPI */
    $sqlKPI = "
        SELECT
            COUNT(*) AS totalToday,
            SUM(CASE WHEN checkout_time IS NULL THEN 1 ELSE 0 END) AS currentlyInside
        FROM Library_logs
        WHERE library = ?
          AND CAST(checkin_time AS DATE) = ?
    ";
    $kpiData = execsqlSRS($sqlKPI, "Query", [$sectionID, $today]);
    $kpi = $kpiData[0] ?? ["totalToday"=>0,"currentlyInside"=>0];

    $pdo->commit();

    send([
        "success"=>true,
        "action"=>$action,
        "kpi"=>$kpi
    ]);

break;


    /* ==========================================================
       6️⃣ KPI DATA (LIVE PER LIBRARY)
    ========================================================== */
case "getKPI":
    $sectionID = intval($_POST["sectionID"]);
    $today = date("Y-m-d");
    $pdo = dbconES();

    // Total check-ins and currently inside
    $sql = "
        SELECT
            COUNT(*) AS totalToday,
            SUM(CASE WHEN checkout_time IS NULL THEN 1 ELSE 0 END) AS currentlyInside
        FROM Library_logs
        WHERE library = ?
          AND CAST(checkin_time AS DATE) = ?
    ";
    $data = execsqlSRS($sql, "Query", [$sectionID, $today]);
    $result = $data[0] ?? ["totalToday"=>0, "currentlyInside"=>0];

    // Top 3 Colleges
    $sqlColleges = "
        SELECT TOP 3 college, COUNT(*) AS cnt
        FROM Library_logs
        WHERE library = ? AND CAST(checkin_time AS DATE) = ?
        GROUP BY college
        ORDER BY cnt DESC
    ";
    $topColleges = array_map(fn($c)=>$c["college"], execsqlSRS($sqlColleges,"Query",[$sectionID,$today]));

    // Top 3 Courses
    $sqlCourses = "
        SELECT TOP 3 course, COUNT(*) AS cnt
        FROM Library_logs
        WHERE library = ? AND CAST(checkin_time AS DATE) = ?
        GROUP BY course
        ORDER BY cnt DESC
    ";
    $topCourses = array_map(fn($c)=>$c["course"], execsqlSRS($sqlCourses,"Query",[$sectionID,$today]));

    $result["topColleges"] = array_pad($topColleges,3,"-");
    $result["topCourses"] = array_pad($topCourses,3,"-");

    send(["success"=>true,"data"=>$result]);
break;


    default:
        send(["error"=>"Invalid request"]);
}

}catch(Exception $e){
    send(["error"=>$e->getMessage()]);
}
?>
