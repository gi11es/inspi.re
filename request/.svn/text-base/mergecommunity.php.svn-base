<?php

set_time_limit(3600);

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Merge two communities into one
*/

require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/alertvariablelist.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitylabel.php');
require_once(dirname(__FILE__).'/../entities/communitylabellist.php');
require_once(dirname(__FILE__).'/../entities/communitymembership.php');
require_once(dirname(__FILE__).'/../entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/../entities/communitymoderator.php');
require_once(dirname(__FILE__).'/../entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadindex.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadindexlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../entities/discussionpostindex.php');
require_once(dirname(__FILE__).'/../entities/discussionpostindexlist.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:false;
$merge_token = isset($_REQUEST['merge_token'])?$_REQUEST['merge_token']:false;

try {
	$community = Community::get($xid);
	$old_name = String::fromaform($community->getName());
	$target_community = Community::get(Token::get($merge_token));
	$target_xid = $target_community->getXid();
	
	$target_community->setActiveMemberCount($community->getActiveMemberCount() + $target_community->getActiveMemberCount());
	
} catch (Exception $e) {
	header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
	exit(0);
}

if ($user->getUid() != $community->getUid() || $user->getUid() != $target_community->getUid()) {
	header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
	exit(0);
}

$community->setStatus($COMMUNITY_STATUS['DELETED']);

// Move memberships to new community

$membershiplist = array_keys(CommunityMembershipList::getByXidAndStatus($xid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']));
$target_membershiplist = array_keys(CommunityMembershipList::getByXidAndStatus($target_xid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']));

foreach (array_diff($membershiplist, $target_membershiplist) as $uid) {
	try {
		$membership = CommunityMembership::get($target_xid, $uid); // Just in case there is one with a different status
		$membership->setStatus($COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);
	} catch (CommunityMembershipException $e) {
		$membership = new CommunityMembership($target_xid, $uid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);
	}
}

// Delete membership information from the old community

foreach ($membershiplist as $uid) try {
	$membership = CommunityMembership::get($xid, $uid);
	$membership->delete();
} catch (CommunityMembershipException $e) {}

// Transfer moderators to the new community

$moderatorlist = CommunityModeratorList::getByXid($xid);
$target_moderatorlist = CommunityModeratorList::getByXid($target_xid);

foreach (array_diff($moderatorlist, $target_moderatorlist) as $uid) $moderator = new CommunityModerator($target_xid, $uid);

// Delete moderators from the old community

foreach ($moderatorlist as $uid) try {
	$moderator = CommunityModerator::get($xid, $uid);
	$moderator->delete();
} catch (CommunityModeratorException $e) {}

// Delete labels associated with the old community

$labellist = CommunityLabelList::getByXid($xid);
foreach ($labellist as $clid) {
	try {
		$label = CommunityLabel::get($xid, $clid);
		$label->delete();
	} catch (CommunityLabelException $e) {}
}

// Move themes to the new community

$themelist = ThemeList::getByXid($xid);
$themecache = Theme::getArray($themelist);

foreach ($themecache as $tid => $theme) $theme->setXid($target_xid);

// Move competitions to the new community

$competitionlist = CompetitionList::getByXid($xid);
$competitioncache = Competition::getArray(array_keys($competitionlist));
foreach ($competitioncache as $cid => $competition) $competition->setXid($target_xid);

// Move discussion threads, posts and their indexes to the new community

$discussionthreadlist = DiscussionThreadList::getByXid($xid);
foreach ($discussionthreadlist as $nid => $creation_time) {
	try {
		$discussionthread = DiscussionThread::get($nid);
		$discussionthread->setXid($target_xid);
		$threadwordlist = DiscussionThreadIndexList::getByNid($nid);
		foreach ($threadwordlist as $word) try {
			$discussionthreadindex = DiscussionThreadIndex::get($word, $nid);
			$discussionthreadindex->setXid($target_xid);
		} catch (DiscussionThreadIndexException $e) {}
		
		$discussionpostlist = DiscussionPostList::getByNid($nid);
		foreach ($discussionpostlist as $oid => $creation_time) {
			$postwordlist = DiscussionPostIndexList::getByOid($oid);
			foreach ($postwordlist as $word) try {
				$discussionpostindex = DiscussionPostIndex::get($word, $oid);
				$discussionpostindex->setXid($target_xid);
			} catch (DiscussionPostIndexException $e) {} 
		}
	} catch (DiscussionThreadException $e) {}
}

$alertvariablelist = AlertVariableList::getByName('xid');
foreach ($alertvariablelist as $aid) try {
	$alertvariable = AlertVariable::get($aid, 'xid');
	if ($alertvariable->getValue() == $xid) $alertvariable->setValue($target_xid);
} catch (AlertVariableException $e) {}

$alert = new Alert($ALERT_TEMPLATE_ID['COMMUNITY_MERGED']);
$aid = $alert->getAid();
$alert_variable = new AlertVariable($aid, 'old_name', $old_name);
$alert_variable = new AlertVariable($aid, 'target_xid', $target_xid);

$memberscache = User::getArray($membershiplist);

foreach ($memberscache as $uid => $user) if ($user->getStatus() == $USER_STATUS['ACTIVE']) {
	$alert_instance = new AlertInstance($aid, $uid, $ALERT_INSTANCE_STATUS['ASYNC']);
}

$community->delete();

echo('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$target_xid.'&merge=true');

?>