<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Delete an existing post from a discussion thread
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userpaging.php');
require_once(dirname(__FILE__).'/../utilities/string.php');

$user = User::getSessionUser();

if (isset($_REQUEST['oid'])) {
	$post = DiscussionPost::get($_REQUEST['oid']);
	
	if ($post->getUid() != $user->getUid()) {
		header('Location: '.$PAGE['DISCUSS'].'?lid='.$user->getLid());
		exit(0);
	}
	
	$all_posts = array();
	try {
		$all_posts = DiscussionPostList::getByNidAndStatus($post->getNid(), $DISCUSSION_POST_STATUS['POSTED']);
	} catch (DiscussionPostListException $e) {
	}
	
	if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
		try {
			$anonymous_posts = DiscussionPostList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['ANONYMOUS']);
			foreach ($anonymous_posts as $anonymous_oid => $anonymous_timestamp) {
				$anonymous_post = DiscussionPost::get($anonymous_oid);
				if ($anonymous_post->getNid() == $post->getNid())
					$all_posts[$anonymous_oid] = $anonymous_timestamp;
			}
		} catch (DiscussionPostListException $e) {}
	}
	
	asort($all_posts);
	
	$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'DISCUSSION_THREAD_POSTS');
	$key = array_search($post->getOid(), array_keys($all_posts));
	if ($key === false) $key = 0;
	$page_offset = ceil(($key) / $amount_per_page); // offset of the previous element
	
	$post->setStatus($DISCUSSION_POST_STATUS['DELETED']);
	if ($post->getAid() !== null) {
		$alert = Alert::get($post->getAid());
		$alert->delete();
	}
	
	if ($post->getReplyAid() !== null) try {
		$alert = Alert::get($post->getReplyAid());
		$alert->delete();
	} catch (AlertException $e) {}
	
	$all_posts = array_diff($all_posts, array($post->getOid() => $post->getCreationTime()));
	
	if (empty($all_posts)) {
		$thread = DiscussionThread::get($post->getNid());
		$thread->setStatus($DISCUSSION_THREAD_STATUS['DELETED']);
		header('Location: '.$PAGE['BOARD'].'?lid='.$user->getLid().($thread->getXid() === null?'':'&xid='.$thread->getXid()));
	} else {
		header('Location: '.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$post->getNid().($page_offset > 1?'&page='.$page_offset:''));
	}
} else header('Location: '.$PAGE['DISCUSS'].'?lid='.$user->getLid());

?>