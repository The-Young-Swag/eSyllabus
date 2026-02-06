<div class="container-fluid mt-4">

	<!-- Header -->
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h4 class="font-weight-bold text-dark">
			<i class="fas fa-bars mr-2 text-primary"></i> Menu Management
		</h4>
		<button class="btn btn-success" id="addModal">
			<i class="fas fa-plus"></i> Add Menu
		</button>
	</div>

	<!-- Tabs Card -->
	<div class="card card-primary card-outline">
		<div class="card-header p-0 border-bottom-0">
			<ul class="nav nav-tabs" id="menuTabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="pill" href="#activeMenus">
						<i class="fas fa-check-circle mr-1"></i> Active Menus
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="pill" href="#deletedMenus">
						<i class="fas fa-trash mr-1"></i> Deleted Menus
					</a>
				</li>
			</ul>
		</div>

		<div class="card-body">
			<div class="tab-content">

				<!--  ACTIVE MENUS  -->
				<div class="tab-pane fade show active" id="activeMenus">
					<div class="table-responsive">
						<table class="table table-hover table-bordered" id="tblActiveMenus">
							<thead class="thead-light">
								<tr>
									<th>#</th>
									<th>Menu</th>
									<th>Mother Menu ID</th>
									<th>Description</th>
									<th>Menu Code</th>
									<th>Menu Link</th>
									<th>Arrangement</th>
									<th>Menu Icon</th>
									<th class="text-center">Unactive</th>
									<th class="text-center">Actions</th>
								</tr>
							</thead>
							<tbody id='tblviewMenus'>
								<tr>
									<td>1</td>
									<td>System Management</td>
									<td>0</td>
									<td>System Management</td>
									<td>SysMngt</td>
									<td></td>
									<td class="text-center">0100</td>
									<td class="text-center">fas fa-home</i></td>
									<td class="text-center">
										<div class="custom-control custom-switch">
											<input type="checkbox"
												class="custom-control-input toggleMenuStatus"
												id="menuStatus1"
												data-id="1">
											<label class="custom-control-label" for="menuStatus1"></label>
										</div>
									</td>
									<td class="text-center">
										<button class="btn btn-sm btn-info" data-toggle="modal" data-target="#mdlMenuEdit">
											<i class="fas fa-edit"></i>
										</button>
										<button class="btn btn-sm btn-danger btnDeleteMenu" data-id="1">
											<i class="fas fa-trash"></i>
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>


				<!--  DELETED MENUS  -->
				<div class="tab-pane fade" id="deletedMenus">
					<div class="alert alert-warning">
						<i class="fas fa-info-circle mr-1"></i>
						Deleted menus can be restored at any time.
					</div>

					<div class="table-responsive">
						<table class="table table-hover table-bordered" id="tblDeletedMenus">
							<thead class="thead-light">
								<tr>
									<th>#</th>
									<th>Menu</th>
									<th>Menu Code</th>
									<th>System Management</th>
									<th>Deleted At</th>
									<th class="text-center">Actions</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>2</td>
									<td>Reports</td>
									<td>RPT_002</td>
									<td>Accounting</td>
									<td>2026-01-15</td>
									<td class="text-center">
										<button class="btn btn-sm btn-warning btnRestoreMenu" data-id="2">
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
<!--<div id="modalContainer"></div>-->
<?php include 'modalContainer.php'; ?>








<script>
//  INITIAL LOAD 
$(document).ready(function() {
    loadTable("backend/bk_menumanagement.php", "viewMenus", "#tblviewMenus");
});

//  MODAL HANDLERS 
$(document).on('click', '#addModal', function() {
    openAddModal("page/modals.php", "menuAddmodal");
});

$(document).on('click', '.btnEditMenu', function() {
    openEditModal("page/modals.php", "menueditmodal", "menID", $(this).data('id'));
});

//  ADD MENU (REAL-TIME) 
$(document).on('click', '#btnaddmenu', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const $btn = $(this);
    
    // Prevent double click
    if ($btn.prop('disabled')) {
        return false;
    }
    
    // Get form data
    const formData = getMenuFormData();
    if (!validateMenuForm(formData)) return false;
    
    // Submit
    $btn.loading(true);
    
    $.ajax({
        type: "POST",
        url: "backend/bk_menumanagement.php",
        data: formData,
        dataType: "json",
        success: (response) => handleAddResponse(response, $btn),
        error: (xhr, status, error) => handleAjaxError(error, $btn)
    });
    
    return false;
});

//  HELPER FUNCTIONS 

// Get form data
function getMenuFormData() {
    return {
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
}

// Validate form
function validateMenuForm(data) {
    if (!data.menu || !data.code) {
        alert("Menu Name and Code are required!");
        return false;
    }
    return true;
}

// Handle add response
function handleAddResponse(response, $btn) {
    $btn.loading(false);
    
    // Check for success using response.success
    if (response.success === true) {
        $("#menuAddmodal").modal("hide");
        
        // Clear form after modal is hidden
        setTimeout(() => {
            clearMenuForm();
        }, 300);
        
        // Show success message
        alert(response.message || "Menu added successfully!");
        
        if (response.rowHtml) {
            insertMenuRow(response.rowHtml, response.menuData?.Arrangement || response.menuData?.arrangement || 0);
        }
        
        updateSidebar();
    } else {
        if (response.message === "DUPLICATE_CODE") {
            alert("Menu code already exists!");
            $("#m_code").focus().select();
        } else {
            alert("Error: " + (response.message || "Failed to add menu"));
        }
    }
}

// Handle AJAX error
function handleAjaxError(error, $btn) {
    $btn.loading(false);
    alert("Connection error: " + error);
}

// Insert row in correct position
function insertMenuRow(rowHtml, arrangement) {
    const $table = $("#tblviewMenus");
    const $rows = $table.find("tr");
    
    // Find position based on arrangement
    let position = $rows.length; // Default: append
    
    $rows.each(function(index) {
        const rowArrangement = parseInt($(this).find("td:eq(6)").text()) || 0;
        if (arrangement < rowArrangement) {
            position = index;
            return false; // Break loop
        }
    });
    
    // Insert at found position
    if (position < $rows.length) {
        $rows.eq(position).before(rowHtml);
    } else {
        $table.append(rowHtml);
    }
    
    // Highlight
    const $newRow = $table.find("tr").eq(position);
    highlightRow($newRow);
}

// Highlight row temporarily
function highlightRow($row) {
    $row.addClass("table-success");
    setTimeout(() => $row.removeClass("table-success"), 1500);
}

// Clear form
function clearMenuForm() {
    $("#m_menu, #m_desc, #m_code, #m_link, #m_icon").val("");
    $("#m_mother, #m_arrange, #m_status").val("0");
}

// Loading button helper
$.fn.loading = function(isLoading) {
    if (isLoading) {
        return this.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
    } else {
        return this.html('Add Menu').prop('disabled', false);
    }
};

//  UPDATE MENU 
$(document).on('click', '#btnUpdateMenu', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const $button = $(this);
    
    // Prevent double click
    if ($button.prop('disabled')) {
        return false;
    }
    
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
    
    if (!formData.menu || !formData.code) {
        alert("Menu name and code are required!");
        return false;
    }
    
    const originalText = $button.html();
    
    $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    $.ajax({
        type: "POST",
        url: "backend/bk_menumanagement.php",
        data: formData,
        dataType: "json",
        success: function(response) {
            $button.prop('disabled', false).html(originalText);
            
            // Check for success using response.success instead of response.status
            if (response.success === true) {
                // Close modal first
                $('.modal').modal('hide');
                
                // Wait for modal to close, then show alert
                setTimeout(() => {
                    alert("Saved successfully!");
                    
                    // Update the specific row
                    updateMenuRow(response.menID || formData.menID, {
                        menu: response.menu,
                        desc: response.desc,
                        code: response.code,
                        link: response.link,
                        mother: response.mother,
                        arrangement: response.arrangement,
                        status: response.menuStatus,
                        icon: response.icon
                    });
                    
                    // Update sidebar in real-time
                    updateSidebar();
                }, 300);
            } else if (response.message === "DUPLICATE_CODE") {
                alert("Menu code already exists! Please use a different code.");
                $("#edit_code").focus().select();
            } else {
                alert("Error: " + (response.message || "Update failed"));
            }
        },
        error: function(xhr, status, error) {
            $button.prop('disabled', false).html(originalText);
            alert("Connection Error: " + error);
        }
    });
    
    return false;
});

// REMOVE THE DUPLICATE FUNCTION - Keep only one updateMenuRow function
function updateMenuRow(menID, data) {
    const $row = $(`#tblviewMenus tr:has(td:first-child:contains('${menID}'))`);
    
    if ($row.length) {
        $row.find("td:eq(1)").text(data.menu);
        $row.find("td:eq(2)").text(data.mother);
        $row.find("td:eq(3)").text(data.desc);
        $row.find("td:eq(4)").text(data.code);
        $row.find("td:eq(5)").text(data.link);
        $row.find("td:eq(6)").text(data.arrangement);
        $row.find("td:eq(7)").text(data.icon);
        
        const $checkbox = $row.find('.toggleMenuStatus');
        const menuStatus = data.menuStatus !== undefined ? data.menuStatus : data.status;
        const isChecked = menuStatus == "0" || menuStatus === 0;
        $checkbox.prop('checked', isChecked);
        
        highlightRow($row);
    } else {
        // Fallback: reload table
        loadTable("backend/bk_menumanagement.php", "viewMenus", "#tblviewMenus");
    }
}

//  TOGGLE STATUS 
$(document).on('change', '.toggleMenuStatus', function() {
    const $checkbox = $(this);
    const menuID = $checkbox.data('id');
    const isActive = $checkbox.is(':checked'); // true = checkbox checked (active)
    
    // IMPORTANT: When checkbox is checked (isActive = true), we want status = 0 (active)
    // When checkbox is unchecked (isActive = false), we want status = 1 (inactive)
    // This matches: 0 = Active, 1 = Inactive
    const newStatus = isActive ? 0 : 1;
    
    if (confirm(`Are you sure you want to ${isActive ? 'activate' : 'deactivate'} this menu?`)) {
        $.ajax({
            url: "backend/bk_menumanagement.php",
            method: "POST",
            dataType: "json",
            data: {
                request: "toggleMenuStatus",
                menID: menuID,
                status: newStatus
            },
            success: function(response) {
                // Check for response.success instead of response.status
                if (response.success === true) {
                    // Update sidebar in real-time
                    updateSidebar();
                } else {
                    $checkbox.prop('checked', !isActive); // Revert checkbox
                    alert("Update failed: " + (response.message || ""));
                }
            },
            error: function() {
                $checkbox.prop('checked', !isActive); // Revert checkbox
                alert("Error updating status!");
            }
        });
    } else {
        // Revert checkbox if user cancels
        $checkbox.prop('checked', !isActive);
    }
});

//  REAL-TIME SIDEBAR UPDATES 

/**
 * Update sidebar menu without page reload
 */
function updateSidebar() {
    const currentRID = UserInfo["RID"] || 0;
    
    if (!currentRID) {
        console.log("Cannot update sidebar: No RID available");
        return;
    }
    
    $.ajax({
        url: "backend/bk_menumanagement.php",
        method: "POST",
        data: {
            request: "getSidebarMenu",
            RID: currentRID,
            userRID: currentRID
        },
        success: function(html) {
            if (html) {
                $("#sidebarMenuContainer").html(html);
                
                // Reinitialize menu highlighting if function exists
                if (typeof setupMenuHighlighting === "function") {
                    setupMenuHighlighting();
                }
                
                console.log("Sidebar updated successfully");
            }
        },
        error: function(xhr, status, error) {
            console.log("Sidebar update failed:", error);
        }
    });
}
</script>