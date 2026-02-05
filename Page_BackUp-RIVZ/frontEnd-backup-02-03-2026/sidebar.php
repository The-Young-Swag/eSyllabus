<?php
	date_default_timezone_set('Asia/Manila');
	
	$query = execsqlSRS("
	SELECT o.[OfficeCode]
	FROM Sys_UserAccount u
	left join [tbl_OfficeStaff] os on os.[EmpID] = u.EmpID
	LEFT JOIN [Sys_Office] o ON o.[OfficeMenID] = os.[OfficeID]
	WHERE o.[OfficeMenID] = '$Office'
	", "Select", array());
	
	$Office_name = isset($query[0])?$query[0]["OfficeCode"]:"";
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-light-success elevation-4" id="sidebardarkmode">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="dist/img/tau-logo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"><?php echo $dashTitle; ?></span>
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
                <a href="#" class="d-block"><?php echo $Name; ?></a>
				<a href="#" class="d-block"><?php echo $Office_name; ?></a>
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
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <?php
                    // Fetch user-specific menus based on their role ID (RID)
                    $UserMenus = execsqlSRS("
                        SELECT m.* 
                        FROM Sys_Menu m 
                        INNER JOIN Sys_RoleMenu rm ON rm.MenID = m.MenID 
                        INNER JOIN Sys_Role r ON r.RID = rm.RID 
                        WHERE rm.RID = '{$RID}' 
                        AND m.Unactive = 0 
                        AND rm.Unactive = 0 
                        AND r.Unactive = 0 
						AND MotherMenID = 0
                        ORDER BY m.Arrangement ASC
                    ", "Select", array());

                    // Loop through the organized menu structure and create HTML
                    foreach ($UserMenus as $menuItem) {
                        // Start a parent menu item
                        echo "<li class='nav-item' id='dropdown' data-read='{$menuItem["MenID"]}'>
                                <a href='#' class='nav-link' id='clckdropdown' data-IDsubmenu='{$menuItem["MenID"]}' data-Page='namepage'>
                                    <i class='" . htmlspecialchars($menuItem["MenIcon"]) . "'></i>
                                    <p>
                                        {$menuItem["Menu"]}
                                        <i class='right fas fa-angle-left'></i> <!-- Indicates dropdown -->
                                    </p>
                                </a>
                                <ul class='nav nav-treeview' id='{$menuItem["MenID"]}'>";


						 $childMenus = execsqlSRS("SELECT m.* 
								FROM Sys_Menu m 
								INNER JOIN Sys_RoleMenu rm ON rm.MenID = m.MenID 
								INNER JOIN Sys_Role r ON r.RID = rm.RID 
								WHERE rm.RID = '{$RID}' AND m.Unactive = 0 AND rm.Unactive = 0 AND r.Unactive = 0 
								and MotherMenID = '{$menuItem["MenID"]}'
								ORDER BY m.Arrangement ASC
							", "Select", array());
					
                        // Loop through child menus and add them under the parent
                        foreach ($childMenus as $childMenu) {
							
							if (htmlspecialchars($childMenu["Menucode"]) == "u_Logout") {
								
							echo "<li class='nav-item bg-danger rounded'>
                                    <a href='#' class='nav-link' id='callpages' data-pagename='{$childMenu["MenuLink"]}'>
                                        <i class='" . htmlspecialchars($childMenu["MenIcon"]) . "'></i>
                                        <p>{$childMenu["Menu"]}</p> <!-- Child menu -->
                                    </a>
                                  </li>";
								
							}
							
							else {
							
                            echo "<li class='nav-item'>
                                    <a href='#' class='nav-link' id='callpages' data-pagename='{$childMenu["MenuLink"]}'>
                                        <i class='" . htmlspecialchars($childMenu["MenIcon"]) . "'></i>
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
    // Get the current page path to highlight the active menu item
    var currentPath = window.location.pathname.toLowerCase();
    console.log('Current Path:', currentPath);

    // Loop through each menu item link to check if it matches the current path
    $(".nav-sidebar li a").each(function () {
        var $link = $(this);
        var url = $link.attr("href");

        // Skip if the link is empty or just a placeholder "#"
        if (!url || url === "#") return;

        // Convert the URL to lowercase for case-insensitive comparison
        var urlLower = url.toLowerCase();

        // If the current URL matches, activate the menu item and open the parent menu
        if (currentPath.includes(urlLower)) {
            // Highlight the current link
            $link.addClass("active");

            // Expand the parent menu (if any)
            $link.closest(".nav-item").addClass("menu-open");
            $link.closest(".nav-treeview").css("display", "block");
        }
    });
});
</script>
