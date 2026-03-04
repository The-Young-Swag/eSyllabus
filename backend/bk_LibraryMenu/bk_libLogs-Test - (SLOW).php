<?php


//  Library Logs Backend
//  Handles: user validation, attendance logging, KPI reporting
//  Database: Library_logs (id, id_number, name, college, course, library,
//            checkin_time, checkout_time, sex, classification)


include "../../db/dbconnection.php";
date_default_timezone_set("Asia/Manila");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(["error" => "Invalid request method."]);
}



//  UTILITY


function sendResponse(array $payload): void
{
    echo json_encode($payload);
    exit;
}



//  DATA SOURCE LOADERS


/**
 * Loads the local JSON data source for 'students' or 'employees'.
 * Supports multiple common root key structures.
 * Swap file_get_contents for a cURL API call when ready.
 */
function loadLocalDataSource(string $source): array
{
    $filePath = __DIR__ . "/../../API_requests/{$source}.json";

    if (!file_exists($filePath)) {
        return [];
    }

    $decoded = json_decode(file_get_contents($filePath), true);

    if (!is_array($decoded)) {
        return [];
    }

    // Root is already a flat list
    if (isset($decoded[0])) {
        return $decoded;
    }

    // Try common wrapper keys
    foreach (["data", "employees", "students", "records", "items"] as $key) {
        if (isset($decoded[$key]) && is_array($decoded[$key])) {
            return $decoded[$key];
        }
    }

    return [];
}

// -----------------------------------------------------------------------------
// To switch to a live API, replace loadLocalDataSource() with:
//
// function loadLocalDataSource(string $source): array
// {
//     $endpoints = [
//         "students"  => "https://your-school-api.edu/api/students",
//         "employees" => "https://your-school-api.edu/api/employees",
//     ];
//     $ch = curl_init($endpoints[$source]);
//     curl_setopt_array($ch, [
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_HTTPHEADER     => ["Authorization: Bearer YOUR_API_TOKEN"],
//     ]);
//     $raw = curl_exec($ch);
//     if (curl_errno($ch)) {
//         $err = curl_error($ch);
//         curl_close($ch);
//         sendResponse(["error" => "API error for {$source}: {$err}"]);
//     }
//     curl_close($ch);
//     return json_decode($raw, true)["data"] ?? [];
// }
// -----------------------------------------------------------------------------



//  USER RECORD MAPPERS


function mapStudentToUserRecord(array $student): array
{
    return [
        "id_number"      => $student["id_number"],
        "name"           => $student["name"],
        "sex"            => $student["sex"]        ?? null,
        "college"        => $student["college"]    ?? null,
        "course"         => $student["course"]     ?? null,
        "classification" => "STUDENT",
        "secretKey"      => $student["birthDate"] ?? null,
    ];
}

function mapEmployeeToUserRecord(array $employee): array
{
    return [
        "id_number"      => $employee["employee_number"] ?? $employee["id_number"] ?? "",
        "name"           => $employee["name"]  ?? "",
        "sex"            => $employee["sex"]   ?? null,
        "college"        => "",
        "course"         => "",
        "classification" => "EMPLOYEE",
        "secretKey"      => null,
    ];
}



//  KPI BUILDER


/**
 * Builds the KPI payload for a library section on a given date.
 *
 * Returns:
 *   totalToday      – Total check-ins for the section today
 *   currentlyInside – Users with no checkout_time (still present)
 *   topColleges     – Top 3 colleges by visit count (students only)
 *   topCourses      – Top 3 courses by visit count (students only)
 */
function buildKPIData(PDO $pdo, int $sectionID, string $today): array
{
    // ── Totals ───────────────────────────────────────────────────────────────
    $stmtTotals = $pdo->prepare("
        SELECT
            COUNT(*) AS totalToday,
            SUM(CASE WHEN checkout_time IS NULL THEN 1 ELSE 0 END) AS currentlyInside
        FROM Library_logs
        WHERE library              = ?
          AND CAST(checkin_time AS DATE) = ?
    ");
    $stmtTotals->execute([$sectionID, $today]);
    $totals = $stmtTotals->fetch(PDO::FETCH_ASSOC);

    // ── Top colleges ─────────────────────────────────────────────────────────
    $stmtColleges = $pdo->prepare("
        SELECT TOP 3 college, COUNT(*) AS total
        FROM   Library_logs
        WHERE  library                   = ?
          AND  CONVERT(date, checkin_time) = ?
          AND  college IS NOT NULL
          AND  college                   <> ''
        GROUP  BY college
        ORDER  BY total DESC
    ");
    $stmtColleges->execute([$sectionID, $today]);
    $colleges = array_column($stmtColleges->fetchAll(PDO::FETCH_ASSOC), "college");

    // ── Top courses ──────────────────────────────────────────────────────────
    $stmtCourses = $pdo->prepare("
        SELECT TOP 3 course, COUNT(*) AS total
        FROM   Library_logs
        WHERE  library                   = ?
          AND  CONVERT(date, checkin_time) = ?
          AND  course IS NOT NULL
          AND  course                    <> ''
        GROUP  BY course
        ORDER  BY total DESC
    ");
    $stmtCourses->execute([$sectionID, $today]);
    $courses = array_column($stmtCourses->fetchAll(PDO::FETCH_ASSOC), "course");

    return [
        "totalToday"      => intval($totals["totalToday"]      ?? 0),
        "currentlyInside" => intval($totals["currentlyInside"] ?? 0),
        "topColleges"     => array_pad($colleges, 3, "-"),
        "topCourses"      => array_pad($courses,  3, "-"),
    ];
}



//  ATTENDANCE HELPERS


/**
 * Performs the full check-in sequence:
 *   1. Skips silently if the user is already checked in at this section.
 *   2. Auto-closes any open session in a DIFFERENT section (switch scenario).
 *   3. Inserts a new log entry for this section.
 */
function performCheckin(PDO $pdo, string $idNumber, int $sectionID, string $now, string $today, array $user): void
{
    // Already here — nothing to do
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) FROM Library_logs
        WHERE  id_number     = ?
          AND  library       = ?
          AND  checkout_time IS NULL
          AND  CAST(checkin_time AS DATE) = ?
    ");
    $stmtCheck->execute([$idNumber, $sectionID, $today]);
    if (intval($stmtCheck->fetchColumn()) > 0) {
        return;
    }

    // Auto-checkout from any other section (switch scenario)
    $pdo->prepare("
        UPDATE Library_logs
        SET    checkout_time = ?
        WHERE  id_number     = ?
          AND  checkout_time IS NULL
          AND  CAST(checkin_time AS DATE) = ?
          AND  library <> ?
    ")->execute([$now, $idNumber, $today, $sectionID]);

    // Insert new check-in
    $pdo->prepare("
        INSERT INTO Library_logs
            (id_number, name, classification, college, course, library, checkin_time, sex)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        $idNumber,
        $user["name"],
        $user["classification"],
        $user["college"],
        $user["course"],
        $sectionID,
        $now,
        $user["sex"],
    ]);
}

/**
 * Closes the user's active session in the specified section.
 */
function performCheckout(PDO $pdo, string $idNumber, int $sectionID, string $now, string $today): void
{
    $pdo->prepare("
        UPDATE Library_logs
        SET    checkout_time = ?
        WHERE  id_number     = ?
          AND  library       = ?
          AND  checkout_time IS NULL
          AND  CAST(checkin_time AS DATE) = ?
    ")->execute([$now, $idNumber, $sectionID, $today]);
}



//  HANDLERS


function handleGetLibraries(): void
{
    $userID = intval($_POST["userID"] ?? 0);

    if (!$userID) {
        sendResponse(["error" => "Missing or invalid userID."]);
    }

    // Query ONLY the section assigned to this specific PC login via LibraryAccess.
    // Returning all sections would cause currentLibraryID to be wrong after an
    // admin reassigns the PC, making innocent check-ins show "Switch & Check In".
    $libraries = execsqlSRS("
        SELECT ls.SectionID, ls.SectionName
        FROM   LibraryAccess  la
        JOIN   LibrarySection ls ON ls.SectionID = la.SectionID
        WHERE  la.UserID   = ?
          AND  ls.IsActive = 1
    ", "Query", [$userID]);

    sendResponse(["success" => true, "data" => $libraries]);
}

function handleValidateUser(): void
{
    $idNumber = trim($_POST["idNumber"] ?? "");

    if (!$idNumber) {
        sendResponse(["error" => "Identification number is required."]);
    }

    $students  = loadLocalDataSource("students");
    $employees = loadLocalDataSource("employees");

    // ── Student lookup ──────────────────────────────────────────────────────
    $matchedStudents = array_values(
        array_filter($students, fn($s) => $s["id_number"] === $idNumber)
    );

    if (count($matchedStudents) === 1) {
        sendResponse(["success" => true, "data" => mapStudentToUserRecord($matchedStudents[0])]);
    }

    if (count($matchedStudents) > 1) {
        sendResponse([
            "duplicate" => true,
            "matches"   => array_map(fn($s) => mapStudentToUserRecord($s), $matchedStudents),
        ]);
    }

    // ── Employee lookup ─────────────────────────────────────────────────────
    $matchedEmployees = array_values(
        array_filter($employees, fn($e) => ($e["employee_number"] ?? "") === $idNumber)
    );

    if (count($matchedEmployees) === 1) {
        sendResponse(["success" => true, "data" => mapEmployeeToUserRecord($matchedEmployees[0])]);
    }

    sendResponse(["error" => "User not found."]);
}

function handleCheckStatusToday(): void
{
    $idNumber = trim($_POST["idNumber"] ?? "");

    if (!$idNumber) {
        sendResponse(["error" => "Identification number is required."]);
    }

    $today = date("Y-m-d");
    $pdo   = dbconES();

    // Only look for an open (not yet checked out) session created TODAY.
    // Past days and checked-out sessions are intentionally excluded so a user
    // who forgot to check out yesterday is never treated as "still inside".
    $stmt = $pdo->prepare("
        SELECT TOP 1 ll.library, ls.SectionName
        FROM   Library_logs  ll
        LEFT   JOIN LibrarySection ls ON ls.SectionID = ll.library
        WHERE  ll.id_number                 = ?
          AND  ll.checkout_time             IS NULL
          AND  CONVERT(date, ll.checkin_time) = ?
        ORDER  BY ll.checkin_time DESC
    ");
    $stmt->execute([$idNumber, $today]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        sendResponse([
            "checkedIn"   => true,
            "sectionID"   => intval($row["library"]),
            "sectionName" => $row["SectionName"] ?? "another library",
        ]);
    }

    sendResponse(["checkedIn" => false]);
}

function handleSaveAttendance(): void
{
    $idNumber       = trim($_POST["idNumber"]       ?? "");
    $sectionID      = intval($_POST["sectionID"]    ?? 0);
    $action         = trim($_POST["action"]         ?? "");
    $classification = trim($_POST["classification"] ?? "STUDENT");
    $name           = trim($_POST["name"]           ?? "");
    $college        = trim($_POST["college"]        ?? "");
    $course         = trim($_POST["course"]         ?? "");
    $sex            = trim($_POST["sex"]            ?? "");

    if (!$idNumber || !$sectionID || !$action) {
        sendResponse(["error" => "Missing required attendance data."]);
    }

    if (!in_array($action, ["checkin", "checkout"])) {
        sendResponse(["error" => "Invalid attendance action: '{$action}'."]);
    }

    $now   = date("Y-m-d H:i:s");
    $today = date("Y-m-d");
    $pdo   = dbconES();

    $user = compact("name", "classification", "college", "course", "sex");

    try {
        // Keep the write transaction as narrow as possible to prevent deadlocks.
        // buildKPIData is read-only and runs AFTER commit, not inside the transaction.
        $pdo->beginTransaction();
        if ($action === "checkin") {
            performCheckin($pdo, $idNumber, $sectionID, $now, $today, $user);
        } else {
            performCheckout($pdo, $idNumber, $sectionID, $now, $today);
        }
        $pdo->commit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendResponse(["error" => "Database error: " . $e->getMessage()]);
    }

    // Read KPI after the transaction is fully committed
    $kpiData = buildKPIData($pdo, $sectionID, $today);

    sendResponse(["success" => true, "action" => $action, "kpi" => $kpiData]);
}

function handleGetKPI(): void
{
    $sectionID = intval($_POST["sectionID"] ?? 0);

    if (!$sectionID) {
        sendResponse(["error" => "Missing or invalid sectionID."]);
    }

    $pdo     = dbconES();
    $kpiData = buildKPIData($pdo, $sectionID, date("Y-m-d"));

    sendResponse(["success" => true, "data" => $kpiData]);
}



//  DISPATCH


$request = trim($_POST["request"] ?? "");

$handlers = [
    "getLibraries"     => "handleGetLibraries",
    "validateUser"     => "handleValidateUser",
    "checkStatusToday" => "handleCheckStatusToday",
    "saveAttendance"   => "handleSaveAttendance",
    "getKPI"           => "handleGetKPI",
];

if (isset($handlers[$request])) {
    $handlers[$request]();
} else {
    sendResponse(["error" => "Unknown request: '{$request}'."]);
}