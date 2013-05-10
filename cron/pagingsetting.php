#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Index all the old user names
 */

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userpaging.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../constants.php');

$userlist = UserList::getByStatus($USER_STATUS['ACTIVE']);

$start_at = 1;
$started = false;

$usercache = User::getArray(array_keys($userlist));

foreach ($usercache as $uid => $user) {
	UserPaging::setPagingValue($uid, 'DISCUSSION_THREAD_POSTS', $user->getPostsPerPage());
	UserPaging::setPagingValue($uid, 'BOARD_THREADS', $user->getThreadsPerPage());
	UserPaging::setPagingValue($uid, 'THEME_LIST_THEMES', $user->getThemesPerPage());
	UserPaging::setPagingValue($uid, 'HALL_OF_FAME_COMPETITIONS', $user->getHOFCompetitionsPerPage());
	UserPaging::setPagingValue($uid, 'HOME_ENTRIES', $user->getHomeEntriesPerPage());
	UserPaging::setPagingValue($uid, 'HOME_PRIVATE_MESSAGES', $user->getPrivateMessagesPerPage());
	UserPaging::setPagingValue($uid, 'HOME_FAVORITES', $user->getFavoritesPerPage());
	UserPaging::setPagingValue($uid, 'COMMUNITIES_COMMUNITIES', $user->getCommunitiesPerPage());
}

?>