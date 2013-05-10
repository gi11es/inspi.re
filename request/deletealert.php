<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Deletes an existing entry
*/

require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$aid = isset($_REQUEST['aid'])?$_REQUEST['aid']:null;

$result = array();

if ($aid !== null) {
	try {
		$alertinstance = AlertInstance::get($aid, $user->getUid());
		$alertinstance->setStatus($ALERT_INSTANCE_STATUS['READ']);
		
		$result['status'] = 0;
		
		$newlist = AlertInstanceList::getByUidAndStatus($user->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
		$alertcount = count($newlist);
		
		$result['alerts_counter'] = UI::RenderAlertsCounter($user, $alertcount, true);
		$result['alerts'] = UI::RenderAlerts($user, $newlist, true);	
	} catch (AlertInstanceException $e) {
		$result['status'] = 1;
	}
} else $result['status'] = 1;

echo json_encode($result);

?>