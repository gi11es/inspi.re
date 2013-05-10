<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Adds a user to the list of moderators for a community
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitymoderator.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();

$uid = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);
$xid = (isset($_REQUEST['xid'])?$_REQUEST['xid']:null);

if ($uid === null || $xid === null) {
	header('Location: /Members/s3-l'.$user->getLid());
	exit(0);
}

try {
	$community = Community::get($xid);
	if ($user->getUid() != $community->getUid()) {
		header('Location: '.UI::RenderUserLink($uid));
		exit(0);
	}
	
	try {
		$moderator = CommunityModerator::get($xid, $uid);
	} catch (CommunityModeratorException $e) {
		$moderator = new CommunityModerator($xid, $uid);
		$alert = new Alert($ALERT_TEMPLATE_ID['MODERATION_RIGHTS_GIVEN']);
		$aid = $alert->getAid();
		$alert_variable = new AlertVariable($aid, 'xid', $xid);
		$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
		$alert_instance = new AlertInstance($aid, $uid, $ALERT_INSTANCE_STATUS['NEW']);
	}
} catch (CommunityException $e) {
	header('Location: '.UI::RenderUserLink($uid));
	exit(0);
}

header('Location: '.UI::RenderUserLink($uid));

?>