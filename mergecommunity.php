<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users can merge a community into another
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitylist.php');
require_once(dirname(__FILE__).'/entities/pointsvalue.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$page = new Page('COMMUNITIES', 'COMMUNITIES', $user);
$page->addJavascript('MERGE_COMMUNITY');
$page->startHTML();

$xid = (isset($_REQUEST['xid'])?$_REQUEST['xid']:null);

try {
	$community = Community::get($xid);
} catch (CommunityException $e) {
	header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
	exit(0);
}

$name = String::fromaform($community->getName());

$page->addJavascriptVariable('merge_link', $REQUEST['MERGE_COMMUNITY'].'?xid='.$xid.'&merge_token=');
$page->addJavascriptVariable('confirmation_title', '<translate id="MERGE_COMMUNITY_CONFIRMATION_TITLE" escape="htmlentities">Are you sure that you want to merge these communities?</translate>');
$page->addJavascriptVariable('confirmation_text', '<translate id="MERGE_COMMUNITY_CONFIRMATION_TEXT" escape="htmlentities">This operation cannot be undone. All the members, past competitions, discussions and entries will be permanently transferred to the community you\'ve selected as the merge target. The merging process takes several seconds, please wait after you\'ve pressed the "yes" button and the page will refresh itself once the merging is done.</translate>');
$page->addJavascriptVariable('confirmation_button_left', '<translate id="MERGE_COMMUNITY_CONFIRMATION_BUTTON_LEFT" escape="htmlentities">Yes, do the merge</translate>');
$page->addJavascriptVariable('confirmation_button_right', '<translate id="MERGE_COMMUNITY_CONFIRMATION_BUTTON_RIGHT" escape="htmlentities">No</translate>');

$page->setTitle('<translate id="MERGE_COMMUNITY_PAGE_TITLE">Merge the <string value="'.$name.'"/> community into another on inspi.re</translate>');

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo '<translate id="MERGE_COMMUNITY_HINT">Merge the <string value="'.$name.'"/> community into another</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="MERGE_COMMUNITY_HINT_BODY">All the members of this community will automatically become members of the community you merge into. All past competitions, entries and discussions will be transferred. The community settings such as name, frequency, theme suggestions costs, etc. will be the ones currently belonging to the community you merge into. <b>The merging process cannot be undone, you must be very careful to choose the right community to merge this one into.</b></translate>';
echo '</div> <!-- hint -->';

echo '<select name="merge_community" id="merge_community">';
echo '<option value="" selected="selected"><translate id="MERGE_COMMUNITY_SELECT_DEFAULT">Choose a community to merge <string value="'.$name.'"/> into...</translate></option>';

$communitylist = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ACTIVE']);
$communitylist = array_merge($communitylist, CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ACTIVE']));
$merge_community = Community::getArray($communitylist);

foreach ($merge_community as $merge_xid => $merge_comm) if ($merge_xid != $xid) {
	$merge_name = String::fromaform($merge_comm->getName());
	$merge_token = new Token($merge_xid);
	echo '<option value="'.$merge_token->getHash().'">'.$merge_name.'</option>';
}

echo '</select><br/>';

echo '<input id="merge_community_submit" type="submit" value="<translate id="MERGE_COMMUNITY_SUBMIT">Proceed with merge</translate>" disabled="">';

$page->endHTML();
$page->render();
?>