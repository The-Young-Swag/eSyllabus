<?php
include __DIR__ . "/../../db/dbconnection.php";

header('Content-Type: application/json');

function sendJson($data)
{
    echo json_encode($data);
    exit;
}

function sendError($message, $status = 400)
{
    http_response_code($status);
    sendJson(['success' => false, 'message' => $message]);
}

$request = $_POST['request'] ?? '';


switch ($request){
        case "getLibraries":
            $sql = "
                SELECT SectionID, SectionCode, SectionName
                FROM LibrarySection
                WHERE IsActive = 1
                ORDER BY SectionName
            ";
            $libraries = execsqlSRS($sql, "Search", []);
            sendJson($libraries);
            break;


			

}

?>