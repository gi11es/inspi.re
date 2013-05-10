<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users can read a specific thread and post a reply to it
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitymembership.php');
require_once(dirname(__FILE__).'/entities/discussionpost.php');
require_once(dirname(__FILE__).'/entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/entities/discussionthread.php');
require_once(dirname(__FILE__).'/entities/insightfulmark.php');
require_once(dirname(__FILE__).'/entities/insightfulmarklist.php');
require_once(dirname(__FILE__).'/entities/pointsvalue.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/entities/userpaging.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page = new Page('DISCUSS', 'COMMUNITIES', $user);
$page->addJavascript('DISCUSSION_THREAD');
$page->startHTML();

$nid = isset($_REQUEST['nid'])?$_REQUEST['nid']:null;
$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;

if (isset($_REQUEST['scrollto'])) $page->addJavascriptVariable('scrollto', $_REQUEST['scrollto']);

$page->addJavascriptVariable('request_transfer_points', $REQUEST['TRANSFER_POINTS']);
$page->addJavascriptVariable('reload_url', $PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$nid);
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);

if ($user->getTranslate()) $page->addJavascriptVariable('translate', true);

$page->addJavascriptVariable('language', strtolower($LANGUAGE_CODE[$user->getLid()]));

$community = null;
try {
	if ($nid !== null) {
		$thread = DiscussionThread::get($nid);
		$subtitle = String::htmlentities($thread->getTitle());
		$xid = $thread->getXid();
		if ($xid === null) {
			$title = 'General discussion';
			$backlink = $PAGE['BOARD'].'?lid='.$user->getLid();
			
			$page->setTitle('<translate id="DISCUSSION_THREAD_PAGE_TITLE_GENERAL">"<string value="'.$subtitle.'"/>" on the General Discussion board of inspi.re</translate>');
		} else {
			$community = Community::get($xid);
			$title = String::fromaform($community->getName());
			$backlink = $PAGE['BOARD'].'?lid='.$user->getLid().'&xid='.$xid;
			
			$page->setTitle('<translate id="DISCUSSION_THREAD_PAGE_TITLE">"<string value="'.$subtitle.'"/>" discussion thread of the <string value="'.$title.'"/> community on inspi.re</translate>');
		}
		if ($community !== null && $user->getUid() != $community->getUid())
		$membership = CommunityMembership::get($xid, $user->getUid());
	}
} catch (Exception $e) {
	header('Location: '.$PAGE['DISCUSS'].'?lid='.$user->getLid());
	exit(0);
}

try {
	$all_posts = DiscussionPostList::getByNidAndStatus($nid, $DISCUSSION_POST_STATUS['POSTED']);
} catch (DiscussionPostListException $e) {
}

if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	try {
		$anonymous_posts = DiscussionPostList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['ANONYMOUS']);
		foreach ($anonymous_posts as $anonymous_oid => $anonymous_timestamp) {
			$anonymous_post = DiscussionPost::get($anonymous_oid);
			if ($anonymous_post->getNid() == $nid) $all_posts[$anonymous_oid] = $anonymous_timestamp;
		}
	} catch (DiscussionPostListException $e) {
	}
} elseif ($user->getStatus() == $USER_STATUS['BANNED']) {
	try {
		$anonymous_posts = DiscussionPostList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['BANNED']);
		foreach ($anonymous_posts as $anonymous_oid => $anonymous_timestamp) {
			$anonymous_post = DiscussionPost::get($anonymous_oid);
			if ($anonymous_post->getNid() == $nid) $all_posts[$anonymous_oid] = $anonymous_timestamp;
		}
	} catch (DiscussionPostListException $e) {
	}
}

asort($all_posts);

$oids = array_keys($all_posts);
$last_post_oid = array_pop($oids);

$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'DISCUSSION_THREAD_POSTS');
$page_count = ceil(count($all_posts) / $amount_per_page);
$posts = array_slice(array_keys($all_posts), ($page_offset - 1) * $amount_per_page, $amount_per_page, true);
if (empty($posts)) {
	header('Location: '.$PAGE['DISCUSS'].'?lid='.$user->getLid());
	exit(0);
}

echo '<div id="thread_header">';
echo '<div id="thread_header_floater">';
echo '<div id="thread_title">';
echo '<a href="'.$backlink.'">';
echo $title;
echo '</a>';
echo '<div id="thread_subtitle">'.$subtitle.'</div>';
echo '</div> <!-- board_title -->';

function RenderDiscussionThreadLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $nid;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&page='.$i.($nid === null?'':'&nid='.$nid).'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo '<div class="hanging_menu floatleft"><a href="'.$PAGE['NEW_DISCUSSION_POST'].'?lid='.$user->getLid().'&nid='.$nid.'"><translate id="DISCUSSION_THREAD_REPLY">Reply to this announcement</translate></a></div>';

echo UI::RenderPaging($page_offset, $page_count, 'RenderDiscussionThreadLink');

echo '</div> <!-- thread_header_floater -->';
echo '</div> <!-- thread_header -->';

echo '<ad ad_id="DISCUSSION_THREAD"/>';

echo '<div id="post_list" '.($hideads?'class="abovemargin"':'').'>';

$first = true;
foreach ($posts as $oid) try {
	$post = DiscussionPost::get($oid);
	$reply_to_oid = $post->getReplyToOid();
	
	try {
	if ($reply_to_oid !== null)
		$replied_to = DiscussionPost::get($reply_to_oid);
	} catch (DiscussionPostException $e) {
		$reply_to_oid = null;
	}
		
	$insightfulmarkcount = count(InsightfulMarkList::getByOid($oid));
		
	echo '<div id="post_'.$oid.'" class="'.($first?'marginless_item':'listing_item').'">';
	if ($first) $first = false;
	
	echo '<div class="listing_thumbnail">';
	echo '<profile_picture uid="'.$post->getUid().'" size="small"/>';
	echo '</div> <!-- listing_thumbnail -->';
	
	if ($insightfulmarkcount > 0) $style = 'insightful_header';
	else $style = '';
	
	echo '<div class="listing_header listing_header_thumbnail_margin '.$style.'">';
	if ($reply_to_oid === null) {
		echo '<translate id="DISCUSSION_THREAD_HEADER">';
		echo '<duration value="'.(time() - $post->getCreationTime()).'" /> ago <user_name uid="'.$post->getUid().'"/> wrote';
		echo '</translate>';
	} else {
		$key = array_search($reply_to_oid, array_keys($all_posts));
		if ($key === false) $key = 0;
		$reply_page_offset = ceil(($key + 1) / $amount_per_page);
		
		if ($page_offset == $reply_page_offset) $reply_link = 'javascript: showPost(\'post_'.$reply_to_oid.'\')';
		else $reply_link = $PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$nid.'&page='.$reply_page_offset.'&scrollto=post_'.$reply_to_oid;
	
		echo '<translate id="DISCUSSION_THREAD_HEADER_REPLY_TO">';
		echo '<duration value="'.(time() - $post->getCreationTime()).'" /> ago, in reply to <a href="'.$reply_link.'">this message</a> from <user_name uid="'.$replied_to->getUid().'"/>, <user_name uid="'.$post->getUid().'"/> wrote';
		echo '</translate>';
	}
	echo '</div> <!-- listing_header -->';
	
	if ($insightfulmarkcount > 0) {
		echo '<div class="listing_subheader listing_header_thumbnail_margin insightful_subheader">';
		if ($insightfulmarkcount == 1) {
			echo '<translate id="DISCUSSION_THREAD_INSIGHTFUL_POINTS_SINGULAR">';
			echo '1 person marked this post as insightful';
			echo '</translate>';
		} else {
			echo '<translate id="DISCUSSION_THREAD_INSIGHTFUL_POINTS">';
			echo '<integer value="'.$insightfulmarkcount.'"/> people marked this post as insightful';
			echo '</translate>';
		}
		echo '</div> <!-- listing_subheader -->';
	}
	
	echo '<div class="post_text">';

	echo String::fromaform($post->getText());
	echo '</div> <!-- post_text -->';
	echo '<div class="post_actions">';
	if ($insightfulmarkcount > 0) $style = 'insightful_action';
		else $style = '';
	echo '<div class="post_action '.$style.'">';
	echo '<a href="'.$PAGE['NEW_DISCUSSION_POST'].'?lid='.$user->getLid().'&nid='.$nid.'&oid='.$oid.'"><translate id="DISCUSSION_THREAD_REPLY_TO_POST">Reply to this post</translate></a>';
	echo '</div> <!-- post_action -->';
	
	try {
		$mark = InsightfulMark::get($oid, $user->getUid());
		$marked = true;
	} catch (InsightfulMarkException $e) {
		$marked = false;
	}
	
	if ($user->getUid() == $post->getUid() && $oid == $last_post_oid) {
		if ($insightfulmarkcount > 0) $style = 'insightful_action';
		else $style = '';
		
		echo '<div class="post_action '.$style.'">';
		echo '<a href="javascript:showConfirmation(\''.$REQUEST['DELETE_DISCUSSION_POST'].'?oid='.$oid.'\'';
		echo ', \'<translate id="DISCUSSION_THREAD_DELETE_CONFIRMATION_TITLE" escape="js">Do you really want to delete this discussion post?</translate>\'';
		echo ', \'<translate id="DISCUSSION_THREAD_DELETE_CONFIRMATION_TEXT" escape="js">This action can\'t be undone! The text you\'ve written will be deleted permanently.</translate>\'';
		echo ', \'<translate id="DISCUSSION_THREAD_DELETE_CONFIRMATION_YES" escape="js">Yes, go ahead</translate>\'';
		echo ', \'<translate id="DISCUSSION_THREAD_DELETE_CONFIRMATION_NO" escape="js">No</translate>\'';
		echo ');">';
		echo '<translate id="DISCUSSION_THREAD_DELETE_POST">Delete this post</translate></a>';
		echo '</div> <!-- post_action -->';
	} elseif ($user->getUid() != $post->getUid() && $user->getStatus() == $USER_STATUS['ACTIVE'] && !$marked) {		
		if ($insightfulmarkcount > 0) $style = 'insightful_action';
		else $style = '';
		
		echo '<div class="post_action '.$style.'">';
		echo '<a href="javascript:showPointsTransfer('.$oid.');">';
		echo '<translate id="DISCUSSION_THREAD_INSIGHTFUL_MARK">';
		echo 'Mark as insightful';
		echo'</translate>';
		echo '</a>';
		echo '</div> <!-- post_action -->';
	}
	if (!$user->getTranslate()) {
		echo '<div class="post_action '.$style.'">';
		echo '<a href="#" onclick="translateDiscussionPost('.$oid.'); blur(); return false;">';
		echo '<translate id="DISCUSSION_THREAD_TRANSLATE">';
		echo 'Translate with <img src="http://www.google.com/uds/css/small-logo.png" style="vertical-align: middle; border: none;"/>';
		echo '</translate>';
		echo '</a>';
		echo '</div> <!-- post_action -->';
	}
	echo '</div> <!-- post_actions -->';
	echo '</div> <!-- listing_item -->';
} catch (DiscussionPostException $e) {}

echo '</div> <!-- post_list -->';

echo UI::RenderPaging($page_offset, $page_count, 'RenderDiscussionThreadLink', true);
echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
echo '<div id="posts_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="DISCUSSION_THREAD_AMOUNT_PLURAL">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> posts per page.';
	echo '</translate>';
} else {
	echo '<translate id="DISCUSSION_THREAD_AMOUNT_SINGULAR">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> post per page.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="posts_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changePostsAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="posts_change_input" style="display:none">';
echo '<translate id="DISCUSSION_THREAD_INPUT_AMOUNT">';
echo 'Display <input id="posts_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> discussion posts per page. <a href="javascript:savePostsAmount()">Save</a> <a href="javascript:cancelPostsAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['INSIGHTFUL_GIVE']);
$points_insightful_give = -$pointsvalue->getValue();

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['INSIGHTFUL_RECEIVE']);
$points_insightful_receive = $pointsvalue->getValue();

echo '<div class="fixed_centered" id="transfer_points" style="display:none">';
echo '<div class="confirmation_title"><translate id="DISCUSSION_THREAD_INSIGHTFUL_TITLE">Mark this discussion post as insightful</translate></div>';
echo '<div class="confirmation_description"><translate id="DISCUSSION_THREAD_INSIGHTFUL_EXPLANATION">Marking this discussion post as insightful will cost you <integer value="'.$points_insightful_give.'"/> point(s) and will reward the post\'s author with <integer value="'.$points_insightful_receive.'"/> point(s).</translate></div>';
echo '<div class="confirmation_buttons">';
echo '<input class="confirmation_button_left" type="button" value="<translate id="ENTRY_INSIGHTFUL_GIVE">Confirm</translate>" onclick="javascript:transferPoints();"/>';
echo '<input class="confirmation_button_right" type="button" value="<translate id="ENTRY_INSIGHTFUL_CANCEL">Cancel</translate>" onclick="javascript:hidePointsTransfer();"/>';
echo '</div> <!-- confirmation_buttons -->';
echo '</div> <!-- transfer_points -->';

$page->endHTML();
$page->render();
?>