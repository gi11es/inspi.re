<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Updates the name of a given user
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/string.php');

if (isset($_REQUEST['name'])) {
	$user = User::getSessionUser();
	
	$new_name = trim(stripslashes($_REQUEST['name']));
	$new_name = preg_replace("/\s+/i", " ", $new_name);
	$user->setName(substr($new_name, 0, 150));
	
	$name = $user->getUniqueName();
	if (strcmp($name, '') == 0)
		$name = $user->getSafeEmail();
	
	echo $name;
} else echo '0';

?>