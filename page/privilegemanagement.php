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
    // Load roles on page load
    getrule();
    
    // Load all roles and menus by default
    loadAllRolesAndMenus();
    
    // When role is selected
    $(document).on('change', '#prvroleSelect', function() {
        var roleID = $(this).val();
        
        if (!roleID) {
            loadAllRolesAndMenus();
        } else {
            loadPrivTable("backend/bk_privilegemanagement.php", "showtblData", "#privilegeTableBody", roleID);
        }
    });
    
// In page/privilegemanagement.php script
$(document).on('change', '.togglePrivilege', function() {
    var $switch = $(this);
    var roleID = $switch.data('roleid');
    var menuID = $switch.data('menid');
    var isChecked = $switch.is(':checked');
    
    if (!roleID) {
        roleID = $switch.closest('tr').data('roleid');
        if (!roleID) {
            showToast("Select a specific role to update", "warning");
            $switch.prop('checked', !isChecked);
            return;
        }
    }
    
    // Disable switch during update
    $switch.prop('disabled', true);
    
    $.ajax({
        url: "backend/bk_privilegemanagement.php",
        method: "POST",
        data: {
            request: "UpdatePrivilege",
            RID: roleID,
            MenID: menuID,
            status: isChecked ? "0" : "1"
        },
        success: function(response) {
            $switch.prop('disabled', false);
            
            if (response.trim() === "SUCCESS") {
                // Visual feedback
                $switch.closest('tr').addClass('table-success');
                setTimeout(() => $switch.closest('tr').removeClass('table-success'), 1000);
                
                // Only refresh sidebar if current user is affected
                if (UserInfo["RID"] == roleID) {
                    refreshCurrentUserSidebar();
                }
            } else {
                showToast("Update failed", "error");
                $switch.prop('checked', !isChecked);
            }
        },
        error: function() {
            $switch.prop('disabled', false);
            showToast("Network error", "error");
            $switch.prop('checked', !isChecked);
        }
    });
});
    showToast(message, type = "info");
/* 	function showToast(message, type = "info") {
    // Create toast element
    var $toast = $(`
        <div class="sidebar-toast" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} mr-2"></i>
                ${message}
            </div>
        </div>
    `);
    
    $("body").append($toast);
    
    setTimeout(function() {
        $toast.fadeOut(300, function() { $(this).remove(); });
    }, 3000);
} */
	
loadAllRolesAndMenus();
});




</script>