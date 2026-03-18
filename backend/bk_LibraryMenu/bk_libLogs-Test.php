<?php
// Library Logs Backend
// Handles: user validation, attendance logging, KPI reporting
// Database: Library_logs (id, id_number, name, college, course, library,
//           checkin_time, checkout_time, sex, classification)

include "../../db/dbconnection.php";
date_default_timezone_set("Asia/Manila");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(["error" => "Invalid request method."]);
}

 
// BOOTSTRAP
 

$now = date("Y-m-d H:i:s");

$USER_SOURCE = [
    "students"  => __DIR__ . "/../../API_requests/students.json",
    "employees" => __DIR__ . "/../../API_requests/employees.json",
];

// $USER_SOURCE = [
//     "students"  => "https://api.yourschool.com/students",
//     "employees" => "https://api.yourschool.com/employees",
// ];


 
// CONFIG
const ACTION_CONFIG = [
    "checkin"  => ["color" => "success", "icon" => "fa-sign-in-alt",  "btnText" => "Check In",         "message" => "Not checked in yet"],
    "checkout" => ["color" => "danger",  "icon" => "fa-sign-out-alt", "btnText" => "Check Out",         "message" => "Currently in this library"],
    "switch"   => ["color" => "warning", "icon" => "fa-random",       "btnText" => "Switch & Check In", "message" => null],
];

 
// UTILITIES
// Clearer: responseData instead of payload
function sendResponse(array $responseData): void {
    echo json_encode($responseData);
    exit;
}

function fail(string $message): void {
    sendResponse(["error" => $message]);
}

function escHtml(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

function validateId(string $id): bool
{
    return (bool) preg_match('/^[A-Z0-9-]+$/i', $id);
}

// Returns [start, end) boundaries for today — index-friendly, no CAST() needed.
function todayRange(): array
{
    return [
        date("Y-m-d 00:00:00"),
        date("Y-m-d 00:00:00", strtotime("+1 day")),
    ];
}

function run(PDO $pdo, string $sql, array $params = []): PDOStatement
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

 
// USER RESOLUTION
// Returns all records matching $id — duplicate IDs trigger the secret key modal.
function resolveUserById(string $id): array
{
    global $USER_SOURCE;

    $group = str_starts_with(strtoupper($id), "TAU") ? "employees" : "students";
    $file  = $USER_SOURCE[$group] ?? null;

    if (!$file || !file_exists($file)) return [];

    // Clearer: jsonData instead of payload
    //replace this when API is ready
    $jsonData = json_decode(file_get_contents($file), true); //replace this when API is ready
    if (!is_array($jsonData)) return [];

        //API READY CODE
    // $apiUrl = $USER_SOURCE[$group] ?? null;
    // if (!$apiUrl) return [];
    
    // $jsonData = json_decode(file_get_contents($apiUrl), true);
    
    // if (!is_array($jsonData)) return [];
        //


        //API READY CODE
    // $source = $USER_SOURCE[$group] ?? null;
    // if (!$source) return [];
    
    // $rawData = $source["type"] === "file"
    //     ? file_get_contents($source["path"])
    //     : file_get_contents($source["url"]);
    
    // $jsonData = json_decode($rawData, true);
    
    // if (!is_array($jsonData)) return [];
        //

    // Clearer: records instead of reusing payload
    $records = isset($jsonData[0])
        ? $jsonData
        : ($jsonData["data"]
        ?? $jsonData["employees"]
        ?? $jsonData["students"]
        ?? $jsonData["records"]
        ?? $jsonData["items"]
        ?? []);

    $mapper  = $group === "employees" ? "mapEmployee" : "mapStudent";
    $matches = [];

    foreach ($records as $record) {
        $user = $mapper($record);
        if ($user["id_number"] === $id) {
            $matches[] = $user;
        }
    }

    return $matches;
}

function mapStudent(array $student): array
{
    return [
        "id_number"      => $student["id_number"] ?? "",
        "name"           => $student["name"]      ?? "",
        "sex"            => $student["sex"]       ?? null,
        "college"        => $student["college"]   ?? null,
        "course"         => $student["course"]    ?? null,
        "classification" => "STUDENT",
        "secretKey"      => $student["birthDate"] ?? null,
    ];
}

function mapEmployee(array $employee): array
{
    return [
        "id_number"      => $employee["employee_number"] ?? $employee["id_number"] ?? "",
        "name"           => $employee["name"] ?? "",
        "sex"            => $employee["sex"]  ?? null,
        "college"        => "",
        "course"         => "",
        "classification" => "EMPLOYEE",
        "secretKey"      => null,
    ];
}

 
// ATTENDANCE LOGIC
// Skips silently if already checked in here; auto-closes any other open session
// (switch scenario); then inserts a new log entry.
function performCheckin(PDO $pdo, string $idNumber, int $sectionID, string $now, array $user): void
{
    [$start, $end] = todayRange();

    $params = [
        ":idNumber" => $idNumber,
        ":name" => $user["name"],
        ":sectionID" => $sectionID,
        ":start" => $start,
        ":end" => $end,
    ];

    $alreadyHere = (int) run($pdo, "
        SELECT COUNT(*)
        FROM   Library_logs
        WHERE  id_number = :idNumber
          AND  name = :name
          AND  library = :sectionID
          AND  checkout_time IS NULL
          AND  checkin_time >= :start
          AND  checkin_time  < :end
    ", $params)->fetchColumn();

    if ($alreadyHere > 0) return;

    run($pdo, "
        UPDATE Library_logs
        SET    checkout_time = :now
        WHERE  id_number = :idNumber
          AND  name = :name
          AND  checkout_time IS NULL
          AND  checkin_time >= :start
          AND  checkin_time < :end
          AND  library <> :sectionID
    ", $params + [":now" => $now]);

    run($pdo, "
        INSERT INTO Library_logs
            (id_number, name, classification, college, course, library, checkin_time, sex, agency_organization)
        VALUES (:idNumber, :name, :classification, :college, :course, :sectionID, :now, :sex, :agency)
    ", [
        ":idNumber" => $idNumber,
        ":name" => $user["name"],
        ":classification" => $user["classification"],
        ":college" => $user["college"],
        ":course"  => $user["course"],
        ":sectionID" => $sectionID,
        ":now" => $now,
        ":sex" => $user["sex"],
        ":agency" => $user["agency_organization"] ?? "",
    ]);
}

function performCheckout(PDO $pdo, string $idNumber, int $sectionID, string $now): void
{
    [$start, $end] = todayRange();
    run($pdo, "
        UPDATE Library_logs
        SET    checkout_time = :now
        WHERE  id_number = :idNumber
          AND  library = :sectionID
          AND  checkout_time IS NULL
          AND  checkin_time >= :start
          AND  checkin_time < :end
    ", [":now" => $now, ":idNumber" => $idNumber, ":sectionID" => $sectionID, ":start" => $start, ":end" => $end]);
}

// Every guest scan is a fresh record — id_number is '0' (no institutional ID).
function performGuestCheckin(PDO $pdo, int $sectionID, string $now, array $user): void
{
    run($pdo, "
        INSERT INTO Library_logs
            (id_number, name, classification, college, course, library, checkin_time, sex, agency_organization)
        VALUES ('0', :name, 'GUEST', '', '', :sectionID, :now, :sex, :agency)
    ", [
        ":name" => $user["name"],
        ":sectionID" => $sectionID,
        ":now" => $now,
        ":sex" => $user["sex"],
        ":agency" => $user["agency_organization"],
    ]);
}

 
// KPI
// Uses checkin_time >= start AND < end instead of CAST()/CONVERT()
// so SQL Server can use an index on checkin_time if one exists.
function kpiData(PDO $pdo, int $sectionID): array
{
    [$start, $end] = todayRange();
    $params = [":sectionID" => $sectionID, ":start" => $start, ":end" => $end];

    $totals = run($pdo, "
        SELECT
            COUNT(*) AS totalToday,
            SUM(CASE WHEN checkout_time IS NULL THEN 1 ELSE 0 END) AS currentlyInside
        FROM Library_logs
        WHERE library = :sectionID
          AND checkin_time >= :start
          AND checkin_time  < :end
    ", $params)->fetch(PDO::FETCH_ASSOC);

    $topBy = fn(string $col) => array_column(run($pdo, "
        SELECT TOP 3 {$col}, COUNT(*) AS total
        FROM   Library_logs
        WHERE  library = :sectionID
          AND  checkin_time >= :start
          AND  checkin_time < :end
          AND  {$col} IS NOT NULL
          AND  {$col} <> ''
        GROUP  BY {$col}
        ORDER  BY total DESC, {$col} ASC
    ", $params)->fetchAll(PDO::FETCH_ASSOC), $col);

    return [
        "totalToday" => intval($totals["totalToday"]      ?? 0),
        "currentlyInside" => intval($totals["currentlyInside"] ?? 0),
        "topColleges" => array_pad($topBy("college"), 3, "-"),
        "topCourses" => array_pad($topBy("course"),  3, "-"),
    ];
}

 
// HANDLERS
function GetLibraries(): void
{
    $userID = (int)($_POST["userID"] ?? 0);
    if (!$userID) fail("Missing or invalid userID.");

    sendResponse(["success" => true, "data" => execsqlSRS("
        SELECT ls.SectionID, ls.SectionName
        FROM   LibraryAccess  la
        JOIN   LibrarySection ls ON ls.SectionID = la.SectionID
        WHERE  la.UserID   = ?
          AND  ls.IsActive  = 1
    ", "Query", [$userID])]);
}

function ValidateUser(): void
{
    $idNumber = trim($_POST["idNumber"] ?? "");
    if (!$idNumber) fail("Identification number is required.");
    if (!validateId($idNumber)) fail("Invalid ID format.");

    $matches = resolveUserById($idNumber);
    $count = count($matches);

    if (!$count) fail("User not found.");
    if ($count === 1) sendResponse(["success" => true, "data" => $matches[0]]);

    sendResponse(["duplicate" => true, "matches" => $matches, "modalHTML" => buildDuplicateModal()]);
}

// Finds the user's latest active session today (checkout_time IS NULL).
// Found → currently checked in somewhere. Not found → not checked in today.
function CheckStatusToday(): void
{
    $idNumber = trim($_POST["idNumber"] ?? "");
    $name = trim($_POST["name"] ?? "");
    if (!$idNumber) fail("Identification number is required.");

    [$start, $end] = todayRange();

    $session = run(dbconES(), "
        SELECT TOP 1 ll.library, ls.SectionName
        FROM   Library_logs   ll
        LEFT   JOIN LibrarySection ls ON ls.SectionID = ll.library
        WHERE  ll.id_number = :idNumber
          AND  ll.name = :name
          AND  ll.checkout_time IS NULL
          AND  ll.checkin_time >= :start
          AND  ll.checkin_time < :end
        ORDER  BY ll.checkin_time DESC
    ", [":idNumber" => $idNumber, ":name" => $name, ":start" => $start, ":end" => $end])
        ->fetch(PDO::FETCH_ASSOC);

    if (!$session) sendResponse(["checkedIn" => false]);

    sendResponse([
        "checkedIn" => true,
        "sectionID" => intval($session["library"]),
        "sectionName" => $session["SectionName"] ?? "another library",
    ]);
}

// Called after JS resolves the user and action. JS owns state/routing —
// PHP owns rendering. Resolves color/icon/btnText/message from ACTION_CONFIG.
//
// POST params:
//   user        – JSON-encoded user record
//   action      – checkin | checkout | switch
//   sectionName – Previous section name (only used when action is "switch")
//   libraryName – Current library section name for display
function AttendanceModal(): void
{
    $user = json_decode(trim($_POST["user"]   ?? "{}"), true);
    $action = trim($_POST["action"] ?? "checkin");

    if (!is_array($user) || empty($user)) fail("Invalid user data.");
    if (!isset(ACTION_CONFIG[$action]))   fail("Invalid action.");

    $config  = ACTION_CONFIG[$action];
    $message = $config["message"];

    if ($action === "switch") {
        $sectionName = escHtml(trim($_POST["sectionName"] ?? "") ?: "another library");
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

    sendResponse(["success" => true] + $modal);
}

function GuestCheckInModal(): void
{
    $libraryName = escHtml(trim($_POST["libraryName"] ?? ""));
    $labelStyle  = "font-size:.65rem;letter-spacing:.12em;color:#6b7280;";
    $inputStyle  = "border-radius:12px;height:40px;font-size:.9rem;border-color:#d1d5db;";
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
        <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>Full Name</label>
        <input type='text' id='guestName' class='form-control'
               placeholder='Enter full name' autocomplete='off' style='$inputStyle'>
    </div>
    <div class='row g-3'>
        <div class='col-6'>
            <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>Sex</label>
            <select id='guestSex' class='form-select w-100'
                    style='$selectStyle;text-align:center;text-align-last:center;'>
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

    sendResponse(["success" => true, "body" => $body, "footer" => $footer]);
}

function GuestCheckoutModal(): void
{
    $sectionID   = (int)($_POST["sectionID"]   ?? 0);
    $libraryName = escHtml(trim($_POST["libraryName"] ?? ""));
    if (!$sectionID) fail("Missing section ID.");

    [$start, $end] = todayRange();

    $guests = run(dbconES(), "
        SELECT id, name, sex, agency_organization, checkin_time
        FROM   Library_logs
        WHERE  library = :sectionID
          AND  classification = 'GUEST'
          AND  checkout_time  IS NULL
          AND  checkin_time >= :start
          AND  checkin_time < :end
        ORDER  BY checkin_time DESC
    ", [":sectionID" => $sectionID, ":start" => $start, ":end" => $end])->fetchAll(PDO::FETCH_ASSOC);

    $guestRows  = implode("", array_map('buildGuestRow', $guests));
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
        <div id='guestCheckoutList' style='max-height:300px;overflow-y:auto;padding-right:2px;'>
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

    sendResponse(["success" => true, "body" => $body, "footer" => $footer]);
}

function GuestCheckout(): void
{
    global $now;
    $logID     = (int)($_POST["logID"]     ?? 0);
    $sectionID = (int)($_POST["sectionID"] ?? 0);
    if (!$logID || !$sectionID) fail("Missing log ID or section ID.");

    run(dbconES(), "
        UPDATE Library_logs
        SET    checkout_time = :now
        WHERE  id = :logID
          AND  library = :sectionID
          AND  classification = 'GUEST'
          AND  checkout_time  IS NULL
    ", [":now" => $now, ":logID" => $logID, ":sectionID" => $sectionID]);

    sendResponse(["success" => true]);
}

function SaveAttendance(): void
{
    global $now;

    $idNumber = trim($_POST["idNumber"] ?? "");
    $sectionID = (int)($_POST["sectionID"] ?? 0);
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

    if ($classification !== "GUEST" && !$idNumber) fail("Missing required attendance data.");
    if (!$sectionID || !$action) fail("Missing required attendance data.");
    if ($idNumber && !validateId($idNumber)) fail("Invalid ID format.");
    if (!in_array($action, ["checkin", "checkout"])) fail("Invalid attendance action: '{$action}'.");

    $pdo = dbconES();

    try {
        $pdo->beginTransaction();

        if ($classification === "GUEST") performGuestCheckin($pdo, $sectionID, $now, $user);
        elseif ($action === "checkin") performCheckin($pdo, $idNumber, $sectionID, $now, $user);
        else performCheckout($pdo, $idNumber, $sectionID, $now);

        $pdo->commit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("[LibraryLogs] SaveAttendance: " . $e->getMessage());
        fail("A database error occurred. Please try again.");
    }

    sendResponse(["success" => true, "action" => $action]);
}

function ShowKPI(): void
{
    $sectionID = (int)($_POST["sectionID"] ?? 0);
    if (!$sectionID) fail("Missing or invalid sectionID.");
    sendResponse(["success" => true, "data" => kpiData(dbconES(), $sectionID)]);
}

 
// HTML BUILDERS
// All modal HTML is built here on the server.
// JS receives the HTML string and injects it — no HTML built in JS.
 

// Builds the attendance confirmation modal body and footer.
// JS sends action key + sectionName — PHP resolves color/icon/btnText/message
// from ACTION_CONFIG. JS owns state and routing — PHP owns rendering.
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

    $rows  = "";
    if (!$isGuest)                
        $rows .= $buildRow("ID",escHtml($user["id_number"]));
        $rows .= $buildRow("Name",escHtml($user["name"]));
        $rows .= $buildRow("Sex",escHtml($user["sex"] ?? "N/A"));
        $rows .= $buildRow("Type",escHtml($user["classification"]));
    if (!$isEmployee && !$isGuest) 
        $rows .= $buildRow("College", escHtml($user["college"]   ?? "N/A"))
                . $buildRow("Course",  escHtml($user["course"]    ?? "N/A"));
    if ($isGuest)
        $rows .= $buildRow("Agency",  escHtml($user["agency_organization"] ?? "N/A"));

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
        <div class='text-center fw-bold text-primary'>Library: " . escHtml($libraryName) . "</div>
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
    $logID = intval($guest["id"]);
    $guestName = escHtml($guest["name"] ?? "");
    $guestSex = escHtml($guest["sex"] ?? "");
    $organization = escHtml($guest["agency_organization"] ?? "");
    $checkinTime  = date("h:i A", strtotime($guest["checkin_time"]));

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

// DISPATCH
switch (trim($_POST["request"] ?? "")) {
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

    case "getGuestCheckoutModal": 
        GuestCheckoutModal();  
        break;

    case "guestCheckout":         
        GuestCheckout();       
        break;

    case "getKPI":                
        ShowKPI();             
        break;

    default: fail("Unknown request: '" . trim($_POST["request"] ?? "") . "'.");
}