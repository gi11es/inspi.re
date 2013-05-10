<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Checks the username and password submitted by the user in the login form
*/

require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();
$lid = $user->getLid();

$levels = UserLevelList::getByUid($user->getUid());

if (isset($_REQUEST['name']) && isset($_REQUEST['translation']) && in_array($USER_LEVEL['TRANSLATOR'][$lid], $levels)) {
	try {
		$latest = I18N::getLatest($lid, $_REQUEST['name']);
		$latest = $latest->getText();
	} catch (I18NException $e) {
		$latest = '';
	}
	
	$new = stripslashes($_REQUEST['translation']);

	I18N::set($lid, stripslashes($_REQUEST['name']), $new, false, $user->getUid());
	
	$outdated = I18N::getOutdated($lid);

	$total_amount = count(I18N::getAllNames($LANGUAGE_SOURCE[$lid]));
	$percent = round(100 * ($total_amount - count($outdated)) / $total_amount, 3);
	$leven = @levenshtein($latest, $new);
	
	if ($leven == -1) $leven = 255;
	
	if ($leven > 5) $user->givePoints(ceil($leven / 25));
	
	$user->setLastActivity();
	
	Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$user->getUid().'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_TRANSLATE"><user_name uid="'.$user->getUid().'"/> translated part of the website into <language_name lid="'.$lid.'"/></translate></div>');

	$result = array('result' => 'success', 'div' => UI::RenderTranslationPercentLeft($user, $percent, true));
	
	echo json_encode($result);
}

?>