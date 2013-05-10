<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Insert a new post in an existing discussion thread
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userpaging.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$oid = isset($_REQUEST['oid'])?$_REQUEST['oid']:null;

if (isset($_REQUEST['text']) && isset($_REQUEST['nid'])) {
	switch ($user->getStatus()) {
		case $USER_STATUS['UNREGISTERED']:
			$post_status = $DISCUSSION_POST_STATUS['ANONYMOUS'];
			break;
		case $USER_STATUS['BANNED']:
			$post_status = $DISCUSSION_POST_STATUS['BANNED'];
			break;
		default:
			$post_status = $DISCUSSION_POST_STATUS['POSTED'];
	}
	
	if ($oid !== null && $user->getStatus() == $USER_STATUS['ACTIVE']) {
		$original_post = DiscussionPost::get($oid);
		$original_poster = User::get($original_post->getUid());
	}
		
	$nid = $_REQUEST['nid'];
	
	$text = stripslashes($_REQUEST['text']);
		
	$post = new DiscussionPost($nid, $user->getUid(), $text, $post_status, $oid);
	
	if ($user->getStatus() != $USER_STATUS['UNREGISTERED']) {
		$xid = null;
		try {
			$discussionthread = DiscussionThread::get($nid);
			$xid = $discussionthread->getXid();
			
			if ($oid === null || $original_poster->getUid() != $discussionthread->getUid()) {
				$alert = new Alert($ALERT_TEMPLATE_ID['ANNOUNCEMENT_REPLY']);
				$aid = $alert->getAid();
				
				$href = $PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$discussionthread->getNid();
				
				$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
				$alert_variable = new AlertVariable($aid, 'xid', $xid);
				$alert_variable = new AlertVariable($aid, 'href', $href);
				
				$alert_instance = new AlertInstance($aid, $discussionthread->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
			}
		} catch (DiscussionThreadException $e) {}
		if ($xid === null) {
			Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$user->getUid().'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_DISCUSSION_POST_GENERAL_BOARD"><user_name uid="'.$user->getUid().'"/> wrote a discussion post in the <a href="'.$PAGE['BOARD'].'">General discussion</a> area</translate></div>');
		} else {
			Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$user->getUid().'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_DISCUSSION_POST"><user_name uid="'.$user->getUid().'"/> wrote a discussion post in the <community_name xid="'.$xid.'" link="true"/> community\'s <a href="'.$PAGE['BOARD'].'?xid='.$xid.'">discussion area</a></translate></div>');
		}
	}
	
	$all_posts = array();
	try {
		$all_posts = DiscussionPostList::getByNidAndStatus($post->getNid(), $DISCUSSION_POST_STATUS['POSTED']);
	} catch (DiscussionPostListException $e) {}
	
	if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
		try {
			$anonymous_posts = DiscussionPostList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['ANONYMOUS']);
			foreach ($anonymous_posts as $anonymous_oid => $anonymous_timestamp) {
				$anonymous_post = DiscussionPost::get($anonymous_oid);
				if ($anonymous_post->getNid() == $nid) $all_posts[$anonymous_oid] = $anonymous_timestamp;
			}
		} catch (DiscussionPostListException $e) {}
	}
	
	asort($all_posts);
	
	$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'DISCUSSION_THREAD_POSTS');
	$key = array_search($post->getOid(), array_keys($all_posts));
	if ($key === false) $key = 0;
	$page_offset = ceil(($key + 1) / $amount_per_page);
		
	$href = $PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$_REQUEST['nid'].($page_offset > 1?'&page='.$page_offset:'').'&scrollto=post_'.$post->getOid();
	
	if ($oid !== null && $user->getStatus() == $USER_STATUS['ACTIVE']) {
		$amount_per_page = UserPaging::getPagingValue($original_post->getUid(), 'DISCUSSION_THREAD_POSTS');
		$key = array_search($post->getOid(), array_keys($all_posts));
		if ($key === false) $key = 0;
		$page_offset = ceil(($key + 1) / $amount_per_page);
		
		$key_original = array_search($oid, array_keys($all_posts));
		if ($key_original === false) $key_original = 0;
		$page_offset_original = ceil(($key_original + 1) / $amount_per_page);
		
		$href_reply = $PAGE['DISCUSSION_THREAD'].'?lid='.$original_poster->getLid().'&nid='.$_REQUEST['nid'].($page_offset > 1?'&page='.$page_offset:'').'&scrollto=post_'.$post->getOid();
		$href_original = $PAGE['DISCUSSION_THREAD'].'?lid='.$original_poster->getLid().'&nid='.$_REQUEST['nid'].($page_offset_original> 1?'&page='.$page_offset_original:'').'&scrollto=post_'.$oid;
		
		// No need to send the alert if we're replying to our own post
		if ($user->getUid() != $original_post->getUid()) {
			$alert = new Alert($ALERT_TEMPLATE_ID['REPLY']);
			$aid = $alert->getAid();
			
			$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
			$alert_variable = new AlertVariable($aid, 'href', $href_original);
			$alert_variable = new AlertVariable($aid, 'reply_href', $href_reply);
			$alert_variable = new AlertVariable($aid, 'nid', $_REQUEST['nid']);
			
			$alert_instance = new AlertInstance($aid, $original_post->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
			
			$post->setAid($aid);
		}
	}
	
	header('Location: '.$href);
} else header('Location: '.$PAGE['DISCUSS'].'?lid='.$user->getLid());

?>