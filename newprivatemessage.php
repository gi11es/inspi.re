<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Page where a user can compose a private message to be sent to another user
*/

require_once(dirname(__FILE__).'/entities/privatemessage.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$destination_uid = isset($_REQUEST['uid'])?$_REQUEST['uid']:null;
$pmid = isset($_REQUEST['pmid'])?$_REQUEST['pmid']:null;
$home = isset($_REQUEST['home'])?$_REQUEST['home']:'';

if ($home)
	$page = new Page('HOME', 'HOME', $user);
else
	$page = new Page('MEMBERS', 'COMMUNITIES', $user);
	
$page->addStyle('NEW_PRIVATE_MESSAGE');
$page->addJavascript('NEW_PRIVATE_MESSAGE');
$page->startHTML();

if (($destination_uid === null && $pmid === null) || $user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());
	exit(0);
}

if ($pmid !== null) {
	$privatemessage = PrivateMessage::get($pmid);
	$destination_uid = $privatemessage->getSourceUid();
	$title = String::fromaform(mb_substr('Re: '.$privatemessage->getTitle(), 0, 80, 'UTF-8'));
} else {
	$title = '';
}

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo '<translate id="NEW_PRIVATE_MESSAGE_HINT_TITLE">';
echo 'Send a private message to <user_name uid="'.$destination_uid.'"/>';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

if ($pmid !== null) {
	$creation_time = $privatemessage->getCreationTime();
	echo UI::RenderPrivateMessageList(array($pmid => $creation_time), 1, false);
}

echo '<form id="new_message" method="post" onSubmit="return checkFields();" action="'.$REQUEST['NEW_PRIVATE_MESSAGE'].'">';
echo '<input type="hidden" name="destination_uid" value="'.$destination_uid.'"/>';
echo '<input type="hidden" name="home" value="'.$home.'"/>';
echo '<label for="message_title_input"><translate id="NEW_PRIVATE_MESSAGE_SUBJECT">Subject:</translate></label><input id="message_title_input" type="text" lefttrimmed="true" minimum="2" maximum="80" name="title" value="'.$title.'"/>';
echo '<label for="message_text_input"><translate id="NEW_PRIVATE_MESSAGE_BODY">Body:</translate></label><textarea id="message_text_input" maximum="5000" name="text" /></textarea>';
echo '<input id="new_message_submit" type="submit" value="<translate id="NEW_PRIVATE_MESSAGE_SEND">Send private message</translate>" disabled="">';
echo '<div class="length_error" id="message_title_too_short"><translate id="NEW_PRIVATE_MESSAGE_SUBJECT_TOO_SHORT">Subject is too short</translate></div>';
echo '<div class="length_error" id="message_title_too_long" style="display:none"><translate id="NEW_PRIVATE_MESSAGE_SUBJECT_TOO_LONG">Subject is too long</translate></div>';
echo '<div class="length_error" id="message_text_too_long" style="display:none"><translate id="NEW_PRIVATE_MESSAGE_BODY_TOO_LONG">Body is too long</translate></div>';
echo '</form>';

$page->endHTML();
$page->render();
?>