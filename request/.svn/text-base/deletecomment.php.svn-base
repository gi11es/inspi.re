<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Insert a new comment on an entry
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();

$oid = isset($_REQUEST['oid'])?$_REQUEST['oid']:null;

if ($oid !== null) {
	$discussionpost = DiscussionPost::get($oid);
	if ($discussionpost->getUid() == $user->getUid()) {
		$discussionpost->setStatus($DISCUSSION_POST_STATUS['DELETED']);
		if ($discussionpost->getAid() !== null) try {
			$alert = Alert::get($discussionpost->getAid());
			$alert->delete();
		} catch (AlertException $e) {}
		
		if ($discussionpost->getReplyAid() !== null) try {
			$alert = Alert::get($discussionpost->getReplyAid());
			$alert->delete();
		} catch (AlertException $e) {}
	}
	
	$thread = DiscussionThread::get($discussionpost->getNid());
	
	$entry = Entry::get($thread->getEid());
	$competition = Competition::get($entry->getCid());
	
	$token = new Token($user->getUid().'-'.$entry->getEid());
	
	if ($user->getUid() != $entry->getUid()) {
		$entry_user = User::get($entry->getUid());
		$entry_user->decrementCommentsReceived();
	}
	
	if ($competition->getStatus() == $COMPETITION_STATUS['CLOSED'])
		header('Location: '.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$thread->getEid());
	else
		header('Location: '.$PAGE['ENTRY'].'?lid='.$user->getLid().'#token='.$token->getHash());
} else header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());

?>