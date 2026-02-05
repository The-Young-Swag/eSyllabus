<?php
//echo psEncrypt("UaNXZVejwTPU6A8UL/+U3g==", "");
/* echo psdecrypt("RvYgZ0s3VuXwDi3p6dVvLw==", "");



function psEncrypt($plainText, $key) {
	$method = "AES-256-CBC";
	$key = substr(hash('sha256', $key, true), 0, 32); // Ensure 256-bit key
	$iv = "1234567890123456"; // 16-byte IV (same as in VB.NET)

	$cipherText = openssl_encrypt($plainText, $method, $key, OPENSSL_RAW_DATA, $iv);
	return base64_encode($cipherText);
}
 */

function psdecrypt($plainText, $key) {
	$method = "AES-256-CBC";
	$key = substr(hash('sha256', $key, true), 0, 32); // Ensure 256-bit key
	$iv = "1234567890123456"; // 16-byte IV (same as in VB.NET)

	$cipherText = openssl_decrypt(base64_decode($plainText), $method, $key, OPENSSL_RAW_DATA, $iv);
	return base64_encode($cipherText);
}


function encrypt_decrypt($action, $string) {
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'This is my secret key';
    $secret_iv = 'This is my secret iv';

    // hash
    $key = hash('sha256', $secret_key);
    
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}
/* 
$plain_txt = "UaNXZVejwTPU6A8UL/+U3g==";
echo "Plain Text =" .$plain_txt. "<br>";

$encrypted_txt = encrypt_decrypt('encrypt', $plain_txt);
echo "Encrypted Text = " .$encrypted_txt. "<br>";

$decrypted_txt = encrypt_decrypt('decrypt', $plain_txt);
echo "Decrypted Text =" .$decrypted_txt. "<br>";

if ( $plain_txt === $decrypted_txt ) echo "SUCCESS";
else echo "FAILED";

echo "<br>"; */


?>