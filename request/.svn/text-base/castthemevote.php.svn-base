<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Cast a vote on a competitions theme
*/

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/pointsvalue.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../entities/themevote.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userpaging.php');
require_once(dirname(__FILE__).'/../utilities/inml.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$tid = isset($_REQUEST['tid'])?$_REQUEST['tid']:null;
$page_offset = isset($_REQUEST['page_offset'])?$_REQUEST['page_offset']:null;
$points = isset($_REQUEST['points'])?$_REQUEST['points']:null;
if ($points < 0) $points = -1; else $points = 1;

if ($tid !== null) {
	$theme = Theme::get($tid);
	
	// Self-votes are not permitted
	if ($theme->getUid() != $user->getUid()) {
		$xid = $theme->getXid();
		
		try {
			$vote = ThemeVote::get($tid, $user->getUid());
			$vote->setPoints($points);
			if ($user->getStatus() == $USER_STATUS['ACTIVE'])
				Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$user->getUid().'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_THEME_VOTE"><user_name uid="'.$user->getUid().'"/> voted for or against a theme in the <community_name xid="'.$xid.'" link="true"/> community</translate></div>');

		} catch (ThemeVoteException $e) {
			switch ($user->getStatus()) {
				case $USER_STATUS['UNREGISTERED']:
					$status = $THEME_VOTE_STATUS['ANONYMOUS'];
					break;
				case $USER_STATUS['BANNED']:
					$status = $THEME_VOTE_STATUS['BANNED'];
					break;
				default:
					$status = $THEME_VOTE_STATUS['CAST'];
			}
			
			$pointsvalue = PointsValue::get($POINTS_VALUE_ID['THEME_VOTING']);
			$points_theme_vote = $pointsvalue->getValue();
			
			$vote = new ThemeVote($tid, $user->getUid(), $points, $status);
			
			if ($user->getStatus() == $USER_STATUS['ACTIVE'])
				Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$user->getUid().'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_THEME_VOTE"><user_name uid="'.$user->getUid().'"/> voted for or against a theme in the <community_name xid="'.$xid.'" link="true"/> community</translate></div>');
			
			$user->givePoints($points_theme_vote);
		}
		
		
		$community = Community::get($theme->getXid());
		
		try {
			$theme_user = User::get($theme->getUid());
		} catch (UserException $e) {
			$theme_user = null;
		}
					
		if ($user->getStatus() != $USER_STATUS['UNREGISTERED'] && $theme->getScore($user) < $community->getThemeMinimumScore()) {
			if ($theme_user !== null) $theme_user->givePoints($theme->getDeletionPoints());
			$theme->setStatus($THEME_STATUS['DELETED']);
		} elseif ($user->getStatus() != $USER_STATUS['UNREGISTERED'] && $theme->getScore($user) < -10) {
			if ($theme_user !== null) $theme_user->givePoints($theme->getDeletionPoints());
			$theme->setStatus($THEME_STATUS['DELETED']);
		}
		
		$themelist = ThemeList::getByXidAndStatus($theme->getXid(), $THEME_STATUS['SUGGESTED']);
		
		if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
			foreach (ThemeList::getByUidAndStatus($user->getUid(), $THEME_STATUS['ANONYMOUS']) as $tid => $local_xid)
				if ($local_xid == $xid) $themelist []= $tid;
		}
			
		$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'THEME_LIST_THEMES');
		$page_count = ceil(count($themelist) / $amount_per_page);
		
		$scores = array();
		$themes = array();
		
		foreach ($themelist as $tid) {
			$themes[$tid] = Theme::get($tid);
			$scores[$tid] = $themes[$tid]->getScore($user);
		}
		
		arsort($scores);
		
		$scores = array_slice($scores, ($page_offset - 1) * $amount_per_page, $amount_per_page, true);
		
		$moderatedcommunitylist = CommunityModeratorList::getByUid($user->getUid());
		$ismoderator = in_array($xid, $moderatedcommunitylist);
        
        echo UI::RenderThemeList($user, $themes, $scores, $xid, $ismoderator, true);
	}
}

?>