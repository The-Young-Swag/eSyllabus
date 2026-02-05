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
<div id="modalContainer"></div>







<script>
	// Load Menus into table
	$.ajax({
		type: "POST",
		url: "backend/bk_menumanagement.php",
		data: {
			request: "viewMenus"
		},

		beforeSend: function() {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},

		success: function(dataResult) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
			$("#tblviewMenus").html(dataResult);
			// TableLoader.load({
			//   tableId: '#userTable',
			//   url: "backend/bk_usermanagment.php",
			//   request: "viewMenus"
			// })
		},
		error: function(xhr, status, error) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
			console.error("AJAX error:", error);
		}
	});
	
	//Load add modal
/* 	    $.ajax({
        type: "POST",
        url: "page/modals.php"",
		data:{
			request: "menumodal"
		}
		
		beforeSend: function() {
          $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
            },		
		
        success: function(menumodal) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            $("#add_menuModal").html(menumodal);
        },
        error: function(xhr, status, error) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            console.error("AJAX error:", error);
            $("#add_menuModal").html("<p>Error loading page: " + error + "</p>");
        }
    }); */

// activate add menu modal
$(document).on('click', '#addModal', function () {

    if ($('#menuAddmodal').hasClass('show')) {
        return; // modal already open
    }

    if ($(this).data('loading')) {
        return;
    }

    $(this).data('loading', true);

    $.ajax({
        type: "POST",
        url: "page/modals.php",
        data: { request: "menuAddmodal" },

        beforeSend: function () {
            $("#loadingSpinner").fadeIn(200);
        },

        success: function (html) {
            $("#loadingSpinner").fadeOut(200);

            // SAFELY remove any existing modal + backdrop
            $('.modal').modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');

            $("#modalContainer").html(html);

            $("#menuAddmodal").modal({
                backdrop: 'static',
                keyboard: true
            });

            $("#menuAddmodal").modal("show");
        },

        complete: function () {
            $('#addModal').data('loading', false);
        },

        error: function (xhr, status, error) {
            $("#loadingSpinner").fadeOut(200);
            $('#addModal').data('loading', false);
            console.error("Modal load error:", error);
        }
    });
});

	/* $(document).on('click', '#addModal', function () {

    $.ajax({
        type: "POST",
        url: "page/modals.php",
        data: { request: "menumodal" },

        beforeSend: function () {
            $("#loadingSpinner").fadeIn(200);
        },

        success: function (html) {
            $("#loadingSpinner").fadeOut(200);
            // Remove old modal to avoid duplicates
            $("#menumodal").remove();

            // Inject modal HTML
            $("#modalContainer").html(html);

            // Show modal (ID MATCHES modal.php)
            $("#menumodal").modal("show");
        },

        error: function (xhr, status, error) {
            $("#loadingSpinner").fadeOut(200);
            console.error("Modal load error:", error);
        }
    });

}); */


//Submit added Menu
//Submit added Menu - FIXED VERSION
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
                alert("✓ Menu added successfully!");
                $("#menuAddmodal").modal("hide");
                reloadMenuTable();
            } else if(trimmedResponse === "DUPLICATE_CODE") {
                alert("❌ Menu code already exists! Please use a different code.");
                $("#m_code").focus().select();
            } else if(trimmedResponse === "INSERT_ERROR") {
                alert("❌ Database error! Please try again.");
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

// activate edit menu modal

$(document).on('click', '.btnEditMenu', function () {

    const menID = $(this).data('id'); 

    $.ajax({
        type: "POST",
        url: "page/modals.php",
        data: {
            request: "menueditmodal",
            menID: menID
        },

        beforeSend: function () {
            $("#loadingSpinner").fadeIn(200);
        },

        success: function (html) {
            $("#loadingSpinner").fadeOut(200);

            // SAFELY cleanup old modal + backdrop
            $('.modal').modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');

            $("#modalContainer").html(html);

            $("#menueditmodal").modal({
                backdrop: 'static',
                keyboard: true
            }).modal("show");
        },

        error: function (xhr, status, error) {
            $("#loadingSpinner").fadeOut(200);
            console.error("Modal load error:", error);
        }
    });

});

/* 	$(document).on('click', '#editModal', function () {

    $.ajax({
        type: "POST",
        url: "page/modals.php",
        data: { request: "menueditmodal" },

        beforeSend: function () {
            $("#loadingSpinner").fadeIn(200);
        },

        success: function (html) {
            $("#loadingSpinner").fadeOut(200);
            // Remove old modal to avoid duplicates
            $("#menueditmodal").remove();

            // Inject modal HTML
            $("#modalContainer").html(html);

            // Show modal (ID MATCHES modal.php)
            $("#menueditmodal").modal("show");
        },

        error: function (xhr, status, error) {
            $("#loadingSpinner").fadeOut(200);
            console.error("Modal load error:", error);
        }
    });

}); */

//add btn clicked
/* $(document).on('click', '#btnaddmenu', function () {

    $.ajax({
        beforeSend: function () {
            alert("Add button clicked!");
        }
    });

}); */

//save edited data
$(document).on('click', '#btnUpdateMenu', function () {
    // Get all form values
    const menID = $("#edit_menID").val();
    const menu = $("#edit_menu").val();
    const desc = $("#edit_desc").val();
    const code = $("#edit_code").val();
    const link = $("#edit_link").val();
    const mother = $("#edit_mother").val();
    const arrangement = $("#edit_arrangement").val();
    const status = $("#edit_status").val();
    const icon = $("#edit_icon").val();
    
    // Validation
    if(!menu.trim() || !code.trim()) {
        alert("Menu name and code are required!");
        return;
    }
    
    // Store the updated data for row update
    const updatedData = {
        menu: menu,
        mother: mother,
        desc: desc,
        code: code,
        link: link,
        arrangement: arrangement,
        status: status,
        icon: icon
    };
    
    // Show loading on button
    const $button = $(this);
    $button.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    
    $.ajax({
        type: "POST",
        url: "backend/bk_menumanagement.php",
        data: {
            request: "updateMenu",
            menID: menID,
            menu: menu,
            desc: desc,
            code: code,
            link: link,
            mother: mother,
            arrangement: arrangement,
            status: status,
            icon: icon
        },
        
        success: function(response){
            const trimmedResponse = response.trim();
            $button.html('Save Changes').prop('disabled', false);
            
            if(trimmedResponse === "SUCCESS") {
                // SUCCESS! Update the specific row
                updateMenuRow(menID, updatedData);
                
                alert("Menu updated successfully!");
                $("#menueditmodal").modal("hide");
                
            } else if(trimmedResponse === "DUPLICATE_CODE") {
                alert("Menu code already exists! Please use a different code.");
                $("#edit_code").focus().select();
            } else if(trimmedResponse === "UPDATE_ERROR") {
                alert("Database error! Please try again.");
                // Fallback: reload entire table
                reloadMenuTable();
            } else {
                alert("Server response: " + response);
            }
        },
        
        error: function(xhr, status, error){
            $button.html('Save Changes').prop('disabled', false);
            console.error("AJAX error:", error);
            alert("Error updating menu. Please try again.");
        }
    });
});

// Function to update just the edited row
function updateMenuRow(menID, updatedData) {
    // Find the row with this menID - look in the first column
    const $row = $("#tblviewMenus tr").filter(function() {
        return $(this).find('td:first').text().trim() == menID;
    });
    
    if ($row.length) {
        // Update each cell in the row
        const $cells = $row.find('td');
        
        // Column 2: Menu name
        $cells.eq(1).html(function() {
            // Preserve the indentation arrow if it exists
            const existingHtml = $(this).html();
            const arrowMatch = existingHtml.match(/<i[^>]*>.*?<\/i>\s*/);
            const arrow = arrowMatch ? arrowMatch[0] : '';
            return arrow + updatedData.menu;
        });
        
        // Column 3: Mother Menu ID
        $cells.eq(2).text(updatedData.mother);
        
        // Column 4: Description
        $cells.eq(3).text(updatedData.desc);
        
        // Column 5: Menu Code
        $cells.eq(4).text(updatedData.code);
        
        // Column 6: Menu Link
        $cells.eq(5).text(updatedData.link);
        
        // Column 7: Arrangement
        $cells.eq(6).text(updatedData.arrangement);
        
        // Column 8: Icon
        $cells.eq(7).text(updatedData.icon);
        
        // Column 9: Status checkbox
        const isChecked = updatedData.status == 0;
        const $checkbox = $row.find('.toggle-switch');
        $checkbox.prop('checked', isChecked);
        
        // Update the data attributes if needed
        $checkbox.data('fltid', menID);
        $checkbox.data('menid', menID);
        
        return true;
    }
    
    // If row not found, reload entire table
    console.log("Row not found for MenID:", menID, "- reloading entire table");
    reloadMenuTable();
    return false;
}

// Keep your existing reloadMenuTable function
function reloadMenuTable() {
    $.ajax({
        type: "POST",
        url: "backend/bk_menumanagement.php",
        data: { request: "viewMenus" },
        beforeSend: function() {
            $("#loadingSpinner").fadeIn(200);
        },
        success: function(dataResult) {
            $("#loadingSpinner").fadeOut(200);
            $("#tblviewMenus").html(dataResult);
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200);
            console.error("AJAX error:", error);
        }
    });
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