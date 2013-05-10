<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Where users pick the competition they're about to vote on
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitymoderator.php');
require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/competitionlist.php');
require_once(dirname(__FILE__).'/entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/image.php');
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
	header('Location: '.$PAGE['VOTE'].'?lid='.$user->getLid());
	exit(0);
}

try {
	$competition = Competition::get($cid);
} catch (CompetitionException $e) {
	header('Location: '.$PAGE['VOTE'].'?lid='.$user->getLid());
	exit(0);
}


try {
	$community = Community::get($competition->getXid());
} catch (CommunityException $e) {
	header('Location: '.$PAGE['VOTE'].'?lid='.$user->getLid());
	exit(0);
}

try {
	$moderator = CommunityModerator::get($competition->getXid(), $uid);
	$ismoderator = true;
} catch (CommunityModeratorException $e) {
	$ismoderator = false;
}

$levels = UserLevelList::getByUid($user->getUid());
$isrolemodel = in_array($USER_LEVEL['ROLE_MODEL'], $levels);

if ($competition->getStatus() == $COMPETITION_STATUS['OPEN'] && $community->getUid() != $uid && !$ismoderator) {
	header('Location: '.$PAGE['VOTE'].'?lid='.$user->getLid());
	exit(0);
}

$theme = Theme::get($competition->getTid());

if ($competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
	$page = new Page('VOTE', 'COMPETITIONS', $user);
	$page->startHTML();
	echo '<div class="hint hintmargin">';
	echo '<div class="hint_title">';
	echo '<translate id="GRID_VOTE_HINT_TITLE">';
	echo 'Entries you can vote on for this competition';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '<translate id="GRID_VOTE_HINT_BODY">';
	echo 'Pick any of the entries below and vote or critique it';
	echo '</translate>';
	echo '</div> <!-- hint -->';
	
} elseif ($competition->getStatus() == $COMPETITION_STATUS['OPEN']) {
	$page = new Page('COMPETE', 'COMPETITIONS', $user);
	$page->startHTML();
} else {
	$page = new Page('HALL_OF_FAME', 'COMPETITIONS', $user);
	$page->startHTML();
}

$page->setTitle('<translate id="GRID_PAGE_TITLE">Entries in the "<string value="'.String::fromaform($theme->getTitle()).'"/>" competition of the <string value="'.String::fromaform($community->getName()).'"/> community on inspi.re</translate>');

$page->addStyle('GRID');
$page->addRSS($RSS['COMPETITION'].'?cid='.$cid.'&uid='.$uid);


echo UI::RenderCompetitionShortDescription($user, $competition, $PAGE['GRID'].'?cid='.$cid, true);

$entrylist = EntryList::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['POSTED']);
$entrylist = array_merge($entrylist, EntryList::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['DELETED']));

if ($uid == $community->getUid() || $ismoderator)  $entrylist = array_merge($entrylist, EntryList::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['DISQUALIFIED']));

// Banned users should be led to believe that their entries are still there
if ($user->getStatus() == $USER_STATUS['BANNED'])
	$entrylist = array_merge($entrylist, EntryList::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['BANNED']));


if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	$extraentrylist = EntryList::getByUidAndCidAndStatus($uid, $cid, $ENTRY_STATUS['ANONYMOUS']);
	if (!isset($entrylist[$uid])) $entrylist[$uid] = array_shift($extraentrylist);
}

$entries = array();
$sortedentries = array();

$nocommententries = array();
if ($isrolemodel) $commentneededlist = UserList::getByLessThan5CommentsReceived();
else $commentneededlist = array();

$entries = Entry::getArray(array_values($entrylist));

foreach ($entrylist as $uid => $eid) if (isset($entries[$eid])) {
	$sortedentries[$eid] = $entries[$eid]->getCreationTime();
	
	// Make a separate list for people who haven't received enough comments yet
	if ($isrolemodel && in_array($entries[$eid]->getUid(), $commentneededlist))
		$nocommententries[$eid] = $entries[$eid]->getCreationTime();
}

if ($uid == $community->getUid() || $ismoderator) asort($sortedentries);

echo '<div class="grid">';

if (empty($entries)) {
	echo '<div class="listing_item">';
	echo '<div class="listing_header">';
	echo '<translate id="GRID_VOTE_NO_ENTRIES">';
	echo 'There were no entries in this competition. Nothing to vote on, unfortunately!';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} elseif ($uid == $community->getUid() || $ismoderator) foreach ($sortedentries as $eid => $creation_time) {
	$entry = $entries[$eid];
	$pid = $entry->getPid();
	$disqualified = ($entry->getStatus() == $ENTRY_STATUS['DISQUALIFIED']);
	
	if ($competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
		$token = new Token($user->getUid().'-'.$eid);
		if ($disqualified) {
			echo '<div class="disqualified_thumbnail picture_grid" title="<translate id="GRID_DISQUALIFIED_ENTRY">Disqualified entry</translate>">';
			echo '<a href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#token='.$token->getHash().'">';
			echo '<img class="disqualification_overlay" src="'.Image::getDisqualificationOverlay($user).'"/>';
			echo '<picture class="picture_disqualified" '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
			echo '</a>';
			echo '</div> <!-- disqualified_thumbnail -->';
		} else
			echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#token='.$token->getHash().'" class="picture_grid" '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
	} else {
		echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'" class="picture_grid'.($disqualified?' picture_disqualified" title="<translate id="GRID_DISQUALIFIED_ENTRY">Disqualified entry</translate>"':'"').' '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
	}
} else foreach ($entrylist as $eid) if (isset($entries[$eid])) {
	$entry = $entries[$eid];
	$pid = $entry->getPid();
	
	if ($competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
		$token = new Token($user->getUid().'-'.$eid);
		echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#token='.$token->getHash().'" class="picture_grid" '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
	} else {
		echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'" class="picture_grid" '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
	}
}

echo '</div> <!-- grid -->';

if ($uid == $community->getUid() || $ismoderator)  asort($sortedentries);
if ($isrolemodel && !empty($nocommententries)) {
	asort($nocommententries);
	
	echo '<div class="hint hintmargin">';
	echo '<div class="hint_title">';
	echo '<translate id="GRID_NO_COMMENTS">';
	echo 'Entries from people who\'ve received less than <integer value="5"/> comments ever';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	
	echo '<div class="grid">';
	foreach ($nocommententries as $eid => $creation_time) {
		$entry = $entries[$eid];
		$pid = $entry->getPid();
		
		if ($competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
			$token = new Token($user->getUid().'-'.$eid);
			echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#token='.$token->getHash().'" class="picture_grid" '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
		} else {
			echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'" class="picture_grid" '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
		}
	}	
	echo '</div> <!-- grid -->';
}


// Show the disqualified entries if we have the right to disqualify/requalify them
/*if ($uid == $community->getUid() || $ismoderator) {
	$entrylist = Entrylist::getByCidAndStatus($cid, $ENTRY_STATUS['DISQUALIFIED']);
	if (!empty($entrylist)) {
		echo '<div class="hint hintmargin">';
		echo '<div class="hint_title">';
		echo '<translate id="GRID_DISQUALIFIED">';
		echo 'Disqualified entries';
		echo '</translate>';
		echo '</div> <!-- hint_title -->';
		echo '</div> <!-- hint -->';
		
		echo '<div class="grid">';
	
		foreach ($entrylist as $uid => $eid) try {
			$entry = Entry::get($eid);
			$pid = $entry->getPid();
			
			if ($competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
				$token = new Token($user->getUid().'-'.$eid);
				echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#token='.$token->getHash().'" class="picture_grid" '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
			} else {
				echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'" class="picture_grid" '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
			}
		} catch (EntryException $e) {}
		
		echo '</div> <!-- grid -->';
	}
}*/

?>

<?php
$page->endHTML();
$page->render();
?>
