<div class="container-fluid mt-3">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col">
            <h4 class="font-weight-bold">
                <i class="fas fa-bars mr-2 text-primary"></i> Menu Management
            </h4>
        </div>
        <div class="col-auto">
            <button class="btn btn-success" id="addModal">
                <i class="fas fa-plus mr-1"></i> Add Menu
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#allMenus">
                        <i class="fas fa-list mr-1"></i> All Menus
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#deletedMenus">
                        <i class="fas fa-trash mr-1"></i> Deleted Menus
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content">
                <!-- All Menus Tab -->
                <div class="tab-pane fade show active" id="allMenus">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th>Menu</th>
                                    <th width="8%">Mother ID</th>
                                    <th>Description</th>
                                    <th>Code</th>
                                    <th>Link</th>
                                    <th width="8%">Order</th>
                                    <th>Icon</th>
                                    <th width="8%">Status</th>
                                    <th width="12%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableAllMenus">
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Deleted Menus Tab -->
                <div class="tab-pane fade" id="deletedMenus">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th>Menu</th>
                                    <th width="8%">Mother ID</th>
                                    <th>Description</th>
                                    <th>Code</th>
                                    <th>Link</th>
                                    <th width="8%">Order</th>
                                    <th>Icon</th>
                                    <th width="8%">Status</th>
                                    <th width="12%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableDeletedMenus">
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'modalContainer.php'; ?>

<script>
// SIMPLE MENU MANAGEMENT - CLEAN VERSION (like user management)
$(document).ready(function() {
    // Load initial data
    loadMenus('all');
     loadMenus('deleted');  // also load deleted menus immediately
    // Setup event handlers
    setupMenuEvents();
});

// Load menus function
function loadMenus(type) {
    const tableId = type === 'all' ? '#tableAllMenus' : '#tableDeletedMenus';
    const request = type === 'all' ? 'getAllMenus' : 'getDeletedMenus';
    
    $.post("backend/bk_menumanagement.php", { request: request }, function(data) {
        $(tableId).html(data);
    });
}

// Update sidebar
function updateSidebar() {
    const currentRID = UserInfo["RID"] || 0;
    
    if (!currentRID) return;
    
    $.post("backend/bk_menumanagement.php", {
        request: "getSidebarMenu",
        RID: currentRID,
        userRID: currentRID
    }, function(html) {
        if (html) {
            $("#sidebarMenuContainer").html(html);
            if (typeof setupMenuHighlighting === "function") {
                setupMenuHighlighting();
            }
        }
    });
}

// Setup all event handlers
function setupMenuEvents() {
    // Tab click handler
    $(document).off('shown.bs.tab.menu').on('shown.bs.tab.menu', 'a[data-toggle="tab"]', function(e) {
        const target = $(e.target).attr('href');
        if (target === '#deletedMenus') {
            loadMenus('deleted');
        } else if (target === '#allMenus') {
            loadMenus('all');
        }
    });
    
    // Add menu button
    $('#addModal').off('click.menu').on('click.menu', function() {
        openAddModal("page/modals.php", "menuAddmodal");
    });
    
    // Edit menu
    $(document).off('click.menu', '.btnEditMenu').on('click.menu', '.btnEditMenu', function() {
        const menuId = $(this).data('id');
        openEditModal("page/modals.php", "menueditmodal", "menID", menuId);
    });
    
    // Delete menu
    $(document).off('click.menu', '.btnDeleteMenu').on('click.menu', '.btnDeleteMenu', function() {
        const menuId = $(this).data('id');
        if (confirm("Move this menu to deleted list?")) {
            $.post("backend/bk_menumanagement.php", {
                request: "softDeleteMenu",
                menID: menuId
            }, function(response) {
                // Parse JSON response
                try {
                    const data = JSON.parse(response);
                    if (data.success) {
                        loadMenus('all');
                        loadMenus('deleted');
                        updateSidebar();
                    }
                } catch (e) {
                    console.error("Error parsing response:", e);
                }
            });
        }
    });
    
    // Restore menu
    $(document).off('click.menu', '.btnRestoreMenu').on('click.menu', '.btnRestoreMenu', function() {
        const menuId = $(this).data('id');
        if (confirm("Restore this menu?")) {
            $.post("backend/bk_menumanagement.php", {
                request: "restoreMenu",
                menID: menuId
            }, function(response) {
                // Parse JSON response
                try {
                    const data = JSON.parse(response);
                    if (data.success) {
                        loadMenus('all');
                        loadMenus('deleted');
                        updateSidebar();
                    }
                } catch (e) {
                    console.error("Error parsing response:", e);
                }
            });
        }
    });
    
    // Toggle menu status
    $(document).off('change.menu', '.toggleMenuStatus').on('change.menu', '.toggleMenuStatus', function() {
        const menuId = $(this).data('id');
        const isChecked = $(this).is(':checked');
        const newStatus = isChecked ? 0 : 1;
        
        if (confirm(`Are you sure you want to ${isChecked ? 'activate' : 'deactivate'} this menu?`)) {
            $.post("backend/bk_menumanagement.php", {
                request: "toggleMenuStatus",
                menID: menuId,
                status: newStatus
            }, function(response) {
                // Parse JSON response
                try {
                    const data = JSON.parse(response);
                    if (!data.success) {
                        $(this).prop('checked', !isChecked);
                    } else {
                        updateSidebar();
                    }
                } catch (e) {
                    console.error("Error parsing response:", e);
                    $(this).prop('checked', !isChecked);
                }
            });
        } else {
            $(this).prop('checked', !isChecked);
        }
    });
    
    // Save menu - SIMPLE VERSION (like user management)
    $(document).off('click.menu', '#btnaddmenu').on('click.menu', '#btnaddmenu', function(e) {
        e.preventDefault();
        const btn = $(this);
        const originalText = btn.html();
        
        // Get form data
        const formData = {
            request: "addMenu",
            menu: $("#m_menu").val().trim(),
            mother: $("#m_mother").val() || 0,
            desc: $("#m_desc").val().trim(),
            code: $("#m_code").val().trim(),
            link: $("#m_link").val().trim(),
            arrangement: $("#m_arrange").val() || 0,
            status: $("#m_status").val() || 0,
            icon: $("#m_icon").val().trim()
        };
        
        // Validation
        if (!formData.menu || !formData.code) {
            alert("Menu Name and Code are required!");
            return;
        }
        
        // Disable button
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Send request
        $.post("backend/bk_menumanagement.php", formData, function(response) {
            btn.prop('disabled', false).html(originalText);
            
            // Parse JSON response
            try {
                const data = JSON.parse(response);
                
                if (data.success) {
                    $('#menuAddmodal').modal('hide');
                    
                    // Clear form
                    $("#m_menu, #m_desc, #m_code, #m_link, #m_icon").val("");
                    $("#m_mother, #m_arrange, #m_status").val("0");
                    
                    // Reload table (simple approach like user management)
                    loadMenus('all');
                    updateSidebar();
                } else if (data.message === "DUPLICATE_CODE") {
                    alert("Menu code already exists!");
                } else {
                    alert("Error: " + data.message);
                }
            } catch (e) {
                console.error("Error parsing response:", e, "Response:", response);
                alert("Server error. Please try again.");
            }
        }).fail(function() {
            btn.prop('disabled', false).html(originalText);
            alert("Server error. Please try again.");
        });
    });
    
    // Update menu - SIMPLE VERSION (like user management)
    $(document).off('click.menu', '#btnUpdateMenu').on('click.menu', '#btnUpdateMenu', function(e) {
        e.preventDefault();
        const btn = $(this);
        const originalText = btn.html();
        
        // Get form data
        const formData = {
            request: "updateMenu",
            menID: $("#edit_menID").val(),
            menu: $("#edit_menu").val().trim(),
            desc: $("#edit_desc").val(),
            code: $("#edit_code").val().trim(),
            link: $("#edit_link").val(),
            mother: $("#edit_mother").val(),
            arrangement: $("#edit_arrangement").val(),
            status: $("#edit_status").val(),
            icon: $("#edit_icon").val()
        };
        
        // Validation
        if (!formData.menu || !formData.code) {
            alert("Menu name and code are required!");
            return;
        }
        
        // Disable button
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Send request
        $.post("backend/bk_menumanagement.php", formData, function(response) {
            btn.prop('disabled', false).html(originalText);
            
            // Parse JSON response
            try {
                const data = JSON.parse(response);
                
                if (data.success) {
                    $('#menueditmodal').modal('hide');
                    loadMenus('all');
                    updateSidebar();
                } else if (data.message === "DUPLICATE_CODE") {
                    alert("Menu code already exists!");
                } else {
                    alert("Error: " + data.message);
                }
            } catch (e) {
                console.error("Error parsing response:", e, "Response:", response);
                alert("Server error. Please try again.");
            }
        }).fail(function() {
            btn.prop('disabled', false).html(originalText);
            alert("Server error. Please try again.");
        });
    });
    
    // Prevent form submission on Enter
    $(document).off('submit.menu', '#addMenuForm').on('submit.menu', '#addMenuForm', function(e) {
        e.preventDefault();
        return false;
    });
}
</script>