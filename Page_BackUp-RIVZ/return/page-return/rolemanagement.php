<div class="container-fluid mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="font-weight-bold text-dark">
      <i class="fas fa-user-tag mr-2 text-primary"></i> Role Access Menu
    </h4>

    <div style="width: 250px;">
      <select id="prvroleSelect" class="form-control form-control-sm">
        <option value="">-- Select Role --</option>
        <option value="admin">Administrator</option>
        <option value="manager">Manager</option>
        <option value="staff">Staff</option>
      </select>
    </div>
  </div>

  <!-- Card -->
  <div class="card card-primary card-outline">
    <div class="card-body p-0">

      <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0 align-middle">
          <thead class="thead-light">
            <tr>
              <th style="width: 50px;">#</th>
              <th>Menu</th>
              <th>Description</th>
              <th class="text-center" style="width: 120px;">Access</th>
			  <th>Action</th>
            </tr>
          </thead>
          <tbody id="tblviewMenus">
            <tr>
              <td>1</td>
              <td>
                <i class="fas fa-home mr-2"></i>
                <b>Dashboard</b>
              </td>
              <td>Main overview page</td>
              <td class="text-center">
                <div class="custom-control custom-switch">
                  <input class="custom-control-input toggle-switch"
                         type="checkbox"
                         id="menu1"
                         checked>
                  <label class="custom-control-label" for="menu1"></label>
                </div>
              </td>
            </tr>

            <tr>
              <td>2</td>
              <td>
                <i class="fas fa-users mr-2"></i>
                <b>User Management</b>
              </td>
              <td>Add, edit, and remove users</td>
              <td class="text-center">
                <div class="custom-control custom-switch">
                  <input class="custom-control-input toggle-switch"
                         type="checkbox"
                         id="menu2">
                  <label class="custom-control-label" for="menu2"></label>
                </div>
              </td>
            </tr>

            <tr>
              <td>3</td>
              <td>
                <i class="fas fa-cogs mr-2"></i>
                <b>Settings</b>
              </td>
              <td>System configurations</td>
              <td class="text-center">
                <div class="custom-control custom-switch">
                  <input class="custom-control-input toggle-switch"
                         type="checkbox"
                         id="menu3"
                         checked>
                  <label class="custom-control-label" for="menu3"></label>
                </div>
              </td>
            </tr>

          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>



<script>
  // Load Menus into table
  // getrule();


 // TableLoader.load({
    // tableId: "#tblviewMenus",
    // url: "backend/bk_privilegemanagement.php",
    // request: "displayRole"
  // });
  
   $.ajax({
        type: "POST",
        url: "backend/bk_rolemanagement.php",
        data: {
            request: "viewRoles"
        },
        beforeSend: function() {
            $("#loadingSpinner").show();
        },
        success: function(dataResult) {
            $("#loadingSpinner").hide();
            $("#tblviewMenus").html(dataResult);
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").hide();
            console.error("AJAX error:", error);
            alert("Error loading users. Please try again.");
        }
    });

</script>