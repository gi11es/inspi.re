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

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;

try {
    $community = Community::get($xid);
    if ($user->getUid() == $community->getUid()) {
        $cid = $community->startNextCompetition();
        header('Location: '.$PAGE['COMPETE'].'?lid='.$user->getLid().'&xid='.$xid);
        exit(0);
    }
} catch (CommunityException $e) {}

header('Location: '.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid());
exit(0);

?>