<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Page where users can browse through the threads of a discussion board or create a new one
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitymembership.php');
require_once(dirname(__FILE__).'/entities/discussionthread.php');
require_once(dirname(__FILE__).'/entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$page = new Page('DISCUSS', 'COMMUNITIES', $user);
$page->addJavascript('NEW_DISCUSSION_THREAD');
$page->startHTML();

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;
if ($xid !== null) {
	try {
		$community = Community::get($xid);
		$name = String::fromaform($community->getName());
		if ($xid == 267) {
			$title = '<translate id="PRIZE_COMMUNITY_NAME">'.$community->getName().'</translate>';
		} else {
			$title = $name;
		}
		if ($community->getUid() != $user->getUid())
			$membership = CommunityMembership::get($xid, $user->getUid());
	} catch (Exception $e) {
		header('Location: '.$PAGE['DISCUSS'].'?lid='.$user->getLid());
		exit(0);
	}
	$backlink = $PAGE['BOARD'].'?lid='.$user->getLid().'&xid='.$xid;
	$request_link = $REQUEST['NEW_DISCUSSION_THREAD'].'?xid='.$xid;
	
	$page->setTitle('<translate id="NEW_DISCUSSION_THREAD_PAGE_TITLE">Post a new announcement in the <string value="'.$name.'"/> community on inspi.re</translate>');
} else {
	$title = 'General discussion';
	$backlink = $PAGE['BOARD'].'?lid='.$user->getLid();
	$request_link = $REQUEST['NEW_DISCUSSION_THREAD'];
	
	$page->setTitle('<translate id="NEW_DISCUSSION_THREAD_PAGE_TITLE_GENERAL">Start a new discussion thread in the General Discussion board on inspi.re</translate>');
}

$discussionthreadlist = DiscussionThreadList::getbyXidAndStatus($xid, $DISCUSSION_THREAD_STATUS['ACTIVE']);
$toosoon = (gmmktime() - max($discussionthreadlist)) < 86400;

?>

<div class="hint hintmargin">
<div class="hint_title">
<a href="<?=$backlink?>"><?=$title?></a>
</div> <!-- hint_title -->
<translate id="BOARD_HINT_BODY">
An alert will be sent to all members of the community when you post this announcement. Only one announcement per day can be made for a given community, make sure that you get your message right before posting it!
</translate>
</div> <!-- hint -->

<?php
if ($toosoon) {?>
<div class="warning hintmargin">
<div class="warning_title">
<translate id="BOARD_TOOSOON_HINT">
An announcement for this community has already been posted recently
</translate>
</div> <!-- hint_title -->
<translate id="BOARD_TOOSOON_HINT_BODY">
Please make your new announcement later, there can't be more than one every 24 hours for a given community.
</translate>
</div> <!-- warning --><?php
} else {?>
	<form id="new_thread" method="post" onSubmit="return checkFields();" action="<?=$request_link?>">
	<label for="thread_title_input"><translate id="NEW_DISCUSSION_THREAD_TITLE">Title:</translate></label><input id="thread_title_input" type="text" lefttrimmed="true" minimum="2" maximum="70" name="title" />
	<label for="thread_text_input"><translate id="NEW_DISCUSSION_THREAD_TEXT">Text:</translate></label><textarea id="thread_text_input" minimum="2" maximum="5000" name="text" /></textarea>
	<input id="new_thread_submit" type="submit" value="<translate id="NEW_DISCUSSION_THREAD_CREATE">Post this announcement</translate>" disabled="">
	<div class="length_error" id="thread_title_too_short"><translate id="NEW_DISCUSSION_THREAD_TITLE_TOO_SHORT">Title is too short</translate></div> 
	<div class="length_error" id="thread_title_too_long" style="display:none"><translate id="NEW_DISCUSSION_THREAD_TITLE_TOO_LONG">Title is too long</translate></div> 
	<div class="length_error" id="thread_text_too_short"><translate id="NEW_DISCUSSION_THREAD_TEXT_TOO_SHORT">Text is too short</translate></div> 
	<div class="length_error" id="thread_text_too_long" style="display:none"><translate id="NEW_DISCUSSION_THREAD_TEXT_TOO_LONG">Text is too long</translate></div>
	</form><?php
}

$page->endHTML();
$page->render();
?>