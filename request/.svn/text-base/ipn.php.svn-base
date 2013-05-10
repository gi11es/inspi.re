<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Payment notifications coming from paypal
*/

require_once(dirname(__FILE__).'/../entities/premiumcode.php');
require_once(dirname(__FILE__).'/../entities/premiumcodelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../constants.php');

if (isset($_GET['secret']) && strcmp($_GET['secret'], 'babliblou1746') == 0) {
	$req = 'cmd=_notify-validate';
	
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}
	
	// renvoyer au système PayPal pour validation
	$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
	
	// affecter les variables du formulaire aux variables locales
	$item_name = $_POST['item_name'];
	$item_number = $_POST['item_number'];
	$payment_status = $_POST['payment_status'];
	
	$payment_amount = $_POST['mc_gross']; // 4
	$payment_currency = $_POST['mc_currency']; // USD
	
	$txn_id = $_POST['txn_id'];
	$receiver_email = $_POST['receiver_email'];
	$payer_email = $_POST['payer_email'];
	
	mail('ipn@inspi.re', 'IPN '.$txn_id, print_r($_REQUEST, true));
	
	if (strcasecmp($_POST['txn_type'], 'masspay') == 0) exit(0);
	
	if (!$fp) {
	// ERREUR HTTP
	} else {
		fputs ($fp, $header . $req);
		while (!feof($fp)) $res = fgets ($fp, 1024);
		fclose ($fp);
			
		if (strcasecmp ($res, "VERIFIED") == 0 && strcasecmp($receiver_email, 'premium@inspi.re') == 0 && strcasecmp($payment_status, 'Completed') == 0) {
			switch ($item_number) {
				case 1:
					$days = 31;
					break;
				case 2:
					$days = 183;
					break;
				case 3:
					$days = 365;
					break;
				case 4:
					$days = 3650;
					break;
				default:
					$days = 0;
					break;
			}
			
			if ($days == 0) {
				mail('beta@inspi.re', '(IPN) Invalid item_number '.$txn_id, print_r($_REQUEST, true));
			} else {	
				$premiumcodelist = PremiumCodeList::getByTXNid($txn_id);
				if (empty($premiumcodelist)) {
					$code = new PremiumCode(86400 * $days);
					$code->setTXNid($txn_id);
					$displaycode = $code->getDisplayCode();
					
					$useremail = trim(strtolower($payer_email));
					try {
						$user = User::getByEmail($useremail);
						$lid = $user->getLid();
					} catch (UserException $e) {
						$lid = $LANGUAGE['EN'];
					}
					
					if ($days == 3650) Email::mail($useremail, $lid, 'PREMIUM_CODE_LIFETIME', 
							array('code' => $displaycode)
							);
	
					else Email::mail($useremail, $lid, 'PREMIUM_CODE', 
							array('code' => $displaycode, 'days' => $days)
							);
				} else {
					mail('beta@inspi.re', '(IPN) Duplicate txn_id '.$txn_id, print_r($_REQUEST, true));
				}
			}
		} else {
			mail('beta@inspi.re', '(IPN) Invalid receiver_email or payment_status '.$txn_id, print_r($_REQUEST, true));
		}
	}
}

?>