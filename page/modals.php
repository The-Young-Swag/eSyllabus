<?php
require_once "../db/dbconnection.php";

$request = $_POST['request'] ?? '';

switch ($request) {
    case 'menuAddmodal':
        echo generateMenuModal();
        break;
        
    case 'menueditmodal':
        $menID = $_POST['menID'] ?? 0;
        echo generateMenuEditModal($menID);
        break;
        
    case 'rolemodal':
        echo generateRoleModal();
        break;
        // In the switch statement, add:
case 'roleeditmodal':
    $roleID = $_POST['roleID'] ?? 0;
    echo generateRoleEditModal($roleID);
    break;
	
	case 'usermodal':
        echo generateUserModal();
        break;
        
    case 'usereditmodal':
        $userID = $_POST['userID'] ?? 0;
        echo generateUserEditModal($userID);
        break;
        
    case 'mdlOfficeStaff':
        echo generateOfficeStaffModal();
        break;
        
    case 'useLstOffAcc':
        echo generateOfficeAccessModal();
        break;
        
    default:
        // You could return all modals or a specific default
        echo "Invalid modal request";
        break;
}

// =============================
// MODAL GENERATOR FUNCTIONS
// =============================

function generateMenuModal() {
    ob_start();
    ?>
    <!-- Add Modal -->
    <div class="modal fade" id="menuAddmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Menu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <!-- Wrap form fields in a form with proper method and action -->
                <form id="addMenuForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="m_menu">Menu Name *</label>
                            <input type="text" class="form-control" id="m_menu" name="m_menu" placeholder="Menu..." required>
                        </div>

                        <div class="form-group">
                            <label for="m_mother">MotherMenuID (0 if it's a Mother Menu)</label>
                            <input type="number" class="form-control" id="m_mother" name="m_mother" placeholder="0" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label for="m_desc">Description</label>
                            <input type="text" class="form-control" id="m_desc" name="m_desc" placeholder="Description...">
                        </div>

                        <div class="form-group">
                            <label for="m_code">Menu Code *</label>
                            <input type="text" class="form-control" id="m_code" name="m_code" placeholder="Menu Code..." required>
                        </div>

                        <div class="form-group">
                            <label for="m_link">Menu Link</label>
                            <input type="text" class="form-control" id="m_link" name="m_link" placeholder="Menu Link...">
                        </div>

                        <div class="form-group">
                            <label for="m_arrange">Arrangement *</label>
                            <input type="number" class="form-control" id="m_arrange" name="m_arrange" placeholder="Menu Index..." required min="1">
                        </div>

                        <div class="form-group">
                            <label for="m_icon">Icon</label>
                            <input type="text" class="form-control" id="m_icon" name="m_icon" placeholder="e.g., fas fa-home">
                        </div>

                        <div class="form-group">
                            <label for="m_status">Status</label>
                            <select class="form-control" id="m_status" name="m_status">
                                <option value="0">Active</option>
                                <option value="1">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" id="btnaddmenu" name="m_submit" class="btn btn-primary">Add Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();

}

function generateMenuEditModal($menID = 0) {
    // If menID is passed from POST (your original approach)
    if ($menID === 0) {
        $menID = isset($_POST['menID']) ? intval($_POST['menID']) : 0;
    }
    
    // 2. INITIALIZE EMPTY DATA ARRAY
    $menu = [ // Changed from $menuData to $menu
        'MenID' => '',
        'Menu' => '',
        'MotherMenID' => '',
        'Description' => '',
        'Menucode' => '',
        'MenuLink' => '',
        'Arrangement' => '',
        'UnActive' => '0',
        'MenIcon' => ''
    ];
    
    // 3. ONLY FETCH IF WE HAVE A VALID ID
    if ($menID > 0) {
        // SQL Query to get the specific menu
        $sql = "SELECT * FROM Sys_Menu WHERE MenID = ?";
        
        // Execute query with parameter to prevent SQL injection
        $result = execsqlSRS($sql, "Search", [$menID]);
        
        // 4. IF DATA EXISTS, UPDATE OUR ARRAY
        if (!empty($result) && is_array($result)) {
            $menu = array_merge($menu, $result[0]); // Changed from $menuData
        } else {
            // If no data found, maybe show an error
            return "<div class='alert alert-danger'>Menu not found!</div>";
        }
    }
    
    // 5. GENERATE THE HTML WITH THE DATA
    ob_start();
    ?>
    <!-- Edit Modal -->
    <div class="modal fade" id="menueditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Menu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="p-3">
                    <!-- Hidden MenID to know which row to update -->
                    <input type="hidden" id="edit_menID" value="<?php echo htmlspecialchars($menu['MenID']); ?>">

                    <div class="form-group">
                        <label>Menu</label>
                        <input type="text" class="form-control" id="edit_menu"
                               value="<?php echo htmlspecialchars($menu['Menu']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" class="form-control" id="edit_desc"
                               value="<?php echo htmlspecialchars($menu['Description']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Menu Code</label>
                        <input type="text" class="form-control" id="edit_code"
                               value="<?php echo htmlspecialchars($menu['Menucode']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Menu Link</label>
                        <input type="text" class="form-control" id="edit_link"
                               value="<?php echo htmlspecialchars($menu['MenuLink']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Mother Menu ID</label>
                        <input type="text" class="form-control" id="edit_mother"
                               value="<?php echo htmlspecialchars($menu['MotherMenID']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Arrangement</label>
                        <input type="text" class="form-control" id="edit_arrangement"
                               value="<?php echo htmlspecialchars($menu['Arrangement']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="edit_status">
                            <option value="0" <?php echo $menu['UnActive'] == 0 ? 'selected' : ''; ?>>Active</option>
                            <option value="1" <?php echo $menu['UnActive'] == 1 ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Menu Icon</label>
                        <input type="text" class="form-control" id="edit_icon"
                               value="<?php echo htmlspecialchars($menu['MenIcon']); ?>">
                    </div>

                    <button class="btn btn-primary" id="btnUpdateMenu">Save Changes</button>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function generateRoleModal() {
    ob_start();
    ?>
    <!-- Add Role Modal -->
    <div class="modal fade" id="rolemodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="p-3">
                    <div class="form-group">
                        <label for="">Role</label>
                        <input type="text" class="form-control" id="r_role" name="r_role" placeholder="Role...">
                    </div>
                    
                    <div class="form-group">
                        <label for="">Role Code</label>
                        <input type="text" class="form-control" id="r_rolecode" name="r_rolecode" placeholder="Role Code...">
                    </div>
                    
                    <div class="form-group">
                        <label for="">Status</label>
                        <select class="form-control" id="r_status" name="r_status">
                            <option value="0">Active</option>
                            <option value="1">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-group pt-2 d-flex justify-content-center">
                        <button type="submit" id="r_submit" name="r_submit" class="btn btn-primary">Add Role</button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}



function generateRoleEditModal($roleID = 0) {
    // Initialize empty role data
    $role = [
        'RID' => '',
        'Role' => '',
        'Rolecode' => '',
        'UnActive' => '0'
    ];
    
    // Fetch role data if ID is provided
    if ($roleID > 0) {
        $sql = "SELECT * FROM Sys_Role WHERE RID = ?";
        $result = execsqlSRS($sql, "Search", [$roleID]);
        
        if (!empty($result) && is_array($result)) {
            $role = array_merge($role, $result[0]);
        }
    }
    
    ob_start();
    ?>
    <!-- Edit Role Modal -->
    <div class="modal fade" id="roleeditmodal" tabindex="-1" aria-labelledby="roleEditModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleEditModalLabel">Edit Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="p-3">
                    <!-- Hidden RID -->
                    <input type="hidden" id="edit_roleID" value="<?php echo htmlspecialchars($role['RID']); ?>">
                    
                    <div class="form-group">
                        <label>Role Name</label>
                        <input type="text" class="form-control" id="edit_role" 
                               value="<?php echo htmlspecialchars($role['Role']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Role Code</label>
                        <input type="text" class="form-control" id="edit_rolecode" 
                               value="<?php echo htmlspecialchars($role['Rolecode']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="edit_role_status">
                            <option value="0" <?php echo $role['UnActive'] == 0 ? 'selected' : ''; ?>>Active</option>
                            <option value="1" <?php echo $role['UnActive'] == 1 ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <button class="btn btn-primary" id="btnUpdateRole">Save Changes</button>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function generateUserModal() {
    // Get roles from Sys_Role (this should be in srsDB)
    $roles = execsqlSRS("
        SELECT RID, Role 
        FROM Sys_Role 
        WHERE UnActive = '0' 
        ORDER BY Role
    ", "Select", []);
    
    // Get offices from Sys_Office (this should be in srsDB)
    $offices = execsqlSRS("
        SELECT [OfficeMenID], [OfficeName]
        FROM [Sys_Office]
        WHERE [UnActive] = '0'
        ORDER BY OfficeName
    ", "Select", []);
    
    ob_start();
    ?>
    <!-- Add User Modal -->
    <div class="modal fade" id="usermodal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <form id="addUserForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Employee ID (EmpID) *</label>
                            <input type="text" class="form-control" id="u_empID" 
                                   placeholder="Enter Employee ID (e.g., TAU-659)" 
                                   required>
                            <small class="form-text text-muted">Unique employee identifier</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" class="form-control" id="u_email" 
                                   placeholder="user@example.com">
                        </div>
                        
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" class="form-control" id="u_name" 
                                   placeholder="Enter full name"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label>Role *</label>
                            <select class="form-control" id="u_role" required>
                                <option value="">-- Select Role --</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value='<?php echo $role['RID']; ?>'>
                                        <?php echo htmlspecialchars($role['Role']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Office</label>
                            <select class="form-control" id="u_unit">
                                <option value="">-- Select Office --</option>
                                <?php foreach ($offices as $office): ?>
                                    <option value='<?php echo $office['OfficeMenID']; ?>'>
                                        <?php echo htmlspecialchars($office['OfficeName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Position ID</label>
                            <input type="text" class="form-control" id="u_position" 
                                   placeholder="Position ID (optional)">
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="u_status" checked>
                                <label class="custom-control-label" for="u_status">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="btnSaveUser">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function generateUserEditModal($userID = 0) {
    // Initialize empty user data
    $user = [
        'UserID' => '',
        'EmpID' => '',
        'EmailAddress' => '',
        'Name' => '',
        'RID' => '',
        'Office_id' => '',
        'Position_id' => '',
        'IsActive' => '0', // Default to active (0)
        'AllOfficeAcess' => '0',
        'ChangePass' => '0'
    ];
    
    // Fetch user data if ID is provided (from Sys_UserAccount in srsDB)
    if ($userID > 0) {
        $sql = "SELECT * FROM [Sys_UserAccount] WHERE UserID = ?";
        $result = execsqlSRS($sql, "Search", [$userID]);
        
        if (!empty($result) && is_array($result)) {
            $user = array_merge($user, $result[0]);
        }
    }
    
    // Get all active roles from srsDB
    $roles = execsqlSRS("
        SELECT RID, Role 
        FROM Sys_Role 
        WHERE UnActive = '0' 
        ORDER BY Role
    ", "Select", []);
    
    // Get all offices from srsDB
    $offices = execsqlSRS("
        SELECT [OfficeMenID], [OfficeName]
        FROM [Sys_Office]
        WHERE [UnActive] = '0'
        ORDER BY OfficeName
    ", "Select", []);
    
    ob_start();
    ?>
    <!-- Edit User Modal -->
    <div class="modal fade" id="usereditmodal" tabindex="-1" aria-labelledby="userEditModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userEditModalLabel">
                        <i class="fas fa-user-edit mr-2"></i> Edit User
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="p-3">
                    <!-- Hidden UserID -->
                    <input type="hidden" id="edit_userID" value="<?php echo htmlspecialchars($user['UserID']); ?>">
                    
                    <div class="form-group">
                        <label>Employee ID (EmpID)</label>
                        <input type="text" class="form-control" id="edit_empID" 
                               value="<?php echo htmlspecialchars($user['EmpID']); ?>"
                               placeholder="Employee ID">
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-control" id="edit_email" 
                               value="<?php echo htmlspecialchars($user['EmailAddress']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" id="edit_name" 
                               value="<?php echo htmlspecialchars($user['Name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control" id="edit_role">
                            <option value="">-- Select Role --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['RID']; ?>" 
                                    <?php echo ($role['RID'] == $user['RID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['Role']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Office</label>
                        <select class="form-control" id="edit_office">
                            <option value="">-- Select Office --</option>
                            <?php foreach ($offices as $office): ?>
                                <option value="<?php echo $office['OfficeMenID']; ?>" 
                                    <?php echo ($office['OfficeMenID'] == $user['Office_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($office['OfficeName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Position ID</label>
                        <input type="text" class="form-control" id="edit_position" 
                               value="<?php echo htmlspecialchars($user['Position_id']); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Active Status</label>
                                <select class="form-control" id="edit_status">
                                    <option value="0" <?php echo $user['IsActive'] == 0 ? 'selected' : ''; ?>>Active</option>
                                    <option value="1" <?php echo $user['IsActive'] == 1 ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>All Office Access</label>
                                <select class="form-control" id="edit_alloffice">
                                    <option value="0" <?php echo $user['AllOfficeAcess'] == 0 ? 'selected' : ''; ?>>No</option>
                                    <option value="1" <?php echo $user['AllOfficeAcess'] == 1 ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Change Password</label>
                                <select class="form-control" id="edit_changepass">
                                    <option value="0" <?php echo $user['ChangePass'] == 0 ? 'selected' : ''; ?>>No</option>
                                    <option value="1" <?php echo $user['ChangePass'] == 1 ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button class="btn btn-primary btn-block" id="btnUpdateUser">
                            <i class="fas fa-save mr-2"></i> Update User
                        </button>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function generateOfficeStaffModal() {
    ob_start();
    ?>
    <!-- All Office Staff Modal -->
    <div class="modal fade" id="mdlOfficeStaff" tabindex="-1" aria-labelledby="mdlOfficeStaffLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content shadow-lg border-0 rounded-2">
                
                <!-- Header -->
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="mdlOfficeStaffLabel">
                        <i class="fas fa-users mr-2"></i> All Office Staff
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;" id='showOfficeSaf'>
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Employee Name</th>
                                    <th scope="col">Position</th>
                                    <th scope="col">Office</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="staffList">
                                <!-- Content loaded via AJAX -->
                                <tr>
                                    <td colspan="6" class="text-center">Loading staff data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function generateOfficeAccessModal() {
    ob_start();
    ?>
    <!-- Office Access Modal -->
    <div class="modal fade" id="useLstOffAcc" tabindex="-1" aria-labelledby="mdlOfficeAccessLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg border-0 rounded-2">

                <!-- Header -->
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="mdlOfficeAccessLabel">
                        <i class="fas fa-building mr-2"></i> Office Access
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <select class="form-control w-75" id="sltoffice" name="sltoffice">
                            <option value="">-- Select Employee --</option>
                            <option value="1">Juan Dela Cruz</option>
                            <option value="2">Maria Santos</option>
                            <option value="3">Pedro Ramirez</option>
                        </select>
                        <button type="button" 
                                class="btn btn-primary" 
                                id="AUAOffice"
                                data-EditEmpID="">Add Office Access</button>
                    </div>
                    <hr>
                    
                    <!-- Access List -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;" id="scrollOfficeList">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 40%;">Office Name</th>
                                    <th style="width: 20%;">Code</th>
                                    <th style="width: 20%;">Status</th>
                                    <th style="width: 15%;" class="text-center">Access</th>
                                </tr>
                            </thead>
                            <tbody id="officeAccessList">
                                <!-- Content loaded via AJAX -->
                                <tr>
                                    <td colspan="5" class="text-center">Select an employee to view office access</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>