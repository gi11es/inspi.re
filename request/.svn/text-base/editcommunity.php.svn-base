<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Create a new community or udpate an existing one
*/

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitylabel.php');
require_once(dirname(__FILE__).'/../entities/communitylabellist.php');
require_once(dirname(__FILE__).'/../entities/communitymembership.php');
require_once(dirname(__FILE__).'/../entities/pointsvalue.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();

$name = isset($_REQUEST['name'])?trim(stripslashes($_REQUEST['name'])):false;
$description = isset($_REQUEST['description'])?stripslashes($_REQUEST['description']):false;
$rules = isset($_REQUEST['rules'])?stripslashes($_REQUEST['rules']):false;
$frequency = isset($_REQUEST['frequency'])?floatval($_REQUEST['frequency']):false;
$enter_length = isset($_REQUEST['enter_length'])?floatval($_REQUEST['enter_length']):false;
$vote_length = isset($_REQUEST['vote_length'])?floatval($_REQUEST['vote_length']):false;
$time_shift = isset($_REQUEST['time_shift'])?intval($_REQUEST['time_shift']):false;
$maximum_theme_count = isset($_REQUEST['maximum_theme_count']) && isset($_REQUEST['maximum_theme_count_checkbox']) && strcasecmp($_REQUEST['maximum_theme_count_checkbox'], 'on') == 0?intval($_REQUEST['maximum_theme_count']):null;
if ($maximum_theme_count <= 0) $maximum_theme_count = null;
$maximum_theme_count_per_member = isset($_REQUEST['maximum_theme_count_per_member']) && isset($_REQUEST['maximum_theme_count_per_member_checkbox']) && strcasecmp($_REQUEST['maximum_theme_count_per_member_checkbox'], "on") == 0?intval($_REQUEST['maximum_theme_count_per_member']):null;
if ($maximum_theme_count_per_member <= 0) $maximum_theme_count_per_member = null;
$theme_minimum_score = isset($_REQUEST['theme_minimum_score']) && isset($_REQUEST['theme_minimum_score_checkbox']) && strcasecmp($_REQUEST['theme_minimum_score_checkbox'], 'on') == 0?intval($_REQUEST['theme_minimum_score']):null;
if ($theme_minimum_score > 0) $theme_minimum_score = null;
$theme_cost = isset($_REQUEST['theme_cost'])?intval($_REQUEST['theme_cost']):0;
if ($theme_cost < 0) $theme_cost = 0;

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:false;
$lid = isset($_REQUEST['lid'])?$_REQUEST['lid']:false;
$theme_restrict_users = isset($_REQUEST['theme_restrict_users_checkbox']) && strcasecmp($_REQUEST['theme_restrict_users_checkbox'], 'on') == 0;

if (isset($_REQUEST['labels_input'])) {
	$labellist = json_decode($_REQUEST['labels_input']);
	if (!is_array($labellist))
		$labellist = array();
} else $labellist = array();

switch ($user->getStatus()) {
	case $USER_STATUS['UNREGISTERED']:
		$status = $COMMUNITY_STATUS['ANONYMOUS'];
		break;
	case $USER_STATUS['BANNED']:
		$status = $COMMUNITY_STATUS['BANNED'];
		break;
	default:
		$status = $COMMUNITY_STATUS['ACTIVE'];
}

if ($xid !== false && $name !== false && $frequency !== false && $enter_length !== false && $vote_length !== false && $time_shift !== false && $lid !== false) {
	// We're updating an existing community
	$community = Community::get($xid);
	if ($community->getUid() == $user->getUid()) {
		// Lazy setting to avoid too many calls to the cache and the DB
		if (strcmp($name, $community->getName()) != 0) $community->setName($name);
		if (strcmp($description, $community->getDescription()) != 0) $community->setDescription($description);
		if (strcmp($rules, $community->getRules()) != 0) $community->setRules($rules);
		if ($frequency != $community->getFrequency()) $community->setFrequency($frequency);
		if ($enter_length != $community->getEnterLength()) $community->setEnterLength($enter_length);
		if ($vote_length != $community->getVoteLength()) $community->setVoteLength($vote_length);
		if ($time_shift != $community->getTimeShift()) $community->setTimeShift($time_shift);
		if ($maximum_theme_count != $community->getMaximumThemeCount()) $community->setMaximumThemeCount($maximum_theme_count);
		if ($maximum_theme_count_per_member != $community->getMaximumThemeCountPerMember()) $community->setMaximumThemeCountPerMember($maximum_theme_count_per_member);
		if ($theme_minimum_score != $community->getThemeMinimumScore()) {
			$community->setThemeMinimumScore($theme_minimum_score);
			$themelist = ThemeList::getByXidAndStatus($xid, $THEME_STATUS['SUGGESTED']);
			if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
				foreach (ThemeList::getByUidAndStatus($user->getUid(), $THEME_STATUS['ANONYMOUS']) as $tid => $local_xid)
					if ($local_xid == $xid) $themelist[]=$tid;
			}
			foreach ($themelist as $tid) {
				$theme = Theme::get($tid);
				if ($theme->getScore($user) < $theme_minimum_score) $theme->setStatus($THEME_STATUS['DELETED']);
			}
		}
		if ($lid != $community->getLid()) $community->setLid($lid);
		if ($theme_restrict_users != $community->getThemeRestrictUsers()) $community->setThemeRestrictUsers($theme_restrict_users);
		if ($theme_cost != $community->getThemeCost()) $community->setThemeCost($theme_cost);
		
		$existinglabellist = CommunityLabelList::getByXid($xid);
		$labelcount = count($existinglabellist);
		
		foreach (array_diff($existinglabellist, $labellist) as $clid) try {
			$label = CommunityLabel::get($xid, $clid);
			$label->delete();
			$labelcount--;
		} catch (CommunityLabelException $e) {}
		
		foreach (array_diff($labellist, $existinglabellist) as $clid) if ($labelcount < 5) {
			$label = new CommunityLabel($community->getXid(), $clid);
			$labelcount++;
		}
	}
	header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$xid);
} elseif ($name !== false && $frequency !== false && $enter_length !== false && $vote_length !== false && $time_shift !== false && $lid !== false) {
	// We're creating a new community
	try {
		$pointsvalue = PointsValue::get($POINTS_VALUE_ID['COMMUNITY_CREATING']);
		$points_create_community = $pointsvalue->getValue();
		
		$user->givePoints($points_create_community);
		$community = new Community($name, $description, $rules, $frequency, $enter_length, $vote_length, $time_shift, $maximum_theme_count, $maximum_theme_count_per_member, $theme_minimum_score, $theme_restrict_users, $theme_cost, $user->getUid(), $lid, $status, -$points_create_community);
		$communitymembership = new CommunityMembership($community->getXid(), $GOOGLE_UID, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);
		
		$labelcount = 0;
		foreach ($labellist as $clid) {
			$label = new CommunityLabel($community->getXid(), $clid);
			$labelcount++;
			if ($labelcount == 5) break;
		}
		
		header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$community->getXid());
	} catch (UserException $e) {
		header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
	}
} else header('Location: '.$PAGE['EDIT_COMMUNITY'].'?lid='.$user->getLid().($xid === false?'':'&xid='.$xid));

?>