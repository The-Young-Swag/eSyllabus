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

// Auto Log-out =================================================================================================

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

class TableLoader {
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
}


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

};


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

//LOAD TABLE  
function loadTable(backendurl, backendrequest, tabletarget) {
    $.ajax({
        type: "POST",
        url: backendurl,
        data: { request: backendrequest },
/*         beforeSend: function() {
            $("#loadingSpinner").show();
        }, */
        success: function(dataResult) {
/*             $("#loadingSpinner").hide(); */
            $(tabletarget).html(dataResult);
        },
        error: function(xhr, status, error) {
/*             $("#loadingSpinner").hide(); */
            console.error("AJAX error:", error);
        }
    });
}





