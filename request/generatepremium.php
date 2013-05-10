<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Convert some or all of a user's account balance into a premium membership code
*/

require_once(dirname(__FILE__).'/../entities/premiumcode.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

if (isset($_REQUEST['amount'])) {
	// Make sure that we don't generate a code with more money than what is available
	$amount = min(floatval($_REQUEST['amount']), $user->getBalance());
	$user->decrementBalance($amount);
	
	// The amount of days depends on which price segment it's in
	if ($amount <= 5) {
		$days = floor($amount * 6.2);
	} else if ($amount <= 25) {
		$days = floor($amount * 7.32);
	} else if (amount < 125) {
		$days = floor($amount * 9.125);
	} else {
		$days = 3650;
	}
	
	$code = new PremiumCode(86400 * $days);
	$displaycode = $code->getDisplayCode();
	
	if ($days == 3650) Email::mail($user->getEmail(), $user->getLid(), 'PREMIUM_CODE_LIFETIME', 
			array('code' => $displaycode)
			);
	
	else Email::mail($user->getEmail(), $user->getLid(), 'PREMIUM_CODE', 
			array('code' => $displaycode, 'days' => $days)
			);
	
	echo $displaycode;
}

?>