<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Renders the payment options for a given currency
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$ip_history = $user->getIpHistory();
$last_ip = array_shift(array_keys($ip_history));
$record = @geoip_record_by_name($last_ip);

if (isset($record['country_code'])) {
	$urladdition = '&lc='.$record['country_code'];
} else $urladdition = '';

echo UI::RenderCurrencyPayment($user, $_REQUEST['currency'], $urladdition, true);

?>