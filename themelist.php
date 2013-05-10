<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of upcoming themes for a specific community
*/

require_once(dirname(__FILE__)."/entities/community.php");
require_once(dirname(__FILE__)."/entities/communitymembership.php");
require_once(dirname(__FILE__)."/entities/communitymoderatorlist.php");
require_once(dirname(__FILE__)."/entities/competitionlist.php");
require_once(dirname(__FILE__)."/entities/theme.php");
require_once(dirname(__FILE__)."/entities/themelist.php");
require_once(dirname(__FILE__)."/entities/themevote.php");
require_once(dirname(__FILE__)."/entities/user.php");
require_once(dirname(__FILE__)."/entities/userpaging.php");
require_once(dirname(__FILE__)."/utilities/page.php");
require_once(dirname(__FILE__)."/utilities/string.php");
require_once(dirname(__FILE__)."/utilities/ui.php");
require_once(dirname(__FILE__)."/constants.php");
require_once(dirname(__FILE__)."/settings.php");

$user = User::getSessionUser();

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;

if ($xid !== null) {
	try {
		$community = Community::get($xid);
		$name = $community->getName();
		$title = String::fromaform($name);
		if ($xid == 267) {
			$title = '<translate id="PRIZE_COMMUNITY_NAME">'.$name.'</translate>';
		}
	} catch (Exception $e) {
		exit(0);
	}
} else {
	exit(0);
}

$page = new Page('THEMES', 'COMPETITIONS', $user);
$page->setTitle('<translate id="THEME_LIST_PAGE_TITLE_CREATE">Upcoming themes for the <string value="'.String::htmlentities($name).'"/> community on inspi.re</translate>');

$moderatedcommunitylist = CommunityModeratorList::getByUid($user->getUid());
$ismoderator = in_array($xid, $moderatedcommunitylist);

if ($community->getThemeRestrictUsers()) {
	$able_to_suggest = ($ismoderator || $community->getUid() == $user->getUid());
} else {
	$able_to_suggest = true;
}

$page->addJavascript('THEME_LIST');

$page->addJavascriptVariable('reload_url', '/'.String::urlify($community->getName()).'/<translate id="URL_THEMELIST" escape="urlify">Upcoming Themes</translate>/s1-l'.$user->getLid().'-x'.$xid);
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);
$page->addJavascriptVariable('request_cast_theme_vote', $REQUEST['CAST_THEME_VOTE']);
if (isset($_REQUEST['scrollto'])) $page->addJavascriptVariable('scrollto', $_REQUEST['scrollto']);

$page->startHTML();

$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;
$page->addJavascriptVariable('page_offset', $page_offset);

$themelist = ThemeList::getByXidAndStatus($xid, $THEME_STATUS['SUGGESTED']);
	
$own_themelist = array();
if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	foreach (ThemeList::getByUidAndStatus($user->getUid(), $THEME_STATUS['ANONYMOUS']) as $tid => $local_xid)
		if ($local_xid == $xid) {
			$themelist[]= $tid;
			$own_themelist[]= $tid;
		}
} elseif ($user->getStatus() == $USER_STATUS['BANNED']) {
	foreach (ThemeList::getByUidAndStatus($user->getUid(), $THEME_STATUS['BANNED']) as $tid => $local_xid)
		if ($local_xid == $xid) {
			$themelist[]= $tid;
			$own_themelist[]= $tid;
		}
} else {
	foreach (ThemeList::getByUidAndStatus($user->getUid(), $THEME_STATUS['SUGGESTED']) as $tid => $local_xid)
		if ($local_xid == $xid) $own_themelist[]= $tid;
}

if ($able_to_suggest) {
	$show_suggest = true;
	
	if ($community->getMaximumThemeCount() !== null && count($themelist) >= $community->getMaximumThemeCount()) {
		echo '<div class="warning hintmargin">';
		echo '<div class="warning_title">';
		echo '<translate id="THEME_LIST_ERROR_THEME_COUNT">The maximum amount of theme suggestions for this community (<integer value="'.$community->getMaximumThemeCount().'" />) has been reached</translate>';
		echo '</div> <!-- warning_title -->';
		echo '<translate id="THEME_LIST_ERROR_THEME_COUNT_BODY">You must wait until the quantity of theme suggestions goes down before you can suggest a new one. In the meantime you can vote on any of the existing theme suggestions or delete your own.</translate>';
		echo '</div> <!-- warning -->';
		$show_suggest = false;
	}
	
	if ($community->getMaximumThemeCountPerMember() !== null && count($own_themelist) >= $community->getMaximumThemeCountPerMember()) {
		echo '<div class="warning hintmargin">';
		echo '<div class="warning_title">';
		echo '<translate id="THEME_LIST_ERROR_THEME_COUNT_PER_MEMBER">You\'ve reached the maximum amount of theme suggestions per member for this community (<integer value="'.$community->getMaximumThemeCountPerMember().'" />)</translate>';
		echo '</div> <!-- warning_title -->';
		echo '<translate id="THEME_LIST_ERROR_THEME_COUNT_PER_MEMBER_BODY">You can wait until one of your theme suggestions is selected or you can delete one of your existing suggestions in order to suggest a new theme.</translate>';
		echo '</div> <!-- warning -->';
		$show_suggest = false;
	}
} else {
	echo '<div class="warning hintmargin">';
	echo '<div class="warning_title">';
	echo '<translate id="THEME_LIST_USERS_RESTRICTED_TITLE">Theme suggestions are restricted for this community</translate>';
	echo '</div> <!-- warning_title -->';
	echo '<translate id="THEME_LIST_USERS_RESTRICTED_BODY">Only the administrator and the moderators of this community can suggest new themes. You can still vote on them.</translate>';
	echo '</div> <!-- warning -->';
	$show_suggest = false;
}

echo'<div id="theme_list_header">';
echo'<div id="theme_list_header_floater">';

$competitionlist = CompetitionList::getByXid($xid);

$next_start_time = $community->getCreationTime() - ($community->getCreationTime() % 3600) + $community->getFrequency() * 86400;
	
if (!empty($competitionlist)) {
	arsort($competitionlist);
	$real_last_time = array_shift($competitionlist);
	$last_start_time = $real_last_time - ($real_last_time % 3600);
	$next_start_time = $last_start_time + $community->getFrequency() * 86400;
}
	
	if ($next_start_time <= gmmktime()) $next_start_time = gmmktime();

$duration = $community->getNextCompetitionTime($next_start_time) - gmmktime();

echo '<div class="hint">';
echo '<div class="hint_big_title">';
echo $title;
echo '</div> <!-- hint_big_title -->';
echo '<translate id="THEMELIST_HINT_BODY">Below are the upcoming themes for this community. The theme with the highest score will become the next competition <duration value="'.$duration.'"/> from now.</translate>';
echo '</div> <!-- hint -->';

if ($show_suggest) {
	echo '<div class="hanging_menu floatleft hanging_menu_margin">';
	echo '<a href="'.$PAGE['NEW_THEME'].'?lid='.$user->getLid().'&xid='.$xid.'"><translate id="THEMELIST_SUGGEST">Suggest a new competition theme</translate></a>';
	echo '</div> <!-- hanging_menu -->';
}
if ($community->getUid() == $user->getUid()) {
	echo '<div class="hanging_menu floatleft">';
	echo '<a href="/request/startnextcompetition.php?xid='.$xid.'"><translate id="THEMELIST_START_EARLY">Start the next competition now (overrides the normal cycle)</translate></a>';
	echo '</div> <!-- hanging_menu -->';
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

function RenderThemeListLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $community;
	global $xid;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="/'.String::urlify($community->getName()).'/<translate id="URL_THEMELIST" escape="urlify">Upcoming Themes</translate>/s1-l'.$user->getLid().'-x'.$xid.'-p'.$i.'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderThemeListLink');

echo '</div> <!-- theme_list_header_floater -->';
echo '</div> <!-- theme_list_header -->';

echo '<ad ad_id="THEME_LIST"/>';

echo UI::RenderThemeList($user, $themes, $scores, $xid, $ismoderator);

echo UI::RenderPaging($page_offset, $page_count, 'RenderThemeListLink', true);
echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
echo '<div id="themes_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="THEME_LIST_AMOUNT_PLURAL">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> themes per page.';
	echo '</translate>';
} else {
	echo '<translate id="THEME_LIST_AMOUNT_SINGULAR">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> theme per page.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="themes_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changeThemesAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="themes_change_input" style="display:none">';
echo '<translate id="THEME_LIST_INPUT_AMOUNT">';
echo 'Display <input id="themes_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> themes per page. <a href="javascript:saveThemesAmount()">Save</a> <a href="javascript:cancelThemesAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>
