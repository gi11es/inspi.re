<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	This is an example page with all the menus and empty content
*/

require_once(dirname(__FILE__)."/entities/community.php");
require_once(dirname(__FILE__)."/entities/communitylist.php");
require_once(dirname(__FILE__)."/entities/communitymembershiplist.php");
require_once(dirname(__FILE__)."/entities/competitionlist.php");
require_once(dirname(__FILE__)."/entities/theme.php");
require_once(dirname(__FILE__)."/entities/themevote.php");
require_once(dirname(__FILE__)."/entities/user.php");
require_once(dirname(__FILE__)."/entities/userlevellist.php");
require_once(dirname(__FILE__)."/entities/userpaging.php");
require_once(dirname(__FILE__)."/utilities/page.php");
require_once(dirname(__FILE__)."/utilities/string.php");
require_once(dirname(__FILE__)."/utilities/ui.php");
require_once(dirname(__FILE__)."/constants.php");
require_once(dirname(__FILE__)."/settings.php");

$user = User::getSessionUser();

$page = new Page('THEMES', 'COMPETITIONS', $user);

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page->setTitle('<translate id="THEMES_PAGE_TITLE">Competition themes on inspi.re</translate>');

$page->startHTML();

$member_of = array_keys(CommunityMembershipList::getByUid($user->getUid()));
if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	$owner = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ANONYMOUS']);
} else {
	$owner = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ACTIVE']);
	$owner = array_merge($owner, CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['INACTIVE']));
}

$community_list = array_unique(array_merge($member_of, $owner));

$themelist = array();
$community = array();

foreach ($community_list as $xid) try {
	$community[$xid] = Community::get($xid);
	
	$themelist[$xid] = ThemeList::getByXidAndStatus($xid, $THEME_STATUS['SUGGESTED']);
	
	if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
		foreach (ThemeList::getByUidAndStatus($user->getUid(), $THEME_STATUS['ANONYMOUS']) as $tid => $local_xid)
			if ($local_xid == $xid) $themelist[$xid][]= $tid;
	} elseif ($user->getStatus() == $USER_STATUS['BANNED']) {
		foreach (ThemeList::getByUidAndStatus($user->getUid(), $THEME_STATUS['BANNED']) as $tid => $local_xid)
			if ($local_xid == $xid) $themelist[$xid][]= $tid;
	}
} catch (CommunityException $e) {}

function describeCommunity($xid) {
	global $PAGE;
	global $user;
	global $themelist;
	global $community;
	global $USER_STATUS;
	global $THEME_VOTE_STATUS;
	global $THEME_STATUS;
	
	switch ($user->getStatus()) {
		case $USER_STATUS['UNREGISTERED']:
			$status = $THEME_VOTE_STATUS['ANONYMOUS'];
			$theme_status = $THEME_STATUS['ANONYMOUS'];
			break;
		case $USER_STATUS['BANNED']:
			$status = $THEME_VOTE_STATUS['BANNED'];
			$theme_status = $THEME_STATUS['BANNED'];
			break;
		default:
			$status = $THEME_VOTE_STATUS['CAST'];
			$theme_status = $THEME_STATUS['SUGGESTED'];
	}
	
	$themevotelist = ThemeVoteList::getByUidAndStatus($user->getUid(), $status);
	
	$left_to_vote = count(array_diff($themelist[$xid], array_keys($themevotelist)));
	
	$ownthemelist = ThemeList::getByXidAndUidAndStatus($xid, $user->getUid(), $theme_status);
	
	$left_to_vote -= count($ownthemelist);

	echo '<div class="listing_item">';
	echo '<picture href="/'.String::urlify($community[$xid]->getName()).'/<translate id="URL_THEMELIST" escape="urlify">Upcoming Themes</translate>/s1-l'.$user->getLid().'-x'.$xid.'" category="community" class="listing_thumbnail" size="small" '.($community[$xid]->getPid() === null?'':'pid="'.$community[$xid]->getPid().'"').' />';
	echo '<div class="listing_header"><community_name href="/'.String::urlify($community[$xid]->getName()).'/<translate id="URL_THEMELIST" escape="urlify">Upcoming Themes</translate>/s1-l'.$user->getLid().'-x'.$xid.'" xid="'.$xid.'"/></div>';
	echo '<div class="community_listing_description">';
	echo '<translate id="THEMES_COMMUNITY_SUBHEADER_TWIN">';
	echo '<integer value="'.count($themelist[$xid]).'"/> upcoming theme(s) suggested for this community. <integer value="'.$left_to_vote.'"/> theme(s) you have yet to vote on.';
	echo '</translate>';
	echo '<br/>';
	echo '<translate id="THEMES_SUGGESTION_COST">';
	echo 'Suggesting a theme for this community costs <integer value="'.$community[$xid]->getThemeCost().'"/> point(s).';
	echo '</translate>';
	echo '</div> <!-- community_listing_description -->';
	echo '</div> <!-- listing_item -->';
}

function describeTheme($theme) {
	global $PAGE;
	global $user;
	global $community;
	
	$xid = $theme->getXid();
	
	// Let's start looking for the end time of the latest competition that happened, if any
	$competitionlist = CompetitionList::getByXid($xid);
	$next_start_time = $community[$xid]->getCreationTime() - ($community[$xid]->getCreationTime() % 3600) + $community[$xid]->getFrequency() * 86400;
	
	if (!empty($competitionlist)) {
		arsort($competitionlist);
		$real_last_time = array_shift($competitionlist);
		$last_start_time = $real_last_time - ($real_last_time % 3600);
		$next_start_time = $last_start_time + $community[$xid]->getFrequency() * 86400;
	}
	
	if ($next_start_time <= gmmktime()) $next_start_time = gmmktime();

	$duration = $community[$xid]->getNextCompetitionTime($next_start_time) - gmmktime();
	
	echo '<div class="listing_item theme_item">';
	echo '<picture href="/'.String::urlify($community[$xid]->getName()).'/<translate id="URL_THEMELIST" escape="urlify">Upcoming Themes</translate>/s1-l'.$user->getLid().'-x'.$xid.'" category="community" class="listing_thumbnail" size="small" '.($community[$xid]->getPid() === null?'':'pid="'.$community[$xid]->getPid().'"').' />';
	echo '<profile_picture class="listing_thumbnail" size="small" uid="'.$theme->getUid().'"/>';
	echo '<div class="listing_header theme_header"><a href="/'.String::urlify($community[$xid]->getName()).'/<translate id="URL_THEMELIST" escape="urlify">Upcoming Themes</translate>/s1-l'.$user->getLid().'-x'.$xid.'">'.String::fromaform($theme->getTitle()).'</a></div>';
	echo '<div class="listing_subheader theme_subheader">';
	echo '<translate id="THEMES_SUBHEADER">';
	echo 'Suggested by <user_name link="true" uid="'.$theme->getUid().'" /> for <community_name link="true" xid="'.$xid.'" />. It will become the next competition theme for that community <duration value="'.$duration.'" /> from now.';
	echo '</translate>';
	echo '</div> <!-- listing_subheader -->';
	echo '<div class="theme_listing_description">';
	echo String::fromaform($theme->getDescription());
	echo '</div> <!-- theme_listing_description -->';
	echo '</div> <!-- listing_item -->';
}

?>

<div class="hint hintmargin"><div class="hint_title"><translate id="TOPICS_HINT_TITLE">Suggest competition themes</translate></div><translate id="TOPICS_HINT_BODY">To suggest a theme or vote only on the themes of a particular community, pick a community below</translate></div>

<?php
	echo '<ad ad_id="LEADERBOARD"/>';
?>

<div id="community_list">

<?php

if (empty($community_list)) {
	echo '<div class="listing_item">';
	echo '<div class="listing_header">';
	echo '<translate id="THEMES_NOT_MEMBER_YET">';
	echo 'You\'re not a member of any community yet. You should <a href="'.$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid().'">browse the available communities</a> and pick one you wish to join.';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else foreach ($community_list as $xid) {
	describeCommunity($xid);
}

echo '</div> <!-- community_list -->';

if (!empty($community_list)) echo '<ad ad_id="THEMES"/>';

$upcoming_themes = array();

if (!empty($community_list)) {
foreach ($community_list as $xid) {
		$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'THEME_LIST_THEMES');
		$page_count = ceil(count($themelist[$xid]) / $amount_per_page);
		
		$scores = array();
		$themes = array();
		
		foreach ($themelist[$xid] as $tid) {
			$themes[$tid] = Theme::get($tid);
			$scores[$tid] = $themes[$tid]->getScore($user);
		}
		
		if (!empty($scores)) {
			arsort($scores);
			
			$tid = array_shift(array_keys($scores));
			
			$upcoming_themes []= $themes[$tid];
			unset($themes[$tid]);
		}
	}
}

if (!empty($upcoming_themes)) {
	echo '<div class="hint hintmargin '.($hideads?'abovemargin':'').'"><div class="hint_title"><translate id="TOPICS_UPCOMING_TITLE">Upcoming competition themes</translate></div>';
	echo '<translate id="TOPICS_UPCOMING_BODY">These are the upcoming themes for all the communities you\'re a member of</translate></div>';

	foreach ($upcoming_themes as $theme) {
		describeTheme($theme);
	}
}

$page->endHTML();
$page->render();
?>
