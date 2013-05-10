<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Cast a vote on a competition entry
*/

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entryvote.php');
require_once(dirname(__FILE__).'/../entities/entryvoteblockedlist.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/pointsvalue.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();
$uid = $user->getUid();

$points = isset($_REQUEST['points'])?intval($_REQUEST['points']):null;

$result = array();

if ($points < 0) $points = 0; elseif ($points > 5) $points = 5;

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['ENTRY_VOTING']);
$points_entry_voting = $pointsvalue->getValue();

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

if ($points == 0 && $entry !== null) {
	$competition = Competition::get($entry->getCid());
	
	if (!$user->isVotingBlocked() && $uid != $entry->getUid() && $competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
		try {
			try {
				$vote = EntryVote::get($entry->getEid(), $uid);
				try {
					$user->givePoints($vote->getDeletionPoints());
					$vote->delete();
				} catch (UserException $e) {}
				
			} catch (EntryVoteException $e) {}
			$result['status'] = 1;
			$result['points'] = $points;
			$result['hash'] = $_REQUEST['hash']; 
		} catch (UserException $e) {
			$result['status'] = 0;
		}
	} else $result['status'] = 0;
} else if ($entry !== null && $points !== null) {
	$competition = Competition::get($entry->getCid());
	
	if (!$user->isVotingBlocked() && $uid != $entry->getUid() && $competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
		try {
			$vote = EntryVote::get($entry->getEid(), $uid);
			$vote->setPoints($points);
			
			if ($user->getStatus() == $USER_STATUS['ACTIVE'])
				Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$uid.'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_ENTRY_VOTE"><user_name uid="'.$uid.'"/> voted for an entry in the <theme_title href="'.$PAGE['GRID'].'?cid='.$competition->getCid().'" tid="'.$competition->getTid().'"/> competition of the <community_name xid="'.$competition->getXid().'" link="true"/> community</translate></div>');
			
			if ($user->updateLastVote($points)) {
				$result['status'] = 3;
				$user->blockVoting(86400); // Block voting for 24 hours
			} else
				$result['status'] = 1;
		} catch (EntryVoteException $e) {
			switch ($user->getStatus()) {
				case $USER_STATUS['UNREGISTERED']:
					$status = $ENTRY_VOTE_STATUS['ANONYMOUS'];
					break;
				case $USER_STATUS['BANNED']:
					$status = $ENTRY_VOTE_STATUS['BANNED'];
					break;
				default:
					$status = $ENTRY_VOTE_STATUS['CAST'];
			}
			
			$blocklist = EntryVoteBlockedList::getByVoterUid($uid);
			
			// If the user registered less than 24 hours ago or has this relationship ion the voting
			// block list, we block this vote (it won't add to the author's total)
			if (in_array($entry->getUid(), $blocklist) || $user->getCreationTime() > time() - 86400)
				$status = $ENTRY_VOTE_STATUS['BLOCKED'];
			
			$vote = new EntryVote($entry->getEid(), $entry->getCid(), $entry->getUid(), $uid, $points, $status, -$points_entry_voting);

			if ($user->getStatus() == $USER_STATUS['ACTIVE']) {
				Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$uid.'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_ENTRY_VOTE"><user_name uid="'.$uid.'"/> voted for an entry in the <theme_title href="'.$PAGE['GRID'].'?cid='.$competition->getCid().'" tid="'.$competition->getTid().'"/> competition of the <community_name xid="'.$competition->getXid().'" link="true"/> community</translate></div>');
			}
			
			// Count vote for speed check
			if (isset($_REQUEST['time']) && $user->voteSpeedCheck($_REQUEST['time'])) {
				$result['status'] = 2;
				$user->blockVoting(7200); // Block voting for 2 hours
			} elseif ($user->addLastVote($points)) {
				$result['status'] = 3;
				$user->blockVoting(86400); // Block voting for 24 hours
			} else $result['status'] = 1;
			
			$user->givePoints($points_entry_voting);
		}
		
		$result['points'] = $points;
		$result['hash'] = $_REQUEST['hash'];
	} else $result['status'] = 0;
} else $result['status'] = 0;

echo json_encode($result);

?>