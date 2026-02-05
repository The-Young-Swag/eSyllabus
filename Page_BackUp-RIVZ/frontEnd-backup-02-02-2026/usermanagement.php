<div class="container-fluid mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="font-weight-bold text-dark">
      <i class="fas fa-users mr-2 text-primary"></i> User Management
    </h4>
    <button class="btn btn-success" data-toggle="modal" data-target="#mdlEmployeeAdd">
      <i class="fas fa-user-plus"></i> Add Employee
    </button>
  </div>

  <!-- Tabs Card -->
  <div class="card card-primary card-outline">
    <div class="card-header p-0 border-bottom-0">
      <ul class="nav nav-tabs" id="userTabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="active-tab" data-toggle="pill"
            href="#activeUsers" role="tab">
            <i class="fas fa-user-check mr-1"></i> Active Users
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="deleted-tab" data-toggle="pill"
            href="#deletedUsers" role="tab">
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
                  <th>Employee Username</th>
				  <th>Password</th>
				  <th>Name</th>
				  <th>Office</th>
                  <th></th>
                  <th>Role</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="userTable">
                <!-- <tr>
                  <td>1</td>
                  <td>Juan Dela Cruz</td>
                  <td>HR Department</td>
                  <td>Manager</td>
                  <td>juan.delacruz@example.com</td>
                  <td class="text-center">
                    <div class="custom-control custom-switch">
                      <input type="checkbox" class="custom-control-input toggleStatus"
                        id="status1" data-id="1" checked>
                      <label class="custom-control-label" for="status1"></label>
                    </div>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#mdlEmployeeEdit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btnDeleteUser" data-id="1">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr> -->
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
              <tbody>
                <tr>
                  <td>2</td>
                  <td>Maria Santos</td>
                  <td>IT Department</td>
                  <td>Staff</td>
                  <td>maria.santos@example.com</td>
                  <td>2026-01-15</td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-warning btnRestoreUser" data-id="2">
                      <i class="fas fa-undo-alt mr-1"></i> Restore
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>



<script>
  $.ajax({
    type: "POST",
    url: "backend/bk_usermanagement.php",
    data: {
      request: "viewUsers"
    },

    beforeSend: function() {
      $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
    },

    success: function(dataResult) {
      $("#loadingSpinner").fadeOut(200).css("display", "none");
      $("#userTable").html(dataResult);
      // TableLoader.load({
      //   tableId: '#userTable',
      //   url: "backend/bk_usermanagment.php",
      //   request: "viewUsers"
      // })
    },
    error: function(xhr, status, error) {
      $("#loadingSpinner").fadeOut(200).css("display", "none");
      console.error("AJAX error:", error);
    }
  });

  // Toggle user active/inactive
  $(document).on('change', '.toggleStatus', function() {
    const checkbox = $(this);
    const userId = checkbox.data('id');
    const newStatus = checkbox.is(':checked') ? 'active' : 'inactive';

    let confirmMsg = newStatus === 'active' ?
      'Are you sure you want to activate this user?' :
      'Are you sure you want to deactivate this user?';

    if (confirm(confirmMsg)) {
      console.log(`Changing user ${userId} status to ${newStatus}`);
      // TODO: AJAX call to update status in backend

      // Optional: move row between tabs if desired
    } else {
      // revert toggle if canceled
      checkbox.prop('checked', !checkbox.is(':checked'));
    }
  });
</script>