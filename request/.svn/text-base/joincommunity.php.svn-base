<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Join an existing community
*/

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitymembership.php');
require_once(dirname(__FILE__).'/../entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/log.php');

$user = User::getSessionUser();

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;

if ($xid !== null) {
	$community = Community::get($xid);
	
	switch ($user->getStatus()) {
		case $USER_STATUS['ACTIVE']:
			$status = $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE'];
			break;
		case $USER_STATUS['UNREGISTERED']:
			$status = $COMMUNITY_MEMBERSHIP_STATUS['UNREGISTERED'];
			break;
		case $USER_STATUS['DELETED']:
			$status = $COMMUNITY_MEMBERSHIP_STATUS['DELETED'];
			break;
		case $USER_STATUS['BANNED']:
			$status = $COMMUNITY_MEMBERSHIP_STATUS['BANNED'];
			break;
	}
	$membershiplist = CommunityMembershipList::getByUid($user->getUid());
	if (!in_array($xid, $membershiplist) && $community->getUid() != $user->getUid()) {
		$community_membership = new CommunityMembership($xid, $user->getUid(), $status);
		
		if ($user->getStatus() == $USER_STATUS['ACTIVE'])
			Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$user->getUid().'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_JOIN_COMMUNITY"><user_name uid="'.$user->getUid().'"/> joined the <community_name xid="'.$xid.'" link="true"/> community</translate></div>');
		
		header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$xid.'&joined=true');
	} else header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$xid);
} else header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());

?>