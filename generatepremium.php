<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Generates a premium code manually
*/

require_once(dirname(__FILE__).'/entities/premiumcode.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());
if (!in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	header('Location: '.$PAGE['PREMIUM'].'?lid='.$user->getLid());
	exit(0);
}

$page = new Page('PREMIUM', 'HOME', $user);

$page->startHTML();

echo '<div class="hint hintmargin">',
	 '<div class="hint_title">',
	 '<translate id="GENERATE_PREMIUM_HINT_TITLE">',
	 'Generate premium membership code',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<form class="generate" method="post">',
	 'Validity duration (in days): ',
	 '<input name="duration" value="" type="text" maximum="10" numerical="true" />',
	 '<input id="generate_submit" type="submit" value="Generate">',
	 '</form>',

	 '<div id="code">';

if (isset($_REQUEST['duration'])) {
	$duration = 86400 * $_REQUEST['duration'];
	$code = new PremiumCode($duration);
	echo $code->getDisplayCode();
}

echo '</div> <!-- code -->';

$page->endHTML();
$page->render();
?>
