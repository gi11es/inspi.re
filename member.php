<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Public profile page
*/

require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitylist.php');
require_once(dirname(__FILE__).'/entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/entities/communitymoderator.php');
require_once(dirname(__FILE__).'/entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/i18n.php');
require_once(dirname(__FILE__).'/entities/teammembership.php');
require_once(dirname(__FILE__).'/entities/teammembershiplist.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userblocklist.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/inml.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$communities_page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;
$entries_page_offset = isset($_REQUEST['entriespage'])?$_REQUEST['entriespage']:1;
$administrated_communities_page_offset = isset($_REQUEST['apage'])?$_REQUEST['apage']:1;
$moderated_communities_page_offset = isset($_REQUEST['mpage'])?$_REQUEST['mpage']:1;

function describeCommunity($xid, $profile_uid) {
	global $PAGE;
	global $LANGUAGE_NAME_FROM_ID;
	global $USER_STATUS;
	global $user;
	global $REQUEST;
	global $COMMUNITY_MEMBERSHIP_STATUS;
	
	try {
		$community = Community::get($xid);
	} catch (CommunityException $e) {
		return;
	}
	$members = CommunityMembershipList::getByXidAndStatus($xid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);
	$member_count = count($members);

	echo '<div class="listing_item clearboth">';
	echo '<picture href="'.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$xid.'" category="community" class="listing_thumbnail" size="small" '.($community->getPid() === null?'':'pid="'.$community->getPid().'"').' />';
	echo '<div class="listing_header"><community_name xid="'.$xid.'" link="true"/>';
	echo '</div> <!-- listing_header -->';
	if ($user->getUid() == $community->getUid()) {
		echo '<div class="administrator_actions">';
		
		if ($profile_uid != $community->getUid()) {
			echo '<a href="javascript:showConfirmation(\''.$REQUEST['TRANSFER_ADMINISTRATION'].'?xid='.$xid.'&uid='.$profile_uid.'\'';
			echo ', \'<translate id="COMMUNITY_TRANSFER_ADMINISTRATION_CONFIRMATION_TITLE" escape="js">Do you really want to transfer administration rights for this community?</translate>\'';
			echo ', \'<translate id="COMMUNITY_TRANSFER_ADMINISTRATION_CONFIRMATION_TEXT" escape="js">By giving administration rights to this user, you will lose any control over the community. This action can only be reversed if that person is willing to give you back the administration rights. Only one person at a time can have administration rights over a community.</translate>\'';
			echo ', \'<translate id="COMMUNITY_TRANSFER_ADMINISTRATION_CONFIRMATION_YES" escape="js">Yes, go ahead</translate>\'';
			echo ', \'<translate id="COMMUNITY_TRANSFER_ADMINISTRATION_CONFIRMATION_NO" escape="js">No</translate>\'';
			echo ');">';	
			echo '<translate id="COMMUNITIES_DESCRIPTION_TRANSFER_ADMINISTRATION">';
			echo 'transfer administration rights to him/her';
			echo '</translate>';
			echo '</a><br/>';
		}
		
		$moderatorlist = CommunityModeratorList::getByXid($xid);
		
		if (in_array($profile_uid, $moderatorlist)) {
			echo '<a href="'.$REQUEST['REMOVE_FROM_MODERATORS'].'?xid='.$xid.'&uid='.$profile_uid.'">';
			echo '<translate id="COMMUNITIES_DESCRIPTION_REMOVE_MODERATOR">';
			echo 'remove him/her from the community moderators';
			echo '</translate>';
			echo '</a><br/>';
		} else {
			echo '<a href="'.$REQUEST['ADD_TO_MODERATORS'].'?xid='.$xid.'&uid='.$profile_uid.'">';
			echo '<translate id="COMMUNITIES_DESCRIPTION_MAKE_MODERATOR">';
			echo 'make him/her a moderator of this community';
			echo '</translate>';
			echo '</a><br/>';
		}
		echo '</div> <!-- administrator_actions -->';
	}
	
	echo '<div class="community_information">';
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
	echo '</div> <!-- community_information -->';
	echo '</div> <!-- listing_item -->';
}

$user = User::getSessionUser();

$page = new Page('MEMBERS', 'COMMUNITIES', $user);
$page->addJavascript('MEMBER');

$page->startHTML();

$member = false;
if (isset($_REQUEST['uid'])) {
	$uid = $_REQUEST['uid'];
	try {
		$member = User::get($uid);
	} catch (UserException $e) {
		header('Location: /Members/s3-l'.$user->getLid());
		exit(0);
	}
}

if (!$member) {
	header('Location: /Members/s3-l'.$user->getLid());
	exit(0);
}

$page->addJavascriptVariable('reload_url', UI::RenderUserLink(stripslashes($member->getUid()), true).'-p'.$communities_page_offset.'-z'.$entries_page_offset.'-a'.$administrated_communities_page_offset.'-m'.$moderated_communities_page_offset);
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);

$page->setTitle('<translate id="MEMBER_PAGE_TITLE"><string value="'.String::fromaform($member->getUniqueName()).'"/> on inspi.re</translate>');

$levels = UserLevelList::getByUid($member->getUid());
$ismemberpremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$ismia = in_array($USER_LEVEL['MIA'], $levels);
$ismiaappealed = in_array($USER_LEVEL['MIA_APPEALED'], $levels);

$levels = UserLevelList::getByUid($user->getUid());
$isadmin = in_array($USER_LEVEL['ADMINISTRATOR'], $levels);

if ($member->getStatus() == $USER_STATUS['BANNED'] && $user->getUid() != $member->getUid() && !$isadmin) {
	header('Location: /Members/s3-l'.$user->getLid());
	exit(0);
}

echo '<div class="hint">';
echo '<div id="member_name" class="hint_title">';
echo '<user_name uid="'.$uid.'"/>';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

if ($user->getStatus() != $USER_STATUS['UNREGISTERED'] && $user->getUid() != $uid) {
	echo '<div class="hanging_menu floatleft" id="new_private_message">';
	echo '<a href="'.$PAGE['NEW_PRIVATE_MESSAGE'].'?uid='.$uid.'"><translate id="MEMBER_NEW_PRIVATE_MESSAGE">Send a private message</translate></a>';
	echo '</div> <!-- hanging_menu -->';
	
	$userblocklist = UserBlockList::getByUid($user->getUid());
	
	if (!in_array($uid, $userblocklist)) {
		echo '<div class="hanging_menu floatleft" id="block_private_messages">';
		echo '<a href="'.$REQUEST['BLOCK_PRIVATE_MESSAGES'].'?uid='.$uid.'"><translate id="MEMBER_BLOCK_PRIVATE_MESSAGES">Block private messages coming from him/her</translate></a>';
		echo '</div> <!-- hanging_menu -->';
	} else {
		echo '<div class="hanging_menu floatleft" id="block_private_messages">';
		echo '<a href="'.$REQUEST['UNBLOCK_PRIVATE_MESSAGES'].'?uid='.$uid.'"><translate id="MEMBER_UNBLOCK_PRIVATE_MESSAGES">Unblock private messages coming from him/her</translate></a>';
		echo '</div> <!-- hanging_menu -->';
	}
	
	if ($ismemberpremium) {
		echo '<div class="hanging_menu floatleft" id="premium_activate">';
		echo '<a href="'.$PAGE['PREMIUM_ACTIVATE'].'?lid='.$user->getLid().'&uid='.$uid.'"><translate id="MEMBER_PREMIUM_SPONSOR_EXTEND">Extend his/her premium membership</translate></a>';
		echo '</div> <!-- hanging_menu -->';	
	} else {
		echo '<div class="hanging_menu floatleft" id="premium_activate">';
		echo '<a href="'.$PAGE['PREMIUM_ACTIVATE'].'?lid='.$user->getLid().'&uid='.$uid.'"><translate id="MEMBER_PREMIUM_SPONSOR">Give him/her premium membership</translate></a>';
		echo '</div> <!-- hanging_menu -->';
	}
	
	if ($ismia && !$ismiaappealed) {
		echo '<div class="hanging_menu floatleft" id="bring_back">';
		echo '<a href="'.$PAGE['BRING_BACK_MEMBER'].'?lid='.$user->getLid().'&uid='.$uid.'"><translate id="MEMBER_BRING_BACK">Bring him/her back!</translate></a>';
		echo '</div> <!-- hanging_menu -->';
	}	
} elseif ($user->getStatus() != $USER_STATUS['UNREGISTERED']) {
	if (!$ismemberpremium) {
	// We're looking at ourselves
		echo '<div class="hanging_menu floatleft" id="premium_activate">';
		echo '<a href="'.$PAGE['PREMIUM_ACTIVATE'].'?uid='.$user->getUid().'"><translate id="MEMBER_PREMIUM_UPGRADE">Upgrade to premium membership</translate></a>';
		echo '</div> <!-- hanging_menu -->';
	} else {
		$membershipduration = max(0, $user->getPremiumTime() - time());
		
		if ($membershipduration < 94608000) {
			echo '<div class="hanging_menu floatleft" id="premium_activate">';
			echo '<a href="'.$PAGE['PREMIUM_ACTIVATE'].'?uid='.$user->getUid().'"><translate id="MEMBER_PREMIUM_EXTEND">Extend your premium membership</translate></a>';
			echo '</div> <!-- hanging_menu -->';	
		} else {
			echo '<br/>';
		}
	}
} elseif ($user->getUid() != $uid) {
	if ($ismemberpremium) {
		echo '<div class="hanging_menu floatleft" id="premium_activate">';
		echo '<a href="'.$PAGE['PREMIUM_ACTIVATE'].'?lid='.$user->getLid().'&uid='.$uid.'"><translate id="MEMBER_PREMIUM_SPONSOR_EXTEND">Extend his/her premium membership</translate></a>';
		echo '</div> <!-- hanging_menu -->';	
	} else {
		echo '<div class="hanging_menu floatleft" id="premium_activate">';
		echo '<a href="'.$PAGE['PREMIUM_ACTIVATE'].'?lid='.$user->getLid().'&uid='.$uid.'"><translate id="MEMBER_PREMIUM_SPONSOR">Give him/her premium membership</translate></a>';
		echo '</div> <!-- hanging_menu -->';
	}
}

// As an administrator a few advanced functions are available on users
if ($isadmin && $user->getUid() != $uid) {
	echo '<div class="warning clearboth">';
	echo '<div class="warning_title">';
	echo '<translate id="MEMBER_ADMINISTRATION_TOOLS">';
	echo 'Administration tools';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
	
	$teammembershiplist = TeamMembershipList::get();
	
	if (in_array($uid, $teammembershiplist)) {
		echo '<div class="warning_hanging_menu floatleft" id="remove_from_team">';
		echo '<a href="'.$REQUEST['DELETE_TEAM_MEMBER'].'?uid='.$uid.'"><translate id="MEMBER_REMOVE_FROM_TEAM">Remove member from the team</translate></a>';
		echo '</div> <!-- warning_hanging_menu -->';
		echo '<div class="warning_hanging_menu floatleft" id="add_to_team">';
		echo '<a href="'.$PAGE['EDIT_TEAM_MEMBER'].'?uid='.$uid.'"><translate id="MEMBER_EDIT_TEAM">Edit team membership</translate></a>';
		echo '</div> <!-- warning_hanging_menu -->';
	} else {
		echo '<div class="warning_hanging_menu floatleft" id="add_to_team">';
		echo '<a href="'.$PAGE['EDIT_TEAM_MEMBER'].'?uid='.$uid.'"><translate id="MEMBER_ADD_TO_TEAM">Add member to the team</translate></a>';
		echo '</div> <!-- warning_hanging_menu -->';
	}
	echo '<div class="warning_hanging_menu floatleft" id="give_points">';
	echo '<a href="'.$PAGE['GIVE_POINTS'].'?uid='.$uid.'"><translate id="MEMBER_GIVE_POINTS">Give points</translate></a>';
	echo '</div> <!-- warning_hanging_menu -->';
	
	echo '<div class="warning_hanging_menu floatleft" id="impersonate">';
	echo '<a href="'.$REQUEST['IMPERSONATE_USER'].'?uid='.$uid.'"><translate id="MEMBER_IMPERSONATE">Impersonate</translate></a>';
	echo '</div> <!-- warning_hanging_menu -->';
	
	$levels = UserLevelList::getByUid($member->getUid());
	
	if (in_array($USER_LEVEL['DONATOR'], $levels)) {
		echo '<div class="warning_hanging_menu floatleft" id="remove_from_donators">';
		echo '<a href="'.$REQUEST['REMOVE_FROM_DONATORS'].'?uid='.$uid.'"><translate id="MEMBER_REMOVE_FROM_DONATORS">Remove from the list of donators</translate></a>';
		echo '</div> <!-- warning_hanging_menu -->';
	} else {
		echo '<div class="warning_hanging_menu floatleft" id="add_to_donators">';
		echo '<a href="'.$REQUEST['ADD_TO_DONATORS'].'?uid='.$uid.'"><translate id="MEMBER_ADD_TO_DONATORS">Add to the list of donators</translate></a>';
		echo '</div> <!-- warning_hanging_menu -->';
	}
	
	if ($member->getStatus() != $USER_STATUS['BANNED']) {
		echo '<div class="warning_hanging_menu floatleft" id="ban">';
		echo '<a href="javascript:showConfirmation(\''.$REQUEST['BAN'].'?uid='.$uid.'\'';
		echo ', \'<translate id="MEMBER_BAN_CONFIRMATION_TITLE" escape="js">Do you really want to ban this user from the website?</translate>\'';
		echo ', \'<translate id="MEMBER_BAN_CONFIRMATION_TEXT" escape="js">All his/her past activity on the website will be gone forever. This cannot be undone!</translate>\'';
		echo ', \'<translate id="MEMBER_BAN_CONFIRMATION_YES" escape="js">Yes, go ahead</translate>\'';
		echo ', \'<translate id="MEMBER_BAN_CONFIRMATION_NO" escape="js">No</translate>\'';
		echo ');"><translate id="MEMBER_BAN">Ban from the website</translate></a>';	
		echo '</div> <!-- warning_hanging_menu -->';
	}
}

if (isset($_REQUEST['successpm'])) {
	echo '<div class="warning hintmargin clearboth highlight_item">';
	echo '<div class="warning_title">';
	echo '<translate id="PRIVATE_MESSAGE_SENT">';
	echo 'Your private message was sent successfully';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} elseif (isset($_REQUEST['successblock'])) {
	echo '<div class="warning hintmargin clearboth highlight_item">';
	echo '<div class="warning_title">';
	echo '<translate id="MEMBER_USER_BLOCKED">';
	echo 'This person was successfully blocked';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} elseif (isset($_REQUEST['successunblock'])) {
	echo '<div class="warning hintmargin clearboth highlight_item">';
	echo '<div class="warning_title">';
	echo '<translate id="MEMBER_USER_UNBLOCKED">';
	echo 'This person was successfully unblocked';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} elseif (isset($_REQUEST['successappeal'])) {
	echo '<div class="warning hintmargin clearboth highlight_item">';
	echo '<div class="warning_title">';
	echo '<translate id="APPEAL_MESSAGE_SENT">';
	echo 'Your appeal was sent successfully';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} 

echo '<profile_picture id="member_picture" uid="'.$uid.'" size="big" />';

echo '<div id="member_description">';
echo '<div id="member_summary">';
echo '<translate id="MEMBER_SINCE">Has been a member of inspi.re for <duration value="'.(time() - $member->getCreationTime()).'"/></translate><br/>';

if ($ismemberpremium)
	echo '<translate id="MEMBER_IS_PREMIUM">Is a <a href="'.$PAGE["PREMIUM"].'">premium member</a></translate><br/>';
else
	echo '<translate id="MEMBER_IS_STANDARD">Is a <a href="'.$PAGE["PREMIUM"].'">standard member</a></translate><br/>';


$ip_history = $member->getIpHistory();

if (!empty($ip_history)) {
	arsort($ip_history);
	echo '<translate id="MEMBER_LAST_CONNECTED">Last connected from <location ip="'.array_shift(array_keys($ip_history)).'"/></translate><br/>';
}

echo '<translate id="MEMBER_LANGUAGE_PREFERENCE">Uses the website in <language_name lid="'.$member->getLid().'"/></translate><br/>';

$last_activity = $member->getLastActivity();
if ($last_activity !== null && (gmmktime() - $last_activity) < $APPEARING_OFFLINE_DELAY)
	echo '<translate id="MEMBER_ONLINE">Is currently online</translate><br/>';
else
	echo '<translate id="MEMBER_OFFLINE">Is currently offline</translate><br/>';
	
if ($ismia)
	echo '<translate id="MEMBER_MIA">Hasn\'t come to the website for over a month</translate><br/>';
	
$entrycount = count(EntryList::getByUid($uid));

if ($entrycount == 0)
	echo '<translate id="MEMBER_ENTERED_NONE">Has yet to enter a competition</translate><br/>';
elseif ($entrycount == 1)
	echo '<translate id="MEMBER_ENTERED_SINGULAR">Entered one competition</translate><br/>';
else 
	echo '<translate id="MEMBER_ENTERED_PLURAL">Entered <integer value="',$entrycount,'"/> competitions</translate><br/>';

$first = count(EntryList::getByUidAndRank($uid, 1));
$second = count(EntryList::getByUidAndRank($uid, 2));
$third = count(EntryList::getByUidAndRank($uid, 3));

if ($first > 0) {
	if ($first > 1)
		echo '<translate id="MEMBER_CAME_FIRST_PLURAL">Came first in <integer value="'.$first.'"/> competitions</translate><br/>';
	else
		echo '<translate id="MEMBER_CAME_FIRST_SINGULAR">Came first in 1 competition</translate><br/>';
}
if ($second > 0) {
	if ($second > 1)
		echo '<translate id="MEMBER_CAME_SECOND_PLURAL">Came second in <integer value="'.$second.'"/> competitions</translate><br/>';
	else
		echo '<translate id="MEMBER_CAME_SECOND_SINGULAR">Came second in 1 competition</translate><br/>';
}
if ($third > 0) {
	if ($third > 1)
		echo '<translate id="MEMBER_CAME_THIRD_PLURAL">Came third in <integer value="'.$third.'"/> competitions</translate><br/>';
	else
		echo '<translate id="MEMBER_CAME_THIRD_SINGULAR">Came third in 1 competition</translate><br/>';
}

$postcount = count(DiscussionPostList::getByUidAndStatus($uid, $DISCUSSION_POST_STATUS['POSTED']));
if ($postcount == 0) {
	echo '<translate id="MEMBER_COMMENT_COUNT_NONE">Has yet to write any discussion post or comment</translate><br/>';
} elseif ($postcount == 1) {
	echo '<translate id="MEMBER_COMMENT_COUNT_SINGULAR">Has written 1 discussion post or comment</translate><br/>';
} else {
	echo '<translate id="MEMBER_COMMENT_COUNT_PLURAL">Has written <integer value="'.$postcount.'"/> discussion posts and comments</translate><br/>';
}

echo '</div> <!-- member_summary -->';
if ($member->getDescription() !== null && strcmp(trim($member->getDescription()), '') != 0) {
	echo '<div class="hint hintmargin" id="about_header">';
	echo '<div class="hint_title">';
	echo '<translate id="MEMBER_ABOUT_HEADER">';
	echo 'About <string value="'.String::htmlentities($member->getUniqueName()).'"/>';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	echo '<div id="about">';
	echo String::fromaform($member->getDescription());
	echo '</div> <!-- about -->';
}
echo '</div> <!-- memberdescription -->';


/************** Showing member's entries *****************/

switch ($member->getStatus()) {
	case $USER_STATUS['UNREGISTERED']:
		$status = $ENTRY_STATUS['ANONYMOUS'];
		break;
	case $USER_STATUS['BANNED']:
		$status = $ENTRY_STATUS['BANNED'];
		break;
	default:
		$status = $ENTRY_STATUS['POSTED'];
}

if ($user->getStatus() != $USER_STATUS['BANNED'] && $status == $ENTRY_STATUS['BANNED'])
	$entrylist = array();
else
	$entrylist = EntryList::getByUidAndStatus($uid, $status);
	
$entrylist += EntryList::getByUidAndStatus($uid, $ENTRY_STATUS['DELETED']);

$cleanentrylist = array();

$competitionlist = array();

foreach ($entrylist as $cid => $eid) {
	try {
		$competitionlist[$cid] = Competition::get($cid);
		if ($competitionlist[$cid]->getStatus() == $COMPETITION_STATUS['CLOSED'])
			$cleanentrylist[$eid] = $competitionlist[$cid]->getEndTime();
	} catch (CompetitionException $e) {}
}

function RenderProfileEntriesLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	global $communities_page_offset;
	global $administrated_communities_page_offset;
	global $moderated_communities_page_offset;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.UI::RenderUserLink($_REQUEST['uid'], true).'-a'.$administrated_communities_page_offset.'-m'.$moderated_communities_page_offset.'-p'.$communities_page_offset.'-z'.$i.'#community_entry_list">'.$i.'</a>').($i == $page_count?'':' ');
}

if (!empty($cleanentrylist)) {
	arsort($cleanentrylist);
	
	$results_count = count($cleanentrylist);
	
	$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'PROFILE_ENTRIES');
	
	$page_count = ceil($results_count / $amount_per_page);
	
	if ($entries_page_offset > $page_count) $entries_page_offset = $page_count;
	
	echo '<div class="hint'.($page_count > 1?'':' hintmargin').'" id="community_entry_list">';
	echo '<div class="hint_title">';
	echo '<translate id="MEMBER_PAST_ENTRIES">';
	echo 'Artworks <user_name uid="'.$uid.'"/> entered in past competitions';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	
	$cleanentrylist = array_slice($cleanentrylist, ($entries_page_offset - 1) * $amount_per_page, $amount_per_page, true);

	echo UI::RenderPaging($entries_page_offset, $page_count, 'RenderProfileEntriesLink');
	
	echo '<div id="member_entries_list">';
        	
	foreach ($cleanentrylist as $eid => $end_time) {
		$entry = Entry::get($eid);
		$cid = $entry->getCid();
		
		try {
			$theme = Theme::get($competitionlist[$cid]->getTid());
		} catch (ThemeException $e) {
			continue;
		}
		
		$title = String::fromaform($theme->getTitle());
		
		$title_suffix = ' (';
		
		if ($member->getDisplayRank()) {
			if ($member->getStatus() == $USER_STATUS['BANNED']) $rank = $entry->getBannedRank();
			else $rank = $entry->getRank();
			
			$title_suffix .= '<translate id="MEMBER_RANK_HEADER">artwork ranked <rank value="'.$rank.'"/> out of <integer value="'.$competitionlist[$cid]->getEntriesCount().'"/></translate> - ';
		}
		
		$title_suffix .= '<translate id="MEMBER_TIME_HEADER"><duration value="'.(time() - $competitionlist[$cid]->getEndTime()).'"/> ago</translate>)';

		$translated_html = I18N::translateHTML($user, $title_suffix);
		$tagged_html = INML::processHTML($user, $translated_html);
		$title .= String::fromaform( I18N::translateHTML($user, $tagged_html));
		
		echo '<picture class="member_entry" title="'.$title.'" category="entry" pid="'.$entry->getPid().'" size="medium" href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'"/>';
	}
	
	echo '</div> <!-- member_entries_list -->';
	
	echo UI::RenderPaging($entries_page_offset, $page_count, 'RenderProfileEntriesLink', true);
	echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
	echo '<div id="entries_current_amount">';
	if ($amount_per_page > 1) {
		echo '<translate id="PROFILE_ENTRIES_BODY_PLURAL">';
		echo 'Currently displaying <integer value="'.$amount_per_page.'"/> entries per page.';
		echo '</translate>';
	} else {
		echo '<translate id="PROFILE_ENTRIES_BODY_SINGULAR">';
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
	echo '<translate id="PROFILE_ENTRIES_INPUT_AMOUNT">';
	echo 'Display <input id="entries_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> entries per page. <a href="javascript:saveEntriesAmount()">Save</a> <a href="javascript:cancelEntriesAmount()">Cancel</a>';
	echo '</translate>';
	echo '</div>';
	echo '</div> <!-- hint -->';
}

if ($member->getStatus() == $USER_STATUS['UNREGISTERED']) {
	$communitylist = CommunityList::getByUidAndStatus($uid, $COMMUNITY_STATUS['ANONYMOUS']);
} else {
	$communitylist = CommunityList::getByUidAndStatus($uid, $COMMUNITY_STATUS['ACTIVE']);
	$communitylist = array_merge($communitylist, CommunityList::getByUidAndStatus($uid, $COMMUNITY_STATUS['INACTIVE']));
}

function RenderProfileAdministratedCommunitiesLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	global $entries_page_offset;
	global $moderated_communities_page_offset;
	global $communities_page_offset;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.UI::RenderUserLink($_REQUEST['uid'], true).'-l'.$user->getLid().'-m'.$moderated_communities_page_offset.'-p'.$communities_page_offset.'-z'.$entries_page_offset.'-a'.$i.'#community_administrator_list">'.$i.'</a>').($i == $page_count?'':' ');
}

if (!empty($communitylist)) {
	asort($communitylist);
	
	$results_count = count($communitylist);
	
	$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'PROFILE_ADMINISTRATED_COMMUNITIES');
	
	$page_count = ceil($results_count / $amount_per_page);
	
	if ($administrated_communities_page_offset > $page_count) $administrated_communities_page_offset = $page_count;
	
	echo '<div class="hint'.($page_count > 1?'':' hintmargin').'" id="community_administrator_list">';
	echo '<div class="hint_title">';
	echo '<translate id="MEMBER_COMMUNITIES_ADMINISTRATOR">';
	echo 'Communities <user_name uid="'.$uid.'"/> administrates';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	
	$communitylist = array_slice($communitylist, ($administrated_communities_page_offset - 1) * $amount_per_page, $amount_per_page, true);

	echo UI::RenderPaging($administrated_communities_page_offset, $page_count, 'RenderProfileAdministratedCommunitiesLink');	
	
	foreach ($communitylist as $xid) describeCommunity($xid, $uid);
	
	echo UI::RenderPaging($administrated_communities_page_offset, $page_count, 'RenderProfileAdministratedCommunitiesLink', true);
	echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
	echo '<div id="administrated_communities_current_amount">';
	if ($amount_per_page > 1) {
		echo '<translate id="PROFILE_COMMUNITIES_BODY_PLURAL">';
		echo 'Currently displaying <integer value="'.$amount_per_page.'"/> communities per page.';
		echo '</translate>';
	} else {
		echo '<translate id="PROFILE_COMMUNITIES_BODY_SINGULAR">';
		echo 'Currently displaying <integer value="'.$amount_per_page.'"/> community per page.';
		echo '</translate>';
	}
	echo '</div>';
	echo '<div id="administrated_communities_change_amount">';
	echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
	echo '<a href="javascript:changeAdministratedCommunitiesAmount();">Change that amount</a>.';
	echo '</translate>';
	echo '</div>';
	echo '<div id="administrated_communities_change_input" style="display:none">';
	echo '<translate id="PROFILE_COMMUNITIES_INPUT_AMOUNT">';
	echo 'Display <input id="administrated_communities_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> communities per page. <a href="javascript:saveAdministratedCommunitiesAmount()">Save</a> <a href="javascript:cancelAdministratedCommunitiesAmount()">Cancel</a>';
	echo '</translate>';
	echo '</div>';
	echo '</div> <!-- hint -->';
}

$communitymoderatorlist = CommunityModeratorList::getByUid($uid);

function RenderProfileModeratedCommunitiesLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	global $entries_page_offset;
	global $administrated_communities_page_offset;
	global $communities_page_offset;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.UI::RenderUserLink($_REQUEST['uid'], true).'-l'.$user->getLid().'-a'.$administrated_communities_page_offset.'-p'.$communities_page_offset.'-z'.$entries_page_offset.'-m'.$i.'#community_moderator_list">'.$i.'</a>').($i == $page_count?'':' ');
}

if (!empty($communitymoderatorlist)) {
	asort($communitymoderatorlist);

	$results_count = count($communitymoderatorlist);
	
	$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'PROFILE_MODERATED_COMMUNITIES');
	
	$page_count = ceil($results_count / $amount_per_page);
	
	if ($moderated_communities_page_offset > $page_count) $moderated_communities_page_offset = $page_count;
	
	echo '<div class="hint'.($page_count > 1?'':' hintmargin').'" id="community_moderator_list">';
	echo '<div class="hint_title">';
	echo '<translate id="MEMBER_COMMUNITIES_MODERATOR">';
	echo 'Communities <user_name uid="'.$uid.'"/> moderates';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	
	$communitymoderatorlist = array_slice($communitymoderatorlist, ($moderated_communities_page_offset - 1) * $amount_per_page, $amount_per_page, true);

	echo UI::RenderPaging($moderated_communities_page_offset, $page_count, 'RenderProfileModeratedCommunitiesLink');
	
	foreach ($communitymoderatorlist as $xid) describeCommunity($xid, $uid);
	
	echo UI::RenderPaging($moderated_communities_page_offset, $page_count, 'RenderProfileModeratedCommunitiesLink', true);
	echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
	echo '<div id="moderated_communities_current_amount">';
	if ($amount_per_page > 1) {
		echo '<translate id="PROFILE_COMMUNITIES_BODY_PLURAL">';
		echo 'Currently displaying <integer value="'.$amount_per_page.'"/> communities per page.';
		echo '</translate>';
	} else {
		echo '<translate id="PROFILE_COMMUNITIES_BODY_SINGULAR">';
		echo 'Currently displaying <integer value="'.$amount_per_page.'"/> community per page.';
		echo '</translate>';
	}
	echo '</div>';
	echo '<div id="moderated_communities_change_amount">';
	echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
	echo '<a href="javascript:changeModeratedCommunitiesAmount();">Change that amount</a>.';
	echo '</translate>';
	echo '</div>';
	echo '<div id="moderated_communities_change_input" style="display:none">';
	echo '<translate id="PROFILE_COMMUNITIES_INPUT_AMOUNT">';
	echo 'Display <input id="moderated_communities_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> communities per page. <a href="javascript:saveModeratedCommunitiesAmount()">Save</a> <a href="javascript:cancelModeratedCommunitiesAmount()">Cancel</a>';
	echo '</translate>';
	echo '</div>';
	echo '</div> <!-- hint -->';
}

$communitymembershiplist = CommunityMembershipList::getByUid($uid);

foreach ($communitymoderatorlist as $xid) unset($communitymembershiplist[$xid]);

function RenderProfileCommunitiesLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	global $entries_page_offset;
	global $administrated_communities_page_offset;
	global $moderated_communities_page_offset;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.UI::RenderUserLink($_REQUEST['uid'], true).'-l'.$user->getLid().'-a'.$administrated_communities_page_offset.'-m'.$moderated_communities_page_offset.'-z'.$entries_page_offset.'-p'.$i.'#community_member_list">'.$i.'</a>').($i == $page_count?'':' ');
}

if (!empty($communitymembershiplist)) {
	asort($communitymembershiplist);
	
	$results_count = count($communitymembershiplist);
	
	$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'PROFILE_COMMUNITIES');
	
	$page_count = ceil($results_count / $amount_per_page);
	
	if ($communities_page_offset > $page_count) $communities_page_offset = $page_count;
	
	echo '<div class="hint'.($page_count > 1?'':' hintmargin').'" id="community_member_list">';
	echo '<div class="hint_title">';
	echo '<translate id="MEMBER_COMMUNITIES_MEMBER_OF">';
	echo 'Communities <user_name uid="'.$uid.'"/> is a member of';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	
	$communitymembershiplist = array_slice($communitymembershiplist, ($communities_page_offset - 1) * $amount_per_page, $amount_per_page, true);

	echo UI::RenderPaging($communities_page_offset, $page_count, 'RenderProfileCommunitiesLink');
	
	foreach ($communitymembershiplist as $xid => $join_time) describeCommunity($xid, $uid);
	
	echo UI::RenderPaging($communities_page_offset, $page_count, 'RenderProfileCommunitiesLink', true);
	echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
	echo '<div id="communities_current_amount">';
	if ($amount_per_page > 1) {
		echo '<translate id="PROFILE_COMMUNITIES_BODY_PLURAL">';
		echo 'Currently displaying <integer value="'.$amount_per_page.'"/> communities per page.';
		echo '</translate>';
	} else {
		echo '<translate id="PROFILE_COMMUNITIES_BODY_SINGULAR">';
		echo 'Currently displaying <integer value="'.$amount_per_page.'"/> community per page.';
		echo '</translate>';
	}
	echo '</div>';
	echo '<div id="communities_change_amount">';
	echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
	echo '<a href="javascript:changeCommunitiesAmount();">Change that amount</a>.';
	echo '</translate>';
	echo '</div>';
	echo '<div id="communities_change_input" style="display:none">';
	echo '<translate id="PROFILE_COMMUNITIES_INPUT_AMOUNT">';
	echo 'Display <input id="communities_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> communities per page. <a href="javascript:saveCommunitiesAmount()">Save</a> <a href="javascript:cancelCommunitiesAmount()">Cancel</a>';
	echo '</translate>';
	echo '</div>';
	echo '</div> <!-- hint -->';
}

$page->endHTML();
$page->render();
?>
