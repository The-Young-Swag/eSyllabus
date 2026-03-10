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


/**
 * Validates that an ID number only contains letters and digits.
 * Rejects anything suspicious before it touches any data source.
 */
function validateIdFormat(string $id): bool
{
    return (bool) preg_match('/^[A-Z0-9-]+$/i', $id);
}


/**
 * Returns the date-range boundaries for "today" as two datetime strings.
 * Used instead of CAST()/CONVERT() on the column so SQL can use an index.
 *
 *   checkin_time >= $start  AND  checkin_time < $end
 *
 * Returns: [ "YYYY-MM-DD 00:00:00", "YYYY-MM-DD 00:00:00" (next day) ]
 */
function getTodayRange(string $today): array
{
    $start = $today . " 00:00:00";
    $end   = date("Y-m-d", strtotime("+1 day", strtotime($today))) . " 00:00:00";
    return [$start, $end];
}







//  DATA SOURCE LOADERS


// ====================================================================
// CONFIGURATION — the only section you ever need to touch
// ====================================================================

// Step 1: Flip to false when your live API is ready
define("USE_LOCAL_DATA", true);

// API endpoints — update these when your real URLs are known
define("API_ENDPOINTS", [
    "students"  => "https://your-school-api.edu/api/students",
    "employees" => "https://your-school-api.edu/api/employees",
]);

// ID filter param each endpoint expects  (GET /api/students?id_number=xxx)
define("API_ID_PARAMS", [
    "students"  => "id_number",
    "employees" => "employee_number",
]);

// ====================================================================


/**
 * Resolves a user by ID — the single entry point for all user lookups.
 *
 * This is the core architectural principle:
 *   Before: load entire dataset → build index → find person  O(n) + memory
 *   Now:    ask for this person → get this person             O(1) always
 *
 * Returns an array of matched records (more than one = duplicate IDs).
 * Returns an empty array if no match is found.
 *
 * Routing (automatic):
 *   USE_LOCAL_DATA = true  → searches local JSON file
 *   USE_LOCAL_DATA = false → fetches exactly this person from the API
 */
function resolveUserById(string $idNumber): array
{
    if (USE_LOCAL_DATA) {
        return resolveFromLocalJSON($idNumber);
    }

    return resolveFromAPI($idNumber);
}


/**
 * Searches the local JSON test file for the given ID.
 * Used during development — no API needed, no network calls.
 */
function resolveFromLocalJSON(string $idNumber): array
{
    $isEmployee = str_starts_with(strtoupper($idNumber), "TAU");
    $source     = $isEmployee ? "employees" : "students";
    $filePath   = __DIR__ . "/../../API_requests/{$source}.json";

    if (!file_exists($filePath)) {
        return [];
    }

    $decoded = json_decode(file_get_contents($filePath), true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return [];
    }

    $records = extractRecordsFromPayload($decoded);
    $matches = [];

    foreach ($records as $record) {
        $id = $record["employee_number"] ?? $record["id_number"] ?? null;
        if ($id === $idNumber) {
            $matches[] = $isEmployee
                ? mapEmployeeToUserRecord($record)
                : mapStudentToUserRecord($record);
        }
    }

    return $matches;
}


/**
 * Fetches exactly this one person from the live API.
 *
 *   GET /api/students?id_number=2022100114
 *   GET /api/employees?employee_number=TAU0001
 *
 * O(1) — no dataset loading, no indexing, no iteration on our side.
 * No caching — each scan fetches exactly one record directly from the API.
 */
function resolveFromAPI(string $idNumber): array
{
    // ── Fetch from API ───────────────────────────────────────────────────────
    $isEmployee = str_starts_with(strtoupper($idNumber), "TAU");
    $source     = $isEmployee ? "employees" : "students";
    $token      = $_ENV["SCHOOL_API_TOKEN"] ?? null;

    if (!$token) {
        return [];
    }

    $url      = API_ENDPOINTS[$source] . "?" . http_build_query([API_ID_PARAMS[$source] => $idNumber]);
    $response = apiRequest($url, $token);

    if ($response === null) {
        return [];
    }

    $records = extractRecordsFromPayload($response);
    $matches = [];

    foreach ($records as $record) {
        $matches[] = $isEmployee
            ? mapEmployeeToUserRecord($record)
            : mapStudentToUserRecord($record);
    }

    return $matches;
}


/**
 * Shared cURL helper. Returns the decoded JSON array, or null on any failure.
 * Both resolvers use this — cURL setup lives in exactly one place.
 */
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

    $responseBody = curl_exec($curl);
    $httpStatus   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($responseBody === false || $httpStatus !== 200) {
        return null;
    }

    $decoded = json_decode($responseBody, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return null;
    }

    return $decoded;
}


/**
 * Handles all JSON wrapper shapes so neither resolver has to repeat this.
 *
 *   { "data": [ ... ] }      ← your current API format
 *   { "students": [ ... ] }
 *   [ ... ]                  ← flat array, no wrapper
 */
function extractRecordsFromPayload(array $payload): array
{
    if (isset($payload[0])) {
        return $payload;
    }

    foreach (["data", "employees", "students", "records", "items"] as $key) {
        if (isset($payload[$key]) && is_array($payload[$key])) {
            return $payload[$key];
        }
    }

    return [];
}



//  USER RECORD MAPPERS


function mapStudentToUserRecord(array $student): array
{
    return [
        "id_number"      => $student["id_number"],
        "name"           => $student["name"],
        "sex"            => $student["sex"]     ?? null,
        "college"        => $student["college"] ?? null,
        "course"         => $student["course"]  ?? null,
        "classification" => "STUDENT",
        "secretKey"      => $student["birthDate"] ?? null,
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



//  HTML GENERATORS
//  All modal HTML is built here on the server.
//  JS receives the HTML string and injects it — no HTML construction in JS.


/**
 * Builds the attendance confirmation modal body and footer.
 *
 * Called by handleBuildAttendanceModal() after JS has already:
 *   - validated the user
 *   - checked their status today
 *   - determined the action (checkin / checkout / switch)
 *
 * JS stays in charge of state and routing.
 * PHP stays in charge of rendering.
 *
 * $message may contain only <strong> and <em> tags (strip_tags enforced).
 */
function buildAttendanceModalHTML(
    array  $user,
    string $color,
    string $icon,
    string $btnText,
    string $message,
    string $libraryName
): array {
    $isEmployee = $user["classification"] === "EMPLOYEE";
    $isGuest    = $user["classification"] === "GUEST";

    $id      = htmlspecialchars($user["id_number"]      ?? "");
    $name    = htmlspecialchars($user["name"]           ?? "");
    $sex     = htmlspecialchars($user["sex"]            ?? "N/A");
    $type    = htmlspecialchars($user["classification"] ?? "");
    $lib     = htmlspecialchars($libraryName);

    // Allow only safe formatting tags — strip everything else
    $message = strip_tags($message, "<strong><em>");

$rows = "";

if (!$isGuest) {
    $rows .= "
        <div class='row mb-2'>
            <div class='col-5 fw-semibold'>ID</div>
            <div class='col-7'>{$id}</div>
        </div>
    ";
}

$rows .= "
    <div class='row mb-2'><div class='col-5 fw-semibold'>Name</div><div class='col-7'>{$name}</div></div>
    <div class='row mb-2'><div class='col-5 fw-semibold'>Sex</div><div class='col-7'>{$sex}</div></div>
    <div class='row mb-2'><div class='col-5 fw-semibold'>Type</div><div class='col-7'>{$type}</div></div>
";

    if (!$isEmployee && !$isGuest) {
        $college = htmlspecialchars($user["college"] ?? "N/A");
        $course  = htmlspecialchars($user["course"]  ?? "N/A");
        $rows .= "
            <div class='row mb-2'><div class='col-5 fw-semibold'>College</div><div class='col-7'>{$college}</div></div>
            <div class='row mb-2'><div class='col-5 fw-semibold'>Course</div><div class='col-7'>{$course}</div></div>
        ";
    }
	
	if ($isGuest) {
    $agency = htmlspecialchars($user["agency_organization"] ?? "N/A");

    $rows .= "
        <div class='row mb-2'>
            <div class='col-5 fw-semibold'>Agency</div>
            <div class='col-7'>{$agency}</div>
        </div>
    ";
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
            <div class='text-center fw-bold text-primary'>Library: {$lib}</div>
        </div>
    ";

    $footer = "
        <button type='button' class='btn btn-outline-secondary' data-bs-dismiss='modal'>Cancel</button>
        <button type='button' class='btn btn-{$color}' id='confirmAttendance'>
            <i class='fas {$icon} me-1'></i>{$btnText}
        </button>
    ";

    return ["body" => $body, "footer" => $footer];
}

function handleBuildGuestModal(): void
{
    $lib = htmlspecialchars(trim($_POST["libraryName"] ?? ""));

    $body = "
        <div class='p-3' style='background:#f0fdf9;border:1px solid #d1fae5;border-radius:12px;'>

            <div class='d-flex align-items-center gap-3 pb-3 mb-3 border-bottom'>
                <div class='d-flex align-items-center justify-content-center flex-shrink-0'
                     style='width:40px;height:40px;border-radius:10px;background:#d1fae5;color:#047857;'>
                    <i class='fas fa-book-open' style='font-size:1rem;line-height:1'></i>
                </div>

                <div class='lh-sm'>
                    <div class='text-uppercase fw-semibold text-muted'
                         style='font-size:.65rem;letter-spacing:.08em;'>
                        Library
                    </div>

                    <div class='fw-bold' style='font-size:.95rem;color:#064e3b;'>
                        {$lib}
                    </div>
                </div>
            </div>

            <div class='mb-3'>
                <label class='form-label small text-uppercase fw-semibold text-muted mb-1'>
                    Full Name <span class='text-danger'>*</span>
                </label>

                <input type='text'
                       id='guestName'
                       class='form-control'
                       placeholder='Enter full name'
                       autocomplete='off'>
            </div>

            <!-- Sex -->
            <div class='mb-3'>
                <label class='d-block fw-bold text-uppercase mb-1'
                       style='font-size:.65rem;letter-spacing:.09em;color:#3d8a6e;'>
                    Sex <span class='text-danger'>*</span>
                </label>

                <select id='guestSex'
                        class='form-select'
                        style='border-color:#a7f3d0;border-radius:8px;font-size:.9rem;'>

                    <option value=''>— Select —</option>
                    <option value='Male'>Male</option>
                    <option value='Female'>Female</option>

                </select>
            </div>

            <div class='row g-2'>

                <div class='col-4'>
                    <label class='form-label small text-uppercase fw-semibold text-muted mb-1'>
                        Type
                    </label>

                    <input type='text'
                           class='form-control text-success fw-semibold bg-light'
                           value='GUEST'
                           readonly>
                </div>

                <div class='col-8'>
                    <label class='form-label small text-uppercase fw-semibold text-muted mb-1'>
                        Agency / Organization <span class='text-danger'>*</span>
                    </label>

                    <input type='text'
                           id='guestAgency'
                           class='form-control'
                           placeholder='Enter agency or organization'
                           autocomplete='off'>
                </div>

            </div>

        </div>
    ";

    $footer = "
        <button type='button'
                class='btn btn-light border rounded-pill px-4'
                data-bs-dismiss='modal'>
            Cancel
        </button>

        <button type='button'
                class='btn btn-success rounded-pill px-4 fw-semibold'
                id='confirmGuestCheckIn'>
            <i class='fas fa-sign-in-alt me-2'></i>
            Check In
        </button>
    ";

    sendResponse([
        "success" => true,
        "body"    => $body,
        "footer"  => $footer
    ]);
}


/**
 * Builds the duplicate ID verification modal body.
 * Shown when multiple students share the same ID number.
 */
function buildDuplicateModalHTML(): string
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
        <div id='verifiedStudentContainer' class='mt-3' style='display:none;'></div>
    ";
}



//  KPI BUILDER


/**
 * Builds the KPI payload for a library section on a given date.
 *
 * Uses checkin_time >= start AND < end instead of CAST()/CONVERT() on the
 * column — this allows SQL Server to use an index on checkin_time if one exists.
 *
 * Returns:
 *   totalToday      – Total check-ins for the section today
 *   currentlyInside – Users with no checkout_time (still present)
 *   topColleges     – Top 3 colleges by visit count
 *   topCourses      – Top 3 courses by visit count
 */
function buildKPIData(PDO $pdo, int $sectionID, string $today): array
{
    [$start, $end] = getTodayRange($today);

    // ── Totals ───────────────────────────────────────────────────────────────
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

    // ── Top colleges ─────────────────────────────────────────────────────────
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

    // ── Top courses ──────────────────────────────────────────────────────────
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



//  ATTENDANCE HELPERS


/**
 * Performs the full check-in sequence:
 *   1. Skips silently if the user is already checked in at this section today.
 *   2. Auto-closes any open session in a DIFFERENT section (switch scenario).
 *   3. Inserts a new log entry for this section.
 *
 * Uses time range instead of CAST() for index-friendly queries.
 */
function performCheckin(PDO $pdo, string $idNumber, int $sectionID, string $now, string $today, array $user): void
{
    [$start, $end] = getTodayRange($today);

    // Already here — nothing to do
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) FROM Library_logs
        WHERE  id_number     = ?
          AND  library       = ?
          AND  checkout_time IS NULL
          AND  checkin_time >= ?
          AND  checkin_time  < ?
    ");
    $stmtCheck->execute([$idNumber, $sectionID, $start, $end]);
    if (intval($stmtCheck->fetchColumn()) > 0) {
        return;
    }

    // Auto-checkout from any other section (switch scenario)
    $pdo->prepare("
        UPDATE Library_logs
        SET    checkout_time = ?
        WHERE  id_number     = ?
          AND  checkout_time IS NULL
          AND  checkin_time >= ?
          AND  checkin_time  < ?
          AND  library <> ?
    ")->execute([$now, $idNumber, $start, $end, $sectionID]);

    // Insert new check-in
    $pdo->prepare("
        INSERT INTO Library_logs
        (id_number, name, classification, college, course, library, checkin_time, sex, agency_organization)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
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

/**
 * Closes the user's active session in the specified section.
 * Uses time range instead of CAST() for index-friendly queries.
 */
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



//  HANDLERS


function handleGetLibraries(): void
{
    $userID = intval($_POST["userID"] ?? 0);

    if (!$userID) {
        sendResponse(["error" => "Missing or invalid userID."]);
    }

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

    // Reject IDs with suspicious characters before touching any data source
    if (!validateIdFormat($idNumber)) {
        sendResponse(["error" => "Invalid ID format."]);
    }

    // Fetch exactly this person — no dataset loading, no indexing
    $matches = resolveUserById($idNumber);

    // ── No match → walk-in guest (JS handles this path) ──────────────────────
    if (count($matches) === 0) {
        sendResponse(["error" => "User not found."]);
    }

    // ── Unique match ─────────────────────────────────────────────────────────
    if (count($matches) === 1) {
        sendResponse(["success" => true, "data" => $matches[0]]);
    }

    // ── Duplicate ID → return duplicate modal HTML for injection ──────────────
    sendResponse([
        "duplicate" => true,
        "matches"   => $matches,
        "modalHTML" => buildDuplicateModalHTML(),
    ]);
}

//GUEST CHECK IN
function handleGuestCheckin(): void
{
    $name      = trim($_POST["name"] ?? "");
    $sex       = trim($_POST["sex"] ?? "");
    $agency    = trim($_POST["agency"] ?? "");
    $sectionID = intval($_POST["sectionID"] ?? 0);

    if (!$name || !$sex || !$agency || !$sectionID) {
        sendResponse(["error" => "Missing guest information."]);
    }

    $pdo = dbconES();
    $now = date("Y-m-d H:i:s");

    $stmt = $pdo->prepare("
INSERT INTO Library_logs
(id_number, name, classification, college, course, library, checkin_time, sex, agency_organization)
        VALUES (NULL, ?, 'GUEST', ?, ?, ?, ?)
    ");

    $stmt->execute([
        $name,
        $sex,
        $agency,
        $sectionID,
        $now
    ]);

    sendResponse(["success" => true]);
}


// ────────────────────────────────────────────────────────────────
// CHECK-IN / CHECK-OUT ALGORITHM
/*
    Purpose:
    --------
    Determines whether a user is currently checked in at a library section 
    and what action (check-in, check-out, or switch) should be performed 
    when the user attempts to log attendance.

    Logical Flow:
    -------------
    1. Input:
       - `id_number` : The unique identifier of the user (student or employee).
       - `currentLibraryID` : The library section where the scan is occurring.
       - `today` : Current date used to isolate records for this day only.

    2. Define "today" range:
       - Compute `start` as YYYY-MM-DD 00:00:00.
       - Compute `end` as the same time on the following day.
       - This allows the query to use index-friendly range conditions.

    3. Database Query:
       - Select records from `Library_logs` where:
           a. `id_number` matches the user.
           b. `checkout_time IS NULL` → meaning the user has not checked out yet.
           c. `checkin_time >= start AND checkin_time < end` → only today's check-ins.
       - Join `LibrarySection` to get human-readable section names.
       - Order by `checkin_time DESC` to get the **most recent active session**.
       - Fetch only the first record (TOP 1) to minimize data processing.

    4. Decision Logic:
       - If a row is returned:
           - User is currently checked in.
           - If the library matches `currentLibraryID` → action is CHECK-OUT.
           - If the library differs → action is SWITCH (auto-checkout previous and check-in here).
       - If no row is returned:
           - User is not checked in today → action is CHECK-IN.

    5. Performance Considerations:
       - The algorithm is designed to be fast and scalable:
           a. Uses a **time range instead of functions like CAST/CONVERT**, allowing SQL Server/MySQL to leverage indexes.
           b. Indexed on `(id_number, checkin_time, checkout_time)` → ensures lookup is near O(1) even for large datasets.
           c. Uses `TOP 1` / `LIMIT 1` → minimizes memory and network overhead.
           d. Minimal PHP-side processing; database does the filtering and ordering.

    6. Result:
       - Returns either:
           a. `checkedIn: true` + `sectionID` and `sectionName` for active session.
           b. `checkedIn: false` if no active check-in exists today.
       - JS then decides which modal to show (Check-In, Check-Out, or Switch) based on this status.

    Summary:
    --------
    This is a **real-time attendance check algorithm** optimized for large-scale library systems. 
    It balances correctness (handling same-library check-outs and multi-library switches) 
    with performance (index-friendly queries, single-row fetch, and minimal PHP logic).

*/

function handleCheckStatusToday(): void
{
    $idNumber = trim($_POST["idNumber"] ?? "");

    if (!$idNumber) {
        sendResponse(["error" => "Identification number is required."]);
    }

    $today         = date("Y-m-d");
    [$start, $end] = getTodayRange($today);
    $pdo           = dbconES();

    // Uses time range — index-friendly, no CONVERT() on column
    $stmt = $pdo->prepare("
        SELECT TOP 1 ll.library, ls.SectionName
        FROM   Library_logs  ll
        LEFT   JOIN LibrarySection ls ON ls.SectionID = ll.library
        WHERE  ll.id_number      = ?
          AND  ll.checkout_time  IS NULL
          AND  ll.checkin_time  >= ?
          AND  ll.checkin_time   < ?
        ORDER  BY ll.checkin_time DESC
    ");
    $stmt->execute([$idNumber, $start, $end]);
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


/**
 * Called by JS after it has determined the action (checkin/checkout/switch)
 * and resolved the user. JS sends all the pieces; PHP renders the modal HTML.
 *
 * This keeps JS in charge of state/routing and PHP in charge of rendering.
 *
 * POST params:
 *   user        – JSON-encoded user record
 *   color       – Bootstrap color name (success / danger / warning)
 *   icon        – FontAwesome class (fa-sign-in-alt / fa-sign-out-alt / fa-random)
 *   btnText     – Button label
 *   message     – Status message (only <strong><em> tags allowed)
 *   libraryName – Current library section name for display
 */
function handleBuildAttendanceModal(): void
{
    $userJSON    = trim($_POST["user"]        ?? "{}");
    $color       = trim($_POST["color"]       ?? "success");
    $icon        = trim($_POST["icon"]        ?? "fa-sign-in-alt");
    $btnText     = trim($_POST["btnText"]     ?? "Check In");
    $message     = trim($_POST["message"]     ?? "");
    $libraryName = trim($_POST["libraryName"] ?? "");

    $user = json_decode($userJSON, true);

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


function handleSaveAttendance(): void
{
    $idNumber       = trim($_POST["idNumber"]            ?? "");
    $sectionID      = intval($_POST["sectionID"]         ?? 0);
    $action         = trim($_POST["action"]              ?? "");
    $classification = trim($_POST["classification"]      ?? "STUDENT");
    $name           = trim($_POST["name"]                ?? "");
    $college        = trim($_POST["college"]             ?? "");
    $course         = trim($_POST["course"]              ?? "");
    $sex            = trim($_POST["sex"]                 ?? "");
    $agencyOrg      = trim($_POST["agency_organization"] ?? "");

    // Guests have no id_number — only non-guests require it
    if ($classification !== "GUEST" && !$idNumber) {
        sendResponse(["error" => "Missing required attendance data."]);
    }

    if (!$sectionID || !$action) {
        sendResponse(["error" => "Missing required attendance data."]);
    }

    if ($idNumber && !validateIdFormat($idNumber)) {
        sendResponse(["error" => "Invalid ID format."]);
    }

    if (!in_array($action, ["checkin", "checkout"])) {
        sendResponse(["error" => "Invalid attendance action: '{$action}'."]);
    }

    $now   = date("Y-m-d H:i:s");
    $today = date("Y-m-d");
    $pdo   = dbconES();

    $user = [
        "name"               => $name,
        "classification"     => $classification,
        "college"            => $college,
        "course"             => $course,
        "sex"                => $sex,
        "agency_organization"=> $agencyOrg,
    ];

    try {
        $pdo->beginTransaction();

        if ($classification === "GUEST") {
            // Guests: always a fresh INSERT — no duplicate check, no auto-checkout
            performGuestCheckin($pdo, $sectionID, $now, $user);
        } elseif ($action === "checkin") {
            performCheckin($pdo, $idNumber, $sectionID, $now, $today, $user);
        } else {
            performCheckout($pdo, $idNumber, $sectionID, $now, $today);
        }

        $pdo->commit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("[LibraryLogs] saveAttendance error: " . $e->getMessage());
        sendResponse(["error" => "A database error occurred. Please try again."]);
    }

    $kpiData = buildKPIData($pdo, $sectionID, $today);
    sendResponse(["success" => true, "action" => $action, "kpi" => $kpiData]);
}

/**
 * Inserts a new guest visit. Every guest scan is a fresh record.
 * id_number is NULL — guests have no institutional ID.
 */
function performGuestCheckin(PDO $pdo, int $sectionID, string $now, array $user): void
{
    $pdo->prepare("
        INSERT INTO Library_logs
        (id_number, name, classification, college, course, library, checkin_time, sex, agency_organization)
        VALUES ('0', ?, 'GUEST', '', '', ?, ?, ?, ?)
    ")->execute([
        $user["name"],
        $sectionID,
        $now,
        $user["sex"],
        $user["agency_organization"],
    ]);
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


//  DISPATCH

$request = trim($_POST["request"] ?? "");

switch ($request) {
    case "getLibraries":
        handleGetLibraries();
        break;

    case "validateUser":
        handleValidateUser();
        break;

    case "checkStatusToday":
        handleCheckStatusToday();
        break;

    case "buildAttendanceModal":
        handleBuildAttendanceModal();
        break;

    case "buildGuestModal":
        handleBuildGuestModal();
        break;

    case "saveAttendance":
        handleSaveAttendance();
        break;

    case "getKPI":
        handleGetKPI();
        break;

    default:
        sendResponse(["error" => "Unknown request: '{$request}'."]);
        break;
}