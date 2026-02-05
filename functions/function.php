<?php
function getlvAllPosition(
	$slfield = "[position_id]
					  ,[position_code]
					  ,[position_desc]
					  ,[plantilla_num]
					  ,[isActive]"

) {
	$adduserpos = execsqlES(" 
				SELECT $slfield
				  FROM [tbl_Positions]
				ORDER BY Position
				", "Select", array());
	return $adduserpos;
}
