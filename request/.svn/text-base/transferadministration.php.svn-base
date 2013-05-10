<?php

/** 
 * Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 *       
 * Transfer administration rights of a community to another user
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitymembership.php');
require_once(dirname(__FILE__).'/../entities/communitymoderator.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$uid = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);
$xid = (isset($_REQUEST['xid'])?$_REQUEST['xid']:null);
$persistenttokenhash = (isset($_REQUEST['persistenttoken'])?$_REQUEST['persistenttoken']:null);

if ($persistenttokenhash === null && ($uid === null || $xid === null)) {
	header('Location: /Members/s3-l'.$user->getLid());
	exit(0);
}

if ($persistenttokenhash !== null) try {
	$persistenttoken = PersistentToken::get($persistenttokenhash);
	$res = explode('-', $persistenttoken);
	if (isset($res[0]) && isset($res[1])) {
		$xid = $res[0];
		$uid = $res[1];
	} else {
		header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
		exit(0);
	}
} catch (PersistentTokenException $e) {
	header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
	exit(0);
}

try {
	$community = Community::get($xid);
	$old_uid = $community->getUid();
	
	if ($persistenttokenhash === null && $user->getUid() != $community->getUid()) {
		header('Location: '.UI::RenderUserLink($uid));
		exit(0);
	}
	
	$member = User::get($uid);
	$community->setUid($uid);

	try {
		$membership = CommunityMembership::get($xid, $old_uid);
		$membership->setStatus($COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);
	} catch (CommunityMembershipException $f) {
		$membership = new CommunityMembership($xid, $old_uid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);
	}
	
	try {
		$membership = CommunityMembership::get($xid, $uid);
		$membership->delete();
	} catch (CommunityMembershipException $f) {}
	
	$alert = new Alert($ALERT_TEMPLATE_ID['COMMUNITY_TRANSFERRED']);
	$aid = $alert->getAid();
	$alert_variable = new AlertVariable($aid, 'uid', $old_uid);
	$alert_variable = new AlertVariable($aid, 'xid', $community->getXid());

	$alert_instance = new AlertInstance($aid, $uid, $ALERT_INSTANCE_STATUS['ASYNC']);
} catch (CommunityException $e) {
	header('Location: '.UI::RenderUserLink($uid));
	exit(0);
} catch (UserException $e) {
	header('Location: '.UI::RenderUserLink($uid));
	exit(0);
}

if ($persistenttokenhash === null)
	header('Location: '.UI::RenderUserLink($uid));
else
	header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$xid.'&relinquished=true');

?>
