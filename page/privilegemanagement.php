<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="font-weight-bold text-dark">
            <i class="fas fa-user-shield mr-2 text-primary"></i> Privilege Management
        </h4>
        
        <div style="width: 300px;">
            <select id="prvroleSelect" class="form-control form-control-sm">
                <option value="">-- All Roles --</option>
            </select>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        Shows all roles and menus by default. Select a specific role to filter.
    </div>

    <!-- Privilege Table -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-list mr-2"></i> Role & Menu Access
            </h5>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Menu ID</th>
                            <th>Menu</th>
                            <th>Description</th>
                            <th width="15%" class="text-center">Access</th>
                        </tr>
                    </thead>
                    <tbody id="privilegeTableBody">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Loading all roles and menus...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // Load role dropdown
    $.post("backend/bk_privilegemanagement.php", { request: "GetRole" }, function(html) {
        $("#prvroleSelect").html(html);
    });

    // Load default view
    loadAllRolesAndMenus();

    // Role select change
    $(document).on('change', '#prvroleSelect', function() {
        const roleID = $(this).val();

        if (!roleID) {
            loadAllRolesAndMenus();
        } else {
            loadPrivTable("backend/bk_privilegemanagement.php", "showtblData", "#privilegeTableBody", roleID);
        }
    });

    // Toggle privilege — individual only, no cascade
    $(document).on('change', '.togglePrivilege', function() {
        const $switch   = $(this);
        const menuID    = $switch.data('menid');
        const isChecked = $switch.is(':checked');
        const roleID    = $switch.data('roleid') || $switch.closest('tr').data('roleid');

        if (!roleID) {
            showToast("Select a specific role to update", "warning");
            $switch.prop('checked', !isChecked);
            return;
        }

        $switch.prop('disabled', true);

        $.post("backend/bk_privilegemanagement.php", {
            request: "UpdatePrivilege",
            RID:     roleID,
            MenID:   menuID,
            status:  isChecked ? "0" : "1"
        })
        .done(function(response) {
            $switch.prop('disabled', false);

            if (response.trim() !== "SUCCESS") {
                showToast("Update failed", "error");
                $switch.prop('checked', !isChecked);
                return;
            }

            $switch.closest('tr').addClass('table-success');
            setTimeout(() => $switch.closest('tr').removeClass('table-success'), 1000);

            if (UserInfo["RID"] == roleID) fetchSidebarMenu(roleID, true);
        })
        .fail(function() {
            $switch.prop('disabled', false);
            showToast("Network error", "error");
            $switch.prop('checked', !isChecked);
        });
    });

});




</script>