#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Index all the old user names
 */

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/usernameindex.php');
require_once(dirname(__FILE__).'/../entities/usernameindexlist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../constants.php');

$userlist = UserList::getByStatus($USER_STATUS['ACTIVE']);

$start_at = 1;
$started = false;

foreach ($userlist as $uid => $creation_time) {
	if ($uid == $start_at) $started = true;
	
	if ($started) {
		$user = User::get($uid);
		$new_name = $user->getName();
		
		if ($new_name !== null) {
			$new_name = mb_strtolower($new_name, 'UTF-8');
			$newchunklist = array();
			
			for ($i = 1; $i <= mb_strlen($new_name, 'UTF-8'); $i++) {
				for ($j = 0; $j <= mb_strlen($new_name, 'UTF-8') - $i; $j++) {
					$chunk = mb_substr($new_name, $j, $i, 'UTF-8');
					if (!isset($newchunklist[$chunk])) $newchunklist[$chunk] = 1;
					else $newchunklist[$chunk]++;	
				}
			}
			
			foreach ($newchunklist as $chunk => $count) $usernameindex = new UserNameIndex($chunk, $uid, $count);
	
			echo $uid."\r\n";
		}
	}
}

?>