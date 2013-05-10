<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Insert a new comment on an entry
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrycommentnotification.php');
require_once(dirname(__FILE__).'/../entities/entrycommentnotificationlist.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();
$entry = null;

if (isset($_REQUEST['hash'])) try {
	$vars = explode('=', $_REQUEST['hash']);
	if (isset($vars[0]) && isset($vars[1])) {
		if (strcasecmp($vars[0], 'eid') == 0) {
			$entry = Entry::get($vars[1]);
		} elseif (strcasecmp($vars[0], 'token') == 0) {
			$token = Token::get($vars[1]);
			$exploded = explode('-', $token);
			if (count($exploded) == 2) {
				$token_uid = $exploded[0];
				$eid = $exploded[1];
				if ($token_uid == $user->getUid() || $user->getUid() == $GOOGLE_UID)
					$entry = Entry::get($eid);
			}
		} elseif (strcasecmp($vars[0], 'persistenttoken') == 0) {
			$token = PersistentToken::get($vars[1]);
			$exploded = explode('-', $token);
			if (count($exploded) == 2) {
				$token_uid = $exploded[0];
				$eid = $exploded[1];
				if ($token_uid == $user->getUid() || $user->getUid() == $GOOGLE_UID)
					$entry = Entry::get($eid);
			}
		}
	}
} catch (EntryException $e) {} catch (TokenException $f) {} catch (PersistentTokenException $g) {}

if (isset($_REQUEST['eid'])) {
	try {
		$entry = Entry::get($_REQUEST['eid']);
	} catch (EntryException $e) {}
}

$result = array();

if ($entry !== null && isset($_REQUEST['text'])) {
	$eid = $entry->getEid();
	$thread = $entry->getDiscussionThread();
	
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
		
	$nid = $thread->getNid();
	
	$text = stripslashes($_REQUEST['text']);
		
	$post = new DiscussionPost($nid, $user->getUid(), $text, $post_status);
	
	$competition = Competition::get($entry->getCid());
	
	$result['hash'] = $_REQUEST['hash'];
	$result['status'] = 1;
	$result['comments'] = UI::RenderCommentThread($user, $entry, true, $post->getOid());
	$result['comments_header'] = UI::RenderCommentThreadHeader($user, $entry, true);
	
	$entry_user = User::get($entry->getUid());
	
	if ($user->getUid() != $entry->getUid()) 
		$entry_user->incrementCommentsReceived();
	
	if ($user->getUid() != $entry->getUid() || $competition->getStatus() == $COMPETITION_STATUS['CLOSED'])
			Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$user->getUid().'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_COMMENT"><user_name uid="'.$user->getUid().'"/> wrote a comment on an entry in the <theme_title href="'.$PAGE['GRID'].'?cid='.$competition->getCid().'" tid="'.$competition->getTid().'"/> competition of the <community_name xid="'.$competition->getXid().'" link="true"/> community</translate></div>');
	
	if ($user->getUid() != $entry->getUid() && $user->getStatus() == $USER_STATUS['ACTIVE']) {
		$alert = new Alert($ALERT_TEMPLATE_ID['COMMENT']);
		$aid = $alert->getAid();
		
		$persistenttoken = new PersistentToken($entry->getUid().'-'.$eid);
		
		$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
		$alert_variable = new AlertVariable($aid, 'href', $PAGE['ENTRY'].'?lid='.$entry_user->getLid().'&home=true#persistenttoken='.$persistenttoken->getHash());
		$alert_variable = new AlertVariable($aid, 'comment_href', $PAGE['ENTRY'].'?lid='.$entry_user->getLid().'&home=true#persistenttoken='.$persistenttoken->getHash());
		$alert_variable = new AlertVariable($aid, 'tid', $competition->getTid());
		
		$alert_instance = new AlertInstance($aid, $entry->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
		
		$post->setAid($aid);
	}
	
	$subscribedcommentators = EntryCommentNotificationList::getByEid($eid);
	
	if (!in_array($user->getUid(), $subscribedcommentators) && $user->getUid() != $entry->getUid()) {
		$entrycommentnotification = new EntryCommentNotification($eid, $user->getUid());
	}
} else $result['status'] = 0;

echo json_encode($result);

?>