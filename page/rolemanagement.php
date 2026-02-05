<div class="container-fluid mt-4">

  <!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

    <!-- Left -->
    <h4 class="font-weight-bold text-dark mb-0">
        <i class="fas fa-user-tag mr-2 text-primary"></i> Role Access Menu
    </h4>

    <!-- Right -->
    <div class="d-flex align-items-center">

        <select id="prvroleSelect"
                class="form-control form-control-sm mr-3"
                style="width: 200px;">
            <option value="">-- Select Role --</option>
            <option value="admin">Administrator</option>
            <option value="manager">Manager</option>
            <option value="staff">Staff</option>
        </select>

        <button type="button"
                class="btn btn-primary btn-sm"
                id="addRoleModal">
            <i class="fas fa-plus mr-1"></i> Add Role
        </button>

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
<?php include 'modalContainer.php'; ?>


<script>
// ============================================
// 1. LOAD INITIAL TABLE
// ============================================
/* function loadRoleTable() {
    $.ajax({
        type: "POST",
        url: "backend/bk_rolemanagement.php",
        data: { request: "viewRoles" },
        beforeSend: function() {
            $("#loadingSpinner").show();
        },
        success: function(dataResult) {
            $("#loadingSpinner").hide();
            $("#tblviewMenus").html(dataResult);
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").hide();
            console.error("AJAX error:", error);
        }
    });
} */

// Initial load
$(document).ready(function() {
	loadTable("backend/bk_rolemanagement.php", "viewRoles", "#tblviewMenus");
});

$(document).on('click', '#addRoleBtn', function () {
    openAddModal("page/modals.php", "rolemodal");
});

// Edit Role Modal
$(document).on('click', '.btnEditRole', function() {
    openEditModal("page/modals.php", "roleeditmodal", "roleID", $(this).data('id'));
});

$(document).on('click', '#btnUpdateRole', function () {
    const formData = {
        er_submit: $("#edit_roleID").val(),
        er_role: $("#edit_role").val().trim(),
        er_rolecode: $("#edit_rolecode").val().trim(),
        er_status: $("#edit_role_status").val()
    };
    
    if (!formData.er_role || !formData.er_rolecode) {
        alert("Role name and code are required!");
        return;
    }
    
    saveData.call(
        this,
        "backend/bk_addrole.php",
        "saverole",
        formData,
        function(updatedData) {
            // Update only the edited role row
            updateRoleRow(updatedData.er_submit, updatedData);
        }
    );
});

function updateRoleRow(roleID, updatedData) {
    const $row = $("tr[data-role-id='" + roleID + "']");
    
    if ($row.length) {
        const $cells = $row.find('td');
        $cells.eq(1).text(updatedData.er_role);
        $cells.eq(2).text(updatedData.er_rolecode);
        $cells.eq(3).text(updatedData.er_status == 0 ? "Active" : "Inactive");
    } else {
        loadTable("backend/bk_rolemanagement.php", "viewRoles", "#tblviewMenus");
    }
}


// 6. ADD ROLE FUNCTIONALITY (Optional)
$(document).on('click', '#addRoleModal', function () {
    $.ajax({
        type: "POST",
        url: "page/modals.php",
        data: { request: "rolemodal" },
        beforeSend: function () {
            $("#loadingSpinner").fadeIn(200);
        },
        success: function (html) {
            $("#loadingSpinner").fadeOut(200);
            
            // Cleanup
            if ($("#rolemodal").length) {
                $("#rolemodal").remove();
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            $("body").append(html);
            $("#rolemodal").modal("show");
        }
    });
});

$(document).on('click', '#r_submit', function (e) {
    e.preventDefault();
    
    const roleData = {
        request: "addrole",
        r_role: $("#r_role").val().trim(),
        r_rolecode: $("#r_rolecode").val().trim(),
        r_status: $("#r_status").val()
    };
    
    if (!roleData.r_role || !roleData.r_rolecode) {
        alert("Please fill in both fields!");
        return;
    }
    
    const $button = $(this);
    $button.prop('disabled', true).text('Adding...');
    
    $.ajax({
        type: "POST",
        url: "backend/bk_addrole.php",
        data: roleData,
        success: function(response) {
            $button.prop('disabled', false).text('Add Role');
            
            if (response.includes("success")) {
                alert("Role added successfully!");
                $("#rolemodal").modal("hide");
                loadTable(); // Reload table for new role
            } else {
                alert("Error: " + response);
            }
        }
    });
});

function updateSingleRow(roleID, roleName, roleCode, roleStatus) {
    const $row = $("tr[data-role-id='" + roleID + "']");
    
    if ($row.length) {
        // Highlight the row briefly to show it was updated
        $row.addClass('table-success');
        
        // Update cells
        const $cells = $row.find('td');
        $cells.eq(1).text(roleName);
        $cells.eq(2).text(roleCode);
        $cells.eq(3).text(roleStatus == 0 ? "Active" : "Inactive");
        
        // Remove highlight after 1.5 seconds
        setTimeout(function() {
            $row.removeClass('table-success');
        }, 1500);
    }
}
</script>