<div class="container-fluid mt-3">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col">
            <h4 class="font-weight-bold">
                <i class="fas fa-users mr-2 text-primary"></i> User Management
            </h4>
        </div>
        <div class="col-auto">
            <button class="btn btn-success" id="btnAddUser">
                <i class="fas fa-user-plus mr-1"></i> Add User
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#allUsers">
                        <i class="fas fa-users mr-1"></i> All Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#deletedUsers">
                        <i class="fas fa-trash mr-1"></i> Deleted Users
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content">
                <!-- All Users Tab -->
<div class="tab-pane fade show active" id="allUsers">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th width="5%">#</th>
                    <th>Emp ID</th>
                    <th>Password</th>
                    <th>Name</th>
                    <th>Office</th>
                    <th>Position</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th width="10%">Status</th>
                    <th width="12%">Actions</th>
                </tr>
            </thead>
            <tbody id="tableAllUsers">
                <!-- Loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>
                
                <!-- Deleted Users Tab -->
<div class="tab-pane fade" id="deletedUsers">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th width="5%">#</th>
                    <th>Emp ID</th>
                    <th>Password</th>
                    <th>Name</th>
                    <th>Office</th>
                    <th>Position</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th width="10%">Status</th>
                    <th width="12%">Actions</th>
                </tr>
            </thead>
            <tbody id="tableDeletedUsers">
                <!-- Loaded via AJAX -->
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
// SIMPLE USER MANAGEMENT - CLEAN VERSION
$(document).ready(function() {
    // Load initial data
    loadUsers('all');
        loadUsers('deleted'); // Add this line to load deleted users on page load

    // Setup event handlers
    setupUserEvents();
});

// Load users function
function loadUsers(type) {
    const tableId = type === 'all' ? '#tableAllUsers' : '#tableDeletedUsers';
    const request = type === 'all' ? 'getAllUsers' : 'getDeletedUsers';
    
    $.post("backend/bk_usermanagement.php", { request: request }, function(data) {
        $(tableId).html(data);
        $('[data-toggle="tooltip"]').tooltip();
    });
}

// Setup all event handlers
function setupUserEvents() {
    // Tab click handler
    $(document).off('shown.bs.tab.user').on('shown.bs.tab.user', 'a[data-toggle="tab"]', function(e) {
        const target = $(e.target).attr('href');
        if (target === '#deletedUsers') {
            loadUsers('deleted');
        } else if (target === '#allUsers') {
            loadUsers('all');
        }
    });
    
    // Add user button
    $('#btnAddUser').off('click.user').on('click.user', function() {
        openAddModal("page/modals.php", "usermodal");
    });
    
    // Toggle password
    $(document).off('click.user', '.toggle-password').on('click.user', '.toggle-password', function() {
        const userId = $(this).data('userid');
        const input = $(`input.password-field[data-userid="${userId}"]`);
        const icon = $(`#eye_${userId}`);
        const actualPassword = input.data('password') || '';
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text').val(actualPassword);
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password').val('••••••••');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Edit user
    $(document).off('click.user', '.btn-edit-user').on('click.user', '.btn-edit-user', function() {
        const userId = $(this).data('userid');
        openEditModal("page/modals.php", "usereditmodal", "userID", userId);
    });
    
    // Delete user
    $(document).off('click.user', '.btn-delete-user').on('click.user', '.btn-delete-user', function() {
        const userId = $(this).data('userid');
        if (confirm("Move this user to deleted list?")) {
            $.post("backend/bk_usermanagement.php", {
                request: "softDeleteUser",
                userID: userId
            }, function(response) {
                if (response === "SUCCESS") {
                    loadUsers('all');
                    loadUsers('deleted');
                }
            });
        }
    });
    
    // Restore user
    $(document).off('click.user', '.btn-restore-user').on('click.user', '.btn-restore-user', function() {
        const userId = $(this).data('userid');
        if (confirm("Restore this user?")) {
            $.post("backend/bk_usermanagement.php", {
                request: "restoreUser",
                userID: userId
            }, function(response) {
                if (response === "SUCCESS") {
                    loadUsers('all');
                    loadUsers('deleted');
                }
            });
        }
    });
    
    // Save user
$(document).off('click.user', '#btnSaveUser').on('click.user', '#btnSaveUser', function(e) {
    e.preventDefault();
    const btn = $(this);
    const originalText = btn.html();
    
    // Get form data - include libAccess and changePass
    const formData = {
        request: "addUser",
        empID: $('#u_empID').val(),
        email: $('#u_email').val(),
        name: $('#u_name').val(),
        roleID: $('#u_role').val(),
        officeID: $('#u_unit').val(),
        positionID: $('#u_position').val(),
        changePass: $('#u_changepass').is(':checked') ? 1 : 0  // Add this line
    };
    
    // Validation
    if (!formData.empID || !formData.name || !formData.roleID) {
        alert('Please fill in required fields');
        return;
    }
    
    // Disable button
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    // Send request
    $.post("backend/bk_usermanagement.php", formData, function(response) {
        btn.prop('disabled', false).html(originalText);
        
        if (response === "SUCCESS") {
            $('#usermodal').modal('hide');
            $('#addUserForm')[0]?.reset();
            loadUsers('all');
        } else if (response === "DUPLICATE") {
            alert('User already exists');
        }
    });
});
    
    // Update user
$(document).off('click.user', '#btnUpdateUser').on('click.user', '#btnUpdateUser', function(e) {
    e.preventDefault();
    const btn = $(this);
    const originalText = btn.html();
    
    // Get form data - include libAccess
    const formData = {
        request: "updateUser",
        userID: $('#edit_userID').val(),
        empID: $('#edit_empID').val(),
        email: $('#edit_email').val(),
        name: $('#edit_name').val(),
        roleID: $('#edit_role').val(),
        officeID: $('#edit_office').val(),
        positionID: $('#edit_position').val(),
        isActive: $('#edit_status').val(),
        allOfficeAccess: $('#edit_alloffice').val(),
        changePass: $('#edit_changepass').val(),
        libAccess: $('#edit_libAccess').val()  // Add this line
    };
    
    // Validation
    if (!formData.empID || !formData.name || !formData.roleID) {
        alert('Please fill in required fields');
        return;
    }
    
    // Disable button
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    // Send request
    $.post("backend/bk_usermanagement.php", formData, function(response) {
        btn.prop('disabled', false).html(originalText);
        
        if (response === "SUCCESS") {
            $('#usereditmodal').modal('hide');
            loadUsers('all');
            loadUsers('deleted');
        }
    });
});
    
    // Prevent form submission on Enter
    $(document).off('submit.user', '#addUserForm').on('submit.user', '#addUserForm', function(e) {
        e.preventDefault();
        return false;
    });
}
// Toggle user status
// Toggle user status
$(document).off('change.user', '.toggleUserStatus').on('change.user', '.toggleUserStatus', function() {
    const userId = $(this).data('userid');
    const isChecked = $(this).is(':checked');
    const newStatus = isChecked ? 0 : 1; // 0 = Active, 1 = Inactive
    
    // Don't show confirm dialog for deleted users (they should be disabled anyway)
    if ($(this).is(':disabled')) {
        return;
    }
    
    if (confirm(`Are you sure you want to ${isChecked ? 'activate' : 'deactivate'} this user?`)) {
        $.post("backend/bk_usermanagement.php", {
            request: "toggleUserStatus",
            userID: userId,
            status: newStatus
        }, function(response) {
            // Parse JSON response
            try {
                const data = JSON.parse(response);
                if (data.success) {
                    // Show success message
                    showToast('User status updated successfully!', 'success');
                } else {
                    // Revert the switch if failed
                    $(this).prop('checked', !isChecked);
                    alert("Error: " + data.message);
                }
            } catch (e) {
                console.error("Error parsing response:", e);
                $(this).prop('checked', !isChecked);
                alert("Server error. Please try again.");
            }
        }).fail(function() {
            $(this).prop('checked', !isChecked);
            alert("Server error. Please try again.");
        });
    } else {
        // Revert if user cancels
        $(this).prop('checked', !isChecked);
    }
});
</script>