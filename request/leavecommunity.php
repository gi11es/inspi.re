<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Leave an existing community
*/

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitymembership.php');
require_once(dirname(__FILE__).'/../entities/user.php');

$user = User::getSessionUser();

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;

if ($xid !== null) {
	try {
		$community_membership = CommunityMembership::get($xid, $user->getUid());
		$community_membership->delete();
	} catch (CommunityMembershipException $e) {}
	header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$xid.'&left=true');
} else header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());

?>