<?php
session_start();

include "../../db/dbconnection.php";
date_default_timezone_set("Asia/Manila");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Invalid request method."]); exit;
}

//null for offline
$student  = $_SESSION["studentAPI"]  ?? null;
$employee = $_SESSION["employeeAPI"] ?? null;
$now = date("Y-m-d H:i:s");

$USER_SOURCE = [
    "students"  => json_decode($student, true),
    "employees" => json_decode($employee, true),
];

const ACTION_CONFIG = [
    "checkin" => [
        "color" => "success", 
        "icon" => "fa-sign-in-alt", 
        "btnText" => "Check In", 
        "message" => "Not checked in yet"
        ],
    "checkout" => [
        "color" => "danger", 
        "icon" => "fa-sign-out-alt", 
        "btnText" => "Check Out", 
        "message" => "Currently in this library"
    ],
    "switch" => [
        "color" => "warning", 
        "icon" => "fa-random", 
        "btnText" => "Switch & Check In", 
        "message" => null],
];

//VALIDATION SECTION
function resolveUserFromDatabase(string $id, string $group): array
{
    $pdo = dbconES();

    if ($group === "employees") {
        $stmt = $pdo->prepare("
            SELECT empNumber AS id_number, name, sex
            FROM   employeeData
            WHERE  empNumber = :id
        ");

        $stmt->execute([":id" => $id]);
        $employeeRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($employee) => [
            "id_number" => $employee["id_number"],
            "name" => $employee["name"],
            "sex" => $employee["sex"] ?? null,
            "college" => "",
            "course" => "",
            "classification" => "EMPLOYEE",
            "secretKey" => null,
            "agency_organization" => "",
        ], $employeeRows);
    }

    // Students
    $stmt = $pdo->prepare("
        SELECT studNumber AS id_number, name, sex,
               college, course, enrollment_status, birthDate
        FROM   studentData
        WHERE  studNumber = :id
    ");

    $stmt->execute([":id" => $id]);
    $studentRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($student) => [
        "id_number" => $student["id_number"],
        "name" => $student["name"],
        "sex" => $student["sex"] ?? null,
        "college" => $student["college"] ?? "",
        "course" => $student["course"] ?? "",
        "classification" => $student["enrollment_status"] ?? "STUDENT",
        "secretKey" => $student["birthDate"] ?? null,
        "agency_organization" => "",
    ], $studentRows);
}
 
 
function resolveUserById(string $id): array
{
    global $USER_SOURCE;

    $inputId = strtoupper(trim($id));

    $employeePrefixes = ["TAU", "JO"];
    $group = "students";

    foreach ($employeePrefixes as $prefix) {
        if (str_starts_with($inputId, $prefix)) {
            $group = "employees";
            break;
        }
    }

    $sourceData = $USER_SOURCE[$group] ?? null;

    // API missing → fallback to DB
    if (!is_array($sourceData) || empty($sourceData)) {
        return resolveUserFromDatabase($id, $group);
    }

    $records = isset($sourceData[0])
        ? $sourceData
        : ($sourceData["data"]
        ?? $sourceData["employees"]
        ?? $sourceData["students"]
        ?? $sourceData["records"]
        ?? $sourceData["items"]
        ?? []);

    $mapFunction = $group === "employees" ? "mapEmployee" : "mapStudent";
    $matchedUsers = [];

    foreach ($records as $record) {
        $mappedUser = $mapFunction($record);

        if (strtoupper(trim($mappedUser["id_number"])) === $inputId) {
            $matchedUsers[] = $mappedUser;
        }
    }

    // Not found in API → fallback to DB
    if (empty($matchedUsers)) {
        return resolveUserFromDatabase($inputId, $group);
    }

    return $matchedUsers;
}
 
// function resolveUserById(string $id): array
// {
//     global $USER_SOURCE;

//     $group = str_starts_with(strtoupper($id), "TAU") ? "employees" : "students";
//     $jsonData = $USER_SOURCE[$group] ?? null;

//     if (!is_array($jsonData)) return [];

//     $records = isset($jsonData[0])
//         ? $jsonData
//         : ($jsonData["data"]
//         ?? $jsonData["employees"]
//         ?? $jsonData["students"]
//         ?? $jsonData["records"]
//         ?? $jsonData["items"]
//         ?? []);

//     $mapper  = $group === "employees" ? "mapEmployee" : "mapStudent";
//     $matches = [];

//     foreach ($records as $record) {
//         $user = $mapper($record);
//         if ($user["id_number"] === $id) $matches[] = $user;
//     }

//     return $matches;
// }

function mapStudent(array $student): array
{
    $status = strtoupper(trim($student["enrollment_status"] ?? ""));
    $isGuest = $status === "NOT ENROLLED";
    $college = $student["college"] ?? "";
    $course = $student["course"]  ?? "";

    return [
        "id_number" => $student["id_number"] ?? "",
        "name" => $student["name"] ?? "",
        "sex" => $student["sex"] ?? null,
        "college" => $college,
        "course" => $course,
        "classification" => $isGuest ? "GUEST" : "STUDENT",
        "secretKey" => $student["birthDate"] ?? null,
        "agency_organization" => $isGuest ? trim("{$college} - {$course}", " -") : "",
    ];
}

function mapEmployee(array $employee): array
{
    return [
        "id_number" => $employee["employee_number"] ?? $employee["id_number"] ?? "",
        "name" => $employee["name"] ?? "",
        "sex" => $employee["sex"]  ?? null,
        "college" => "",
        "course" => "",
        "classification" => "EMPLOYEE",
        "secretKey" => null,
    ];
}

//  ATTENDANCE LOGIC 

function performCheckin(PDO $pdo, string $idNumber, int $libraryID, string $now, array $user)
{
    $start = date("Y-m-d 00:00:00");
    $end = date("Y-m-d 00:00:00", strtotime("+1 day"));

    $params = [
        ":idNumber" => $idNumber,
        ":name" => $user["name"],
        ":sectionID" => $libraryID,
        ":start" => $start,
        ":end" => $end,
    ];

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM   LibraryLogs
        WHERE  id_number = :idNumber
          AND  name = :name
          AND  library = :sectionID
          AND  checkout_time IS NULL
          AND  checkin_time >= :start
          AND  checkin_time  < :end
    ");
    $stmt->execute($params);
    if ((int) $stmt->fetchColumn() > 0) return;

    $stmt = $pdo->prepare("
        UPDATE LibraryLogs
        SET    checkout_time = :now
        WHERE  id_number = :idNumber
          AND  name = :name
          AND  checkout_time IS NULL
          AND  checkin_time >= :start
          AND  checkin_time < :end
          AND  library <> :sectionID
    ");
    $stmt->execute($params + [":now" => $now]);

    $stmt = $pdo->prepare("
        INSERT INTO LibraryLogs
            (id_number, name, classification, college, course, library, checkin_time, sex, agency_organization)
        VALUES (:idNumber, :name, :classification, :college, :course, :sectionID, :now, :sex, :agency)
    ");
    $stmt->execute([
        ":idNumber" => $idNumber,
        ":name" => $user["name"],
        ":classification" => $user["classification"],
        ":college" => $user["college"],
        ":course" => $user["course"],
        ":sectionID" => $libraryID,
        ":now" => $now,
        ":sex" => $user["sex"],
        ":agency" => $user["agency_organization"] ?? "",
    ]);
}

function performCheckout(PDO $pdo, string $idNumber, int $libraryID, string $now)
{
    $start = date("Y-m-d 00:00:00");
    $end = date("Y-m-d 00:00:00", strtotime("+1 day"));

    $stmt = $pdo->prepare("
        UPDATE LibraryLogs
        SET checkout_time = :now
        WHERE id_number = :idNumber
          AND library = :sectionID
          AND checkout_time IS NULL
          AND checkin_time >= :start
          AND checkin_time < :end
    ");
    $stmt->execute([
        ":now" => $now,
        ":idNumber" => $idNumber,
        ":sectionID" => $libraryID,
        ":start" => $start,
        ":end" => $end,
    ]);
}

// Every guest scan is a fresh record — id_number is '0' (no institutional ID).
function performGuestCheckin(PDO $pdo, int $libraryID, string $now, array $user)
{
    $stmt = $pdo->prepare("
        INSERT INTO LibraryLogs
            (id_number, name, classification, college, course, library, checkin_time, sex, agency_organization)
        VALUES ('0', :name, 'GUEST', '', '', :sectionID, :now, :sex, :agency)
    ");
    $stmt->execute([
        ":name" => $user["name"],
        ":sectionID" => $libraryID,
        ":now" => $now,
        ":sex" => $user["sex"],
        ":agency" => $user["agency_organization"],
    ]);
}

//  KPI 

function kpiData(PDO $pdo, int $libraryID): array
{
    $start = date("Y-m-d 00:00:00");
    $end = date("Y-m-d 00:00:00", strtotime("+1 day"));
    $params = [":sectionID" => $libraryID, ":start" => $start, ":end" => $end];

    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS totalToday,
            SUM(CASE WHEN checkout_time IS NULL THEN 1 ELSE 0 END) AS currentlyInside
        FROM LibraryLogs
        WHERE library = :sectionID
          AND checkin_time >= :start
          AND checkin_time  < :end
    ");
    $stmt->execute($params);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);

    $topBy = function (string $col) use ($pdo, $params): array {
        $stmt = $pdo->prepare("
            SELECT TOP 3 {$col}, COUNT(*) AS total
            FROM LibraryLogs
            WHERE library = :sectionID
              AND checkin_time >= :start
              AND checkin_time < :end
              AND {$col} IS NOT NULL
              AND {$col} <> ''
            GROUP BY {$col}
            ORDER BY total DESC, {$col} ASC
        ");
        $stmt->execute($params);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), $col);
    };

    return [
        "totalToday" => (int) ($totals["totalToday"] ?? 0),
        "currentlyInside" => (int) ($totals["currentlyInside"] ?? 0),
        "topColleges" => array_pad($topBy("college"), 3, "-"),
        "topCourses" => array_pad($topBy("course"), 3, "-"),
    ];
}

//  HANDLERS 

function GetLibraries()
{
    $userID = (int) ($_POST["userID"] ?? 0);
    if (!$userID) { echo json_encode(["error" => "Missing or invalid userID."]); exit; }

    try {
        $data = execsqlSRS("
            SELECT ls.SectionID, ls.SectionName
            FROM LibraryAccess  la
            JOIN LibrarySection ls ON ls.SectionID = la.SectionID
            WHERE la.UserID  = ?
              AND ls.IsActive = 1
        ", "Query", [$userID]);
        echo json_encode(["success" => true, "data" => $data]);
    } catch (Exception $e) {
        error_log("[LibraryLogs] GetLibraries: " . $e->getMessage());
        echo json_encode(["error" => "A database error occurred. Please try again."]);
    }
    exit;
}

function ValidateUser()
{
    $idNumber = trim($_POST["idNumber"] ?? "");
    if (!$idNumber)
        { echo json_encode(["error" => "Identification number is required."]); exit; }
    if (!preg_match('/^[A-Z0-9-]+$/i', $idNumber))
        { echo json_encode(["error" => "Invalid ID format."]); exit; }

    try {
        $matches = resolveUserById($idNumber);
    } catch (Exception $e) {
        error_log("[LibraryLogs] ValidateUser: " . $e->getMessage());
        echo json_encode(["error" => "A database error occurred. Please try again."]); exit;
    }

    $count = count($matches);

    if (!$count)
        { echo json_encode(["error" => "User not found."]); exit; }
    if ($count === 1)
        { echo json_encode(["success" => true, "data" => $matches[0]]); exit; }

    echo json_encode(["duplicate" => true, "matches" => $matches, "modalHTML" => buildDuplicateModal()]); exit;
}

// Finds the user's latest active session today (checkout_time IS NULL).
// If found then currently checked in somewhere. Not found then not checked in today.
function CheckStatusToday()
{
    $idNumber = trim($_POST["idNumber"] ?? "");
    $name = trim($_POST["name"] ?? "");
    if (!$idNumber) { echo json_encode(["error" => "Identification number is required."]); 
    exit; }

    $start = date("Y-m-d 00:00:00");
    $end = date("Y-m-d 00:00:00", strtotime("+1 day"));

    try {
        $stmt = dbconES()->prepare("
            SELECT TOP 1 ll.library, ls.SectionName
            FROM LibraryLogs ll
            LEFT JOIN LibrarySection ls ON ls.SectionID = ll.library
            WHERE ll.id_number = :idNumber
              AND ll.name = :name
              AND ll.checkout_time IS NULL
              AND ll.checkin_time >= :start
              AND ll.checkin_time < :end
            ORDER BY ll.checkin_time DESC
        ");
        $stmt->execute([":idNumber" => $idNumber, ":name" => $name, ":start" => $start, ":end" => $end]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("[LibraryLogs] CheckStatusToday: " . $e->getMessage());
        echo json_encode(["error" => "A database error occurred. Please try again."]); exit;
    }

    if (!$session) { echo json_encode(["checkedIn" => false]); exit; }

    echo json_encode([
        "checkedIn" => true,
        "sectionID" => (int) $session["library"],
        "sectionName" => $session["SectionName"] ?? "another library",
    ]); exit;
}


function AttendanceModal()
{
    $user = json_decode(trim($_POST["user"] ?? "{}"), true);
    $action = trim($_POST["action"] ?? "checkin");

    if (!is_array($user) || empty($user))
        { echo json_encode(["error" => "Invalid user data."]);
    exit; }
    if (!isset(ACTION_CONFIG[$action]))
        { echo json_encode(["error" => "Invalid action."]);
    exit; }

    $config = ACTION_CONFIG[$action];
    $message = $config["message"];

    if ($action === "switch") {
        $sectionName = htmlspecialchars((string) (trim($_POST["sectionName"] ?? "") ?: "another library"), ENT_QUOTES, "UTF-8");
        $message = "You forgot to check out at <strong>{$sectionName}</strong>. No worries — we've automatically checked you out and completed your check-in here.";
    }

    $modal = buildAttendanceModal(
        $user,
        $config["color"],
        $config["icon"],
        $config["btnText"],
        $message ?? "",
        trim($_POST["libraryName"] ?? "")
    );

    echo json_encode(["success" => true] + $modal); exit;
}

function GuestCheckInModal()
{
    $libraryName = htmlspecialchars((string) trim($_POST["libraryName"] ?? ""), ENT_QUOTES, "UTF-8");
    $labelStyle = "font-size:.65rem;letter-spacing:.12em;color:#6b7280;";
    $inputStyle = "border-radius:12px;height:40px;font-size:.9rem;border-color:#d1d5db;";
    $selectStyle = "height:44px;border-radius:12px;font-size:.9rem;border-color:#d1d5db;";

    $body = <<<HTML
<div style='background:#ecfdf5;border:1px solid #d1fae5;border-radius:16px;padding:22px;'>
    <div class='d-flex align-items-center justify-content-between mb-4 pb-3'
         style='border-bottom:1px solid #d1fae5;'>
        <div class='d-flex align-items-center gap-3'>
            <div class='d-flex align-items-center justify-content-center'
                 style='width:44px;height:44px;border-radius:14px;background:#d1fae5;color:#047857;font-size:1.05rem;'>
                <i class='fas fa-book-open'></i>
            </div>
            &nbsp;&nbsp;
            <div class='lh-sm'>
                <div class='text-uppercase fw-semibold' style='$labelStyle'>Library</div>
                <div class='fw-semibold' style='font-size:1.05rem;color:#064e3b;'>{$libraryName}</div>
            </div>
        </div>
        <span class='badge rounded-pill'
              style='background:#d1fae5;color:#047857;font-size:.75rem;padding:7px 14px;font-weight:600;'>
            Guest Check-In
        </span>
    </div>
    <div class='mb-3'>
    <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>First Name</label>
    <input type='text' id='guestFirstName' class='form-control'
           placeholder='Enter first name' autocomplete='off' style='$inputStyle'>
</div>
<div class='row g-3 mb-3'>
    <div class='col-4'>
        <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>
            M.I. <span style='font-weight:400;opacity:.55;font-size:.6rem;'>(optional)</span>
        </label>
        <input type='text' id='guestMiddleInitial' class='form-control text-center'
               placeholder='A.' maxlength='2' autocomplete='off' style='$inputStyle'>
    </div>
    <div class='col-8'>
        <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>Last Name</label>
        <input type='text' id='guestLastName' class='form-control'
               placeholder='Enter last name' autocomplete='off' style='$inputStyle'>
    </div>
</div>
    <div class='row g-3'>
        <div class='col-6'>
            <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>Sex</label>
            <select id='guestSex' class='form-select w-100'
                    style='{$selectStyle}text-align:center;text-align-last:center;'>
                <option value=''>Select</option>
                <option value='Male'>Male</option>
                <option value='Female'>Female</option>
            </select>
        </div>
        <div class='col-6'>
            <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>Type</label>
            <input type='text' class='form-control w-100 fw-semibold text-success' value='GUEST' readonly
                   style='height:44px;border-radius:12px;font-size:.9rem;background:#dcfce7;
                          border:1px solid #86efac;text-align:center;letter-spacing:.05em;'>
        </div>
    </div>
    <div class='mt-3'>
        <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>Agency / Organization</label>
        <input type='text' id='guestAgency' class='form-control'
               placeholder='Enter agency or organization' autocomplete='off' style='$inputStyle'>
    </div>
</div>
HTML;

    $footer = <<<HTML
<button type='button' class='btn btn-light border rounded-pill px-4' data-bs-dismiss='modal'>Cancel</button>
<button type='button' class='btn btn-success rounded-pill px-4 fw-semibold' id='confirmGuestCheckIn'>
    <i class='fas fa-sign-in-alt me-2'></i>Check In
</button>
HTML;

    echo json_encode(["success" => true, "body" => $body, "footer" => $footer]); exit;
}

function GuestCheckOutModal()
{
    $libraryID = (int) ($_POST["sectionID"] ?? 0);
    $libraryName = htmlspecialchars((string) trim($_POST["libraryName"] ?? ""), ENT_QUOTES, "UTF-8");
    if (!$libraryID) { echo json_encode(["error" => "Missing section ID."]); exit; }

    $start = date("Y-m-d 00:00:00");
    $end = date("Y-m-d 00:00:00", strtotime("+1 day"));

    try {
        $stmt = dbconES()->prepare("
            SELECT id, name, sex, agency_organization, checkin_time
            FROM   LibraryLogs
            WHERE  library = :sectionID
              AND  classification = 'GUEST'
              AND  checkout_time  IS NULL
              AND  checkin_time >= :start
              AND  checkin_time < :end
            ORDER  BY checkin_time DESC
        ");
        $stmt->execute([":sectionID" => $libraryID, ":start" => $start, ":end" => $end]);
        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("[LibraryLogs] GuestCheckOutModal: " . $e->getMessage());
        echo json_encode(["error" => "A database error occurred. Please try again."]); exit;
    }

    $guestRows = implode("", array_map("buildGuestRow", $guests));
    $guestCount = count($guests);
    $guestLabel = $guestCount !== 1 ? "guests" : "guest";
    $emptyStyle = $guestCount ? "display:none;" : "";

    $body = "
    <div style='background:#fef2f2;border:1px solid #fca5a5;border-radius:16px;padding:22px;'>
        <div class='d-flex align-items-center justify-content-between mb-4 pb-3'
             style='border-bottom:1px solid #fca5a5;'>
            <div class='d-flex align-items-center gap-3'>
                <div class='d-flex align-items-center justify-content-center'
                     style='width:44px;height:44px;border-radius:14px;background:#fca5a5;color:#b91c1c;font-size:1.05rem;'>
                    <i class='fas fa-book-open'></i>
                </div>
                &nbsp;&nbsp;
                <div class='lh-sm'>
                    <div class='text-uppercase fw-semibold'
                         style='font-size:.65rem;letter-spacing:.14em;color:#6b7280;'>Library</div>
                    <div class='fw-semibold' style='font-size:1.05rem;color:#991b1b;'>{$libraryName}</div>
                </div>
            </div>
            <span class='badge rounded-pill'
                  style='background:#fee2e2;color:#dc2626;font-size:.75rem;padding:7px 14px;font-weight:600;'>
                {$guestCount} {$guestLabel}
            </span>
        </div>
        <div class='mb-4 position-relative'>
            <i class='fas fa-search position-absolute'
               style='left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.85rem;'></i>
            <input type='text' id='guestSearchInput' class='form-control'
                   placeholder='Search guest name'
                   style='padding-left:36px;border-radius:12px;height:40px;font-size:.9rem;border-color:#fca5a5;'>
        </div>
        <div id='guestCheckOutList' style='max-height:300px;overflow-y:auto;padding-right:2px;'>
            {$guestRows}
            <div id='guestEmptyState' class='text-center text-muted py-4' style='font-size:.85rem;{$emptyStyle}'>
                <i class='fas fa-users-slash mb-2 d-block' style='font-size:1.5rem;opacity:.35;'></i>
                No guests currently checked in.
            </div>
            <div id='guestNoResults' class='text-center text-muted py-3' style='font-size:.85rem;display:none;'>
                No guests match your search.
            </div>
        </div>
    </div>";

    $footer = "<button type='button' class='btn btn-light border rounded-pill px-4' data-bs-dismiss='modal'>Close</button>";

    echo json_encode(["success" => true, "body" => $body, "footer" => $footer]); exit;
}

function GuestCheckOut()
{
    global $now;
    $logID = (int) ($_POST["logID"] ?? 0);
    $libraryID = (int) ($_POST["sectionID"] ?? 0);
    if (!$logID || !$libraryID) { echo json_encode(["error" => "Missing log ID or section ID."]); exit; }

    try {
        $stmt = dbconES()->prepare("
            UPDATE LibraryLogs
            SET checkout_time = :now
            WHERE id = :logID
              AND library = :sectionID
              AND classification = 'GUEST'
              AND checkout_time  IS NULL
        ");
        $stmt->execute([":now" => $now, ":logID" => $logID, ":sectionID" => $libraryID]);
    } catch (Exception $e) {
        error_log("[LibraryLogs] GuestCheckOut: " . $e->getMessage());
        echo json_encode(["error" => "A database error occurred. Please try again."]); exit;
    }

    echo json_encode(["success" => true]); exit;
}

function SaveAttendance()
{
    global $now;

    $idNumber = trim($_POST["idNumber"] ?? "");
    $libraryID = (int)  ($_POST["sectionID"] ?? 0);
    $action = trim($_POST["action"] ?? "");
    $classification = trim($_POST["classification"] ?? "STUDENT");

    $user = [
        "name" => trim($_POST["name"] ?? ""),
        "classification" => $classification,
        "college" => trim($_POST["college"] ?? ""),
        "course" => trim($_POST["course"] ?? ""),
        "sex" => trim($_POST["sex"] ?? ""),
        "agency_organization" => trim($_POST["agency_organization"] ?? ""),
    ];

    if ($classification !== "GUEST" && !$idNumber){ 
        echo json_encode(["error" => "Missing required attendance data."]); 
    exit; }
    if (!$libraryID || !$action){ 
        echo json_encode(["error" => "Missing required attendance data."]); 
    exit; }
    if ($idNumber && !preg_match('/^[A-Z0-9-]+$/i', $idNumber)) { 
        echo json_encode(["error" => "Invalid ID format."]); 
    exit; }
    if (!in_array($action, ["checkin", "checkout"])){ 
        echo json_encode(["error" => "Invalid attendance action: '{$action}'."]); 
    exit; }

    $pdo = dbconES();

    try {
        $pdo->beginTransaction();

        $isRealGuest = empty($idNumber);

        if ($isRealGuest) {
            performGuestCheckin($pdo, $libraryID, $now, $user);
        } elseif ($action === "checkin") {
            performCheckin($pdo, $idNumber, $libraryID, $now, $user);
        } else {
            performCheckout($pdo, $idNumber, $libraryID, $now);
        }

        $pdo->commit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("[LibraryLogs] SaveAttendance: " . $e->getMessage());
        echo json_encode(["error" => "A database error occurred. Please try again."]); exit;
    }

    echo json_encode(["success" => true, "action" => $action]); exit;
}

function ShowKPI()
{
    $libraryID = (int) ($_POST["sectionID"] ?? 0);
    if (!$libraryID) { echo json_encode(["error" => "Missing or invalid sectionID."]); exit; }

    try {
        $data = kpiData(dbconES(), $libraryID);
        echo json_encode(["success" => true, "data" => $data]);
    } catch (Exception $e) {
        error_log("[LibraryLogs] ShowKPI: " . $e->getMessage());
        echo json_encode(["error" => "A database error occurred. Please try again."]);
    }
    exit;
}

//  HTML BUILDERS 
function buildAttendanceModal(
    array $user, string $color, string $icon,
    string $btnText, string $message, string $libraryName
): array {
    $isEmployee = $user["classification"] === "EMPLOYEE";
    $isGuest = $user["classification"] === "GUEST";

    $buildRow = fn($label, $value) =>
        "<div class='row mb-2'>
            <div class='col-5 fw-semibold'>{$label}</div>
            <div class='col-7'>{$value}</div>
         </div>";

         $rows = "";
         if (!$isGuest) {
             $rows .= $buildRow("ID",   htmlspecialchars((string) ($user["id_number"] ?? ""),    ENT_QUOTES, "UTF-8"));
         }
         $rows .= $buildRow("Name", htmlspecialchars((string) ($user["name"] ?? ""), ENT_QUOTES, "UTF-8"));
         $rows .= $buildRow("Sex",  htmlspecialchars((string) ($user["sex"] ?? "N/A"), ENT_QUOTES, "UTF-8"));
         $rows .= $buildRow("Type", htmlspecialchars((string) ($user["classification"] ?? ""),    ENT_QUOTES, "UTF-8"));
         if (!$isEmployee && !$isGuest) {
             $rows .= $buildRow("College", htmlspecialchars((string) ($user["college"] ?? "N/A"), ENT_QUOTES, "UTF-8"));
             $rows .= $buildRow("Course",  htmlspecialchars((string) ($user["course"]  ?? "N/A"), ENT_QUOTES, "UTF-8"));
         }
         if ($isGuest) {
             $rows .= $buildRow("Agency", htmlspecialchars((string) ($user["agency_organization"] ?? "N/A"), ENT_QUOTES, "UTF-8"));
         }

    $safeLibrary = htmlspecialchars((string) $libraryName, ENT_QUOTES, "UTF-8");

    $body = "
    <div class='text-center mb-3'>
        <div class='badge bg-{$color} fs-6 p-2'>
            <i class='fas {$icon} me-2'></i>{$btnText} Confirmation
        </div>
        <p class='text-muted mt-2 small'>" . strip_tags($message, "<strong><em>") . "</p>
    </div>
    <div class='bg-light p-3 rounded'>
        {$rows}
        <hr>
        <div class='text-center fw-bold text-primary'>Library: {$safeLibrary}</div>
    </div>";

    $footer = "
    <button type='button' class='btn btn-outline-secondary' data-bs-dismiss='modal'>Cancel</button>
    <button type='button' class='btn btn-{$color}' id='confirmAttendance'>
        <i class='fas {$icon} me-1'></i>{$btnText}
    </button>";

    return compact("body", "footer");
}

// Shown when multiple students share the same ID number.
function buildDuplicateModal(): string
{
    return "
    <div class='text-center mb-3'>
        <div class='badge bg-warning fs-6 p-2'>
            <i class='fas fa-user-shield me-2'></i>Duplicate ID Found
        </div>
        <p class='text-muted mt-2 small'>Enter your birth date (MM/DD/YYYY)</p>
    </div>
    <div class='card bg-light p-3 border-0'>
        <div class='input-group mb-2'>
            <input type='text' id='modalSecretKey'
                   class='form-control text-center fw-bold fs-5'
                   maxlength='10' placeholder='MM/DD/YYYY' autocomplete='off'>
            <button class='btn btn-outline-secondary' type='button' id='toggleSecretKey'>
                <i class='fas fa-eye' id='secretIcon'></i>
            </button>
        </div>
        <div id='secretKeyStatus' class='small text-muted mt-1'>
            <i class='fas fa-info-circle me-1'></i>Enter birth date (MM/DD/YYYY)
        </div>
    </div>
    <div id='verifiedStudentContainer' class='mt-3' style='display:none;'></div>";
}

// Builds a single guest row for the checkout modal list.
function buildGuestRow(array $guest): string
{
    $logID = (int) $guest["id"];
    $guestName = htmlspecialchars((string) ($guest["name"] ?? ""), ENT_QUOTES, "UTF-8");
    $guestSex = htmlspecialchars((string) ($guest["sex"] ?? ""), ENT_QUOTES, "UTF-8");
    $organization = htmlspecialchars((string) ($guest["agency_organization"] ?? ""), ENT_QUOTES, "UTF-8");
    $checkinTime = date("h:i A", strtotime($guest["checkin_time"]));

    return "
    <div class='guest-row d-flex align-items-center gap-3 px-3 py-3 mb-2'
         data-name='{$guestName}'
         style='background:#ffffff;border:1px solid #fca5a5;border-radius:14px;'>
        <div class='d-flex align-items-center justify-content-center flex-shrink-0'
             style='width:42px;height:42px;border-radius:50%;background:#fee2e2;color:#dc2626;font-size:.9rem;'>
            <i class='fas fa-user'></i>
        </div>
        &nbsp;&nbsp;
        <div class='flex-grow-1' style='min-width:0;'>
            <div class='fw-semibold text-dark' style='font-size:.92rem;letter-spacing:.01em;'>{$guestName}</div>
            <div class='d-flex align-items-center flex-wrap gap-2 text-muted' style='font-size:.75rem;'>
                <span>{$guestSex}</span>&nbsp;
                <span style='opacity:.35;'>•</span>&nbsp;
                <span class='text-truncate' style='max-width:170px;'>{$organization}</span>&nbsp;
                <span style='opacity:.35;'>•</span>&nbsp;
                <span class='text-danger fw-semibold'>
                    {$checkinTime}&nbsp;&nbsp;<i class='fas fa-clock me-1' style='font-size:.65rem;'></i>
                </span>
            </div>
        </div>
        <button type='button'
                class='btn btn-sm btn-outline-danger rounded-pill px-3 btn-guest-checkout'
                data-logid='{$logID}' data-name='{$guestName}'
                style='font-size:.75rem;height:32px;'>
            <i class='fas fa-sign-out-alt me-1'></i>Out
        </button>
    </div>";
}

//  DISPATCH 
$request = trim($_POST["request"] ?? "");

switch ($request) {
    case "getLibraries": 
        GetLibraries();
        break;
    case "getValidateUser":
        ValidateUser();
        break;
    case "checkStatusToday":
        CheckStatusToday();
        break;
    case "getAttendanceModal":
        AttendanceModal();
        break;
    case "guestCheckIn":
        GuestCheckInModal();
        break;
    case "getSaveAttendance":
        SaveAttendance();
        break;
    case "getGuestCheckOutModal":
        GuestCheckOutModal();
        break;
    case "guestCheckOut":
        GuestCheckOut();
        break;
    case "getKPI":
        ShowKPI();
        break;
    default: echo json_encode(["error" => "Unknown request: '{$request}'"]);
}