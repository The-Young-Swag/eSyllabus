<?php
	require_once "../db/dbconnection.php"
	
	// $request = $_POST['request'] ?? '';

// if ($request === 'menumodal') {}
// if ($request === 'menueditmodal') {}
?>


<!-- Add Modal -->
<div class="modal fade" id="menumodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Menu aaa</h5>
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
						<button type="submit" id="btnaddmenu" name="m_submit" class="btn btn-primary">Add Menu</button>
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

<!-- Add role 	Modal -->
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
<!-- User Management Modal-->
<div class="modal fade" id="usermodal" tabindex="-1" aria-labelledby="lblAddU" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="lblAddU">Add User</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

				<div class="p-3">
                  <div class="form-group">
                    <label for="">Employee/Username</label>
							<select class="form-control" aria-label="Default select example" name="u_username" id="u_username">
									<?php
										$adduser = execsqlES("SELECT [EmpId],[CollegeId],upper([LastName]) LastName,[FirstName],[MiddleName] ,ExtName
													,concat(upper([LastName]),', ' ,[FirstName],' ',iif([MiddleInitial] is not null,upper([MiddleInitial]),upper(LEFT([MiddleName], 1))),'.') EmpName
											  FROM [tbl_Employees]
											  order by [LastName],[FirstName],[MiddleName]","Select",[]);
										foreach ($adduser as $user) {
													$id = $user['EmpId'];
													$desc = $user['EmpName'];
													echo "<option value = '$id'>$desc</option>";
										}
									?>
							</select>
                   <!-- <input type="text" class="form-control" id="u_username" name="u_username" placeholder="Username..."> -->
                  </div>
				  
                  <div class="form-group">
                    <label for="">Password</label>
                    <input type="password" class="form-control" value="************" id="u_password" name="u_password" placeholder="Password..." readonly>
                  </div>
				  <!--
				  <div class="form-group">
                    <label for="">Name</label> -->
                    <input type="hidden" class="form-control" id="u_name" name="u_name" placeholder="Name...">
                  <!--</div> -->
				  
                  <div class="form-group">
                    <label for="">Set As Active : </label>
                    <!--<input type="text" class="form-control" id="u_status" name="u_status" placeholder="Status...">-->
					<label class='custom-switch'>
						<input type='checkbox' class='toggle-switch' 
							data-updateField='IsActive'
							data-userid='' id='u_status'>
						<span class='slider'></span>
					</label>
                  </div>

					<div class="dropdown">
						<label for="" class="pr-2">Select Role:</label>
							<select class="form-control" aria-label="Default select example" name="u_role" id="u_role">
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
							<select class="form-control" aria-label="Default select example" name="u_unit" id="u_unit">
									<?php
										$adduserun = execsqlSRS("
										SELECT [OfficeMenID]
											  ,[OfficeName]
											  ,[OfficeCode]
											  ,[OfficeMotherMenID]
											  ,[Arrangement]
											  ,[UnActive]
										  FROM [Sys_Office]
										ORDER BY OfficeName
										", "Select", array());
										foreach ($adduserun as $unit) {
													$unit_id = $unit['OfficeMenID'];
													$unit_name = $unit['OfficeName'];
													echo "<option value = '$unit_id'>$unit_name</option>";
										}
									?>
							</select>
					</div>
					
					<div class="dropdown mt-2">
						<label for="" class="pr-2">Select Position:</label>
							<select class="form-control" aria-label="Default select example" id="u_position">
									<?php
										$adduserpos = execsqlES("
										SELECT [PositionId]
											  ,[SalaryGradeId]
											  ,[PositionCode]
											  ,[Position]
											  ,[Authorized]
											  ,[Actual]
											  ,[Unfilled]
											  ,[PlantillaNo]
											  ,[SalaryPerAnnum]
											  ,[DateEntered]
											  ,[DateEnteredBy]
											  ,[DateUpdate]
											  ,[DateUpdateBy]
											  ,[InActive]
										  FROM [db_HRIS].[dbo].[tbl_Positions]
										ORDER BY Position
										", "Select", array());
										foreach ($adduserpos as $pos) {
													$position_id = $pos['PositionId'];
													$position_desc = $pos['Position'];
													echo "<option value = '$position_id'>$position_desc</option>";
										}
									?>
							</select>
							
						<label for="" class="pr-2">Plantilla</label>
							<?php
							
						echo "<td>
								<label class='custom-switch'>
									<input type='checkbox' class='toggle-switch' 
										data-updateField='IsActive'
										data-userid='' id='swchPlantilla'>
									<span class='slider'></span>
								</label>
								<span class='status-text'></span>
							  </td>";

							?>
					</div>
					
					  <div class="form-group pt-2 d-flex justify-content-center">
						<button type="submit" id="u_submit" name="u_submit" data-request="adduser" class="btn btn-primary">Add User</button>
					  </div>
				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- All Office Staff Modal -->
<div class="modal fade" id="mdlOfficeStaff" tabindex="-1" aria-labelledby="mdlOfficeStaffLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl"> <!-- Extra large modal for big tables -->
    <div class="modal-content shadow-lg border-0 rounded-2">
      
      <!-- Header -->
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="mdlOfficeStaffLabel">
          <i class="fas fa-users mr-2"></i> All Office Staff
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body">

		<!-- Office Name 
		<div class="mb-2">
			<h6 class="font-weight-bold text-primary" id='officename'>
			  Office: Human Resource Department
			</h6>
		</div>-->
		
        <!-- Search bar
        <div class="mb-3">
          <input type="text" id="searchStaff" class="form-control" placeholder="🔍 Search staff by name, position, or office...">
        </div> -->

        <!-- Staff list table -->
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;" id='showOfficeSaf'>
          <table class="table table-hover table-striped table-bordered">
            <thead class="thead-light">
              <tr>
                <th scope="col">#</th>
                <th scope="col">Employee Name</th>
                <th scope="col">Position</th>
                <th scope="col">Office</th>
                <th scope="col">Status</th>
                <th scope="col" class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="staffList">
              <!-- Example static rows (replace with PHP/AJAX loop) -->
              <tr>
                <td>1</td>
                <td>Juan Dela Cruz</td>
                <td>Manager</td>
                <td>HR Department</td>
                <td><span class="badge badge-success">Active</span></td>
                <td class="text-center">
                  <button class="btn btn-sm btn-info">View</button>
                  <button class="btn btn-sm btn-success">Select</button>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>Maria Santos</td>
                <td>Staff</td>
                <td>IT Department</td>
                <td><span class="badge badge-secondary">Inactive</span></td>
                <td class="text-center">
                  <button class="btn btn-sm btn-info">View</button>
                  <button class="btn btn-sm btn-success">Select</button>
                </td>
              </tr>
              <!-- dynamic rows go here -->
            </tbody>
          </table>
        </div>

      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>




<!-- Office Access Modal -->
<div class="modal fade" id="useLstOffAcc" tabindex="-1" aria-labelledby="mdlOfficeAccessLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content shadow-lg border-0 rounded-2">

      <!-- Header -->
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="mdlOfficeAccessLabel">
          <i class="fas fa-building mr-2"></i> Office Access
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body">

		<div class="mb-3 d-flex justify-content-between align-items-center">
			<h6 class="font-weight-bold text-secondary mb-0">
			  Employee: Juan Dela Cruz
			</h6>
		</div>
		
		<div class="mb-3 d-flex justify-content-between align-items-center">
			<select class="form-control w-75" id="sltoffice" name="sltoffice">
				<option value="">-- Select Employee --</option>
				<option value="1">Juan Dela Cruz</option>
				<option value="2">Maria Santos</option>
				<option value="3">Pedro Ramirez</option>
			</select>
			<button type="button" 
				    class="btn btn-primary" 
					id="AUAOffice"
					data-EditEmpID="">Add Office Access</button>
		</div>
		<hr>
        <!-- Access List -->
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;" id="scrollOfficeList">
          <table class="table table-bordered table-hover">
            <thead class="thead-light">
              <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 40%;">Office Name</th>
                <th style="width: 20%;">Code</th>
                <th style="width: 20%;">Status</th>
                <th style="width: 15%;" class="text-center">Access</th>
              </tr>
            </thead>
            <tbody id="officeAccessList">
              <!-- Example static rows, replace with PHP/AJAX -->
              <tr>
                <td>1</td>
                <td>Human Resource Department</td>
                <td>HRD01</td>
                <td><span class="badge badge-success">Active</span></td>
                <td class="text-center">
                  <input type="checkbox" checked>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>IT Department</td>
                <td>IT02</td>
                <td><span class="badge badge-success">Active</span></td>
                <td class="text-center">
                  <input type="checkbox">
                </td>
              </tr>
              <tr>
                <td>3</td>
                <td>Finance Department</td>
                <td>FIN03</td>
                <td><span class="badge badge-secondary">Inactive</span></td>
                <td class="text-center">
                  <input type="checkbox" disabled>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>