#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Reevaluate the cost of actions on the website based on weekly metrics
 */

require_once(dirname(__FILE__).'/../entities/communitylist.php');
require_once(dirname(__FILE__).'/../entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/pointsvalue.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../constants.php');

$changed = false;

$entrylist = EntryList::getCreated7Days(false);

$entriescount = count($entrylist);

$votescount = 0;

foreach ($entrylist as $eid => $creation_time) {
	$entryvotelist = EntryVoteList::getByEid($eid, false);
	$votescount += count($entryvotelist);
	unset($entryvotelist);
}

$averagevotes = $votescount/$entriescount;

if (abs($averagevotes - 30) > 2.5) {
	$pointsvalue = PointsValue::get($POINTS_VALUE_ID['ENTRY_POSTING'], false);
	if ($averagevotes < 30) {
		$changed = true;
		$new_value = $pointsvalue->getValue() - 5;
		//$pointsvalue->setValue($new_value);
		echo 'The average amount of votes is too low ('.$averagevotes.'), changing the points cost for entering, which is now '.$new_value."\r\n";
	} elseif ($pointsvalue->getValue() < -10) {
		$changed = true;
		$new_value = $pointsvalue->getValue() + 5;
		//$pointsvalue->setValue($new_value);
		echo 'The average amount of votes is high enough ('.$averagevotes.'), changing the points cost for entering, which is now '.$new_value."\r\n";
	}
}

$communitylist = CommunityList::getByStatus($COMMUNITY_STATUS['ACTIVE'], false);
$communitylist = array_merge($communitylist, CommunityList::getByStatus($COMMUNITY_STATUS['INACTIVE'], false));

$communitiescount = count($communitylist);
$membershipcount = 0;

foreach ($communitylist as $xid) {
	$communitymembershiplist = CommunityMembershipList::getByXidAndStatus($xid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE'], false);
	$membershipcount += count($communitymembershiplist);
	unset($communitymembershiplist);
}

$averagemembers = $membershipcount/$communitiescount;

if (abs($averagemembers - 300) > 10) {
	$pointsvalue = PointsValue::get($POINTS_VALUE_ID['COMMUNITY_CREATING'], false);
	if ($averagemembers < 300) {
		$changed = true;
		$new_value = $pointsvalue->getValue() - 50;
		//$pointsvalue->setValue($new_value);
		echo 'The average amount of members per community is too low ('.$averagemembers.'), changing the points cost for creating a community, which is now '.$new_value."\r\n";
	} elseif ($pointsvalue->getValue() < -250) {
		$changed = true;
		$new_value = $pointsvalue->getValue() + 50;
		//$pointsvalue->setValue($new_value);
		echo 'The average amount of members per community is high enough ('.$averagemembers.'), changing the points cost for creating a community, which is now '.$new_value."\r\n";
	}
}

/*if ($changed) {
	// Some values changed, we need to send an alert to the users
	$alert = new Alert($ALERT_TEMPLATE_ID['POINTS_REEVALUATED']);
	$aid = $alert->getAid();
	$alert_variable = new AlertVariable($aid, 'href', $PAGE['HELP'].'#points');
	
	$userlist = UserList::getByStatus($USER_STATUS['ACTIVE']);
	
	foreach ($userlist as $uid => $creation_time) $alert_instance = new AlertInstance($aid, $uid, $ALERT_INSTANCE_STATUS['NEW']);
}*/

?>