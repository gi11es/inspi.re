<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Bans a user from the website
*/

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());

if (!in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	exit(0);
}

$competitionlist = CompetitionList::getByStatus($COMPETITION_STATUS['VOTING']);
$competitionlist += CompetitionList::getByStatus($COMPETITION_STATUS['OPEN']);

foreach ($competitionlist as $cid => $start_time) {
	$entrylist = EntryList::getByCidAndStatus($cid, $ENTRY_STATUS['POSTED']);
	foreach ($entrylist as $uid => $eid) {
		$entry = Entry::get($eid);
		echo 'Changing rank of eid='.$eid.' to null<br/>';
		$entry->setRank(null);
	}
}

echo('Location: '.UI::RenderUserLink($uid));

?>