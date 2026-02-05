<div class="container-fluid mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="font-weight-bold text-dark">
      <i class="fas fa-user-shield mr-2 text-primary"></i> Privilege Management
    </h4>
	    <div style="width: 250px;">
      <select id="prvroleSelect" class="form-control form-control-sm">
        <option value="">-- Select Role --</option>
        <option value="admin">Administrator</option>
        <option value="manager">Manager</option>
        <option value="staff">Staff</option>
      </select>
    </div>
	
    <button class="btn btn-success" data-toggle="modal" data-target="#mdlPrivilegeAdd">
      <i class="fas fa-plus"></i> Add Privilege
    </button>
	
  </div>

  <!-- Tabs Card -->
  <div class="card card-primary card-outline">
    <div class="card-header p-0 border-bottom-0">
      <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" data-toggle="pill" href="#activePrivileges">
            <i class="fas fa-check-circle mr-1"></i> Active Privileges
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-toggle="pill" href="#deletedPrivileges">
            <i class="fas fa-trash mr-1"></i> Deleted Privileges
          </a>
        </li>
      </ul>
    </div>

    <div class="card-body">
      <div class="tab-content">

        <!-- ================= ACTIVE PRIVILEGES ================= -->
        <div class="tab-pane fade show active" id="activePrivileges">
          <div class="table-responsive">
            <table class="table table-hover table-bordered" id="tblActivePrivileges">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Privilege</th>
                  <th class="text-center">Unactive</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="privTable">
                <tr>
                  <td>1</td>
                  <td>Administrator</td>
                  <td class="text-center">
                    <div class="custom-control custom-switch">
                      <input type="checkbox"
                             class="custom-control-input togglePrivilegeStatus"
                             id="privStatus1"
                             data-id="1">
                      <label class="custom-control-label" for="privStatus1"></label>
                    </div>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#mdlPrivilegeEdit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btnDeletePrivilege" data-id="1">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ================= DELETED PRIVILEGES ================= -->
        <div class="tab-pane fade" id="deletedPrivileges">
          <div class="alert alert-warning">
            <i class="fas fa-info-circle mr-1"></i>
            Deleted privileges can be restored at any time.
          </div>

          <div class="table-responsive">
            <table class="table table-hover table-bordered" id="tblDeletedPrivileges">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Privilege</th>
                  <th>Deleted At</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>2</td>
                  <td>Accounting</td>
                  <td>2026-01-15</td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-warning btnRestorePrivilege" data-id="2">
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
  // Load Menus into table
  $.ajax({
        type: "POST",
        url: "backend/bk_privilegemanagement.php",
        data: {
            request: "showtblData"
        },
        beforeSend: function() {
            $("#loadingSpinner").show();
        },
        success: function(dataResult) {
            $("#loadingSpinner").hide();
            $("#privTable").html(dataResult);
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").hide();
            console.error("AJAX error:", error);
            alert("Error loading users. Please try again.");
        }
    });
  
/*   getrule();
  TableLoader.load({
    tableId: "#tblviewMenus",
    url: "backend/bk_privilegemanagement.php",
    request: "showtblData"
  }); */
  
  // Toggle user active/inactive
  $(document).on('change', '.toggleStatus', function() {
    const checkbox = $(this);
    const userId = checkbox.data('id');
    const newStatus = checkbox.is(':checked') ? 'active' : 'inactive';

    let confirmMsg = newStatus === 'active' 
      ? 'Are you sure you want to activate this user?' 
      : 'Are you sure you want to deactivate this user?';

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