<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Visually hide a given competition
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/competitionhide.php');
require_once(dirname(__FILE__).'/../entities/competitionhidelist.php');

$user = User::getSessionUser();

if (isset($_REQUEST['cid']) && isset($_REQUEST['hide'])) {
	$competitionhidelist = CompetitionHideList::getByUid($user->getUid());
	
	if (strcasecmp($_REQUEST['hide'], 'true') == 0) {
		// hide
		if (!in_array($_REQUEST['cid'], $competitionhidelist))
			$competitionhide = new CompetitionHide($_REQUEST['cid'], $user->getUid());
	} else {
		// unhide
		if (in_array($_REQUEST['cid'], $competitionhidelist)) try {
			$competitionhide = CompetitionHide::get($_REQUEST['cid'], $user->getUid());
			$competitionhide->delete();
		} catch (CompetitionHideException $e) {}
	}
}
?>
0