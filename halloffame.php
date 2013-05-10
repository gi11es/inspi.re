<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where the past winners of competitions can be seen
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitylist.php');
require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/competitionlist.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/entities/userpaging.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

function RenderCompetition($competition, $theme, $end_time, $first) {
	global $user; 
	global $USER_LEVEL;
	global $REQUEST;
	global $PAGE;
	global $USER_STATUS;
	global $ENTRY_STATUS;

	echo '<div class="'.($first?'':'listing_item ').'hof_item">';
	echo '<div class="listing_thumbnail hof_thumbnail">';
	
	if ($user->getStatus() == $USER_STATUS['BANNED']) {
		$ranks = $competition->getBannedRanks();
		$first = array();
		$second = array();
		$third = array();
		
		foreach ($ranks as $eid => $rank) {
		$entry = Entry::get($eid);
			if ($rank == 1) $first[$entry->getUid()] = $eid;
			elseif ($rank == 2) $second[$entry->getUid()] = $eid;
			elseif ($rank == 3) $third[$entry->getUid()] = $eid;
		}
		$amount = count($ranks);
	} else {	
		$first = EntryList::getByCidAndRank($competition->getCid(), 1);
		$second = EntryList::getByCidAndRank($competition->getCid(), 2);
		$third = EntryList::getByCidAndRank($competition->getCid(), 3);
		$amount = $competition->getEntriesCount();
	}
	
	switch ($user->getStatus()) {
		case $USER_STATUS['UNREGISTERED']:
			$status = $ENTRY_STATUS['ANONYMOUS'];
			break;
		case $USER_STATUS['BANNED']:
			$status = $ENTRY_STATUS['BANNED'];
			break;
		default:
			$status = $ENTRY_STATUS['POSTED'];
	}

	$ownentry = EntryList::getByUidAndCidAndStatus($user->getUid(), $competition->getCid(), $status);
	$votelist = EntryVoteList::getByUidAndCid($user->getUid(), $competition->getCid());
	$votecount = count($votelist);
	
	$votablecount = $amount - count($ownentry);
	
	if ($votecount > $votablecount) $votecount = $votablecount;
	
	if (empty($first)) echo '<picture category="entry" size="medium"/>';
	else foreach ($first as $uid => $eid) {
		$entry = Entry::get($eid);
		$pid = $entry->getPid();
		echo '<picture category="entry" size="medium" href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'" '.($pid === null?'':'pid="'.$pid.'"').'/>';
	}
	
	echo '</div> <!-- listing_thumbnail -->';
	echo '<div class="'.($votecount > 0 && $votablecount > 0 && $amount > 0?'voted_header':'listing_header').'">';
	echo '<theme_title href="'.$PAGE['RANKED'].'?lid='.$user->getLid().'&amp;cid='.$competition->getCid().'" tid="'.$competition->getTid().'"/>';

	if ($votecount > 0 && $votablecount > 0 && $amount > 0) {
		echo '<span class="vote_quantity">';
		echo '<translate id="VOTE_QUANTITY">';
		echo 'You\'ve voted on <float value="'.round(100 * ($votecount / $votablecount), 2).'"/>% of this competition\'s entries';
		echo '</translate>';
		echo '</span> <!-- vote_quantity -->';
	}

	echo '</div> <!-- listing_header -->';
	echo '<div class="listing_subheader hof_subheader">';
	echo '<translate id="COMPETITION_SHORT_DESCRIPTION_CLOSED">';
	echo 'Suggested by <user_name uid="'.$theme->getUid().'"/> for <community_name link="true" xid="'.$competition->getXid().'"/>. This competition closed <duration value="'.(gmmktime() - $competition->getEndTime()).'"/> ago.';
	echo'</translate>';
	echo '</div> <!-- listing_subheader -->';
	
	if ($amount == 0) {
		echo '<span class="no_entries">';
		echo '<translate id="HALL_OF_FAME_NO_ENTRIES">';
		echo 'There were no entries in this competition';
		echo '</translate>';
		echo '</span> <!-- no_entries -->';
	}
	
	foreach ($first as $uid => $eid) {
		echo '<div class="rank_first">';
		echo '<translate id="HALL_OF_FAME_RANKED_FIRST">';
		echo '<user_name uid="'.$uid.'"/> came first with <a href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'">this entry</a>';
		echo '</translate>';
		echo '</div> <!-- rank_first -->';
	}
	
	foreach ($second as $uid => $eid) {
		echo '<div class="rank_second">';
		echo '<translate id="HALL_OF_FAME_RANKED_SECOND">';
		echo '<user_name uid="'.$uid.'"/> came second with <a href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'">this entry</a>';
		echo '</translate>';
		echo '</div> <!-- rank_second -->';
	}
	
	foreach ($third as $uid => $eid) {
		echo '<div class="rank_third">';
		echo '<translate id="HALL_OF_FAME_RANKED_THIRD">';
		echo '<user_name uid="'.$uid.'"/> came third with <a href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'">this entry</a>';
		echo '</translate>';
		echo '</div> <!-- rank_third -->';
	}
	
	if ($amount > 1) {
		echo '<div class="see_all">';
		echo '<a href="'.$PAGE['RANKED'].'?lid='.$user->getLid().'&amp;cid='.$competition->getCid().'">';
		echo '<translate id="HALL_OF_FAME_LINK_ALL">';
		echo 'See all <integer value="'.$amount.'"/> entries in this competition';
		echo '</translate>';
		echo '</a>';
		echo '</div> <!-- see_all -->';
	}
	
	echo '</div> <!-- listing_item -->';
}

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page = new Page('HALL_OF_FAME', 'COMPETITIONS', $user);
$page->addJavascript('VIEW_HALL_OF_FAME');

$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;

$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'HALL_OF_FAME_COMPETITIONS');

$competitionlist = array();
$communitylist = $user->getCommunityList();
if (empty($communitylist) || isset($_REQUEST['showall'])) {
	$communitylist = CommunityList::getByStatus($COMMUNITY_STATUS['ACTIVE']);
	$communitylist = array_merge($communitylist, CommunityList::getByStatus($COMMUNITY_STATUS['INACTIVE']));
}
$competition = array();
$endTimeList = array();

$page->addJavascriptVariable('reload_url', $PAGE['HALL_OF_FAME'].'?lid='.$user->getLid().(isset($_REQUEST['showall'])?'&showall=true':''));
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);

$page->startHTML();

// If a community's xid is specified in the request we only display the HOF entries for tht specific community
if (isset($_REQUEST['xid'])) try {
	$selected_xid = $_REQUEST['xid'];
	$community = Community::get($selected_xid);
	$communitylist = array($selected_xid);
	
	$members = CommunityMembershipList::getByXidAndStatus($selected_xid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);
	
	$member_count = count($members);
	echo '<div class="community_name">';
	echo '<picture href="'.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$selected_xid.'" category="community" class="listing_thumbnail" size="small" '.($community->getPid() === null?'':'pid="'.$community->getPid().'"').' />';
	echo '<div class="listing_header"><community_name link="true" xid="'.$selected_xid.'"/></div>';
	echo '<translate id="COMMUNITIES_DESCRIPTION_WHEN2">';
	echo 'Created <duration value="'.(time() - $community->getCreationTime()).'"/> ago. Administrated by <user_name class="community_administrator" uid="'.$community->getUid().'" /><br/>';
	echo '</translate>';
	echo '<translate id="COMMUNITIES_DESCRIPTION_LANGUAGE2">';
	echo 'The primary language is <language_name lid="'.$community->getLid().'"/>.';
	echo '</translate>';
	echo '<br/>';
	
	$active_member_count = $community->getActiveMemberCount();
	
	if ($active_member_count == 1) {
		echo '<translate id="COMMUNITIES_DESCRIPTION_MEMBERSHIP_SINGULAR">';
		echo '1 active member (<integer value="'.$member_count.'"/> registered)';
		echo '</translate>';
	} else {
		echo '<translate id="COMMUNITIES_DESCRIPTION_MEMBERSHIP_PLURAL">';
		echo '<integer value="'.$active_member_count.'"/> active members (<integer value="'.$member_count.'"/> registered)';
		echo '</translate>';
	}
	echo '</div> <!-- community_name -->';
} catch (CommunityException $e) {
	$selected_xid = null;
} else $selected_xid = null;

if ($selected_xid === null) {
	$page->setTitle('<translate id="HALL_OF_FAME_PAGE_TITLE">Hall of fame on inspi.re</translate>');
} else {
	$page->setTitle('<translate id="HALL_OF_FAME_PAGE_TITLE_COMMUNITY">Hall of fame for the <string value="'.String::fromaform($community->getName()).'"/> community on inspi.re</translate>');
}

if (!empty($communitylist)) foreach ($communitylist as $xid) {
	$competitionlist += CompetitionList::getByXidAndStatus($xid, $COMPETITION_STATUS['CLOSED']);
}

if (!empty($communitylist)) {
	$competition = Competition::getArray(array_keys($competitionlist));

	foreach ($competition as $cid => $comp)
		$endTimeList[$cid] = $comp->getEndTime();
	
	arsort($endTimeList);
}

$page_count = ceil(count($endTimeList) / $amount_per_page);

$endTimeList = array_slice($endTimeList, ($page_offset - 1) * $amount_per_page, $amount_per_page, true);

$themelist = array();
foreach ($endTimeList as $cid => $end_time) {
	$themelist []= $competition[$cid]->getTid();
}

$theme = Theme::getArray($themelist);

if ($page_count > $page_offset)
	$page->addJavascriptVariable('next_page', $PAGE['HALL_OF_FAME'].'?lid='.$user->getLid().($selected_xid == null?'':'&xid='.$selected_xid).(isset($_REQUEST['showall'])?'&showall=true':'').'&page='.($page_offset + 1));

if ($page_offset > 1)
	$page->addJavascriptVariable('previous_page', $PAGE['HALL_OF_FAME'].'?lid='.$user->getLid().($selected_xid == null?'':'&xid='.$selected_xid).(isset($_REQUEST['showall'])?'&showall=true':'').'&page='.($page_offset - 1));

echo '<div class="hint">';
echo '<div class="hint_title">';
echo '<translate id="HALL_OF_FAME_HINT_TITLE">';
echo 'Winning entries of the past competitions';
echo '</translate>';
echo '</div> <!-- hint_title -->';
if ($selected_xid === null) {
	echo '<translate id="HALL_OF_FAME_HINT_BODY">';
	echo 'You\'ll find below an exhaustive list presenting the winners of all of your communities\' past competitions. Congratulations to all the winners!';
	echo '</translate>';
} else {
	echo '<translate id="HALL_OF_FAME_HINT_BODY_SINGLE_COMMUNITY">';
	echo 'You\'ll find below an exhaustive list presenting the winners of this communities\' past competitions. Congratulations to all the winners!';
	echo '</translate>';

}
echo ' ';
echo '<translate id="HALL_OF_FAME_HINT_BODY_KEYBOARD">';
echo 'You can use the left and right arrow on your keyboard to navigate the pages.';
echo '</translate>';
echo '</div> <!-- hint -->';

if (!isset($_REQUEST['showall'])) {
	echo '<div class="hanging_menu floatleft '.($hideads?'belowmargin':'').'" id="toggle_view_filter">';
	echo '<a href="'.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid().'&showall=true">';
	echo '<translate id="HALL_OF_FAME_DISPLAY_ALL_LINK">';
	echo 'Display the hall of fame for all the communities on inspi.re';
	echo '</translate>';
	echo '</a>';
	echo '</div> <!-- hanging_menu -->';
} else {
	echo '<div class="hanging_menu floatleft '.($hideads?'belowmargin':'').'" id="toggle_view_filter">';
	echo '<a href="'.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid().'">';
	echo '<translate id="HALL_OF_FAME_DISPLAY_OWN_LINK">';
	echo 'Display the hall of fame for the communities you\'re a member of';
	echo '</translate>';
	echo '</a>';
	echo '</div> <!-- hanging_menu -->';
}

function RenderHOFLink($i, $page_offset, $page_count) {
	global $user;
	global $selected_xid;
	global $_REQUEST;
	global $PAGE;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid().($selected_xid == null?'':'&xid='.$selected_xid).(isset($_REQUEST['showall'])?'&showall=true':'').'&page='.$i.'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderHOFLink');

echo '<ad id="ad_hof" ad_id="HALL_OF_FAME"/>';

if ($selected_xid !== null && empty($competitionlist)) {
	echo '<div class="hof_item">';
	echo '<div class="listing_header belowmargin">';
	echo '<translate id="HALL_OF_FAME_NO_CLOSED_COMPETITIONS_SINGLE_COMMUNITY">';
	echo 'There are currently no closed competitions in this community.';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else {
	if (empty($communitylist)) {
		echo '<div class="hof_item">';
		echo '<div class="listing_header belowmargin">';
		echo '<translate id="HALL_OF_FAME_NO_COMMUNITIES">';
		echo 'You\'re not a member of any community yet. You must first <a href="'.$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid().'">join a community</a> before you can see closed competitions in the hall of fame.';
		echo '</translate>';
		echo '</div> <!-- listing_header -->';
		echo '</div> <!-- listing_item -->';
	}
	
	if (!empty($communitylist) && empty($competitionlist)) {
		echo '<div class="hof_item">';
		echo '<div class="listing_header belowmargin">';
		echo '<translate id="HALL_OF_FAME_NO_CLOSED_COMPETITIONS">';
		echo 'There are currently no closed competitions in your communities. You can <a href="'.$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid().'">join more communities</a> if you like.';
		echo '</translate>';
		echo '</div> <!-- listing_header -->';
		echo '</div> <!-- listing_item -->';
	} elseif (!empty($endTimeList)) {
		$first = true;
		if ($hideads) $first = false;
		
		foreach ($endTimeList as $cid => $end_time) if (isset($competition[$cid]) && isset($theme[$competition[$cid]->getTid()])) {
			RenderCompetition($competition[$cid], $theme[$competition[$cid]->getTid()], $end_time, $first);
			if ($first) $first = false;
		}
	}
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderHOFLink', true);
echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
echo '<div id="competitions_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="HALL_OF_FAME_AMOUNT_PLURAL">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> competitions per page.';
	echo '</translate>';
} else {
	echo '<translate id="HALL_OF_FAME_AMOUNT_SINGULAR">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> competitions per page.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="competitions_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changeCompetitionsAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="competitions_change_input" style="display:none">';
echo '<translate id="HALL_OF_FAME_INPUT_AMOUNT">';
echo 'Display <input id="competitions_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> competitions per page. <a href="javascript:saveCompetitionsAmount()">Save</a> <a href="javascript:cancelCompetitionsAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>
