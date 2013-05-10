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

$page = new Page('FAVORITES', 'HOME', $user);
$page->addStyle('HOME');

$page->setTitle('<translate id="FAVORITES_PAGE_TITLE">Your favorites on inspi.re</translate>');

$page->addJavascriptVariable('reload_url', $PAGE['FAVORITES'].'?lid='.$user->getLid());
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);

$page->startHTML();

switch ($user->getStatus()) {
	case $USER_STATUS['UNREGISTERED']:
		$status = $ENTRY_STATUS['ANONYMOUS'];
		break;
	case $USER_STATUS['BANNED']:
		$status = $ENTRY_STATUS['BANNED'];
		break;
	default:
		$status = $ENTRY_STATUS['POSTED'];
}

// Handling paging of the entries

$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;

$favlist = FavoriteList::getByUid($user->getUid());

$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'HOME_FAVORITES');

$page_count = ceil(count($favlist) / $amount_per_page);

$favcount = count($favlist);
arsort($favlist);

$favlist = array_slice($favlist, ($page_offset - 1) * $amount_per_page, $amount_per_page, true);

echo '<div id="favorites_header" class="hint'.($page_count > 1?'':' hintmargin').'">';
echo '<div class="hint_title">';
echo '<translate id="HOME_FAVORITES_HINT_TITLE">';
echo 'Your favorite artworks';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

function RenderHomeFavoritesLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['FAVORITES'].'?lid='.$user->getLid().'&page='.$i.'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderHomeFavoritesLink');

echo '<ad ad_id="LEADERBOARD"/>';

echo '<div class="grid">';

if (empty($favlist)) {
	echo '<div class="listing_item">';
	echo '<div class="listing_header">';
	echo '<translate id="HOME_NO_FAVORITES">';
	echo 'You haven\'t added any artworks to your list of favorites yet.';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else {
	$favorite = Entry::getArray(array_keys($favlist));
	
	foreach ($favlist as $eid => $creation_time) if (isset($favorite[$eid])) {
		$entry = $favorite[$eid];
		$entry_competition = Competition::get($entry->getCid());
		$pid = $entry->getPid();
		
		if ($entry_competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
			$token = new Token($user->getUid().'-'.$eid);
			echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#token='.$token->getHash().'" class="picture_grid" '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
		} else {
			echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$eid.'" class="picture_grid" '.($pid === null?'':'pid="'.$pid.'"').' size="medium"/>';	
		}
	}
}

echo '</div> <!-- grid -->';

echo UI::RenderPaging($page_offset, $page_count, 'RenderHomeFavoritesLink', true);
echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
echo '<div id="favorites_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="HOME_FAVORITES_BOTTOM_BODY_PLURAL">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> favorites per page.';
	echo '</translate>';
} else {
	echo '<translate id="HOME_FAVORITES_BOTTOM_BODY_SINGULAR">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> favorite per page.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="favorites_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changeFavoritesAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="favorites_change_input" style="display:none">';
echo '<translate id="HOME_FAVORITES_INPUT_AMOUNT">';
echo 'Display <input id="favorites_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> favorites per page. <a href="javascript:saveFavoritesAmount()">Save</a> <a href="javascript:cancelFavoritesAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>
