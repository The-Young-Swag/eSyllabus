<?php
require_once "../../db/dbconnection.php";

$request = $_POST['request'] ?? '';

switch ($request) {

    case 'assignAccess':

        $userID = $_POST['userID'] ?? '';
        $sectionID = $_POST['sectionID'] ?? '';

        if ($userID === '') {
            echo "MISSING_USER";
            exit;
        }

        try {

            // Check if row exists
            $existing = execsqlSRS(
                "SELECT AccessID FROM LibraryAccess WHERE UserID = ?",
                "Search",
                [$userID]
            );

            $hasRecord = !empty($existing);

            // ==========================
            // REMOVE ACCESS
            // ==========================
            if ($sectionID === '') {

                if ($hasRecord) {
                    execsqlSRS(
                        "UPDATE LibraryAccess
                         SET SectionID = NULL,
                             IsActive = 0
                         WHERE UserID = ?",
                        "Update",
                        [$userID]
                    );
                } else {
                    // Optional: insert inactive record
                    execsqlSRS(
                        "INSERT INTO LibraryAccess 
                         (UserID, SectionID, AccessGrantedDate, IsActive)
                         VALUES (?, NULL, GETDATE(), 0)",
                        "Insert",
                        [$userID]
                    );
                }

            } else {

                // ==========================
                // ASSIGN / REASSIGN ACCESS
                // ==========================
                if ($hasRecord) {

                    execsqlSRS(
                        "UPDATE LibraryAccess
                         SET SectionID = ?,
                             AccessGrantedDate = GETDATE(),
                             IsActive = 1
                         WHERE UserID = ?",
                        "Update",
                        [$sectionID, $userID]
                    );

                } else {

                    execsqlSRS(
                        "INSERT INTO LibraryAccess
                         (UserID, SectionID, AccessGrantedDate, IsActive)
                         VALUES (?, ?, GETDATE(), 1)",
                        "Insert",
                        [$userID, $sectionID]
                    );
                }
            }

            echo "SUCCESS";

        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage();
        }

        break;

    default:
        echo "INVALID_REQUEST";
        break;
}