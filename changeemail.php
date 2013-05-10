<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page that lets users request an email that will let them retrieve their lost password
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('SETTINGS', 'HOME', $user);
$page->addJavascript('CHANGE_EMAIL');
$page->addJavascriptVariable('request_check_email', $REQUEST['CHECK_EMAIL']);

$page->startHTML();

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo '<translate id="CHANGE_EMAIL_HINT_TITLE">';
echo 'Change your account\'s email address';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="CHANGE_EMAIL_HINT_BODY">';
echo 'The change will be effective as soon as you click on the link provided in the confirmation email.';
echo '</translate>';
echo '</div> <!-- hint -->';

echo '<div id="email_change">';

if (isset($_REQUEST['success']) && strcasecmp($_REQUEST['success'], 'true') == 0) {
	echo '<translate id="CHANGE_EMAIL_SUCCESS">';
	echo 'Your new email address (<b><string value="'.$user->getEmail().'"/></b>) has been successfully registered.';
	echo '</translate>';
} elseif (isset($_REQUEST['progress'])) {
	if (strcasecmp($_REQUEST['progress'], 'true') == 0) {
		echo '<translate id="CHANGE_EMAIL_PROGRESS_TRUE">';
		echo 'A confirmation email has been sent to your new email address. Please click on the link provided in that email in order to make your address change effective.';
		echo '</translate>';
	} else {
		echo '<translate id="CHANGE_EMAIL_PROGRESS_FALSE">';
		echo 'An error occured when attempting to update your email address. You can <a href="'.$PAGE['CHANGE_EMAIL'].'?lid='.$user->getLid().'">try again</a>.';
		echo '</translate>';
	}
} else {
	echo '<form method="post" action="'.$REQUEST['UPDATE_EMAIL'].'">';
	echo '<translate id="CHANGE_EMAIL_CURRENT">You registered email address is <string value="'.$user->getEmail().'"/>.</translate><br/><br/>';
	echo '<label for="new_email"><translate id="CHANGE_EMAIL_LABEL">Please enter your new email address:</translate></label>';
	echo '<input type="text" class="new_email_field" id="new_email" name="new_email" value="" autocomplete="off" maximum="200"/>';
	echo '<span class="icon" id="icon_new_email"><img src="'.$GRAPHICS_PATH.'x-red.gif"></span>';
	echo '<span class="form_error" id="email_unavailable" style="display:none"><translate id="CHANGE_EMAIL_INVALID_EMAIL">Address is already registered to an inspi.re account</translate></span>';
	echo '<br/><input type="submit" name="submitbutton" class="button" id="submitbutton" value="<translate id="CHANGE_EMAIL_SUBMIT_BUTTON">Update your email address</translate>" disabled/>';
	echo '</form>';
}

echo '</div> <!-- email_change -->';

echo '<ad ad_id="LEADERBOARD"/>';

$page->endHTML();
$page->render();
?>
