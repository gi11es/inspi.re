<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Lists the private message sent by the user
*/

require_once(dirname(__FILE__).'/entities/privatemessagelist.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userblocklist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['INDEX'].'?lid='.$user->getLid());
	exit(0);
}

$page = new Page('CONTACTS', 'MESSAGING', $user);
$page->addStyle('HOME');

$page->setTitle('<translate id="CONTACTS_PAGE_TITLE">Your private messaging contacts on inspi.re</translate>');

$page->startHTML();

echo '<div class="hint">';
echo '<div class="hint_title">';
echo '<translate id="CONTACTS_HINT_TITLE">';
echo 'Your contacts';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="CONTACTS_HINT_BODY">';
echo 'This is the list of users you\'ve messaged or have been messaged by in the past. Simply click on their profile picture in order to send them a new private message.';
echo '</translate>';
echo '</div> <!-- hint -->';

echo '<ad id="ad_home" ad_id="HOME_TOP"/>';

$userlist = PrivateMessageList::getSourceUidByDestinationUid($user->getUid());
$userlist = array_merge($userlist, PrivateMessageList::getDestinationUidBySourceUid($user->getUid()));

$userlist = array_unique($userlist);

echo '<div id="contacts" class="clearboth">';

if (count($userlist) == 0) {
	echo '<div class="listing_item clearboth">';
	echo '<div class="listing_header">';
	echo '<translate id="CONTACTS_NONE">';
	echo 'You haven\'t sent nor received any private message yet. You can find the link used to send a message on people\'s profiles.';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else foreach ($userlist as $uid) {
	echo '<profile_picture class="member_thumbnail" href="'.$PAGE['NEW_PRIVATE_MESSAGE'].'?uid='.$uid.'" uid="'.$uid.'" size="small"/>';
}

echo '</div> <!-- contacts -->';

$blockedlist = UserBlockList::getByUid($user->getUid());

if (count($blockedlist) > 0) {
	echo '<div class="hint clearboth hintmargin">';
	echo '<div class="hint_title">';
	echo '<translate id="CONTACTS_BLOCKED_HINT_TITLE">';
	echo 'People you\'ve blocked';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '<translate id="CONTACTS_BLOCKED_HINT_BODY">';
	echo 'This is the list of users you\'ve blocked from private messaging you. If you want to remove them from this blacklist, simply click on their photo and you will be taken to their profile where you can unblock them.';
	echo '</translate>';
	echo '</div> <!-- hint -->';

	echo '<div id="blocked_contacts">';
	
	foreach ($blockedlist as $uid) echo '<profile_picture class="member_thumbnail" uid="'.$uid.'" size="small"/>';

	echo '</div> <!-- blocked_contacts -->';
}

$page->endHTML();
$page->render();
?>
