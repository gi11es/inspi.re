<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Transfers money from inspi.re to paypal
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();

if (isset($_REQUEST['amount']) && isset($_REQUEST['account'])) {
	// Make sure that we don't generate a code with more money than what is available
	$amount = min(floatval($_REQUEST['amount']), $user->getBalance());
	$actual_amount = ceil(100 * ($amount + min(1, 0.02 * $amount))) / 100.0;
	
	$user->decrementBalance($actual_amount);
	$email = trim($_REQUEST['account']);
	
	$request = 'USER='.$PAYPAL_API_USERNAME.'&';
	$request .= 'PWD='.$PAYPAL_API_PASSWORD.'&';
	$request .= 'SIGNATURE='.$PAYPAL_API_SIGNATURE.'&';
	$request .= 'VERSION=56.0&';
	$request .= 'METHOD=MassPay&';
	$request .= 'RECEIVERTYPE=EmailAddress&';
	$request .= 'L_EMAIL0='.urlencode($email).'&';
	$request .= 'L_AMT0='.urlencode($amount).'&';
	$request .= 'CURRENCYCODE=USD';
	
	$header = "POST /nvp HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($request) . "\r\n\r\n";
	
	
	$host=str_replace("https://","",$PAYPAL_API_SERVER);
 	$host=substr($host,0,strpos($host,"/"));
	
	$err="";
	$response="";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $PAYPAL_API_SERVER);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Host: '.$host,
	'Content-type: application/x-www-form-urlencoded', 
	'Content-Length: '. strlen($request)
	));
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	$http_response=curl_exec($ch);
	if (curl_error($ch)){
	$err = "curl failed";
	$response= "cURL ERROR: ".curl_errno($ch).": ".curl_error($ch);
	}
	curl_close($ch);

	 if (!$err) {
	  // check for server errors, if any (unlikely)
	  if (strpos($http_response,"200 OK")===false) {
	   $err="paypal server returned an http error code";
	   $response=$http_response;
	  } else {
	   // clean http headers
	   $http_response = ereg_replace("^[^<]*\r\n\r\n","", $http_response);
	   
	   $response=$http_response;
	  }
	 }

	parse_str($response, $resp);
	
	if (strcasecmp($resp['ACK'], 'Failure') == 0) {
		$user->incrementBalance($actual_amount);
		mail('kouiskas@gmail.com', 'Mass pay error: '.$resp['L_SHORTMESSAGE0'], $resp['L_LONGMESSAGE0']."\n\n".'Amount = $'.$amount."\n".'Destination paypal account = '.$email);
		echo '0';
	} else {
		mail('kouiskas@gmail.com', 'Mass pay', print_r($resp, true)."\n\n".'Amount = $'.$amount."\n".'Destination paypal account = '.$email);
		echo $amount;
	}
}
?>