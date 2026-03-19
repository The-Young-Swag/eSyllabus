<?php
include "../../db/dbconnection.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST")
    sendResponse(["error" => "Invalid request method."]);

// ============================================================
// UTILITIES
// ============================================================

function sendResponse(array $payload): void { echo json_encode($payload); exit; }

// ============================================================
// HANDLERS
// ============================================================

function showAccess(): void {

    $users = execsqlSRS("
        SELECT UserID, EmpID, Name, EmailAddress
        FROM   Sys_UserAccount
        WHERE  IsDeleted = 0
        ORDER  BY Name
    ", "Search", []);

    $sections = execsqlSRS("
        SELECT SectionID, SectionName
        FROM   LibrarySection
        WHERE  IsActive = 1
        ORDER  BY SectionName
    ", "Search", []);

    $accessRows = execsqlSRS("
        SELECT UserID, SectionID
        FROM   LibraryAccess
        WHERE  IsActive = 1
    ", "Search", []);

    $userAccess = [];
    foreach ($accessRows as $row) {
        $userAccess[$row["UserID"]] = $row["SectionID"];
    }

    $html = "";

    foreach ($users as $i => $user) {
        $userID  = htmlspecialchars($user["UserID"]);
        $empID   = htmlspecialchars($user["EmpID"]);
        $name    = htmlspecialchars($user["Name"]);
        $email   = htmlspecialchars($user["EmailAddress"]);
        $current = $userAccess[$user["UserID"]] ?? "";

        $options = "<option value=''>-- None --</option>";
        foreach ($sections as $sec) {
            $secID    = $sec["SectionID"];
            $secName  = htmlspecialchars($sec["SectionName"]);
            $selected = ($secID == $current) ? "selected" : "";
            $options .= "<option value='$secID' $selected>$secName</option>";
        }

        $num = $i + 1;

        $html .= "
        <tr data-userid='$userID'>
            <td class='fw-semibold'>$num</td>
            <td class='fw-semibold'>$empID</td>
            <td>$name</td>
            <td class='text-muted'>$email</td>
            <td style='max-width:220px;'>
                <select class='form-control select-access rounded-pill' data-userid='$userID'>
                    $options
                </select>
            </td>
            <td>
                <button class='btn btn-success btn-sm rounded-pill btn-save-access' data-userid='$userID'>
                    <i class='fas fa-save me-1'></i>Save
                </button>
            </td>
        </tr>";
    }

    if (!$html)
        $html = "<tr><td colspan='6' class='text-center text-muted py-4'>No active users found.</td></tr>";

    sendResponse(["html" => $html]);
}

function assignAccess(): void {

    $userID    = $_POST["userID"]    ?? "";
    $sectionID = $_POST["sectionID"] ?? "";

    if ($userID === "") sendResponse(["error" => "Missing user ID."]);

    $existing  = execsqlSRS("SELECT AccessID FROM LibraryAccess WHERE UserID = ?", "Search", [$userID]);
    $hasRecord = !empty($existing);

    if ($sectionID === "") {

        // Remove access
        if ($hasRecord) {
            execsqlSRS(
                "UPDATE LibraryAccess SET SectionID = NULL, IsActive = 0 WHERE UserID = ?",
                "Update", [$userID]
            );
        } else {
            execsqlSRS(
                "INSERT INTO LibraryAccess (UserID, SectionID, AccessGrantedDate, IsActive) VALUES (?, NULL, GETDATE(), 0)",
                "Insert", [$userID]
            );
        }

    } elseif ($hasRecord) {
        // Reassign
        execsqlSRS(
            "UPDATE LibraryAccess SET SectionID = ?, AccessGrantedDate = GETDATE(), IsActive = 1 WHERE UserID = ?",
            "Update", [$sectionID, $userID]
        );

    } else {
        // New record
        execsqlSRS(
            "INSERT INTO LibraryAccess (UserID, SectionID, AccessGrantedDate, IsActive) VALUES (?, ?, GETDATE(), 1)",
            "Insert", [$userID, $sectionID]
        );
    }

    sendResponse(["success" => true]);
}

// ============================================================
// DISPATCH
// ============================================================

switch ($_POST["request"]) {
    case "getAccess":    showAccess();    break;
    case "assignAccess": assignAccess();  break;
    default: sendResponse(["error" => "Unknown request: '" . trim($_POST["request"] ?? "") . "'."]);
}