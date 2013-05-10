<?php

/** 
 * Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 *       
 * Save a community from deletion for another 4 weeks
*/

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/pointsvalue.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['COMMUNITY_CREATING']);
$points_community_creating = $pointsvalue->getValue();

try {
    $community = Community::get($_REQUEST['xid']);
} catch (CommunityException $e) {
    header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());
    exit(0);
}

if ($community->getStatus() != $COMMUNITY_STATUS['INACTIVE']) {
    header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$community->getXid());
    exit(0);
}

try {
    $user->givePoints($points_community_creating);
    $community->setStatus($COMMUNITY_STATUS['ACTIVE']);
    $community->setInactiveSince($community->getInactiveSince() + 2419200);
    header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$community->getXid().'&saved=true');
} catch (UserException $e) {
    header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$community->getXid().'&saved=false');
}

?>
