<?php

include "../../db/dbconnection.php";

header("Content-Type: application/json");
date_default_timezone_set("Asia/Manila");

if ($_SERVER["REQUEST_METHOD"] !== "POST")
    sendResponse(["error"=>"Invalid request method."]);

function showSection() {

    $rows = execsqlSRS("
        SELECT SectionID, SectionCode, Description, SectionName, IsActive, DateCreated
        FROM LibrarySection
        ORDER BY SectionID
    ", "Search", []);

    $html = "";

    foreach ($rows as $r) {

        $id   = htmlspecialchars($r["SectionID"]);
        $code = htmlspecialchars($r["SectionCode"]);
        $name = htmlspecialchars($r["SectionName"]);
        $desc = htmlspecialchars($r["Description"]);
        $stat = htmlspecialchars($r["IsActive"]);
        $date = htmlspecialchars($r["DateCreated"]);

        $html .= "
        <tr data-id='$id'>
        
            <td>$id</td>
            <td>$code</td>
            <td>$desc</td>
            <td>$name</td>
        
            <td class='text-center'>
                <div class='form-check form-switch d-flex justify-content-center'>
                    <input
                        class='form-check-input toggleMenuStatus'
                        type='checkbox'
                        id='toggle_$id'
                        data-id='$id'
                        ".($stat ? "checked" : "").">
                </div>
            </td>
        
            <td>$date</td>
        
            <td class='text-nowrap d-flex gap-2'>
        
                <button
                    class='btn btn-sm btn-outline-primary rounded-pill edit-section'
                    data-id='$id'>
                    <i class='bi bi-pencil'></i> Edit
                </button>
        
                <button
                    class='btn btn-sm btn-outline-danger rounded-pill delete-section'
                    data-id='$id'>
                    <i class='bi bi-trash'></i> Delete
                </button>
        
            </td>
        
        </tr>";
    }

    echo json_encode(["html"=>$html]);
    exit;
}

// ============================================================
// DISPATCH
// ============================================================

$request = trim($_POST["request"] ?? "");

switch ($request) {

    case "getSection":
        showSection();
        break;

    default:
        sendResponse(["error"=>"Unknown request: '$request'."]);
}