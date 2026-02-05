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
          <a class="nav-link" id="deleted-tab" data-toggle="pill" href="#deletedUsers" role="tab">
            <i class="fas fa-user-slash mr-1"></i> Deleted Users
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
                <!-- Users will be loaded here via AJAX -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- ================= DELETED USERS ================= -->
        <div class="tab-pane fade" id="deletedUsers" role="tabpanel">
          <div class="alert alert-warning">
            <i class="fas fa-info-circle mr-1"></i>
            Deleted users can be restored at any time.
          </div>
          <div class="table-responsive">
            <table class="table table-hover table-bordered">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Employee Name</th>
                  <th>Office</th>
                  <th>Position</th>
                  <th>Email</th>
                  <th>Deleted At</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="deletedUserTable">
                <!-- Deleted users will be loaded here -->
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



// Password toggle function
/* function togglePassword(userId) {
    const passwordInput = document.getElementById('password_' + userId);
    const eyeIcon = document.getElementById('eye_' + userId);
    const btn = document.getElementById('btn_' + userId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.className = 'fas fa-eye-slash';
        btn.setAttribute('data-original-title', 'Hide Password');
    } else {
        passwordInput.type = 'password';
        eyeIcon.className = 'fas fa-eye';
        btn.setAttribute('data-original-title', 'Show Password');
    }
    
    // Update Bootstrap tooltip
    $(btn).tooltip('dispose').tooltip();
} */

// Load users function

$(document).ready(function() {
	loadTable("backend/bk_usermanagement.php", "viewUsers", "#userTable");
});

// Add Menu Modal
$(document).on('click', '#btnAddEmployee', function () {
    openAddModal("page/modals.php", "usermodal");
});

//Open Edit Modal
$(document).on('click', '.btnEditUser', function() {
    const userID = $(this).data('id'); // Make sure your button has data-id attribute
    openEditModal("page/modals.php", "usereditmodal", "userId", userID);
});






// Document ready
/* $(document).ready(function() {
    // Load active users on page load
    loadUsers();
    
    // Load deleted users when tab is clicked
    $('#deleted-tab').on('click', function() {
        loadDeletedUsers();
    });
    
    // Handle status toggle
    $(document).on('change', '.toggleStatus', function() {
        const checkbox = $(this);
        const userId = checkbox.data('id');
        const isActive = checkbox.is(':checked') ? 0 : 1;
        
        let confirmMsg = isActive ?
            'Are you sure you want to activate this user?' :
            'Are you sure you want to deactivate this user?';
        
        if (confirm(confirmMsg)) {
            $.ajax({
                type: "POST",
                url: "backend/bk_usermanagement.php",
                data: {
                    request: "Update",
                    userId: userId,
                    chval: isActive,
                    updateField: "IsActive"
                },
                success: function(response) {
                    // Reload the user list
                    loadUsers();
                    alert("User status updated successfully!");
                },
                error: function() {
                    alert("Error updating user status.");
                    // Revert the checkbox
                    checkbox.prop('checked', !checkbox.is(':checked'));
                }
            });
        } else {
            // Revert toggle if canceled
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
    
    
}); */
</script>