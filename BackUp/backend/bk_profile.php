<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";

$profile_data = isset($_POST["profile_data"]) ? $_POST["profile_data"] : "";
$profile_name = isset($_POST["profile_name"]) ? $_POST["profile_name"] : "";
$profile_email = isset($_POST["profile_email"]) ? $_POST["profile_email"] : "";
$profile_newpassword = isset($_POST["profile_newpassword"]) ? $_POST["profile_newpassword"] : "";
$profile_oldpassword = isset($_POST["profile_oldpassword"]) ? $_POST["profile_oldpassword"] : "";

switch ($request) {

	case "viewprofile":

		// Fetch all menu items at once
		 $queryviewprofile = execsqlSRS("
			SELECT ua.UserID, ua.Name, ua.EmailAddress, ua.Password, u.unit_name
			FROM Sys_UserAccount ua 
			
			LEFT JOIN tbl_Units u 
			ON u.unit_id = ua.Office_id
			
			WHERE (
			
			(ua.IsActive = '0' AND u.unit_status = '0')
			AND
			(ua.UserID = :user_id)
			
				  )",
			"Select", [
						":user_id" => $user_id
					  ]
			);

			foreach ($queryviewprofile as $profile) {

$password = htmlspecialchars($profile["Password"], ENT_QUOTES);

                // Left Side: User Details
echo                "<div class='col-md-6'>

					<p class='font-weight-bold'><b class='text-success'>Your User Profile</b> <b class='text-danger'>*Contact an Administrator for a Name/Username/Unit Change*</b></p>
				
                  <div class='form-group'>
                    <label for=''>Name</label>
                    <input type='text' class='form-control' id='profile_name' value='" . htmlspecialchars($profile["Name"]) . "' disabled>
                  </div>

                  <div class='form-group'>
                    <label for=''>Username/Email</label>
                    <input type='text' class='form-control' id='profile_email' value='" . htmlspecialchars($profile["EmailAddress"]) . "' disabled>
                  </div>

					<div class='form-group'>
						<label for=''>Unit</label>
						<input type='text' style='border: transparent;' class='form-control' 
						id='profile_unit' value='" . htmlspecialchars($profile["unit_name"]) . "' disabled>
					</div>

                </div>";

                // Right Side: User Settings
echo                "<div class='col-md-6'>

					<p class='text-success font-weight-bold'><b>Change your Password Here</b></p>

                  <div class='form-group'>
                    <label for=''>Current Password</label>
					<input type='password' class='form-control' id='profile_oldpassword' value='" . htmlspecialchars($profile["Password"]) . "'>
                  </div>

                  <div class='form-group' style='display : none;' id='nextpass'> 
                    <label for=''>New Password</label>
					<input type='password' class='form-control' id='profile_newpassword'>
                  </div>

                  <div class='form-group' style='display : none;' id='nextnextpass'>
                    <label for=''>Confirm New Password</label>
					<input type='password' class='form-control' id='profile_confirmpassword'>
                  </div>

                </div>
			  <div class='form-group pl-2'>
                <button type='submit' id='profile_submit' class='btn btn-primary' value='" . htmlspecialchars($profile["UserID"]) . "'>Save Changes</button>
              </div>";
			} 
	break;
	
	case "saveprofileold":

		$querysaveprofileold = execsqlSRS("
			UPDATE Sys_UserAccount
			SET EmailAddress = :profile_email, Password = :profile_oldpassword, Name = :profile_name
			WHERE UserID = :profile_data" , 
			"Update", [
						":profile_email" => $profile_email, 
						":profile_oldpassword" => $profile_oldpassword, 
						":profile_name" => $profile_name, 
						":profile_data" => $profile_data
					  ]); 

	echo "System: Changes have been Saved! Website Restart is Recommended!";

	break;

	case "saveprofilenew":

		$querysaveprofilenew = execsqlSRS("
			UPDATE Sys_UserAccount
			SET EmailAddress = :profile_email, Password = :profile_newpassword, Name = :profile_name
			WHERE UserID = :profile_data" , 
			"Update", [
						":profile_email" => $profile_email, 
						":profile_newpassword" => $profile_newpassword, 
						":profile_name" => $profile_name, 
						":profile_data" => $profile_data
					  ]); 

	echo "System: Changes have been Saved! Website Restart is Recommended!";

	break;
}
?>