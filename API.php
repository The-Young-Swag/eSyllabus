<?php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'tau.edu.ph:8087/ProxyTAUService/studentLibrary',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "UserAccount" : "LibrarySys",
    "Password" : "libraryAPI",
    "deviceUUID": "LibSys"
}
',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer accessLibrary'
  ),
));

$response = curl_exec($curl);

curl_close($curl);




$now = date("Y-m-d H:i:s");


