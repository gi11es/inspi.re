<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Remove a user from the list of moderators of a community
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
		$moderator->delete();
		
		$demoteduser = User::get($uid);
		
		if ($demoteduser->getStatus() == $USER_STATUS['ACTIVE']) {
			$alert = new Alert($ALERT_TEMPLATE_ID['MODERATION_RIGHTS_TAKEN']);
			$aid = $alert->getAid();
			$alert_variable = new AlertVariable($aid, 'xid', $xid);
			$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
			$alert_instance = new AlertInstance($aid, $uid, $ALERT_INSTANCE_STATUS['ASYNC']);
		}
	} catch (CommunityModeratorException $e) {}
	catch (UserException $f) {}
} catch (CommunityException $e) {
	header('Location: '.UI::RenderUserLink($uid));
	exit(0);
}

header('Location: '.UI::RenderUserLink($uid));

?>