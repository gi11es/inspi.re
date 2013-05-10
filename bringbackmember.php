<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Page where a user can compose a private message to be sent to another user
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$destination_uid = isset($_REQUEST['uid'])?$_REQUEST['uid']:null;

$page = new Page('MEMBERS', 'COMMUNITIES', $user);
	
$page->addStyle('NEW_PRIVATE_MESSAGE');
$page->addJavascript('BRING_BACK_MEMBER');
$page->startHTML();

$ismia = false;
$ismiaappealed = true;

if ($destination_uid !== null) try {
	$member = User::get($destination_uid);
	$levels = UserLevelList::getByUid($member->getUid());
	$ismia = in_array($USER_LEVEL['MIA'], $levels);
	$ismiaappealed = in_array($USER_LEVEL['MIA_APPEALED'], $levels);
} catch (UserException $e) {}

if ($destination_uid === null || $user->getStatus() == $USER_STATUS['UNREGISTERED'] || !$ismia || $ismiaappealed) {
	header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());
	exit(0);
}

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo '<translate id="BRING_BACK_MEMBER_HINT_TITLE">';
echo 'Bring <user_name uid="'.$destination_uid.'"/> back to inspi.re';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="BRING_BACK_MEMBER_HINT_BODY">';
echo '<user_name uid="'.$destination_uid.'"/> hasn\'t visited inspi.re for over a month. You can send him/her a message below that will be sent by email. If you convince him/her to visit inspi.re again, you will earn one day of premium membership when he/she logs back into his/her account. Make that person want to come back to inspi.re and participate again!';
echo '</translate>';
echo '</div> <!-- hint -->';

echo '<form id="new_message" method="post" onSubmit="return checkFields();" action="'.$REQUEST['BRING_BACK_MEMBER'].'">';
echo '<input type="hidden" name="destination_uid" value="'.$destination_uid.'"/>';
echo '<label for="message_text_input"><translate id="BRING_BACK_MEMBER_MESSAGE_BODY">Message:</translate></label><textarea id="message_text_input" minimum="30" maximum="500" name="text" /></textarea>';
echo '<input id="new_message_submit" type="submit" value="<translate id="BRING_BACK_MEMBER_MESSAGE_SEND">Send appeal</translate>" disabled="">';
echo '<div class="length_error" id="message_text_too_short"><translate id="NEW_BRING_BACK_MEMBER_MESSAGE_TOO_SHORT">Message is too short</translate></div>';
echo '<div class="length_error" id="message_text_too_long" style="display:none"><translate id="NEW_BRING_BACK_MEMBER_MESSAGE_TOO_LONG">Message is too long</translate></div>';
echo '</form>';

$page->endHTML();
$page->render();
?>