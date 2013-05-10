<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Display information about a specific community
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitylabel.php');
require_once(dirname(__FILE__).'/entities/communitylabellist.php');
require_once(dirname(__FILE__).'/entities/communitymembership.php');
require_once(dirname(__FILE__).'/entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/competitionlist.php');
require_once(dirname(__FILE__).'/entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/insightfulmarklist.php');
require_once(dirname(__FILE__).'/entities/pointsvalue.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

require_once(dirname(__FILE__).'/libraries/open_flash_chart_object.php');

$user = User::getSessionUser();

$xid = (isset($_REQUEST['xid'])?$_REQUEST['xid']:null);

if ($xid === null) {
	header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
	exit(0);
}

$member = true;
try { CommunityMembership::get($xid, $user->getUid()); } catch (CommunityMembershipException $e) { $member = false; }

if ($member)
	$page = new Page('COMMUNITIES', 'COMMUNITIES', $user);
else {
	$page = new Page('JOIN_COMMUNITIES', 'COMMUNITIES', $user);
	$page->addStyle('COMMUNITIES');
}

$page->addStyle('EDITABLE_PICTURE');
$page->addJavascript('COMMUNITY');
$page->addJavascript('EDITABLE_PICTURE');
$persistenttoken = new PersistentToken($user->getUid());
$page->addJavascriptVariable('persistenttoken', $persistenttoken->getHash());
$page->startHTML();

$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);
$page->addJavascriptVariable('reload_url', $PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$_REQUEST['xid']);

try {
	$community = Community::get($xid);
} catch (CommunityException $e) {
	header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
	exit(0);
}

$moderatorlist = CommunityModeratorList::getByXid($xid);
$ismoderator = in_array($user->getUid(), $moderatorlist);

if ($xid == 267) {
	$page->setTitle('<translate id="COMMUNITY_PRIZE_PAGE_TITLE">The Monthly Prize community on inspi.re</translate>');
} else {
	$page->setTitle('<translate id="COMMUNITY_PAGE_TITLE"><string value="'.String::fromaform($community->getName()).'"/> community on inspi.re</translate>');
}

if (isset($_REQUEST['left']) && strcasecmp($_REQUEST['left'], 'true') == 0 && !$member) {
	echo '<div class="warning hintmargin" id="left_confirmation">';
	echo '<div class="warning_title">';
	echo '<translate id="COMMUNITY_LEAVE_CONFIRMATION">';
	echo 'You have left this community successfully';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} elseif (isset($_REQUEST['joined']) && strcasecmp($_REQUEST['joined'], 'true') == 0 && $member) {
	echo '<div class="warning hintmargin" id="joined_confirmation">';
	echo '<div class="warning_title">';
	echo '<translate id="COMMUNITY_JOIN_CONFIRMATION">';
	echo 'You have joined this community successfully';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} elseif (isset($_REQUEST['appeal']) && strcasecmp($_REQUEST['appeal'], 'true') == 0) {
	echo '<div class="warning hintmargin" id="appeal_confirmation">';
	echo '<div class="warning_title">';
	echo '<translate id="COMMUNITY_APPEAL_CONFIRMATION">';
	echo 'Your administration rights appeal has been sent successfully to the current administrator of this community';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} elseif (isset($_REQUEST['merge']) && strcasecmp($_REQUEST['merge'], 'true') == 0) {
	echo '<div class="warning hintmargin" id="merge_confirmation">';
	echo '<div class="warning_title">';
	echo '<translate id="COMMUNITY_MERGE_CONFIRMATION">';
	echo 'The community was successfully merged into this one';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} elseif (isset($_REQUEST['relinquished']) && strcasecmp($_REQUEST['relinquished'], 'true') == 0) {
	echo '<div class="warning hintmargin" id="merge_confirmation">';
	echo '<div class="warning_title">';
	echo '<translate id="COMMUNITY_RELINQUISHED_CONFIRMATION">';
	echo 'This community has been successfully transferred to a new administrator';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} elseif (isset($_REQUEST['saved']) && strcasecmp($_REQUEST['saved'], 'true') == 0) {
	echo '<div class="warning hintmargin">';
	echo '<div class="warning_title">';
	echo '<translate id="COMMUNITY_SAVED">';
	echo 'This community has been successfully saved from deletion for another four weeks';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} elseif (isset($_REQUEST['saved'])) {
	echo '<div class="warning hintmargin">';
	echo '<div class="warning_title">';
	echo '<translate id="COMMUNITY_NOT_SAVED">';
	echo 'You don\'t have enough points to save this community from deletion';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
}

if ($community->getUid() == $user->getUid() && $community->getStatus() == $COMMUNITY_STATUS['INACTIVE'] && $community->getInactiveSince() !== null) {
	$pointsvalue = PointsValue::get($POINTS_VALUE_ID['COMMUNITY_CREATING']);
	$points_community_creating = - $pointsvalue->getValue();
	$duration = max(0, $community->getInactiveSince() + 2419200 - time());
	$duration2 = max(0, $community->getInactiveSince() + (2 * 2419200) - time());

	echo '<div class="warning hintmargin">',
		 '<div class="warning_title">',
		 '<translate id="COMMUNITY_INACTIVE">',
		 'Your community has been seeing very low activity recently. If nothing is done, it will be deleted automatically <duration value="',$duration,'"/> from now.',
		 '</translate>',
		 '</div> <!-- warning_title -->',
		 '<translate id="COMMUNITY_INACTIVE_BODY">',
		 'There are 2 options for you to save this community from deletion:<br/>',
		 ' - Transfer administration rights to someone else who would be willing to merge it with an active community (you can do that on the profile of a member of this community).<br/>',
		 ' - <a href="',$REQUEST['SAVE_COMMUNITY'],'?xid=',$community->getXid(),'">Spend <integer value="',$points_community_creating,'"/> points to save it from deletion<a/> for another four weeks (if it stays inactive, the earliest next automatic deletion date would be <duration value="',$duration2,'"/> from now).<br/>',
		 '<br/>If you go for the second option, you should be aware that you will have to make your community more active, otherwise you will end up spending <integer value="',$points_community_creating,'"/> points every 4 weeks to save it from deletion.',
		 '</translate>',
		 '</div> <!-- warning -->';
}

?>

<div class="hint">
<div class="hint_title" id="community_title">
<?php
	if ($xid == 267) {
		echo '<translate id="PRIZE_COMMUNITY_NAME">'.$community->getName().'</translate>';
	} else {
		echo String::htmlentities($community->getName());
	}
?>
</div> <!-- hint_title -->
</div> <!-- hint -->
<?php

if ($ismoderator || $user->getUid() == $community->getUid()) {
	echo '<div class="hanging_menu floatleft">';
	echo '<a href="'.$PAGE['NEW_DISCUSSION_THREAD'].'?lid='.$user->getLid().'&xid='.$xid.'"><translate id="BOARD_NEW_THREAD">Make a new announcement</translate></a>';
	echo '</div>';
}

if ($user->getUid() == $community->getUid()) {
	echo '<div class="hanging_menu floatleft">';
	echo '<a href="'.$PAGE['EDIT_COMMUNITY'].'?lid='.$user->getLid().'&xid='.$xid.'"><translate id="COMMUNITY_EDIT_LINK">Edit this community</translate></a>';
	echo '</div>';
	
	$communitylist = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ACTIVE']);
	$communitylist = array_merge($communitylist, CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['INACTIVE']));
	
	if (count($communitylist) > 1) {
		echo '<div class="hanging_menu floatleft" id="merge_community">';
		echo '<a href="'.$PAGE['MERGE_COMMUNITY'].'?lid='.$user->getLid().'&xid='.$xid.'"><translate id="COMMUNITY_MERGE_LINK">Merge this community into another</translate></a>';
		echo '</div>';
	}
	
	echo '<div class="hanging_menu floatleft" id="delete_community">';

	echo '<a href="javascript:showConfirmation(\''.$REQUEST['DELETE_COMMUNITY'].'?xid='.$xid.'\'';
	echo ', \'<translate id="COMMUNITY_DELETE_CONFIRMATION_TITLE" escape="js">Do you really want to delete this community?</translate>\'';
	echo ', \'<translate id="COMMUNITY_DELETE_CONFIRMATION_TEXT" escape="js">All past and future competitions, themes, discussions which this community contains will be deleted forever. This can\'t be undone!</translate>\'';
	echo ', \'<translate id="COMMUNITY_DELETE_CONFIRMATION_YES" escape="js">Yes, go ahead</translate>\'';
	echo ', \'<translate id="COMMUNITY_DELETE_CONFIRMATION_NO" escape="js">No</translate>\'';
	echo ');"><translate id="COMMUNITY_DELETE_LINK">Delete this community</translate></a>';	

	echo '</div> <!-- delete_community -->';

} else {

	if (!$member && $xid != 267) {
	?>
	<div class="hanging_menu floatleft">
	<a href="<?=$REQUEST['JOIN_COMMUNITY'].'?xid='.$xid?>"><translate id="COMMUNITY_JOIN_LINK">Join this community</translate></a>
	</div>
	<?php
	} elseif ($xid != 267) {
	?>
	<div class="hanging_menu floatleft">
	<a href="<?=$REQUEST['LEAVE_COMMUNITY'].'?xid='.$xid?>"><translate id="COMMUNITY_LEAVE_LINK">Leave this community</translate></a>
	</div>
	<?php
	}
}

$recentlyactiveuserlist = array_keys(UserList::getActive30Days());

if (!in_array($community->getUid(), $recentlyactiveuserlist) && $user->getUid() != $community->getUid() && $user->getStatus() == $USER_STATUS['ACTIVE']) {
	echo '<div class="warning" id="inactive_administrator">';
	echo '<div class="warning_title">';
	echo '<translate id="COMMUNITY_INACTIVE_ADMINISTRATOR_TITLE">';
	echo 'The administrator of this community has been inactive for over 30 days';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '<translate id="COMMUNITY_INACTIVE_ADMINISTRATOR_BODY">';
	echo 'If you want to ask him/her to give you administrative control over this community, you can <a href="'.$PAGE['COMMUNITY_APPEAL'].'?lid='.$user->getLid().'&xid='.$community->getXid().'">send a community transfer appeal</a>.';
	echo '</translate>';
	echo '</div> <!-- warning -->';
}

?>

<div id="left_side">
<?php
echo UI::RenderEditablePicture($page, $community->getPid(), $PICTURE_CATEGORY['COMMUNITY'], $user->getUid() == $community->getUid(), $REQUEST['COMMUNITY_PICTURE_UPLOAD'].'?xid='.$xid, $REQUEST['COMMUNITY_PICTURE_RESET'].'?xid='.$xid, $PAGE['EDIT_CROPPING'].'?xid='.$xid, $persistenttoken->getHash());

echo '<div class="hint hintmargin abovemargin">';
echo '<div class="hint_title">';
echo '<translate id="COMMUNITY_ADMINISTRATOR_HEADER">';
echo 'Administrator';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

echo '<div id="community_administrator">';
echo '<profile_picture class="listing_thumbnail" uid="'.$community->getUid().'" size="small"/>';
echo '</div> <!-- community_administrator -->';

$moderatorlist = CommunityModeratorList::getByXid($xid);

if (!empty($moderatorlist)) {
	echo '<div class="hint hintmargin">';
	echo '<div class="hint_title">';
	echo '<translate id="COMMUNITY_MODERATORS_HEADER">';
	echo 'Moderators';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	
	echo '<div id="community_moderators">';
	foreach ($moderatorlist as $uid)
		echo '<profile_picture class="moderator_thumbnail" uid="'.$uid.'" size="small"/>';
	echo '</div> <!-- community_moderators -->';
}

echo '</div> <!-- left_side -->';
echo '<div id="community_information">';

$entrylist = array();
$competitionlist = CompetitionList::getByXidAndStatus($xid, $COMPETITION_STATUS['CLOSED']);

arsort($competitionlist);

$i = 1;
foreach ($competitionlist as $cid => $start_time) {
	$entrylist = array_merge($entrylist, array_values(EntryList::getByCidAndRank($cid, 1)));
	if (++$i > 5) break;
}

if (count($entrylist) > 5) $entrylist = array_slice($entrylist, 0, 5, true);

if (!empty($entrylist)) {
	echo '<div class="hint hintmargin">';
	echo '<div class="hint_title">';
	echo '<translate id="COMMUNITY_RECENT_WINNERS_HEADER">';
	echo 'Recent winning artworks';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	
	echo '<div id="winning_thumbnails">';
	
	foreach ($entrylist as $eid) try {
		$entry = Entry::get($eid);
		$pid = $entry->getPid();
		
		if ($pid !== null) try {
			$entry_user = User::get($entry->getUid());
			$competition = Competition::get($entry->getCid());
			$theme = Theme::get($competition->getTid());
			$competitionname = $theme->getTitle();
			$title = '<translate id="INDEX_WINNING_ENTRY_TITLE"><string value="'.String::htmlentities($entry_user->getUniqueName()).'"/> won <string value="'.String::htmlentities('"'.$competitionname.'"').'"/> <duration value="'.(gmmktime() - $competition->getEndTime()).'"/> ago</translate>';
			$title = INML::processHTML($user, I18N::translateHTML($user, $title));
			echo '<picture title="'.$title.'" href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'" class="winning_thumbnail" pid="'.$pid.'" size="medium"/>';
		} catch (UserException $f) {}
	} catch (EntryException $e) {}
	
	echo '</div> <!-- winning_thumbnails -->';
}

echo '<div class="hint hintmargin clearboth">';
echo '<div class="hint_title">';
echo '<translate id="COMMUNITY_DESCRIPTION_HEADER">';
echo 'Description';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';
echo '<div id="community_description">';
if ($xid == 267) {
	echo '<translate id="PRIZE_COMMUNITY_DESCRIPTION">'.String::fromaform($community->getDescription()).'</translate>';
} else {
	echo String::fromaform($community->getDescription());
}
echo '</div>';

$labellist = CommunityLabelList::getByXid($xid);

if (!empty($labellist)) {

	echo '<div class="hint hintmargin">';
	echo '<div class="hint_title">';
	echo '<translate id="COMMUNITY_LABELS_HEADER">';
	echo 'Keywords';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	echo '<div id="community_labellist">';
	
	$current = 0;
	foreach ($labellist as $clid) {
		echo '<span class="community_label_view">';
		echo '<translate id="COMMUNITY_LABEL_'.$clid.'">'.$COMMUNITY_LABEL_NAME[$clid].'</translate>';
		echo '</span> <!-- community_label_view -->';
		$current++;
		if ($current != count($labellist))
			echo ' - ';
	}
	echo '</div>';

}
?>

<div class="hint hintmargin clearboth">
<div class="hint_title">
<translate id="COMMUNITY_RULES_HEADER">
Rules
</translate>
</div> <!-- hint_title -->
</div> <!-- hint -->
<div id="community_rules">
<?php
	$rules = String::fromaform($community->getRules());
	if (strcmp(trim($rules), '') != 0)
		echo $rules;
	else
		echo '<translate id="COMMUNITY_NO_RULES">The administrator hasn\'t specified any rules for this community</translate>';
?>
</div>
<div class="hint hintmargin">
<div class="hint_title">
<translate id="COMMUNITY_INFORMATION_HEADER">
Information
</translate>
</div> <!-- hint_title -->
</div> <!-- hint -->
<div id="community_details">
<translate id="COMMUNTY_SHORT_DESCRIPTION">
The main language of this community is <language_name lid="<?=$community->getLid()?>"/>, there's a new competition every <float value="<?=$community->getFrequency()?>"/> day(s) starting at <gmt_time timestamp="<?=$community->getTimeShift()?>" /> GMT.
</translate><br/>
<translate id="COMMUNITY_COMPETITION_LENGTH_DESCRIPTION">
There is a window of <float value="<?=$community->getEnterLength()?>"/> day(s) to enter each competition, immediately followed by <float value="<?=$community->getVoteLength()?>"/> day(s) of voting.
</translate>
<?php
	echo '<br/><translate id="COMMUNITY_SHORT_DESCRIPTION_THEME_COST">Suggesting a theme costs <integer value="'.$community->getThemeCost().'" /> point(s).</translate>';

	if ($community->getMaximumThemeCount() !== null)
		echo '<br/><translate id="COMMUNITY_SHORT_DESCRIPTION_THEME_COUNT">There is a maximum of <integer value="'.$community->getMaximumThemeCount().'" /> theme(s) in the suggestions queue.</translate>';
		
	if ($community->getMaximumThemeCountPerMember() !== null)
		echo '<br/><translate id="COMMUNITY_SHORT_DESCRIPTION_THEME_COUNT_PER_MEMBER">There is a maximum of <integer value="'.$community->getMaximumThemeCountPerMember().'" /> theme(s) per member in the suggestions queue.</translate>';
		
	if ($community->getThemeMinimumScore() !== null)
		echo '<br/><translate id="COMMUNITY_SHORT_DESCRIPTION_THEME_MINIMUM_SCORE">Theme suggestions whose score goes below <integer value="'.$community->getThemeMinimumScore().'" /> are automatically deleted.</translate>';
	
	if ($community->getThemeRestrictUsers()) {
		echo '<br/><translate id="COMMUNITY_SHORT_DESCRIPTION_THEME_USERS_RESTRICTED">Only the community administrator and moderators can suggest themes.</translate>';
	} else {
		echo '<br/><translate id="COMMUNITY_SHORT_DESCRIPTION_THEME_USERS_UNRESTRICTED">Anyone can suggest themes.</translate>';	
	}

echo '</div> <!-- community_details -->';

echo '</div> <!-- community_information -->';

if ($member || $user->getUid() == $community->getUid()) {
	$threadlist = DiscussionThreadList::getByXidAndStatus($xid, $DISCUSSION_THREAD_STATUS['ACTIVE']);

	if (!empty($threadlist)) {
		echo '<div class="hint hintmargin clearboth">';
		echo '<div class="hint_title">';
		echo '<a href="'.$PAGE['BOARD'].'?lid='.$user->getLid().'&xid='.$xid.'">';
		echo '<translate id="COMMUNITY_BOARD_LINK">';
		echo 'Announcements';
		echo '</translate>';
		echo '</a>';
		echo '</div> <!-- hint_title -->';
		echo '<translate id="COMMUNITY_BOARD_LINK_BODY">';
		echo 'Administrators and moderators keep you updated on the latest news. See their latest anouncement below.';
		echo '</translate>';
		echo '</div> <!-- hint -->';
		
		asort($threadlist);
		$nid = array_pop(array_keys($threadlist));
		
		$postlist = DiscussionPostList::getByNidAndStatus($nid, $DISCUSSION_POST_STATUS['POSTED']);
		
		arsort($postlist);
		$oid = array_pop(array_keys($postlist));
		
		try {
			$thread = DiscussionThread::get($nid);
			$post = DiscussionPost::get($oid);
			
			$marked = count(InsightfulMarkList::getByOid($oid));
			$style = $marked > 0?'insightful_header':'';
				
			echo '<div class="listing_item listing_overflow">';
			echo '<profile_picture class="listing_thumbnail" uid="'.$post->getUid().'" size="small"/>';
			echo '<div class="listing_header recent_header '.$style.'">';
			echo '<a href="'.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$nid.'">'.$thread->getTitle().'</a>';
			echo '</div> <!-- listing_header -->';
			echo '<div class="listing_subheader recent_subheader">';

			echo '<translate id="DISCUSS_RECENT_THREAD_HEADER">';
			echo '<duration value="'.(time() - $post->getCreationTime()).'" /> ago <user_name uid="'.$post->getUid().'"/> announced the following in <community_name link="true" xid="'.$xid.'"/>';
			echo '</translate>';
			
			echo '</div> <!-- post_header -->';
			echo '<div class="recent_post">';
			echo String::fromaform($post->getText());
			echo '</div> <!-- recent_post -->';
			echo '</div> <!-- listing_item -->';
		} catch (DiscussionPostException $e) {}
		catch (DiscussionThreadException $e) {}

	}
	
	echo '<div class="hint hintmargin ',(!empty($threadlist)?'abovemargin':''),'">';
	echo '<div class="hint_title">';
	echo '<a href="/'.String::urlify($community->getName()).'/<translate id="URL_THEMELIST" escape="urlify">Upcoming Themes</translate>/s1-l'.$user->getLid().'-x'.$xid.'">';
	echo '<translate id="COMMUNITY_THEMES_LINK">';
	echo 'View this community\'s upcoming themes';
	echo '</translate>';
	echo '</a>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	
	echo '<div class="hint hintmargin">';
	echo '<div class="hint_title">';
	echo '<a href="'.$PAGE['COMPETE'].'?lid='.$user->getLid().'&xid='.$xid.'">';
	echo '<translate id="COMMUNITY_COMPETE_LINK">';
	echo 'View this community\'s open competitions';
	echo '</translate>';
	echo '</a>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
}

echo '<div class="hint hintmargin clearboth">';
echo '<div class="hint_title">';
echo '<a href="'.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid().'&xid='.$xid.'">';
echo '<translate id="COMMUNITY_HOF_LINK">';
echo 'View this community\'s hall of fame';
echo '</translate>';
echo '</a>';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);

if ($ispremium) {	
	// Calculating average and percentile data for premium users
	
	$votelist = EntryVoteList::getByAuthorUidAndStatus($user->getUid(), $ENTRY_VOTE_STATUS['CAST']);

	$average = array();
	$percentile = array();
	$competitionendtime = array();
	
	foreach ($votelist as $eid => $votes) {
		$entry = Entry::get($eid);
		$cid = $entry->getCid();
		$rank = $entry->getRank();
	
		if ($rank !== null && $rank != 0) {
			$comp = Competition::get($cid);
			
			if ($comp->getXid() == $xid) {
				$competitionendtime[$cid] = $comp->getEndTime();
			
				if (count($votes) > 0) $average[$cid] = round(array_sum($votes) / count($votes), 2);
				else $average[$cid] = 0;
			
				$totalparticipants = $comp->getEntriesCount();
				$samerank = count(EntryList::getByCidAndRank($cid, $rank)) - 1;
				
				if ($totalparticipants > 1)
					$percentile[$cid]= 100 * round(($totalparticipants - $rank - $samerank) / ($totalparticipants - 1), 4);
				else
					$percentile[$cid]= 100;
			}
		}
	}

	$token = new Token($user->getUid());
	
	echo '<div class="hint hintmargin">';
	echo '<div class="hint_title">';
	echo '<translate id="COMMUNITY_STATISTICS">';
	echo 'Your statistics for this community';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '<translate id="HOME_STATISTICS_BODY">';
	echo 'A visual representation of your progression since you\'ve joined the website';
	echo '</translate>';
	echo '</div> <!-- hint -->';
	
	echo '<div id="statistics">';
	
	echo '<translate id="HOME_DOWNLOAD_VOTING_CSV">';
	echo '<a href="'.$REQUEST['VOTING_STATISTICS'].'?xid='.$xid.'">Download the data about your entries\' performance in CSV format</a> (can be imported into Excel and most spreadsheet software).';
	echo '</translate>';
	
	if (count($competitionendtime) > 1) {
		echo '<br/>';
		echo '<br/>';
		echo '<translate id="HOME_STATISTICS_FLASH">';
		echo 'If the charts do not appear below, you need to <a target="_blank" href="http://get.adobe.com/flashplayer/">download and install the latest version of the Adobe Flash Player plugin</a>.';
		echo '</translate>';

		echo '<div class="statistics_percentile">';
		echo '<div id="statistics_percentile_chart">';
		open_flash_chart_object(936, 250, $REQUEST['PERCENTILE_CHART_DATA'].'?xid='.$xid.'&token='.$token->getHash(), false);
		echo '</div> <!-- statistics_percentile_chart -->';
	
		echo '<span class="chart_explanation">';
		echo '<translate id="HOME_STATISTICS_PERCENTILE">';
		echo 'The chart above shows the percentage of participants who are ranked below you in the last 100 competitions you\'ve entered.<br/>';
		echo '100% is when you did the best performance possible (being the only person ranked 1st).';
		echo '</translate>';
		echo '</span>';
	
		echo '</div> <!-- statistics_percentile -->';
		
		echo '<div class="statistics_average">';
		echo '<div id="statistics_average_chart">';
		open_flash_chart_object(930, 250, $REQUEST['AVERAGE_CHART_DATA'].'?xid='.$xid.'&token='.$token->getHash(), false);
		echo '</div> <!-- statistics_percentile_chart -->';
	
		echo '<span class="chart_explanation">';
		echo '<translate id="HOME_STATISTICS_AVERAGE">';
		echo 'The chart above shows the average score (amount of stars) of your entries in the last 100 competitions you\'ve entered.';
		echo '</translate>';
		echo '</span>';
	
		echo '</div> <!-- statistics_average -->';
		
		echo '</div> <!-- statistics -->';
	} else {
		echo '<br/>';
		echo '<br/>';
		echo '<translate id="HOME_STATISTICS_NO_DATA">';
		echo 'There isn\'t enough data about your entries yet for the statistics charts to be displayed. They will appear once at least two of your entries have been ranked.';
		echo '</translate>';
		
		echo '</div> <!-- statistics -->';
	}
}

$memberlist = CommunityMembershipList::getByXidAndStatus($xid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);



foreach ($moderatorlist as $uid) unset($memberlist[$uid]);	

if (isset($memberlist[$GOOGLE_UID])) unset($memberlist[$GOOGLE_UID]);

if ($user->getStatus() == $USER_STATUS['BANNED']) $memberlist += CommunityMembershipList::getByXidAndStatus($xid, $COMMUNITY_MEMBERSHIP_STATUS['BANNED']);
asort($memberlist);

$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;

$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'COMMUNITY_MEMBERS');

$page_count = ceil(count($memberlist) / $amount_per_page);

$pagedmemberlist = array_slice($memberlist, ($page_offset - 1) * $amount_per_page, $amount_per_page, true);

echo '<div class="hint '.($page_count > 1?'':'hintmargin ').'clear_both">';
echo '<div class="hint_title">';
echo '<translate id="COMMUNITY_MEMBERS_HEADER">';
echo '<integer value="'.count($memberlist).'"/> members in this community';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

function RenderCommunityMembersLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$_REQUEST['xid'].'&page='.$i.'#community_members">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderCommunityMembersLink');

echo '<div id="community_members">';

foreach ($pagedmemberlist as $uid => $join_time) echo '<profile_picture class="member_thumbnail" uid="'.$uid.'" size="small"/>';

echo '</div> <!-- community_members -->';

echo UI::RenderPaging($page_offset, $page_count, 'RenderCommunityMembersLink', true);

echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
echo '<div id="members_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="COMMUNITY_MEMBERS_PAGING_PLURAL">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> members per page.';
	echo '</translate>';
} else {
	echo '<translate id="COMMUNITY_MEMBERS_PAGING_SINGULAR">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> member per page.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="members_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changeMembersAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="members_change_input" style="display:none">';
echo '<translate id="COMMUNITY_MEMBERS_PAGING_AMOUNT">';
echo 'Display <input id="members_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> members per page. <a href="javascript:saveMembersAmount()">Save</a> <a href="javascript:cancelMembersAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>