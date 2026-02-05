<?php
include "../db/dbconnection.php";

$request = $_POST["request"] ?? "";

switch ($request) {
	
    case "addUser":
        $empID = trim($_POST["empID"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $name = trim($_POST["name"] ?? "");
        $roleID = trim($_POST["roleID"] ?? "");
        $officeID = trim($_POST["officeID"] ?? "");
        $positionID = trim($_POST["positionID"] ?? "");
        
        // Validate required fields
        if (empty($empID) || empty($name) || empty($roleID)) {
            echo "MISSING_REQUIRED_FIELDS";
            exit;
        }
        
        // Check if user exists
        $check = execsqlSRS("SELECT COUNT(*) as count FROM Sys_UserAccount WHERE EmpID = ?", "Search", [$empID]);
        if ($check[0]['count'] > 0) {
            echo "DUPLICATE";
            exit;
        }
        
        // Set default email if empty
        if (empty($email)) {
            $email = $empID . '@example.com';
        }
        
        // Default password is EmpID
        $password = $empID;
        
        // Insert user (default IsActive = 0 = active)
        $sql = "INSERT INTO Sys_UserAccount (EmpID, EmailAddress, Password, Name, RID, Office_id, Position_id, IsActive, AccountRegDate) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, GETDATE())";
        
        try {
            execsqlSRS($sql, "Insert", [$empID, $email, $password, $name, $roleID, $officeID, $positionID]);
            echo "SUCCESS";
        } catch (Exception $e) {
            echo "INSERT_ERROR: " . $e->getMessage();
        }
        break;
        
case "updateUser":
    $userID = $_POST["userID"] ?? "";
    $empID = trim($_POST["empID"] ?? ""); // ADD THIS
    $email = trim($_POST["email"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $roleID = trim($_POST["roleID"] ?? "");
    $officeID = trim($_POST["officeID"] ?? "");
    $positionID = trim($_POST["positionID"] ?? "");
    $isActive = $_POST["isActive"] ?? 0;
    $allOfficeAccess = $_POST["allOfficeAccess"] ?? 0;
    $changePass = $_POST["changePass"] ?? 0;
    
    // Validate required fields
    if (empty($userID) || empty($empID) || empty($name) || empty($roleID)) {
        echo "MISSING_REQUIRED_FIELDS";
        exit;
    }
    
    $sql = "UPDATE Sys_UserAccount 
            SET EmpID = ?, EmailAddress = ?, Name = ?, RID = ?, Office_id = ?, Position_id = ?, 
                IsActive = ?, AllOfficeAcess = ?, ChangePass = ?
            WHERE UserID = ?";
    
    try {
        execsqlSRS($sql, "Update", [$empID, $email, $name, $roleID, $officeID, $positionID, $isActive, $allOfficeAccess, $changePass, $userID]);
        echo "SUCCESS";
    } catch (Exception $e) {
        echo "UPDATE_ERROR: " . $e->getMessage();
    }
    break;
        
    case "toggleStatus":
        $userID = $_POST["userID"] ?? "";
        $status = $_POST["status"] ?? 0; // 0 = active, 1 = inactive
        
        execsqlSRS("UPDATE Sys_UserAccount SET IsActive = ? WHERE UserID = ?", "Update", [$status, $userID]);
        echo "SUCCESS";
        break;
        
    case "getUserRow":
        $userID = $_POST["userID"] ?? 0;
        
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
                    ua.AllOfficeAcess,
                    ua.ChangePass
                FROM Sys_UserAccount ua
                LEFT JOIN Sys_Role r ON ua.RID = r.RID
                LEFT JOIN Sys_Office o ON ua.Office_id = o.OfficeMenID
                WHERE ua.UserID = ?";
        
        $user = execsqlSRS($sql, "Search", [$userID]);
        
        if (!empty($user)) {
            echo json_encode($user[0]);
        } else {
            echo "ERROR";
        }
        break;
        
    case "viewActiveUsers":
    case "viewInactiveUsers":
        $isActive = ($request == "viewActiveUsers") ? 0 : 1;
        echo getUserTableHTML($isActive);
        break;
        
    default:
        echo "ERROR";
        break;
}

function getUserTableHTML($isActive = 0) {
    $sql = "SELECT 
                ua.UserID,
                ua.EmpID,
                ua.EmailAddress,
                ua.Password,
                ua.Name,
                r.Role,
                o.OfficeName,
                ua.Position_id,
                ua.IsActive
            FROM Sys_UserAccount ua
            LEFT JOIN Sys_Role r ON ua.RID = r.RID
            LEFT JOIN Sys_Office o ON ua.Office_id = o.OfficeMenID
            WHERE ua.IsActive = ?
            ORDER BY ua.Name";
    
    $users = execsqlSRS($sql, "Search", [$isActive]);
    
    if (empty($users)) {
        return '<tr><td colspan="10" class="text-center">No users found</td></tr>';
    }
    
    $html = '';
    foreach ($users as $index => $user) {
        $statusClass = $isActive == 1 ? 'table-danger' : '';
        $maskedPassword = str_repeat('•', 8); // Masked dots
        
        // Get the actual password - escape it for HTML attribute
        $actualPassword = htmlspecialchars($user['Password'], ENT_QUOTES);
        
        $html .= "<tr class='{$statusClass}' data-userid='{$user['UserID']}' data-active='{$user['IsActive']}'>";
        $html .= "<td>" . ($index + 1) . "</td>";
        $html .= "<td>{$user['EmpID']}</td>";
        
        // Password field with toggle - FIXED: Add class and proper data attributes
        $html .= "<td>
                    <div class='input-group'>
                        <input type='password' 
                               class='form-control border-0 bg-transparent password-field' 
                               value='{$maskedPassword}' 
                               data-password='{$actualPassword}' 
                               data-userid='{$user['UserID']}'
                               readonly>
                        <div class='input-group-append'>
                            <button class='btn btn-sm btn-outline-secondary toggle-password' 
                                    data-userid='{$user['UserID']}'
                                    data-toggle='tooltip'
                                    title='Show Password'>
                                <i class='fas fa-eye' id='eye_{$user['UserID']}'></i>
                            </button>
                        </div>
                    </div>
                  </td>";
        
        $html .= "<td>" . htmlspecialchars($user['Name']) . "</td>";
        $html .= "<td>" . htmlspecialchars($user['OfficeName'] ?? 'N/A') . "</td>";
        $html .= "<td>" . htmlspecialchars($user['Position_id'] ?? 'N/A') . "</td>";
        $html .= "<td>" . htmlspecialchars($user['Role']) . "</td>";
        $html .= "<td>" . htmlspecialchars($user['EmailAddress']) . "</td>";
        
        // Status toggle
        $checked = $user['IsActive'] == 0 ? 'checked' : '';
        $html .= "<td class='text-center'>
                    <div class='custom-control custom-switch'>
                        <input type='checkbox' 
                               class='custom-control-input user-status' 
                               id='status_{$user['UserID']}' 
                               data-userid='{$user['UserID']}' 
                               {$checked}>
                        <label class='custom-control-label' for='status_{$user['UserID']}'></label>
                    </div>
                  </td>";
        
        // Action buttons
        $html .= "<td class='text-center'>";
        $html .= "<button class='btn btn-sm btn-info btn-edit-user mr-1' 
                          data-userid='{$user['UserID']}' 
                          title='Edit User'>
                    <i class='fas fa-edit'></i>
                  </button>";
        
        if ($isActive == 0) {
            $html .= "<button class='btn btn-sm btn-danger btn-toggle-user' 
                              data-userid='{$user['UserID']}' 
                              data-action='disable'
                              title='Disable User'>
                        <i class='fas fa-user-slash'></i>
                      </button>";
        } else {
            $html .= "<button class='btn btn-sm btn-success btn-toggle-user' 
                              data-userid='{$user['UserID']}' 
                              data-action='enable'
                              title='Enable User'>
                        <i class='fas fa-user-check'></i>
                      </button>";
        }
        
        $html .= "</td>";
        $html .= "</tr>";
    }
    
    return $html;
}
?>