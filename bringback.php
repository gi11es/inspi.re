<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	A page where members can send an email message to others who haven't come to the website for a while
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());
	exit(0);
}


$page = new Page('PREMIUM', 'HOME', $user);
$page->addJavascript('BRING_BACK');

$page->setTitle('<translate id="BRING_BACK_PAGE_TITLE">Bring back former members of inspi.re</translate>');

$page->addStyle('MEMBERS');
$page->addStyle('BRING_BACK');

$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;

$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'BRING_BACK');

$page->addJavascriptVariable('reload_url', $PAGE['BRING_BACK'].'?lid='.$user->getLid());
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);

$page->startHTML();

echo '<div class="hint">',
	 '<div class="hint_title">',
	 '<translate id="BRING_BACK_LIST_HINT_TITLE">',
	 'The "bring back old friends" programme',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '<translate id="BRING_BACK_LIST_HINT_BODY">',
	 'If you convince a member of inspi.re who hasn\'t visited the website in a long time to come back ',
	 'and be active on the website again, you\'ll earn one day of premium membership when he/she logs ',
	 'back into his/her account. Below are all the members of inspi.re that haven\'t come for over a ',
	 'month. Click on any of them and once on their profile use the "bring back!" button.',
	 '</translate>',
	 '</div> <!-- hint -->';
	 
$mialist = UserLevelList::getByLevel($USER_LEVEL['MIA']);
$miaappealedlist = UserLevelList::getByLevel($USER_LEVEL['MIA_APPEALED']);

$mialist = array_diff($mialist, $miaappealedlist);

$page_count = ceil(count($mialist) / $amount_per_page);

$mialist = array_slice($mialist, ($page_offset - 1) * $amount_per_page, $amount_per_page, true);

function RenderBringBackLink($i, $page_offset, $page_count) {
	global $user;
	global $selected_xid;
	global $_REQUEST;
	global $PAGE;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['BRING_BACK'].'?lid='.$user->getLid().'&page='.$i.'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderBringBackLink'),

	 '<div class="members clearboth">';
	 
foreach ($mialist as $uid)
	echo '<profile_picture class="member_thumbnail" uid="',$uid,'" size="small" />';

echo '</div> <!-- members -->',
	 UI::RenderPaging($page_offset, $page_count, 'RenderBringBackLink', true),
	 '<div class="light_hint clearboth ',($page_count <= 1?'abovemargin':''),'">',
	 '<div id="bring_back_current_amount">';
	 
if ($amount_per_page > 1) {
	echo '<translate id="BRING_BACK_AMOUNT_PLURAL">',
		 'Currently displaying <integer value="',$amount_per_page,'"/> members per page.',
		 '</translate>';
} else {
	echo '<translate id="BRING_BACK_AMOUNT_SINGULAR">',
		 'Currently displaying <integer value="',$amount_per_page,'"/> members per page.',
		 '</translate>';
}

echo '</div>',
	 '<div id="bring_back_change_amount">',
	 '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">',
	 '<a href="javascript:changeBringBackAmount();">Change that amount</a>.',
	 '</translate>',
	 '</div>',
	 '<div id="bring_back_change_input" style="display:none">',
	 '<translate id="BRING_BACK_INPUT_AMOUNT">',
	 'Display <input id="bring_back_per_page" class="number_field" maximum="4" numerical="true" type="text" value="',$amount_per_page,'" /> members per page. <a href="javascript:saveBringBackAmount()">Save</a> <a href="javascript:cancelBringBackAmount()">Cancel</a>',
	 '</translate>',
	 '</div>',
	 '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>
