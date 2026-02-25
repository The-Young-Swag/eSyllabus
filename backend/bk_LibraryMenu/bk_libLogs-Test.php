<?php

// =============================================================================
//  Library Logs Backend
//  Handles: user validation, attendance logging, KPI reporting
//  Database: Library_logs (id, id_number, name, college, course, library,
//            checkin_time, checkout_time, sex, classification)
// =============================================================================

include "../../db/dbconnection.php";
date_default_timezone_set("Asia/Manila");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(["error" => "Invalid request method."]);
}


// =============================================================================
//  UTILITY
// =============================================================================

/**
 * Encodes $payload as JSON, outputs it, and halts execution.
 */
function sendResponse(array $payload)
{
    echo json_encode($payload);
    exit;
}


// =============================================================================
//  DATA SOURCE LOADERS  (swap these out for live API calls when ready)
// =============================================================================

/**
 * Loads the local JSON data source for 'students' or 'employees'.
 * Replace the file_get_contents call here with a real API call when ready.
 */
function loadLocalDataSource(string $source): array
{
    $filePath     = __DIR__ . "/../../API_requests/{$source}.json";
    $jsonContents = file_get_contents($filePath);
    $decoded      = json_decode($jsonContents, true);

    return $decoded["data"] ?? [];
}


/**
 * Fetches user data from the live API for 'students' or 'employees'.
 * Uncomment this and comment out loadLocalDataSource() above when API is ready.
 */
// function loadLocalDataSource(string $source): array
// {
//     $apiEndpoints = [
//         "students"  => "https://your-school-api.edu/api/students",
//         "employees" => "https://your-school-api.edu/api/employees",
//     ];
//
//     $apiToken = "YOUR_API_TOKEN_HERE";
//
//     $curlHandle = curl_init($apiEndpoints[$source]);
//
//     curl_setopt_array($curlHandle, [
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_HTTPHEADER     => [
//             "Content-Type: application/json",
//             "Authorization: Bearer {$apiToken}",
//         ],
//     ]);
//
//     $rawResponse = curl_exec($curlHandle);
//
//     if (curl_errno($curlHandle)) {
//         $curlError = curl_error($curlHandle);
//         curl_close($curlHandle);
//         sendResponse(["error" => "API connection failed for {$source}: {$curlError}"]);
//     }
//
//     curl_close($curlHandle);
//
//     $decoded = json_decode($rawResponse, true);
//
//     return $decoded["data"] ?? [];
// }

// =============================================================================
//  USER RECORD MAPPERS
// =============================================================================

/**
 * Maps a raw student JSON record to the standard user record shape.
 * Students carry college, course, and a secret key for duplicate resolution.
 */
function mapStudentToUserRecord(array $student): array
{
    return [
        "id_number"      => $student["id_number"],
        "name"           => $student["name"],
        "sex"            => $student["sex"]        ?? null,
        "college"        => $student["college"]    ?? null,
        "course"         => $student["course"]     ?? null,
        "classification" => "STUDENT",
        "secretKey"      => $student["secret_key"] ?? null,
    ];
}

/**
 * Maps a raw employee JSON record to the standard user record shape.
 * Employees do not carry college or course — those fields are stored as null.
 */
function mapEmployeeToUserRecord(array $employee): array
{
    return [
        "id_number"      => $employee["employee_number"],
        "name"           => $employee["name"],
        "sex"            => $employee["sex"] ?? null,
        "college"        => null,
        "course"         => null,
        "classification" => "EMPLOYEE",
        "secretKey"      => null,
    ];
}


// =============================================================================
//  KPI BUILDER
// =============================================================================

/**
 * Builds the KPI payload for a library section on a given date.
 *
 * Returns:
 *   totalToday      – Total check-ins for the section today
 *   currentlyInside – Users with no checkout_time (still present)
 *   topColleges     – Top 3 colleges by visit count
 *   topCourses      – Top 3 courses by visit count
 */
function buildKPIData($pdo, int $sectionID, string $today): array
{
    $kpi = [
        "totalToday"      => 0,
        "currentlyInside" => 0,
        "topColleges"     => ["-", "-", "-"],
        "topCourses"      => ["-", "-", "-"],
    ];

    // ── Totals ───────────────────────────────────────────────────────────────
    $stmtTotals = $pdo->prepare("
        SELECT
            COUNT(*) AS totalToday,
            SUM(CASE WHEN checkout_time IS NULL THEN 1 ELSE 0 END) AS currentlyInside
        FROM Library_logs
        WHERE library = ?
          AND CAST(checkin_time AS DATE) = ?
    ");
    $stmtTotals->execute([$sectionID, $today]);
    $totals = $stmtTotals->fetch(PDO::FETCH_ASSOC);

    $kpi["totalToday"]      = intval($totals["totalToday"]      ?? 0);
    $kpi["currentlyInside"] = intval($totals["currentlyInside"] ?? 0);

    // ── Top colleges ─────────────────────────────────────────────────────────
    $stmtColleges = $pdo->prepare("
        SELECT TOP 3 college, COUNT(*) AS total
        FROM   Library_logs
        WHERE  library = ?
          AND  CONVERT(date, checkin_time) = ?
          AND  college IS NOT NULL
          AND  college <> ''
        GROUP  BY college
        ORDER  BY total DESC
    ");
    $stmtColleges->execute([$sectionID, $today]);
    $collegeNames     = array_column($stmtColleges->fetchAll(PDO::FETCH_ASSOC), "college");
    $kpi["topColleges"] = array_pad($collegeNames, 3, "-");

    // ── Top courses ──────────────────────────────────────────────────────────
    $stmtCourses = $pdo->prepare("
        SELECT TOP 3 course, COUNT(*) AS total
        FROM   Library_logs
        WHERE  library = ?
          AND  CONVERT(date, checkin_time) = ?
          AND  course IS NOT NULL
          AND  course <> ''
        GROUP  BY course
        ORDER  BY total DESC
    ");
    $stmtCourses->execute([$sectionID, $today]);
    $courseNames     = array_column($stmtCourses->fetchAll(PDO::FETCH_ASSOC), "course");
    $kpi["topCourses"] = array_pad($courseNames, 3, "-");

    return $kpi;
}


// =============================================================================
//  ATTENDANCE HELPERS
// =============================================================================

/**
 * Performs the full check-in sequence:
 *   1. Skips silently if the user is already checked in at this section.
 *   2. Auto-closes any open session in a DIFFERENT section (switch scenario).
 *   3. Inserts a new log entry for this section.
 */
function performCheckin($pdo, string $idNumber, int $sectionID, string $now, string $today, array $userDetails)
{
    // Skip if already checked in here
    $stmtAlreadyHere = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM   Library_logs
        WHERE  id_number     = ?
          AND  library       = ?
          AND  checkout_time IS NULL
          AND  CAST(checkin_time AS DATE) = ?
    ");
    $stmtAlreadyHere->execute([$idNumber, $sectionID, $today]);
    $alreadyCheckedIn = intval($stmtAlreadyHere->fetchColumn());

    if ($alreadyCheckedIn) {
        return;
    }

    // Close any open sessions in other sections
    $stmtCloseOtherSections = $pdo->prepare("
        UPDATE Library_logs
        SET    checkout_time = ?
        WHERE  id_number     = ?
          AND  checkout_time IS NULL
          AND  CAST(checkin_time AS DATE) = ?
          AND  library <> ?
    ");
    $stmtCloseOtherSections->execute([$now, $idNumber, $today, $sectionID]);

    // Insert new check-in record
    $stmtInsert = $pdo->prepare("
        INSERT INTO Library_logs
            (id_number, name, classification, college, course, library, checkin_time, sex)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([
        $idNumber,
        $userDetails["name"],
        $userDetails["classification"],
        $userDetails["college"],
        $userDetails["course"],
        $sectionID,
        $now,
        $userDetails["sex"],
    ]);
}

/**
 * Closes the user's active session in the specified section.
 */
function performCheckout($pdo, string $idNumber, int $sectionID, string $now, string $today)
{
    $stmtCheckout = $pdo->prepare("
        UPDATE Library_logs
        SET    checkout_time = ?
        WHERE  id_number     = ?
          AND  library       = ?
          AND  checkout_time IS NULL
          AND  CAST(checkin_time AS DATE) = ?
    ");
    $stmtCheckout->execute([$now, $idNumber, $sectionID, $today]);
}


// =============================================================================
//  HANDLERS
// =============================================================================

/**
 * Returns all active library sections.
 */
function handleGetLibraries()
{
    $userID = intval($_POST["userID"] ?? 0);

    if (!$userID) {
        sendResponse(["error" => "Missing or invalid userID."]);
    }

    $libraries = execsqlSRS("
        SELECT SectionID, SectionName
        FROM   LibrarySection
        WHERE  IsActive = 1
        ORDER  BY SectionID ASC
    ", "Query", []);

    sendResponse(["success" => true, "data" => $libraries]);
}

/**
 * Looks up a user by ID number from the local JSON data source.
 * Checks students first, then employees.
 * Returns a single match, a duplicate flag, or an error if not found.
 */
function handleValidateUser()
{
    $idNumber = trim($_POST["idNumber"] ?? "");

    if (!$idNumber) {
        sendResponse(["error" => "Identification number is required."]);
    }

    $students  = loadLocalDataSource("students");
    $employees = loadLocalDataSource("employees");

    // ── Student lookup ──────────────────────────────────────────────────────
    $matchedStudents = array_values(
        array_filter($students, fn($student) => $student["id_number"] === $idNumber)
    );

    if (count($matchedStudents) === 1) {
        sendResponse([
            "success" => true,
            "data"    => mapStudentToUserRecord($matchedStudents[0])
        ]);
    }

    if (count($matchedStudents) > 1) {
        sendResponse([
            "duplicate" => true,
            "matches"   => array_map(fn($student) => mapStudentToUserRecord($student), $matchedStudents)
        ]);
    }

    // ── Employee lookup ─────────────────────────────────────────────────────
    $matchedEmployees = array_values(
        array_filter($employees, fn($employee) => $employee["employee_number"] === $idNumber)
    );

    if (count($matchedEmployees) === 1) {
        sendResponse([
            "success" => true,
            "data"    => mapEmployeeToUserRecord($matchedEmployees[0])
        ]);
    }

    sendResponse(["error" => "User not found."]);
}

/**
 * Checks whether a user is currently checked in today,
 * and returns which library section they are in.
 */
function handleCheckStatusToday()
{
    $idNumber = trim($_POST["idNumber"] ?? "");
    $today    = date("Y-m-d");

    if (!$idNumber) {
        sendResponse(["error" => "Identification number is required."]);
    }

    $pdo  = dbconES();
    $stmt = $pdo->prepare("
        SELECT TOP 1 library
        FROM   Library_logs
        WHERE  id_number     = ?
          AND  checkout_time IS NULL
          AND  CONVERT(date, checkin_time) = ?
        ORDER  BY checkin_time DESC
    ");
    $stmt->execute([$idNumber, $today]);
    $activeLog = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($activeLog) {
        sendResponse([
            "checkedIn" => true,
            "sectionID" => intval($activeLog["library"])
        ]);
    }

    sendResponse(["checkedIn" => false]);
}

/**
 * Records a check-in or check-out event in Library_logs.
 *
 * Check-in:  auto-closes any open session in a different section first,
 *            then inserts a new log row (skips if already checked in here).
 * Check-out: closes the open session in the given section.
 */
function handleSaveAttendance()
{
    $idNumber       = trim($_POST["idNumber"]       ?? "");
    $sectionID      = intval($_POST["sectionID"]    ?? 0);
    $action         = trim($_POST["action"]         ?? "");
    $classification = trim($_POST["classification"] ?? "STUDENT");
    $name           = trim($_POST["name"]           ?? "");
    $college        = trim($_POST["college"]        ?? "") ?: null;
    $course         = trim($_POST["course"]         ?? "") ?: null;
    $sex            = trim($_POST["sex"]            ?? "");

    if (!$idNumber || !$sectionID || !$action) {
        sendResponse(["error" => "Missing required attendance data."]);
    }

    $now   = date("Y-m-d H:i:s");
    $today = date("Y-m-d");
    $pdo   = dbconES();

    $userDetails = [
        "name"           => $name,
        "classification" => $classification,
        "college"        => $college,
        "course"         => $course,
        "sex"            => $sex,
    ];

    if ($action === "checkin") {
        $pdo->beginTransaction();
        performCheckin($pdo, $idNumber, $sectionID, $now, $today, $userDetails);
        $kpiData = buildKPIData($pdo, $sectionID, $today);
        $pdo->commit();

    } elseif ($action === "checkout") {
        $pdo->beginTransaction();
        performCheckout($pdo, $idNumber, $sectionID, $now, $today);
        $kpiData = buildKPIData($pdo, $sectionID, $today);
        $pdo->commit();

    } else {
        sendResponse(["error" => "Invalid attendance action: '{$action}'."]);
    }

    sendResponse(["success" => true, "action" => $action, "kpi" => $kpiData]);
}

/**
 * Returns KPI statistics for a given library section and today's date.
 */
function handleGetKPI()
{
    $sectionID = intval($_POST["sectionID"] ?? 0);

    if (!$sectionID) {
        sendResponse(["error" => "Missing or invalid sectionID."]);
    }

    $pdo     = dbconES();
    $today   = date("Y-m-d");
    $kpiData = buildKPIData($pdo, $sectionID, $today);

    sendResponse(["success" => true, "data" => $kpiData]);
}


// =============================================================================
//  DISPATCH
// =============================================================================

$request = trim($_POST["request"] ?? "");

switch ($request) {
    case "getLibraries":     handleGetLibraries();     break;
    case "validateUser":     handleValidateUser();     break;
    case "checkStatusToday": handleCheckStatusToday(); break;
    case "saveAttendance":   handleSaveAttendance();   break;
    case "getKPI":           handleGetKPI();           break;

    default:
        sendResponse(["error" => "Unknown request: '{$request}'."]);
}