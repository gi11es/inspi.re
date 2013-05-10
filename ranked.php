<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Display the HOF entries ordered by rank
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitymoderator.php');
require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/competitionlist.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/entryvotelist.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
$uid = $user->getUid();

$cid = isset($_REQUEST['cid'])?$_REQUEST['cid']:null;

if ($cid === null) {
	header('Location: '.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid());
	exit(0);
}

try {
	$competition = Competition::get($cid);
} catch (CompetitionException $e) {
	header('Location: '.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid());
	exit(0);
}

if ($competition->getStatus() != $COMPETITION_STATUS['CLOSED']) {
	header('Location: '.$PAGE['VOTE'].'?lid='.$user->getLid());
	exit(0);
}

try {
	$community = Community::get($competition->getXid());
} catch (CommunityException $e) {
	header('Location: '.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid());
	exit(0);
}

$theme = Theme::get($competition->getTid());

$page = new Page('HALL_OF_FAME', 'COMPETITIONS', $user);

$page->setTitle('<translate id="RANKED_PAGE_TITLE">Rankings in the "<string value="'.String::fromaform($theme->getTitle()).'"/>" competition of the <string value="'.String::fromaform($community->getName()).'"/> community on inspi.re</translate>');

$page->startHTML();

$page->addStyle('GRID');
$page->addRSS($RSS['COMPETITION'].'?cid='.$cid.'&uid='.$uid);


echo UI::RenderCompetitionShortDescription($user, $competition, $PAGE['GRID'].'?cid='.$cid, true);

$entrylist = Entrylist::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['POSTED']);
$entrylist = array_merge($entrylist, Entrylist::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['DELETED']));

if ($user->getStatus() == $USER_STATUS['BANNED']) {
	$entrylist = array_merge($entrylist, array_values(Entrylist::getByCidAndStatus($cid, $ENTRY_STATUS['BANNED'])));
	$bannedranks = $competition->getBannedRanks();
	$entriesamount = count($entrylist);
} else $entriesamount = $competition->getEntriesCount();

if (empty($entrylist)) {
	echo '<div class="listing_item">',
		 '<div class="listing_header">',
		 '<translate id="GRID_VOTE_NO_ENTRIES">',
		 'There were no entries in this competition. Nothing to vote on, unfortunately!',
		 '</translate>',
		 '</div> <!-- listing_header -->',
		 '</div> <!-- listing_item -->';
} else {
	$rankedlist = array();
	$unorderedlist = array();
	$entry = array();
	$author = array();
	
	foreach ($entrylist as $author_uid => $eid) {
		try {
			$entry[$eid] = Entry::get($eid);
			try {
				$author[$eid] = User::get($entry[$eid]->getUid());
			} catch (UserException $e) {
				$author[$eid] = null;
			}
			
			if ($user->getStatus() == $USER_STATUS['BANNED']) $rank = $bannedranks[$eid];
			else $rank = $entry[$eid]->getRank();
			
			if ($author[$eid] == null || $rank < 4 || $author[$eid]->getDisplayRank()) {
				$rankedlist[$eid] = $rank;
			} else $unorderedlist[]= $eid;
		} catch (EntryException $e) {}
	}
	
	if (!empty($rankedlist)) {
		echo '<div class="hint">',
			 '<div class="hint_title">',
			 '<translate id="RANKED_ORDERED_TITLE">',
			 'Ordered entries',
			 '</translate>',
			 '</div> <!-- hint_title -->',
			 '<translate id="RANKED_ORDERED_BODY">',
			 'The rank of these artworks is public. They either ranked in the top 3 or their author decided to share their rank publicly.',
			 '</translate>',
			 '</div> <!-- hint -->',
		
			 '<div id="ordered_list">';
		asort($rankedlist);
		foreach ($rankedlist as $eid => $rank) {
			$pid = $entry[$eid]->getPid();
			$author_uid = $entry[$eid]->getUid();
			$votes = EntryVoteList::getByEidAndStatus($eid, $ENTRY_VOTE_STATUS['CAST']);
			
			$own_vote = 0;
			try {
				$own_vote = EntryVote::get($eid, $uid);
				$own_vote = $own_vote->getPoints();
			} catch (EntryVoteException $e) {}
			
			$amount = count($votes);
			$score = array_sum($votes);
			
			echo '<div class="ranked_item_container">',
				 '<picture class="ranked_picture clearboth" href="',$PAGE['ENTRY'],'?lid='.$user->getLid().'#eid='.$eid.'" '.($pid === null?'':'pid="'.$pid.'"').' size="small"/>',
				 '<div class="rank"><rank value="',$rank,'"/></div>',
			
				 '<profile_picture class="ranked_picture" uid="',$author_uid,'" size="small"/>',
				 '<div class="listing_item ranked_item">',
				 '<div class="listing_header">',
				 '<translate id="RANKED_AUTHOR_HEADER">',
				 '<user_name uid="',$author_uid,'"/> entered this artwork into the competition',
				 '</translate>',
				 '</div> <!-- listing_header -->',
				 '<div class="ranked_content">',
				 '<translate id="RANKED_SCORE_DETAILS">',
				 'It ranked <rank value="',$rank,'"/> out of <integer value="',$entriesamount,'"/>, thanks to <integer value="',$amount,'"/> vote(s) totalling <integer value="',$score,'"/> point(s).',
				 '</translate> ';
			
			if ($own_vote == 0) {
				echo '<translate id="RANKED_SCORE_DETAILS_OWN_VOTE_NULL">',
					 'You didn\'t vote on it.',
					 '</translate>';
			} else {
				echo '<translate id="RANKED_SCORE_DETAILS_OWN_VOTE">',
					 'You gave it <integer value="',$own_vote,'"/> star(s).',
					 '</translate>';
			}
			
			echo '</div> <!-- ranked_content -->',
				 '</div> <!-- listing_item -->',
				 '</div> <!-- ranked_item_container -->';
		}
		echo '</div> <!-- ordered_list -->';
	}
	
	if (!empty($unorderedlist)) {
		echo '<div class="hint hintmargin">',
			 '<div class="hint_title">',
			 '<translate id="RANKED_UNORDERED_TITLE">',
			 'Unordered entries',
			 '</translate>',
			 '</div> <!-- hint_title -->',
			 '<translate id="RANKED_UNORDERED_BODY">',
			 'The rank of these artworks hasn\'t been made public',
			 '</translate>',
			 '</div> <!-- hint -->',
			 '<div class="small_grid">';
		
		foreach ($unorderedlist as $eid) {
			$pid = $entry[$eid]->getPid();
			
			echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'" class="picture_grid" '.($pid === null?'':'pid="'.$pid.'"').' size="small"/>';	
		}
		
		echo '</div> <!-- small_grid -->';
	}
	
	try {
		$moderator = CommunityModerator::get($competition->getXid(), $uid);
		$ismoderator = true;
	} catch (CommunityModeratorException $e) {
		$ismoderator = false;
	}
	
	// Show the disqualified entries if we have the right to disqualify/requalify them
	if ($uid == $community->getUid() || $ismoderator) {
		$entrylist = Entrylist::getByCidAndStatus($cid, $ENTRY_STATUS['DISQUALIFIED']);
		if (!empty($entrylist)) {
			echo '<div class="hint hintmargin">',
				 '<div class="hint_title">',
				 '<translate id="GRID_DISQUALIFIED">',
				 'Disqualified entries',
				 '</translate>',
				 '</div> <!-- hint_title -->',
				 '</div> <!-- hint -->',
			
				 '<div class="small_grid">';
		
			foreach ($entrylist as $uid => $eid) try {
				$entry = Entry::get($eid);
				$pid = $entry->getPid();
				
				echo '<picture href="',$PAGE['ENTRY'],'?lid=',$user->getLid(),'#eid=',$eid,'" class="picture_grid" ',($pid === null?'':'pid="'.$pid.'"'),' size="small"/>';	
			} catch (EntryException $e) {}
			
			echo '</div> <!-- small_grid -->';
		}
	}
}

?>

<?php
$page->endHTML();
$page->render();
?>
