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
    <th class="text-center">Status</th>          
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
//  INITIAL LOAD 
$(document).ready(function() {
    loadRoles();
});

//  LOAD ROLES 
function loadRoles() {
    loadTable("backend/bk_rolemanagement.php", "viewRoles", "#tblviewRoles");
}

//  ADD ROLE MODAL 
$(document).on('click', '#addRoleModal', function() {
    openAddModal("page/modals.php", "rolemodal");
});

//  ADD ROLE (FIXED - Simple & Clean) 
$(document).on('click', '#r_submit', function(e) {
    e.preventDefault();
    e.stopPropagation(); // Prevent multiple clicks
    
    // Disable button immediately to prevent multiple submissions
    const $btn = $(this);
    if ($btn.prop('disabled')) return; // Already processing
    
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
    
    const formData = {
        request: "addRole",
        r_role: $("#r_role").val().trim(),
        r_rolecode: $("#r_rolecode").val().trim(),
        r_status: $("#r_status").val() || 0
    };
    
    if (!validateRoleForm(formData)) {
        $btn.prop('disabled', false).html('Add Role');
        return;
    }
    
    $.ajax({
        type: "POST",
        url: "backend/bk_rolemanagement.php",
        data: formData,
        dataType: "json",
        success: function(response) {
            // Re-enable button
            $btn.prop('disabled', false).html('Add Role');
            
            if (response.status === "success") {
                // Close modal FIRST
                $("#rolemodal").modal("hide");
                
                // Clear form
                $("#r_role, #r_rolecode").val("");
                $("#r_status").val("0");
                
                // Add new row to table
                if (response.rowHtml) {
                    $("#tblviewRoles").append(response.rowHtml);
                    highlightRow($("#tblviewRoles tr:last-child"));
                }
                
                // Simple alert (won't duplicate)
                setTimeout(() => {
                    alert(" Role added successfully!");
                }, 300);
                
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function(xhr, status, error) {
            $btn.prop('disabled', false).html('Add Role');
            alert("Connection error: " + error);
        }
    });
});

//  EDIT ROLE 
$(document).on('click', '.btnEditRole', function() {
    const roleID = $(this).data('id');
    openEditModal("page/modals.php", "roleeditmodal", "roleID", roleID);
});

//  UPDATE ROLE (Simplified) 
$(document).on('click', '#btnUpdateRole', function() {
    const $button = $(this);
    if ($button.prop('disabled')) return;
    
    const formData = {
        er_submit: $("#edit_roleID").val(),
        er_role: $("#edit_role").val().trim(),
        er_rolecode: $("#edit_rolecode").val().trim(),
        er_status: $("#edit_role_status").val()
    };
    
    if (!validateRoleForm(formData)) return;
    
    $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    $.ajax({
        type: "POST",
        url: "backend/bk_rolemanagement.php",
        data: { 
            request: "updateRole",
            ...formData 
        },
        success: function(response) {
            $button.prop('disabled', false).html('Save Changes');
            
            if (response.trim() === "SUCCESS") {
                // Update row
                updateRoleRow(formData);
                
                // Close modal
                $('.modal').modal('hide');
                
                // Simple alert
                setTimeout(() => {
                    alert("Role updated successfully!");
                }, 300);
                
            } else {
                alert("Error: " + response);
            }
        },
        error: function(xhr, status, error) {
            $button.prop('disabled', false).html('Save Changes');
            alert("Error saving: " + error);
        }
    });
});

//  TOGGLE ROLE STATUS 
$(document).on('change', '.toggleRoleStatus', function() {
    const $checkbox = $(this);
    if ($checkbox.prop('disabled')) return;
    
    const roleID = $checkbox.data('id');
    const isActive = $checkbox.is(':checked');
    const newStatus = isActive ? 0 : 1;
    const actionText = isActive ? 'activate' : 'deactivate';
    
    if (confirm(`Are you sure you want to ${actionText} this role?`)) {
        $checkbox.prop('disabled', true);
        
        $.ajax({
            url: "backend/bk_rolemanagement.php",
            method: "POST",
            dataType: "json",
            data: {
                request: "toggleRoleStatus",
                RID: roleID,
                status: newStatus
            },
            success: function(response) {
                $checkbox.prop('disabled', false);
                
                if (response.status === "success") {
                    // Just highlight the row, no status text to update
                    const $row = $(`tr[data-role-id="${roleID}"]`);
                    highlightRow($row);
                } else {
                    $checkbox.prop('checked', !isActive);
                    alert("Update failed!");
                }
            },
            error: function() {
                $checkbox.prop('disabled', false);
                $checkbox.prop('checked', !isActive);
                alert("Error updating status!");
            }
        });
    } else {
        $checkbox.prop('checked', !isActive);
    }
});

//  HELPER FUNCTIONS 

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

// Update existing role row
function updateRoleRow(data) {
    const roleID = data.er_submit;
    const $row = $(`tr[data-role-id="${roleID}"]`);
    
    if ($row.length) {
        $row.find("td:eq(1)").text(data.er_role);      // Role Name
        $row.find("td:eq(2)").text(data.er_rolecode);  // Role Code
        
        // Update the toggle switch status (column index changed from 4 to 3)
        const $checkbox = $row.find('.toggleRoleStatus');
        $checkbox.prop('checked', data.er_status == 0);
        
        highlightRow($row);
    } else {
        loadRoles();
    }
}

// Update role status text
/* function updateRoleStatus(roleID, statusText) {
    const $row = $(`tr[data-role-id="${roleID}"]`);
    
    if ($row.length) {
        $row.find("td:eq(3)").text(statusText);
        highlightRow($row);
    }
} */

// Highlight row
function highlightRow($row) {
    $row.addClass("table-success");
    setTimeout(() => $row.removeClass("table-success"), 1500);
}
</script>