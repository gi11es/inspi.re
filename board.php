<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users can browse through the threads of a discussion board or create a new one
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitymembership.php');
require_once(dirname(__FILE__).'/entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/entities/discussionpost.php');
require_once(dirname(__FILE__).'/entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/entities/discussionthread.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/entities/userpaging.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page = new Page('DISCUSS', 'COMMUNITIES', $user);
$page->addJavascript('BOARD');
$page->startHTML();

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;
$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;

$page->addJavascriptVariable('reload_url', $PAGE['BOARD'].'?lid='.$user->getLid().($xid !== null?'&xid='.$xid:''));
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);

$ismoderator = false;
$isadmin = false;

if ($xid !== null) {
	try {
		$community = Community::get($xid);
		
		$moderatorlist = CommunityModeratorList::getByXid($xid);
		$ismoderator = in_array($user->getUid(), $moderatorlist);
		$isadmin = $community->getUid() == $user->getUid();
		
		$name = $community->getName();
		$title = String::fromaform($name);
		if ($xid == 267) {
			$title = '<translate id="PRIZE_COMMUNITY_NAME">'.$name.'</translate>';
			$title = INML::processHTML($user, I18N::translateHTML($user, $title));
		}
	} catch (Exception $e) {
		header('Location: '.$PAGE['DISCUSS'].'?lid='.$user->getLid());
		exit(0);
	}
	$page->setTitle('<translate id="BOARD_PAGE_TITLE">Announcements for the <string value="'.$title.'"/> community on inspi.re</translate>');
} else {
	$title = 'General discussion'; // Default discussion board, name is deliberately not translated in other languages
	$page->setTitle('<translate id="BOARD_PAGE_TITLE_GENERAL">General discussion board on inspi.re</translate>');
}

try {
	$all_threads = DiscussionThreadList::getByXidAndStatus($xid, $DISCUSSION_THREAD_STATUS['ACTIVE']);
} catch (DiscussionThreadListException $e) {
}

// If the user is unregistered/anonymous he/she has to see his/her own anonymous postings
if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	try {
		$anonymous_threads = DiscussionThreadList::getByUidAndStatus($user->getUid(), $DISCUSSION_THREAD_STATUS['ANONYMOUS']);
		$all_threads += $anonymous_threads;
	} catch (DiscussionThreadListException $e) {} // If there are no threads, then we quietly don't add anything
}

arsort($all_threads);

$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'BOARD_THREADS');
$page_count = ceil(count($all_threads) / $amount_per_page);
$threads = array_keys($all_threads);

// Reduce the list to the slice (page) the user is currently browsing

echo '<div id="board_header">';
echo '<div id="board_header_floater">';
echo '<div id="board_title">'.$title.'</div>';

if ($ismoderator || $isadmin) {
	echo '<div class="hanging_menu floatleft"><a href="'.$PAGE['NEW_DISCUSSION_THREAD'].'?lid='.$user->getLid().($xid !== null?'&xid='.$xid:'').'"><translate id="BOARD_NEW_THREAD">Make a new announcement</translate></a></div>';
}

function RenderBoardLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $xid; 
	global $user;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['BOARD'].'?lid='.$user->getLid().'&page='.$i.($xid === null?'':'&xid='.$xid).'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderBoardLink');

echo '</div> <!-- board_header_floater -->';
echo '</div> <!-- board_header -->';

echo '<ad ad_id="DISCUSSION_BOARD"/>';

echo '<div id="thread_list" '.($hideads?'class="abovemargin"':'').'>';
if (empty($threads)) echo '<div class="marginless_thread"><div class="thread_description"><translate id="BOARD_NO_THREAD">There is currently no thread on this discussion board</translate></div></div>';
else {
	$last_post = array();
	$threads_chronology = array();
	$anonymous_posts = array();
	$posts_count = array();
	
	if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
		$temp_posts = DiscussionPostList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['ANONYMOUS']);
		foreach ($temp_posts as $oid => $timestamp) {
			$post = DiscussionPost::get($oid);
			if (!isset($anonymous_posts[$post->getNid()]))
				$anonymous_posts[$post->getNid()] = array();
			$anonymous_posts[$post->getNid()] []= $post;
		}
	}
	
	foreach ($threads as $nid) {
		$post_oids = DiscussionPostList::getByNidAndStatus($nid, $DISCUSSION_POST_STATUS['POSTED']);

		// If an unregistered user is browsing we need to add his/her own threads to the 'last post' calculation
		if (isset($anonymous_posts[$nid]))
			foreach ($anonymous_posts[$nid] as $anonymous_post)
				$post_oids[$anonymous_post->getOid()] = $anonymous_post->getCreationTime();
		
		$posts_count[$nid] = count($post_oids);
		if ($posts_count[$nid] > 0) {
			asort($post_oids);
			$last_post_oid = array_pop(array_keys($post_oids));
			$last_post[$nid] = DiscussionPost::get($last_post_oid);
			$threads_chronology[$nid] = $last_post[$nid]->getCreationTime();
		}
	}
	
	// Sort by most recent posts first
	arsort($threads_chronology);
	
	$threads_chronology = array_slice($threads_chronology, ($page_offset - 1) * $amount_per_page, $amount_per_page, true);
	
	$first = true;
	foreach ($threads_chronology as $nid => $last_post_timestamp) {
		$thread = DiscussionThread::get($nid);
		echo '<div class="'.($first?'marginless_':'').'thread">';
		if ($first) $first = false;
		echo '<div class="thread_description">';
		echo '<a href="'.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$nid.'">';
		echo String::htmlentities($thread->getTitle());
		echo '</a>';
		echo '</div> <!-- thread_description -->';
		
		$last_post_uid = $last_post[$nid]->getUid();
		
		// Since handling plural automatically in various languages can be problematic, we just request two translations
		if ($posts_count[$nid] > 1) {
			echo '<translate id="BOARD_POST_COUNT_PLURAL">This announcement has <integer value="'.$posts_count[$nid].'" /> replies</translate>';
			echo ' - ';
			echo '<translate id="BOARD_LATEST_ACTIVITY"><user_name uid="'.$last_post_uid.'"/> wrote the latest message <duration value="'.(time() - $last_post_timestamp).'" /> ago</translate>';
		}
		echo '</div> <!-- thread -->';
	}
}

echo '</div> <!-- thread_list -->';

echo UI::RenderPaging($page_offset, $page_count, 'RenderBoardLink', true);
echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
echo '<div id="threads_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="BOARD_AMOUNT_PLURAL">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> threads per page.';
	echo '</translate>';
} else {
	echo '<translate id="BOARD_AMOUNT_SINGULAR">';
	echo 'Currently displaying <integer value="'.$amount_per_page.'"/> thread per page.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="threads_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changeThreadsAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="threads_change_input" style="display:none">';
echo '<translate id="BOARD_INPUT_AMOUNT">';
echo 'Display <input id="threads_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> discussion threads per page. <a href="javascript:saveThreadsAmount()">Save</a> <a href="javascript:cancelThreadsAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>