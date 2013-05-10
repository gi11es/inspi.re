<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Lists the private message sent by the user
*/

require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrycommentnotificationlist.php');
require_once(dirname(__FILE__).'/entities/insightfulmarklist.php');
require_once(dirname(__FILE__).'/entities/privatemessagelist.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userpaging.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['INDEX'].'?lid='.$user->getLid());
	exit(0);
}

$page = new Page('COMMENTS', 'MESSAGING', $user);

$page->setTitle('<translate id="COMMENTS_PAGE_TITLE">Comments on inspi.re</translate>');
$page->addJavascriptVariable('reload_url', $PAGE['COMMENTS'].'?lid='.$user->getLid());
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);

$page->startHTML();

$commentsraw = EntryCommentNotificationList::getCommentsByUid($user->getUid());
$commentsdates = array();
foreach ($commentsraw as $oid => $commentraw) {
    $commentsdates[$oid] = $commentraw['creation_time'];
}
arsort($commentsdates);

$count = count($commentsdates);
$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;
$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'MESSAGING_COMMENTS');
$page_count = ceil($count / $amount_per_page);
if ($page_offset > $page_count) $page_offset = $page_count;
$commentsdates = array_slice($commentsdates, ($page_offset - 1) * $amount_per_page, $amount_per_page, true);

$comments = DiscussionPost::getArray(array_keys($commentsdates));

$entrylist = array();
foreach ($commentsdates as $oid => $creation_time) {
    $entrylist[$oid]= $commentsraw[$oid]['eid'];
}

$entries = Entry::getArray(array_unique(array_values($entrylist)));

$showauthor = array();
$tokens = array();
$competitionlist = array();

foreach ($entries as $eid => $entry) {
    $competitionlist[$eid]= $entry->getCid();
}

$competitions = Competition::getArray(array_unique(array_values($competitionlist)));

foreach ($competitionlist as $eid => $cid) {
    if (isset($competitions[$cid]) && $competitions[$cid]->getStatus() == $COMPETITION_STATUS['CLOSED']) {
        $showauthor[$eid] = true;
    } else {
        $showauthor[$eid] = false;
        $token = new Token($user->getUid().'-'.$eid);
        $tokens[$eid] = $token->getHash();
    }
}

echo '<div class="hint">',
     '<div class="hint_title">',
     '<translate id="COMMENTS_HINT_TITLE">',
     'Comments of interest',
     '</translate>',
     '</div> <!-- hint_title -->',
     '<translate id="COMMENTS_HINT_BODY">',
     'Comments on entries you\'re following',
     '</translate>',
     '</div> <!-- hint -->';

function RenderCommentsLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['COMMENTS'].'?lid='.$user->getLid().'&page='.$i.'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderCommentsLink');

echo '<ad id="ad_home" ad_id="HOME_TOP"/>';

echo '<div id="comment_list">';

if (empty($commentsdates)) {
    echo '<div class="warning topmargin hintmargin">',
         '<div class="warning_title">',
         '<translate id="MESSAGING_COMMENTS_EMPTY">',
         'You\'re not subscribed to any entries yet. You need to tick the option to follow comments on an entry in order to see them here.',
         '</translate>',
         '</div> <!-- warning_title -->',
         '</div> <!-- warning -->';
} else foreach ($commentsdates as $oid => $creation_time) if (isset($comments[$oid])) {
    $comment = $comments[$oid];
    $eid = $entrylist[$oid];
    
    if ($showauthor[$eid]) {
        $href = $PAGE['ENTRY'].'#eid='.$eid;
    } else {
        $href = $PAGE['ENTRY'].'#token='.$tokens[$eid];
    }
    
    $insightful = count(InsightfulMarkList::getByOid($oid));
    
    echo '<div class="comment">';
    echo '<picture href="',$href,'" size="small" pid="',$entries[$eid]->getPid(),'"/>';
    if ($showauthor[$eid] || $entries[$eid]->getUid() != $comment->getUid()) {
        echo '<profile_picture class="profile_picture" uid="',$comment->getUid(),'"/>';
    } else {
        echo '<profile_picture class="profile_picture" size="small"/>';
    }
    echo '<div class="comment_text_contents">';
    echo '<div class="header',($insightful?' insightful_header ':' '),($entries[$eid]->getUid() == $comment->getUid()?'author_header':''),'">';
    if ($showauthor[$eid] || $entries[$eid]->getUid() != $comment->getUid()) {
        echo '<translate id="DISCUSSION_THREAD_HEADER">';
        echo '<duration value="'.(time() - $creation_time).'" /> ago <user_name uid="'.$comment->getUid().'"/> wrote';
        echo '</translate>';
    } else {
        echo '<translate id="DISCUSSION_THREAD_HEADER_ARTIST">';
        echo '<duration value="'.(time() - $creation_time).'" /> ago the author wrote';
        echo '</translate>';
    }
    echo '</div>';
    echo '<div class="text">';
    
    $text = $comment->getText();
    if (strstr($text, '<p>'))
        echo String::cleanhtml($text, false);
    else
        echo String::fromaform($text);
    
    echo '</div>';
    echo '</div>';
    echo '</div> <!-- comment -->';
}

echo '</div> <!-- private_message_list -->';

echo UI::RenderPaging($page_offset, $page_count, 'RenderCommentsLink', true);
echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
echo '<div id="messages_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="MESSAGING_COMMENTS_BOTTOM_BODY_PLURAL">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> comments per page.';
	echo '</translate>';
} else {
	echo '<translate id="MESSAGING_COMMENTS_BOTTOM_BODY_SINGULAR">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> comment per page.';
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
echo 'Display <input id="private_messages_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> private messages per page. <a href="javascript:saveMessagesAmount()">Save</a> <a href="javascript:cancelMessagesAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>
