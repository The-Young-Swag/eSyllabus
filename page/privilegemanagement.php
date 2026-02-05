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
            loadTable("backend/bk_privilegemanagement.php", "showtblData", "#privilegeTableBody", roleID);
        }
    });
    
    // In page/privilegemanagement.php script, change the toggle handler:
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
    
	function showToast(message, type = "info") {
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
}
	
    function loadAllRolesAndMenus() {
        $.ajax({
            url: "backend/bk_privilegemanagement.php",
            method: "POST",
            data: { request: "showAllRolesAndMenus" },
            beforeSend: function() {
                $("#privilegeTableBody").html(`
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-success mr-2"></div>
                            Loading...
                        </td>
                    </tr>
                `);
            },
            success: function(data) {
                $("#privilegeTableBody").html(data);
            },
            error: function() {
                $("#privilegeTableBody").html(`
                    <tr>
                        <td colspan="4" class="text-center text-danger py-4">
                            Error loading data
                        </td>
                    </tr>
                `);
            }
        });
    }
});

function getrule() {
    $.ajax({
        url: "backend/bk_privilegemanagement.php",
        method: "POST",
        data: { request: 'GetRole' },
        beforeSend: function() {
            $("#loadingSpinner").show();
        },
        success: function(data) {
            $("#loadingSpinner").hide();
            $("#prvroleSelect").html(data);
        },
        error: function() {
            $("#loadingSpinner").hide();
            $("#prvroleSelect").html('<option value="">Error loading roles</option>');
        }
    });
}

function loadTable(url, request, target, roleID = "") {
    $.ajax({
        url: url,
        method: "POST",
        data: { request: request, RID: roleID },
        beforeSend: function() {
            $(target).html(`
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-success mr-2"></div>
                        Loading...
                    </td>
                </tr>
            `);
        },
        success: function(data) {
            $(target).html(data);
        },
        error: function() {
            $(target).html(`
                <tr>
                    <td colspan="4" class="text-center text-danger py-4">
                        Error loading data
                    </td>
                </tr>
            `);
        }
    });
}

// NEW FUNCTION: Refresh sidebar for users with the given role
function refreshSidebarForRole(roleID) {
    $.ajax({
        url: "backend/bk_privilegemanagement.php",
        method: "POST",
        data: { 
            request: "RefreshSidebar",
            RID: roleID
        },
        success: function(response) {
            console.log("Sidebar refresh triggered for role: " + roleID);
            
            // If current user has this role, refresh their sidebar
            if (UserInfo["RID"] == roleID) {
                refreshCurrentUserSidebar();
            }
        },
        error: function() {
            console.log("Sidebar refresh failed");
        }
    });
}

// NEW FUNCTION: Refresh current user's sidebar menu only
function refreshCurrentUserSidebar() {
    $.ajax({
        type: "POST",
        url: "backend/bk_privilegemanagement.php",
        data: {
            request: "GetUserMenu",
            RID: UserInfo["RID"]
        },
        success: function(htmlResult) {
            // Replace only the menu container content
            $("#sidebarMenuContainer").html(htmlResult);
            
            // Re-apply menu highlighting
            setupMenuHighlighting();
            
            // Show subtle notification
            showSidebarNotification();
        },
        error: function() {
            console.log("Failed to refresh sidebar menu");
        }
    });
}

// Update helper function to setup highlighting
function setupMenuHighlighting() {
    var currentPath = window.location.pathname.toLowerCase();
    
    $("#sidebarMenuContainer .nav-link").each(function () {
        var $link = $(this);
        var url = $link.attr("href");
        
        if (!url || url === "#") return;
        
        if (currentPath.includes(url.toLowerCase())) {
            $link.addClass("active");
            $link.closest(".nav-item").addClass("menu-open");
            $link.closest(".nav-treeview").css("display", "block");
        }
    });
}

// NEW FUNCTION: Show sidebar update notification
function showSidebarNotification() {
    // Create notification element
    var $notification = $(`
        <div class="sidebar-notification alert alert-success alert-dismissible fade show" role="alert" 
             style="position: fixed; top: 70px; right: 20px; z-index: 9999; max-width: 300px;">
            <i class="fas fa-sync-alt mr-2"></i>
            Sidebar menus updated
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    // Add to page
    $("body").append($notification);
    
    // Auto-remove after 3 seconds
    setTimeout(function() {
        $notification.alert('close');
    }, 3000);
}
</script>