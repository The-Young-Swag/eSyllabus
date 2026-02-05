<?php
include "../db/dbconnection.php";

$request = $_POST["request"] ?? "";

switch ($request) {
    case "addUser":
        $empID = $_POST["empID"] ?? "";
        $email = $_POST["email"] ?? "";
        $name = $_POST["name"] ?? "";
        $roleID = $_POST["roleID"] ?? "";
        $officeID = $_POST["officeID"] ?? "";
        $positionID = $_POST["positionID"] ?? "";
        
        // Check if user exists
        $check = execsqlSRS("SELECT UserID FROM Sys_UserAccount WHERE EmpID = ?", "Search", [$empID]);
        if (!empty($check)) {
            echo "DUPLICATE";
            exit;
        }
        
        // Insert user (default IsActive = 0 = active)
        $sql = "INSERT INTO Sys_UserAccount (EmpID, EmailAddress, Password, Name, RID, Office_id, Position_id, IsActive, AccountRegDate) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, GETDATE())";
        
        execsqlSRS($sql, "Insert", [$empID, $email, $empID, $name, $roleID, $officeID, $positionID]);
        echo "SUCCESS";
        break;
        
    case "updateUser":
        $userID = $_POST["userID"] ?? "";
        $email = $_POST["email"] ?? "";
        $name = $_POST["name"] ?? "";
        $roleID = $_POST["roleID"] ?? "";
        $officeID = $_POST["officeID"] ?? "";
        $positionID = $_POST["positionID"] ?? "";
        $isActive = $_POST["isActive"] ?? 0;
        $allOfficeAccess = $_POST["allOfficeAccess"] ?? 0;
        $changePass = $_POST["changePass"] ?? 0;
        
        $sql = "UPDATE Sys_UserAccount 
                SET EmailAddress = ?, Name = ?, RID = ?, Office_id = ?, Position_id = ?, 
                    IsActive = ?, AllOfficeAcess = ?, ChangePass = ?
                WHERE UserID = ?";
        
        execsqlSRS($sql, "Update", [$email, $name, $roleID, $officeID, $positionID, $isActive, $allOfficeAccess, $changePass, $userID]);
        echo "SUCCESS";
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
        $password = str_repeat('•', 8); // Password dots instead of asterisks
        
        $html .= "<tr class='{$statusClass}' data-userid='{$user['UserID']}' data-active='{$user['IsActive']}'>";
        $html .= "<td>" . ($index + 1) . "</td>";
        $html .= "<td>{$user['EmpID']}</td>";
        
        // Password field with toggle
        $html .= "<td>
                    <div class='input-group'>
                        <input type='password' 
                               class='form-control border-0 bg-transparent password-field' 
                               value='{$password}' 
                               data-password='{$user['Password']}'
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
        
        $html .= "<td>{$user['Name']}</td>";
        $html .= "<td>" . ($user['OfficeName'] ?? 'N/A') . "</td>";
        $html .= "<td>" . ($user['Position_id'] ?? 'N/A') . "</td>";
        $html .= "<td>{$user['Role']}</td>";
        $html .= "<td>{$user['EmailAddress']}</td>";
        
        // Status toggle - Note: IsActive = 0 means ACTIVE (checkbox checked)
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