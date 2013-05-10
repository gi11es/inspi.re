<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Search results on the discussion boards
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitymembership.php');
require_once(dirname(__FILE__).'/entities/discussionpost.php');
require_once(dirname(__FILE__).'/entities/discussionpostindexlist.php');
require_once(dirname(__FILE__).'/entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/entities/discussionthread.php');
require_once(dirname(__FILE__).'/entities/discussionthreadindexlist.php');
require_once(dirname(__FILE__).'/entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/entities/insightfulmarklist.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userpaging.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$page = new Page('DISCUSS', 'COMMUNITIES', $user);
$page->startHTML();

$all_posts = array();
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


$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;

if (!isset($_REQUEST['search'])) {
	header('Location: '.$PAGE['DISCUSS'].'?lid='.$user->getLid());
	exit(0);
}

$page->setTitle('<translate id="DISCUSSION_SEARCH_PAGE_TITLE">Search results for "<string value="'.String::fromaform(stripslashes($_REQUEST['search'])).'"/>" on the discussion boards of inspi.re</translate>');

$searchwordlist = String::wordlist(stripslashes($_REQUEST['search']));

$results = array();
$multiplier = array();

$communitylist = $user->getCommunityList();
$communitylist []= null;

foreach ($communitylist as $xid) {
	$discussionpostindexlist = array();
	
	$emptyresult = false;
	
	foreach ($searchwordlist as $searchword) {
		if (!$emptyresult) {
			$newlist = DiscussionPostIndexList::getByXidAndWord($xid, $searchword);
			if (empty($newlist)) {
				$discussionpostindexlist = array();
				$emptyresult = true;
			}
		} else $newlist = array();
		
		if (empty($discussionpostindexlist) && !empty($newlist)) $discussionpostindexlist = $newlist;
		else {	
			$keys = array_intersect($discussionpostindexlist, $newlist);
			foreach ($newlist as $oid => $count) if (isset($discussionpostindexlist[$oid]))
				$discussionpostindexlist[$oid] += $count;
					
			foreach ($discussionpostindexlist as $oid => $count)
				if (!isset($newlist[$oid])) unset($discussionpostindexlist[$oid]);
		}
	}
	
	foreach ($discussionpostindexlist as $oid => $count) {
		if (!isset($results[$oid])) $results[$oid] = $count;
		else $results[$oid] += $count;
	}
}

foreach ($communitylist as $xid) {
	$discussionthreadindexlist = array();
	
	$emptyresult = false;
	
	foreach ($searchwordlist as $searchword) {
		if (!$emptyresult) {
			$newlist = DiscussionThreadIndexList::getByXidAndWord($xid, $searchword);
			if (empty($newlist)) {
				$discussionthreadindexlist = array();
				$emptyresult = true;
			}
		} else $newlist = array();
		
		if (empty($discussionthreadindexlist) && !empty($newlist)) $discussionthreadindexlist = $newlist;
		else {	
			$keys = array_intersect($discussionthreadindexlist, $newlist);
			foreach ($newlist as $nid => $count) if (isset($discussionthreadindexlist[$nid]))
				$discussionthreadindexlist[$nid] += $count;
					
			foreach ($discussionthreadindexlist as $nid => $count)
				if (!isset($newlist[$nid])) unset($discussionpostindexlist[$nid]);
		}
	}
	
	foreach ($discussionthreadindexlist as $nid => $count) {
		$discussionpostlist = DiscussionPostList::getByNidAndStatus($nid, $DISCUSSION_POST_STATUS['POSTED']);
		foreach ($discussionpostlist as $oid => $creation_time) 
			if (!isset($results[$oid])) $results[$oid] = $count * 3; else $results[$oid] += $count * 3;
	}
}

arsort($results);

$results_count = count($results);

$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'DISCUSSION_THREAD_POSTS');
$page_count = ceil($results_count / $amount_per_page);

echo '<div class="hint '.($page_count <= 1?'hintbigmargin':'').'">';
echo '<div class="hint_title">';
echo '<translate id="DISCUSSION_SEARCH_HINT_TITLE">';
echo 'Results in your discussion boards';
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

function RenderDiscussionSearchLink($i, $page_offset, $page_count) {
	global $PAGE;
	global $user;
	global $_REQUEST;
	
	return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="'.$PAGE['DISCUSSION_SEARCH'].'?lid='.$user->getLid().'&page='.$i.'&search='.urlencode(stripslashes($_REQUEST['search'])).'">'.$i.'</a>').($i == $page_count?'':' ');
}

echo UI::RenderPaging($page_offset, $page_count, 'RenderDiscussionSearchLink');

echo '<ad ad_id="DISCUSSION_SEARCH"/>';

$results = array_slice($results, $amount_per_page * ($page_offset - 1), $amount_per_page, true);

echo '<div id="search_result_list">';

if (empty($results)) {
	echo '<div class="listing_item clearboth">';
	echo '<div class="listing_header">';
	echo '<translate id="DISCUSSION_SEARCH_EMPTY_RESULTS">';
	echo 'There are no results in your discussion boards for that query.';
	echo '</translate>';
	echo '</div> <!-- listing_header -->';
	echo '</div> <!-- listing_item -->';
} else foreach ($results as $oid => $count) {
	$post = DiscussionPost::get($oid);
	$nid = $post->getNid();
	if (!isset($threads[$nid]))
		$threads[$nid] = DiscussionThread::get($nid);
	$xid = $threads[$nid]->getXid();
		
	$key = array_search($oid, array_keys(getAllPostsByNid($nid)));
	if ($key === false) $key = 0;
	$post_page_offset = ceil(($key + 1) / $amount_per_page);
	
	if ($xid !== null) {
		$community = Community::get($xid);
		$community_pid = $community->getPid();
	} else $community_pid = null;
	
	$marked = count(InsightfulMarkList::getByOid($oid));
	$style = $marked > 0?'insightful_header':'';
		
	echo '<div class="listing_item clearboth">';
	echo '<picture href="'.$PAGE['BOARD'].'?lid='.$user->getLid().($xid === null ?'':'&xid='.$xid).'" category="community" class="listing_thumbnail" size="small" '.($community_pid === null?'':'pid="'.$community_pid.'"').' />';
	echo '<profile_picture class="listing_thumbnail" uid="'.$post->getUid().'" size="small"/>';
	echo '<div class="listing_header recent_header '.$style.'">';
	
	$thread_title = String::htmlentities($threads[$nid]->getTitle());
	foreach ($searchwordlist as $searchword) {
		$thread_title = mb_ereg_replace('([\s,.:;?!¿¡()><*]+)('.String::htmlentities($searchword).')([\s,.:;?!¿¡()><*]+)', '\\1<span class="search_highlight">\\2</span>\\3', $thread_title, 'i');
		$thread_title = mb_ereg_replace('^('.String::htmlentities($searchword).')([\s,.:;?!¿¡()><*]+)', '<span class="search_highlight">\\1</span>\\2', $thread_title, 'i');
		$thread_title = mb_ereg_replace('([\s,.:;?!¿¡()><*]+)('.String::htmlentities($searchword).')$', '\\1<span class="search_highlight">\\2</span>', $thread_title, 'i');
	}
	$thread_title = String::fromaform($thread_title, false);
	
	echo '<a href="'.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$nid.'&page='.$post_page_offset.'&scrollto=post_'.$oid.'">'.$thread_title.'</a>';
	echo '</div> <!-- listing_header -->';
	echo '<div class="listing_subheader recent_subheader">';
	if ($xid === null) {
		echo '<translate id="DISCUSS_RECENT_THREAD_HEADER_GENERAL">';
		echo '<duration value="'.(time() - $post->getCreationTime()).'" /> ago <user_name uid="'.$post->getUid().'"/> wrote the following in General Discussion';
		echo '</translate>';	
	} else {
		echo '<translate id="DISCUSS_RECENT_THREAD_HEADER">';
		echo '<duration value="'.(time() - $post->getCreationTime()).'" /> ago <user_name uid="'.$post->getUid().'"/> wrote the following in <community_name link="true" xid="'.$xid.'"/>';
		echo '</translate>';
	}
	echo '</div> <!-- post_header -->';
	echo '<div class="search_result_post">';
	
	$post_text = String::htmlentities($post->getText());
	foreach ($searchwordlist as $searchword) {
		$post_text = mb_ereg_replace('([\s,.:;?!¿¡()><*]+)('.String::htmlentities($searchword).')([\s,.:;?!¿¡()><*]+)', '\\1<span class="search_highlight">\\2</span>\\3', $post_text, 'i');
		$post_text = mb_ereg_replace('^('.String::htmlentities($searchword).')([\s,.:;?!¿¡()><*]+)', '<span class="search_highlight">\\1</span>\\2', $post_text, 'i');
		$post_text = mb_ereg_replace('([\s,.:;?!¿¡()><*]+)('.String::htmlentities($searchword).')$', '\\1<span class="search_highlight">\\2</span>', $post_text, 'i');
	}
	$post_text = String::fromaform($post_text, false);
	
	echo $post_text;
	echo '</div> <!-- search_result_post -->';
	echo '</div> <!-- listing_item -->';
}

echo '</div> <!-- search_result_list -->';

$page->endHTML();
$page->render();
?>