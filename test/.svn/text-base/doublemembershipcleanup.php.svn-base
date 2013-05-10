#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Delete membership for users that are also admins of a community
 */

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitylist.php');
require_once(dirname(__FILE__).'/../entities/communitymembership.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('doublemembershipcleanup.php')) {
	echo 'Had to abort double membership cleanup cron job, it was already running';
} else {
	$communitylist = CommunityList::getByStatus($COMMUNITY_STATUS['ACTIVE']);
	$communitylist = array_merge($communitylist, CommunityList::getByStatus($COMMUNITY_STATUS['INACTIVE']));
	
	$communitycache = Community::getArray($communitylist, false);
	
	foreach ($communitycache as $xid => $community) {
	    $admin_uid = $community->getUid();
	    
	    try {
	        $membership = CommunityMembership::get($xid, $admin_uid);
	        echo 'Uid = ',$admin_uid,' is both admin and member of community xid = ',$xid,"\r\n";
	        $membership->delete();
	    } catch (CommunityMembershipException $e) {}
	}
}

?>
