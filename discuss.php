<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users pick the discussion board they want to interact with
*/

require_once(dirname(__FILE__).'/entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/entities/discussionpost.php');
require_once(dirname(__FILE__).'/entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/entities/discussionthread.php');
require_once(dirname(__FILE__).'/entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/entities/insightfulmarklist.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/entities/userpaging.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
$page = new Page('DISCUSS', 'COMMUNITIES', $user);

$page->setTitle('<translate id="DISCUSS_PAGE_TITLE">Community announcements on inspi.re</translate>');

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$page->startHTML();

$page->addJavascriptVariable('reload_url', $PAGE['DISCUSS'].'?lid='.$user->getLid());
$page->addJavascriptVariable('request_update_paging', $REQUEST['UPDATE_PAGING']);

if ($user->getTranslate()) $page->addJavascriptVariable('translate', true);

$page->addJavascriptVariable('language', strtolower($LANGUAGE_CODE[$user->getLid()]));

$all_posts = array();
$posts_count = array();
$all_posts_nid = array();

function getAllPostsByNid($nid) {
	global $all_posts_nid;
	global $user;
	global $DISCUSSION_POST_STATUS;
	global $USER_STATUS;
	
	if (isset($all_posts_nid[$nid]))
		return $all_posts_nid[$nid];
	
	try {
		$all_posts_nid[$nid] = DiscussionPostList::getByNidAndStatus($nid, $DISCUSSION_POST_STATUS['POSTED']);
	} catch (DiscussionPostListException $e) {
	}
	
	if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
		try {
			$anonymous_posts = DiscussionPostList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['ANONYMOUS']);
			foreach ($anonymous_posts as $anonymous_oid => $anonymous_timestamp) {
				$anonymous_post = DiscussionPost::get($anonymous_oid);
				if ($anonymous_post->getNid() == $nid) $all_posts_nid[$nid][$anonymous_oid] = $anonymous_timestamp;
			}
		} catch (DiscussionPostListException $e) {
		}
	} elseif($user->getStatus() == $USER_STATUS['BANNED']) {
		try {
			$anonymous_posts = DiscussionPostList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['BANNED']);
			foreach ($anonymous_posts as $anonymous_oid => $anonymous_timestamp) {
				$anonymous_post = DiscussionPost::get($anonymous_oid);
				if ($anonymous_post->getNid() == $nid) $all_posts_nid[$nid][$anonymous_oid] = $anonymous_timestamp;
			}
		} catch (DiscussionPostListException $e) {
		}
	}
	
	asort($all_posts_nid[$nid]);
	
	return $all_posts_nid[$nid];
}

function latestBoardActivity($user, $xid, &$all_posts, &$posts_count) {
	global $USER_STATUS;
	global $DISCUSSION_THREAD_STATUS;
	global $DISCUSSION_POST_STATUS;
	
	$posts = array();
	
	$threads = DiscussionThreadList::getByXidAndStatus($xid, $DISCUSSION_THREAD_STATUS['ACTIVE']);
	if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
		foreach (DiscussionThreadList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['ANONYMOUS']) as $anonymous_nid => $anonymous_timestamp) {
			$thread = DiscussionThread::get($anonymous_nid);
			if ($thread->getXid() == $xid)
				$threads[$anonymous_nid] = $anonymous_timestamp;
		}
	} elseif ($user->getStatus() == $USER_STATUS['BANNED']) {
		foreach (DiscussionThreadList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['BANNED']) as $anonymous_nid => $anonymous_timestamp) {
			$thread = DiscussionThread::get($anonymous_nid);
			if ($thread->getXid() == $xid)
				$threads[$anonymous_nid] = $anonymous_timestamp;
		}
	}
	
	foreach ($threads as $nid => $timestamp) {
		$threadposts = DiscussionPostList::getByNidAndStatus($nid, $DISCUSSION_POST_STATUS['POSTED']);
		arsort($threadposts);
		$oid = array_pop(array_keys($threadposts));
		$posts_count[$oid] = count($threadposts) - 1;
		$posts[$oid] = array_pop($threadposts);
	}
	
	if (!empty($posts)) {
		if ($xid !== null) $all_posts += $posts;
		asort($posts);
		$latest_timestamp = array_pop($posts);
		return '<translate id="DISCUSS_LATEST_ACTIVITY_WITH_VALUE">Latest activity <duration value="'.(time() - $latest_timestamp).'" /> ago</translate>';
	} else return '<translate id="DISCUSS_LATEST_ACTIVITY_WITHOUT_VALUE">This discussion board hasn\'t seen any activity yet</translate>';
}

$communitylist = $user->getCommunityList();

$community = array();
$boardactivity = array();

foreach ($communitylist as $xid) try {
	$community[$xid] = Community::get($xid);
	$boardactivity[$xid] = latestBoardActivity($user, $xid, $all_posts, $posts_count);
} catch (CommunityException $e) {}

$count = count($all_posts);
$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;
$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'DISCUSS_RECENT_POSTS');
$redirect_amount_per_page = UserPaging::getPagingValue($user->getUid(), 'DISCUSSION_THREAD_POSTS');
$page_count = ceil($count / $amount_per_page);

if (!empty($all_posts))
	echo '<div class="hint ',($page_count < 2?'hintmargin':''),'"><div class="hint_title"><translate id="DISCUSS_LATEST_HINT_TITLE">Community announcements</translate></div><translate id="DISCUSS_LATEST_HINT_BODY">Administrators and moderators keep you up to date about the latest news in your communities</translate></div>';

arsort($all_posts);

if ($page_offset > $page_count) $page_offset = $page_count;

function RenderDiscussLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['DISCUSS'].'?lid='.$user->getLid().'&page='.$i.'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderDiscussLink');

echo '<ad ad_id="DISCUSS_TOP"/>

<div class="announcement_list">';

$all_posts = array_slice($all_posts, ($page_offset - 1) * $amount_per_page, $amount_per_page, true);
$threads = array();

foreach ($all_posts as $oid => $timestamp) try {
	$post = DiscussionPost::get($oid);
	$nid = $post->getNid();
	if (!isset($threads[$nid]))
		$threads[$nid] = DiscussionThread::get($nid);
	$xid = $threads[$nid]->getXid();
		
	$key = array_search($oid, array_keys(getAllPostsByNid($nid)));
	if ($key === false) $key = 0;
	$post_page_offset = ceil(($key + 1) / $redirect_amount_per_page);
	
	$community_pid = $xid !== null && isset($community[$xid])?$community[$xid]->getPid():null;
	
	$marked = count(InsightfulMarkList::getByOid($oid));
	$style = $marked > 0?'insightful_header':'';
		
	echo '<div class="listing_item listing_overflow">';
	echo '<picture href="'.$PAGE['BOARD'].'?lid='.$user->getLid().'&xid='.$xid.'" category="community" class="listing_thumbnail" size="small" '.($community_pid === null?'':'pid="'.$community_pid.'"').' />';
	echo '<profile_picture class="listing_thumbnail" uid="'.$post->getUid().'" size="small"/>';
	echo '<div class="listing_header recent_header '.$style.'">';
	echo '<a href="'.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$nid.'&page='.$post_page_offset.'&scrollto=post_'.$oid.'">'.$threads[$nid]->getTitle().'</a>';
	echo '</div> <!-- listing_header -->';
	echo '<div class="listing_subheader recent_subheader">';

	echo '<translate id="DISCUSS_RECENT_THREAD_HEADER">';
	echo '<duration value="'.(time() - $post->getCreationTime()).'" /> ago <user_name uid="'.$post->getUid().'"/> announced the following in <community_name link="true" xid="'.$xid.'"/>';
	echo '</translate>';
		if (isset($posts_count[$oid])) {
		if ($posts_count[$oid] == 1) {
			echo ' - ';
			echo '<translate id="DISCUSS_RECENT_THREAD_HEADER_REPLIES_SINGULAR">';
			echo '1 reply';
			echo '</translate>';
		} elseif ($posts_count[$oid] > 1) {
			echo ' - ';
			echo '<translate id="DISCUSS_RECENT_THREAD_HEADER_REPLIES_PLURAL">';
			echo '<integer value="',$posts_count[$oid],'"/> replies';
			echo '</translate>';
		}
	}
	
	echo '</div> <!-- post_header -->';
	echo '<div class="recent_post">';
	echo String::fromaform($post->getText());
	echo '</div> <!-- recent_post -->';
	echo '</div> <!-- listing_item -->';
} catch (DiscussionPostException $e) {}

echo '</div> <!-- announcement_list -->';

echo UI::RenderPaging($page_offset, $page_count, 'RenderDiscussLink', true);
echo '<div class="light_hint clearboth '.($page_count <= 1?'abovemargin':'').'">';
echo '<div id="recent_posts_current_amount">';
if ($amount_per_page > 1) {
	echo '<translate id="DISCUSS_RECENT_POSTS_BODY_PLURAL">';
	echo 'Currently displaying the <integer value="'.$amount_per_page.'"/> most recent announcement.';
	echo '</translate>';
} else {
	echo '<translate id=DISCUSS_RECENT_POSTS_BODY_SINGULAR">';
	echo 'Currently displaying the most recent announcement.';
	echo '</translate>';
}
echo '</div>';
echo '<div id="recent_posts_change_amount">';
echo '<translate id="HOME_INBOX_BOTTOM_CHANGE_AMOUNT">';
echo '<a href="javascript:changeRecentPostsAmount();">Change that amount</a>.';
echo '</translate>';
echo '</div>';
echo '<div id="recent_posts_change_input" style="display:none">';
echo '<translate id="DISCUSS_RECENT_POSTS_INPUT_AMOUNT">';
echo 'Display the <input id="recent_posts_per_page" class="number_field" maximum="4" numerical="true" type="text" value="'.$amount_per_page.'" /> most recent announcements. <a href="javascript:saveRecentPostsAmount()">Save</a> <a href="javascript:cancelRecentPostsAmount()">Cancel</a>';
echo '</translate>';
echo '</div>';
echo '</div> <!-- hint -->';

$page->endHTML();
$page->render();
?>
