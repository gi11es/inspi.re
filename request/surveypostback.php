<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       This script is hit when a member completes a survey
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevel.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../constants.php');

if (isset($_REQUEST['survey']) && isset($_REQUEST['subid']) && isset($_REQUEST['earn']) && isset($_REQUEST['password']) && strcasecmp($_REQUEST['password'], 'cp4l34d') == 0) {
	try {
		$member = User::get($_REQUEST['subid']);
		$referencetime = max(time(), $member->getPremiumTime());
		$duration = ceil(floatval($_REQUEST['earn']) * 6) * 86400;
		$member->setPremiumTime($referencetime + $duration);
		$userlevel = new UserLevel($member->getUid(), $USER_LEVEL['PREMIUM']);
		
		$alert = new Alert($ALERT_TEMPLATE_ID['PREMIUM_SURVEY']);
		$aid = $alert->getAid();
		$alert_variable = new AlertVariable($aid, 'duration', $duration);
		$alert_variable = new AlertVariable($aid, 'survey_name', String::htmlentities($_REQUEST['survey']));
		$alert_instance = new AlertInstance($aid, $member->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);
	} catch (UserException $e) {}
}

?>