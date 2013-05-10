<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Return the list of alerts for the current user
*/

require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$result = array();
		
$newlist = AlertInstanceList::getByUidAndStatus($user->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
		
echo UI::RenderAlerts($user, $newlist, true);	

?>