#!/usr/bin/php
<?php
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../constants.php');

$uids = array_keys(UserList::getByStatus($USER_STATUS['UNREGISTERED'], false));

foreach ($uids as $uid) try {
	$user = User::get($uid, false);
	if ((time() - $user->getCreationTime()) > 86400 && strcmp($user->getEmail(), '') == 0) {
		$user->delete();
	} elseif ((time() - $user->getCreationTime()) > 1209600) {
		$user->delete();
	}
} catch (UserException $e) {}

?>