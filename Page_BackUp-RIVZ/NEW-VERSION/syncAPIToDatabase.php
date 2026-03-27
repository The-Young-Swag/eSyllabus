<?php
/**
 * syncAPIToDatabase.php
 *
 * Bulk-syncs the cached API payload (students + employees) into the local
 * database so that resolveUserFromDatabase() has fresh data to fall back on
 * when the upstream API is unavailable.
 *
 * DESIGN NOTES
 * ─────────────────────────────────────────────────────────────────────────────
 * • Called once per page-load, right after $_SESSION["studentAPI"] /
 *   $_SESSION["employeeAPI"] are written (i.e. at the bottom of your loader).
 *
 * • Lookup key  →  students  : (studNumber, name)   — id_number alone is NOT
 *                               unique due to ARS duplicates, so name is added
 *                               as a discriminator.
 *               →  employees : (empNumber)           — employee numbers are
 *                               treated as unique.
 *
 * • Strategy    →  record exists  : compare every tracked field; UPDATE only
 *                                   when at least one value has changed.
 *               →  record missing : INSERT.
 *
 * • The function is intentionally silent (no output, no die()) so a sync
 *   failure never breaks the page load.  Errors are written to the PHP
 *   error log only.
 */

// ── Helpers reused from your existing backend ─────────────────────────────────

function _mapStudent(array $s): array
{
    $status  = strtoupper(trim($s["enrollment_status"] ?? ""));
    $isGuest = $status === "NOT ENROLLED";
    $college = $s["college"] ?? "";
    $course  = $s["course"]  ?? "";

    return [
        "id_number"          => strtoupper(trim($s["id_number"] ?? "")),
        "name"               => trim($s["name"]      ?? ""),
        "sex"                => $s["sex"]             ?? null,
        "college"            => $college,
        "course"             => $course,
        "enrollment_status"  => $isGuest ? "GUEST" : "STUDENT",
        "birthDate"          => $s["birthDate"]       ?? null,
        "agency_organization"=> $isGuest
                                    ? trim("{$college} - {$course}", " -")
                                    : "",
    ];
}

function _mapEmployee(array $e): array
{
    return [
        "id_number" => strtoupper(trim(
            $e["employee_number"] ?? $e["id_number"] ?? ""
        )),
        "name"      => trim($e["name"] ?? ""),
        "sex"       => $e["sex"] ?? null,
    ];
}

/**
 * Normalise the various wrapper shapes the API might return:
 *   [ {...}, {...} ]  /  { "data": [...] }  /  { "students": [...] }  / etc.
 */
function _normaliseRecords(array $json): array
{
    if (isset($json[0])) return $json;               // already a flat list

    foreach (["data","students","employees","records","items"] as $key) {
        if (isset($json[$key]) && is_array($json[$key])) {
            return $json[$key];
        }
    }
    return [];
}

// ── Core upsert functions ──────────────────────────────────────────────────────

/**
 * Upsert one student record.
 *
 * Lookup : studNumber + name  (handles id_number duplicates from ARS)
 * Tracked: name, sex, college, course, enrollment_status, birthDate
 */
function _upsertStudent(PDO $pdo, array $u): void
{
    if ($u["id_number"] === "" || $u["name"] === "") return;

    // ── Does this exact (id + name) pair already exist? ──────────────────────
    $stmt = $pdo->prepare("
        SELECT id, name, sex, college, course, enrollment_status, birthDate
        FROM   studentData
        WHERE  studNumber = :id
          AND  name       = :name
    ");
    $stmt->execute([":id" => $u["id_number"], ":name" => $u["name"]]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // ── UPDATE only changed fields ────────────────────────────────────────
        $diff = [];
        $tracked = ["sex","college","course","enrollment_status","birthDate"];

        foreach ($tracked as $col) {
            // Treat NULL == "" as "no change" to avoid spurious updates
            $dbVal  = $existing[$col]  ?? null;
            $apiVal = $u[$col]         ?? null;
            if ((string)$dbVal !== (string)$apiVal) {
                $diff[$col] = $apiVal;
            }
        }

        if (empty($diff)) return;   // nothing changed

        $setParts = array_map(fn($c) => "$c = :$c", array_keys($diff));
        $stmt = $pdo->prepare(
            "UPDATE studentData
             SET    " . implode(", ", $setParts) . "
             WHERE  studNumber = :id
               AND  name       = :name"
        );
        $diff[":id"]   = $u["id_number"];
        $diff[":name"] = $u["name"];
        $stmt->execute($diff);

    } else {
        // ── INSERT ────────────────────────────────────────────────────────────
        $stmt = $pdo->prepare("
            INSERT INTO studentData
                (studNumber, name, sex, college, course,
                 enrollment_status, birthDate)
            VALUES
                (:id, :name, :sex, :college, :course,
                 :status, :birthDate)
        ");
        $stmt->execute([
            ":id"      => $u["id_number"],
            ":name"    => $u["name"],
            ":sex"     => $u["sex"],
            ":college" => $u["college"],
            ":course"  => $u["course"],
            ":status"  => $u["enrollment_status"],
            ":birthDate"=> $u["birthDate"],
            
        ]);
    }
}

/**
 * Upsert one employee record.
 *
 * Lookup : empNumber  (treated as unique for employees)
 * Tracked: name, sex
 */
function _upsertEmployee(PDO $pdo, array $u): void
{
    if ($u["id_number"] === "") return;

    $stmt = $pdo->prepare("
        SELECT id, name, sex
        FROM   employeeData
        WHERE  empNumber = :id
    ");
    $stmt->execute([":id" => $u["id_number"]]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $diff = [];
        foreach (["name","sex"] as $col) {
            if ((string)($existing[$col] ?? "") !== (string)($u[$col] ?? "")) {
                $diff[$col] = $u[$col];
            }
        }
        if (empty($diff)) return;

        $setParts = array_map(fn($c) => "$c = :$c", array_keys($diff));
        $stmt = $pdo->prepare(
            "UPDATE employeeData
             SET    " . implode(", ", $setParts) . "
             WHERE  empNumber = :id"
        );
        $diff[":id"] = $u["id_number"];
        $stmt->execute($diff);

    } else {
        $stmt = $pdo->prepare("
            INSERT INTO employeeData (empNumber, name, sex)
            VALUES (:id, :name, :sex)
        ");
        $stmt->execute([
            ":id"   => $u["id_number"],
            ":name" => $u["name"],
            ":sex"  => $u["sex"],
        ]);
    }
}

// ── Public entry point ─────────────────────────────────────────────────────────

/**
 * syncAPIToDatabase()
 *
 * Call this right after writing $_SESSION["studentAPI"] / ["employeeAPI"].
 *
 * @param  string $studentJson   Raw JSON string from the student API
 * @param  string $employeeJson  Raw JSON string from the employee API
 * @param  PDO    $pdo           Your existing PDO connection
 * @return void                  Silent on success; errors go to error_log only
 */
function syncAPIToDatabase(string $studentJson, string $employeeJson, PDO $pdo): void
{
    // ── Decode ────────────────────────────────────────────────────────────────
    $studentData  = json_decode($studentJson,  true);
    $employeeData = json_decode($employeeJson, true);

    // If either API returned garbage / empty, skip that group silently
    $studentRecords  = is_array($studentData)  ? _normaliseRecords($studentData)  : [];
    $employeeRecords = is_array($employeeData) ? _normaliseRecords($employeeData) : [];

    if (empty($studentRecords) && empty($employeeRecords)) return;

    try {
        $pdo->beginTransaction();

        // ── Sync students ─────────────────────────────────────────────────────
        foreach ($studentRecords as $raw) {
            _upsertStudent($pdo, _mapStudent($raw));
        }

        // ── Sync employees ────────────────────────────────────────────────────
        foreach ($employeeRecords as $raw) {
            _upsertEmployee($pdo, _mapEmployee($raw));
        }

        $pdo->commit();

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("[syncAPIToDatabase] " . $e->getMessage());
        // Never propagate — sync failure must not break the page load
    }
}