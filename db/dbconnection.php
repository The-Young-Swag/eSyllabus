<?php

function execsqlSRS($sql, $oper, $arr)
{
	$dbc = dbconES();


	if ($oper == "Insert" or $oper == "Update" or $oper == "Delete") {

		#echo $sql;
		$stmt = $dbc->prepare($sql);
		$stmt->execute($arr);
	} else {
		if (!$dbc == 0) {
			$stmt = $dbc->prepare($sql);
			$stmt->execute($arr);
			$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$dbc = null;

			return $row;
		} else {
			return "No DB";
		}
	}
}

function dbconES()
{
	include "../config/config.php";
	try {

		$dbh = new PDO("sqlsrv:Server={$srsServer};Database={$srsDB}", "", "");
	} catch (Exception $e) {
		$dbh->rollBack();
		echo "Failed: " . $e->getMessage();
		$dbh = 0;
	}

	$dbh->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);
	return $dbh;
}
#connection string for DBHRMO

function execsqlES($sql, $oper, $arr)
{
	$dbc = SysQeurydbcon();


	if ($oper == "Insert" or $oper == "Update" or $oper == "Delete") {

		#echo $sql;
		$stmt = $dbc->prepare($sql);
		$stmt->execute($arr);
	} else {
		if (!$dbc == 0) {
			$stmt = $dbc->prepare($sql);
			$stmt->execute($arr);
			$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$dbc = null;

			return $row;
		} else {
			return "No DB";
		}
	}
}


function SysQeurydbcon()
{
	include "../config/config.php";
	try {

		$dbh = new PDO("sqlsrv:Server=" . trim($SysServer) . ";Database=" . trim($SysDB), "", "");
		//$dbh = new PDO("sqlsrv:Server=".trim($SysServer).";Database=".trim($SysDB), "", "");
	} catch (Exception $e) {
		$dbh->rollBack();
		echo "Failed: " . $e->getMessage();
		$dbh = 0;
	}

	$dbh->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);
	return $dbh;
}

function exeLiveDbQuery($sql, $oper, $arr)
{
	$dbc = exeLiveDb();


	if ($oper == "Insert" or $oper == "Update" or $oper == "Delete") {

		#echo $sql;
		$stmt = $dbc->prepare($sql);
		$stmt->execute($arr);
	} else {
		if (!$dbc == 0) {
			$stmt = $dbc->prepare($sql);
			$stmt->execute($arr);
			$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$dbc = null;

			return $row;
		} else {
			return "No DB";
		}
	}
}

function exeLiveDb()
{
	include "../config/config.php";
	try {

		$dbh = new PDO("sqlsrv:Server={$SysLVServer};Database={$SysLVDB}", "", "");
	} catch (Exception $e) {
		$dbh->rollBack();
		echo "Failed: " . $e->getMessage();
		$dbh = 0;
	}

	$dbh->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);
	return $dbh;
}
