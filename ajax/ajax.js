// Store logged-in user information
const UserInfo = [];
var officeTabNo = 0;

var arrCSCform = [];





/**
 * Auto-load a page into the container
 * @param {string} page - Page name to load
 * @param {string} id - Optional ID for request
 * @param {string} request - Optional request action
 */
function autocall(page = "", id = "", request = "") {
    $.ajax({
        type: "POST",
        url: "page/" + page + ".php",
        data: { request, id },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResult) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            $("#container").html(dataResult);
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
            console.error("Error:", error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
    });
}

// Auto Log-out 

  var inactivityTime = 60 * 60 * 1000; // minutes
  var timeout;

  function startInactivityTimer() {
    clearTimeout(timeout);
    timeout = setTimeout(logoutUser, inactivityTime);
  }

  function logoutUser() {
	  
		alert("You have been automatically logged out due to inactivity...");
		location.reload();
  }

  ['mousemove', 'keydown', 'scroll', 'click'].forEach(evt =>
    window.addEventListener(evt, startInactivityTimer)
  );

  startInactivityTimer();

/**
 * Load page content when menu link is clicked
 */
 
$(document).on('click', '#callpages', function(e) {
    e.preventDefault();
	UserInfo["SpcfcOfficeID"] ="";
    var pagename = $(this).attr('data-pagename');

    // Prepend "page/" if needed
    if (!pagename.startsWith("page/") && !pagename.startsWith("/")) {
        pagename = "page/" + pagename;
    }

    $.ajax({
        type: "POST",
        url: pagename,
		
		beforeSend: function() {
          $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		     $("#mainContent").html("");
            },		
			
        success: function(dataResult) {
			//alert(dataResult);
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            $("#mainContent").html(dataResult);
        },
        error: function(xhr, status, error) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            console.error("AJAX error:", error);
            $("#mainContent").html("<p class='p-3'>Error loading page: " + error + "</p>");
        }
    });
});


/**
 * 
 * Login Button
 */
 //SESSION IS SAVED HERE

 
$(document).on('click', '#btnLogin', function(e) {
    var lgtxtEmail = $("#lgtxtEmail").val();
    var lgtxtpassword = $("#lgtxtpassword").val();

    $.ajax({
        type: "POST",
        url: "backend/bk_login.php",
        data: { request: "verifyLogin", lgtxtEmail, lgtxtpassword },
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResults) {
            //alert(dataResults);
			
            var dataRes = JSON.parse(dataResults);

            if (dataRes.ChangePass === "1") {
                // Save user info
                UserInfo["UserID"] = dataRes.UserID;
                UserInfo["EmpID"] = dataRes.EmpID;
                UserInfo["RID"] = dataRes.RID;
                UserInfo["EmailAddress"] = dataRes.EmailAddress;
				UserInfo["Office_id"] = dataRes.Office_id;
				UserInfo["Name"] = dataRes.Name;
				UserInfo["Password"] = dataRes.Password;
				UserInfo["ChangePass"] = dataRes.ChangePass;
				UserInfo["AllOfficeAcess"] = dataRes.AllOfficeAcess;

                // Load dashboard page
                $.ajax({
                    type: "POST",
                    url: "page/RequiredChangePassword.php",
                    data: {
                        UserID: UserInfo["UserID"],
                        RID: UserInfo["RID"],
                        EmailAddress: UserInfo["EmailAddress"],
						Office_id: UserInfo["Office_id"],
						Name: UserInfo["Name"],
						Password: UserInfo["Password"]
                    },
                    success: function(dataResult) {
						
						$("#loadingSpinner").fadeOut(200).css("display", "none");
						alert("Require to change your password!");
                        $("#container").html(dataResult);
						$('#welcomemodal').modal('show');
                    }
                });

            }else if (dataRes.status === "Registered") {
                // Save user info
                UserInfo["UserID"] = dataRes.UserID;
                UserInfo["EmpID"] = dataRes.EmpID;
                UserInfo["RID"] = dataRes.RID;
                UserInfo["EmailAddress"] = dataRes.EmailAddress;
				UserInfo["Office_id"] = dataRes.Office_id;
				UserInfo["Name"] = dataRes.Name;
				UserInfo["Password"] = dataRes.Password;
				UserInfo["ChangePass"] = dataRes.ChangePass;
				UserInfo["AllOfficeAcess"] = dataRes.AllOfficeAcess;

                // Load dashboard page
                $.ajax({
                    type: "POST",
                    url: "page/dashboard.php",
                    data: {
                        UserID: UserInfo["UserID"],
                        RID: UserInfo["RID"],
                        EmailAddress: UserInfo["EmailAddress"],
						Office_id: UserInfo["Office_id"],
						Name: UserInfo["Name"],
						Password: UserInfo["Password"]
                    },
                    success: function(dataResult) {
						
						$("#loadingSpinner").fadeOut(200).css("display", "none");
						alert("Verified and Logged in!");
                        $("#container").html(dataResult);
						$('#welcomemodal').modal('show');
                    }
                });

            } else if (dataRes.status === "unrecognized") {
				$("#loadingSpinner").fadeOut(200).css("display", "none");
                alert("User doesn't exist...");
            }
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
        }
    });
});

/**
 * Handle dropdown menu toggle
 */
$(document).on('click', '#clckdropdown', function(e) {
    var $dropdown = $(this).closest('li');
    var submenuID = $(this).attr('data-IDsubmenu');

    if ($dropdown.hasClass("menu-open")) {
        // If open, close submenu
        $dropdown.removeClass("menu-is-opening menu-open");
        $("#" + submenuID).hide();
    } else {
        // If closed, open submenu
        $dropdown.addClass("menu-is-opening menu-open");
        $("#" + submenuID).show();
    }
});

//Incoming Redirect=============================================================================================

$(document).on('click', '#incredirect', function(e) {
    e.preventDefault();

    var pagename = $(this).attr('data-pagename');

    // Prepend "page/" if needed
    if (!pagename.startsWith("page/") && !pagename.startsWith("/")) {
        pagename = "page/" + pagename;
    }

    $.ajax({
        type: "POST",
        url: pagename,
		
		beforeSend: function() {
          $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
            },		
		
        success: function(dataResult) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
			$("#notifdrop").modal("hide");
            $("#mainContent").html(dataResult);
        },
        error: function(xhr, status, error) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            console.error("AJAX error:", error);
            $("#mainContent").html("<p>Error loading page: " + error + "</p>");
        }
    });
});


//Notif Redirect======================================================================================================

	$(document).on('click', '#notifred', function(e){
		
		var user_id = UserInfo["UserID"];
		var user_email = UserInfo["EmailAddress"];
		var user_office = UserInfo["Office_id"];

			$.ajax({
				url:"backend/bk_notifred.php",
				method:"POST",
				data:{
					user_id: user_id,
					user_email: user_email,
					user_office: user_office,
					request: 'notifsee'
					},

				success:function(dataResult){
						$("#notifshow").html(dataResult);
						$('#notifdrop').modal('show');
				}
			});
	});


//Dark Mode toggle
$(document).on('click', '#customSwitch1', function(e) {
    var isDark = $("#container").hasClass("dark-mode");

    $("#container").attr("class", isDark ? "sidebar-mini layout-fixed control-sidebar-slide-open" : "sidebar-mini layout-fixed control-sidebar-slide-open dark-mode");
    $("#navbardarkmode").attr("class", isDark ? "main-header navbar navbar-expand navbar-light" : "main-header navbar navbar-expand navbar-dark");
    $("#sidebardarkmode").attr("class", isDark ? "main-sidebar sidebar-light-success elevation-4" : "main-sidebar sidebar-dark-success elevation-4");
});

$(document).on('click', '#showPasswordCheckbox', function(e) {
    // Select the password input field by its ID
    var passwordInput = $('#lgtxtpassword');

    // Check if the checkbox is currently checked
    var isChecked = $(this).is(':checked');

    // Set the input field type based on the checkbox state
    // If checked, show password as plain text; otherwise, mask it
    passwordInput.attr('type', isChecked ? 'text' : 'password');
});

// Table loader===================================================================================

/* class TableLoader {
    static load({ tableId, url, request, onSuccess = null }) {
        $.post(url, { request }, function(data) {
			
            // If a custom success callback is provided, call it with the response data
            if (typeof onSuccess === "function") {
                onSuccess(data);
            } else {
                // Otherwise, insert the response HTML into the specified table element
				
				//alert(data);
			var dataRes = JSON.parse(data);
			if (dataRes.status) {
				 $(tableId).html(dataRes.tbleDetails);
			}
			
               
            }
        }).fail(function(xhr, status, error) {
            // Log an error if the request fails
            console.error("Failed to load table:", error);
        });
    }
} */


class PasswordToggler {
    constructor() {
        // Initialize Bootstrap tooltips on all elements that use data-toggle="tooltip"
        $('[data-toggle="tooltip"]').tooltip();
    }

    toggle(id) {
        // Select the input, button, and icon elements based on the provided ID
        const $input = $(`#password_${id}`);
        const $button = $(`#btn_${id}`);
        const $icon = $(`#eye_${id}`);
        const password = $input.data('password');

        // Determine if the password is currently masked
        const isMasked = $input.attr('type') === 'password';

        // Toggle the input type between 'text' and 'password'
        // If showing the password, use the actual value; otherwise, mask it
        $input

            .attr('type', isMasked ? 'text' : 'password')
            .val(isMasked ? password : '$input');

        // Toggle icon classes to reflect visibility state
        $icon
            .toggleClass('fa-eye', !isMasked)
            .toggleClass('fa-eye-slash', isMasked);

        // Update the tooltip title and reinitialize the tooltip
        $button
            .attr('title', isMasked ? 'Hide Password' : 'Show Password')
            .tooltip('dispose')
            .tooltip();
    }
}













































$(document).on('change', '#mgtPrvActvStat', function(e) {

	let URID = $(this).attr("data-URID");
    let isChecked = $(this).prop("checked"); // true or false

   /*  console.log("User ID:", userId);
    console.log("Active Status:", isChecked); */
	let chval = isChecked == true?0:1;
		
	$.ajax({
		url:"backend/bk_privilegemanagement.php",
		method:"POST",
		data:{
			URID: URID
			,request: 'Update'
			,chval: chval
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		success:function(dataResult){
			alert(dataResult);
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
		},
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});

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
//-BACKUP
/* function getrule() {

	$.ajax({
		url:"backend/bk_privilegemanagement.php",
		method:"POST",
		data:{
			request: 'GetRole'
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		success:function(dataResult){
			
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			$("#prvroleSelect").html(dataResult);
		},
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});

}; */


//Role Management select dropdown when selecting added privileges
$(document).on('change', '#prvroleSelect', function(e) {
	$.ajax({
		url:"backend/bk_privilegemanagement.php",
		method:"POST",
		data:{
			request: 'showPrvMenAcc'
			,RID: $(this).val()
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		success:function(dataResult){
			
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			var dataRes = JSON.parse(dataResult);
			
			dataRes.forEach(function(item, index) {
				$("[name='PrvStat"+item.MenID +"']").prop("checked",item.UnActive);
			});
			
		},
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});

});

//Role Management updating new access to a certain privilege
$(document).on('change', '#UpdatePrvRole', function(e) {

    let Upval =$(this).is(":checkbox") 
		? ($(this).prop("checked")== true?0:1)
		: $(this).val(); 
	//alert(Upval); return;
	
	$.ajax({
		url:"backend/bk_privilegemanagement.php",
		method:"POST",
		data:{
			request: 'Update'
			,table:  $(this).attr("data-table")
			,UpFld:  $(this).attr("data-UpFld")
			,Upval:  Upval
			,FltFld:  $(this).attr("data-FltFld")
			,FltID:  $(this).attr("data-FltID")
			,RID:  $("#prvroleSelect").val()
			,userId:  UserInfo["UserID"]
			
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		success:function(dataResult){
			//alert(dataResult);
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
		},
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});

});





// RIVZ - Core Utility Functions

//  Menu Loaders 
function loadMenus(type) {
    const tableId  = type === 'all' ? '#tableAllMenus' : '#tableDeletedMenus';
    const request  = type === 'all' ? 'getAllMenus'    : 'getDeletedMenus';

    $.post("backend/bk_menumanagement.php", { request }, function(data) {
        $(tableId).html(data);
    });
}

function loadTable(url, request, target) {
    $.post(url, { request }, function(response) {
        $(target).html(response);
    }).fail(function(xhr, status, error) {
        console.error("loadTable AJAX error:", error);
    });
}

function loadPrivTable(url, request, target, roleID = "") {
    $(target).html(`
        <tr>
            <td colspan="4" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-success mr-2"></div>
                Loading...
            </td>
        </tr>
    `);

    $.post(url, { request, RID: roleID }, function(response) {
        $(target).html(response);
    }).fail(function(xhr, status, error) {
        console.error("loadPrivTable AJAX error:", error);
        $(target).html(`
            <tr>
                <td colspan="4" class="text-center text-danger py-4">
                    Error loading data
                </td>
            </tr>
        `);
    });
}

function loadAllRolesAndMenus() {
    loadPrivTable(
        "backend/bk_privilegemanagement.php",
        "showAllRolesAndMenus",
        "#privilegeTableBody"
    );
}


//  AJAX Helpers 
function postJSON(url, data, onSuccess, onFail) {
    return $.post(url, data)
        .done(res => {
            try {
                const trimmed = typeof res === "string" ? res.trim() : res;
                const json    = typeof trimmed === "string" ? JSON.parse(trimmed) : trimmed;

                if (json.success) onSuccess?.(json);
                else
                    onFail?.(json) ?? alert(json.message || "Operation failed");
            } catch (e) {
                console.error("Invalid JSON response:", res);
                alert("Server error. Please try again.");
            }
        })
        .fail(err => {
            console.error("Request failed:", err);
            onFail?.(err) ?? alert("Server error. Please try again.");
        });
}

//  Modal Helpers 
function clearModals() {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
}

function resolveModalId(modalRequest) {
    return "#" + modalRequest.replace("modal", "") + "modal";
}

// openAddModal("page/modals.php", "menuAddmodal")
function openAddModal(modalURL, modalRequest) {
    $.ajax({
        type: "POST",
        url: modalURL,
        data: { request: modalRequest },
        beforeSend: () => $("#loadingSpinner").fadeIn(200),
        success: function(html) {
            $("#loadingSpinner").fadeOut(200);
            clearModals();
            $("#modalContainer").html(html);

            const $modal = $(resolveModalId(modalRequest));
            if ($modal.length) $modal.modal("show");
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200);
            console.error("openAddModal error:", error);
        }
    });
}


// openEditModal("page/modals.php", "menueditmodal", "menID", 5)
function openEditModal(modalURL, modalRequest, itemIDParam, itemID) {
    $.ajax({
        type: "POST",
        url: modalURL,
        data: { request: modalRequest, [itemIDParam]: itemID },
        beforeSend: () => $("#loadingSpinner").fadeIn(200),
        success: function(html) {
            $("#loadingSpinner").fadeOut(200);
            clearModals();
            $("#modalContainer").html(html);

            const $modal = $(resolveModalId(modalRequest));
            if ($modal.length) $modal.modal("show");
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200);
            console.error("openEditModal error:", error);
        }
    });
}


//  Save / Row Update 
function saveData(saveURL, saveRequest, formData, updateRowCallback) {
    const $btn         = $(".modal:visible").find("button[type='submit'], .btn-primary");
    const originalText = $btn.html();

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.post(saveURL, { request: saveRequest, ...formData })
        .done(function(resp) {
            $btn.prop('disabled', false).html(originalText);

            try {
                const data = typeof resp === "string" ? JSON.parse(resp) : resp;

                if (data.success) {
                    updateRowCallback?.(data);
                    if (typeof updateSidebarMenu === "function") updateSidebarMenu();
                    $('.modal').modal('hide');
                    showToast("Menu updated successfully!");

                } else if (data.message === "DUPLICATE_CODE") {
                    alert("Menu code already exists!");
                } else {
                    alert("Error: " + data.message);
                }

            } catch (e) {
                console.error("saveData parse error:", e, resp);
                alert("Server error. Please try again.");
            }
        })
        .fail(function() {
            $btn.prop('disabled', false).html(originalText);
            alert("Server error. Please try again.");
        });
}

function updateMenuRow(data) {
    const $row = $("button.btnEditMenu[data-id='" + data.menID + "']").closest("tr");
    if (!$row.length) return;

    $row.find("td:eq(1)").html(data.menu);
    $row.find("td:eq(3)").text(data.desc);
    $row.find("td:eq(4)").text(data.code);
    $row.find("td:eq(5)").text(data.link);
    $row.find("td:eq(6)").text(data.arrangement);
    $row.find("td:eq(7)").text(data.icon);
    $row.find(".toggleMenuStatus").prop("checked", data.menuStatus == 0);
}


//  Sidebar 
function fetchSidebarMenu(roleID = null, notify = false) {
    roleID = roleID || UserInfo["RID"];
    if (!roleID) return;

    $.post("backend/bk_privilegemanagement.php", { request: "GetUserMenu", RID: roleID }, function(html) {
        if (!html) return;

        $("#sidebarMenuContainer").html(html);

        if (typeof setupMenuHighlighting === "function") setupMenuHighlighting();
        if (notify && typeof showSidebarNotification === "function") showSidebarNotification();
    }).fail(() => console.error("Failed to load sidebar menu"));
}

function refreshSidebarForRole(roleID) {
    if (!roleID) return;

    $.post("backend/bk_privilegemanagement.php", { request: "RefreshSidebar", RID: roleID }, function() {
        console.log("Sidebar refresh triggered for role:", roleID);
        if (UserInfo["RID"] == roleID) fetchSidebarMenu(roleID, true);
    }).fail(() => console.error("Sidebar refresh failed"));
}

function updateSidebarMenu(RID = null) {
    const currentRID = RID || UserInfo?.RID || 0;
    if (!currentRID) return;

    const $spinner = $("#loadingSpinner");

    $.ajax({
        url: "backend/bk_menumanagement.php",
        method: "POST",
        data: { request: "getSidebarMenu", RID: currentRID },
        beforeSend: () => $spinner.css("display", "flex").hide().fadeIn(200),
        success: function(html) {
            const $sidebar = $("#sidebarMenuContainer");
            $sidebar.empty().html(html);

            setTimeout(function() {
                $sidebar.Treeview({ accordion: false });
                if (typeof setupMenuHighlighting === "function") setupMenuHighlighting();
            }, 30);
        },
        complete: () => $spinner.fadeOut(200),
        error: () => console.log("Sidebar update failed")
    });
}

function setupMenuHighlighting() {
    const currentPath = window.location.pathname.toLowerCase();

    $("#sidebarMenuContainer .nav-link").each(function() {
        const $link = $(this);
        const url   = $link.attr("href");
        if (!url || url === "#") return;

        if (currentPath.includes(url.toLowerCase())) {
            $link.addClass("active");
            $link.closest(".nav-item").addClass("menu-open");
            $link.closest(".nav-treeview").css("display", "block");
        }
    });
}


//  Toast 
function showToast(message, type = "success") {
    const toastId = "toast-" + Date.now();
    const bgColor  = { success: "bg-success", warning: "bg-warning", error: "bg-danger" }[type] || "bg-info";

    $("body").append(`
        <div id="${toastId}" class="toast" style="position: fixed; top: 20px; right: 20px; z-index: 1060;">
            <div class="toast-header ${bgColor} text-white">
                <strong class="mr-auto">System</strong>
                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `);

    $(`#${toastId}`).toast({ delay: 2000 }).toast("show");
    setTimeout(() => $(`#${toastId}`).remove(), 2500);
}


//  Menu Events 
function setupMenuEvents() {

    // Tab switch
    $(document).off('shown.bs.tab.menu').on('shown.bs.tab.menu', 'a[data-toggle="tab"]', function(e) {
        const target = $(e.target).attr('href');
        if (target === '#deletedMenus') loadMenus('deleted');
        else if (target === '#allMenus') loadMenus('all');
    });

    // Open Add modal
    $('#addModal').off('click.menu').on('click.menu', function() {
        openAddModal("page/modals.php", "menuAddmodal");
    });

    // Open Edit modal
    $(document).off('click.menu', '.btnEditMenu').on('click.menu', '.btnEditMenu', function() {
        openEditModal("page/modals.php", "menueditmodal", "menID", $(this).data('id'));
    });

    // Soft delete — cascade children
    $(document).off('click.menu', '.btnDeleteMenu').on('click.menu', '.btnDeleteMenu', function() {
        const menuId = $(this).data('id');
        if (!menuId || !confirm("Move this menu (and its children) to deleted list?")) return;

        postJSON(
            "backend/bk_menumanagement.php",
            { request: "softDeleteMenu", menID: menuId },
            function() {
                loadMenus('all');
                loadMenus('deleted');
                updateSidebarMenu();
            }
        );
    });

    // Restore
    $(document).off('click.menu', '.btnRestoreMenu').on('click.menu', '.btnRestoreMenu', function() {
        const menuId = $(this).data('id');
        if (!menuId || !confirm("Restore this menu?")) return;

        postJSON(
            "backend/bk_menumanagement.php",
            { request: "restoreMenu", menID: menuId },
            function() {
                loadMenus('all');
                loadMenus('deleted');
                updateSidebarMenu();
            }
        );
    });

    // Toggle status: cascade to children visually + backend
    $(document).off('change.menu', '.toggleMenuStatus').on('change.menu', '.toggleMenuStatus', function() {
        const $switch = $(this);
        const menID = $switch.data('id');
        const isChecked = $switch.is(':checked');
        const newStatus = isChecked ? 0 : 1;

        if (!menID) return;

        if (!confirm(`Are you sure you want to ${isChecked ? 'activate' : 'deactivate'} this menu?`)) {
            $switch.prop('checked', !isChecked);
            return;
        }

        $switch.prop('disabled', true);

        $.post("backend/bk_menumanagement.php", { request: "toggleMenuStatus", menID, status: newStatus })
            .done(function(resp) {
                $switch.prop('disabled', false);

                try {
                    const res = typeof resp === "string" ? JSON.parse(resp) : resp;

                    if (!res.success) {
                        alert(res.message || "Update failed");
                        $switch.prop('checked', !isChecked);
                        return;
                    }

                    // Visual cascade: disable/enable child rows whose MotherMenID matches
                    cascadeChildStatus(menID, isChecked);

                    $switch.closest('tr').addClass('table-success');
                    setTimeout(() => $switch.closest('tr').removeClass('table-success'), 800);

                    loadMenus('all');
                    loadMenus('deleted');
                    updateSidebarMenu();

                } catch (e) {
                    console.error(e, resp);
                    alert("Invalid response from server");
                    $switch.prop('checked', !isChecked);
                }
            })
            .fail(function() {
                $switch.prop('disabled', false).prop('checked', !isChecked);
                alert("Network error");
            });
    });

    // Add Menu
    $(document).off('click.menu', '#btnaddmenu').on('click.menu', '#btnaddmenu', function(e) {
        e.preventDefault();

        const $btn         = $(this);
        const originalText = $btn.html();

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

        if (!formData.menu || !formData.code) {
            alert("Menu Name and Code are required!");
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.post("backend/bk_menumanagement.php", formData)
            .done(function(resp) {
                $btn.prop('disabled', false).html(originalText);

                try {
                    const data = typeof resp === "string" ? JSON.parse(resp) : resp;

                    if (data.success) {
                        $('#menuAddmodal').modal('hide');
                        $("#m_menu, #m_desc, #m_code, #m_link, #m_icon").val("");
                        $("#m_mother, #m_arrange, #m_status").val("0");
                        loadMenus('all');
                        loadMenus('deleted');
                        updateSidebarMenu();
                        showToast("Menu added successfully!");

                    } else if (data.message === "DUPLICATE_CODE") {
                        alert("Menu code already exists!");
                    } else {
                        alert("Error: " + data.message);
                    }

                } catch (e) {
                    console.error("addMenu parse error:", e, resp);
                    alert("Server error. Please try again.");
                }
            })
            .fail(function() {
                $btn.prop('disabled', false).html(originalText);
                alert("Server error. Please try again.");
            });
    });

    // Prevent form submit on Enter
    $(document).off('submit.menu', '#addMenuForm').on('submit.menu', '#addMenuForm', function(e) {
        e.preventDefault();
        return false;
    });
}

// Save via btnSaveMenu (legacy path)
$(document).on("click", "#btnSaveMenu", function() {
    const formData = {
        menID: $("#menID").val(),
        menu: $("#menu").val(),
        desc: $("#desc").val(),
        code: $("#code").val(),
        link: $("#link").val(),
        mother: $("#mother").val(),
        arrangement: $("#arrangement").val(),
        status: $("#status").is(":checked") ? 0 : 1,
        icon: $("#icon").val()
    };

    saveData("backend/bk_menumanagement.php", "updateMenu", formData, function() {
        loadMenus("all");
    });
});


// Update Menu (edit modal)
$(document).off('click.menu', '#btnUpdateMenu').on('click.menu', '#btnUpdateMenu', function(e) {
    e.preventDefault();

    const $btn = $(this);
    const originalText = $btn.html();

    const formData = {
        request: "updateMenu",
        menID: $("#edit_menID").val(),
        menu: $("#edit_menu").val().trim(),
        desc: $("#edit_desc").val().trim(),
        code: $("#edit_code").val().trim(),
        link: $("#edit_link").val().trim(),
        mother: $("#edit_mother").val() || 0,
        arrangement: $("#edit_arrangement").val() || 0,
        status: $("#edit_status").val() || 0,
        icon: $("#edit_icon").val().trim()
    };

    if (!formData.menu || !formData.code) {
        alert("Menu Name and Code are required!");
        return;
    }

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.post("backend/bk_menumanagement.php", formData)
        .done(function(resp) {
            $btn.prop('disabled', false).html(originalText);

            try {
                const data = typeof resp === "string" ? JSON.parse(resp) : resp;

                if (data.success) {
                    updateMenuRow(data);
                    $('#menueditmodal').modal('hide');
                    updateSidebarMenu();
                    showToast("Menu updated successfully!");

                } else if (data.message === "DUPLICATE_CODE") {
                    alert("Menu code already exists!");
                } else {
                    alert("Error: " + data.message);
                }

            } catch (e) {
                console.error("updateMenu parse error:", e, resp);
                alert("Server error. Please try again.");
            }
        })
        .fail(function() {
            $btn.prop('disabled', false).html(originalText);
            alert("Server error. Please try again.");
        });
});


//  Helpers 

/**
 * Cascades enable/disable state to child rows when a mother menu is toggled.
 * Walks the rendered table and matches rows by MotherMenID (td:eq(2)).
 */
function cascadeChildStatus(motherMenID, isActive) {
    $("#tableAllMenus tr").each(function() {
        const $row = $(this);
        const rowMotherId = $row.find("td:eq(2)").text().trim();

        if (rowMotherId != motherMenID) return;

        const $childSwitch = $row.find(".toggleMenuStatus");
        $childSwitch.prop('checked', isActive);

        // Fire backend update for each child
        const childMenID = $childSwitch.data('id');
        if (!childMenID) return;

        $.post("backend/bk_menumanagement.php", {
            request: "toggleMenuStatus",
            menID: childMenID,
            status:  isActive ? 0 : 1
        });

        // Recurse for grandchildren
        cascadeChildStatus(childMenID, isActive);
    });
}


//  Analytics / Library helpers 
function showLoading(target) {
    $(target).html('<div class="text-center py-5">Loading...</div>');
}

function showError(target, msg = "Failed to load content.") {
    $(target).html(`<div class="text-danger py-5">${msg}</div>`);
}

function fetchTabContent(tabName, url = "backend/bk_LibraryMenu/bk_libReports.php") {
    return $.post(url, { request: tabName });
}

function initChartsIfExists(tabName) {
    if (typeof initTabCharts === "function") initTabCharts(tabName);
}

function setActiveTab(tabButtons, clickedTab) {
    tabButtons.removeClass("active");
    clickedTab.addClass("active");
}

function loadTab(tabName, contentTarget) {
    showLoading(contentTarget);

    fetchTabContent(tabName)
        .done(html => {
            $(contentTarget).html(html);
            initChartsIfExists(tabName);
        })
        .fail((xhr, status, err) => {
            console.error("loadTab AJAX error:", status, err);
            showError(contentTarget);
        });
}

async function getStudentInfo(studentNumber) {
    const paths = [
        'API_requests/students.json',
        '../API_requests/students.json',
        '/eSyllabus/API_requests/students.json'
    ];

    for (const path of paths) {
        try {
            const res = await fetch(path);
            if (res.ok) {
                const data = await res.json();
                return data[studentNumber] || null;
            }
        } catch {}
    }

    return null;
}

async function fetchTable(url, request, injectTo) {
    const response = await fetch(url, {
        method: 'POST',
        body:   new URLSearchParams({ request })
    });

    document.getElementById(injectTo).innerHTML = await response.text();
}

function btnClick(url, request, target) {
    loadTable(url, request, target);
}