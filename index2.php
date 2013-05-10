<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Main page, shall contain a user's current and past entries when logged in
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page(null, null, $user);
$page->addJavascript('HOME');
$page->addJavascript('EARTH');
$page->addStyle('HOME');

$page->startHTML();

$geoip_record = geoip_record_by_name($_SERVER['REMOTE_ADDR']);
$page->addJavascriptVariable('latitude', $geoip_record['latitude']);
$page->addJavascriptVariable('longitude', $geoip_record['longitude']);

?>

<div id='earth'></div>

<?php
$page->endHTML();
$page->render();
?>
