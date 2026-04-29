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
$(document).ready(function() {
    loadRoles();
});

function loadRoles() {
    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
    $.post("backend/bk_rolemanagement.php", { request: "viewRoles" }, function(response) {
        $("#loadingSpinner").fadeOut(200).css("display", "none");
        $("#tblviewRoles").html(response);
    }).fail(function() {
        $("#loadingSpinner").fadeOut(200).css("display", "none");
    });
}

$(document).on('click', '#addRoleModal', function() {
    openAddModal("page/modals.php", "rolemodal");
});

$(document).on('click', '#r_submit', function(e) {
    e.preventDefault();
    e.stopPropagation();

    const $btn = $(this);
    if ($btn.prop('disabled')) return;

    const formData = {
        request: "addRole",
        r_role: $("#r_role").val().trim(),
        r_rolecode: $("#r_rolecode").val().trim(),
        r_status: $("#r_status").val() || 0
    };

    if (!formData.r_role || !formData.r_rolecode) {
        alert("Role Name and Role Code are required!");
        return;
    }

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);

    $.ajax({
        type: "POST",
        url: "backend/bk_rolemanagement.php",
        data: formData,
        dataType: "json",
        success: function(response) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
            $btn.prop('disabled', false).html('Add Role');

            if (response.status === "success") {
                $("#rolemodal").modal("hide");
                $("#r_role, #r_rolecode").val("");
                $("#r_status").val("0");
                loadRoles();
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
            $btn.prop('disabled', false).html('Add Role');
            alert("Connection error: " + error);
        }
    });
});

$(document).on('click', '.btnEditRole', function() {
    const roleID = $(this).data('id');
    openEditModal("page/modals.php", "roleeditmodal", "roleID", roleID);
});

$(document).on('click', '#btnUpdateRole', function() {
    const $button = $(this);
    if ($button.prop('disabled')) return;

    const formData = {
        er_submit: $("#edit_roleID").val(),
        er_role: $("#edit_role").val().trim(),
        er_rolecode: $("#edit_rolecode").val().trim(),
        er_status: $("#edit_role_status").val()
    };

    if (!formData.er_role || !formData.er_rolecode) {
        alert("Role Name and Role Code are required!");
        return;
    }

    $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);

    $.ajax({
        type: "POST",
        url: "backend/bk_rolemanagement.php",
        data: { request: "updateRole", ...formData },
        success: function(response) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
            $button.prop('disabled', false).html('Save Changes');

            if (response.trim() === "SUCCESS") {
                $('.modal').modal('hide');
                loadRoles();
            } else {
                alert("Error: " + response);
            }
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
            $button.prop('disabled', false).html('Save Changes');
            alert("Error saving: " + error);
        }
    });
});

$(document).on('change', '.toggleRoleStatus', function() {
    const $checkbox = $(this);
    if ($checkbox.prop('disabled')) return;

    const roleID     = $checkbox.data('id');
    const isActive   = $checkbox.is(':checked');
    const newStatus  = isActive ? 0 : 1;
    const actionText = isActive ? 'activate' : 'deactivate';

    if (confirm(`Are you sure you want to ${actionText} this role?`)) {
        $checkbox.prop('disabled', true);
        $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);

        $.ajax({
            url: "backend/bk_rolemanagement.php",
            method: "POST",
            dataType: "json",
            data: { request: "toggleRoleStatus", RID: roleID, status: newStatus },
            success: function(response) {
                $("#loadingSpinner").fadeOut(200).css("display", "none");
                $checkbox.prop('disabled', false);

                if (response.status === "success") {
                    loadRoles();
                } else {
                    $checkbox.prop('checked', !isActive);
                    alert("Update failed!");
                }
            },
            error: function() {
                $("#loadingSpinner").fadeOut(200).css("display", "none");
                $checkbox.prop('disabled', false);
                $checkbox.prop('checked', !isActive);
                alert("Error updating status!");
            }
        });
    } else {
        $checkbox.prop('checked', !isActive);
    }
});
</script>