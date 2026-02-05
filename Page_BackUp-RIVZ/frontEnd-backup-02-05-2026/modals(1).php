<?php
	require_once "../db/dbconnection.php"
?>

<!-- Modals -->
<!-- View Modal -->
<div class="modal fade" id="viewmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">View Document</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div id="view_content" class="p-3">
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Document</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div id="edit_content" class="p-3">
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Track Modal -->
<div class="modal fade" id="trackmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-xl" style="min-width:90%">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Track Document</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

      <!-- Modal body -->
      <div class="modal-body">
		  <div class="card-body table-responsive table-bordered p-0 text-center">
			<table class="table table-hover text-center table-striped">
			  <thead class="thead-dark">
				<tr>
				  <th>Sender</th>
				  <th>Office</th>
				  <th style="width: 280px;">Remarks</th>
				  <th>D/T Released</th>
				  <th>Receiver</th>
				  <th>Office</th>
				  <th>D/T Received</th>
				  <th>Elapsed</th>
				  <th>Attachment</th>
				</tr>
			  </thead>
			  <tbody id="track_content">
			<!-- Table rows will be injected here -->
		  </tbody>
			</table>
		  </div>
      </div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>	

<!-- Forward Modal -->
<div class="modal fade" id="forwardmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Forward Document</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div id="forward_content" class="p-3">
			
			</div> 

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completemodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-danger font-weight-bold" id="exampleModalLabel">Warning!</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div id="complete_content" class="p-3">

			</div> 

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Return Document</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div id="return_content" class="p-3">

			</div> 

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Draft-Forward Modal -->
<div class="modal fade" id="draftmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Forward Document</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div id="draft_content" class="p-3">

			</div> 
			
		</div>
	</div>
</div>

<!-- User Management ================================================================================================================= -->

<!-- Add Modal -->
<div class="modal fade" id="usermodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add User</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">
                  <div class="form-group">
                    <label for="">Email/Username</label>
                    <input type="text" class="form-control" id="u_username" name="u_username" placeholder="Username...">
                  </div>
				  
                  <div class="form-group">
                    <label for="">Password</label>
                    <input type="text" class="form-control" id="u_password" name="u_password" placeholder="Password...">
                  </div>
				  
				  <div class="form-group">
                    <label for="">Name</label>
                    <input type="text" class="form-control" id="u_name" name="u_name" placeholder="Name...">
                  </div>
				  
                  <div class="form-group">
                    <label for="">Status</label>
                    <input type="text" class="form-control" id="u_status" name="u_status" placeholder="Status...">
                  </div>

					<div class="dropdown">
						<label for="" class="pr-2">Select Role:</label>
							<select class="form-select" aria-label="Default select example" name="u_role" id="u_role">
									<?php
										$adduser = execsqlSRS("
										SELECT RID, Role
										FROM Sys_Role
										WHERE UnActive = '0'
										", "Select", array());
										foreach ($adduser as $user) {
													$id = $user['RID'];
													$desc = $user['Role'];
													echo "<option value = '$id'>$desc</option>";
										}
									?>
							</select>
					</div>
					
					<div class="dropdown">
						<label for="" class="pr-2">Select Unit:</label>
							<select class="form-select" aria-label="Default select example" name="u_unit" id="u_unit">
									<?php
										$adduserun = execsqlSRS("
										SELECT unit_id, unit_name
										FROM tbl_Units
										WHERE unit_status = '0'
										ORDER BY unit_name
										", "Select", array());
										foreach ($adduserun as $unit) {
													$unit_id = $unit['unit_id'];
													$unit_name = $unit['unit_name'];
													echo "<option value = '$unit_id'>$unit_name</option>";
										}
									?>
							</select>
					</div>
					
					<div class="dropdown mt-2">
						<label for="" class="pr-2">Select Position:</label>
							<select class="form-select" aria-label="Default select example" id="u_position">
									<?php
										$adduserpos = execsqlSRS("
										SELECT position_id, position_desc
										FROM tbl_Positions
										WHERE IsActive = '0'
										ORDER BY position_desc
										", "Select", array());
										foreach ($adduserpos as $pos) {
													$position_id = $pos['position_id'];
													$position_desc = $pos['position_desc'];
													echo "<option value = '$position_id'>$position_desc</option>";
										}
									?>
							</select>
					</div>
					
					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="u_submit" name="u_submit" class="btn btn-primary">Add User</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="usereditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit User</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="useredit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Role Management ====================================================================================================================== -->

<!-- Add Modal -->
<div class="modal fade" id="rolemodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Role</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">
                  <div class="form-group">
                    <label for="">Role</label>
                    <input type="text" class="form-control" id="r_role" name="r_role" placeholder="Role...">
                  </div>
				  
                  <div class="form-group">
                    <label for="">Role Code</label>
                    <input type="text" class="form-control" id="r_rolecode" name="r_rolecode" placeholder="Role Code...">
                  </div>
				  
				  <div class="form-group">
                    <label for="">Status</label>
                    <input type="text" class="form-control" id="r_status" name="r_status" placeholder="Status...">
                  </div>
					
					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="r_submit" name="r_submit" class="btn btn-primary">Add Role</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="roleeditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Role</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="roleedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Privelege Management ====================================================================================================================== -->

<!-- Add Modal -->
<div class="modal fade" id="privmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Privilege</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">

					<div class="dropdown">
						<label for="" class="pr-2">Select Role:</label>
							<select class="form-select" aria-label="Default select example" name="p_role" id="p_role">
									<?php
										$roles = execsqlSRS("
										SELECT RID, Role
										FROM Sys_Role
										WHERE UnActive = '0'
										", "Select", array());
										foreach ($roles as $role) {
													$id = $role['RID'];
													$desc = $role['Role'];
													echo "<option value = '$id'>$desc</option>";
										}
									?>
							</select>
					</div>

					<div class="dropdown">
						<label for="" class="pr-2">Select Menu:</label>
							<select class="form-select" aria-label="Default select example" name="p_menu" id="p_menu">
									<?php
										$menus = execsqlSRS("
										SELECT MenID, Menu
										FROM Sys_Menu
										WHERE UnActive = '0'
										", "Select", array());
										foreach ($menus as $menu) {
													$mid = $menu['MenID'];
													$mdesc = $menu['Menu'];
													echo "<option value = '$mid'>$mdesc</option>";
										}
									?>
							</select>
					</div>

                  <div class="form-group">
                    <label for="">Status</label>
                    <input type="text" class="form-control" id="p_status" name="p_status" placeholder="Status...">
                  </div>

					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="p_submit" name="p_submit" class="btn btn-primary">Add Privilege</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="priveditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Privilege</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="privedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Menu Management ====================================================================================================================== -->

<!-- Add Modal -->
<div class="modal fade" id="menumodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Menu</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">
                  <div class="form-group">
                    <label for="">Menu</label>
                    <input type="text" class="form-control" id="m_menu" name="m_menu" placeholder="Menu...">
                  </div>

                  <div class="form-group">
                    <label for="">MotherMenuID (0 if it's a Mother Menu)</label>
                    <input type="text" class="form-control" id="m_mother" name="m_mother" placeholder="Mother Menu...">
                  </div>

				  <div class="form-group">
                    <label for="">Description</label>
                    <input type="text" class="form-control" id="m_desc" name="m_desc" placeholder="Description...">
                  </div>

				  <div class="form-group">
                    <label for="">Menu Code</label>
                    <input type="text" class="form-control" id="m_code" name="m_code" placeholder="Menu Code...">
                  </div>

				  <div class="form-group">
                    <label for="">Menu Link</label>
                    <input type="text" class="form-control" id="m_link" name="m_link" placeholder="Menu Link...">
                  </div>

				  <div class="form-group">
                    <label for="">Arrangement</label>
                    <input type="text" class="form-control" id="m_arrange" name="m_arrange" placeholder="Menu Index...">
                  </div>

				  <div class="form-group">
                    <label for="">Icon</label>
                    <input type="text" class="form-control" id="m_icon" placeholder="Menu Icon...">
                  </div>

				  <div class="form-group">
                    <label for="">Status</label>
                    <input type="text" class="form-control" id="m_status" name="m_status" placeholder="Menu Status...">
                  </div>

					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="m_submit" name="m_submit" class="btn btn-primary">Add Menu</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="menueditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Menu</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="menuedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- ===================================================================================================================================== -->

<!-- Feedback-Remarks Modal -->
<div class="modal fade" id="feedbackmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display : none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Feedback Remarks</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div id="feedback_content" class="p-3">
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Document Management ====================================================================================================================== -->

<!-- Admin-Edit Modal -->
<div class="modal fade" id="doceditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Document</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="docedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>	


<!-- Add Modal -->
<div class="modal fade" id="doctypemodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Type</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">
                  <div class="form-group">
                    <label for="">Document ID</label>
                    <input type="text" class="form-control" id="type_id" placeholder="Document Type ID...">
                  </div>
				  
                  <div class="form-group">
                    <label for="">Type Details</label>
                    <input type="text" class="form-control" id="type_details" placeholder="Details...">
                  </div>
				  
				  <div class="form-group">
                    <label for="">Status</label>
                    <input type="text" class="form-control" id="type_status" placeholder="Status...">
                  </div>
				  
				  <div class="form-group">
                    <label for="">Link</label>
                    <input type="text" class="form-control" id="type_link" placeholder="Link...">
                  </div>
					
					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="type_submit" class="btn btn-primary">Add Type</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="doctypeeditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Type</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="doctypeedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- ===================================================================================================================================== -->

<!-- Office/Dept/Unit Management ========================================================================================================== -->

<!-- Add-Unit Modal -->
<div class="modal fade" id="unitmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Unit</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">
                  <div class="form-group">
                    <label for="">Unit Name</label>
                    <input type="text" class="form-control" id="addunit_name" placeholder="Name...">
                  </div>
				  
                  <div class="form-group">
                    <label for="">Unit Description</label>
                    <input type="text" class="form-control" id="addunit_desc" placeholder="Description...">
                  </div>

					<div class="dropdown">
						<label for="destination" class="pr-2">Mother Department:</label>
							<select class="form-select" aria-label="Default select example" id="addunit_dept">
								<option>Select Department...</option>
									<?php
										$unit_dept = execsqlSRS("
										SELECT dept_id, dept_name
										FROM tbl_Departments
										WHERE dept_status = '0'
										ORDER BY dept_name
										", "Select", array());
										foreach ($unit_dept as $undept) {
													$dept_id = $undept['dept_id'];
													$dept_name = $undept['dept_name'];
													echo "<option value = '$dept_id'>$dept_name</option>";
										}
									?>
							</select>
					</div>

				  <div class="form-group">
                    <label for="">Status</label>
                    <input type="text" class="form-control" id="addunit_status" placeholder="Status...">
                  </div>
					
					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="addunit_submit" class="btn btn-primary">Add Unit</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Add-Dept Modal -->
<div class="modal fade" id="deptmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Department</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">
                  <div class="form-group">
                    <label for="">Department Name</label>
                    <input type="text" class="form-control" id="adddept_name" placeholder="Name...">
                  </div>
				  
                  <div class="form-group">
                    <label for="">Department Description</label>
                    <input type="text" class="form-control" id="adddept_desc" placeholder="Description...">
                  </div>
				  
					<div class="dropdown">
						<label for="destination" class="pr-2">Mother Office:</label>
							<select class="form-select" aria-label="Default select example" id="adddept_office">
								<option>Select Office...</option>
									<?php
										$dept_office = execsqlSRS("
										SELECT office_id, office_name
										FROM tbl_Offices
										WHERE office_status = '0'
										ORDER BY office_name
										", "Select", array());
										foreach ($dept_office as $dpoff) {
													$office_id = $dpoff['office_id'];
													$office_name = $dpoff['office_name'];
													echo "<option value = '$office_id'>$office_name</option>";
										}
									?>
							</select>
					</div>				  
				  
				  <div class="form-group">
                    <label for="">Status</label>
                    <input type="text" class="form-control" id="adddept_status" placeholder="Status...">
                  </div>
					
					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="adddept_submit" class="btn btn-primary">Add Department</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Add-Office Modal -->
<div class="modal fade" id="officemodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Office</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">
                  <div class="form-group">
                    <label for="">Office Name</label>
                    <input type="text" class="form-control" id="addoffice_name" placeholder="Name...">
                  </div>
				  
                  <div class="form-group">
                    <label for="">Office Description</label>
                    <input type="text" class="form-control" id="addoffice_desc" placeholder="Description...">
                  </div>
				  
				  <div class="form-group">
                    <label for="">Status</label>
                    <input type="text" class="form-control" id="addoffice_status" placeholder="Status...">
                  </div>
					
					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="addoffice_submit" class="btn btn-primary">Add Office</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit-Unit Modal -->
<div class="modal fade" id="uniteditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Unit</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="unitedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit-Dept Modal -->
<div class="modal fade" id="depteditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Department</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="deptedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit-Office Modal -->
<div class="modal fade" id="officeeditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Office</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="officeedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- ===================================================================================================================================== -->


<!-- Edit DocTrans Modal -->
<div class="modal fade" id="doctranseditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Transaction</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="doctransedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- ============================================================================================================== -->
<!-- Position Management Modals -->

<!-- Add -->
<div class="modal fade" id="positionmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Position</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">
				  
                  <div class="form-group">
                    <label for="">Position Code</label>
                    <input type="text" class="form-control" id="addpos_code" placeholder="Code...">
                  </div>
				  
				  <div class="form-group">
                    <label for="">Position Description</label>
                    <input type="text" class="form-control" id="addpose_desc" placeholder="Desc...">
                  </div>

				  <div class="form-group">
                    <label for="">Plantilla Number</label>
                    <input type="text" class="form-control" id="addpos_plant" placeholder="Number...">
                  </div>
				  
				  <div class="form-group">
                    <label for="">Is Active</label>
                    <input type="text" class="form-control" id="addpos_status" placeholder="Status...">
                  </div>
					
					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="addpos_submit" class="btn btn-primary">Add Position</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit -->
<div class="modal fade" id="positioneditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Transaction</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="positionedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>


<!-- View -->
<div class="modal fade" id="viewpositionmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-xl" style="min-width:90%">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Tagged Users</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

      <!-- Modal body -->
      <div class="modal-body">
		  <div class="card-body table-responsive table-bordered p-0 text-center">
			<table class="table table-hover text-nowrap text-center table-striped">
			  <thead class="thead-dark">
				<tr>
				  <th>Name</th>
				  <th>Office</th>
				  <th>TAU ID</th>
				  <th>Role</th>
				  <th>Is Active</th>
				</tr>
			  </thead>
			  <tbody id="viewposition_content">
			<!-- Table rows will be injected here -->
		  </tbody>
			</table>
		  </div>
      </div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>	

<!-- ============================================================================================================== -->

<!-- Paginations  ==================================================================================================== -->

<!-- Add -->
<div class="modal fade" id="pagimodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Pagination</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">
                  <div class="form-group">
                    <label for="">Pagination ID</label>
                    <input type="text" class="form-control" id="addpagi_id" placeholder="ID...">
                  </div>
				  
                  <div class="form-group">
                    <label for="">Pagination Number</label>
                    <input type="text" class="form-control" id="addpagi_num" placeholder="Number...">
                  </div>
				  
				  <div class="form-group">
                    <label for="">Pagination Status</label>
                    <input type="text" class="form-control" id="addpagi_status" placeholder="Status...">
                  </div>
				  
				  <div class="form-group">
                    <label for="">User Only</label>
                    <input type="text" class="form-control" id="addpagi_cond" placeholder="Tag for Users...">
                  </div>

					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="addpagi_submit" class="btn btn-primary">Add Pagination Number</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit -->
<div class="modal fade" id="paginationeditmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Pagination</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div id="paginationedit_content" class="p-3">
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>