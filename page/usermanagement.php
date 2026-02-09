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
                <!--  ACTIVE USERS  -->
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

                <!--  INACTIVE USERS  -->
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
//  UTILITY FUNCTIONS 
function togglePassword(userId) {
    const input = $(`input.password-field[data-userid="${userId}"]`);
    const icon = $(`#eye_${userId}`);
    
    if (input.length === 0) {
        console.error('Password input not found for user ID:', userId);
        return;
    }
    
    const actualPassword = input.data('password') || '';
    
    if (input.attr('type') === 'password') {
        input.attr('type', 'text').val(actualPassword);
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
        icon.closest('button').attr('title', 'Hide Password');
    } else {
        input.attr('type', 'password').val('••••••••');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
        icon.closest('button').attr('title', 'Show Password');
    }
    
    // Update tooltip
    const button = icon.closest('button');
    button.tooltip('dispose').tooltip();
}

function loadUsers(type) {
    const request = type === 'active' ? 'viewActiveUsers' : 'viewInactiveUsers';
    const target = type === 'active' ? '#userTable' : '#deletedUserTable';
    
    $.post("backend/bk_usermanagement.php", {request: request}, function(html) {
        $(target).html(html);
        $('[data-toggle="tooltip"]').tooltip();
    });
}

//INITIALIZE
$(document).ready(function() {
    loadUsers('active');
    setupEventHandlers();
});

//  EVENT HANDLERS 
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
$(document).on('click', '.btn-toggle-user', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const $button = $(this);
    const userID = $button.data('userid');
    const action = $button.data('action');
    const newStatus = action === 'disable' ? 1 : 0;
    const actionText = action === 'disable' ? 'Disable this user?' : 'Enable this user?';
    
    if (confirm(actionText)) {
        // Disable button to prevent double click
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.post("backend/bk_usermanagement.php", {
            request: "toggleStatus",
            userID: userID,
            status: newStatus
        }, function(response) {
            // Re-enable button
            $button.prop('disabled', false).html(action === 'disable' ? 
                '<i class="fas fa-user-slash"></i>' : 
                '<i class="fas fa-user-check"></i>');
            
            if (response === "SUCCESS") {
                // Reload both tables
                loadUsers('active');
                loadUsers('inactive');
            } else {
                alert('Failed to update user status');
            }
        }).fail(function() {
            // Re-enable button on error
            $button.prop('disabled', false).html(action === 'disable' ? 
                '<i class="fas fa-user-slash"></i>' : 
                '<i class="fas fa-user-check"></i>');
            alert('Server error. Please try again.');
        });
    }
});
    
// Status switch change
$(document).on('change', '.user-status', function() {
    const $checkbox = $(this);
    const userID = $checkbox.data('userid');
    const isChecked = $checkbox.prop('checked');
    const newStatus = isChecked ? 0 : 1; // Convert checkbox to IsActive value
    const actionText = isChecked ? 'Enable this user?' : 'Disable this user?';
    
    // Prevent immediate change
    $checkbox.prop('checked', !isChecked); // Revert immediately
    
    // Show confirmation
    if (confirm(actionText)) {
        // If confirmed, make the change via AJAX
        $.post("backend/bk_usermanagement.php", {
            request: "toggleStatus",
            userID: userID,
            status: newStatus
        }, function(response) {
            if (response === "SUCCESS") {
                // Success: Update checkbox to reflect new state
                $checkbox.prop('checked', isChecked);
                // Reload both tables
                loadUsers('active');
                loadUsers('inactive');
            } else {
                // Failed: Keep checkbox in original state
                $checkbox.prop('checked', !isChecked);
                alert('Failed to update user status');
            }
        }).fail(function() {
            // On error, revert checkbox
            $checkbox.prop('checked', !isChecked);
            alert('Server error. Please try again.');
        });
    }
    // If cancelled, checkbox stays reverted (no change)
});
    
    // Save user (add) - FIXED: Prevent double submission
    $(document).on('click', '#btnSaveUser', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const button = $(this);
        const originalText = button.html();
        
        // Get form data
        const formData = {
            request: "addUser",
            empID: $('#u_empID').val(),
            email: $('#u_email').val() || $('#u_empID').val() + '@example.com',
            name: $('#u_name').val(),
            roleID: $('#u_role').val(),
            officeID: $('#u_unit').val(),
            positionID: $('#u_position').val() || ''
        };
        
        // Validation - Check if EmpID and RoleID are not empty
        const empID = String(formData.empID || '').trim();
        const roleID = String(formData.roleID || '').trim();
        
        if (!empID) {
            alert('Employee ID is required!');
            return false;
        }
        
        if (!roleID) {
            alert('Role is required!');
            return false;
        }
        
        // Show loading and disable button to prevent double click
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Send request
        $.post("backend/bk_usermanagement.php", formData, function(response) {
            button.prop('disabled', false).html(originalText);
            
            if (response === "SUCCESS") {
                $('.modal').modal('hide');
                $('#addUserForm')[0]?.reset();
                loadUsers('active');
                alert('User added successfully!');
            } else if (response === "DUPLICATE") {
                alert('User already exists!');
            } else {
                alert('Error: ' + response);
            }
        }).fail(function() {
            button.prop('disabled', false).html(originalText);
            alert('Server error. Please try again.');
        });
        
        return false;
    });
    
// Update user (edit) - FIXED: Prevent double execution
$(document).on('click', '#btnUpdateUser', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const button = $(this);
    
    // Prevent double click
    if (button.prop('disabled')) {
        return false;
    }
    
    const originalText = button.html();
    
    // Get form data
    const formData = {
        request: "updateUser",
        userID: $('#edit_userID').val(),
        empID: $('#edit_empID').val(),
        email: $('#edit_email').val(),
        name: $('#edit_name').val(),
        roleID: $('#edit_role').val(),
        officeID: $('#edit_office').val(),
        positionID: $('#edit_position').val() || '',
        isActive: $('#edit_status').val(),
        allOfficeAccess: $('#edit_alloffice').val(),
        changePass: $('#edit_changepass').val()
    };
    
    // Validation
    const empID = String(formData.empID || '').trim();
    const roleID = String(formData.roleID || '').trim();
    
    if (!empID) {
        alert('Employee ID is required!');
        return false;
    }
    
    if (!roleID) {
        alert('Role is required!');
        return false;
    }
    
    // Show loading and disable button
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    // Send request
    $.post("backend/bk_usermanagement.php", formData, function(response) {
        button.prop('disabled', false).html(originalText);
        
        if (response === "SUCCESS") {
            $('.modal').modal('hide');
            
            // Wait a bit for modal to close
            setTimeout(function() {
                alert('User updated successfully!');
                loadUsers('active');
                loadUsers('inactive');
            }, 300);
        } else {
            alert('Error: ' + response);
        }
    }).fail(function() {
        button.prop('disabled', false).html(originalText);
        alert('Server error. Please try again.');
    });
    
    return false;
});
    
    // Also prevent form submission from Enter key
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        return false;
    });
    
    // Tab switching
    $('#inactive-tab').click(function() {
        loadUsers('inactive');
    });
}
</script>