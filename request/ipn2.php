<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Payment notifications coming from moneybookers
*/

require_once(dirname(__FILE__).'/../entities/premiumcode.php');
require_once(dirname(__FILE__).'/../entities/premiumcodelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../constants.php');

mail('ipn@inspi.re', 'IPN Moneybookers '.gmmktime(), print_r($_REQUEST, true));

if (isset($_REQUEST['mb_transaction_id']) && isset($_REQUEST['status']) && isset($_REQUEST['amount']) && isset($_REQUEST['currency']) && isset($_REQUEST['pay_from_email'])) {
    $payment_status = intval($_REQUEST['status']);
    $payment_amount = $_REQUEST['amount'];
    $payment_currency = $_REQUEST['currency'];
    $payer_email = $_REQUEST['pay_from_email'];
    $txn_id = $_REQUEST['mb_transaction_id'];
    
    if ($payment_status == 2) {
        $days = 0;
        switch (strtoupper($payment_currency)) {
            case 'EUR':
                if ($payment_amount == 4.00) $days = 31;
                elseif ($payment_amount == 20.00) $days = 183;
                elseif ($payment_amount == 30.00) $days = 365;
                elseif ($payment_amount == 100.00) $days = 3650;
                break;
            case 'USD':
                if ($payment_amount == 5.00) $days = 31;
                elseif ($payment_amount == 25.00) $days = 183;
                elseif ($payment_amount == 40.00) $days = 365;
                elseif ($payment_amount == 125.00) $days = 3650;
                break;
            case 'CAD':
                if ($payment_amount == 6.00) $days = 31;
                elseif ($payment_amount == 30.00) $days = 183;
                elseif ($payment_amount == 50.00) $days = 365;
                elseif ($payment_amount == 150.00) $days = 3650;
                break;
            case 'AUD':
                if ($payment_amount == 8.00) $days = 31;
                elseif ($payment_amount == 40.00) $days = 183;
                elseif ($payment_amount == 60.00) $days = 365;
                elseif ($payment_amount == 200.00) $days = 3650;
                break;
            case 'NZD':
                if ($payment_amount == 10.00) $days = 31;
                elseif ($payment_amount == 50.00) $days = 183;
                elseif ($payment_amount == 75.00) $days = 365;
                elseif ($payment_amount == 250.00) $days = 3650;
                break;
            case 'GBP':
                if ($payment_amount == 4.00) $days = 31;
                elseif ($payment_amount == 20.00) $days = 183;
                elseif ($payment_amount == 25.00) $days = 365;
                elseif ($payment_amount == 90.00) $days = 3650;
                break;
            default:
                break;
        }
        
        if ($days == 0) {
				mail('beta@inspi.re', '(IPN Moneybookers) Invalid amount '.$txn_id, print_r($_REQUEST, true));
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
					mail('beta@inspi.re', '(IPN Moneybookers) Duplicate txn_id '.$txn_id, print_r($_REQUEST, true));
            }
		}
    } else {
        mail('beta@inspi.re', '(IPN Moneybookers) Invalid status '.$txn_id, print_r($_REQUEST, true));
    }
}

?>