<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Updates the language preference of a given user
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/string.php');

$user = User::getSessionUser();

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;

$isauthorized = false;
if ($xid !== null) try {
	$community = Community::get($xid);
	if ($community->getUid() == $user->getUid()) {
		$isauthorized = true;
	}
	
	$moderatorlist = CommunityModeratorList::getByXid($xid);
	if (in_array($user->getUid(), $moderatorlist)) {
		$isauthorized = true;
	}
	
	$discussionthreadlist = DiscussionThreadList::getbyXidAndStatus($xid, $DISCUSSION_THREAD_STATUS['ACTIVE']);
	$toosoon = (gmmktime() - max($discussionthreadlist)) < 86400;
} catch (CommunityException $e) {}

if (isset($_REQUEST['title']) && isset($_REQUEST['text']) && $isauthorized && !$toosoon) {

	if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
		$thread_status = $DISCUSSION_THREAD_STATUS['ANONYMOUS'];
		$post_status = $DISCUSSION_POST_STATUS['ANONYMOUS'];
	} elseif ($user->getStatus() == $USER_STATUS['BANNED']) {
		$thread_status = $DISCUSSION_THREAD_STATUS['BANNED'];
		$post_status = $DISCUSSION_POST_STATUS['BANNED'];
	} else {
		$thread_status = $DISCUSSION_THREAD_STATUS['ACTIVE'];
		$post_status = $DISCUSSION_POST_STATUS['POSTED'];
	}
		
	$thread = new DiscussionThread(stripslashes($_REQUEST['title']), $user->getUid(), $thread_status, $xid);
	$post = new DiscussionPost($thread->getNid(), $user->getUid(), stripslashes($_REQUEST['text']), $post_status);

	Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$user->getUid().'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_DISCUSSION_THREAD"><user_name uid="'.$user->getUid().'"/> posted <a href="'.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$thread->getNid().'">a new announcement</a> in the <community_name xid="'.$xid.'" link="true"/> community</translate></div>');

	$memberslist = CommunityMembershipList::getByXidAndStatus($xid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);

	$alert = new Alert($ALERT_TEMPLATE_ID['ANNOUNCEMENT_NEW']);
	$aid = $alert->getAid();
	$alert_variable = new AlertVariable($aid, 'href', $PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$thread->getNid());
	$alert_variable = new AlertVariable($aid, 'xid', $xid);
	
	foreach ($memberslist as $uid => $jointime) {
		$alert_instance = new AlertInstance($aid, $uid, $ALERT_INSTANCE_STATUS['ASYNC']);
	}
	
	$alert_instance = new AlertInstance($aid, $community->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);

	header('Location: '.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$thread->getNid());
	
	exit(0);
}

header('Location: '.$PAGE['NEW_DISCUSSION_THREAD'].'?lid='.$user->getLid().(isset($_REQUEST['xid'])?'?&id='.$_REQUEST['xid']:''));

?>