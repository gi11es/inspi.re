<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Bans a user from the website
*/

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/favorite.php');
require_once(dirname(__FILE__).'/../entities/favoritelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$uid = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);

$levels = UserLevelList::getByUid($user->getUid());

if ($uid === null || !in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	header('Location: /Members/s3-l'.$user->getLid());
	exit(0);
}

$member = User::get($uid);
$member->setStatus($USER_STATUS['BANNED']);

$entrylist = array_values(EntryList::getByUidAndStatus($uid, $ENTRY_STATUS['POSTED']));
$entrylist += array_values(EntryList::getByUidAndStatus($uid, $ENTRY_STATUS['DELETED']));

foreach ($entrylist as $eid) {
	$entry = Entry::get($eid);
	$entry->setStatus($ENTRY_STATUS['BANNED']);
	$entry->setRank(null);
	
	$favoritelist = FavoriteList::getByEid($eid);
	foreach ($favoritelist as $uid => $creation_time) {
		try {
			$favorite = Favorite::get($eid, $uid);
			$favorite->delete();
		} catch (FavoriteException $e) {}
	}
		
	$competition = Competition::get($entry->getCid());
	if ($competition->getStatus() == $COMPETITION_STATUS['CLOSED'])
		$competition->calculateRankings(true);
}

header('Location: '.UI::RenderUserLink($uid));

?>