#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Delete favorites whose users has deleted his/her account
 */

require_once(dirname(__FILE__).'/../entities/favorite.php');
require_once(dirname(__FILE__).'/../entities/favoritelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('favoritecleanup.php')) {
	echo 'Had to abort favorite cleanup cron job, it was already running';
} else {
	$favorites = FavoriteList::getAll();
	foreach ($favorites as $favorite) try {
		$user = User::get($favorite['uid']);
	} catch (UserException $e) {
		$fav = Favorite::get($favorite['eid'], $favorite['uid']);
		$fav->delete();
	}
}

?>
