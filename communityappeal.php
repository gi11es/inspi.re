<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users can ask for administration rights transfer
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitylist.php');
require_once(dirname(__FILE__).'/entities/pointsvalue.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$page = new Page('COMMUNITIES', 'COMMUNITIES', $user);
$page->addJavascript('COMMUNITY_APPEAL');
$page->startHTML();

$xid = (isset($_REQUEST['xid'])?$_REQUEST['xid']:null);

try {
	$community = Community::get($xid);
} catch (CommunityException $e) {
	header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
	exit(0);
}

if ($user->getStatus() != $USER_STATUS['ACTIVE']) {
	header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$community->getXid());
	exit(0);
}

$recentlyactiveuserlist = array_keys(UserList::getActive30Days());

if (in_array($community->getUid(), $recentlyactiveuserlist) || $user->getUid() == $community->getUid()) {
	header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$community->getXid());
	exit(0);
}

$name = String::fromaform($community->getName());
$page->setTitle('<translate id="COMMUNITY_APPEAL_PAGE_TITLE">Appeal for the administration rights transfer of the <string value="'.$name.'"/> community on inspi.re</translate>');

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo '<translate id="COMMUNITY_APPEAL_HINT">Request administration rights for the <string value="'.$name.'"/> community</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="COMMUNITY_APPEAL_HINT_BODY">This appeal will be sent by email to the current inactive administrator of this community. The email will request for the administrator to transfer administration rights to you. Consequently, you must be ready to take the responsibilites associated with being administrator for this community.</translate>';
echo '</div> <!-- hint -->';

echo '<form id="appeal" method="post" onSubmit="return checkFields();" action="'.$REQUEST['COMMUNITY_APPEAL'].'?xid='.$xid.'">';
echo '<label for="message_input"><translate id="COMMUNITY_APPEAL_MESSAGE">Message:</translate></label><textarea id="message_input" minimum="30" maximum="500" name="message" /></textarea>';
echo '<input id="appeal_submit" type="submit" value="<translate id="COMMUNITY_APPEAL_SEND">Send community administration appeal</translate>" disabled="">';
echo '<div class="length_error" id="message_too_short"><translate id="COMMUNITY_APPEAL_MESSAGE_TOO_SHORT">Appeal message is too short</translate></div>';
echo '<div class="length_error" id="message_too_long" style="display:none"><translate id="COMMUNITY_APPEAL_MESSAGE_TOO_LONG">Appeal message is too long</translate></div>';
echo '</form>';

$page->endHTML();
$page->render();
?>