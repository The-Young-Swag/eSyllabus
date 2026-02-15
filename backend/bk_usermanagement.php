<?php
include "../db/dbconnection.php";

$request = $_POST["request"] ?? "";

switch ($request) {
case "addUser":
    // Your existing add user code with libAccess added
    $empID = trim($_POST["empID"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $roleID = trim($_POST["roleID"] ?? "");
    $officeID = trim($_POST["officeID"] ?? "");
    $positionID = trim($_POST["positionID"] ?? "");
    $changePass = isset($_POST["changePass"]) ? 1 : 0; // Add this line
    
    if (empty($empID) || empty($name) || empty($roleID)) {
        echo "MISSING_REQUIRED_FIELDS";
        exit;
    }
    
    $check = execsqlSRS("SELECT COUNT(*) as count FROM Sys_UserAccount WHERE EmpID = ?", "Search", [$empID]);
    if ($check[0]['count'] > 0) {
        echo "DUPLICATE";
        exit;
    }
    
    if (empty($email)) {
        $email = $empID . '@example.com';
    }
    
    $password = $empID;
    
    try {
        // Modified SQL to include libAccess and ChangePass
$sqlUser = "INSERT INTO Sys_UserAccount (EmpID, EmailAddress, Password, Name, RID, Office_id, Position_id, IsActive, AccountRegDate, IsDeleted, ChangePass) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, GETDATE(), 0, ?)";
execsqlSRS($sqlUser, "Insert", [$empID, $email, $password, $name, $roleID, $officeID, $positionID, $changePass]);

        
        if (!empty($officeID) && !empty($positionID)) {
            $sqlStaff = "INSERT INTO tbl_OfficeStaff (OfficeID, EmpID, PositionID, Plantilla) 
                         VALUES (?, ?, ?, '')";
            
            execsqlSRS($sqlStaff, "Insert", [$officeID, $empID, $positionID]);
        }
        
        echo "SUCCESS";
    } catch (Exception $e) {
        echo "INSERT_ERROR: " . $e->getMessage();
    }
    break;
        
   case "updateUser":
    // Your existing update code with libAccess added
    $userID = $_POST["userID"] ?? "";
    $empID = trim($_POST["empID"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $roleID = trim($_POST["roleID"] ?? "");
    $officeID = trim($_POST["officeID"] ?? "");
    $positionID = trim($_POST["positionID"] ?? "");
    $isActive = $_POST["isActive"] ?? 0;
    $allOfficeAccess = $_POST["allOfficeAccess"] ?? 0;
    $changePass = $_POST["changePass"] ?? 0;
    
    if (empty($userID) || empty($empID) || empty($name) || empty($roleID)) {
        echo "MISSING_REQUIRED_FIELDS";
        exit;
    }
    
    try {
        // Modified SQL to include libAccess
$sqlUser = "UPDATE Sys_UserAccount 
            SET EmpID = ?, EmailAddress = ?, Name = ?, RID = ?, Office_id = ?, Position_id = ?, 
                IsActive = ?, AllOfficeAcess = ?, ChangePass = ?
            WHERE UserID = ?";
execsqlSRS($sqlUser, "Update", [$empID, $email, $name, $roleID, $officeID, $positionID, $isActive, $allOfficeAccess, $changePass, $userID]);

        
        $checkStaff = execsqlSRS("SELECT COUNT(*) as count FROM tbl_OfficeStaff WHERE EmpID = ?", "Search", [$empID]);
        
        if ($checkStaff[0]['count'] > 0) {
            $sqlStaff = "UPDATE tbl_OfficeStaff 
                         SET OfficeID = ?, PositionID = ?
                         WHERE EmpID = ?";
            
            execsqlSRS($sqlStaff, "Update", [$officeID, $positionID, $empID]);
        } else {
            if (!empty($officeID) && !empty($positionID)) {
                $sqlStaff = "INSERT INTO tbl_OfficeStaff (OfficeID, EmpID, PositionID, Plantilla) 
                             VALUES (?, ?, ?, '')";
                
                execsqlSRS($sqlStaff, "Insert", [$officeID, $empID, $positionID]);
            }
        }
        
        echo "SUCCESS";
    } catch (Exception $e) {
        echo "UPDATE_ERROR: " . $e->getMessage();
    }
    break;
        
    case "softDeleteUser":
        $userID = $_POST["userID"] ?? "";
        
        if (empty($userID)) {
            echo "ERROR";
            exit;
        }
        
        // Soft delete: set IsDeleted = 1
        execsqlSRS("UPDATE Sys_UserAccount SET IsDeleted = 1, DeletedDate = GETDATE() WHERE UserID = ?", "Update", [$userID]);
        echo "SUCCESS";
        break;
        
    case "restoreUser":
        $userID = $_POST["userID"] ?? "";
        
        if (empty($userID)) {
            echo "ERROR";
            exit;
        }
        
        // Restore: set IsDeleted = 0
        execsqlSRS("UPDATE Sys_UserAccount SET IsDeleted = 0, DeletedDate = NULL WHERE UserID = ?", "Update", [$userID]);
        echo "SUCCESS";
        break;
		
case "toggleUserStatus":
    $userID = $_POST["userID"] ?? "";
    $status = $_POST["status"] ?? 0;
    
    if (empty($userID)) {
        echo json_encode(["success" => false, "message" => "Invalid user ID"]);
        exit;
    }
    
    try {
        execsqlSRS("UPDATE Sys_UserAccount SET IsActive = ? WHERE UserID = ?", 
                   "Update", [$status, $userID]);
        
        echo json_encode(["success" => true, "message" => "Status updated"]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
    break;
        
    case "getAllUsers":
        echo getUserTableHTML(false); // Not deleted
        break;
        
    case "getDeletedUsers":
        echo getUserTableHTML(true); // Deleted only
        break;
        
    default:
        echo "ERROR";
        break;
}

function getUserTableHTML($isDeleted = false) {
    // Handle both NULL and 0 for non-deleted users
    $deletedCondition = $isDeleted ? "= 1" : "IS NULL OR IsDeleted = 0";
    
$sql = "SELECT 
            ua.UserID,
            ua.EmpID,
            ua.EmailAddress,
            ua.Password,
            ua.Name,
            r.Role,
            o.OfficeName,
            ua.Position_id,
            ua.IsActive,
            ua.IsDeleted,
            ls.SectionName
        FROM Sys_UserAccount ua
        LEFT JOIN Sys_Role r ON ua.RID = r.RID
        LEFT JOIN Sys_Office o ON ua.Office_id = o.OfficeMenID
        LEFT JOIN LibrarySection ls ON 1=0  -- remove join to libAccess
        WHERE (ua.IsDeleted $deletedCondition)
        ORDER BY ua.Name";

    
    $users = execsqlSRS($sql, "Search", []);
    
    if (empty($users)) {
        $message = $isDeleted ? "No deleted users found" : "No users found";
        return '<tr><td colspan="11" class="text-center text-muted">' . $message . '</td></tr>';
    }
    
    $html = '';
    foreach ($users as $index => $user) {
        // Add table-danger class for deleted users
        $rowClass = $isDeleted ? "table-danger" : "";
        
        $html .= "<tr class='" . $rowClass . "' data-userid='" . $user['UserID'] . "'>";
        $html .= "<td>" . ($index + 1) . "</td>";
        $html .= "<td><strong>" . htmlspecialchars($user['EmpID']) . "</strong></td>";
        
        // Password field
        $maskedPassword = "••••••••";
        $actualPassword = htmlspecialchars($user['Password'], ENT_QUOTES);
        
        $html .= "<td>
                    <div class='input-group input-group-sm'>
                        <input type='password' 
                               class='form-control password-field' 
                               value='" . $maskedPassword . "' 
                               data-password='" . $actualPassword . "'
                               data-userid='" . $user['UserID'] . "'
                               readonly>
                        <div class='input-group-append'>
                            <button class='btn btn-outline-secondary btn-sm toggle-password' 
                                    data-userid='" . $user['UserID'] . "'
                                    title='Show Password'>
                                <i class='fas fa-eye' id='eye_" . $user['UserID'] . "'></i>
                            </button>
                        </div>
                    </div>
                  </td>";
        
        $html .= "<td>" . htmlspecialchars($user['Name']) . "</td>";
        $html .= "<td>" . htmlspecialchars($user['OfficeName'] ?? 'N/A') . "</td>";
        $html .= "<td>" . htmlspecialchars($user['Position_id'] ?? 'N/A') . "</td>";
        $html .= "<td>" . htmlspecialchars($user['Role']) . "</td>";
        $html .= "<td>" . htmlspecialchars($user['EmailAddress']) . "</td>";
        
        
        // Status with Bootstrap Toggle Switch (replaces text badge)
        $isActive = $user['IsActive'] == 0;
        $switchId = "userStatus" . $user['UserID'];
        
        // For deleted users, disable the toggle
        $disabled = $isDeleted ? "disabled" : "";
        
        $html .= "<td>
                    <div class='custom-control custom-switch'>
                        <input type='checkbox' 
                               class='custom-control-input toggleUserStatus' 
                               id='" . $switchId . "' 
                               data-userid='" . $user['UserID'] . "' 
                               " . ($isActive ? "checked" : "") . "
                               " . $disabled . ">
                        <label class='custom-control-label' for='" . $switchId . "'></label>
                    </div>
                  </td>";
        
        // Actions
        $html .= "<td class='text-center'>";
        $html .= "<div class='btn-group' role='group'>";

        if (!$isDeleted) {
            // Edit button for active users
            $html .= "<button class='btn btn-sm btn-info btn-edit-user mr-1' 
                              data-userid='" . $user['UserID'] . "' 
                              title='Edit'>
                        <i class='fas fa-edit'></i>
                      </button>";
                      
            // Delete button for active users
            $html .= "<button class='btn btn-sm btn-danger btn-delete-user' 
                              data-userid='" . $user['UserID'] . "' 
                              title='Delete'>
                        <i class='fas fa-trash'></i>
                      </button>";
        } else {
            // Restore button for deleted users
            $html .= "<button class='btn btn-sm btn-success btn-restore-user' 
                              data-userid='" . $user['UserID'] . "' 
                              title='Restore'>
                        <i class='fas fa-undo'></i>
                      </button>";
        }

        $html .= "</div></td>";
        $html .= "</tr>";
    }
    
    return $html;
}
?>