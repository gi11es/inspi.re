<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users can post a reply to a specific discussion thread
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitymembership.php');
require_once(dirname(__FILE__).'/entities/discussionpost.php');
require_once(dirname(__FILE__).'/entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/entities/discussionthread.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userpaging.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');

$user = User::getSessionUser();

$page = new Page('DISCUSS', 'COMMUNITIES', $user);
$page->addJavascript('NEW_DISCUSSION_POST');
$page->startHTML();

$nid = isset($_REQUEST['nid'])?$_REQUEST['nid']:null;
$oid = isset($_REQUEST['oid'])?$_REQUEST['oid']:null;

if ($oid !== null) try {
	$replied_to = DiscussionPost::get($oid);
} catch (DiscussionPostException $e) {
	$oid = null;
}

try {
	$thread = DiscussionThread::get($nid);
	$subtitle = '<a href="'.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$nid.'">'.String::htmlentities($thread->getTitle()).'</a>';
	$xid = $thread->getXid();
	if ($xid === null) {
		$title = 'General discussion';
		$backlink = $PAGE['BOARD'].'?lid='.$user->getLid();
		
		$page->setTitle('<translate id="NEW_DISCUSSION_POST_PAGE_TITLE_GENERAL">Write a new discussion post in the "<string value="'.String::fromaform($thread->getTitle()).'"/>" discussion of the General Discussion board on inspi.re</translate>');
	} else {
		$community = Community::get($xid);
		$name = String::fromaform($community->getName());
		if ($xid == 267) {
			$title = '<translate id="PRIZE_COMMUNITY_NAME">'.$community->getName().'</translate>';
		} else {
			$title = $name;
		}
		$backlink = $PAGE['BOARD'].'?lid='.$user->getLid().'&xid='.$xid;
		
		$page->setTitle('<translate id="NEW_DISCUSSION_POST_PAGE_TITLE">Write a new discussion post in the "<string value="'.String::fromaform($thread->getTitle()).'"/>" discussion of the <string value="'.$name.'"/> community on inspi.re</translate>');
	}
	if (isset($community) && $user->getUid() != $community->getUid())
		$membership = CommunityMembership::get($xid, $user->getUid());
} catch (Exception $e) {
	header('Location: '.$PAGE['DISCUSS'].'?lid='.$user->getLid());
	exit(0);
}

echo '<div id="thread_title">';
echo '<a href="'.$backlink.'">';
echo $title;
echo '</a>';
echo '<div id="thread_subtitle">'.$subtitle.'</div>';
echo '</div> <!-- thread_title -->';

if ($oid !== null) {
	$all_posts = array();
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
	}
	
	asort($all_posts);
	
	$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'DISCUSSION_THREAD_POSTS');
	
	$key = array_search($oid, array_keys($all_posts));
	if ($key === false) $key = 0;
	$page_offset = ceil(($key + 1) / $amount_per_page);
	
	$post = DiscussionPost::get($oid);
	$reply_to_oid = $post->getReplyToOid();
	if ($reply_to_oid !== null)
		$replied_to_replied_to = DiscussionPost::get($reply_to_oid);
	
	echo '<div id="post_'.$oid.'" class="listing_item">';
	echo '<div class="listing_thumbnail">';
	echo '<profile_picture uid="'.$post->getUid().'" size="small"/>';
	echo '</div> <!-- listing_thumbnail -->';
	echo '<div class="listing_header listing_header_thumbnail_margin">';
	if ($reply_to_oid === null) {
		echo '<translate id="DISCUSSION_THREAD_HEADER">';
		echo '<duration value="'.(time() - $post->getCreationTime()).'" /> ago <user_name uid="'.$post->getUid().'"/> wrote';
		echo '</translate>';
	} else {
		$key = array_search($reply_to_oid, array_keys($all_posts));
		if ($key === false) $key = 0;
		$reply_page_offset = ceil(($key + 1) / $amount_per_page);
		
		$reply_link = $PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$nid.'&page='.$reply_page_offset.'&scrollto=post_'.$reply_to_oid;
	
		echo '<translate id="DISCUSSION_THREAD_HEADER_REPLY_TO">';
		echo '<duration value="'.(time() - $post->getCreationTime()).'" /> ago, in reply to <a href="'.$reply_link.'">this message</a> from <user_name uid="'.$replied_to_replied_to->getUid().'"/>, <user_name uid="'.$post->getUid().'"/> wrote';
		echo '</translate>';
	}
	echo '</div> <!-- listing_header -->';
	echo '<div class="post_text">';

	echo String::fromaform($post->getText());
	echo '</div> <!-- post_text -->';
	echo '</div> <!-- listing_item -->';
	
	echo '<form id="new_post" method="post" action="'.$REQUEST['NEW_DISCUSSION_POST'].'?nid='.$nid.($oid !== null?'&oid='.$oid:'').'">';
	echo '<div class="listing_item">';
	echo '<div class="listing_thumbnail">';
	echo '<profile_picture uid="'.$user->getUid().'" size="small"/>';
	echo '</div> <!-- listing_thumbnail -->';
	echo '<div class="listing_header listing_header_thumbnail_margin">';
	
	
	echo '<translate id="NEW_DISCUSSION_POST_HEADER_REPLY_TO">';
	echo 'In reply to <a href="'.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$nid.($page_offset > 1?'&page='.$page_offset:'').'&scrollto=post_'.$oid.'">this post</a> from <user_name uid="'.$replied_to->getUid().'"/>, <user_name uid="'.$user->getUid().'"/> wrote';
	echo '</translate>';

} else {
	echo '<form id="new_post" method="post" action="'.$REQUEST['NEW_DISCUSSION_POST'].'?nid='.$nid.($oid !== null?'&oid='.$oid:'').'">';
	echo '<div class="listing_item">';
	echo '<div class="listing_thumbnail">';
	echo '<profile_picture uid="'.$user->getUid().'" size="small"/>';
	echo '</div> <!-- listing_thumbnail -->';
	echo '<div class="listing_header listing_header_thumbnail_margin">';

	echo '<translate id="NEW_DISCUSSION_POST_HEADER">';
	echo '<user_name uid="'.$user->getUid().'"/> wrote';
	echo '</translate>';
}
echo '</div> <!-- listing_header -->';
echo '<textarea minimum="2" maximum="5000" id="text" name="text" minimumrows="8" autoexpand="true" class="new_post_text">';
echo '</textarea> <!-- new_post_text -->';
echo '<div id="send_post" class="post_action">';
echo '<a href="javascript:submitPost()"><translate id="NEW_DISCUSSION_POST_SUBMIT">Submit this post</translate></a>';
echo '</div> <!-- post_action -->';
echo '</div> <!-- post -->';
echo '<div id="length_errors">';
echo '<span class="length_error" id="post_too_short"><translate id="NEW_DISCUSSION_POST_TOO_SHORT">Post is too short</translate></span>';
echo '<span class="length_error" id="post_too_long" style="display:none"><translate id="NEW_DISCUSSION_POST_TOO_LONG">Post is too long</translate></span>';
echo '</div>';
echo '</form>';

?>

<?php

$page->endHTML();
$page->render();
?>