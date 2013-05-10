<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users create, join and leave communities
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitylist.php');
require_once(dirname(__FILE__).'/entities/communitymembership.php');
require_once(dirname(__FILE__).'/entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/entities/pointsvalue.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page = new Page('JOIN_COMMUNITIES', 'COMMUNITIES', $user);
$page->addJavascript('VIEW_COMMUNITIES');
$page->addStyle('COMMUNITIES');

$page->setTitle('<translate id="JOIN_COMMUNITIES_PAGE_TITLE">Join communities on inspi.re</translate>');

$page->startHTML();
$page->addJavascriptVariable('request_update_communities_per_page', $REQUEST['UPDATE_COMMUNITIES_PER_PAGE']);
$page->addJavascriptVariable('request_joinable_community_list', $REQUEST['JOINABLE_COMMUNITY_LIST']);

echo '<div class="hint">';
echo '<div class="hint_title">';
echo '<translate id="COMMUNITIES_JOINABLE">';
echo 'Communities you can join';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="COMMUNITIES_JOINABLE_BODY">';
echo 'Click on any community\'s name and you will access its description page, which is where you can join it.';
echo '</translate>';
echo '</div> <!-- hint -->';

echo UI::RenderJoinableCommunityPaging($user, 1, true);

echo '<ad ad_id="COMMUNITIES"/>';

echo '<div id="joinable_order" class="'.($hideads?'abovemargin':'').'">';
echo '<translate id="JOINABLE_COMMUNITY_ORDER">';
echo 'Order: ';
echo '<a class="order_option" id="order_'.$COMMUNITY_ORDER['RECENT'].'" href="javascript:orderJoinableCommunities('.$COMMUNITY_ORDER['RECENT'].', current_page, restrict_language, restrict_labels);">';
echo 'most recent first';
echo '</a>';
echo ' - ';
echo '<a class="order_option" id="order_'.$COMMUNITY_ORDER['OLD'].'" href="javascript:orderJoinableCommunities('.$COMMUNITY_ORDER['OLD'].', current_page, restrict_language, restrict_labels);">';
echo 'oldest first';
echo '</a>';
echo ' - ';
echo '<a class="order_option" id="order_'.$COMMUNITY_ORDER['BIG'].'" href="javascript:orderJoinableCommunities('.$COMMUNITY_ORDER['BIG'].', current_page, restrict_language, restrict_labels);">';
echo 'biggest first';
echo '</a>';
echo ' - ';
echo '<a class="order_option" id="order_'.$COMMUNITY_ORDER['SMALL'].'" href="javascript:orderJoinableCommunities('.$COMMUNITY_ORDER['SMALL'].', current_page, restrict_language, restrict_labels);">';
echo 'smallest first';
echo '</a>';
echo '</translate>';
echo '</div>';
echo '<img src="'.$GRAPHICS_PATH.'ajax-loader.gif" id="loader" style="display:none">';

echo '<div id="language_toggle">';
echo '<input id="language_switch" type="checkbox" checked/>';
echo '<translate id="JOINABLE_COMMUNITY_LANGUAGE_RESTRICT">';
echo 'Restrict list to communities whose main language is <language_name lid="'.$user->getLid().'"/>';
echo '</translate>';
echo '</div>';

echo '<div id="label_filter">';
echo '<div id="label_filter_header">';
echo '<translate id="JOINABLE_COMMUNITY_KEYWORD_FILTER">Filter by keyword(s):</translate> ';
echo '</div> <!-- label_filter_header -->';
foreach ($COMMUNITY_LABEL_NAME as $clid => $name) {
	echo '<div class="filter_label" id="label_'.$clid.'">';
	echo '<a href="javascript:selectLabel('.$clid.');">';
	echo '<translate id="COMMUNITY_LABEL_'.$clid.'">'.$name.'</translate>';
	echo '</a>';
	echo '</div> <!-- label_'.$clid.' -->';
}
echo '</div> <!-- label_filter -->';

$page->addJavascriptVariable('current_page', 1);
$page->addJavascriptVariable('current_order', $COMMUNITY_ORDER['BIG']);
echo UI::RenderJoinableCommunityList($user, $COMMUNITY_ORDER['BIG'], 1, true);

$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'COMMUNITIES_COMMUNITIES');

echo '<div class="light_hint clearboth abovemargin">';
echo '<div id="communities_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="COMMUNITIES_AMOUNT_PLURAL">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> communities per page.';
	echo '</translate>';
} else {
	echo '<translate id="COMMUNITIES_AMOUNT_SINGULAR">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> community per page.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="communities_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changeCommunitiesAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="communities_change_input" style="display:none">';
echo '<translate id="COMMUNITIES_INPUT_AMOUNT">';
echo 'Display <input id="communities_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> communities per page. <a href="javascript:saveCommunitiesAmount()">Save</a> <a href="javascript:cancelCommunitiesAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>
