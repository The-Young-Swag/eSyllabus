<div class="container-fluid mt-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="font-weight-bold text-dark">
      <i class="fas fa-user-shield mr-2 text-primary"></i> Privilege Management
    </h4>
    
    <!-- Role Selection Dropdown -->
    <div style="width: 300px;">
      <select id="prvroleSelect" class="form-control">
        <option value="">-- Select Role --</option>
        <?php
        // Load roles directly in PHP for better performance
        require_once "../db/dbconnection.php";
        $roles = execsqlSRS("SELECT RID, Role FROM Sys_Role ORDER BY Role", "Select", []);
        foreach($roles as $role) {
          echo "<option value='{$role['RID']}'>{$role['Role']}</option>";
        }
        ?>
      </select>
    </div>
  </div>

  <!-- Privileges Display Card -->
  <div class="card">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">
        <i class="fas fa-key mr-2"></i> Role Privileges
        <small class="float-right" id="selectedRoleName">No role selected</small>
      </h5>
    </div>
    
    <div class="card-body">
      <!-- Role Info -->
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="form-group">
            <label><i class="fas fa-info-circle mr-2 text-info"></i> Role Information</label>
            <div class="p-3 bg-light rounded" id="roleInfo">
              Select a role to view privileges
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label><i class="fas fa-history mr-2 text-info"></i> Last Updated</label>
            <div class="p-3 bg-light rounded" id="lastUpdated">
              --
            </div>
          </div>
        </div>
      </div>
      
      <!-- Menu Privileges Table -->
      <div class="table-responsive">
        <table class="table table-hover table-bordered" id="tblPrivileges">
          <thead class="thead-light">
            <tr>
              <th width="5%">#</th>
              <th>Menu</th>
              <th width="15%">Menu Code</th>
              <th width="25%">Description</th>
              <th width="10%" class="text-center">Access</th>
              <th width="15%" class="text-center">Status</th>
            </tr>
          </thead>
          <tbody id="privilegeTable">
            <tr>
              <td colspan="6" class="text-center text-muted py-5">
                <i class="fas fa-user-shield fa-3x mb-3 d-block"></i>
                Please select a role to view and manage privileges
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="card-footer text-muted">
      <small>
        <i class="fas fa-info-circle mr-1"></i> 
        <span id="privilegeCount">0</span> menu privileges available
      </small>
    </div>
  </div>
</div>

<!-- Modal Container (for loading modals dynamically) -->
<div id="modalContainer"></div>

<script>
// Privilege Management Functions
class PrivilegeManager {
  constructor() {
    this.init();
  }
  
  init() {
    this.loadRoles();
    this.bindEvents();
    this.initializeTooltips();
  }
  
  initializeTooltips() {
    $('[data-toggle="tooltip"]').tooltip();
  }
  
  loadRoles() {
    $.ajax({
      url: "backend/bk_privilegemanagement.php",
      method: "POST",
      data: { request: "GetRole" },
      beforeSend: () => this.showLoading(),
      success: (dataResult) => {
        this.hideLoading();
        $("#prvroleSelect").html(dataResult);
      },
      error: (xhr, status, error) => {
        this.hideLoading();
        console.error("Error loading roles:", error);
        this.showAlert("Failed to load roles. Please refresh the page.", "danger");
      }
    });
  }
  
  loadRoleInfo(roleID) {
    $.ajax({
      url: "backend/bk_privilegemanagement.php",
      method: "POST",
      data: { 
        request: "GetRoleInfo",
        roleID: roleID
      },
      beforeSend: () => {
        $("#roleInfo").html('<div class="spinner-border spinner-border-sm"></div> Loading...');
      },
      success: (dataResult) => {
        try {
          const roleInfo = JSON.parse(dataResult);
          $("#roleInfo").html(`
            <strong>Role:</strong> ${roleInfo.Role}<br>
            <strong>Code:</strong> ${roleInfo.Rolecode}<br>
            <strong>Status:</strong> <span class="badge ${roleInfo.UnActive == '0' ? 'badge-success' : 'badge-danger'}">
              ${roleInfo.UnActive == '0' ? 'Active' : 'Inactive'}
            </span>
          `);
        } catch(e) {
          $("#roleInfo").html("Error loading role information");
        }
      },
      error: () => {
        $("#roleInfo").html("Error loading role information");
      }
    });
  }
  
  loadPrivileges(roleID) {
    $.ajax({
      url: "backend/bk_privilegemanagement.php",
      method: "POST",
      data: { 
        request: "showtblData",
        roleID: roleID
      },
      beforeSend: () => {
        this.showLoading();
        $("#privilegeTable").html(`
          <tr>
            <td colspan="6" class="text-center py-4">
              <div class="spinner-border text-primary"></div>
              <p class="mt-2">Loading privileges...</p>
            </td>
          </tr>
        `);
      },
      success: (dataResult) => {
        this.hideLoading();
        $("#privilegeTable").html(dataResult);
        
        // Update privilege count
        this.updatePrivilegeCount();
        
        // Load role's menu access status
        this.loadRoleMenuAccess(roleID);
      },
      error: (xhr, status, error) => {
        this.hideLoading();
        $("#privilegeTable").html(`
          <tr>
            <td colspan="6" class="text-center text-danger py-4">
              <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
              Failed to load privileges. Please try again.
            </td>
          </tr>
        `);
        console.error("Error loading privileges:", error);
      }
    });
  }
  
  loadRoleMenuAccess(roleID) {
    $.ajax({
      url: "backend/bk_privilegemanagement.php",
      method: "POST",
      data: { 
        request: "showPrvMenAcc",
        RID: roleID
      },
      success: (dataResult) => {
        try {
          const menuAccess = JSON.parse(dataResult);
          this.updateMenuAccessUI(menuAccess);
        } catch(e) {
          console.error("Error parsing menu access:", e);
        }
      },
      error: () => {
        console.error("Failed to load menu access");
      }
    });
  }
  
  updateMenuAccessUI(menuAccess) {
    menuAccess.forEach((item) => {
      const checkbox = $(`[data-MenID="${item.MenID}"]`);
      if (checkbox.length) {
        // item.UnActive is boolean from showPrvMenAcc (true=enabled, false=disabled)
        checkbox.prop("checked", item.UnActive === true);
        
        // Update status text
        const statusText = checkbox.closest('td').find('.status-text');
        if (statusText.length) {
          statusText.text(item.UnActive ? 'Enabled' : 'Disabled');
          statusText.removeClass('text-success text-danger')
            .addClass(item.UnActive ? 'text-success' : 'text-danger');
        }
      }
    });
  }
  
  togglePrivilege(checkbox) {
    const roleID = $("#prvroleSelect").val();
    
    if (!roleID) {
      this.showAlert("Please select a role first!", "warning");
      checkbox.prop("checked", !checkbox.prop("checked")); // Revert toggle
      return;
    }
    
    const isChecked = checkbox.prop("checked");
    const menuID = checkbox.data("menid");
    const menuName = checkbox.data("menuname") || "Menu";
    const statusText = checkbox.closest('td').find('.status-text');
    
    // Update UI immediately
    statusText.text(isChecked ? 'Enabled' : 'Disabled');
    statusText.removeClass('text-success text-danger')
      .addClass(isChecked ? 'text-success' : 'text-danger');
    
    // Send update to server
    this.updatePrivilegeOnServer(roleID, menuID, isChecked, statusText, checkbox);
  }
  
  updatePrivilegeOnServer(roleID, menuID, isChecked, statusText, checkbox) {
    $.ajax({
      url: "backend/bk_privilegemanagement.php",
      method: "POST",
      data: {
        request: "Update",
        table: "Sys_RoleMenu",
        UpFld: "UnActive",
        Upval: isChecked ? 0 : 1, // 0=Enabled, 1=Disabled
        FltFld: "MenID",
        FltID: menuID,
        RID: roleID,
        userId: window.UserInfo?.UserID || 0
      },
      beforeSend: () => {
        statusText.html('<span class="spinner-border spinner-border-sm"></span> Updating...');
      },
      success: (response) => {
        const trimmedResponse = response.trim();
        if (trimmedResponse === "SUCCESS") {
          // Success - update UI
          statusText.text(isChecked ? 'Enabled' : 'Disabled');
          statusText.removeClass('text-success text-danger')
            .addClass(isChecked ? 'text-success' : 'text-danger');
          
          // Log action
          this.logAction(roleID, menuID, isChecked ? 'enabled' : 'disabled');
        } else {
          // Revert on error
          checkbox.prop("checked", !isChecked);
          statusText.text(isChecked ? 'Disabled' : 'Enabled');
          statusText.removeClass('text-success text-danger')
            .addClass(isChecked ? 'text-danger' : 'text-success');
          this.showAlert("Failed to update privilege. Please try again.", "danger");
        }
      },
      error: () => {
        // Revert on error
        checkbox.prop("checked", !isChecked);
        statusText.text(isChecked ? 'Disabled' : 'Enabled');
        statusText.removeClass('text-success text-danger')
          .addClass(isChecked ? 'text-danger' : 'text-success');
        this.showAlert("Network error. Please check your connection.", "danger");
      }
    });
  }
  
  updatePrivilegeCount() {
    const rowCount = $("#privilegeTable tr").filter(function() {
      return $(this).find("td").length > 1;
    }).length;
    $("#privilegeCount").text(rowCount);
  }
  
  logAction(roleID, menuID, action) {
    // You can implement logging here if needed
    console.log(`Privilege ${action}: Role ${roleID}, Menu ${menuID}`);
  }
  
  showAlert(message, type = "info") {
    // Simple alert function - you can enhance this with a proper notification system
    alert(message);
  }
  
  showLoading() {
    $("#loadingSpinner").fadeIn(200);
  }
  
  hideLoading() {
    $("#loadingSpinner").fadeOut(200);
  }
  
  bindEvents() {
    // Role selection change
    $(document).on('change', '#prvroleSelect', (e) => this.onRoleSelect(e));
    
    // Toggle privilege access
    $(document).on('change', '.toggle-switch', (e) => this.onToggleSwitch(e));
    
    // Custom switch styling
    $(document).on('change', '.custom-control-input', function() {
      const isChecked = $(this).prop('checked');
      const label = $(this).next('.custom-control-label');
      label.text(isChecked ? 'Enabled' : 'Disabled');
    });
  }
  
  onRoleSelect(e) {
    const roleID = $(e.target).val();
    const roleName = $(e.target).find('option:selected').text();
    
    if (!roleID) {
      $("#selectedRoleName").text("No role selected");
      $("#roleInfo").html("Select a role to view privileges");
      $("#privilegeTable").html(`
        <tr>
          <td colspan="6" class="text-center text-muted py-5">
            <i class="fas fa-user-shield fa-3x mb-3 d-block"></i>
            Please select a role to view and manage privileges
          </td>
        </tr>
      `);
      $("#privilegeCount").text("0");
      return;
    }
    
    $("#selectedRoleName").text(roleName);
    
    // Load role information
    this.loadRoleInfo(roleID);
    
    // Load privileges for this role
    this.loadPrivileges(roleID);
  }
  
  onToggleSwitch(e) {
    this.togglePrivilege($(e.target));
  }
}

// Initialize Privilege Manager when document is ready
$(document).ready(function() {
  window.privilegeManager = new PrivilegeManager();
});
</script>