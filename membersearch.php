<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Search results on the discussion boards
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/entities/usernameindexlist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page = new Page('MEMBERS', 'COMMUNITIES', $user);
$page->addJavascript('MEMBER_SEARCH');
$page->startHTML();

if (!isset($_REQUEST['search'])) {
	header('Location: /Members/s3-l'.$user->getLid());
	exit(0);
}

$page->setTitle('<translate id="MEMBER_SEARCH_PAGE_TITLE">Search results for "<string value="'.String::fromaform(stripslashes($_REQUEST['search'])).'"/>" among the members of inspi.re</translate>');

$search = mb_strtolower(trim(stripslashes($_REQUEST['search'])), 'UTF-8');

$page->addJavascriptVariable('reload_url', $PAGE['MEMBER_SEARCH'].'?lid='.$user->getLid().'&search='.stripslashes($_REQUEST['search']));
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);

$userlist = UserNameIndexList::getByChunk($search);
arsort($userlist);

$results_count = count($userlist);

$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;

$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'MEMBERS_SEARCH');

$page_count = ceil($results_count / $amount_per_page);

$userlist = array_slice($userlist, ($page_offset - 1) * $amount_per_page, $amount_per_page, true);

echo '<div class="hint'.($page_count > 1?'':' hintmargin').'">';
echo '<div class="hint_title">';
echo '<translate id="MEMBER_SEARCH_HINT_TITLE">';
echo 'Registered members whose name matches your search query';
echo '</translate>';
echo '</div> <!-- hint_title -->';
if ($results_count == 0) {
	echo '<translate id="DISCUSSION_SEARCH_HINT_BODY_ZERO">';
	echo 'No search results for "<string value="'.String::fromaform(stripslashes($_REQUEST['search'])).'"/>"';
	echo '</translate>';
} elseif ($results_count == 1) {
	echo '<translate id="DISCUSSION_SEARCH_HINT_BODY_SINGULAR">';
	echo '1 search result for "<string value="'.String::fromaform(stripslashes($_REQUEST['search'])).'"/>"';
	echo '</translate>';
} else {
	echo '<translate id="DISCUSSION_SEARCH_HINT_BODY">';
	echo '<integer value="'.$results_count.'"/> search results for "<string value="'.String::fromaform(stripslashes($_REQUEST['search'])).'"/>"';
	echo '</translate>';
}
echo '</div> <!-- hint -->';

function RenderMemberSearchLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['MEMBER_SEARCH'].'?lid='.$user->getLid().'&page='.$i.'&search='.stripslashes($_REQUEST['search']).'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderMemberSearchLink');

echo '<div class="clearboth">';
echo '<form method="GET" action="'.$PAGE['MEMBER_SEARCH'].'">';
echo '<input type="hidden" name="lid" value="'.$user->getLid().'"/>';
echo '<input type="text" id="search" name="search"/>';
echo '<input type="submit" value="<translate id="MEMBERS_SEARCH">Find member</translate>"/>';
echo '</form>';
echo '</div> <!-- member_search -->';

echo '<ad ad_id="MEMBERS"/>';

echo '<div id="search_result_list">';

$first = true;
if (empty($userlist)) {
	echo '<div class="'.(!$hideads?'marginless_item':'listing_item').' clearboth">';
	echo '<div class="listing_header">';
	echo '<translate id="MEMBER_SEARCH_EMPTY_RESULTS">';
	echo 'There is no member with a name that matches your query.';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else foreach ($userlist as $uid => $count) try {
	$member = User::get($uid);
	
	echo '<div class="'.($first && !$hideads?'marginless_item':'listing_item').' clearboth">';
	$first = false;
	echo '<profile_picture class="listing_thumbnail" uid="'.$uid.'" size="small"/>';
	echo '<div class="listing_header listing_header_thumbnail_margin">';
	echo '<user_name uid="'.$uid.'"/>';
	echo '</div> <!-- listing_header -->';
	echo '<div class="member_summary">';
	echo '<translate id="MEMBER_SINCE">Has been a member of inspi.re for <duration value="'.(time() - $member->getCreationTime()).'"/></translate><br/>';
	$ip_history = $member->getIpHistory();
	if (!empty($ip_history)) {
		arsort($ip_history);
		echo '<translate id="MEMBER_LAST_CONNECTED">Last connected from <location ip="'.array_shift(array_keys($ip_history)).'"/></translate><br/>';
	}
	echo '<translate id="MEMBER_LANGUAGE_PREFERENCE">Uses the website in <language_name lid="'.$member->getLid().'"/></translate><br/>';
	echo '</div> <!-- member_summary -->';
	echo '</div> <!-- listing_item -->';
} catch (UserException $e) {}

echo '</div> <!-- search_result_list -->';

echo UI::RenderPaging($page_offset, $page_count, 'RenderMemberSearchLink', true);
echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
echo '<div id="results_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="MEMBER_SEARCH_BODY_PLURAL">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> search results per page.';
	echo '</translate>';
} else {
	echo '<translate id="MEMBER_SEARCH_BODY_SINGULAR">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> search result per page.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="results_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changeResultsAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="results_change_input" style="display:none">';
echo '<translate id="MEMBER_SEARCH_INPUT_AMOUNT">';
echo 'Display <input id="search_results_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> search results per page. <a href="javascript:saveResultsAmount()">Save</a> <a href="javascript:cancelResultsAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>