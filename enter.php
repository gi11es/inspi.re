<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users send their contributions to open competitions
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/pointsvalue.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page = new Page('COMPETE', 'COMPETITIONS', $user);
$page->addStyle('EDITABLE_PICTURE');
$page->addJavascript('EDITABLE_PICTURE');
$persistenttoken = new PersistentToken($user->getUid());
$page->addJavascriptVariable('persistenttoken', $persistenttoken->getHash());

$cid = isset($_REQUEST['cid'])?$_REQUEST['cid']:null;

if ($cid == null) {
	header('Location: ' . $PAGE['COMPETE'].'?lid='.$user->getLid());
	exit(0);
}

$competition = Competition::get($cid);
if ($competition->getStatus() != $COMPETITION_STATUS['OPEN']) {
	header('Location: ' . $PAGE['COMPETE'].'?lid='.$user->getLid());
	exit(0);
}

$page->startHTML();

$community = Community::get($competition->getXid());
$theme = Theme::get($competition->getTid());

$page->setTitle('<translate id="ENTER_PAGE_TITLE">Enter the "<string value="'.String::fromaform($theme->getTitle()).'"/>" competition of the <string value="'.String::fromaform($community->getName()).'"/> community on inspi.re</translate>');

echo '<div id="competition_description">';
echo '<div class="listing_item nomargin">';
echo '<picture href="'.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$competition->getXid().'" category="community" class="listing_thumbnail" size="small" '.($community->getPid() === null?'':'pid="'.$community->getPid().'"').' />';
echo '<div class="listing_header">';
if ($community->getXid() == 267) {
	echo '<translate id="PRIZE_COMMUNITY_THEME_TITLE'.$competition->getTid().'">'.$theme->getTitle().'</translate>';
} else {
	echo '<theme_title tid="'.$competition->getTid().'"/>';
}
echo '</div> <!-- listing_header -->';
echo '<div class="listing_subheader">';
echo '<translate id="COMPETE_LIST_SUBHEADER">';
echo 'Suggested by <user_name uid="'.$theme->getUid().'"/> for <community_name link="true" xid="'.$competition->getXid().'"/>. <span class="time_left"><duration value="'.($competition->getVoteTime() - gmmktime()).'"/> left to enter this competition</span>.';
echo '</translate>';
echo '</div> <!-- listing_subheader -->';
echo '<div class="listing_content">';
if ($community->getXid() == 267) {
	echo '<translate id="PRIZE_COMMUNITY_THEME_DESCRIPTION'.$competition->getTid().'">'.$theme->getDescription().'</translate>';
} else {
	echo String::fromaform($theme->getDescription());
}
echo '</div>';
echo '</div> <!-- listing_item -->';
echo '</div> <!-- competition_description -->';

$rules = $community->getRules();

if (strcmp(trim($rules), '') !=0) {
	echo '<div class="warning hintmargin">';
	echo '<div class="warning_title">';
	echo '<translate id="ENTER_WARNING_RULES">';
	echo '<community_name link="true" xid="'.$competition->getXid().'"/> has specific rules';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
	echo '<div id="rules">';
	echo String::fromaform($rules);
	echo '</div> <!-- rules -->';
}

$pid = null;

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

$entrylist = EntryList::getByUidAndCidAndStatus($user->getUid(), $cid, $status);
if (empty($entrylist))
	$entrylist = EntryList::getByUidAndCidAndStatus($user->getUid(), $cid, $ENTRY_STATUS['DISQUALIFIED']);

if (!empty($entrylist)) {
	try {
		$eid = array_shift($entrylist);
		$entry = Entry::get($eid);
		$pid = $entry->getPid();
	} catch (EntryException $e) {
		$pid = null;
	}
}

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['ENTRY_POSTING']);
$points_entry_posting = -$pointsvalue->getValue();

echo '<ad ad_id="LEADERBOARD"/>';

$artworkcount = count(EntryList::getByUidAndStatus($user->getUid(), $ENTRY_STATUS['POSTED']));

if ($pid === null && $user->getPoints() < $points_entry_posting) {
	echo '<div class="warning '.($hideads?'abovemargin':'').'">';
	echo '<div class="warning_title">';
	echo '<translate id="ENTER_LACK_OF_FUNDS_TITLE">';
	echo 'You do not have enough points to enter a competition';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '<translate id="LACK_OF_FUNDS">';
	echo '<integer value="'.$points_entry_posting.'"/> points are needed and you only have <integer value="'.$user->getPoints().'"/>. We suggest that you <a href="'.$PAGE['VOTE'].'?lid='.$user->getLid().'">vote and critique entries</a> in order to earn points.';
	echo '</translate>';
	echo '</div> <!-- warning -->';
} elseif ($artworkcount > 70 && !$ispremium) {
	echo '<div class="hint hintmargin '.($hideads?'abovemargin':'').'">';
	echo '<div class="hint_title">';
	echo '<translate id="ENTER_HINT_TITLE">';
	echo 'Enter this competition or replace your existing entry';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '<translate id="ENTER_HINT_BODY">';
	echo 'Use the button and the links below the image to enter, delete or replace your entry for this competition. <b>Please note that the picture\'s cropping settings only affect the thumbnails. Your entry will always be displayed without any cropping during the competition.</b>';
	echo '</translate>';
	echo ' <span id="points_warning">';
	echo '<translate id="ENTER_POINTS_COST">';
	echo 'Entering a competition costs <integer value="'.$points_entry_posting.'"/> points.';
	echo '</translate>';
	echo '</span>';
	echo '</div> <!-- hint -->';
	
	echo '<div class="warning '.($hideads?'abovemargin':'').'">';
	echo '<div class="warning_title">';
	echo '<translate id="ENTER_STANDARD_MEMBERSHIP_MAX_TITLE">';
	echo 'You\'ve reached the storage limit that your standard membership allows';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '<translate id="ENTER_STANDARD_MEMBERSHIP_MAX">';
	echo 'Standard members can only store a maximum of <integer value="70"/> artworks on their account. You currently have <integer value="'.$artworkcount.'"/> artworks stored on your account. In order to enter a new competition, you need to either <a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'">upgrade to premium membership</a> or to delete older entries.';
	echo '</translate>';
	echo '</div> <!-- warning -->';
} else {
	echo '<div class="hint hintmargin '.($hideads?'abovemargin':'').'">';
	echo '<div class="hint_title">';
	echo '<translate id="ENTER_HINT_TITLE">';
	echo 'Enter this competition or replace your existing entry';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '<translate id="ENTER_HINT_BODY">';
	echo 'Use the button and the links below the image to enter, delete or replace your entry for this competition. <b>Please note that the picture\'s cropping settings only affect the thumbnails. Your entry will always be displayed without any cropping during the competition.</b>';
	echo '</translate>';
	echo ' <span id="points_warning">';
	echo '<translate id="ENTER_POINTS_COST">';
	echo 'Entering a competition costs <integer value="'.$points_entry_posting.'"/> points.';
	echo '</translate>';
	echo '</span>';
	echo '</div> <!-- hint -->';
	
	echo '<div id="entry_picture">';
	echo UI::RenderEditablePicture($page, $pid, $PICTURE_CATEGORY['ENTRY'], true, $REQUEST['ENTRY_UPLOAD'].'?cid='.$cid, $REQUEST['ENTRY_RESET'].'?cid='.$cid, $PAGE['EDIT_CROPPING'].'?cid='.$cid, $persistenttoken->getHash());
	echo '</div> <!-- entry_picture -->';
	
	echo '<div id="picture_huge_container">';
	if ($pid != null) {
		echo '<picture id="picture_huge" pid="'.$pid.'" size="huge"/>';
	} else echo '<img id="picture_huge" src="'.$GRAPHICS_PATH.'invisible.gif">';
	echo '</div> <!-- picture_huge_container -->';
}

$page->endHTML();
$page->render();
?>
