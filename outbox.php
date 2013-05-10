<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Lists the private message sent by the user
*/

require_once(dirname(__FILE__).'/entities/privatemessagelist.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userpaging.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['INDEX'].'?lid='.$user->getLid());
	exit(0);
}

$page = new Page('OUTBOX', 'MESSAGING', $user);
$page->addStyle('HOME');

$page->setTitle('<translate id="OUTBOX_PAGE_TITLE">Your private messaging outbox on inspi.re</translate>');

$page->startHTML();

$pmlist = PrivateMessageList::getBySourceUidAndOutboxStatus($user->getUid(), $PRIVATE_MESSAGE_OUTBOX_STATUS['SENT']);

arsort($pmlist);

$pmcount = count($pmlist);

$pm_page_offset = isset($_REQUEST['pmpage'])?$_REQUEST['pmpage']:1;
$pm_amount_per_page = UserPaging::getPagingValue($user->getUid(), 'HOME_PRIVATE_MESSAGES');
$pm_page_count = ceil($pmcount / $pm_amount_per_page);

// Cap the page requested to the maximum page possible
if ($pm_page_offset > $pm_page_count) $pm_page_offset = $pm_page_count;

$pmlist = array_slice($pmlist, ($pm_page_offset - 1) * $pm_amount_per_page, $pm_amount_per_page, true);

echo '<div class="hint">';
echo '<div class="hint_title">';
echo '<translate id="OUTBOX_HINT_TITLE">';
echo 'Your outbox';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="OUTBOX_HINT_BODY">';
echo 'Private messages you\'ve sent to fellow users in the past';
echo '</translate>';
echo '</div> <!-- hint -->';

function RenderOutboxPrivateMessageLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['OUTBOX'].'?lid='.$user->getLid().'&pmpage='.$i.'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($pm_page_offset, $pm_page_count, 'RenderOutboxPrivateMessageLink');

if (isset($_REQUEST['successpm'])) {
	echo '<div class="warning clearboth highlight_item">';
	echo '<div class="warning_title">';
	echo '<translate id="PRIVATE_MESSAGE_SENT">';
	echo 'Your private message was sent successfully';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
}

echo '<ad id="ad_home" ad_id="HOME_TOP"/>';

echo '<div id="private_message_list">';

if ($pmcount == 0) {
	echo '<div class="listing_item clearboth">';
	echo '<div class="listing_header">';
	echo '<translate id="OUTBOX_NO_PRIVATE_MESSAGE">';
	echo 'You haven\'t sent any private message yet';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else {
	echo UI::RenderPrivateMessageList($pmlist, $pm_page_offset, false, false);
}

echo '</div> <!-- private_message_list -->';

$page->endHTML();
$page->render();
?>
