<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	This is the error page reached when a user requests a non-existing page
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');


$user = User::getSessionUser();

$url = @parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$param_map = array(
	's' => 'script',
	'l' => 'lid',
	'x' => 'xid',
	'c' => 'cid',
	'n' => 'nid',
	'u' => 'uid',
	'p' => 'page',
	't' => 'scrollto',
	'e' => 'eid',
	'z' => 'entriespage',
	'a' => 'apage',
	'm' => 'mpage',
	'y' => 'successblock',
	'd' => 'successappeal',
	'w' => 'successpm',
	'v' => 'successunblock',
	'b' => 'unused'
);

// To translate: /Member/ /Members/ /Prize/ /Bug-Report/ /Terms-And-Conditions/

$script_map = array(
	1 => 'themelist.php',
	2 => 'member.php',
	3 => 'members.php',
	4 => 'prize.php',
	5 => 'aboutus.php',
	6 => 'maintenance.php',
	7 => 'bugreport.php',
	8 => 'legal.php'
);

$rawparams = array();
$success = false;
if (substr_count($url, '/') == 1) {
	$custom_url = strtolower(str_replace('/', '', @parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
	
	$userlist = UserList::getByCustomURL($custom_url);
	
	if (!empty($userlist)) {
		$uid = array_pop($userlist);
		$levels = UserLevelList::getByUid($uid);
		
		if (in_array($USER_LEVEL['PREMIUM'], $levels)) {
			$success = true;
			
			$params = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
			if (substr_count($params, '-') > 0) {
				$rawparams = explode('-', $params);
			}
			$rawparams[] = 's2';
			$rawparams[] = 'u'.$uid;
		}
	}
}

if (substr_count($url, '/') > 1 || $success) {
	if (!$success) {
		$url = preg_replace('/(\/.+)\//si', '', $url);
		$rawparams = explode('-', $url);
	}
	$parameters = array();
	$script = null;
	
	foreach ($rawparams as $rawparam) {
		if (isset($param_map[substr($rawparam, 0, 1)])) {
			$parameters[$param_map[substr($rawparam, 0, 1)]] = substr($rawparam, 1);
		}
	}
	
	if (isset($parameters['script'])) {
		$script = $parameters['script'];
		unset($parameters['script']);
	}
	
	foreach (explode('&', parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)) as $parameter) {
		$breakdown = explode('=', $parameter);
		if (count($breakdown) == 2) {
			$_REQUEST[$breakdown[0]] = $breakdown[1];
		}
	}
	
	foreach ($parameters as $param => $value) {
		$_REQUEST[$param] = $value;
	}
	
	if (isset($script_map[$script])) {
		include($script_map[$script]);
		$success = true;
	}
}

if (!$success) {
	header('Status: 404 Not Found'); 
	
	$page = new Page('404', 'INFORMATION', $user);
	
	$page->startHTML();
	
	echo '<div class="warning hintmargin">';
	echo '<div class="warning_title">';
	echo '<translate id="ERROR_404">The page you\'ve requested could not be found</translate>';
	echo '</div> <!-- warning_title -->';
	
	echo htmlentities($_SERVER['REQUEST_URI']);
	
	echo ' <translate id="ERROR_404_BODY">is not a valid page on inspi.re</translate>';
	echo '</div> <!-- warning -->';
	
	$page->endHTML();
	$page->render();
}
?>