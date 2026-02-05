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

				<!-- ================= ACTIVE MENUS ================= -->
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


				<!-- ================= DELETED MENUS ================= -->
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
	// Load Menus into table
	
	// Initial load
$(document).ready(function() {
	loadTable("backend/bk_menumanagement.php", "viewMenus", "#tblviewMenus");
});

// Add Menu Modal
$(document).on('click', '#addModal', function () {
    openAddModal("page/modals.php", "menuAddmodal");
});

// Edit Menu Modal
$(document).on('click', '.btnEditMenu', function() {
    openEditModal("page/modals.php", "menueditmodal", "menID", $(this).data('id'));
});

//Submit added Menu
$(document).on('click', '#btnaddmenu', function (e) {
    e.preventDefault();
    
    // Get form values
    const menuData = {
        request: "addMenu",
        menu: $("#m_menu").val(),
        mother: $("#m_mother").val(),
        desc: $("#m_desc").val(),
        code: $("#m_code").val(),
        link: $("#m_link").val(),
        arrangement: $("#m_arrange").val(),
        status: $("#m_status").val(),
        icon: $("#m_icon").val()
    };
    
    // Validation
    if(!menuData.menu.trim() || !menuData.code.trim()) {
        alert("Please fill in Menu Name and Menu Code!");
        return;
    }
    
    // Show loading
    const $button = $(this);
    $button.html('<i class="fas fa-spinner fa-spin"></i> Adding...').prop('disabled', true);
    
    // Send request
    $.ajax({
        type: "POST",
        url: "backend/bk_menumanagement.php",
        data: menuData,
        
        success: function(response) {
            const trimmedResponse = response.trim();
            $button.html('Add Menu').prop('disabled', false);
            
            if(trimmedResponse === "SUCCESS") {
                alert("Menu added successfully!");
                $("#menuAddmodal").modal("hide");
                reloadMenuTable();
            } else if(trimmedResponse === "DUPLICATE_CODE") {
                alert("Menu code already exists! Please use a different code.");
                $("#m_code").focus().select();
            } else if(trimmedResponse === "INSERT_ERROR") {
                alert("Database error! Please try again.");
            } else {
                alert("Response: " + response);
            }
        },
        
        error: function(xhr, status, error) {
            $button.html('Add Menu').prop('disabled', false);
            alert("Connection error: " + error);
        }
    });
});





$(document).on('click', '#btnUpdateMenu', function () {
    const formData = {
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
    
    // Reusable save with row update
    saveData.call(
        this,
        "backend/bk_menumanagement.php",
        "updateMenu",
        formData,
        function(updatedData) {
            // This updates ONLY the edited row
            updateMenuRow(updatedData.menID, updatedData);
        }
    );
});

// Keep your existing updateMenuRow function
function updateMenuRow(menID, updatedData) {
    const $row = $("#tblviewMenus tr").filter(function() {
        return $(this).find('td:first').text().trim() == menID;
    });
    
    if ($row.length) {
        const $cells = $row.find('td');
        $cells.eq(1).text(updatedData.menu);
        $cells.eq(2).text(updatedData.mother);
        $cells.eq(3).text(updatedData.desc);
        $cells.eq(4).text(updatedData.code);
        $cells.eq(5).text(updatedData.link);
        $cells.eq(6).text(updatedData.arrangement);
        $cells.eq(7).text(updatedData.icon);
        
        const $checkbox = $row.find('.toggle-switch');
        $checkbox.prop('checked', updatedData.status == 0);
    } else {
        reloadMenuTable(); // Fallback
    }
}



	
	// Toggle user active/inactive
	$(document).on('change', '.toggleStatus', function() {
		const checkbox = $(this);
		const userId = checkbox.data('id');
		const newStatus = checkbox.is(':checked') ? 'active' : 'inactive';

		let confirmMsg = newStatus === 'active' ?
			'Are you sure you want to activate this user?' :
			'Are you sure you want to deactivate this user?';

		if (confirm(confirmMsg)) {
			console.log(`Changing user ${userId} status to ${newStatus}`);
			// TODO: AJAX call to update status in backend

			// Optional: move row between tabs if desired
		} else {
			// revert toggle if canceled
			checkbox.prop('checked', !checkbox.is(':checked'));
		}
	});
</script>