<?php
include "../../db/dbconnection.php";

header("Content-Type: application/json");
date_default_timezone_set("Asia/Manila");

if ($_SERVER["REQUEST_METHOD"] !== "POST")
    sendResponse(["error" => "Invalid request method."]);

// UTILITIES

function sendResponse(array $payload): void { echo json_encode($payload); exit; }

function escHtml(mixed $value): string {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

// HANDLERS

function showSection(): void {

    $rows = execsqlSRS("
        SELECT SectionID, SectionCode, SectionName, Description, IsActive, DateCreated
        FROM   LibrarySection
        ORDER  BY SectionID
    ", "Search", []);

    $html = "";

    foreach ($rows as $row) {
        $id     = escHtml($row["SectionID"]);
        $code   = escHtml($row["SectionCode"]);
        $name   = escHtml($row["SectionName"]);
        $desc   = escHtml($row["Description"]);
        $active = $row["IsActive"] ? "checked" : "";
        $date   = escHtml($row["DateCreated"]);

        $html .= "
        <tr data-id='$id'>
            <td>$id</td>
            <td>$code</td>
            <td>$desc</td>
            <td>$name</td>
            <td class='text-center'>
                <div class='custom-control custom-switch'>
                    <input class='form-check-input toggle-section-status'
                           type='checkbox' data-id='$id' $active>
                </div>
            </td>
            <td>$date</td>
            <td class='text-nowrap'>
                <div class='d-flex gap-2'>
                    <button class='btn btn-sm btn-outline-primary rounded-pill btn-edit-section'
                            data-id='$id'>
                        <i class='bi bi-pencil'></i> Edit
                    </button>
                </div>
            </td>
        </tr>";
    }

    if (!$html)
        $html = "<tr><td colspan='7' class='text-center text-muted py-4'>No sections found.</td></tr>";

    sendResponse(["html" => $html]);
}

function getSectionModal(): void {

    $id   = (int)($_POST["sectionID"] ?? 0);
    $mode = $id ? "edit" : "add";

    $code = $name = $desc = "";

    if ($mode === "edit") {
        $rows = execsqlSRS("
            SELECT SectionCode, SectionName, Description
            FROM   LibrarySection
            WHERE  SectionID = ?
        ", "Search", [$id]);

        if (empty($rows)) sendResponse(["error" => "Section not found."]);

        $code = escHtml($rows[0]["SectionCode"]);
        $name = escHtml($rows[0]["SectionName"]);
        $desc = escHtml($rows[0]["Description"]);
    }

    $title    = $mode === "edit" ? "Edit Section" : "Add Section";
    $btnLabel = $mode === "edit" ? "Save Changes" : "Add Section";
    $btnIcon  = $mode === "edit" ? "bi-check-lg"  : "bi-plus-lg";

    $body = "
    <input type='hidden' id='modalSectionID' value='$id'>
    <div class='mb-3'>
        <label class='form-label fw-semibold'>Section Code</label>
        <input type='text' class='form-control' id='modalSectionCode'
               value='$code' placeholder='e.g. REF, CIR'>
    </div>
    <div class='mb-3'>
        <label class='form-label fw-semibold'>Section Name</label>
        <input type='text' class='form-control' id='modalSectionName'
               value='$name' placeholder='e.g. Reference Section'>
    </div>
    <div class='mb-3'>
        <label class='form-label fw-semibold'>Description</label>
        <textarea class='form-control' id='modalSectionDesc'
                  rows='3' placeholder='Brief description'>$desc</textarea>
    </div>";

    $footer = "
    <button type='button' class='btn btn-light border rounded-pill px-4'
            data-dismiss='modal' data-bs-dismiss='modal'>Cancel</button>
    <button type='button' class='btn btn-success rounded-pill px-4 fw-semibold'
            id='confirmSaveSection'>
        <i class='bi $btnIcon me-1'></i>$btnLabel
    </button>";

    sendResponse(["success" => true, "title" => $title, "body" => $body, "footer" => $footer]);
}

function getDeleteModal(): void {

    $id   = (int)($_POST["sectionID"]  ?? 0);
    $name = trim($_POST["sectionName"] ?? "");
    if (!$id) sendResponse(["error" => "Missing section ID."]);

    $safeName = escHtml($name);

    $body = "
    <div class='text-center py-2'>
        <i class='bi bi-trash3 fs-1 text-danger d-block mb-3'></i>
        <p class='mb-1'>Delete <strong>$safeName</strong>?</p>
        <p class='text-muted small mb-0'>This cannot be undone.</p>
    </div>";

    $footer = "
    <button type='button' class='btn btn-light border rounded-pill px-4'
            data-dismiss='modal' data-bs-dismiss='modal'>Cancel</button>
    <button type='button' class='btn btn-danger rounded-pill px-4'
            id='confirmDeleteSection' data-id='$id'>
        <i class='bi bi-trash me-1'></i>Delete
    </button>";

    sendResponse(["success" => true, "title" => "Delete Section", "body" => $body, "footer" => $footer]);
}

function saveSection(): void {
    $id   = (int)($_POST["sectionID"]  ?? 0);
    $code = trim($_POST["sectionCode"] ?? "");
    $name = trim($_POST["sectionName"] ?? "");
    $desc = trim($_POST["description"] ?? "");

    if (!$code || !$name) sendResponse(["error" => "Section code and name are required."]);

    if ($id) {
        execsqlSRS("
            UPDATE LibrarySection
            SET    SectionCode = ?, SectionName = ?, Description = ?
            WHERE  SectionID   = ?
        ", "Update", [$code, $name, $desc, $id]);
    } else {
        execsqlSRS("
            INSERT INTO LibrarySection (SectionCode, SectionName, Description, IsActive, DateCreated)
            VALUES (?, ?, ?, 1, GETDATE())
        ", "Insert", [$code, $name, $desc]);
    }
    sendResponse(["success" => true]);
}


function toggleStatus(): void {
    $id     = (int)($_POST["sectionID"] ?? 0);
    $active = (int)($_POST["isActive"]  ?? 0);
    if (!$id) sendResponse(["error" => "Missing section ID."]);

    execsqlSRS("
        UPDATE LibrarySection SET IsActive = ? WHERE SectionID = ?
    ", "Update", [$active, $id]);

    sendResponse(["success" => true]);
}

// DISPATCH

switch ($_POST["request"]) {
    case "getSection":      showSection();     break;
    case "getSectionModal": getSectionModal(); break;
    case "getDeleteModal":  getDeleteModal();  break;
    case "saveSection":     saveSection();     break;
    case "toggleStatus":    toggleStatus();    break;
    default: sendResponse(["error" => "Unknown request: '" . trim($_POST["request"] ?? "") . "'."]);
}