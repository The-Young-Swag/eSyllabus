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

// ============================================================
// UTILITIES
// ============================================================

function sendResponse(array $payload): void
{
    echo json_encode($payload);
    exit;
}

function validateIdFormat(string $idNumber): bool
{
    return (bool) preg_match('/^[A-Z0-9-]+$/i', $idNumber);
}

function getTodayRange(string $today): array
{
    $start = $today . " 00:00:00";
    $end   = date("Y-m-d", strtotime("+1 day", strtotime($today))) . " 00:00:00";
    return [$start, $end];
}

// ============================================================
// DATA SOURCE CONFIG — Unified Local JSON / API Loader
// ============================================================

define("USE_LOCAL_DATA", true); // flip to false when API is ready

$USER_SOURCE = [
    "students"  => __DIR__ . "/../../API_requests/students.json",
    "employees" => __DIR__ . "/../../API_requests/employees.json",

    // Example API replacement:
    // "students"  => "https://your-school-api.edu/api/students",
    // "employees" => "https://your-school-api.edu/api/employees",
];

define("API_ID_PARAMS", [
    "students"  => "id_number",
    "employees" => "employee_number",
]);

// Resolves a user record from either local JSON or the API,
// filtered to the exact ID number provided.
// Note: validateIdFormat() allows lowercase via /i flag — ensure source
// JSON stores id_number in consistent casing to avoid === mismatches here.
function resolveUserById(string $idNumber): array
{
    $isEmployee  = str_starts_with(strtoupper($idNumber), "TAU");
    $sourceGroup = $isEmployee ? "employees" : "students";

    $rawPayload = loadUserPayload($sourceGroup, $idNumber);
    if (!$rawPayload) return [];

    $allRecords  = extractRecordsFromPayload($rawPayload);
    $mappedUsers = [];

    foreach ($allRecords as $record) {
        $mappedUsers[] = $isEmployee
            ? mapEmployeeToUserRecord($record)
            : mapStudentToUserRecord($record);
    }

    // array_values() resets keys after filter so $mappedUsers[0] is always safe
    return array_values(
        array_filter($mappedUsers, fn($user) => $user["id_number"] === $idNumber)
    );
}

// Loads a user payload from either a local JSON file or a remote API endpoint.
function loadUserPayload(string $source, string $idNumber = null): ?array
{
    global $USER_SOURCE;

    $localOrAPI = $USER_SOURCE[$source] ?? null;
    if (!$localOrAPI) return null;

    if (USE_LOCAL_DATA) {
        if (!file_exists($localOrAPI)) return null;
        $decoded = json_decode(file_get_contents($localOrAPI), true);
        return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null;
    }

    // API path
    $token = $_ENV["SCHOOL_API_TOKEN"] ?? null;
    if (!$token) return null;

    if ($idNumber) {
        $localOrAPI .= "?" . http_build_query([API_ID_PARAMS[$source] => $idNumber]);
    }

    return apiRequest($localOrAPI, $token);
}

function apiRequest(string $url, string $token): ?array
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer {$token}",
            "Accept: application/json",
        ],
    ]);

    $body       = curl_exec($curl);
    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($body === false || $statusCode !== 200) return null;

    $decoded = json_decode($body, true);
    return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null;
}

function extractRecordsFromPayload(array $payload): array
{
    if (isset($payload[0])) return $payload;

    foreach (["data", "employees", "students", "records", "items"] as $key) {
        if (isset($payload[$key]) && is_array($payload[$key])) return $payload[$key];
    }

    return [];
}

// ============================================================
// USER RECORD MAPPERS
// ============================================================

function mapStudentToUserRecord(array $student): array
{
    return [
        "id_number"      => $student["id_number"] ?? "",
        "name"           => $student["name"]      ?? "",
        "sex"            => $student["sex"]        ?? null,
        "college"        => $student["college"]    ?? null,
        "course"         => $student["course"]     ?? null,
        "classification" => "STUDENT",
        "secretKey"      => $student["birthDate"]  ?? null,
    ];
}

function mapEmployeeToUserRecord(array $employee): array
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

// ============================================================
// HTML GENERATORS
// All modal HTML is built here on the server.
// JS receives the HTML string and injects it — no HTML built in JS.
// ============================================================

// Builds the attendance confirmation modal body and footer.
// Called after JS has validated the user, checked their status,
// and determined the action (checkin / checkout / switch).
// JS owns state and routing — PHP owns rendering.
// $message may contain only <strong> and <em> tags (strip_tags enforced).
function buildAttendanceModalHTML(
    array $user,
    string $color,
    string $icon,
    string $btnText,
    string $message,
    string $libraryName
): array {
    $isEmployee = $user["classification"] === "EMPLOYEE";
    $isGuest    = $user["classification"] === "GUEST";

    $escape = fn($value) => htmlspecialchars($value ?? "");

    $idNumber   = $escape($user["id_number"]);
    $name       = $escape($user["name"]);
    $sex        = $escape($user["sex"] ?? "N/A");
    $type       = $escape($user["classification"]);
    $library    = $escape($libraryName);
    $message    = strip_tags($message, "<strong><em>");

    $buildRow = fn($label, $value) =>
        "<div class='row mb-2'>
            <div class='col-5 fw-semibold'>{$label}</div>
            <div class='col-7'>{$value}</div>
         </div>";

    $rows = "";

    if (!$isGuest) {
        $rows .= $buildRow("ID", $idNumber);
    }

    $rows .= $buildRow("Name", $name);
    $rows .= $buildRow("Sex",  $sex);
    $rows .= $buildRow("Type", $type);

    if (!$isEmployee && !$isGuest) {
        $rows .= $buildRow("College", $escape($user["college"] ?? "N/A"));
        $rows .= $buildRow("Course",  $escape($user["course"]  ?? "N/A"));
    }

    if ($isGuest) {
        $rows .= $buildRow("Agency", $escape($user["agency_organization"] ?? "N/A"));
    }

    $body = "
    <div class='text-center mb-3'>
        <div class='badge bg-{$color} fs-6 p-2'>
            <i class='fas {$icon} me-2'></i>{$btnText} Confirmation
        </div>
        <p class='text-muted mt-2 small'>{$message}</p>
    </div>
    <div class='bg-light p-3 rounded'>
        {$rows}
        <hr>
        <div class='text-center fw-bold text-primary'>Library: {$library}</div>
    </div>";

    $footer = "
    <button type='button' class='btn btn-outline-secondary' data-bs-dismiss='modal'>Cancel</button>
    <button type='button' class='btn btn-{$color}' id='confirmAttendance'>
        <i class='fas {$icon} me-1'></i>{$btnText}
    </button>";

    return ["body" => $body, "footer" => $footer];
}

// Builds the duplicate ID verification modal.
// Shown when multiple students share the same ID number.
function DuplicateModal(): string
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
            <input type='text'
                   id='modalSecretKey'
                   class='form-control text-center fw-bold fs-5'
                   maxlength='10'
                   placeholder='MM/DD/YYYY'
                   autocomplete='off'>
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

// ============================================================
// KPI BUILDER
// Builds the KPI payload for a library section on a given date.
// Uses checkin_time >= start AND < end instead of CAST()/CONVERT()
// so SQL Server can use an index on checkin_time if one exists.
//
// Returns:
//   totalToday      – Total check-ins for the section today
//   currentlyInside – Users with no checkout_time (still present)
//   topColleges     – Top 3 colleges by visit count
//   topCourses      – Top 3 courses by visit count
// ============================================================

function KPIData(PDO $pdo, int $sectionID, string $today): array
{
    [$start, $end] = getTodayRange($today);

    // Totals
    $stmtTotals = $pdo->prepare("
        SELECT
            COUNT(*) AS totalToday,
            SUM(CASE WHEN checkout_time IS NULL THEN 1 ELSE 0 END) AS currentlyInside
        FROM Library_logs
        WHERE library      = ?
          AND checkin_time >= ?
          AND checkin_time  < ?
    ");
    $stmtTotals->execute([$sectionID, $start, $end]);
    $totals = $stmtTotals->fetch(PDO::FETCH_ASSOC);

    // Top colleges
    $stmtColleges = $pdo->prepare("
        SELECT TOP 3 college, COUNT(*) AS total
        FROM   Library_logs
        WHERE  library      = ?
          AND  checkin_time >= ?
          AND  checkin_time  < ?
          AND  college IS NOT NULL
          AND  college <> ''
        GROUP  BY college
        ORDER  BY total DESC, college ASC
    ");
    $stmtColleges->execute([$sectionID, $start, $end]);
    $colleges = array_column($stmtColleges->fetchAll(PDO::FETCH_ASSOC), "college");

    // Top courses
    $stmtCourses = $pdo->prepare("
        SELECT TOP 3 course, COUNT(*) AS total
        FROM   Library_logs
        WHERE  library      = ?
          AND  checkin_time >= ?
          AND  checkin_time  < ?
          AND  course IS NOT NULL
          AND  course <> ''
        GROUP  BY course
        ORDER  BY total DESC, course ASC
    ");
    $stmtCourses->execute([$sectionID, $start, $end]);
    $courses = array_column($stmtCourses->fetchAll(PDO::FETCH_ASSOC), "course");

    return [
        "totalToday"      => intval($totals["totalToday"]      ?? 0),
        "currentlyInside" => intval($totals["currentlyInside"] ?? 0),
        "topColleges"     => array_pad($colleges, 3, "-"),
        "topCourses"      => array_pad($courses,  3, "-"),
    ];
}

// ============================================================
// ATTENDANCE HELPERS
// ============================================================

// Performs the full check-in sequence:
//   1. Skips silently if the user is already checked in at this section today.
//   2. Auto-closes any open session in a DIFFERENT section (switch scenario).
//   3. Inserts a new log entry for this section.
function performCheckin(PDO $pdo, string $idNumber, int $sectionID, string $now, string $today, array $user): void
{
    [$start, $end] = getTodayRange($today);

    // Already here — nothing to do
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*)
        FROM   Library_logs
        WHERE  id_number     = ?
          AND  name          = ?
          AND  library       = ?
          AND  checkout_time IS NULL
          AND  checkin_time >= ?
          AND  checkin_time  < ?
    ");
    $stmtCheck->execute([$idNumber, $user["name"], $sectionID, $start, $end]);

    if (intval($stmtCheck->fetchColumn()) > 0) {
        return;
    }

    // Auto-checkout from any other section (switch scenario)
    $pdo->prepare("
        UPDATE Library_logs
        SET    checkout_time = ?
        WHERE  id_number     = ?
          AND  name          = ?
          AND  checkout_time IS NULL
          AND  checkin_time >= ?
          AND  checkin_time  < ?
          AND  library      <> ?
    ")->execute([$now, $idNumber, $user["name"], $start, $end, $sectionID]);

    // Insert new check-in
    $pdo->prepare("
        INSERT INTO Library_logs
            (id_number, name, classification, college, course, library, checkin_time, sex, agency_organization)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        $idNumber,
        $user["name"],
        $user["classification"],
        $user["college"],
        $user["course"],
        $sectionID,
        $now,
        $user["sex"],
        $user["agency_organization"] ?? "",
    ]);
}

// Closes the user's active session in the specified section.
function performCheckout(PDO $pdo, string $idNumber, int $sectionID, string $now, string $today): void
{
    [$start, $end] = getTodayRange($today);

    $pdo->prepare("
        UPDATE Library_logs
        SET    checkout_time = ?
        WHERE  id_number     = ?
          AND  library       = ?
          AND  checkout_time IS NULL
          AND  checkin_time >= ?
          AND  checkin_time  < ?
    ")->execute([$now, $idNumber, $sectionID, $start, $end]);
}

// Inserts a new guest visit. Every guest scan is a fresh record.
// id_number is '0' — guests have no institutional ID.
function performGuestCheckin(PDO $pdo, int $sectionID, string $now, array $user): void
{
    $pdo->prepare("
        INSERT INTO Library_logs
            (id_number, name, classification, college, course, library, checkin_time, sex, agency_organization)
        VALUES
            ('0', ?, 'GUEST', '', '', ?, ?, ?, ?)
    ")->execute([
        $user["name"],
        $sectionID,
        $now,
        $user["sex"],
        $user["agency_organization"],
    ]);
}

// ============================================================
// HANDLERS
// ============================================================

function GetLibraries(): void
{
    $userID = intval($_POST["userID"] ?? 0);
    if (!$userID) sendResponse(["error" => "Missing or invalid userID."]);

    $libraries = execsqlSRS("
        SELECT ls.SectionID, ls.SectionName
        FROM   LibraryAccess  la
        JOIN   LibrarySection ls ON ls.SectionID = la.SectionID
        WHERE  la.UserID  = ?
          AND  ls.IsActive = 1
    ", "Query", [$userID]);

    sendResponse(["success" => true, "data" => $libraries]);
}

function ValidateUser(): void
{
    $idNumber = trim($_POST["idNumber"] ?? "");

    if (!$idNumber)                    sendResponse(["error" => "Identification number is required."]);
    if (!validateIdFormat($idNumber))  sendResponse(["error" => "Invalid ID format."]);

    $matches = resolveUserById($idNumber);
    $count   = count($matches);

    if (!$count)      sendResponse(["error" => "User not found."]);
    if ($count === 1) sendResponse(["success" => true, "data" => $matches[0]]);

    sendResponse([
        "duplicate" => true,
        "matches"   => $matches,
        "modalHTML" => DuplicateModal(),
    ]);
}

// Check-in status algorithm:
// Finds the user's latest active session today (checkout_time IS NULL)
// using an index-friendly time range.
// Found → currently checked in. Not found → not checked in today.
function CheckStatusToday(): void
{
    $idNumber = trim($_POST["idNumber"] ?? "");
    $name     = trim($_POST["name"]     ?? "");

    if (!$idNumber) sendResponse(["error" => "Identification number is required."]);

    [$start, $end] = getTodayRange(date("Y-m-d"));
    $pdo = dbconES();

    $stmt = $pdo->prepare("
        SELECT TOP 1 ll.library, ls.SectionName
        FROM   Library_logs   ll
        LEFT   JOIN LibrarySection ls ON ls.SectionID = ll.library
        WHERE  ll.id_number     = ?
          AND  ll.name          = ?
          AND  ll.checkout_time IS NULL
          AND  ll.checkin_time >= ?
          AND  ll.checkin_time  < ?
        ORDER  BY ll.checkin_time DESC
    ");
    $stmt->execute([$idNumber, $name, $start, $end]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        sendResponse(["checkedIn" => false]);
    }

    sendResponse([
        "checkedIn"   => true,
        "sectionID"   => intval($row["library"]),
        "sectionName" => $row["SectionName"] ?? "another library",
    ]);
}

// Called by JS after it has determined the action (checkin/checkout/switch)
// and resolved the user. JS sends all the pieces; PHP renders the modal HTML.
// JS stays in charge of state/routing — PHP stays in charge of rendering.
//
// POST params:
//   user        – JSON-encoded user record
//   color       – Bootstrap color name (success / danger / warning)
//   icon        – FontAwesome class (fa-sign-in-alt / fa-sign-out-alt / fa-random)
//   btnText     – Button label
//   message     – Status message (only <strong><em> tags allowed)
//   libraryName – Current library section name for display
function AttendanceModal(): void
{
    $color       = trim($_POST["color"]       ?? "success");
    $icon        = trim($_POST["icon"]        ?? "fa-sign-in-alt");
    $btnText     = trim($_POST["btnText"]     ?? "Check In");
    $message     = trim($_POST["message"]     ?? "");
    $libraryName = trim($_POST["libraryName"] ?? "");
    $user        = json_decode(trim($_POST["user"] ?? "{}"), true);

    if (!is_array($user) || empty($user)) {
        sendResponse(["error" => "Invalid user data."]);
    }

    $modal = buildAttendanceModalHTML($user, $color, $icon, $btnText, $message, $libraryName);

    sendResponse([
        "success" => true,
        "body"    => $modal["body"],
        "footer"  => $modal["footer"],
    ]);
}

function GuestCheckInModal(): void
{
    $libraryName = htmlspecialchars(trim($_POST["libraryName"] ?? ""));

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
        <input type='text'
               id='guestName'
               class='form-control'
               placeholder='Enter full name'
               autocomplete='off'
               style='$inputStyle'>
    </div>

    <div class='row g-3'>
        <div class='col-6'>
            <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>Sex</label>
            <select id='guestSex' class='form-select w-100' style='$selectStyle'>
                <option value=''>Select</option>
                <option value='Male'>Male</option>
                <option value='Female'>Female</option>
            </select>
        </div>
        <div class='col-6'>
            <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>Type</label>
            <input type='text'
                   class='form-control w-100 fw-semibold text-success'
                   value='GUEST'
                   readonly
                   style='height:44px;border-radius:12px;font-size:.9rem;background:#dcfce7;border:1px solid #86efac;text-align:center;letter-spacing:.05em;'>
        </div>
    </div>

    <div class='mt-3'>
        <label class='text-uppercase fw-semibold mb-1' style='$labelStyle'>Agency / Organization</label>
        <input type='text'
               id='guestAgency'
               class='form-control'
               placeholder='Enter agency or organization'
               autocomplete='off'
               style='$inputStyle'>
    </div>
</div>
HTML;

    $footer = <<<HTML
<button type='button'
        class='btn btn-light border rounded-pill px-4'
        data-bs-dismiss='modal'>
    Cancel
</button>
<button type='button'
        class='btn btn-success rounded-pill px-4 fw-semibold'
        id='confirmGuestCheckIn'>
    <i class='fas fa-sign-in-alt me-2'></i>Check In
</button>
HTML;

    sendResponse(["success" => true, "body" => $body, "footer" => $footer]);
}

function GuestCheckoutModal(): void
{
    $sectionID   = intval($_POST["sectionID"]   ?? 0);
    $libraryName = htmlspecialchars(trim($_POST["libraryName"] ?? ""));

    if (!$sectionID) sendResponse(["error" => "Missing section ID."]);

    [$start, $end] = getTodayRange(date("Y-m-d"));

    $stmt = dbconES()->prepare("
        SELECT id, name, sex, agency_organization, checkin_time
        FROM   Library_logs
        WHERE  library        = ?
          AND  classification = 'GUEST'
          AND  checkout_time  IS NULL
          AND  checkin_time  >= ?
          AND  checkin_time   < ?
        ORDER  BY checkin_time DESC
    ");
    $stmt->execute([$sectionID, $start, $end]);
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rows = "";
    foreach ($guests as $guest) {
        $logID        = intval($guest["id"]);
        $guestName    = htmlspecialchars($guest["name"]                ?? "");
        $guestSex     = htmlspecialchars($guest["sex"]                 ?? "");
        $organization = htmlspecialchars($guest["agency_organization"] ?? "");
        $checkinTime  = date("h:i A", strtotime($guest["checkin_time"]));

        $rows .= "
        <div class='guest-row d-flex align-items-center gap-3 px-3 py-3 mb-2'
             data-name='{$guestName}'
             style='background:#ffffff;border:1px solid #fca5a5;border-radius:14px;'>
            <div class='d-flex align-items-center justify-content-center flex-shrink-0'
                 style='width:42px;height:42px;border-radius:50%;background:#fee2e2;color:#dc2626;font-size:.9rem;'>
                <i class='fas fa-user'></i>
            </div>
            &nbsp;&nbsp;
            <div class='flex-grow-1' style='min-width:0;'>
                <div class='fw-semibold text-dark' style='font-size:.92rem;letter-spacing:.01em;'>
                    {$guestName}
                </div>
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
                    data-logid='{$logID}'
                    data-name='{$guestName}'
                    style='font-size:.75rem;height:32px;'>
                <i class='fas fa-sign-out-alt me-1'></i>Out
            </button>
        </div>";
    }

    $guestCount = count($guests);
    $guestLabel = $guestCount !== 1 ? "guests" : "guest";

    $emptyState = !$guestCount
        ? "<div class='text-center text-muted py-4' style='font-size:.85rem;'>
               <i class='fas fa-users-slash mb-2 d-block' style='font-size:1.5rem;opacity:.35;'></i>
               No guests currently checked in.
           </div>"
        : "";

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
            <input type='text'
                   id='guestSearchInput'
                   class='form-control'
                   placeholder='Search guest name'
                   style='padding-left:36px;border-radius:12px;height:40px;font-size:.9rem;border-color:#fca5a5;'>
        </div>

        <div id='guestCheckoutList' style='max-height:300px;overflow-y:auto;padding-right:2px;'>
            {$rows}
            {$emptyState}
            <div id='guestNoResults'
                 class='text-center text-muted py-3'
                 style='font-size:.85rem;display:none;'>
                No guests match your search.
            </div>
        </div>
    </div>";

    $footer = "<button type='button' class='btn btn-light border rounded-pill px-4' data-bs-dismiss='modal'>Close</button>";

    sendResponse(["success" => true, "body" => $body, "footer" => $footer]);
}

function GuestCheckout(): void
{
    $logID     = intval($_POST["logID"]     ?? 0);
    $sectionID = intval($_POST["sectionID"] ?? 0);

    if (!$logID || !$sectionID) {
        sendResponse(["error" => "Missing log ID or section ID."]);
    }

    $pdo = dbconES();
    $pdo->prepare("
        UPDATE Library_logs
        SET    checkout_time = ?
        WHERE  id             = ?
          AND  library        = ?
          AND  classification = 'GUEST'
          AND  checkout_time  IS NULL
    ")->execute([date("Y-m-d H:i:s"), $logID, $sectionID]);

    sendResponse(["success" => true]);
}

function SaveAttendance(): void
{
    $idNumber        = trim($_POST["idNumber"]            ?? "");
    $sectionID       = intval($_POST["sectionID"]         ?? 0);
    $action          = trim($_POST["action"]              ?? "");
    $classification  = trim($_POST["classification"]      ?? "STUDENT");

    $user = [
        "name"                => trim($_POST["name"]                ?? ""),
        "classification"      => $classification,
        "college"             => trim($_POST["college"]             ?? ""),
        "course"              => trim($_POST["course"]              ?? ""),
        "sex"                 => trim($_POST["sex"]                 ?? ""),
        "agency_organization" => trim($_POST["agency_organization"] ?? ""),
    ];

    if ($classification !== "GUEST" && !$idNumber) sendResponse(["error" => "Missing required attendance data."]);
    if (!$sectionID || !$action)                   sendResponse(["error" => "Missing required attendance data."]);
    if ($idNumber && !validateIdFormat($idNumber))  sendResponse(["error" => "Invalid ID format."]);
    if (!in_array($action, ["checkin", "checkout"])) sendResponse(["error" => "Invalid attendance action: '{$action}'."]);

    $now   = date("Y-m-d H:i:s");
    $today = date("Y-m-d");
    $pdo   = dbconES();

    try {
        $pdo->beginTransaction();

        if ($classification === "GUEST") {
            performGuestCheckin($pdo, $sectionID, $now, $user);
        } elseif ($action === "checkin") {
            performCheckin($pdo, $idNumber, $sectionID, $now, $today, $user);
        } else {
            performCheckout($pdo, $idNumber, $sectionID, $now, $today);
        }

        $pdo->commit();

    } catch (Exception $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("[LibraryLogs] saveAttendance error: " . $exception->getMessage());
        sendResponse(["error" => "A database error occurred. Please try again."]);
    }
    sendResponse([
        "success" => true,
        "action"  => $action,
    ]);
}

function ShowKPI(): void
{
    $sectionID = intval($_POST["sectionID"] ?? 0);

    if (!$sectionID) {
        sendResponse(["error" => "Missing or invalid sectionID."]);
    }

    $pdo = dbconES();
    sendResponse(["success" => true, "data" => KPIData($pdo, $sectionID, date("Y-m-d"))]);
}

// ============================================================
// DISPATCH
// ============================================================

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

    case "getGuestCheckoutModal":
        GuestCheckoutModal();
        break;

    case "guestCheckout":
        GuestCheckout();
        break;

    case "getKPI":
        ShowKPI();
        break;

    default:
        sendResponse(["error" => "Unknown request: '{$request}'."]);
}