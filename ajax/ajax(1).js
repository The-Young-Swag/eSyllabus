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
 * Handle login button click
 * Sends login credentials to server and loads dashboard if successful
 */
 
$(document).on('click', '#btnSaveNewPass', function(e) {
    var txtRePassword = $("#txtRePassword").val();
    var txtNewPassword = $("#txtNewPassword").val();
	if( txtRePassword === txtNewPassword){
    $.ajax({
        type: "POST",
        url: "backend/bk_login.php",
        data: { request: "RegNewPassword"
			  , txtNewPassword:txtNewPassword
			  , txtRePassword:txtRePassword
			  , UserID:UserInfo["UserID"]
		},
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResults) {
            //alert(dataResults);
			
            var dataRes = JSON.parse(dataResults);

            if (dataRes.status === "PassExist") {
                
                alert("password Exist!");

            }else if (dataRes.status === "Registered") {
                // Save user info
                UserInfo["UserID"] = dataRes.UserID;
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
                        $("#container").html(dataResult);
						$('#welcomemodal').modal('show');
                    }
                });
                alert("successful change password...");

            } else if (dataRes.status === "unrecognized") {
				$("#loadingSpinner").fadeOut(200).css("display", "none");
                alert("User doesn't exist...");
            }
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
        }
    });
	}else{
		alert("Password mismatch!");
	}
});
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

//Dropdown document type ============================================================================================

$(document).on('change', '#dtype', function(e) {
       
	var doctype = $(this).val();

			$.ajax({
				data: 	{
					doctype: doctype,
					request: 'dype'
				},

				type: "POST",
				url: "backend/bk_process.php",
				success: function(dataResult) {
					$('#doc_type').val(dataResult);
					$('#view_type').val(dataResult);
				}
			});

});


//==================================================================================================================


//Dropdown Send=====================================================================================================

jQuery(function($) {
  var checkList = $('.dropdown-check-list');
  checkList.on('click', 'span.anchor', function(event) {
    var element = $(this).parent();

    if (element.hasClass('visible')) {
      element.removeClass('visible');
    } else {
      element.addClass('visible');
    }
  });
});


	$(document).on('click', '#savedraft', function(e) {

		var data = $(this).val();

		var foredit_desc = $("#foredit_desc").val();
		var foredit_number = $("#foredit_number").val();
		var foredit_date = $("#foredit_date").val();
		var foredit_proponent = $("#foredit_proponent").val();
		var foredit_amount = $("#foredit_amount").val();

			$.ajax({
				data: 	{data: data, 
						request: 'savedraft',
						foredit_desc: foredit_desc,
						foredit_number: foredit_number,
						foredit_date: foredit_date,
						foredit_proponent: foredit_proponent,
						foredit_amount: foredit_amount
				},

				type: "POST",
				url: "backend/bk_savedraft.php",
				success: function(dataResult) {
					alert(dataResult);

					$.ajax({
						url:"backend/bk_draftstable.php",
						method:"POST",
						data:{
							request:"viewdrafts", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewdrafts").html(dataResult);
						}
					});
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

//==================================================================================================================

//Add User =========================================================================================================

$(document).on('change', '#mgtActiveStat', function(e) {

	let updateField = $(this).attr("data-updateField");
	let userId = $(this).attr("data-userid");
    let isChecked = $(this).prop("checked"); // true or false

   /*  console.log("User ID:", userId);
    console.log("Active Status:", isChecked); */
	let chval = isChecked == true?0:1;
		
	$.ajax({
		url:"backend/bk_usermanagement.php",
		method:"POST",
		data:{
			request: 'Update'
			,userId: userId
			,updateField: updateField
			,chval: chval
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

$(document).on('change', '#u_username', function(e) {
	$.ajax({
			url:"backend/bk_usermanagement.php",
			method:"POST",
			data:{
				request: 'chckExistPosition'
				,userId: $(this).val()
			},
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},
			success:function(dataResult){
				$("#loadingSpinner").fadeOut(200, function() {
					$(this).css("display", "none");
				});
				//u_unit u_position
				var dataRes = JSON.parse(dataResult);
				if (dataRes.status=="failed") {
					
				}else{
					$('#u_unit').val(dataRes.u_unit);
					$('#u_position').val(dataRes.u_position);
				} 
				
					
			},
			error: function(xhr, status, error) {
				// Hide the animation and show an error message
				$("#loadingSpinner").fadeOut(200, function() {
					$(this).css("display", "none");
				});

				console.error("Error: " + error);
				$("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
			}
		});

});
$(document).on('click', '#adduser', function(e) {
	
	if($(this).attr("data-label") !== undefined){
		if($(this).attr("data-label") == "Edit User"){
			var EmpID=  $(this).attr("data-EmpID");
			$("#lblAddU").text("Edit User");
			$("#u_username").val(EmpID);
			$("#u_username").prop("disabled", true);
			 
			let isChecked = $("[name='mgtActiveStat"+EmpID+"']").prop("checked"); // true or false		 
			 
			$("#u_position").val($(this).attr("data-PositionId"));
			$("#u_unit").val($(this).attr("data-OfficeMenID"));
			$("#u_role").val($(this).attr("data-RID"));
			UserInfo["OfStaffID"] = $(this).attr("data-OfStaffID");
			 
			//var IsActive = $(this).attr("data-IsActive")=="0"?true:false;
			$("#u_status").prop("checked", isChecked);
			 
			var IsPlantilla = $(this).attr("data-Plantilla")=="0"?false:true;
			$("#swchPlantilla").prop("checked", IsPlantilla);
			 
			$("#u_submit").attr("data-request",$(this).attr("data-request"));
			$("#u_submit").text("Save");
		}	
		 
	}	else{
			 $("#lblAddU").text("Add User");
			 
			 $("#u_username").val("");
			 $("#u_username").prop("disabled", false);
			 
			 
			 
			 $("#u_position").val("");
			 $("#u_unit").val("");
			 $("#u_role").val("");
			 
			 $("#u_submit").attr("data-request","adduser");
			 $("#u_submit").text("Add User");
		
	}
	$('#usermodal').modal('show');

});

$(document).on('click', '#u_submit', function(e){
	
	var request = $(this).attr("data-request");
	//alert(request); return;
	
	var u_username = $("#u_username").val();
	var u_password = $("#u_password").val();
	
    let isstatus = $("#u_status").prop("checked"); 
	var u_status = isstatus == true?0:1;
	
	var u_role = $("#u_role").val();
	var u_name = $("#u_name").val();
	var u_unit = $("#u_unit").val();
	var u_position = $("#u_position").val();
	
    let isChecked = $("#swchPlantilla").prop("checked"); 
	let chval = isChecked == true?1:0;

		$.ajax({
			url:"backend/bk_adduser.php",
			method:"POST",
			data:{
				u_username: u_username,
				u_password: u_password,
				u_status: u_status,
				u_role: u_role,
				u_name: u_name,
				u_unit: u_unit,
				u_position: u_position,
				Plantilla: chval,
				OfStaffID: UserInfo["OfStaffID"],
				UserID: UserInfo["UserID"],
				request: request
			},
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},
			success:function(dataResult){
				//alert(request);
				$("#loadingSpinner").fadeOut(200, function() {
					$(this).css("display", "none");
				});
				
				var dataRes = JSON.parse(dataResult);
				if (dataRes.status=="failed") {
					alert(dataRes.message);
				}else{
					alert(dataRes.message);
					$('#usermodal').modal('hide');
					$.ajax({
						url:"backend/bk_usermanagement.php",
						method:"POST",
						data:{
							request:"viewUsers", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dta){
								var dataRes = JSON.parse(dta);
								if (dataRes.OfStaffID) {
									$("#tblviewUsers").html(dataRes.tbleDetails);
								}
								
						}
					});
				} 
				
					
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

	$(document).on('click', '#edituser', function(e){
		
		var UserID = $(this).val();

			$.ajax({
				url:"backend/bk_adduser.php",
				method:"POST",
				data:{
					UserID: UserID,
					request: 'edituser',
					},

				success:function(dataResult){
						$("#useredit_content").html(dataResult);
						$('#usereditmodal').modal('show');
				}
			});
	});

$(document).on('click', '#eu_submit', function(e){

	var eu_submit = $(this).val();

	var eu_email = $("#eu_email").val();
	var eu_password = $("#eu_password").val();
	var eu_role = $("#eu_role").val();
	var eu_status = $("#eu_status").val();
	var eu_name = $("#eu_name").val();
	var eu_unit = $("#eu_unit").val();
	var eu_position = $("#eu_position").val();
	
		$.ajax({
			url:"backend/bk_adduser.php",
			method:"POST",
			data:{
				eu_email: eu_email,
				eu_password: eu_password,
				eu_role: eu_role,
				eu_status: eu_status,
				eu_name: eu_name,
				eu_unit: eu_unit,
				eu_submit: eu_submit,
				eu_position: eu_position,
				request: 'saveuser'
				},

			success:function(dataResult){
				alert(dataResult);

					$('#usereditmodal').modal('hide');
					$.ajax({
						url:"backend/bk_usermanagement.php",
						method:"POST",
						data:{
							request:"viewUsers", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewUsers").html(dataResult);
						}
					});
			}
		});
});

//==================================================================================================================

//Add Role =========================================================================================================

$(document).on('click', '#addrole', function(e) {

	$('#rolemodal').modal('show');

});

$(document).on('click', '#r_submit', function(e){
	
	var r_role = $("#r_role").val();
	var r_rolecode = $("#r_rolecode").val();
	var r_status = $("#r_status").val();
		$.ajax({
			url:"backend/bk_addrole.php",
			method:"POST",
			data:{
				r_role: r_role,
				r_rolecode: r_rolecode,
				r_status: r_status,
				request: 'addrole'
				},

			success:function(dataResult){
				
				
				var dataRes = JSON.parse(dataResult);
				if(dataRes.status == "success"){
					alert(dataRes.message);
					$('#rolemodal').modal('hide');
					$.ajax({
						url:"backend/bk_rolemanagement.php",
						method:"POST",
						data:{
							request:"viewRoles", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewRoles").html(dataResult);
						}
					});
				}else{
					alert(dataRes.message);
				}
				
			}
		});
});

	$(document).on('click', '#editrole', function(e){
		
		var RID = $(this).val();

			$.ajax({
				url:"backend/bk_addrole.php",
				method:"POST",
				data:{
					RID: RID,
					request: 'editrole',
					},

				success:function(dataResult){
						$("#roleedit_content").html(dataResult);
						$('#roleeditmodal').modal('show');
				}
			});
	});

	$(document).on('click', '#er_submit', function(e){
		
		var er_submit = $(this).val();
		var er_role = $('#er_role').val();
		var er_rolecode = $('#er_rolecode').val();
		var er_status = $('#er_status').val();

			$.ajax({
				url:"backend/bk_addrole.php",
				method:"POST",
				data:{
					er_submit: er_submit,
					er_role: er_role,
					er_rolecode: er_rolecode,
					er_status: er_status,
					request: 'saverole',
					},

				success:function(dataResult){
					alert(dataResult);
					
					$('#roleeditmodal').modal('hide');
					$.ajax({
						url:"backend/bk_rolemanagement.php",
						method:"POST",
						data:{
							request:"viewRoles", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewRoles").html(dataResult);
						}
					});
				}
			});
	});


//==================================================================================================================

//Add Menu =========================================================================================================

$(document).on('click', '#addmenu', function(e) {

	$('#menumodal').modal('show');

});

$(document).on('click', '#m_submit', function(e){
	
	var m_menu = $("#m_menu").val();
	var m_mother = $("#m_mother").val();
	var m_desc = $("#m_desc").val();
	var m_code = $("#m_code").val();
	var m_link = $("#m_link").val();
	var m_arrange = $("#m_arrange").val();
	var m_icon = $("#m_icon").val();
	var m_status = $("#m_status").val();
		$.ajax({
			url:"backend/bk_addmenu.php",
			method:"POST",
			data:{
				m_menu: m_menu,
				m_mother: m_mother,
				m_desc: m_desc,
				m_code: m_code,
				m_link: m_link,
				m_arrange: m_arrange,
				m_icon: m_icon,
				m_status: m_status,
				request: 'addmenu'
				},

			success:function(dataResult){
				//alert(dataResult);
				var dataRes = JSON.parse(dataResult);
				if(dataRes.status == "success"){
					alert(dataRes.message);
					$('#menumodal').modal('hide');
					$.ajax({
						url:"backend/bk_menumanagement.php",
						method:"POST",
						data:{
							request:"viewMenus", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewMenus").html(dataResult);
						}
					});
				}else{
					alert(dataRes.message);
				}
			}
		});
});

	$(document).on('click', '#editmenu', function(e){
		
		var MenID = $(this).val();

			$.ajax({
				url:"backend/bk_addmenu.php",
				method:"POST",
				data:{
					MenID: MenID,
					request: 'editmenu',
					},

				success:function(dataResult){
						$("#menuedit_content").html(dataResult);
						$('#menueditmodal').modal('show');
				}
			});
	});

	$(document).on('click', '#em_submit', function(e){
		
		var em_submit = $(this).val();

		var em_menu = $('#em_menu').val();
		var em_mother = $('#em_mother').val();
		var em_desc = $('#em_desc').val();
		var em_code = $('#em_code').val();
		var em_link = $('#em_link').val();
		var em_arrange = $('#em_arrange').val();
		var em_icon = $('#em_icon').val();
		var em_status = $('#em_status').val();

			$.ajax({
				url:"backend/bk_addmenu.php",
				method:"POST",
				data:{
					em_submit: em_submit,
					em_menu: em_menu,
					em_mother: em_mother,
					em_desc: em_desc,
					em_code: em_code,
					em_link: em_link,
					em_arrange: em_arrange,
					em_icon: em_icon,
					em_status: em_status,
					request: 'savemenu'
					},

				success:function(dataResult){
					alert(dataResult);
					
					$('#menueditmodal').modal('hide');
					$.ajax({
						url:"backend/bk_menumanagement.php",
						method:"POST",
						data:{
							request:"viewMenus", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewMenus").html(dataResult);
						}
					});
				}
			});
	});

//==================================================================================================================

//Add Privilege ======================================================================================================

$(document).on('click', '#addpriv', function(e) {

	$('#privmodal').modal('show');

});

$(document).on('click', '#p_submit', function(e){

	var p_role = $("#p_role").val();
	var p_menu = $("#p_menu").val();
	var p_status = $("#p_status").val();
		$.ajax({
			url:"backend/bk_addpriv.php",
			method:"POST",
			data:{
				p_role: p_role,
				p_menu: p_menu,
				p_status: p_status,
				request: 'addpriv'
				},

			success:function(dataResult){
				alert(dataResult);
				
					$('#privmodal').modal('hide');
					$.ajax({
						url:"backend/bk_privilegemanagement.php",
						method:"POST",
						data:{
							request:"viewPrivilege", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewPrivilege").html(dataResult);
						}
					});
			}
		});
});

	$(document).on('click', '#editpriv', function(e){

		var URID = $(this).val();

			$.ajax({
				url:"backend/bk_addpriv.php",
				method:"POST",
				data:{
					URID: URID,
					request: 'editpriv',
					},

				success:function(dataResult){
						$("#privedit_content").html(dataResult);
						$('#priveditmodal').modal('show');
				}
			});
	});

	$(document).on('click', '#ep_submit', function(e){
		
		var ep_submit = $(this).val();

		var ep_role = $('#ep_role').val();
		var ep_menu = $('#ep_menu').val();
		var ep_status = $('#ep_status').val();

			$.ajax({
				url:"backend/bk_addpriv.php",
				method:"POST",
				data:{
					ep_submit: ep_submit,
					ep_role: ep_role,
					ep_menu: ep_menu,
					ep_status: ep_status,
					request: 'savepriv',
					},

				success:function(dataResult){
					alert(dataResult);
					
					$('#priveditmodal').modal('hide');
					$.ajax({
						url:"backend/bk_privilegemanagement.php",
						method:"POST",
						data:{
							request:"viewPrivilege", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewPrivilege").html(dataResult);
						}
					});
				}
			});
	});

//==================================================================================================================

//Document Types======================================================================================================

	$(document).on('click', '#adddoctype', function(e){
		
		var data = $(this).val();

			$.ajax({
				url:"backend/bk_doctypemanagement.php",
				method:"POST",
				data:{
					data: data,
					request: 'adddoctype'
					},

				success:function(dataResult){
						$('#doctypemodal').modal('show');
				}
			});
	});
	
	$(document).on('click', '#type_submit', function(e){
		
		var type_id = $('#type_id').val();
		var type_details = $('#type_details').val();
		var type_status = $('#type_status').val();
		var type_link = $('#type_link').val();

			$.ajax({
				url:"backend/bk_doctypemanagement.php",
				method:"POST",
				data:{
					type_id: type_id,
					type_details: type_details,
					type_status: type_status,
					type_link: type_link,
					request: 'saveaddedtype'
					},

				success:function(dataResult){
						alert(dataResult);
						
					$('#doctypemodal').modal('hide');
					$.ajax({
						url:"backend/bk_doctypemanagement.php",
						method:"POST",
						data:{
							request:"viewTypes", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewTypes").html(dataResult);
						}
					});
				}
			});
	});
	
	$(document).on('click', '#edit_type', function(e){

		var editdata = $(this).val();

			$.ajax({
				url:"backend/bk_doctypemanagement.php",
				method:"POST",
				data:{
					editdata: editdata,
					request: 'edittype',
					},

				success:function(dataResult){
						$("#doctypeedit_content").html(dataResult);
						$('#doctypeeditmodal').modal('show');
				}
			});
	});
	
	$(document).on('click', '#save_type', function(e){

		var savedata = $(this).val();
		
		var te_details = $('#te_details').val();
		var te_status = $('#te_status').val();
		var te_link = $('#te_link').val();
		
			$.ajax({
				url:"backend/bk_doctypemanagement.php",
				method:"POST",
				data:{
					savedata: savedata,
					request: 'savetype',
					te_details: te_details,
					te_status: te_status,
					te_link: te_link,
					},

				success:function(dataResult){
					alert(dataResult);
					
					$('#doctypeeditmodal').modal('hide');
					$.ajax({
						url:"backend/bk_doctypemanagement.php",
						method:"POST",
						data:{
							request:"viewTypes", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewTypes").html(dataResult);
						}
					});
				}
			});
	});

//==================================================================================================================

//Office Management======================================================================================================

$(document).on('click', '#addunit', function(e) {

	$('#unitmodal').modal('show');

});

	$(document).on('click', '#addunit_submit', function(e){
		
		var addunit_name = $('#addunit_name').val();
		var addunit_desc = $('#addunit_desc').val();
		var addunit_dept = $('#addunit_dept').val();
		var addunit_status = $('#addunit_status').val();
		
			$.ajax({
				url:"backend/bk_officemanagement.php",
				method:"POST",
				data:{
					request: 'addunit',
					addunit_name: addunit_name,
					addunit_desc: addunit_desc,
					addunit_dept: addunit_dept,
					addunit_status: addunit_status,
					},

				success:function(dataResult){
					alert(dataResult);
					
					$('#unitmodal').modal('hide');
					$.ajax({
						url:"backend/bk_officemanagementtables.php",
						method:"POST",
						data:{
							request:"viewUnits", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewUnits").html(dataResult);
						}
					});
				}
			});
	});
	
	$(document).on('click', '#edit_unit', function(e){
		
		var unit_data = $(this).val();
		
			$.ajax({
				url:"backend/bk_officemanagement.php",
				method:"POST",
				data:{
					request: 'editunit',
					unit_data: unit_data,
					},

				success:function(dataResult){

						$("#unitedit_content").html(dataResult);
						$('#uniteditmodal').modal('show');
				}
			});
	});
	
	$(document).on('click', '#editunit_submit', function(e){
		
		var saveunit_data = $(this).val();
		
		var editunit_name = $("#editunit_name").val();
		var editunit_desc = $("#editunit_desc").val();
		var editunit_dept = $("#editunit_dept").val();
		var editunit_status = $("#editunit_status").val();
		
		
			$.ajax({
				url:"backend/bk_officemanagement.php",
				method:"POST",
				data:{
					request: 'saveunit',
					saveunit_data: saveunit_data,
					editunit_name: editunit_name,
					editunit_desc: editunit_desc,
					editunit_dept: editunit_dept,
					editunit_status: editunit_status,
					},

				success:function(dataResult){
					alert(dataResult);
					
					$('#uniteditmodal').modal('hide');
					$.ajax({
						url:"backend/bk_officemanagementtables.php",
						method:"POST",
						data:{
							request:"viewUnits", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewUnits").html(dataResult);
						}
					});
				}
			});
	});


$(document).on('click', '#adddept', function(e) {

	$('#deptmodal').modal('show');

});

	$(document).on('click', '#adddept_submit', function(e){
		
		var adddept_name = $('#adddept_name').val();
		var adddept_desc = $('#adddept_desc').val();
		var adddept_office = $('#adddept_office').val();
		var adddept_status = $('#adddept_status').val();
		
			$.ajax({
				url:"backend/bk_officemanagement.php",
				method:"POST",
				data:{
					request: 'adddept',
					adddept_name: adddept_name,
					adddept_desc: adddept_desc,
					adddept_office: adddept_office,
					adddept_status: adddept_status,
					},

				success:function(dataResult){
					alert(dataResult);
					
					$('#deptmodal').modal('hide');
					$.ajax({
						url:"backend/bk_officemanagementtables.php",
						method:"POST",
						data:{
							request:"viewDepartments", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewDepartments").html(dataResult);
						}
					});
				}
			});
	});
	
	$(document).on('click', '#edit_dept', function(e){
		
		var dept_data = $(this).val();
		
			$.ajax({
				url:"backend/bk_officemanagement.php",
				method:"POST",
				data:{
					request: 'editdept',
					dept_data: dept_data,
					},

				success:function(dataResult){

						$("#deptedit_content").html(dataResult);
						$('#depteditmodal').modal('show');
				}
			});
	});	
	
	$(document).on('click', '#editdept_submit', function(e){
		
		var savedept_data = $(this).val();
		
		var editdept_name = $('#editdept_name').val();
		var editdept_desc = $('#editdept_desc').val();
		var editdept_office = $('#editdept_office').val();
		var editdept_status = $('#editdept_status').val();
		
			$.ajax({
				url:"backend/bk_officemanagement.php",
				method:"POST",
				data:{
					request: 'savedept',
					editdept_name: editdept_name,
					editdept_desc: editdept_desc,
					editdept_office: editdept_office,
					editdept_status: editdept_status,
					savedept_data: savedept_data
					},

				success:function(dataResult){
					alert(dataResult);
					
					$('#depteditmodal').modal('hide');
					$.ajax({
						url:"backend/bk_officemanagementtables.php",
						method:"POST",
						data:{
							request:"viewDepartments", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewDepartments").html(dataResult);
						}
					});
				}
			});
	});

$(document).on('click', '#addoffice', function(e) {

	$('#officemodal').modal('show');

});

	$(document).on('click', '#addoffice_submit', function(e){
		
		var addoffice_name = $('#addoffice_name').val();
		var addoffice_desc = $('#addoffice_desc').val();
		var addoffice_status = $('#addoffice_status').val();
		
			$.ajax({
				url:"backend/bk_officemanagement.php",
				method:"POST",
				data:{
					request: 'addoffice',
					addoffice_name: addoffice_name,
					addoffice_desc: addoffice_desc,
					addoffice_status: addoffice_status
					},

				success:function(dataResult){
					alert(dataResult);

					$('#officemodal').modal('hide');
					$.ajax({
						url:"backend/bk_officemanagementtables.php",
						method:"POST",
						data:{
							request:"viewOffices", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewOffices").html(dataResult);
						}
					});
				}
			});
	});

	$(document).on('click', '#edit_office', function(e){

		var office_data = $(this).val();

			$.ajax({
				url:"backend/bk_officemanagement.php",
				method:"POST",
				data:{
					request: 'editoffice',
					office_data: office_data,
					},

				success:function(dataResult){

						$("#officeedit_content").html(dataResult);
						$('#officeeditmodal').modal('show');
				}
			});
	});	
	
	$(document).on('click', '#editoffice_submit', function(e){
		
		var saveoffice_data = $(this).val();
		
		var editoffice_name = $('#editoffice_name').val();
		var editoffice_desc = $('#editoffice_desc').val();
		var editoffice_status = $('#editoffice_status').val();
		
			$.ajax({
				url:"backend/bk_officemanagement.php",
				method:"POST",
				data:{
					request: 'saveoffice',
					editoffice_name: editoffice_name,
					editoffice_desc: editoffice_desc,
					editoffice_status: editoffice_status,
					saveoffice_data: saveoffice_data
					},

				success:function(dataResult){
					alert(dataResult);

					$('#officeeditmodal').modal('hide');
					$.ajax({
						url:"backend/bk_officemanagementtables.php",
						method:"POST",
						data:{
							request:"viewOffices", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewOffices").html(dataResult);
						}
					});
				}
			});
	});

//Profile Editor =============================================================================================================
$(document).on('click', '#profile_oldpassword', function(e){
	$('#nextpass').show();
});

$(document).on('click', '#profile_newpassword', function(e){
	$('#nextnextpass').show();
});

$(document).on('click', '#profile_submit', function(e){

	var profile_data = $(this).val();

	var profile_name = $('#profile_name').val();
	var profile_email = $('#profile_email').val();
	var profile_oldpassword = $('#profile_oldpassword').val();
	var profile_newpassword = $('#profile_newpassword').val();
	var profile_confirmpassword = $('#profile_confirmpassword').val();

	var oldpassword = UserInfo["Password"];

	if(profile_oldpassword != oldpassword){
		alert("Please enter your correct current Password!");
	}

	else if(profile_newpassword != profile_confirmpassword){
		alert("New and Confirmation Passwords don't match!");
	}

	else if ((profile_oldpassword == oldpassword) && (!profile_newpassword)) {
		$.ajax({
			url:"backend/bk_profile.php",
			method:"POST",
			data:{
				request: 'saveprofileold',
				profile_name: profile_name,
				profile_email: profile_email,
				profile_oldpassword: profile_oldpassword,
				profile_data: profile_data
				},

			success:function(dataResult){
				alert(dataResult);
			}
		});
	}

	else {
		$.ajax({
			url:"backend/bk_profile.php",
			method:"POST",
			data:{
				request: 'saveprofilenew',
				profile_name: profile_name,
				profile_email: profile_email,
				profile_newpassword: profile_newpassword,
				profile_data: profile_data
				},

			success:function(dataResult){
				alert(dataResult);
			}
		});
	}
});

//Complete Document ==================================================================================================================

	$(document).on('click', '#complete_docpreview', function(e){

		var complete_data = $(this).val();

			$.ajax({
				url:"backend/bk_receivedoc.php",
				method:"POST",
				data:{
					request: 'previewcomplete',
					complete_data: complete_data,
					},

				success:function(dataResult){

						$("#complete_content").html(dataResult);
						$('#completemodal').modal('show');
				}
			});
	});	

$(document).on('click', '#complete_doc', function(e){

	var savecomplete_data = $(this).val();

		$.ajax({
			url:"backend/bk_receivedoc.php",
			method:"POST",
			data:{
				request: 'savecomplete',
				savecomplete_data: savecomplete_data,
				},

			success:function(dataResult){
				alert(dataResult);

					$('#completemodal').modal('hide');
					$.ajax({
						url:"backend/bk_receivedtable.php",
						method:"POST",
						data:{
							request:"viewreceived", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"]
							},
						success:function(dataResult){
								$("#tblviewreceived").html(dataResult);
						}
					});
			}
		});
});	

//View Attachment=====================================================================================================

$(document).on('click', '#view_attachment', function(e){
	var highwayid = $(this).attr('data-highwayid');
	var attrydata = $(this).attr('data-openattach');

	if($(this).val() =="Close"){
		$(this).val("View");
		$("#ajaxnewtd"+highwayid).hide();
		$(this).removeAttr('style');
	}
	else{
		$(this).val("Close");
		$(this).closest("#trackhead").after("<tr id='ajaxnewtd"+highwayid+"'><td colspan='9'><iframe src='"+attrydata+"' width = '100%' height = '500' ></iframe></td></tr>");
		$(this).css('background-color','red');
	}

});	

//==================================================================================================================

//DocTrans==========================================================================================================

	$(document).on('click', '#doctrans_edit', function(e){
		
		var highway = $(this).val();
		var sname = $(this).attr('data-sname');
		var soffice = $(this).attr('data-soffice');
		var rname = $(this).attr('data-rname');
		var roffice = $(this).attr('data-roffice');
		var dstatus = $(this).attr('data-status');
		
			$.ajax({
				url:"backend/bk_doctransmanagement.php",
				method:"POST",
				data:{
					request: 'editdoctrans',
					highway: highway,
					sname: sname,
					soffice: soffice,
					rname: rname,
					roffice: roffice,
					dstatus: dstatus
					},

				success:function(dataResult){

						$("#doctransedit_content").html(dataResult);
						$('#doctranseditmodal').modal('show');
				}
			});
	});
	
	$(document).on('click', '#edt_submit', function(e){
		
		var highway = $(this).val();
		var edt_tracking = $("#edt_tracking").val();
		var edt_sender = $("#edt_sender").val();
		var edt_senderoffice = $("#edt_senderoffice").val();
		var edt_released = $("#edt_released").val();
		var edt_remarks = $("#edt_remarks").val();
		var edt_receiver = $("#edt_receiver").val();
		var edt_receiveroffice = $("#edt_receiveroffice").val();
		var edt_received = $("#edt_received").val();
		var edt_status = $("#edt_status").val();
		var edt_active = $("#edt_active").val();
		
		
			$.ajax({
				url:"backend/bk_doctransmanagement.php",
				method:"POST",
				data:{
					request: 'savedoctrans',
					highway: highway,
					edt_tracking: edt_tracking,
					edt_sender: edt_sender,
					edt_senderoffice: edt_senderoffice,
					edt_released: edt_released,
					edt_remarks: edt_remarks,
					edt_receiver: edt_receiver,
					edt_receiveroffice: edt_receiveroffice,
					edt_received: edt_received,
					edt_status: edt_status,
					edt_active: edt_active,
					},

				success:function(dataResult){
					alert(dataResult);
					
					$('#doctranseditmodal').modal('hide');
					$.ajax({
						url:"backend/bk_doctransmanagement.php",
						method:"POST",
						data:{
							request:"viewDocTrans"
							},
						success:function(dataResult){
								$("#tblviewDocTransactions").html(dataResult);
						}
					});


				}
			});
	});
//=====================================================================================================

//Position Management==================================================================================

	$(document).on('click', '#addposition', function(e){

		$('#positionmodal').modal('show');
	});
	
	$(document).on('click', '#addpos_submit', function(e){
		
		var poscode = $("#addpos_code").val();
		var posdesc = $("#addpose_desc").val();
		var posplant = $("#addpos_plant").val();
		var posstat = $("#addpos_status").val();
		
			$.ajax({
				url:"backend/bk_positionmanagement.php",
				method:"POST",
				data:{
					request: 'addposition',
					poscode: poscode,
					posdesc: posdesc,
					posplant: posplant,
					posstat: posstat,
					},

				success:function(dataResult){
					alert(dataResult);
					
					$('#positionmodal').modal('hide');
					$.ajax({
						url:"backend/bk_positionmanagement.php",
						method:"POST",
						data:{
							request:"viewPositions"
							},
						success:function(dataResult){
								$("#tblviewPositions").html(dataResult);
						}
					});


				}
			});
	});
	
	$(document).on('click', '#edit_position', function(e){
		
		var pos_data = $(this).val();
		
			$.ajax({
				url:"backend/bk_positionmanagement.php",
				method:"POST",
				data:{
					request: 'editposition',
					pos_data: pos_data,
					},

				success:function(dataResult){

						$("#positionedit_content").html(dataResult);
						$('#positioneditmodal').modal('show');
				}
			});
	});

	$(document).on('click', '#posedit_submit', function(e){

		var pos_data = $(this).val();

		var pos_code = $("#posedit_code").val();
		var pos_desc = $("#posedit_desc").val();
		var pos_plant = $("#posedit_plant").val();
		var pos_stat = $("#posedit_status").val();
		
			$.ajax({
				url:"backend/bk_positionmanagement.php",
				method:"POST",
				data:{
					request: 'saveposition',
					pos_data: pos_data,
					pos_code: pos_code,
					pos_desc: pos_desc,
					pos_plant: pos_plant,
					pos_stat: pos_stat
					},

				success:function(dataResult){
					alert(dataResult);

					$('#positioneditmodal').modal('hide');
					$.ajax({
						url:"backend/bk_positionmanagement.php",
						method:"POST",
						data:{
							request:"viewPositions"
							},
						success:function(dataResult){
								$("#tblviewPositions").html(dataResult);
						}
					});


				}
			});
	});

	$(document).on('click', '#view_position', function(e){
		
		var tagged_data = $(this).val();
		
			$.ajax({
				url:"backend/bk_positionmanagement.php",
				method:"POST",
				data:{
					request: 'viewPositionsTagged',
					tagged_data: tagged_data,
					},

				success:function(dataResult){

						$("#viewposition_content").html(dataResult);
						$('#viewpositionmodal').modal('show');
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

class TableLoader2 {
    static load2({ tableId, url, request, onSuccess = null }) {
        $.post(url, request, function(data) {
            // If a custom success callback is provided, call it with the response data
            if (typeof onSuccess === "function") {
                onSuccess(data, tableId);
            } else {
                // Otherwise, insert the response HTML into the specified table element
                $(tableId).html(data);
            }
        }).fail(function(xhr, status, error) {
            // Log an error if the request fails
            console.error("Failed to load table:", error);
        });
    }
}

//===============================================================================================

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

//Pagination========================================================================================

$(document).on('change', '#pagilimit', function(e){

	var pagi_limit = $(this).val();
	var tableid = $(this).attr('data-tableid');
	var tableurl = $(this).attr('data-tableurl');
	var tablereq = $(this).attr('data-tablereq');

		$.ajax({
			url:tableurl,
			method:"POST",
			data:{
				pagi_limit: pagi_limit,
				request: tablereq
				},

			success:function(dataResult){

				TableLoader2.load2({
					tableId: tableid,
					url: tableurl,
					request: { request:tablereq, user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"], pagi_limit:pagi_limit, },
				  });
				  
				   $("#loadercount").html("<i class='text-danger'>Showing "+pagi_limit+" rows...</i>");

				}
		});
});

$(document).on('click', '#addpagi', function(e){

	$('#pagimodal').modal('show');

});

$(document).on('click', '#addpagi_submit', function(e){

	var pagi_id = $("#addpagi_id").val();
	var pagi_number = $("#addpagi_num").val();
	var pagi_status = $("#addpagi_status").val();
	var pagi_cond = $("#addpagi_cond").val();

		$.ajax({
			url:"backend/bk_paginationmanagement.php",
			method:"POST",
			data:{
				request: 'addpagi',
				pagi_id: pagi_id,
				pagi_number: pagi_number,
				pagi_status: pagi_status,
				pagi_cond: pagi_cond
				},

			success:function(dataResult){
				alert(dataResult);
				
				$('#pagimodal').modal('hide');
				$.ajax({
					url:"backend/bk_paginationmanagement.php",
					method:"POST",
					data:{
						request:"viewpagination"
						},
					success:function(dataResult){
							$("#tblviewpagination").html(dataResult);
					}
				});


			}
		});
});

$(document).on('click', '#pagi_edit', function(e){
	
	var data = $(this).val();
	
		$.ajax({
			url:"backend/bk_paginationmanagement.php",
			method:"POST",
			data:{
				request: 'editpagination',
				data: data,
				},

			success:function(dataResult){

					$("#paginationedit_content").html(dataResult);
					$('#paginationeditmodal').modal('show');
			}
		});
});

$(document).on('click', '#pagiedit_submit', function(e){

	var pagi_data = $(this).val();

	var pagiedit_id = $("#pagiedit_id").val();
	var pagiedit_number = $("#pagiedit_number").val();
	var pagiedit_status = $("#pagiedit_status").val();
	var pagiedit_cond = $("#pagiedit_cond").val();

		$.ajax({
			url:"backend/bk_paginationmanagement.php",
			method:"POST",
			data:{
				request: 'savepagi',
				pagi_data: pagi_data,
				pagiedit_id: pagiedit_id,
				pagiedit_number: pagiedit_number,
				pagiedit_status: pagiedit_status,
				pagiedit_cond: pagiedit_cond
				},

			success:function(dataResult){
				alert(dataResult);

				$('#paginationeditmodal').modal('hide');
				$.ajax({
					url:"backend/bk_paginationmanagement.php",
					method:"POST",
					data:{
						request:"viewpagination"
						},
					success:function(dataResult){
							$("#tblviewpagination").html(dataResult);
					}
				});

			}
		});
});

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


// My additional code

function GetOffice(){
	//alert("alert");
	$.ajax({
		data : {
			request:"GetOffice"
			
		},
		type: "post",
		url: "backend/bk_mnuReportSummary.php",
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		success: function(dataResult){	
		//alert(dataResult);
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			$("select[name='sltoffice']").html(dataResult);
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	}); 
}
function GetHRISOffice(){
	let dsble = UserInfo['AllOfficeAcess']==="0"?"disabled":"";
	 $('#sltoffice').prop(dsble, true); 
	 //alert(UserInfo["EmpID"]);
	var officeID = UserInfo["Office_id"];
	$.ajax({
		data : {
			request:"GetHRISOffice2"
			,dsble:dsble
			,EmpID:UserInfo["EmpID"]
			
		},
		type: "post",
		url: "backend/bk_mnuReportSummary.php",
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		success: function(dataResult){	
		//alert(dataResult);
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			$("select[name='sltoffice']").html(dataResult);
			$("#sltoffice").val(officeID);
			
			$.ajax({
				data: {
					request:"GetAllEmpBYOffice"
					,OfficeID:officeID
				},
				type: "post",
				url: "backend/bk_mnuReportSummary.php",
				success: function(dataResult){
					$("#dtrLstEmplo").html(dataResult);
				}
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
}
function DynamicGetHRISOffice(){
	$.ajax({
		data : {
			request:"GetHRISOffice"
		},
		type: "post",
		url: "backend/bk_mnuReportSummary.php",
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		success: function(dataResult){	
		//alert(dataResult);
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			$("select[name='sltoffice']").html(dataResult);
			
			
		},
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	}); 
}


$(document).on('change','#sragrpslt',function(e) { 	
	var Month = $("[name='sltMonth']").length ? $("[name='sltMonth']").val():"";
	var Year = $("[name='sltYear']").length ? $("[name='sltYear']").val():"";
	var office = $("[name='sltoffice']").length ? $("[name='sltoffice']").val():"";
	if (Month !== "" && Year !== "" && office !== "") {
		$.ajax({
			data : {
				request:"MonthSummaryReport"
				,Month:Month
				,Year:Year
				,office:office
			}, 
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},
			type: "post",
			url: "backend/bk_mnuReportSummary.php",
			success: function(dataResult){	
				
				$("#loadingSpinner").fadeOut(200, function() {
					$(this).css("display", "none");
				});
				$("#DataSummaryDTRReport").html(dataResult);
			},
			error: function(xhr, status, error) {
				// Hide the animation and show an error message
				$("#loadingSpinner").fadeOut(200, function() {
					$(this).css("display", "none");
				});

				console.error("Error: " + error);
				$("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
			}
		}); 
	}else{
		alert("select first Month, Year and Office before procced.");
	}
}); 

$(document).on("click", "#ConvertPDF", function() {
	
	//alert("");
	var request = $(this).attr("data-request");
	
	var Month = $("[name='sltMonth']").length ? $("[name='sltMonth']").val():"";
	var Year = $("[name='sltYear']").length ? $("[name='sltYear']").val():"";
	var office = $("[name='sltoffice']").length ? $("[name='sltoffice']").val():"";
	if (Month !== "" && Year !== "" && office !== "") {
	$.ajax({
		data: {
			request:request
			,Month:Month
			,Year:Year
			,office:office
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_pdfreport.php",
		success: function(dataResult){
				//alert(dataResult);
				
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
				
				const newTab = window.open(); 
				newTab.document.write('<html><head><title>PDF Viewer</title></head><body><iframe src="vendor/DirFile/PDFGen.pdf" width="100%" height="100%" style="border: none;"></iframe></body></html>'); // Embed PDF inside an iframe
				
				newTab.document.close(); 
			
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});
	}else{
		alert("select first Month, Year and Office before procced.");
	}
	MonthClick="";
});

$(document).on("click", "#IDviewDTR", function() {
	let selectedVal = $("input[name='rdorptdtr']:checked").data("val");
	
	let empIDs = [];
	$(".chekEmp:checked").each(function () {
	  empIDs.push($(this).data("empid"));
	});
	
	var request = $(this).attr("data-request");
	
	var Month = $("[name='sltMonth']").length ? $("[name='sltMonth']").val():"";
	var Year = $("[name='sltYear']").length ? $("[name='sltYear']").val():"";
	var office = $("[name='sltoffice']").length ? $("[name='sltoffice']").val():"";
	//if (Month !== "" && Year !== "" && office !== "") {
	$.ajax({
		data: {
			request:request
			,Month:Month
			,Year:Year
			,office:office
			,selectedVal:selectedVal
			,empIDs:empIDs
			
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_DTR.php",
		success: function(dataResult){
				//alert(dataResult);
			$("#loadingSpinner").fadeOut(200, function() {
				$(this).css("display", "none");
			});
			$("#divDTR").html(dataResult);
			
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});
	/* }else{
		alert("select first Month, Year and Office before procced.");
	} */
});
$(document).on("click", "#addNewStaff", function() {
	//alert("");
	$('#addStaffModal').modal('show');
	getallEmp();
});

$(document).on("change", "#sltAddEmpl", function() {
			
	$.ajax({
		data: {
			request:"showinfo"
			,EmpID:$(this).val()
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_mnuMgtOrgStructure.php",
		success: function(dataResult){
			var dataRes = JSON.parse(dataResult);
			if (dataRes.image) {
				$('#PicEMp').attr('src', 'data:image/jpeg;base64,' + dataRes.image);
				$("#loadingSpinner").fadeOut(200, function() {
					$(this).css("display", "none");
				});
			}else {
				$('#PicEMp').attr('src', 'image/tau.jpg'); // fallback
				$("#loadingSpinner").fadeOut(200, function() {
					$(this).css("display", "none");
				});
			}
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});
});
/*
$(document).on("click", "#addNewStaff", function() {
	//alert("");
	$('#addStaffModal').modal('show');
});
*/
function getTAUEmp(){
	$.ajax({
		data: {
			request:"GetAllEmployee"
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_mnuMgtOrgStructure.php",
		success: function(dataResult){
			//alert(dataResult);
			$("#sltAddEmpl").html(dataResult);
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});
}

$(document).on("click", "#addOfficeStaff", function() {
	var sltpositions = $("#sltpositions").val();
	var sltAddEmpl = $("#sltAddEmpl").val();

	if (sltAddEmpl.length === 0) {
		alert("Please select Employee before procced.");
		return; // Exit the function
	} else {
		if (sltpositions.length === 0) {
			alert("Please select position before procced.");
			return; // Exit the function
		} 
	}
	var OfficeMenID = (UserInfo["SpcfcOfficeID"]) 
		? UserInfo["SpcfcOfficeID"] 
		: UserInfo["Office_id"];
		
	$.ajax({
		data: {
			request:"AddOfficeStaff"
			,EmpID:sltAddEmpl
			,posID:sltpositions
			,Officeid:OfficeMenID
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_mnuMgtOrgStructure.php",
		success: function(dataResult){
			$("#ShowAllStaff").append(dataResult);
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
$(document).on("change", "#cheksltEmp", function() {
	
	
	
		$('.chekEmp').prop('checked', $(this).prop('checked'));
});
function showMyOffice(OfficeId){
	$.ajax({
		data: {
			request:"showMyOffice"
			,OfficeMenID:OfficeId
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_mnuMgtOrgStructure.php",
		success: function(dataResult){
			//alert(dataResult);
			var dataRes = JSON.parse(dataResult);
			if (dataRes.OfficeName) {
				//alert(dataResult);
				$("#LabelMyOffice").append(dataRes.OfficeName);
				$("#ShowAllStaff").append(dataRes.Picture);
				$("#loadingSpinner").fadeOut(200, function() {
					$(this).css("display", "none");
				});
			}
		},
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});
}
function getallEmp(OfficeMotherMenID,OffTab){
	$.ajax({
		data: {
			request:"GetAllOffice"
			,OfficeMotherMenID:OfficeMotherMenID
			,OffTab:OffTab
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_offMgt.php",
		success: function(dataResult){
			var dataRes = JSON.parse(dataResult);
			$("#orgTab").append(dataRes.TabDetails);
			$("#orgTabContent").append(dataRes.tblDetails);
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});
	officeTabNo = officeTabNo+1;
	
}

$(document).on("click", "#showOfficeStaff", function() {
	//alert("");
	$("#mdlOfficeStaff").modal('show');
	
	//$("#officename").text("Office: "+$(this).attr("data-OfficeName"));
	UserInfo["SpcfcOfficeID"] = $(this).attr("data-OfficeMenID");
	$.ajax({
        type: "POST",
        url: "page/mnuMgtOrgStructure.php",
		
		beforeSend: function() {
          $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
            },		
			
        success: function(dataResult) {
			//alert(dataResult);
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            $("#showOfficeSaf").html(dataResult);
        },
        error: function(xhr, status, error) {
			$("#loadingSpinner").fadeOut(200).css("display", "none");
            console.error("AJAX error:", error);
            $("#showOfficeSaf").html("<p class='p-3'>Error loading page: " + error + "</p>");
        }
    });
	
	
});

$(document).on("dblclick", "#trselect", function() {
	$(".trselect").css("background-color", "");
	$(".icon-cell").html("");
  $(this).css("background-color", "#EEFCEE");
  //$(this).find(".icon-cell").html('✅');
  $(this).find(".icon-cell").html('👉');  
  
	  
  for( var t=parseInt($(this).attr("data-OffTab"))+1; t<officeTabNo;t++){
			//alert(t & officeTabNo);
	 
    if (t !== officeTabNo) {
		 $.ajax({
			data: {
				request:"removeTab"
				,OffTab:t
			},
			type: "post",
			url: "backend/bk_offMgt.php",
			success: function(dataResult){ //dtabID
				var dataRes = JSON.parse(dataResult);
				
				if (dataRes.dtabID) {
					 $('#orgTab').find('#'+dataRes.dtabID+"-tab").remove();
					$('#orgTabContent').find('#'+dataRes.dtabID).remove();
				}
				 
			}
		});
	}
	  
  }
  
  var OffTab = parseInt($(this).attr("data-OffTab")) +1;
  //alert(OffTab);
  getallEmp($(this).attr("data-MotherOfficeMenID"),OffTab);
  
});
$(document).on("click", "#ppof", function() { //showppof
	//alert("bryan");
	var popDiv = $(this).attr("data-showppof"); //$(this).attr("data-popDiv");
	$("#"+popDiv).css("display", "block");
});

$(document).on("click", "#shwmdlEmpInfo", function() { //showppof
	var EmpNo = $(this).attr("data-EmpNo");
	var EmpName = $(this).attr("data-EmpName");
	var PositionId =  $(this).attr("data-PositionId");
	var OfStaffID =  $(this).attr("data-OfStaffID");
	
	var IsPlantilla =  $(this).attr("data-Plantilla")=="0"?false:true;
	$('#chkMOIsPlantilla').prop("checked",IsPlantilla);
	
	
	UserInfo["OfStaffID"] = $(this).attr("data-OfStaffID");
	
	
	$('#clkEmpNo').html(EmpNo);
	$('#clkEmpName').html(EmpName);
	$('#sltEditPos').val(PositionId); 
	
	
	// =================================================
	
	$("div#MultiRecPosition").remove();
	
	$("#frmShowEmpInfo #updStaffInfo").before(
		"<div class='mb-3' id='MultiRecPosition'>" +
			"<label for='staffEmail' class='form-label text-danger'>Other Position or Designation</label>" +
		"</div>"
	);
	
	$.ajax({
		data: {
			request:"getAdditionPosition"
			,OfStaffOfficeID:PositionId
			,OfStaffEmpNo:EmpNo
			,OfStaffID:OfStaffID
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_mnuMgtOrgStructure.php",
		success: function(dataResult){
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			
			//$("#frmShowEmpInfo").append(dataResult);
			
			$("#frmShowEmpInfo #updStaffInfo").before(
				dataResult
			);
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});

// =================================================
	
	
	$('#mdlShowEMpInfo').modal('show');
});

$(document).on("click", "#shwmdlReEntry", function() { 

	UserInfo["OfStaffID"] = $(this).attr("data-OfStaffID");
	UserInfo["OfStaffEmpNo"] = $(this).attr("data-EmpNo");
	UserInfo["OfStaffOfficeID"] = $(this).attr("data-OfficeID");
	UserInfo["request"] = $(this).attr("data-request");
	$('#mdlShowReEntryPass').modal('show');
});

$(document).on("click", "#btnmdlContinue", function() { 

	$.ajax({
		data: {
			request:UserInfo["request"]
			,OfStaffID:UserInfo["OfStaffID"]
			,OfStaffEmpNo:UserInfo["OfStaffEmpNo"]
			,OfStaffOfficeID:UserInfo["OfStaffOfficeID"]
			,ReEntryPass:$("#txtmdlReEntryPass").val()
			,UserID:UserInfo["UserID"]
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_mnuMgtOrgStructure.php",
		success: function(dataResult){
			
				//alert(dataResult);
			
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			
			var dataRes = JSON.parse(dataResult);
			if (dataRes.status=="failed") {
				alert(dataRes.message);
				$("#txtmdlReEntryPass").val("");
			}else{
				$('#mdlShowReEntryPass').modal('hide');
				alert(dataRes.message);
				$("#txtmdlReEntryPass").val("");
				$('#dvEmpPicInfo'+UserInfo["OfStaffID"]).remove();
			}
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});
	
});

$(document).on("click", "#updStaffInfo", function() { 
	$.ajax({
		data: {
			request:"EmpUpdatePosition"
			,positionID:$("#sltEditPos").val()
			,empNo:$("#clkEmpNo").text()
			,UserID:UserInfo["UserID"]
			,OfStaffID:UserInfo["OfStaffID"]
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_mnuMgtOrgStructure.php",
		success: function(dataResult){
			
				//alert(dataResult);
			
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			
			var dataRes = JSON.parse(dataResult);
			if (dataRes.status=="failed") {
			}else{
				
				//alert($("[name='shwmdlEmpInfo"+UserInfo["OfStaffID"]+"']").attr("data-PositionId"));
				$("[name='shwmdlEmpInfo"+UserInfo["OfStaffID"]+"']").attr("data-PositionId",$("#sltEditPos").val());
				$('#mdlShowEMpInfo').modal('hide');
			} 
				alert(dataRes.message);
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});
	
	
});

$(document).on("click", "#chkMOIsPlantilla", function() { 
	$.ajax({
		data: {
			request:"EmpUpPaltilla"
			,Plantilla:($(this).prop("checked")== true?1:0)
			,OfStaffID:UserInfo["OfStaffID"]
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_mnuMgtOrgStructure.php",
		success: function(dataResult){
			
				//alert(dataResult);
			
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			 
			var dataRes = JSON.parse(dataResult);
			/*if (dataRes.status=="failed") {
			}else{
				
				//alert($("[name='shwmdlEmpInfo"+UserInfo["OfStaffID"]+"']").attr("data-PositionId"));
				$("[name='shwmdlEmpInfo"+UserInfo["OfStaffID"]+"']").attr("data-PositionId",$("#sltEditPos").val());
				$('#mdlShowEMpInfo').modal('hide');
			} */
			//alert(dataRes.message); 
			
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});
	
	
});

$(document).on("change", "#sltoffice", function() { 
	$.ajax({
		data: {
			request:"GetAllEmpBYOffice"
			,OfficeID:$(this).val()
		},
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		type: "post",
		url: "backend/bk_mnuReportSummary.php",
		success: function(dataResult){
			
				//alert(dtrLstEmplo);
			
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			$("#dtrLstEmplo").html(dataResult);
		},
        error: function(xhr, status, error) {
            // Hide the animation and show an error message
            $("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });

            console.error("Error: " + error);
            $("#mainContent").html("<div class='alert alert-danger' role='alert'>Page Module loading error. Please try again later.</div>");
        }
	});
	
	
});

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


$(document).on("click", "#ResetPass", function() { 
	UserInfo["Edituserid"] = $(this).attr("data-Edituserid"); 
	$('#mdlResetPass').modal('show');
});


$(document).on("click", "#rvmUAOffice", function() { 
	
	let officeId = $(this).data("officeid");
	$("[data-OfficeID='" + officeId + "']").closest(".form-group").remove();
	$.ajax({
        type: "POST",
        url: "backend/bk_usermanagement.php",
        data: { 
			request: "rvmUAOffice"
			,EditEmpID: $(this).attr("data-EmpID")
			,EditOfficeID: $(this).attr("data-OfficeID")
			,userId:UserInfo["UserID"]
		},
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResults) {
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			//$("#rvmUAOffice").closest(".form-group").remove();
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
        }
    });
});
$(document).on("click", "#AUAOffice", function() { 
	
	$.ajax({
        type: "POST",
        url: "backend/bk_usermanagement.php",
        data: { 
			request: "AUAOffice"
			,EditEmpID: $(this).attr("data-EditEmpID")
			,EditOfficeID: $("#sltoffice").val()
			,userId:UserInfo["UserID"]
		},
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResultsz) {
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			var dataRes = JSON.parse(dataResultsz);
			if (dataRes.status=="failed") {
				alert("This office already exists!");
			}else if (dataRes.status=="success") {
				$.ajax({
					type: "POST",
					url: "backend/bk_usermanagement.php",
					data: { 
						request: "GetUserOfficeAccess"
						,EditEmpID: $("#AUAOffice").attr("data-EditEmpID")
						,where: dataRes.where
					},
					beforeSend: function() {
						$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
					},
					success: function(dataResults) {
						$("#loadingSpinner").fadeOut(200, function() {
							$(this).css("display", "none");
						});
						$("#scrollOfficeList").append(dataResults);
					},
					error: function(xhr, status, error) {
						$("#loadingSpinner").fadeOut(200).css("display", "none");
					}
				});
				
			}
			
			
			
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
        }
    });
});
//

$(document).on("click", "#OfficeAccess", function() { 

	$('#useLstOffAcc').modal('show');
	$('#AUAOffice').attr("data-EditEmpID",$(this).attr("data-EmpID"));
	
	$.ajax({
        type: "POST",
        url: "backend/bk_usermanagement.php",
        data: { 
			request: "GetUserOfficeAccess"
			,EditEmpID: $(this).attr("data-EmpID")
		},
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResults) {
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			$("#scrollOfficeList").html(dataResults);
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
        }
    });
});

 
$(document).on('click', '#ContinueResetPass', function(e) {
    var Edituserid = UserInfo["Edituserid"]
    var userid = UserInfo["UserID"]
    var lgtxtpassword = $("#txtmdlReEntryPass").val();
			

    $.ajax({
        type: "POST",
        url: "backend/bk_usermanagement.php",
        data: { request: "ResetPass"
			,userId:userid
			,lgtxtpassword:lgtxtpassword
			,Edituserid:Edituserid
		},
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
        success: function(dataResults) {
            //alert(dataResults); 
			$("#loadingSpinner").fadeOut(200, function() {
                $(this).css("display", "none");
            });
			
			var dataRes = JSON.parse(dataResults);
			if (dataRes.status=="failed") {
				alert(dataRes.message);
				$("#txtmdlReEntryPass").val("");
			}else{
				$('#mdlResetPass').modal('hide');
				alert(dataRes.message);
				
			}
        },
        error: function(xhr, status, error) {
            $("#loadingSpinner").fadeOut(200).css("display", "none");
        }
    });
});

$(document).on('change', '#SingleUpdate', function(e) {

    let Upval =$(this).is(":checkbox") 
		? ($(this).prop("checked")== true?0:1)
		: $(this).val(); 
	//alert(Upval); return;
	
	$.ajax({
		url:"backend/bk_Dynamic.php",
		method:"POST",
		data:{
			request: 'Update'
			,table:  $(this).attr("data-table")
			,UpFld:  $(this).attr("data-UpFld")
			,Upval:  Upval
			,FltFld:  $(this).attr("data-FltFld")
			,FltID:  $(this).attr("data-FltID")
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

//========== CSC Form Management ======================

function loadTable(){
	$.ajax({
		url: "backend/bk_formManagement.php",
		type: "POST"
		,data: {
			request: "load"
		}
		,success: function(data){
			$("#formsTable tbody").html(data);
		}
	});
}



// Submit Form
$("#cscForm").on("submit", function(e){
	e.preventDefault();
	$.ajax({
		url: "backend/bk_formManagement.php",
		type: "POST",
		data: {
			request: "save",
			CSCFrmID: arrCSCform["CSCFrmID"],
			FormNo: $("#FormNo").val(),
			CSCForm: $("#CSCForm").val(),
			Revised: $("#Revised").val(),
			UnActive: $("#UnActive").val(),
			Oper:arrCSCform["Oper"]
		},
        beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },
		success: function(response){
			alert(response);
			loadTable();
			$("#cscForm")[0].reset();
			$('#formCSCModal').modal('hide');
		}
		,complete: function(){
            $("#loadingSpinner").fadeOut(200).css("display", "none");
			$("#cscForm")[0].reset();
			$('#formCSCModal').modal('hide');
		}
	});
});

//$(document).on('click', '#formsTable tbody tr', function(){
$(document).on('click', '#showCSCFrmEdit', function(){
	let $tr = $(this).closest('tr');
	arrCSCform["CSCFrmID"] = $tr.find('td:eq(0)').text();
	let formNo = $tr.find('td:eq(1)').text();
	let cscForm = $tr.find('td:eq(2)').text();
	let revised = $tr.find('td:eq(3)').text();
	let unactive = $tr.find('td:eq(4)').text() === 'Inactive' ? '1' : '0';


	$('#FormNo').val(formNo);
	$('#CSCForm').val(cscForm);
	$('#Revised').val(revised);
	$('#UnActive').val(unactive);
	$('#formCSCModal').modal('show');
	
	//$('#FormNo').prop('disabled', true);
	arrCSCform["Oper"] = "Update";
});

$('#formCSCModal').on('hidden.bs.modal', function () {
	//$('#FormNo').prop('disabled', false);
	$('#cscForm')[0].reset();	
	arrCSCform["Oper"] = "";
});

$(document).on('click', '.popup-icon', function(e){
    e.stopPropagation(); // prevent row click
    var popup = $(this).siblings('.popup-div');
    $('.popup-div').not(popup).hide(); // hide other popups
    popup.toggle(); // toggle current popup
});
$(document).on('click', '.popup-close', function(e){
    e.stopPropagation();
    $(this).parent('.popup-div').hide();
});


$(document).on('click', '#ShowSignatories', function(e){
    e.preventDefault();
	let $tr = $(this).closest('tr');
	let cscForm = $tr.find('td:eq(2)').text();
	
    $('#tltModal').text(cscForm+" Signatories");
    // Get the parent <tr> of the clicked link
    /* let $tr = $(this).closest('tr');
    let formNo = $tr.find('td:eq(1)').text(); // assuming FormNo is in second td

    // Clear previous table data
    $('#signatoriesTable tbody').empty();

    // Fetch signatories via AJAX (assuming you have an endpoint to get JSON)
    $.ajax({
        url: 'getSignatories.php', // create this PHP file to return signatories for a FormNo
        method: 'GET',
        data: { formNo: formNo },
        dataType: 'json',
        success: function(data){
            if(data.length > 0){
                $.each(data, function(index, row){
                    $('#signatoriesTable tbody').append(
                        `<tr>
                            <td>${index+1}</td>
                            <td>${row.SingatoriesEmpId}</td>
                            <td>${row.SingatoriesPosition}</td>
                            <td>${row.steps}</td>
                        </tr>`
                    );
                });
            } else {
                $('#signatoriesTable tbody').append('<tr><td colspan="4" class="text-center">No signatories found</td></tr>');
            }
        },
        error: function(err){
            console.error(err);
            $('#signatoriesTable tbody').append('<tr><td colspan="4" class="text-center text-danger">Error fetching data</td></tr>');
        }
    });
 */
    // Show modal
    $('#formSignatoriesModal').modal('show');
});

/* $(document).on('click', '#ShowSignatories', function(e){
    e.preventDefault();
	let $tr = $(this).closest('tr');
	let cscForm = $tr.find('td:eq(2)').text();
	
    $('#tltModal').text(cscForm+" Signatories");

    // Show modal
    $('#formSignatoriesModal').modal('show');
}); */


$(document).on('click', '#addSignatoryRow', function(){
    let rowCount = $('#signatoriesTable tbody tr').length + 1;
    $('#signatoriesTable tbody').append(`
        <tr>
            <td>${rowCount}</td>
            <td><input type="text" class="form-control emp-id" placeholder="Employee ID"></td>
            <td><input type="text" class="form-control position" placeholder="Position"></td>
            <td><input type="number" class="form-control step" placeholder="Step"></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `);
});

/* $(document).on("click", "#Showtest", function () {

    // =============================
    // 1. Reset Active Tab (always tab 1)
    // =============================
    $('#popupSignatoryTabs a[href="#popupOfficePane"]').tab('show');

    // =============================
    // 2. Clear previous table rows
    // =============================
    $("#popupSignatoriesTable tbody").empty();
    $("#popupSpecificTable tbody").empty();

    // Optional placeholder row
    $("#popupSignatoriesTable tbody").append(`
        <tr><td colspan="6" class="text-center text-muted">No data loaded</td></tr>
    `);

    $("#popupSpecificTable tbody").append(`
        <tr><td colspan="4" class="text-center text-muted">No data loaded</td></tr>
    `);

    // =============================
    // 3. Reset Select Dropdowns
    // =============================
    $("#popupOfficeSelect").val("");
    $("#popupOfficeSelectSpecific").val("");

    // =============================
    // 4. Finally show modal
    // =============================
    $("#testPopupModal").modal("show");
}); */

$(document).on("click", "#listEmployeeBtn", function () {

    // =============================
    // 1. Always show TAB 1 (Office Table)
    // =============================
    $('#popupSignatoryTabs a[href="#popupOfficePane"]').tab('show');

    // =============================
    // 2. Clear previous table rows
    // =============================
    $("#popupSignatoriesTable tbody").empty();
    $("#popupSpecificTable tbody").empty();

    // Placeholder rows
    $("#popupSignatoriesTable tbody").append(`
        <tr><td colspan="6" class="text-center text-muted">No data loaded</td></tr>
    `);

    $("#popupSpecificTable tbody").append(`
        <tr><td colspan="4" class="text-center text-muted">No data loaded</td></tr>
    `);

    // =============================
    // 3. Reset dropdown selections
    // =============================
    $("#popupOfficeSelect").val("");
    $("#popupOfficeSelectSpecific").val("");

    // =============================
    // 4. Open the popup modal
    // =============================
    $("#specificemployeeModal").modal("show");
});


// Remove row
$(document).on('click', '.remove-row', function(){
    $(this).closest('tr').remove();

    // Re-index the rows
    $('#signatoriesTable tbody tr').each(function(index){
        $(this).find('td:first').text(index + 1);
    });
});


LoadCSCAllOffice();

function LoadCSCAllOffice(){
	let dsble = UserInfo['AllOfficeAcess']==="0"?"disabled":"";
	 $('#sltoffice').prop(dsble, true); 
	var officeID = UserInfo["Office_id"];
	$.ajax({
		data : {
			request:"GetHRISOffice2"
			,dsble:dsble
			,EmpID:UserInfo["EmpID"]
			
		},
		type: "post",
		url: "backend/bk_mnuReportSummary.php",
		beforeSend: function(xhr) {
			$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
		},
		success: function(dataResult){	
			$("#officeSelect").html(dataResult);
		}
		,complete: function(){
            $("#loadingSpinner").fadeOut(200).css("display", "none");
			
		}
	}); 
}