<?php
date_default_timezone_set('Asia/Manila');
	
$query = execsqlSRS("
SELECT o.[OfficeCode]
FROM Sys_UserAccount u
left join [tbl_OfficeStaff] os on os.[EmpID] = u.EmpID
LEFT JOIN [Sys_Office] o ON o.[OfficeMenID] = os.[OfficeID]
WHERE o.[OfficeMenID] = '$Office'
", "Select", array());
	
$Office_name = isset($query[0]) ? $query[0]["OfficeCode"] : "";

// Add defaults for all required variables
$Name = $Name ?? 'User';
$RID = $RID ?? 0;
$Office = $Office ?? 0;
$dashTitle = $dashTitle ?? 'Dashboard';
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-light-success elevation-4" id="sidebardarkmode">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="dist/img/tau-logo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"><?php echo htmlspecialchars($dashTitle); ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="dist/img/tau-logo.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <!-- Display the user info: Email address and role ID -->
                <a href="#" class="d-block"><?php echo htmlspecialchars($Name); ?></a>
				<a href="#" class="d-block"><?php echo htmlspecialchars($Office_name); ?></a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search" id="doc_search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false" id="sidebarMenuContainer">
                <?php
                // Fetch user-specific menus based on their role ID (RID)
				$UserMenus = execsqlSRS("
					SELECT m.* 
					FROM Sys_Menu m
					INNER JOIN Sys_RoleMenu rm ON rm.MenID = m.MenID AND rm.RID = ?
					WHERE m.UnActive = 0
					  AND (m.IsDeleted = 0 OR m.IsDeleted IS NULL)
					  AND rm.UnActive = 0
					  AND MotherMenID = 0
					ORDER BY m.Arrangement ASC
				", "Select", [$RID]);


                // Loop through the organized menu structure and create HTML
                foreach ($UserMenus as $menuItem) {
                    // Add icon with fallback
                    $icon = !empty($menuItem["MenIcon"]) ? htmlspecialchars($menuItem["MenIcon"]) : 'fas fa-circle';
                    
                    // Start a parent menu item
                    echo "<li class='nav-item' id='dropdown' data-read='{$menuItem["MenID"]}'>
                            <a href='#' class='nav-link' id='clckdropdown' data-IDsubmenu='{$menuItem["MenID"]}' data-Page='namepage'>
                                <i class='{$icon}'></i>
                                <p>
                                    {$menuItem["Menu"]}
                                    <i class='right fas fa-angle-left'></i> <!-- Indicates dropdown -->
                                </p>
                            </a>
                            <ul class='nav nav-treeview' id='{$menuItem["MenID"]}'>";

						$childMenus = execsqlSRS("
							SELECT m.* 
							FROM Sys_Menu m 
							INNER JOIN Sys_RoleMenu rm ON rm.MenID = m.MenID 
							INNER JOIN Sys_Role r ON r.RID = rm.RID 
							WHERE rm.RID = :rid 
							AND m.UnActive = 0 
							AND rm.UnActive = 0 
							AND r.UnActive = 0 
							AND (m.IsDeleted = 0 OR m.IsDeleted IS NULL)
							AND MotherMenID = :motherID
							ORDER BY m.Arrangement ASC
						", "Select", array(":rid" => $RID, ":motherID" => $menuItem["MenID"]));

                
                    // Loop through child menus and add them under the parent
                    foreach ($childMenus as $childMenu) {
                        // Add icon with fallback for child menus
                        $childIcon = !empty($childMenu["MenIcon"]) ? htmlspecialchars($childMenu["MenIcon"]) : 'fas fa-circle';
                        
                        if (htmlspecialchars($childMenu["Menucode"]) == "u_Logout") {
                            echo "<li class='nav-item bg-danger rounded'>
                                    <a href='#' class='nav-link' id='callpages' data-pagename='{$childMenu["MenuLink"]}'>
                                        <i class='{$childIcon}'></i>
                                        <p>{$childMenu["Menu"]}</p> <!-- Child menu -->
                                    </a>
                                  </li>";
                        } else {
                            echo "<li class='nav-item'>
                                    <a href='#' class='nav-link' id='callpages' data-pagename='{$childMenu["MenuLink"]}'>
                                        <i class='{$childIcon}'></i>
                                        <p>{$childMenu["Menu"]}</p> <!-- Child menu -->
                                    </a>
                                  </li>";
                        }
                    }

                    // Close the parent menu item
                    echo "</ul></li>";
                }
                ?>
            </ul>
        </nav>
    </div>
</aside>

<script type="text/javascript">
$(function () {
    setupMenuHighlighting();
});

function setupMenuHighlighting() {
    var currentPath = window.location.pathname.toLowerCase();
    
    $(".nav-sidebar li a").each(function () {
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
</script>