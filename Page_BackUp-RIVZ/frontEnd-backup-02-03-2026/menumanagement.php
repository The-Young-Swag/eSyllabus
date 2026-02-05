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

    if ($('#menumodal').hasClass('show')) {
        return; // modal already open
    }

    if ($(this).data('loading')) {
        return;
    }

    $(this).data('loading', true);

    $.ajax({
        type: "POST",
        url: "page/modals.php",
        data: { request: "menumodal" },

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

            $("#menumodal").modal({
                backdrop: 'static',
                keyboard: true
            });

            $("#menumodal").modal("show");
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

// activate edit menu modal
	$(document).on('click', '#editModal', function () {

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

});

$(document).on('click', '#btnaddmenu', function () {

    $.ajax({
        beforeSend: function () {
            alert("Add button clicked!");
        }
    });

});

/* //Edit Modal
$(document).on('click', '.btnEditMenu', function () {
    // $("#menueditmodal").modal("show");
	
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

            // Show modal (ID MATCHES modals.php)
            $("#menueditmodal").modal("show");
        },

        error: function (xhr, status, error) {
            $("#loadingSpinner").fadeOut(200);
            console.error("Modal load error:", error);
        }
    });
	
}); */


	
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