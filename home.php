<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Main page, shall contain a user's current and past entries when logged in
*/

require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/entryvotelist.php');
require_once(dirname(__FILE__).'/entities/favoritelist.php');
require_once(dirname(__FILE__).'/entities/privatemessagelist.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/entities/userpaging.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

require_once(dirname(__FILE__).'/libraries/open_flash_chart_object.php');

$user = User::getSessionUser();
if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['INDEX'].'?lid='.$user->getLid());
	exit(0);
}

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page = new Page('HOME', 'HOME', $user);

$page->setTitle('<translate id="HOME_PAGE_TITLE">Your entries on inspi.re</translate>');

$page->addHeadJavascript('SWFOBJECT');

$page->addJavascriptVariable('reload_url', $PAGE['HOME'].'?lid='.$user->getLid());
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);
$page->addJavascriptVariable('request_update_private_message_status', $REQUEST['UPDATE_PRIVATE_MESSAGE_STATUS']);
$page->addJavascriptVariable('open_text', 
							 '<translate id="HOME_PRIVATE_MESSAGE_OPEN" escape="htmlentities">Open</translate>');
$page->addJavascriptVariable('close_text', 
							 '<translate id="HOME_PRIVATE_MESSAGE_CLOSE" escape="htmlentities">Close</translate>');

// Check if people are refreshing the home page like maniacs

$onlyhome = 0;
$previouspage = $_SERVER['REQUEST_URI'];
foreach ($user->getRecentlyVisitedPages() as $time => $page_name) {
	if (strcasecmp($page_name, $previouspage) == 0) $onlyhome++;
	if ($time < time() - 600) $onlyhome = -10;
	$previouspage = $page_name;
}

if ($onlyhome == 5) $page->addJavascriptVariable('home_obsessed', true);

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

$entrylist = EntryList::getByUidAndStatus($user->getUid(), $status);
$entrylist += EntryList::getByUidAndStatus($user->getUid(), $ENTRY_STATUS['DELETED']);
$entrylist += EntryList::getByUidAndStatus($user->getUid(), $ENTRY_STATUS['DISQUALIFIED']);

$competitionlist = array();

$competition = Competition::getArray(array_keys($entrylist));

foreach ($competition as $cid => $comp) {
	$competitionlist[$cid] = $comp->getEndTime();
}

arsort($competitionlist);

// Calculating average and percentile data for premium users

$votelist = EntryVoteList::getByAuthorUidAndStatus($user->getUid(), $ENTRY_VOTE_STATUS['CAST']);

$average = array();
$percentile = array();
$competitionendtime = array();

$voteentry = Entry::getArray(array_keys($votelist));

foreach ($votelist as $eid => $votes) if (isset($voteentry[$eid])) {
	$entry = $voteentry[$eid];
	$cid = $entry->getCid();
	$rank = $entry->getRank();

	if ($rank !== null && $rank != 0 && isset($competition[$cid])) {
		$comp = $competition[$cid];
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

// Handling paging of the entries

$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;

$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'HOME_ENTRIES');

$page_count = ceil(count($competitionlist) / $amount_per_page);

$competitionlist = array_slice($competitionlist, ($page_offset - 1) * $amount_per_page, $amount_per_page, true);

$page->startHTML();

echo '<div id="too_much_refresh" class="warning highlight_item hintmargin" style="display: none">';
echo '<div class="warning_title">';
echo '<translate id="HOME_TOO_MUCH_REFRESH_TITLE">';
echo 'Maybe you\'re checking your own scores too much (based on how often you refresh this page)';
echo '</translate>';
echo '</div> <!-- warning_title -->';
echo '<translate id="HOME_TOO_MUCH_REFRESH_BODY">';
echo 'Why not vote on some entries, so that others can see their own scores move too?';
echo '</translate>';
echo '</div> <!-- warning -->';

echo '<div id="entries_header" class="hint '.($page_count > 1?'':' hintmargin').'">';
echo '<div class="hint_title">';
echo '<translate id="HOME_HINT_TITLE">';
echo 'Your entries';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="HOME_HINT_BODY">';
echo 'All the artworks you\'ve entered in current and past competitions';
echo '</translate>';
echo '</div> <!-- hint -->';

function RenderHomeLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['HOME'].'?lid='.$user->getLid().'&page='.$i.(isset($_REQUEST['favpage'])?'&favpage='.$_REQUEST['favpage']:'').(isset($_REQUEST['pmpage'])?'&pmpage='.$_REQUEST['pmpage']:'').'#entries_header">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderHomeLink');

echo '<ad ad_id="HOME_BOTTOM"/>';

if (empty($competitionlist)) {
	echo '<div class="listing_item">';
	echo '<div class="listing_header">';
	echo '<translate id="HOME_NO_COMPETITION">';
	echo 'You haven\'t entered any competition yet. If you haven\'t done so already, you should start by <a href="'.$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid().'">joining at least one community</a>.';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else {
	echo '<div id="entry_list">';
	
	$entrycache = Entry::getArray(array_values($entrylist));

	foreach ($competitionlist as $cid => $end_time) if (isset($entrycache[$entrylist[$cid]])) {
		$entry = $entrycache[$entrylist[$cid]];
		
		if ($competition[$cid]->getStatus() == $COMPETITION_STATUS['CLOSED']) {
			$link = $PAGE['ENTRY'].'?lid='.$user->getLid().'&home=true#eid='.$entrylist[$cid];
		} else {
			$token = new Token($entry->getUid().'-'.$entry->getEid());
			$link = $PAGE['ENTRY'].'?lid='.$user->getLid().'&home=true#token='.$token->getHash();
		}
		
		$theme = Theme::get($competition[$cid]->getTid());
		
		echo '<div class="listing_item clearboth">';
		echo '<div class="listing_thumbnail">';
		echo '<picture href="'.$link.'" size="small" pid="'.$entry->getPid().'"/>';
		echo '</div> <!-- listing_thumbnail -->';
		echo '<div class="listing_header">';
		if ($competition[$cid]->getXid() == 267) {
			echo '<a href="',$link,'"><translate id="PRIZE_COMMUNITY_THEME_TITLE'.$competition[$cid]->getTid().'">'.$theme->getTitle().'</translate></a> ';
		} else {
			echo '<theme_title href="'.$link.'" tid="'.$competition[$cid]->getTid().'"/>';
		}
		if ($entry->getStatus() == $ENTRY_STATUS['DISQUALIFIED']) {
			echo ' (';
			echo '<translate id="HOME_DISQUALIFIED">';
			echo 'entry disqualified';
			echo '</translate>';
			echo ')';
		}
		echo '</div> <!-- listing_header -->';
		
		
		
		echo '<div class="listing_subheader">';
		if ($competition[$cid]->getStatus() == $COMPETITION_STATUS['OPEN']) {
			echo '<translate id="COMPETITION_SHORT_DESCRIPTION_OPEN">';
			echo 'Suggested by <user_name uid="'.$theme->getUid().'"/> for <community_name link="true" xid="'.$competition[$cid]->getXid().'"/>. <duration value="'.($competition[$cid]->getVoteTime() - gmmktime()).'"/> left to enter this competition.';
			echo '</translate>';
		} elseif ($competition[$cid]->getStatus() == $COMPETITION_STATUS['VOTING']) {
			echo '<translate id="GRID_VOTE_SUBHEADER">';
			echo 'Suggested by <user_name uid="'.$theme->getUid().'"/> for <community_name link="true" xid="'.$competition[$cid]->getXid().'"/>. <duration value="'.($competition[$cid]->getEndTime() - gmmktime()).'"/> left to vote on this competition.';
			echo '</translate>';
		} else {
			echo '<translate id="COMPETITION_SHORT_DESCRIPTION_CLOSED">';
			echo 'Suggested by <user_name uid="'.$theme->getUid().'"/> for <community_name link="true" xid="'.$competition[$cid]->getXid().'"/>. This competition closed <duration value="'.(gmmktime() - $competition[$cid]->getEndTime()).'"/> ago.';
			echo '</translate>';
		}
		echo '</div> <!-- listing_subheader -->';
		
		if ($competition[$cid]->getStatus() != $COMPETITION_STATUS['OPEN']) {
			if ($competition[$cid]->getStatus() == $COMPETITION_STATUS['VOTING'])
				$votes = EntryVoteList::getByEidAndStatusAndCreationTime($entry->getEid(), $ENTRY_VOTE_STATUS['CAST'], time() - 7200);
			else
				$votes = EntryVoteList::getByEidAndStatus($entry->getEid(), $ENTRY_VOTE_STATUS['CAST']);
			$amount = count($votes);
			$score = array_sum($votes);
			if ($amount > 1) {
				echo '<translate id="HOME_ENTRY_VOTES_PLURAL">';
				echo '<integer value="'.$amount.'"/> votes were cast on this entry, bringing it to a total score of <integer value="'.$score.'" />.';
				echo '</translate>';
			} elseif ($amount == 1) {
				echo '<translate id="HOME_ENTRY_VOTES_SINGULAR">';
				echo '1 vote was cast on this entry, bringing it to a total score of <integer value="'.$score.'" />.';
				echo '</translate>';
			} else {
				echo '<translate id="HOME_ENTRY_VOTES_NULL">';
				echo 'No vote was cast on this entry yet.';
				echo '</translate>';
			}
			
			if ($ispremium && isset($average[$cid])) {
				echo ' <translate id="HOME_ENTRY_AVERAGE">';
				echo 'It received an average of <float value="'.$average[$cid].'"/> star(s).';
				echo '</translate> ';
			}
			echo '<br/>';
		}
		
		if ($competition[$cid]->getStatus() == $COMPETITION_STATUS['CLOSED'] && $entry->getStatus() != $ENTRY_STATUS['DISQUALIFIED']) {
			$amount = $competition[$cid]->getEntriesCount();
			
			if ($user->getStatus() == $USER_STATUS['BANNED']) {
				$rank = $entry->getBannedRank();
				if ($rank > $amount) $amount = $rank;
			} else $rank = $entry->getRank();

			echo '<translate id="HOME_ENTRY_RANK">';
			echo 'It ranked <b><rank value="'.$rank.'"/> out of <integer value="'.$amount.'" /></b>.';
			echo '</translate> ';
		}
		

		
		$favoritelist = FavoriteList::getByEid($entrylist[$cid]);
		$favoritecount = count($favoritelist);
		if ($favoritecount == 1) {
			echo '<translate id="HOME_ENTRY_FAVORITED_SINGULAR">';
			echo '1 person has this entry in his/her favorites.';
			echo '</translate> ';
		} elseif ($favoritecount > 1) {
			echo '<translate id="HOME_ENTRY_FAVORITED_PLURAL">';
			echo '<integer value="'.$favoritecount.'"/> people have this entry in their favorites.';
			echo '</translate> ';
		}
		
		echo '</div> <!-- listing_item -->';
	}
	
	echo '</div> <!-- entry_list -->';
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderHomeLink', true);
echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
echo '<div id="entries_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="HOME_BOTTOM_BODY_PLURAL">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> entries per page.';
	echo '</translate>';
} else {
	echo '<translate id="HOME_BOTTOM_BODY_SINGULAR">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> entry per page.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="entries_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changeEntriesAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="entries_change_input" style="display:none">';
echo '<translate id="HOME_ENTRIES_INPUT_AMOUNT">';
echo 'Display <input id="home_entries_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> entries per page. <a href="javascript:saveEntriesAmount()">Save</a> <a href="javascript:cancelEntriesAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

if ($ispremium) {	
	$token = new Token($user->getUid());
	
	echo '<div class="hint hintmargin '.($hideads?'abovemargin':'').'">';
	echo '<div class="hint_title">';
	echo '<translate id="HOME_STATISTICS">';
	echo 'Your statistics';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '<translate id="HOME_STATISTICS_BODY">';
	echo 'A visual representation of your progression since you\'ve joined the website';
	echo '</translate>';
	echo '</div> <!-- hint -->';
	
	echo '<div id="statistics">';
	
	echo '<translate id="HOME_DOWNLOAD_VOTING_CSV">';
	echo '<a href="'.$REQUEST['VOTING_STATISTICS'].'">Download the data about your entries\' performance in CSV format</a> (can be imported into Excel and most spreadsheet software).';
	echo '</translate>';
	
	if (count($competitionendtime) > 1) {
		echo '<br/>';
		echo '<br/>';
		echo '<translate id="HOME_STATISTICS_FLASH">';
		echo 'If the charts do not appear below, you need to <a target="_blank" href="http://get.adobe.com/flashplayer/">download and install the latest version of the Adobe Flash Player plugin</a>.';
		echo '</translate>';

		echo '<div class="statistics_percentile">';
		echo '<div id="statistics_percentile_chart">';
		open_flash_chart_object(936, 250, $REQUEST['PERCENTILE_CHART_DATA'].'?token='.$token->getHash(), false);
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
		open_flash_chart_object(930, 250, $REQUEST['AVERAGE_CHART_DATA'].'?token='.$token->getHash(), false);
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

$page->endHTML();
$page->render();
?>
