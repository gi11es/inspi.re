#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Calculates how many entries users have entered on the website so far
 */

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitylist.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../constants.php');

$communitylist = CommunityList::getByStatus($COMMUNITY_STATUS['ACTIVE']);
$communitylist = array_merge($communitylist, CommunityList::getByStatus($COMMUNITY_STATUS['INACTIVE']));

$themequeuecount = array();
$i = 0;

foreach ($communitylist as $xid) {
	$themelist = ThemeList::getByXidAndStatus($xid, $THEME_STATUS['SUGGESTED']);
	$themequeuecount[$xid] = count($themelist);
	if (count($themelist) < 4) {
		$community = Community::get($xid);
		if ($community->getThemeRestrictUsers()) unset($themequeuecount[$xid]); else $i++;
	}
}

echo $i.' out of '.count($themequeuecount).' communities have less than 4 themes in their queue'."\r\n";

?>