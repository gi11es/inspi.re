<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
      Find the translations that contain a given string
*/

require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();

if (isset($_REQUEST['text'])) {
	$text = trim(urldecode(stripslashes($_REQUEST['text'])));
	$names = I18N::getAllNames($user->getLid());
	$translations = I18N::getArray($user->getLid(), $names);
	$results = array();
	
	foreach ($translations as $name => $translation) {
		if (strcmp($text, '') == 0 || stristr($translation->getText(), $text))
			$results[$name] = array('translation' => $translation->getText());
	}
	
	$english_originals = I18N::getArray($LANGUAGE['EN'], array_keys($results));
	
	foreach ($results as $name => $result)
		$results[$name]['english'] = $english_originals[$name]->getText();
	
	if (count($results) == 0)
		echo json_encode($results);
	else
		echo json_encode(array('results' => $results));
}

?>