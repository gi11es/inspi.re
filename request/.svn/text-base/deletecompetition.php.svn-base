<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Deletes an existing competition
*/

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$cid = isset($_REQUEST['cid'])?$_REQUEST['cid']:null;

if ($cid !== null) {
	try {
		$competition = Competition::get($cid);
	} catch (CompetitionException $e) {
		header('Location: '.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid());
		exit(0);
	}
	
	$levels = UserLevelList::getByUid($user->getUid());
	
	if (in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) { // Check that this is not a forged request
		$competition = Competition::get($cid);
		$competition->delete();
	}
}

header('Location: '.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid());

?>