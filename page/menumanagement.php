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
	loadMenus('deleted'); // <-- add this line

    // Setup event handlers
    setupMenuEvents();
});


</script>