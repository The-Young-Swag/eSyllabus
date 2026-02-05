<?php 
# field type= Regular or is foriegn
$MappingPDS = [
    "personal" => [
		"MtherTable" => "tbl_Employees",
        "MtherPrimaryID" => "EmpId",
		"data" => [
			[
				"group" => "Personal Information",
				"elements" => [
					["name"=>"LastName"
						,"type"=>"text"
						,"label"=>"Surname Name"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"FirstName"
						,"type"=>"text"
						,"label"=>"First Name"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"MiddleName"
						,"type"=>"text"
						,"label"=>"Middle Name"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"MiddleInitial"
						,"type"=>"text"
						,"label"=>"Middle Initial"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"ExtName"
						,"type"=>"text"
						,"label"=>"Extension Name (Jr.,SR.,III)"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"DateofBirth","type"=>"date"
						,"label"=>"Date of Birth"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"PlaceofBirth"
						,"type"=>"text"
						,"label"=>"Place of Birth"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"GenderId"
						,"type"=>"text"
						,"label"=>"SEX AT BIRTH"
						,"FldType"=>"foriegn"
						,"table"=>"tbl_Gender"
						,"PrimaryID"=>"GenderId"
						,"SelectedFields"=>"GenderId,GenderDesc"
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>"GenderDesc"],
					["name"=>"CivilStatusId"
						,"type"=>"text"
						,"label"=>"Civil Status"
						,"FldType"=>"foriegn"
						,"table"=>"tbl_CivilStatus"
						,"PrimaryID"=>"CivilStatusId"
						,"SelectedFields"=>"CivilStatusId,CivilStatusDesc"
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>"CivilStatusDesc"],
					["name"=>"Height"
						,"type"=>"text"
						,"label"=>"HEIGHT (m)"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Weight"
						,"type"=>"text"
						,"label"=>"WEIGHT (kg)"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"BloodTypeId"
						,"type"=>"text"
						,"label"=>"BLOOD TYPE"
						,"FldType"=>"foriegn"
						,"table"=>"tbl_BloodType"
						,"PrimaryID"=>"BloodTypeId"
						,"SelectedFields"=>"[BloodTypeId],[BloodTypeCode]"
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>"BloodTypeCode"],
					["name"=>"GSIS"
						,"type"=>"text"
						,"label"=>"UMID NO."
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"PAGIBIG"
						,"type"=>"text"
						,"label"=>"PAG-IBIG NO."
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"PHILHEALTH"
						,"type"=>"text"
						,"label"=>"PHILHEALTH NO."
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"NationalID"
						,"type"=>"text"
						,"label"=>"PhilSys Number(PSN)"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"TIN"
						,"type"=>"text"
						,"label"=>"TIN NO."
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"EmpId"
						,"type"=>"text"
						,"label"=>"AGENCY EMPLOYEE NO."
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Citizenship"
						,"type"=>"text"
						,"label"=>"CITIZENSHIP"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"CivilStatusId"
						,"type"=>"text"
						,"label"=>"Pls. indicate country:"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
				]
			],
			[
				"group" => "Residential Address",
				"elements" => [
					["name"=>"Res_HouseNo"
						,"type"=>"text"
						,"label"=>"House No"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Res_Street"
						,"type"=>"text"
						,"label"=>"Street"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Res_SubdivisionVillage"
						,"type"=>"text"
						,"label"=>"Subdivision / Village"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Res_Province"
						,"type"=>"text"
						,"label"=>"Province"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Res_CityMunicipality"
						,"type"=>"text"
						,"label"=>"City / Municipality"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Res_Barangay"
						,"type"=>"text"
						,"label"=>"Barangay"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Res_ZIPCode"
						,"type"=>"text"
						,"label"=>"ZIP Code"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
				]
			],
			[
				"group" => "Permanent Address",
				"elements" => [
					["name"=>"Per_HouseNo"
						,"type"=>"text"
						,"label"=>"House No"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Per_Street"
						,"type"=>"text"
						,"label"=>"Street"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Per_SubdivisionVillage"
						,"type"=>"text"
						,"label"=>"Subdivision / Village"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Per_Province"
						,"type"=>"text"
						,"label"=>"Province"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Per_CityMunicipality"
						,"type"=>"text"
						,"label"=>"City / Municipality"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Per_Barangay"
						,"type"=>"text"
						,"label"=>"Barangay"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"Per_ZIPCode"
						,"type"=>"text"
						,"label"=>"ZIP Code"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
				]
			]
		]
    ],
	
    "family" => [
		"MtherTable" => "tbl_EmpFamilyBackground",
        "MtherPrimaryID" => "EmpId",
		"data" => [
			[
				"group" => "Family Background",
				"elements" => [
					["name"=>"SpouseLastName"
						,"type"=>"text"
						,"label"=>"Spouse LastName"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"SpouseFirstName"
						,"type"=>"text"
						,"label"=>"Spouse FirstName"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"SpouseMiddleName"
						,"type"=>"text"
						,"label"=>"Spouse MiddleName"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"SpouseOccupation"
						,"type"=>"text"
						,"label"=>"Spouse Occupation"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"SpouseEmployer"
						,"type"=>"text"
						,"label"=>"Spouse Employer"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"SpouseBusinessAddress"
						,"type"=>"text"
						,"label"=>"Spouse Business Address"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"SpouseTelNo"
						,"type"=>"text"
						,"label"=>"Spouse TelNo"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"FatherLastName"
						,"type"=>"text"
						,"label"=>"Father LastName"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"FatherFirstName"
						,"type"=>"text"
						,"label"=>"Father FirstName"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"FatherMiddleName"
						,"type"=>"text"
						,"label"=>"Father MiddleName"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"FatherNameExtension"
						,"type"=>"text"
						,"label"=>"EXTENSION NAME (JR.,SR.,III)"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"MotherLastName"
						,"type"=>"text"
						,"label"=>"Mother LastName"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"MotherFirstName"
						,"type"=>"text"
						,"label"=>"Mother FirstName"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
					,["name"=>"MotherMiddleName"
						,"type"=>"text"
						,"label"=>"Mother MiddleName"
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""],
					["name"=>"EmpId"
						,"type"=>"hidden"
						,"label"=>""
						,"FldType"=>"Regular"
						,"table"=>""
						,"PrimaryID"=>""
						,"SelectedFields"=>""
						,"Whereclause"=>""
						,"orderFields"=>""
						,"dsplyField"=>""]
				]
			]
		]
    ]
];



// === mapping table ============
$MappingTablePDS = [
    "Child" => [
		"MtherTable"=>"tbl_EmpChildren",
		"MtherPrimaryID"=>"IndexId",
		"elDataDsplay"=>"ChildFormRow",
		"tbodyID"=>"showChild",
		"DsTop" => "
			  <div class='table-responsive'>
			  <table>
				<thead>
				  <tr>
					<th style='text-align:center;' rowspan='2'></th>
					<th colspan=4>23. NAME OF CHILDREN (Write full nameand list all)</th>
					<th>DATE OF BIRTH (dd/mm/yyyy)</th>
					<th>ACTION</th>
				  </tr>
				</thead>
				<tbody id='showChild'>",
        "Dsbuttom" => "
				  </tbody>
				</table>
			  </div>",
			  /// attached in backend
		"elements" => [
			["name"=>"ChilLastName"
				,"type"=>"text"
				,"placeholder"=>"Chil LastName"
			],
			["name"=>"ChilFirstName"
				,"type"=>"text"
				,"placeholder"=>"Chil FirstName"
			],
			["name"=>"ChilMIddleName"
				,"type"=>"text"
				,"placeholder"=>"Chil MIddleName"
			],
			["name"=>"ChilExtName"
				,"type"=>"text"
				,"placeholder"=>"Chil ExtName"
			],
			["name"=>"DateofBirth"
				,"type"=>"text"
				,"placeholder"=>"Chil  Date of Birth"
			]
		]
	],
    "cse" => [
		"MtherTable"=>"tbl_EmpCivilServiceEligibility",
		"MtherPrimaryID"=>"IndexId",
		"elDataDsplay"=>"cseFormRow",
		"tbodyID"=>"showcse",
		"DsTop" => "
			  <section class='section'>
				<h3>IV. CIVIL SERVICE ELIGIBILITY</h3>
				<div class='table-responsive'  id='cseFormRow'>
			  <table>
			  <thead>
				<tr>
				  <th style='text-align:center;' rowspan='2'></th>
				  <th style='text-align:center;' rowspan='2'>CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/ BAR)/UNDER SPECIAL LAWS/CATEGORY II/ IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL</th>
				  <th style='text-align:center;' rowspan='2'>RATING </br>
					  <span style='font-weight:normal; font-size:smaller;'>(If Applicable)</span>
				  </th>
				  <th style='text-align:center;' rowspan='2'>DATE OF EXAMINATION / CONFERMENT</th>
				  <th style='text-align:center;' rowspan='2'>PLACE OF EXAMINATION / CONFERMENT</th>
				  <th style='text-align:center;' colspan='2'>LICENSE </br>
					  <span style='font-weight:normal; font-size:smaller;'>(If Applicable)</span>
				  </th>
				</tr>
				<tr>
				  <th style='text-align:center;'>Number</th>
				  <th style='text-align:center;'>Valid Until</th>
				  <th rowspan='2'>ACTION</th>
				</tr>
			  </thead>
			  <tbody id= 'showcse'>
		",
        "Dsbuttom" => "
			  </tbody>
			</table>

				</div>
			  </section>
		",
		"elements" => [
			["name"=>"Eligibility"
				,"type"=>"text"
				,"placeholder"=>"Eligibility"
			],
			["name"=>"Rating"
				,"type"=>"text"
				,"placeholder"=>"Rating"
			],
			["name"=>"DateofExam"
				,"type"=>"text"
				,"placeholder"=>"Date of Exam"
			],
			["name"=>"PlaceofExam"
				,"type"=>"text"
				,"placeholder"=>"Place of Exam"
			],
			["name"=>"licenseNumber"
				,"type"=>"text"
				,"placeholder"=>"License Number"
			],
			["name"=>"DateofRelease"
				,"type"=>"text"
				,"placeholder"=>"Date of Release"
			]
		]
	],
    "work" => [
		"MtherTable"=>"tbl_EmpCivilServiceEligibility",
		"MtherPrimaryID"=>"IndexId",
		"elDataDsplay"=>"work",
		"tbodyID"=>"showwork",
		"DsTop" => "
			  <section class='section'>
				<h3>V. WORK EXPERIENCE</h3>
				<p class='muted small'>(Include private employment.  Start from your recent work.) Description of duties should be indicated in the attached Work Experience Sheet.</p>
				<div class='table-responsive'  id='workFormRow'>
				  <table>
					<thead>
					  <tr>
						<th style='text-align:center;' rowspan='2'></th>
						<th colspan='2' style='text-align:center;'>INCLUSIVE DATES <br/>
							<span style='font-weight:normal; font-size:smaller;'>(dd/mm/yyyy)</span>
							</th>
						<th rowspan='2' style='text-align:center;'>POSITION TITLE <br/> 
							<span style='font-weight:normal; font-size:smaller;'>(Write in full/Do not abbreviate)</span>
							</th>
						<th rowspan='2' style='text-align:center;'>DEPARTMENT / AGENCY / OFFICE / COMPANY <br/> 
							<span style='font-weight:normal; font-size:smaller;'>(Write in full/Do not abbreviate)</span>
						</th>
						<th rowspan='2' style='text-align:center;'>STATUS OF APPOINTMENT</th>
						<th rowspan='2' style='text-align:center;'>GOV'T SERVICE <br/>
							<span style='font-weight:normal; font-size:smaller;'>(Y/N)</span>
						</th>
					  </tr>
					  <tr>
					<th style='text-align:center;'>FROM</th>
					<th style='text-align:center;'>TO</th>
					</tr>
					</thead>
					<tbody id= 'showwork'>
		",
        "Dsbuttom" => "
					</tbody>
				  </table>
				</div>
			  </section>
		",
		"elements" => [
			["name"=>"From"
				,"type"=>"text"
				,"placeholder"=>"Inclusive From"
			],
			["name"=>"To"
				,"type"=>"text"
				,"placeholder"=>"Inclusive To"
			],
			["name"=>"PositionId"
				,"type"=>"text"
				,"placeholder"=>"PositionId"
			],
			["name"=>"Company"
				,"type"=>"text"
				,"placeholder"=>"Agency"
			],
			["name"=>"StatusId"
				,"type"=>"text"
				,"placeholder"=>"Status App"
			],
			["name"=>"IsGovService"
				,"type"=>"text"
				,"placeholder"=>"IsGovService"
			]
		]
	],
    "edu" => [
		"MtherTable"=>"tbl_EmpEducBackground",
		"MtherPrimaryID"=>"IndexId",
		"elDataDsplay"=>"edu",
		"tbodyID"=>"showedu",
		"DsTop" => "
			  <section class='section'>
				<h3>III. EDUCATIONAL BACKGROUND</h3>
				<div class='table-responsive' id='eduFormRow'>
				<h3>”Note: Double-click to enable dragging, then drag the row to arrange.”</h3>
				  <table id='edusortableTable' >
					<thead>
					  <tr>
						<th style='text-align:center;' rowspan='2'></th>
						<th style='text-align:center;' rowspan='2'>LEVEL</th>
						<th style='text-align:center;' rowspan='2'>NAME OF SCHOOL</th>
						<th style='text-align:center;' rowspan='2'>BASIC EDUCATION/DEGREE/COURSE (Write in full)</th>
						<th style='text-align:center;' colspan='2'>PERIOD OF ATTENDANCE</th>
						<th style='text-align:center;' rowspan='2'>HIGHEST LEVEL/UNITS EARNED (if not graduated)</th>
						<th style='text-align:center;' rowspan='2'>YEAR GRADUATED</th>
						<th style='text-align:center;' rowspan='2'>SCHOLARSHIP/ACADEMIC HONORS RECEIVED</th>
					  </tr>
					  <tr>

						<th style='text-align:center;'>FROM</th>
						<th style='text-align:center;'>TO</th>

					  </tr>
					</thead>
					<tbody id= 'showedu'>
		",
        "Dsbuttom" => "
					</tbody>
				  </table>
				</div>
			  </section>
		",
		"elements" => [
			["name"=>"EducLevel"
				,"type"=>"text"
				,"placeholder"=>"Level"
			],
			["name"=>"SchoolName"
				,"type"=>"text"
				,"placeholder"=>"NAME OF SCHOOL"
			],
			["name"=>"Degree"
				,"type"=>"text"
				,"placeholder"=>"Write in full"
			],
			["name"=>"From"
				,"type"=>"text"
				,"placeholder"=>"From"
			],
			["name"=>"To"
				,"type"=>"text"
				,"placeholder"=>"To"
			],
			["name"=>"UnitsEarned"
				,"type"=>"text"
				,"placeholder"=>"Units Earned"
			],
			["name"=>"YearGraduated"
				,"type"=>"text"
				,"placeholder"=>"Year Graduated"
			],
			["name"=>"Scholarship"
				,"type"=>"text"
				,"placeholder"=>"Scholarship"
			]
		]
	],
    "vol" => [
		"MtherTable"=>"tbl_EmpVoluntaryWork",
		"MtherPrimaryID"=>"IndexId",
		"elDataDsplay"=>"vol",
		"tbodyID"=>"showvol",
		"DsTop" => "
			  <section class='section'>
				<h3>VI. VOLUNTARY WORK OR INVOLVEMENT IN CIVIC / NON-GOVERNMENT / PEOPLE / VOLUNTARY ORGANIZATION/S</h3>
				<div class='table-responsive'>
				  <table id='volsortableTable'>
					<thead>
					  <tr>
						<th style='text-align:center;' rowspan='2'></th>
						<th rowspan='2' style='text-align:center;'>NAME & ADDRESS OF ORGANIZATION <br/>
							<span style='font-weight:normal; font-size:smaller;'>(Write in full)</span>
							</th>
						<th colspan='2' style='text-align:center;'>INCLUSIVE DATES <br/>
							<span style='font-weight:normal; font-size:smaller;'>(dd/mm/yyyy)</span>
							</th>
						<th rowspan='2' style='text-align:center;'>NUMBER OF HOURS</th>
						<th rowspan='2' style='text-align:center;'>POSITION / NATURE OF WORK</th>
					  </tr>
					  <tr>
						<th style='text-align:center;'>FROM</th>
						<th style='text-align:center;'>TO</th>
					  </tr>
					</thead>
					<tbody id= 'showvol'>
		",
        "Dsbuttom" => "
					</tbody>
				  </table>
				</div>
			  </section>
		",
		"elements" => [
			["name"=>"NameAddressOfOrg"
				,"type"=>"text"
				,"placeholder"=>"Name/Address Of Org."
			],
			["name"=>"From"
				,"type"=>"text"
				,"placeholder"=>"From"
			],
			["name"=>"To"
				,"type"=>"text"
				,"placeholder"=>"To"
			],
			["name"=>"NumHour"
				,"type"=>"text"
				,"placeholder"=>"Number of Hour"
			],
			["name"=>"PosOrNatureWork"
				,"type"=>"text"
				,"placeholder"=>"Position/Nature of Work"
			]
		]
	],
    "ld" => [
		"MtherTable"=>"tbl_EmpLearningAndDevelopement",
		"MtherPrimaryID"=>"IndexId",
		"elDataDsplay"=>"ld",
		"tbodyID"=>"showld",
		"DsTop" => "
			  <section class='section'>
				<h3>VII. LEARNING AND DEVELOPMENT (L&D) INTERVENTIONS/TRAINING PROGRAMS ATTENDED</h3>
				<div class=table-responsive>
				  <table>
					<thead>
					  <tr>
						<th style='text-align:center;' rowspan='2'></th>
						<th rowspan=2 style=text-align:center;>TITLE OF LEARNING AND DEVELOPMENT INTERVENTIONS/TRAINING PROGRAMS </br>
							<span style=font-weight:normal; font-size:smaller;>(Write in full)</span>
							</th>
						<th colspan=2 style=text-align:center;>INCLUSIVE DATES OF ATTENDANCE </br> 
							<span style=font-weight:normal; font-size:smaller;>(dd/mm/yyyy)</span>
							</th>
						<th rowspan=2 style=text-align:center;>NUMBER OF HOURS</th>
						<th rowspan=2 style=text-align:center;>Type of L&D </br>
							<span style=font-weight:normal; font-size:smaller;>(Managerial/Supervisory/Technical/etc)</span>
							</th>
						<th rowspan=2 style=text-align:center;>CONDUCTED / SPONSORED BY </br>
							<span style=font-weight:normal; font-size:smaller;>(Write in full)</span>
							</th>
					  </tr>
					  <tr>
						<th style=text-align:center;>FROM</th>
						<th style=text-align:center;>TO</th>
					  </tr>
					</thead>
					<tbody id= 'showld'>
		",
        "Dsbuttom" => "
					</tbody>
				  </table>
				</div>
			  </section>
		",
		"elements" => [
			["name"=>"Title"
				,"type"=>"text"
				,"placeholder"=>"Title"
			],
			["name"=>"From"
				,"type"=>"text"
				,"placeholder"=>"From"
			],
			["name"=>"To"
				,"type"=>"text"
				,"placeholder"=>"To"
			],
			["name"=>"NumHour"
				,"type"=>"text"
				,"placeholder"=>"Number of Hour"
			],
			["name"=>"TypeofLD"
				,"type"=>"text"
				,"placeholder"=>"TypeofLD"
			],
			["name"=>"ConductOrSponsored"
				,"type"=>"text"
				,"placeholder"=>"Conduct / Sponsored"
			]
		]
	],
    "other" => [
		"MtherTable"=>"tbl_EmpCSCOtherInformation",
		"MtherPrimaryID"=>"IndexId",
		"elDataDsplay"=>"other",
		"tbodyID"=>"showother",
		"DsTop" => "
			  <section class='section'>
				<h3>VIII. OTHER INFORMATION</h3>
				<div class=table-responsive>
				  <table>
					<thead>
					  <tr>
						<th style='text-align:center;' rowspan='2'></th>
						<th rowspan='2' style='text-align:center;'>SPECIAL SKILLS and HOBBIES</th>
						<th rowspan='2' style='text-align:center;'>NON-ACADEMIC DISTINCTIONS / RECOGNITION </br> 
							<span style='font-weight:normal; font-size:smaller;'>(Write in full)</span>
							</th>
						<th rowspan='2' style='text-align:center;'>MEMBERSHIP IN ASSOCIATION/ORGANIZATION </br>
							<span style='font-weight:normal; font-size:smaller;'>(Write in full)</span>
							</th>
					</thead>
					<tbody id= 'showother'>
		",
        "Dsbuttom" => "
					</tbody>
				  </table>
				</div>
			  </section>
		",
		"elements" => [
			["name"=>"SpecialSkillsHobbies"
				,"type"=>"text"
				,"placeholder"=>"SpecialSkillsHobbies"
			],
			["name"=>"NonAcadDistinctions"
				,"type"=>"text"
				,"placeholder"=>"NonAcadDistinctions"
			],
			["name"=>"MembershipOrganization"
				,"type"=>"text"
				,"placeholder"=>"MembershipOrganization"
			]
		]
	]
];
?>
