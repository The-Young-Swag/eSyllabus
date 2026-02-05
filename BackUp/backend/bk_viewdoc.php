<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$view_doc = isset($_POST["view_doc"]) ? $_POST["view_doc"] : "";

switch ($request) {

	case "viewdoc":

		// Fetch all menu items at once
		$queryviewview = execsqlSRS("
			SELECT doc_type, tracking_id, doc_details, doc_number, doc_date, doc_proponent, doc_amount, doc_enduser, doc_payee
			FROM tbl_Intersection 
			WHERE tracking_id = :view_doc", 
			"Select",
			[":view_doc" => $view_doc]
			);

				foreach ($queryviewview as $view) {
					
echo "<div class=''>
              <div class='card mb-3'>
                <div class='card-body'>
                  <div class='row'>
                    <div class='col-sm-3'>
                      <h6 class='mb-0 text-secondary'>Document Type</h6>
                    </div>
                    <div class='col-sm-9'>
                      ";
echo					  htmlspecialchars($view["doc_type"]);
echo				  "
                    </div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Tracking</h6>
                    </div>
                    <div class='col-sm-9'>
					<strong>";
echo                      htmlspecialchars($view["tracking_id"]);
echo                    "</strong>
					</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Document Details</h6>
                    </div>
                    <div class='col-sm-9'>
					<strong>";
echo                      htmlspecialchars($view["doc_details"]);
echo                    "</strong>
					</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Number</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($view["doc_number"]);
echo                    "</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Date</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($view["doc_date"]);
echo                    "</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Proponent</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($view["doc_proponent"]);
echo                    "</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Amount</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($view["doc_amount"]);
echo                    "</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>End-User</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($view["doc_enduser"]);
echo                    "</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Payee</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($view["doc_payee"]);
echo                    "</div>
                  </div>
                 
                </div>
              </div>
</div>";	
				}
				
	break;
}

?>
