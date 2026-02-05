<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="font-weight-bold text-dark">
            <i class="fas fa-users mr-2 text-primary"></i> User Management
        </h4>
        <button class="btn btn-success" id="btnAddEmployee">
            <i class="fas fa-user-plus"></i> Add Employee
        </button>
    </div>

    <!-- Tabs Card -->
    <div class="card card-primary card-outline">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="userTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab" data-toggle="pill" href="#activeUsers" role="tab">
                        <i class="fas fa-user-check mr-1"></i> Active Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="inactive-tab" data-toggle="pill" href="#inactiveUsers" role="tab">
                        <i class="fas fa-user-slash mr-1"></i> Inactive Users
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                <!-- ================= ACTIVE USERS ================= -->
                <div class="tab-pane fade show active" id="activeUsers" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Employee ID</th>
                                    <th>Password</th>
                                    <th>Name</th>
                                    <th>Office</th>
                                    <th>Position</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userTable">
                                <!-- Active users will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ================= INACTIVE USERS ================= -->
                <div class="tab-pane fade" id="inactiveUsers" role="tabpanel">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle mr-1"></i>
                        Inactive users can be enabled at any time.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Employee ID</th>
                                    <th>Password</th>
                                    <th>Name</th>
                                    <th>Office</th>
                                    <th>Position</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="deletedUserTable">
                                <!-- Inactive users will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'modalContainer.php'; ?>


<script>
// ==================== UTILITY FUNCTIONS ====================
function togglePassword(userId) {
    const input = $(`[data-userid="${userId}"].password-field`);
    const icon = $(`#eye_${userId}`);
    const actualPassword = input.data('password') || '••••••••';
    
    if (input.attr('type') === 'password') {
        input.attr('type', 'text').val(actualPassword);
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
        icon.closest('button').attr('title', 'Hide Password');
    } else {
        input.attr('type', 'password').val('••••••••');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
        icon.closest('button').attr('title', 'Show Password');
    }
    
    icon.closest('button').tooltip('dispose').tooltip();
}

function loadUsers(type) {
    const request = type === 'active' ? 'viewActiveUsers' : 'viewInactiveUsers';
    const target = type === 'active' ? '#userTable' : '#deletedUserTable';
    
    $.post("backend/bk_usermanagement.php", {request: request}, function(html) {
        $(target).html(html);
        $('[data-toggle="tooltip"]').tooltip();
    });
}

// ==================== INITIALIZE ====================
$(document).ready(function() {
    loadUsers('active');
    setupEventHandlers();
});

// ==================== EVENT HANDLERS ====================
function setupEventHandlers() {
    // Add user modal
    $('#btnAddEmployee').click(function() {
        openAddModal("page/modals.php", "usermodal");
    });
    
    // Edit user modal
    $(document).on('click', '.btn-edit-user', function() {
        const userID = $(this).data('userid');
        openEditModal("page/modals.php", "usereditmodal", "userID", userID);
    });
    
    // Password toggle
    $(document).on('click', '.toggle-password', function() {
        const userID = $(this).data('userid');
        togglePassword(userID);
    });
    
    // Toggle user status (button)
    $(document).on('click', '.btn-toggle-user', function() {
        const userID = $(this).data('userid');
        const action = $(this).data('action');
        const newStatus = action === 'disable' ? 1 : 0;
        const actionText = action === 'disable' ? 'Disable this user?' : 'Enable this user?';
        
        if (confirm(actionText)) {
            $.post("backend/bk_usermanagement.php", {
                request: "toggleStatus",
                userID: userID,
                status: newStatus
            }, function(response) {
                if (response === "SUCCESS") {
                    // Move row between tables
                    moveUserRow(userID, newStatus);
                }
            });
        }
    });
    
    // Status switch change
    $(document).on('change', '.user-status', function() {
        const userID = $(this).data('userid');
        const isChecked = $(this).prop('checked');
        const newStatus = isChecked ? 0 : 1; // Convert checkbox to IsActive value
        const actionText = isChecked ? 'Enable this user?' : 'Disable this user?';
        
        if (confirm(actionText)) {
            $.post("backend/bk_usermanagement.php", {
                request: "toggleStatus",
                userID: userID,
                status: newStatus
            }, function(response) {
                if (response === "SUCCESS") {
                    moveUserRow(userID, newStatus);
                } else {
                    // Revert checkbox if failed
                    $(this).prop('checked', !isChecked);
                }
            });
        } else {
            // Revert checkbox if user cancels
            $(this).prop('checked', !isChecked);
        }
    });
    
    // Save user (add/edit)
    $(document).on('click', '#btnSaveUser, #btnUpdateUser', function() {
        const isAdd = this.id === 'btnSaveUser';
        const button = $(this);
        const originalText = button.html();
        
        // Get form data
        const formData = {
            request: isAdd ? "addUser" : "updateUser"
        };
        
        if (isAdd) {
            formData.empID = $('#u_empID').val();
            formData.email = $('#u_email').val() || formData.empID + '@example.com';
            formData.name = $('#u_name').val();
            formData.roleID = $('#u_role').val();
            formData.officeID = $('#u_unit').val();
            formData.positionID = $('#u_position').val() || '';
        } else {
            formData.userID = $('#edit_userID').val();
            formData.empID = $('#edit_empID').val();
            formData.email = $('#edit_email').val();
            formData.name = $('#edit_name').val();
            formData.roleID = $('#edit_role').val();
            formData.officeID = $('#edit_office').val();
            formData.positionID = $('#edit_position').val() || '';
            formData.isActive = $('#edit_status').val();
            formData.allOfficeAccess = $('#edit_alloffice').val();
            formData.changePass = $('#edit_changepass').val();
        }
        
        // Validation - Check if EmpID and RoleID are not empty
        const empID = String(formData.empID || '').trim();
        const roleID = String(formData.roleID || '').trim();
        
        if (!empID) {
            alert('Employee ID is required!');
            return;
        }
        
        if (!roleID) {
            alert('Role is required!');
            return;
        }
        
        // Show loading
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Send request
        $.post("backend/bk_usermanagement.php", formData, function(response) {
            button.prop('disabled', false).html(originalText);
            
            if (response === "SUCCESS") {
                $('.modal').modal('hide');
                
                if (isAdd) {
                    // New user - reload active table
                    loadUsers('active');
                    $('#addUserForm')[0]?.reset();
                } else {
                    // Updated user - update specific row
                    updateUserRow(formData.userID);
                }
            } else if (response === "DUPLICATE") {
                alert('User already exists!');
            }
        });
    });
    
    // Tab switching
    $('#inactive-tab').click(function() {
        loadUsers('inactive');
    });
}

// ==================== ROW OPERATIONS ====================
function moveUserRow(userID, newStatus) {
    // Remove from current table
    $(`[data-userid="${userID}"]`).remove();
    
    // Reload both tables to reflect changes
    loadUsers('active');
    loadUsers('inactive');
}

function updateUserRow(userID) {
    // Get updated user data
    $.post("backend/bk_usermanagement.php", {
        request: "getUserRow",
        userID: userID
    }, function(response) {
        if (response !== "ERROR") {
            const user = JSON.parse(response);
            const currentRow = $(`[data-userid="${userID}"]`);
            
            if (currentRow.length) {
                // Update row data
                currentRow.find('td:nth-child(2)').text(user.EmpID);
                currentRow.find('td:nth-child(4)').text(user.Name);
                currentRow.find('td:nth-child(5)').text(user.OfficeName || 'N/A');
                currentRow.find('td:nth-child(6)').text(user.Position_id || 'N/A');
                currentRow.find('td:nth-child(7)').text(user.Role);
                currentRow.find('td:nth-child(8)').text(user.EmailAddress);
                
                // Update password field
                const passwordField = currentRow.find('.password-field');
                passwordField.data('password', user.Password);
                
                // Update status checkbox
                const checkbox = currentRow.find('.user-status');
                checkbox.prop('checked', user.IsActive == 0);
                
                // Update row class if status changed
                if (user.IsActive == 1) {
                    currentRow.addClass('table-danger');
                } else {
                    currentRow.removeClass('table-danger');
                }
            }
        }
    });
}
</script>