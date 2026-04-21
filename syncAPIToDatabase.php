<?php

function _mapStudent(array $apiRecord): array
{
    $enrollmentStatus = strtoupper(trim($apiRecord["enrollment_status"] ?? ""));
    $isGuest = $enrollmentStatus === "NOT ENROLLED";
    $college = $apiRecord["college"] ?? "";
    $course = $apiRecord["course"]  ?? "";

    return [
        "id_number" => strtoupper(trim($apiRecord["id_number"] ?? "")),
        "name" => trim($apiRecord["name"] ?? ""),
        "sex" => $apiRecord["sex"] ?? null,
        "college" => $college,
        "course" => $course,
        "enrollment_status" => $isGuest ? "GUEST" : "STUDENT",
        "birthDate" => $apiRecord["birthDate"] ?? null,
    ];
}

function _mapEmployee(array $apiRecord): array
{
    return [
        "id_number" => strtoupper(trim(
            $apiRecord["employee_number"] ?? $apiRecord["id_number"] ?? ""
        )),
        "name" => trim($apiRecord["name"] ?? ""),
        "sex"  => $apiRecord["sex"] ?? null,
    ];
}

function normalizeRecords(array $responseData): array
{
    if (isset($responseData[0])) return $responseData;

    foreach (["data"] as $wrapperKey) {
        if (isset($responseData[$wrapperKey]) && is_array($responseData[$wrapperKey])) {
            return $responseData[$wrapperKey];
        }
    }
    return []; 
}

function saveStudentToDatabase(PDO $pdo, array $studentData): void
{
    if ($studentData["id_number"] === "" || $studentData["name"] === "") return;

    $stmt = $pdo->prepare("
        SELECT id, sex, college, course, enrollment_status, birthDate
        FROM studentData
        WHERE studNumber = :studNumber
          AND  name = :name
    ");
    $stmt->execute([
        ":studNumber" => $studentData["id_number"],
        ":name" => $studentData["name"],
    ]);
    $existingRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingRow) {

        $updates = [];
        $fieldsToCheck = ["sex", "college", "course", "enrollment_status", "birthDate"];

        foreach ($fieldsToCheck as $column) {
            $dbValue = $existingRow[$column]  ?? null;
            $apiValue = $studentData[$column]  ?? null;

            if ((string)$dbValue !== (string)$apiValue) {
                $updates[$column] = $apiValue;
            }
        }

        if (empty($updates)) return;

        $setColumns = array_map(fn($column) => "$column = :$column", array_keys($updates));

        $stmt = $pdo->prepare(
            "UPDATE studentData
             SET " . implode(", ", $setColumns) . "
             WHERE  studNumber = :studNumber
               AND  name = :name"
        );

        $updates[":studNumber"] = $studentData["id_number"];
        $updates[":name"] = $studentData["name"];
        $stmt->execute($updates);

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO studentData
                (studNumber, name, sex, college, course, enrollment_status, birthDate)
            VALUES
                (:studNumber, :name, :sex, :college, :course, :enrollmentStatus, :birthDate)
        ");
        $stmt->execute([
            ":studNumber" => $studentData["id_number"],
            ":name" => $studentData["name"],
            ":sex" => $studentData["sex"],
            ":college" => $studentData["college"],
            ":course" => $studentData["course"],
            ":enrollmentStatus" => $studentData["enrollment_status"],
            ":birthDate" => $studentData["birthDate"],
        ]);
    }
}

function saveEmployeeToDatabase(PDO $pdo, array $employeeData): void
{
    if ($employeeData["id_number"] === "") return;

    $stmt = $pdo->prepare("
        SELECT id, name, sex
        FROM employeeData
        WHERE  empNumber = :empNumber
    ");
    $stmt->execute([":empNumber" => $employeeData["id_number"]]);
    $existingRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingRow) {

        $updates = [];
        foreach (["name", "sex"] as $column) {
            $dbValue  = $existingRow[$column] ?? null;
            $apiValue = $employeeData[$column] ?? null;

            if ((string)$dbValue !== (string)$apiValue) {
                $updates[$column] = $apiValue;
            }
        }

        if (empty($updates)) return;

        $setColumns = array_map(fn($column) => "$column = :$column", array_keys($updates));

        $stmt = $pdo->prepare(
            "UPDATE employeeData
             SET " . implode(", ", $setColumns) . "
             WHERE  empNumber = :empNumber"
        );

        $updates[":empNumber"] = $employeeData["id_number"];
        $stmt->execute($updates);

    } else {
        $stmt = $pdo->prepare("
            INSERT INTO employeeData (empNumber, name, sex)
            VALUES (:empNumber, :name, :sex)
        ");
        $stmt->execute([
            ":empNumber" => $employeeData["id_number"],
            ":name" => $employeeData["name"],
            ":sex" => $employeeData["sex"],
        ]);
    }
}

function syncAPIToDatabase(string $studentJson, string $employeeJson, PDO $pdo): void
{
    $Students = json_decode($studentJson,  true);
    $Employees = json_decode($employeeJson, true);

    $studentRecords = is_array($Students) ? normalizeRecords($Students) : [];
    $employeeRecords = is_array($Employees) ? normalizeRecords($Employees) : [];

    if (empty($studentRecords) && empty($employeeRecords)) return;

    try {
        $pdo->beginTransaction();

        foreach ($studentRecords as $record) {
            saveStudentToDatabase($pdo, _mapStudent($record));
        }

        foreach ($employeeRecords as $record) {
            saveEmployeeToDatabase($pdo, _mapEmployee($record));
        }

        $pdo->commit();

    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("[syncAPIToDatabase] " . $exception->getMessage());
    }
}