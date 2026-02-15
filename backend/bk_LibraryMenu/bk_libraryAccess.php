<?php
require_once "../../db/dbconnection.php";

$request = $_POST['request'] ?? '';

switch ($request) {

    case 'assignAccess':
        $userID = $_POST['userID'] ?? '';
        $sectionID = $_POST['sectionID'] ?? null; // can be null for no access

        if (empty($userID)) {
            echo "MISSING_USER";
            exit;
        }

        try {
            if (empty($sectionID)) {
                // Remove access if null
                execsqlSRS("DELETE FROM LibraryAccess WHERE UserID = ?", "Delete", [$userID]);
            } else {
                // Check if already exists
                $existing = execsqlSRS(
                    "SELECT COUNT(*) AS cnt FROM LibraryAccess WHERE UserID = ?",
                    "Search",
                    [$userID]
                );

                if ($existing[0]['cnt'] > 0) {
                    // Update SectionID and timestamp
                    execsqlSRS(
                        "UPDATE LibraryAccess SET SectionID = ?, AccessGrantedDate = GETDATE() WHERE UserID = ?",
                        "Update",
                        [$sectionID, $userID]
                    );
                } else {
                    // Insert new access
                    execsqlSRS(
                        "INSERT INTO LibraryAccess (UserID, SectionID, AccessGrantedDate) VALUES (?, ?, GETDATE())",
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
?>
