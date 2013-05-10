<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Updates the profile preferences of a given user
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/string.php');

if (isset($_REQUEST['name']) 
	&& isset($_REQUEST['communityfiltericons']) 
	&& isset($_REQUEST['displayrank'])
	&& isset($_REQUEST['hideads'])
	&& isset($_REQUEST['alertemail'])
	&& isset($_REQUEST['markup'])
	&& isset($_REQUEST['allowsales'])
	&& isset($_REQUEST['custom_url'])
	&& isset($_REQUEST['translate'])
	&& isset($_REQUEST['description'])) {
	
	$user = User::getSessionUser();
	$levels = UserLevelList::getByUid($user->getUid());
	$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
	
	$custom_url = strtolower(rawurlencode(preg_replace('/[\s\/?#.\\!@#&%=;,|+*^~]+/', '', stripslashes($_REQUEST['custom_url']))));
	
	if ($ispremium && strcasecmp($custom_url, $user->getCustomURL()) != 0)
		$user->setCustomURL($custom_url);
	
	$user->setCommunityFilterIcons(strcasecmp($_REQUEST['communityfiltericons'], 'true') == 0);
	$user->setDisplayRank(strcasecmp($_REQUEST['displayrank'], 'true') == 0);
	$user->setDescription(substr(stripslashes($_REQUEST['description']), 0, 2000));
	
	$hideads = strcasecmp($_REQUEST['hideads'], 'true') == 0;
	if ($ispremium) $user->setHideAds($hideads);
	
	$user->setTranslate(strcasecmp($_REQUEST['translate'], 'true') == 0);
	
	$user->setAllowSales(strcasecmp($_REQUEST['allowsales'], 'true') == 0);
	
	$markup = $_REQUEST['markup'];
	
	if (!$ispremium && $markup != 0 && $markup != 10) $markup = 10;
	
	$user->setMarkup($markup);
	
	$alertemail = strcasecmp($_REQUEST['alertemail'], 'true') == 0;
	$user->setAlertEmail($alertemail);
	
	$new_name = trim(stripslashes($_REQUEST['name']));
	$new_name = preg_replace("/\s+/i", " ", $new_name);
	$user->setName(substr($new_name, 0, 150));
	
	$name = $user->getUniqueName();
	if (strcmp($name, '') == 0)
		$name = $user->getSafeEmail();
	
	echo $name;
} else echo '0';

?>