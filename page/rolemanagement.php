<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="font-weight-bold text-dark">
            <i class="fas fa-user-tag mr-2 text-primary"></i> Role Management
        </h4>
        <button class="btn btn-primary" id="addRoleModal">
            <i class="fas fa-plus mr-1"></i> Add Role
        </button>
    </div>
    
    <!-- Table Card -->
    <div class="card card-primary card-outline">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Role Name</th>
                            <th>Role Code</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tblviewRoles">
                        <!-- Table loads here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'modalContainer.php'; ?>

<script>
// ============ INITIAL LOAD ============
$(document).ready(function() {
    loadRoles();
});

// ============ LOAD ROLES ============
function loadRoles() {
    loadTable("backend/bk_rolemanagement.php", "viewRoles", "#tblviewRoles");
}

// ============ ADD ROLE MODAL ============
$(document).on('click', '#addRoleModal', function() {
    openAddModal("page/modals.php", "rolemodal");
});

// ============ ADD ROLE ============
$(document).on('click', '#r_submit', function(e) {
    e.preventDefault();
    
    const formData = {
        request: "addRole",
        r_role: $("#r_role").val().trim(),
        r_rolecode: $("#r_rolecode").val().trim(),
        r_status: $("#r_status").val() || 0
    };
    
    if (!validateRoleForm(formData)) return;
    
    const $btn = $(this).loading(true);
    
    $.ajax({
        type: "POST",
        url: "backend/bk_rolemanagement.php",
        data: formData,
        dataType: "json",
        success: (response) => handleAddRoleResponse(response, $btn),
        error: (xhr, status, error) => handleAjaxError(error, $btn)
    });
});

// ============ EDIT ROLE ============
$(document).on('click', '.btnEditRole', function() {
    const roleID = $(this).data('id');
    openEditModal("page/modals.php", "roleeditmodal", "roleID", roleID);
});

// ============ UPDATE ROLE ============
$(document).on('click', '#btnUpdateRole', function() {
    const formData = {
        er_submit: $("#edit_roleID").val(),
        er_role: $("#edit_role").val().trim(),
        er_rolecode: $("#edit_rolecode").val().trim(),
        er_status: $("#edit_role_status").val()
    };
    
    if (!validateRoleForm(formData)) return;
    
    saveData.call(
        this,
        "backend/bk_rolemanagement.php",
        "updateRole",
        formData,
        (updatedData) => updateRoleRow(updatedData.er_submit, updatedData)
    );
});

// ============ HELPER FUNCTIONS ============

// Validate role form
function validateRoleForm(data) {
    const roleName = data.r_role || data.er_role;
    const roleCode = data.r_rolecode || data.er_rolecode;
    
    if (!roleName || !roleCode) {
        alert("Role name and code are required!");
        return false;
    }
    return true;
}

// Handle add role response
function handleAddRoleResponse(response, $btn) {
    $btn.loading(false);
    
    if (response.status === "success") {
        $("#rolemodal").modal("hide");
        alert(response.message);
        
        // Add new row to table
        if (response.rowHtml) {
            $("#tblviewRoles").append(response.rowHtml);
            highlightRow($("#tblviewRoles tr:last-child"));
        }
        
        // Clear form
        $("#r_role, #r_rolecode").val("");
        $("#r_status").val("0");
        
    } else {
        alert(response.message);
    }
}

// Update existing role row
function updateRoleRow(roleID, data) {
    const $row = $(`tr[data-role-id="${roleID}"]`);
    
    if ($row.length) {
        $row.find("td:eq(1)").text(data.er_role);
        $row.find("td:eq(2)").text(data.er_rolecode);
        $row.find("td:eq(3)").text(data.er_status == 0 ? "Active" : "Inactive");
        
        highlightRow($row);
    } else {
        // If row not found, reload table
        loadRoles();
    }
}

// Highlight row temporarily
function highlightRow($row) {
    $row.addClass("table-success");
    setTimeout(() => $row.removeClass("table-success"), 1500);
}

// Loading button helper
$.fn.loading = function(isLoading) {
    if (isLoading) {
        return this.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
    } else {
        return this.html('Add Role').prop('disabled', false);
    }
};

// Handle AJAX error
function handleAjaxError(error, $btn) {
    $btn.loading(false);
    alert("Connection error: " + error);
}
</script>