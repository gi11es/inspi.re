<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Checks the username and password submitted by the user in the login form
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitylist.php');
require_once(dirname(__FILE__).'/../entities/communitymembership.php');
require_once(dirname(__FILE__).'/../entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvote.php');
require_once(dirname(__FILE__).'/../entities/entryvoteblockedlist.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../entities/themevote.php');
require_once(dirname(__FILE__).'/../entities/themevotelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevel.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$old_user = User::getSessionUser();

// Import all the actions of an anonymous user into his/her real account upon login
function mergeUsers($old_user, $new_user) {
	global $DISCUSSION_THREAD_STATUS;
	global $DISCUSSION_POST_STATUS;
	global $COMMUNITY_STATUS;
	global $COMPETITION_STATUS;
	global $THEME_STATUS;
	global $THEME_VOTE_STATUS;
	global $ENTRY_STATUS;
	global $ENTRY_VOTE_STATUS;
	global $POINTS_VALUE;
	global $ALERT_TEMPLATE_ID;
	global $ALERT_INSTANCE_STATUS;
	global $PAGE;
	global $POINTS_VALUE_ID;
	
	$old_uid = $old_user->getUid();
	$new_uid = $new_user->getUid();
	
	$threads = DiscussionThreadList::getByUidAndStatus($old_uid, $DISCUSSION_THREAD_STATUS['ANONYMOUS']);
	foreach ($threads as $nid => $timestamp) {
		$thread = DiscussionThread::get($nid);
		$thread->setUid($new_uid);
		$thread->setStatus($DISCUSSION_THREAD_STATUS['ACTIVE']);
	}
	
	$posts = DiscussionPostList::getByUidAndStatus($old_uid, $DISCUSSION_POST_STATUS['ANONYMOUS']);
	foreach ($posts as $oid => $timestamp) {
		$post = DiscussionPost::get($oid);
		$thread = DiscussionThread::get($post->getNid());
		$post->setUid($new_uid);
		$post->setStatus($DISCUSSION_POST_STATUS['POSTED']);
		
		if ($thread->getEid() !== null) {
			$entry = Entry::get($thread->getEid());
			if ($new_uid != $entry->getUid()) {
				$entry_user = User::get($entry->getUid());
				$competition = Competition::get($entry->getCid());
				$alert = new Alert($ALERT_TEMPLATE_ID['COMMENT']);
				$aid = $alert->getAid();
				
				$alert_variable = new AlertVariable($aid, 'uid', $new_uid);
				$alert_variable = new AlertVariable($aid, 'href', $PAGE['ENTRY'].'?lid='.$entry_user->getLid().'&home=true#eid='.$entry->getEid());
				$alert_variable = new AlertVariable($aid, 'comment_href', $PAGE['ENTRY'].'?lid='.$entry_user->getLid().'&home=true#eid='.$entry->getEid());
				$alert_variable = new AlertVariable($aid, 'tid', $competition->getTid());
				
				$alert_instance = new AlertInstance($aid, $entry->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);
				
				$post->setAid($aid);
			}
		} elseif ($post->getReplyToOid() !== null) {
			$all_posts = array();
			try {
				$all_posts = DiscussionPostList::getByNidAndStatus($post->getNid(), $DISCUSSION_POST_STATUS['POSTED']);
			} catch (DiscussionPostListException $e) {}
			
			$original_post = DiscussionPost::get($post->getReplyToOid());
			$original_poster = User::get($original_post->getUid());
		
			$amount_per_page = UserPaging::getPagingValue($original_post->getUid(), 'DISCUSSION_THREAD_POSTS');
			$key = array_search($post->getOid(), array_keys($all_posts));
			if ($key === false) $key = 0;
			$page_offset = ceil(($key + 1) / $amount_per_page);
			
			$key_original = array_search($post->getReplyToOid(), array_keys($all_posts));
			if ($key_original === false) $key_original = 0;
			$page_offset_original = ceil(($key_original + 1) / $amount_per_page);
			
			$href = $PAGE['DISCUSSION_THREAD'].'?lid='.$original_poster->getLid().'&nid='.$post->getNid().($page_offset > 1?'&page='.$page_offset:'').'&scrollto=post_'.$post->getOid();
			$href_original = $PAGE['DISCUSSION_THREAD'].'?lid='.$original_poster->getLid().'&nid='.$post->getNid().($page_offset_original> 1?'&page='.$page_offset_original:'').'&scrollto=post_'.$post->getReplyToOid();

			
			// No need to send the alert if we're replying to our own post
			if ($new_uid != $original_post->getUid()) {
				$alert = new Alert($ALERT_TEMPLATE_ID['REPLY']);
				$aid = $alert->getAid();
				
				$alert_variable = new AlertVariable($aid, 'uid', $new_uid);
				$alert_variable = new AlertVariable($aid, 'href', $href_original);
				$alert_variable = new AlertVariable($aid, 'reply_href', $href);
				$alert_variable = new AlertVariable($aid, 'nid', $post->getNid());
				
				$alert_instance = new AlertInstance($aid, $original_post->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);
				
				$post->setAid($aid);
			}
		}
	}
	
	$memberships = CommunityMembershipList::getByUid($old_uid);
	foreach ($memberships as $xid => $join_time) {
		$membership = CommunityMembership::get($xid, $old_uid);
		try {
			$existing_membership = CommunityMembership::get($xid, $new_uid);
		} catch (CommunityMembershipException $e) {
			$membership->delete();
		}
	}
	
	$theme_votes = ThemeVoteList::getByUidAndStatus($old_uid, $THEME_VOTE_STATUS['ANONYMOUS']);
	foreach ($theme_votes as $tid => $points) {
		try {
			// If the merged vote should overwrite an old one, we need to delete the old vote first
			$old_theme_vote = ThemeVote::get($tid, $new_uid);
			$old_theme_vote->delete();
		} catch (ThemeVoteException $e) {
			// If this is a new vote, we have to add it to the user's points
			$pointsvalue = PointsValue::get($POINTS_VALUE_ID['THEME_VOTING']);
			$points_theme_vote = $pointsvalue->getValue();
			
			$new_user->givePoints($points_theme_vote);
		}
		$theme_vote = ThemeVote::get($tid, $old_uid);
		$theme_vote->setUid($new_uid);
		$theme_vote->setStatus($THEME_VOTE_STATUS['CAST']);
		
		$theme = Theme::get($tid);
		try {
			$community = Community::get($theme->getXid());
			if ($theme->getScore($new_user) < $community->getThemeMinimumScore())
				$theme->setStatus($THEME_STATUS['DELETED']);
		} catch (CommunityException $e) {}
	}
	
	$entry_votes = EntryVoteList::getByUidAndStatus($old_uid, $ENTRY_VOTE_STATUS['ANONYMOUS'], false);
	if (!$new_user->isVotingBlocked()) foreach ($entry_votes as $eid => $array) {
		$points = $array['points'];
		
		try {
			// If the merged vote should overwrite an old one, we need to delete the old vote first
			$old_entry_vote = EntryVote::get($eid, $new_uid);
			$old_entry_vote->delete();
		} catch (EntryVoteException $e) {
			// If this is a new vote, we have to add it to the user's points
			$pointsvalue = PointsValue::get($POINTS_VALUE_ID['ENTRY_VOTING']);
			$points_entry_vote = $pointsvalue->getValue();
			
			$new_user->givePoints($points_entry_vote);
		}
		$entry_vote = EntryVote::get($eid, $old_uid);
		$entry_vote->setUid($new_uid);
		
		$entry = Entry::get($eid);
		$competition = Competition::get($entry->getCid());
		if ($competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
			switch ($new_user->getStatus()) {
				case $USER_STATUS['UNREGISTERED']:
					$status = $ENTRY_VOTE_STATUS['ANONYMOUS'];
					break;
				case $USER_STATUS['BANNED']:
					$status = $ENTRY_VOTE_STATUS['BANNED'];
					break;
				default:
					$status = $ENTRY_VOTE_STATUS['CAST'];
			}
			
			$blocklist = EntryVoteBlockedList::getByVoterUid($new_uid);
			
			// If the user registered less than 24 hours ago or has this relationship ion the voting
			// block list, we block this vote (it won't add to the author's total)
			if (in_array($entry->getUid(), $blocklist) || $user->getCreationTime() > time() - 86400)
				$status = $ENTRY_VOTE_STATUS['BLOCKED'];
				
			$entry_vote->setStatus($status);
		} else
			$entry_vote->delete();
	}
	
	$communities = CommunityList::getByUidAndStatus($old_uid, $COMMUNITY_STATUS['ANONYMOUS']);
	foreach ($communities as $xid) {
		try {
			$community = Community::get($xid);
			$new_user->givePoints(-$community->getDeletionPoints());
			$community->setUid($new_uid);
			$community->setStatus($COMMUNITY_STATUS['ACTIVE']);
		} catch (UserException $e) {
			// Failed to import action due to lack of funds
		} 
	}
	
	$themes = ThemeList::getByUidAndStatus($old_uid, $THEME_STATUS['ANONYMOUS']);
	foreach ($themes as $tid => $xid) {
		try {
			$theme = Theme::get($tid);
			$new_user->givePoints(-$theme->getDeletionPoints());
			$theme->setUid($new_uid);
			$theme->setStatus($THEME_STATUS['SUGGESTED']);
		} catch (UserException $e) {
			// Failed to import action due to lack of funds
		}
	}
	
	$entries = EntryList::getByUidAndStatus($old_uid, $ENTRY_STATUS['ANONYMOUS']);
	foreach ($entries as $cid =>$eid) {
		$entrylist = EntryList::getByUidAndCidAndStatus($new_uid, $cid, $ENTRY_STATUS['POSTED']);
		if (empty($entrylist)) try {
			$entry = Entry::get($eid);
			$new_user->givePoints(-$entry->getDeletionPoints());
			$entry->setUid($new_uid);
			$competition = Competition::get($entry->getCid());
			if ($competition->getStatus() == $COMPETITION_STATUS['OPEN'])
				$entry->setStatus($ENTRY_STATUS['POSTED']);
			else
				$entry->setStatus($ENTRY_STATUS['DELETED']);
		} catch (UserException $e) {
			// Failed to import action due to lack of funds
		}
	}
	
	$old_user->delete();
}

if (isset($_REQUEST['login_user_name']) && isset($_REQUEST['login_password'])) {
	try {
		$user = User::getByEmail(trim(mb_strtolower($_REQUEST['login_user_name'])));
		if ($user->isPassword($_REQUEST['login_password'])) {
			$user->setSessionUser();
			if (isset($_REQUEST['login_remember']) && strcmp($_REQUEST['login_remember'], 'on') == 0)
				$user->StayLoggedIn();

			if ($old_user->getStatus() == $USER_STATUS['UNREGISTERED'] && $old_user->getEmail() === null && $user->getStatus() != $USER_STATUS['UNREGISTERED'])
				mergeUsers($old_user, $user);
				
			if (strcmp($user->getUid(), '820941') != 0) // Pingdom
				Log::xmpp('USER_ON', '<profile_picture class="member_thumbnail" uid="'.$user->getUid().'" size="small" id="user_'.$user->getUid().'" style="hidden"/>');
				
			// Check if the member was MIA and give premium membership to whoever brought him/her back
				
			$levels = UserLevelList::getByUid($user->getUid());
			if (in_array($USER_LEVEL['MIA'], $levels)) try {
				if ($user->getAffiliateUid() !== null && in_array($USER_LEVEL['MIA_APPEALED'], $levels)) try {
					$affiliate_uid = $user->getAffiliateUid();
					$affiliate = User::get($affiliate_uid);
				
					$referencetime = max(time(), $affiliate->getPremiumTime());
					$affiliate->setPremiumTime($referencetime + 86400);
				
					$alert = new Alert($ALERT_TEMPLATE_ID['AFFILIATE_MIA']);
					$aid = $alert->getAid();
					
					$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
					$alert_instance = new AlertInstance($aid, $affiliate_uid, $ALERT_INSTANCE_STATUS['ASYNC']);
					
					$user->setAffiliateUid(null);
				} catch (UserException $g) {}
				
				$userlevel = UserLevel::get($user->getUid(), $USER_LEVEL['MIA']);
				$userlevel->delete();
			} catch (UserLevelException $f) {}
			
			if (in_array($USER_LEVEL['MIA_APPEALED'], $levels)) try {
				$userlevel = UserLevel::get($user->getUid(), $USER_LEVEL['MIA_APPEALED']);
				$userlevel->delete();
			} catch (UserLevelException $h) {}
			
			$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:$PAGE['INDEX'];

			$location = $PAGE['HOME'].'?lid='.$user->getLid();
			
			if ($referer) foreach ($PAGE as $key => $url) {
				if (strstr($referer, $url) && !in_array($key, $REDIRECT_BLACKLIST))
					$location = $referer;
			}
			
			header('Location: '.$location);
			
		} else header('Location: '.$PAGE['LOST_PASSWORD'].'?wrongdata=true');
	} catch (UserException $e) {
		header('Location: '.$PAGE['LOST_PASSWORD'].'?wrongdata=true');
	}
} else header('Location: '.$PAGE['LOST_PASSWORD'].'?wrongdata=true');

?>