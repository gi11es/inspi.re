<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Create a new competition theme
*/

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/../entities/pointsvalue.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userpaging.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;
$title = isset($_REQUEST['title'])?stripslashes($_REQUEST['title']):null;
$description = isset($_REQUEST['description'])?stripslashes($_REQUEST['description']):null;

function leave($from) {
	global $PAGE;
	global $user;
	global $xid;
	global $community;

	header(I18N::translateHTML($user, 'Location: /'.String::urlify($community->getName()).'/<translate id="URL_THEMELIST" escape="urlify">Upcoming Themes</translate>/s1-l'.$user->getLid().'-x'.$xid));
	exit(0);
}

if ($title !== null && $xid !== null) {
	$community = Community::get($xid);
	$limit = $community->getMaximumThemeCountPerMember();
	
	switch ($user->getStatus()) {
		case $USER_STATUS['UNREGISTERED']:
			$status = $THEME_STATUS['ANONYMOUS'];
			break;
		case $USER_STATUS['BANNED']:
			$status = $THEME_STATUS['BANNED'];
			break;
		default:
			$status = $THEME_STATUS['SUGGESTED'];
	}
	
	$ownthemes = Themelist::getByXidAndUidAndStatus($xid, $user->getUid(), $status);
	
	if ($limit !== null && $limit <= count($ownthemes)) leave(1); // Already suggested too many themes
	
	if ($community->getThemeRestrictUsers()) {
		$moderatedcommunitylist = CommunityModeratorList::getByUid($user->getUid());
		if (!in_array($xid, $moderatedcommunitylist) && $community->getUid() != $user->getUid())
			leave(1);
	}

	$points_suggest_theme = $community->getThemeCost();
	
	if ($points_suggest_theme > 0) try {
		$user->givePoints(- $points_suggest_theme);
	} catch (UserException $e) {
		leave(2);
	}
	
	$theme = new Theme($xid, $user->getUid(), $title, $description, $status, $points_suggest_theme);
	
	$themelist = ThemeList::getByXidAndStatus($xid, $THEME_STATUS['SUGGESTED']);
	
	if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
		foreach (ThemeList::getByUidAndStatus($user->getUid(), $THEME_STATUS['ANONYMOUS']) as $tid => $local_xid)
			if ($local_xid == $xid) $themelist []= $tid;
	} elseif ($user->getStatus() == $USER_STATUS['BANNED']) {
		foreach (ThemeList::getByUidAndStatus($user->getUid(), $THEME_STATUS['BANNED']) as $tid => $local_xid)
			if ($local_xid == $xid) $themelist []= $tid;
	}
	
	$themelist = array_unique($themelist);
	
	$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'THEME_LIST_THEMES');
	
	$scores = array();
	$themes = array();
	
	foreach ($themelist as $tid) {
		$themes[$tid] = Theme::get($tid);
		$scores[$tid] = $themes[$tid]->getScore($user);
	}
	
	arsort($scores);
	
	$page_offset = 1;
	$count = 0;
	
	foreach($scores as $tid => $score) {
		$count ++;
		if ($tid == $theme->getTid()) {
			$page_offset = ceil($count / $amount_per_page);
			break;
		}
	}
	
	header(I18N::translateHTML($user, 'Location: /'.String::urlify($community->getName()).'/<translate id="URL_THEMELIST" escape="urlify">Upcoming Themes</translate>/s1-l'.$user->getLid().'-x'.$xid.'-p'.$page_offset.'-ttheme_'.$theme->getTid()));	
} else leave(3);

?>