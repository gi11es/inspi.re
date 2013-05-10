<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Activation page for premium membership codes
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
$page = new Page('PREMIUM', 'HOME', $user);
$page->addJavascript('PREMIUM_ACTIVATE');

if (!isset($_REQUEST['uid'])) {
	header('Location: '.$PAGE['PREMIUM'].'?lid='.$user->getLid());
	exit(0);
} else {
	$uid = $_REQUEST['uid'];
	try {
		$member = User::get($uid);
	} catch (UserException $e) {
		header('Location: '.$PAGE['PREMIUM'].'?lid='.$user->getLid());
		exit(0);
	}
}

$page->addJavascriptVariable('request_premium_activate', $REQUEST['PREMIUM_ACTIVATE']);

$page->startHTML();

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo '<translate id="PREMIUM_ACTIVATE_HINT_TITLE">';
echo 'Premium membership activation';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="PREMIUM_ACTIVATE_HINT_BODY">';
echo 'Use the premium membership code that you\'ve purchased and received by email. If you need to get one, they are sold on the <a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'">premium membership page</a>.';
echo '</translate>';
echo '</div> <!-- hint -->';

if ($user->getUid() == $uid) {
	echo '<translate id="PREMIUM_ACTIVATE_YOURSELF">';
	echo 'Simply enter the premium membership code below in order to activate or extend your premium membership. The change will be effective as soon as you\'ve activated your code.';
	echo '</translate>';
} else try {
	echo '<translate id="PREMIUM_ACTIVATE_SOMEONE">';
	echo 'Simply enter the premium membership code below in order to activate or extend <user_name class="blackusername" uid="'.$uid.'"/>\'s premium membership. The change will be effective as soon as you\'ve activated the code.';
	echo '</translate>';
} catch (UserException $e) {
	header('Location: '.$PAGE['PREMIUM'].'?lid='.$user->getLid());
	exit(0);
}

echo '<form class="activate" method="post" action="javascript:submitCode();">';
echo '<translate id="PREMIUM_ACTIVATE_LABEL">';
echo 'Premium membership code:';
echo '</translate>';
echo '<input id="activate_code" value="" type="text"/>';
echo '<input id="activate_submit" type="submit" value="<translate id="PREMIUM_ACTIVATE_SUBMIT">Activate</translate>">';
echo '<input id="uid" type="hidden" name="uid" value="'.$uid.'"/>';
echo '</form>';

echo '<div id="activation_result">';
echo '</div> <!-- activation_result -->';

$page->endHTML();
$page->render();
?>
