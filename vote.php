<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Where users pick the competition they're about to vote on
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/competitionlist.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/entryvotelist.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());
			
$ispremium  = in_array($USER_LEVEL['PREMIUM'], $levels);

$page = new Page('VOTE', 'COMPETITIONS', $user);

$page->setTitle('<translate id="VOTE_PAGE_TITLE">Competitions open for voting on inspi.re</translate>');

$page->startHTML();

function RenderCompetition($competition, $first) {
	global $user;
	global $PAGE;
	global $ENTRY_STATUS;
	global $USER_STATUS;
	
	$theme = Theme::get($competition->getTid());
	$community = Community::get($competition->getXid());
	$entries = EntryList::getByCidAndStatus($competition->getCid(), $ENTRY_STATUS['POSTED']);
	
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
	
	if ($user->getStatus() == $USER_STATUS['BANNED'])
		$entries += EntryList::getByCidAndStatus($competition->getCid(), $ENTRY_STATUS['BANNED']);
		
	$entries_count = count($entries);
	
	$votelist = EntryVoteList::getByUidAndCid($user->getUid(), $competition->getCid());
	$votecount = count($votelist);
	
	echo '<div class="'.($first?'marginless_item':'listing_item').'">';
	echo '<picture href="'.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$competition->getXid().'" category="community" class="listing_thumbnail" size="small" '.($community->getPid() === null?'':'pid="'.$community->getPid().'"').' />';
	echo '<div class="'.($votecount > 0?'voted_header':'listing_header').'">';
	
	if ($community->getXid() == 267) {
		echo '<a href="',$PAGE['GRID'].'?cid='.$competition->getCid(),'"><translate id="PRIZE_COMMUNITY_THEME_TITLE'.$competition->getTid().'">'.$theme->getTitle().'</translate></a> ';
	} else {
		echo '<theme_title href="',$PAGE['GRID'].'?cid='.$competition->getCid(),'" tid="',$competition->getTid(),'"/> ';
	}

	$votablecount = $entries_count - count($ownentry);

	echo '<span class="vote_quantity">';
	if ($votecount == 0) {
		echo '<translate id="VOTE_QUANTITY_NONE">';
		echo 'You have yet to vote on any of this competition\'s entries';
		echo '</translate>';
	} elseif ($votablecount > 0) {
		if ($votecount > $votablecount) $votecount = $votablecount;
		echo '<translate id="VOTE_QUANTITY">';
		echo 'You\'ve voted on <float value="'.round(100 * ($votecount / $votablecount), 2).'"/>% of this competition\'s entries';
		echo '</translate>';
	}

	echo '</span> <!-- vote_quantity -->';

	echo '</div> <!-- listing_header -->';
	echo '<div class="listing_subheader">';
	echo '<translate id="VOTE_LIST_SUBHEADER">';
	echo 'Suggested by <user_name uid="'.$theme->getUid().'"/> for <community_name link="true" xid="'.$competition->getXid().'"/>. ';
	echo '<duration value="'.($competition->getEndTime() - gmmktime()).'"/> left to vote on this competition.';
	echo '</translate> ';
	if ($entries_count == 0) {
		echo '<translate id="COMPETE_LIST_SUBHEADER_2_NONE">';
		echo 'No entry has been submitted yet.';
		echo '</translate>';
	} elseif ($entries_count == 1) {
		echo '<translate id="COMPETE_LIST_SUBHEADER_2_SINGULAR">';
		echo '1 entry has been submitted.';
		echo '</translate>';
	} else {
		echo '<translate id="COMPETE_LIST_SUBHEADER_2_PLURAL">';
		echo '<integer value="'.$entries_count.'"/> entries have been submitted.';
		echo '</translate>';
	}
	
	echo '</div> <!-- listing_subheader -->';
	echo '<div class="listing_content">';
	
	if ($community->getXid() == 267) {
		 echo '<translate id="PRIZE_COMMUNITY_THEME_DESCRIPTION'.$competition->getTid().'">'.$theme->getDescription().'</translate>';
	} else {
		 echo String::fromaform($theme->getDescription());
	}
	echo '</div> <!-- listing_content -->';
	if (!empty($entries)) {
		echo '<div class="listing_content">';
		
		$uids = array_rand($entries, min(13, $entries_count));
		if (!is_array($uids)) $uids = array($uids);
		
		$eidlist = array();
		foreach ($uids as $uid) $eidlist []= $entries[$uid];
		$entrylist = Entry::getArray($eidlist);
		
		foreach ($entrylist as $eid => $entry) {
			$eid = $entry->getEid();
			$token = new Token($user->getUid().'-'.$eid);
			echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#token='.$token->getHash().'" class="entry_preview" pid="'.$entry->getPid().'" size="small"/>';
		}
		echo '</div> <!-- listing_content -->';
	}
	
	echo '</div> <!-- listing_item -->';
}

?>

<div class="hint hintmargin">
<div class="hint_title">
<translate id="VOTE_HINT_TITLE">
Competitions you can vote on
</translate>
</div> <!-- hint_title -->
<translate id="VOTE_HINT_BODY">
Below is the list of competitions currently at the voting stage for all the communities you're a member of
</translate>
</div> <!-- hint -->

<?php

$communitylist = $user->getCommunityList();

echo '<div class="competition_list">';

$competitionlist = array();

if (empty($communitylist)) {
	echo '<div class="marginless_item">';
	echo '<div class="listing_header">';
	echo '<translate id="VOTE_NO_COMMUNITIES">';
	echo 'You\'re not a member of any community yet. You must first <a href="'.$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid().'">join a community</a> before you can vote on entries.';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else foreach ($user->getCommunityList() as $xid) {
	$competitionlist += CompetitionList::getByXidAndStatus($xid, $COMPETITION_STATUS['VOTING']);
}

if (!empty($communitylist) && empty($competitionlist)) {
	echo '<div class="marginless_item">';
	echo '<div class="listing_header">';
	echo '<translate id="VOTE_NO_OPEN_COMPETITIONS">';
	echo 'There are currently no competitions open for voting in your communities. You can <a href="'.$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid().'">join more communities</a> if you like.';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} elseif (!empty($communitylist)) {
	$competition = array();
	$endTimeList = array();
	
	$competition = Competition::getArray(array_keys($competitionlist));
	
	foreach ($competition as $cid => $comp) {
		$endtime = $comp->getEndTime();
		if ($endtime >= gmmktime())
		$endTimeList[$cid] = $endtime;
		
		if ($competition[$cid]->getXid() == 267) {
			$endTimeList[$cid] = gmmktime();
		}
	}
	
	asort($endTimeList);
	
	$cids = array_keys($endTimeList);
	if (count($cids) > 4) {
		$insert_ad_cid = $cids[round(count($cids) / 2)];
	} else $insert_ad_cid = 0;
	
	$first = false;
	foreach ($endTimeList as $cid => $end_time) {
		RenderCompetition($competition[$cid], $first);
		if ($first) $first = false;
		
		if ($cid == $insert_ad_cid && (!$ispremium || !$user->getHideAds())) {
			echo '</div> <!-- competition_list -->';
			echo '<ad ad_id="VOTE"/>';
			echo '<div class="competition_list">';
			$first = true;
		}
	}
}

?>

</div> <!-- competition_list -->

<?php
$page->endHTML();
$page->render();
?>
