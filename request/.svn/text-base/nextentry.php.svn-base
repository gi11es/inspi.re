<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Provides information about the next entry in the given competition
*/

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrycommentnotification.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvote.php');
require_once(dirname(__FILE__).'/../entities/favoritelist.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/picturefile.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../utilities/inml.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

function nextEntry($entrylist, $eid) {
	if (count($entrylist) == 1)
		return Entry::get($eid);
		
	$next_eid = null;
	$grabnext = false;
	
	foreach ($entrylist as $uid => $entry_eid) {
		if ($grabnext) {
			$next_eid = $entry_eid;
			break;
		}
		
		if ($entry_eid == $eid) $grabnext = true;
	}
	
	if ($next_eid === null)
		$next_eid = array_shift(array_values($entrylist));
		
	try {
		$entry = Entry::get($next_eid);
		return $entry;
	} catch (EntryException $e) {
		return nextEntry($entrylist, $next_eid);
	}
}

function nextSortedEntry($sortedentries, $entries, $eid) {
	if (count($sortedentries) == 1)
		return $entries[$eid];
		
	$next_eid = null;
	$grabnext = false;
	
	foreach ($sortedentries as $entry_eid => $creation_time) {
		if ($grabnext) {
			$next_eid = $entry_eid;
			break;
		}
		
		if ($entry_eid == $eid) $grabnext = true;
	}
	
	if ($next_eid === null)
		$next_eid = array_shift(array_keys($sortedentries));
		
	if (isset($entries[$next_eid]))
		return $entries[$next_eid];
	else
		return nextSortedEntry($sortedentries, $entries, $next_eid);
}

$user = User::getSessionUser();
$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$uid = $user->getUid();
$entry = null;
$hashtype = -1;

if (isset($_REQUEST['hash'])) try {
	$vars = explode('=', $_REQUEST['hash']);
	if (isset($vars[0]) && isset($vars[1])) {
		if (strcasecmp($vars[0], 'eid') == 0) {
			$entry = Entry::get($vars[1]);
			$hashtype = 0;
		} elseif (strcasecmp($vars[0], 'token') == 0) {
			$token = Token::get($vars[1]);
			$exploded = explode('-', $token);
			if (count($exploded) == 2) {
				$token_uid = $exploded[0];
				$eid = $exploded[1];
				if ($token_uid == $user->getUid() || $user->getUid() == $GOOGLE_UID)
					$entry = Entry::get($eid);
			}
			$hashtype = 1;
		} elseif (strcasecmp($vars[0], 'persistenttoken') == 0) {
			$token = PersistentToken::get($vars[1]);
			$exploded = explode('-', $token);
			if (count($exploded) == 2) {
				$token_uid = $exploded[0];
				$eid = $exploded[1];
				if ($token_uid == $user->getUid() || $user->getUid() == $GOOGLE_UID)
					$entry = Entry::get($eid);
			}
			$hashtype = 2;
		}
	}
} catch (EntryException $e) {} catch (TokenException $f) {} catch (PersistentTokenException $g) {}


if ($entry !== null) {
	$eid = $entry->getEid();
	$cid = $entry->getCid();
	
	$competition = Competition::get($cid);
	$community = Community::get($competition->getXid());
	$theme = Theme::get($competition->getTid());
	
	$moderatorlist = CommunityModeratorList::getByXid($competition->getXid());
	$ismoderator = in_array($user->getUid(), $moderatorlist);
	
	$entrylist = EntryList::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['POSTED']);
	$entrylist = array_merge($entrylist, EntryList::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['DELETED']));
	if ($ismoderator) $entrylist = array_merge($entrylist, EntryList::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['DISQUALIFIED']));
	
	if ($user->getStatus() == $USER_STATUS['BANNED'])
		$entrylist = array_merge($entrylist, EntryList::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['BANNED']));
	
	if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
		$ownentries = EntryList::getByUidAndCidAndStatus($uid, $cid, $ENTRY_STATUS['ANONYMOUS']);
		if (!empty($ownentries))
			$entrylist[$uid] = array_shift($ownentries);
	} else $user->setLastActivity();
	
	if ($uid == $community->getUid() || $ismoderator) {
		$entries = array();
		$sortedentries = array();
		foreach ($entrylist as $uid => $entry_eid) try {
			$entries[$entry_eid] = Entry::get($entry_eid);
			$sortedentries[$entry_eid] = $entries[$entry_eid]->getCreationTime();
		} catch (EntryException $e) {}
		asort($sortedentries);
		$entry = nextSortedEntry($sortedentries, $entries, $eid);
	} elseif ($competition->getStatus() == $COMPETITION_STATUS['CLOSED']) {
		$entries = array();
		$sortedentries = array();
		
		foreach ($entrylist as $uid => $entry_eid) try {
			$entries[$entry_eid] = Entry::get($entry_eid);
			try {
				$author = User::get($entries[$entry_eid]->getUid());
			} catch (UserException $e) {
				$author = null;
			}
			$rank = $entries[$entry_eid]->getRank();
			
			if ($author == null || !$author->getDisplayRank() && $rank > 3)
				$rank = 9999;
				
			$sortedentries[$entry_eid] = $rank;
		} catch (EntryException $e) {}
		asort($sortedentries);
		$entry = nextSortedEntry($sortedentries, $entries, $eid);
	} else $entry = nextEntry($entrylist, $eid);
	
	$entry_info = array();
	
	try {
		$picture = Picture::get($entry->getPid());
		$entry_info['src'] = $picture->getRealThumbnail($PICTURE_SIZE['HUGE']);
		$entry_info['exif'] = UI::RenderExif($competition, $user, $picture->getExif(), true);
		$fid = $picture->getFid($PICTURE_SIZE['HUGE']);
		$picturefile = PictureFile::get($fid);
		$entry_info['width'] = $picturefile->getWidth();
	} catch (PictureException $e) {
		$entry_info['src'] = $GRAPHICS_PATH.'default-community-picture-big.png';
		$entry_info['exif'] = I18N::translateHTML($user, '<translate id="ENTRY_DELETED">This entry has been deleted</translate>');
		$entry_info['width'] = 256;
	}
	
	$entry_info['disqualified'] = ($entry->getStatus() == $ENTRY_STATUS['DISQUALIFIED']);
	$entry_info['competition_status'] = $competition->getStatus();
	
	switch ($hashtype) {
		case 0: 
			$entry_info['hash'] = 'eid='.$entry->getEid();
			break;
		case 1:
			$token = new Token($user->getUid().'-'.$entry->getEid());
			$entry_info['hash'] = 'token='.$token->getHash();
			break;
		case 2:
			$persistenttoken = new PersistentToken($user->getUid().'-'.$entry->getEid());
			$entry_info['hash'] = 'persistenttoken='.$persistenttoken->getHash();
			break;
	}
	
	$entry_info['uid'] = $entry->getUid();
	$entry_info['comments'] = UI::RenderCommentThread($user, $entry, true);
	$entry_info['comments_header'] = UI::RenderCommentThreadHeader($user, $entry, true);
	$entry_info['favorite'] = in_array($entry->getEid(), array_keys(FavoriteList::getByUid($user->getUid())));
	
	$entry_info['home_title'] = html_entity_decode(INML::processHTML($user, I18N::translateHTML($user, '<translate id="ENTRY_PAGE_TITLE_HOME">Your entry in the "<string value="'.String::fromaform($theme->getTitle()).'"/>" competition of the <string value="'.String::fromaform($community->getName()).'"/> community on inspi.re</translate>')), ENT_COMPAT, 'UTF-8');
	
	if ($ispremium && $user->getUid() == $entry->getUid()) {
		if ($competition->getStatus() == $COMPETITION_STATUS['VOTING'])
			$creation_time_limit = time() - 7200;
		else
			$creation_time_limit = null;
		$entry_info['vote_repartition'] = UI::RenderVoteRepartition($user, $entry->getEid(), $creation_time_limit, true);
	} else
		$entry_info['vote_repartition'] = '<div id="vote_repartition"></div>';
	
	if ($competition->getStatus() == $COMPETITION_STATUS['CLOSED']) {
		try {
			$author = User::get($entry->getUid());
			$author_name = $author->getUniqueName();
		} catch (UserException $e) {
			$author = null;
			$author_name = '?';
		}
		
		$amount = $competition->getEntriesCount();
		
		if ($author !== null && $author->getStatus() == $USER_STATUS['BANNED']) {
			$rank = $entry->getBannedRank();
			if ($rank > $amount) $amount = $rank;
		} else $rank = $entry->getRank();
	
		$entry_info['author'] = UI::RenderEntryAuthor($user, $entry->getUid(), $rank, $amount, true);


		$entry_info['title'] = html_entity_decode(INML::processHTML($user, I18N::translateHTML($user, '<translate id="ENTRY_PAGE_TITLE_HOF"><string value="'.String::fromaform($author_name).'"/>\'s entry in the "<string value="'.String::fromaform($theme->getTitle()).'"/>" competition of the <string value="'.String::fromaform($community->getName()).'"/> community on inspi.re</translate>')), ENT_COMPAT, 'UTF-8');
	} elseif ($competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
		$entry_info['title'] = html_entity_decode(INML::processHTML($user, I18N::translateHTML($user, '<translate id="ENTRY_PAGE_TITLE_VOTE">Vote on the "<string value="'.String::fromaform($theme->getTitle()).'"/>" competition of the <string value="'.String::fromaform($community->getName()).'"/> community on inspi.re</translate>')), ENT_COMPAT, 'UTF-8');
	} else $entry_info['title'] = html_entity_decode(INML::processHTML($user, I18N::translateHTML($user, '<translate id="ENTRY_PAGE_TITLE_OPEN">Entries in the "<string value="'.String::fromaform($theme->getTitle()).'"/>" competition of the <string value="'.String::fromaform($community->getName()).'"/> community on inspi.re</translate>')), ENT_COMPAT, 'UTF-8');
	
	try {
		$vote = EntryVote::get($entry->getEid(), $user->getUid());
		$points = $vote->getPoints();
	} catch (EntryVoteException $e) {
		$points = 0;
	}

	$entry_info['points'] = $points;
	
	try {
		$author = User::get($entry->getUid());
		$entry_info['purchaseable'] = ($author->getAllowSales() || $user->getUid() == $entry->getUid());
	} catch (UserException $e) {
		
		$entry_info['purchaseable'] = false;
	}
	
	$entry_info['big_commentator'] = false;
		
	if ($user->getStatus() != $USER_STATUS['UNREGISTERED']) try {
		$userlevellist = UserLevelList::getByUid($entry->getUid());
		$entry_info['big_commentator'] = in_array($USER_LEVEL['BIG_COMMENTATOR'], $userlevellist);
	} catch (UserLevelListException $e) {}	
	
	try {
		$entrycommentnotification = EntryCommentNotification::get($entry->getEid(), $user->getUid());
		$entry_info['entry_comment_notification'] = true;
	} catch (EntryCommentNotificationException $e) {
		$entry_info['entry_comment_notification'] = false;
	}
	
	echo json_encode($entry_info);
}

?>