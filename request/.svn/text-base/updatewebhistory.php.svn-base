<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Updates the history information about other websites a user has visited in the past
*/

require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/user.php');

if (isset($_REQUEST['history'])) {
	$user = User::getSessionUser();
	
	$visited_websites = json_decode(stripslashes($_REQUEST['history']));
	$old_visited_websites = $user->getWebHistoryURLs();
	$new_visited_websites = array_diff($visited_websites, $old_visited_websites);
	
	if (empty($new_visited_websites)) {
		$user->updateWebHistoryCheckLastTime();
		echo 'No new website history entry';
	} else foreach ($new_visited_websites as $url) {
		$user->insertWebHistoryURL($url);
		echo "Added $url to the website history\r\n";
	}
}

?>