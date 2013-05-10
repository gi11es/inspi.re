<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Updates a user's custom URL if available, returns error otherwise
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');

$user = User::getSessionUser();

if (isset($_REQUEST['custom_url'])) {
	$custom_url = strtolower(rawurlencode(preg_replace('/[\s\/?#.\\!@#&%=;,|+*^~]+/', '', $_REQUEST['custom_url'])));
	
	$userlist = UserList::getByCustomURL($custom_url);
	
	if (strcmp($custom_url, '') == 0) {
		echo '2';
		exit(0);
	}
	
	if (in_array($user->getUid(), $userlist)) {
		echo '1';
	} elseif (empty($userlist) && !file_exists($WEBSITE_LOCAL_PATH.rawurldecode($custom_url))) {
		$user->setCustomURL($custom_url);
		echo '1';
	} else echo '0';
} else echo '0';

?>