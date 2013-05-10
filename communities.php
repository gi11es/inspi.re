<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users create, join and leave communities
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitylist.php');
require_once(dirname(__FILE__).'/entities/communitymembership.php');
require_once(dirname(__FILE__).'/entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/entities/pointsvalue.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page = new Page('COMMUNITIES', 'COMMUNITIES', $user);

$page->setTitle('<translate id="COMMUNITIES_PAGE_TITLE">Your communities on inspi.re</translate>');

$page->startHTML();

$member_of = array_keys(CommunityMembershipList::getByUid($user->getUid()));

if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	$owner = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ANONYMOUS']);
} else {
	$owner = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ACTIVE']);
	$owner = array_merge($owner, CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['INACTIVE']));
}

$community_list = array_unique(array_merge($member_of, $owner));
$community = Community::getArray($community_list);

$moderate = array();
foreach ($member_of as $xid) {
	$moderatorlist = CommunityModeratorList::getByXid($xid);
	if (in_array($user->getUid(), $moderatorlist)) {
		$moderate []= $xid;
	}
}

$member_of = array_diff($member_of, $moderate);

if (!empty($owner)) {
	echo '<div class="hint">';
	echo '<div class="hint_title">';
	echo '<translate id="COMMUNITIES_HINT_ADMINISTRATE_TITLE">';
	echo 'Communities you administrate';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div>';
	echo '<div class="hanging_menu floatleft">';
	echo '<a href="'.$PAGE['EDIT_COMMUNITY'].'?lid='.$user->getLid().'"><translate id="COMMUNITIES_NEW_COMMUNITY">Create new community</translate></a>';
	echo '</div> <!-- hanging_menu -->';
	
	echo '<div class="community_list '.($hideads?'abovemargin':'').'">';

	foreach ($owner as $xid) if (isset($community[$xid])) {
		echo UI::RenderCommunityListing($user, $community[$xid]);
	}
	
	echo '</div> <!-- community_list -->';
}

if (!empty($moderate)) {
	echo '<div class="hint ',(empty($owner)?'':'abovemargin'),'">';
	echo '<div class="hint_title">';
	echo '<translate id="COMMUNITIES_HINT_MODERATE_TITLE">';
	echo 'Communities you moderate';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div>';
	if (empty($owner)) {
		echo '<div class="hanging_menu floatleft">';
		echo '<a href="'.$PAGE['EDIT_COMMUNITY'].'?lid='.$user->getLid().'"><translate id="COMMUNITIES_NEW_COMMUNITY">Create new community</translate></a>';
		echo '</div> <!-- hanging_menu -->';
	}
	
	echo '<div class="community_list '.($hideads?'abovemargin':'').'">';

	foreach ($moderate as $xid) if (isset($community[$xid])) {
		echo UI::RenderCommunityListing($user, $community[$xid]);
	}
	
	echo '</div> <!-- community_list -->';
}

/*echo '<div class="hint">';
echo '<div class="hint_title">';
echo '<translate id="COMMUNITIES_HINT_TITLE">';
echo 'Communities you moderate';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '</div>';*/

echo '<div class="hint ',(empty($owner) && empty($moderate)?'':'abovemargin'),'">';
echo '<div class="hint_title">';
echo '<translate id="COMMUNITIES_HINT_TITLE">';
echo 'Communities you\'re a member of';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

if (empty($owner) && empty($moderate)) {
	echo '<div class="hanging_menu floatleft">';
	echo '<a href="'.$PAGE['EDIT_COMMUNITY'].'?lid='.$user->getLid().'"><translate id="COMMUNITIES_NEW_COMMUNITY">Create new community</translate></a>';
	echo '</div> <!-- hanging_menu -->';
}
echo '<ad ad_id="COMMUNITIES"/>';

echo '<div class="community_list '.($hideads?'abovemargin':'').'">';

if (empty($community_list)) {
	echo '<div class="listing_item">';
	echo '<div class="listing_header">';
	echo '<translate id="COMMUNITIES_NOT_MEMBER_YET">';
	echo 'You\'re not a member of any community yet. You should browse the available communities below and pick one you wish to join.';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else foreach ($member_of as $xid) if (isset($community[$xid])) {
	echo UI::RenderCommunityListing($user, $community[$xid]);
}

echo '</div> <!-- community_list -->';

$page->endHTML();
$page->render();
?>
