<?php
include "../../db/dbconnection.php";
date_default_timezone_set("Asia/Manila");
header("Content-Type: application/json");

function send($data){ echo json_encode($data); exit; }

if($_SERVER["REQUEST_METHOD"] !== "POST"){ send(["error"=>"Invalid request method"]); }

$request = $_POST["request"] ?? '';

try {
    switch($request){

        case "getLibraries":
            $libraries = execsqlSRS("SELECT SectionID, SectionName FROM LibrarySection WHERE IsActive=1 ORDER BY SectionID ASC","Query",[]);
            send(["success"=>true,"data"=>$libraries]);
        break;

        case "validateUser":
            $idNumber = trim($_POST["idNumber"] ?? '');
            $students = json_decode(file_get_contents(__DIR__."/../../API_requests/students.json"), true)["data"] ?? [];
            $employees = json_decode(file_get_contents(__DIR__."/../../API_requests/employees.json"), true)["data"] ?? [];

            $matchedStudents = array_filter($students, fn($s)=> $s["id_number"] === $idNumber);
            if(count($matchedStudents)===1){
                $s=array_values($matchedStudents)[0];
                send(["success"=>true,"data"=>[
                    "id_number"=>$s["id_number"],"name"=>$s["name"],"sex"=>$s["sex"],
                    "college"=>$s["college"],"course"=>$s["course"],"classification"=>"STUDENT",
                    "secretKey"=>$s["secret_key"]
                ]]);
            } elseif(count($matchedStudents)>1){
                $matches = array_map(fn($s)=>[
                    "id_number"=>$s["id_number"],"name"=>$s["name"],"sex"=>$s["sex"],
                    "college"=>$s["college"],"course"=>$s["course"],"classification"=>"STUDENT",
                    "secretKey"=>$s["secret_key"]
                ], array_values($matchedStudents));
                send(["duplicate"=>true,"matches"=>$matches]);
            } else {
                $matchedEmp = array_filter($employees, fn($e)=> $e["employee_number"]===$idNumber);
                if(count($matchedEmp)===1){
                    $e = array_values($matchedEmp)[0];
                    send(["success"=>true,"data"=>[
                        "id_number"=>$e["employee_number"],"name"=>$e["name"],"sex"=>$e["sex"],
                        "college"=>$e["department"],"course"=>$e["position"],"classification"=>"EMPLOYEE","secretKey"=>null
                    ]]);
                } else send(["error"=>"User not found"]);
            }
        break;


							//API READY CODE
											/* case "validateUser":

												$idNumber = trim($_POST["idNumber"] ?? '');

												$apiUrl = "https://api.yourschool.edu/validate-user";

												$payload = json_encode([
													"id_number" => $idNumber
												]);

												$ch = curl_init($apiUrl);
												curl_setopt_array($ch, [
													CURLOPT_RETURNTRANSFER => true,
													CURLOPT_POST => true,
													CURLOPT_HTTPHEADER => [
														"Content-Type: application/json",
														"Authorization: Bearer YOUR_API_TOKEN"
													],
													CURLOPT_POSTFIELDS => $payload
												]);

												$response = curl_exec($ch);

												if(curl_errno($ch)){
													send(["error" => "API connection failed"]);
												}

												curl_close($ch);

												$apiData = json_decode($response, true);

												if(!$apiData){
													send(["error" => "Invalid API response"]);
												}

												send($apiData);

											break; */

								//API READY CODE V2
													/* case "validateUser":

														$idNumber = trim($_POST["idNumber"] ?? '');

														$student = callAPI("https://school.edu/api/students/".$idNumber);

														if($student && !empty($student["data"])){

															send([
																"success" => true,
																"data" => [
																	"id_number" => $student["data"]["id_number"],
																	"name" => $student["data"]["name"],
																	"sex" => $student["data"]["sex"],
																	"college" => $student["data"]["college"],
																	"course" => $student["data"]["course"],
																	"classification" => "STUDENT",
																	"secretKey" => $student["data"]["secret_key"]
																]
															]);

														} else {

															$employee = callAPI("https://school.edu/api/employees/".$idNumber);

															if($employee && !empty($employee["data"])){

																send([
																	"success" => true,
																	"data" => [
																		"id_number" => $employee["data"]["employee_number"],
																		"name" => $employee["data"]["name"],
																		"sex" => $employee["data"]["sex"],
																		"college" => $employee["data"]["department"],
																		"course" => $employee["data"]["position"],
																		"classification" => "EMPLOYEE",
																		"secretKey" => null
																	]
																]);

															} else {
																send(["error" => "User not found"]);
															}
														}

													break; */

        case "saveAttendance":
            $idNumber=trim($_POST["idNumber"]??''); $sectionID=intval($_POST["sectionID"]??0);
            $action=trim($_POST["action"]??''); $classification=$_POST["classification"]??'';
            $name=$_POST["name"]??''; $college=$_POST["college"]??''; $course=$_POST["course"]??''; $sex=$_POST["sex"]??'';

            if(!$idNumber || !$sectionID || !$action) send(["error"=>"Missing required data"]);

            $now = date("Y-m-d H:i:s"); $today = date("Y-m-d");
            $pdo = dbconES(); $pdo->beginTransaction();

            try {
                if($action==="checkin"){
                    $stmt=$pdo->prepare("SELECT COUNT(*) FROM Library_logs WHERE id_number=? AND library=? AND checkout_time IS NULL AND CAST(checkin_time AS DATE)=?");
                    $stmt->execute([$idNumber,$sectionID,$today]);
                    $alreadyCheckedIn = $stmt->fetchColumn();

                    if(!$alreadyCheckedIn){
                        $stmt=$pdo->prepare("UPDATE Library_logs SET checkout_time=? WHERE id_number=? AND checkout_time IS NULL AND CAST(checkin_time AS DATE)=? AND library<>?");
                        $stmt->execute([$now,$idNumber,$today,$sectionID]);

                        $stmt=$pdo->prepare("INSERT INTO Library_logs (id_number,name,classification,college,course,library,checkin_time,sex) VALUES (?,?,?,?,?,?,?,?)");
                        $stmt->execute([$idNumber,$name,$classification,$college,$course,$sectionID,$now,$sex]);
                    }
                } elseif($action==="checkout"){
                    $stmt=$pdo->prepare("UPDATE Library_logs SET checkout_time=? WHERE id_number=? AND library=? AND checkout_time IS NULL AND CAST(checkin_time AS DATE)=?");
                    $stmt->execute([$now,$idNumber,$sectionID,$today]);
                }

                $kpi = getLibraryKPI($pdo,$sectionID,$today);
                $pdo->commit();
                send(["success"=>true,"action"=>$action,"kpi"=>$kpi]);

            } catch(Exception $e){
                $pdo->rollBack();
                send(["error"=>$e->getMessage()]);
            }
        break;

        case "checkStatusToday":
            $idNumber=trim($_POST["idNumber"]??''); $pdo=dbconES(); $today=date("Y-m-d");
            $stmt=$pdo->prepare("SELECT TOP 1 library FROM Library_logs WHERE id_number=? AND checkout_time IS NULL AND CONVERT(date, checkin_time)=? ORDER BY checkin_time DESC");
            $stmt->execute([$idNumber,$today]); $row=$stmt->fetch(PDO::FETCH_ASSOC);
            send($row?["checkedIn"=>true,"sectionID"=>intval($row['library'])]:["checkedIn"=>false]);
        break;

        case "getKPI":
            $sectionID=intval($_POST["sectionID"]??0);
            $pdo=dbconES(); $kpi=getLibraryKPI($pdo,$sectionID,date("Y-m-d"));
            send(["success"=>true,"data"=>$kpi]);
        break;

        default: send(["error"=>"Invalid request"]);
    }

} catch(Exception $e){ send(["error"=>$e->getMessage()]); }


// ======================
// KPI HELPER
// ======================
function getLibraryKPI($pdo,$sectionID,$today){
    $result=["totalToday"=>0,"currentlyInside"=>0,"topColleges"=>["-","-","-"],"topCourses"=>["-","-","-"]];
    try{
        $stmt=$pdo->prepare("SELECT COUNT(*) AS totalToday,SUM(CASE WHEN checkout_time IS NULL THEN 1 ELSE 0 END) AS currentlyInside FROM Library_logs WHERE library=? AND CAST(checkin_time AS DATE)=?");
        $stmt->execute([$sectionID,$today]); $data=$stmt->fetch(PDO::FETCH_ASSOC);
        $result["totalToday"]=intval($data["totalToday"]??0); $result["currentlyInside"]=intval($data["currentlyInside"]??0);

        $stmt=$pdo->prepare("SELECT TOP 3 college, COUNT(*) AS cnt FROM Library_logs WHERE library=? AND CONVERT(date, checkin_time)=? AND college IS NOT NULL AND college<>'' GROUP BY college ORDER BY cnt DESC");
        $stmt->execute([$sectionID,$today]); $result["topColleges"]=array_pad(array_column($stmt->fetchAll(PDO::FETCH_ASSOC),"college"),3,"-");

        $stmt=$pdo->prepare("SELECT TOP 3 course, COUNT(*) AS cnt FROM Library_logs WHERE library=? AND CONVERT(date, checkin_time)=? AND course IS NOT NULL AND course<>'' GROUP BY course ORDER BY cnt DESC");
        $stmt->execute([$sectionID,$today]); $result["topCourses"]=array_pad(array_column($stmt->fetchAll(PDO::FETCH_ASSOC),"course"),3,"-");

    } catch(Exception $e){ error_log("KPI error: ".$e->getMessage()); }
    return $result;
}
?>
