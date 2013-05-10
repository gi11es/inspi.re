<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Main page, shall contain a user's current and past entries when logged in
*/

require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/entryvotelist.php');
require_once(dirname(__FILE__).'/entities/favoritelist.php');
require_once(dirname(__FILE__).'/entities/privatemessagelist.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/entities/userpaging.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['INDEX'].'?lid='.$user->getLid());
	exit(0);
}

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page = new Page('PRIVATE_MESSAGING', 'MESSAGING', $user);
$page->addStyle('HOME');

$page->setTitle('<translate id="PRIVATE_MESSAGING_PAGE_TITLE">Private messaging on inspi.re</translate>');

$page->addJavascriptVariable('reload_url', $PAGE['PRIVATE_MESSAGING'].'?lid='.$user->getLid());
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);
$page->addJavascriptVariable('request_update_private_message_status', $REQUEST['UPDATE_PRIVATE_MESSAGE_STATUS']);
$page->addJavascriptVariable('open_text', 
							 '<translate id="HOME_PRIVATE_MESSAGE_OPEN" escape="htmlentities">Open</translate>');
$page->addJavascriptVariable('close_text', 
							 '<translate id="HOME_PRIVATE_MESSAGE_CLOSE" escape="htmlentities">Close</translate>');

// Check if people are refreshing the home page like maniacs

$page->startHTML();

$pmnewlist = PrivateMessageList::getByDestinationUidAndStatus($user->getUid(), $PRIVATE_MESSAGE_STATUS['NEW']);
$pmreadlist = PrivateMessageList::getByDestinationUidAndStatus($user->getUid(), $PRIVATE_MESSAGE_STATUS['READ']);
$pmreadcount = count($pmreadlist);
$pmnewcount = count($pmnewlist);

$pmlist = $pmnewlist + $pmreadlist;
arsort($pmlist);

$pmcount = $pmreadcount + $pmnewcount;

$pm_page_offset = isset($_REQUEST['pmpage'])?$_REQUEST['pmpage']:1;
$pm_amount_per_page = UserPaging::getPagingValue($user->getUid(), 'HOME_PRIVATE_MESSAGES');
$pm_page_count = ceil($pmcount / $pm_amount_per_page);

if ($pm_page_offset > $pm_page_count) $pm_page_offset = $pm_page_count;

$pmlist = array_slice($pmlist, ($pm_page_offset - 1) * $pm_amount_per_page, $pm_amount_per_page, true);

echo '<div class="hint" id="messages_header">';
echo '<div class="hint_title">';
echo '<translate id="HOME_INBOX_HINT_TITLE">';
echo 'Your inbox';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="HOME_INBOX_HINT_BODY">';
echo 'Private messages sent to you by fellow users';
echo '</translate>';
echo '</div> <!-- hint -->';

function RenderHomePrivateMessageLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['PRIVATE_MESSAGING'].'?lid='.$user->getLid().'&pmpage='.$i.'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($pm_page_offset, $pm_page_count, 'RenderHomePrivateMessageLink');

echo '<ad ad_id="LEADERBOARD"/>';

if (isset($_REQUEST['successpm'])) {
	echo '<div class="warning clearboth highlight_item">';
	echo '<div class="warning_title">';
	echo '<translate id="PRIVATE_MESSAGE_SENT">';
	echo 'Your private message was sent successfully';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
}

echo '<div id="private_message_list">';

if ($pmcount == 0) {
	echo '<div class="listing_item clearboth">';
	echo '<div class="listing_header">';
	echo '<translate id="HOME_NO_PRIVATE_MESSAGE">';
	echo 'You don\'t have any private messages in your inbox';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else {
	echo UI::RenderPrivateMessageList($pmlist, $pm_page_offset, true);
}

echo '</div> <!-- private_message_list -->';

echo UI::RenderPaging($pm_page_offset, $pm_page_count, 'RenderHomePrivateMessageLink', true);
echo '<div class="light_hint clearboth '.($pm_page_count <= 1?'abovemargin':'').'">';
echo '<div id="messages_current_amount">';
if ($pm_amount_per_page > 1) {
	echo '<translate id="HOME_INBOX_BOTTOM_BODY_PLURAL">';
	echo 'Currently displaying <integer value="'.$pm_amount_per_page.'"/> private messages per page.';
	echo '</translate>';
} else {
	echo '<translate id="HOME_INBOX_BOTTOM_BODY_SINGULAR">';
	echo 'Currently displaying <integer value="'.$pm_amount_per_page.'"/> private message per page.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="messages_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changeMessagesAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="messages_change_input" style="display:none">';
echo '<translate id="HOME_MESSAGES_INPUT_AMOUNT">';
echo 'Display <input id="private_messages_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$pm_amount_per_page.'" /> private messages per page. <a href="javascript:saveMessagesAmount()">Save</a> <a href="javascript:cancelMessagesAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>
