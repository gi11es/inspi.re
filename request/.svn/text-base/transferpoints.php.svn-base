<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Mark a comment or post as insightful
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/insightfulmark.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userpaging.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

function getAllPostsByNid($nid) {
	global $user;
	global $DISCUSSION_POST_STATUS;
	global $USER_STATUS;
	
	$all_posts_nid = array();
	
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
	}
	
	asort($all_posts_nid[$nid]);
	
	return $all_posts_nid[$nid];
}

$oid = (isset($_REQUEST['oid'])?$_REQUEST['oid']:null);

$discussionpost = null;

try {
	if ($oid !== null) {
		try {
			$discussionpost = DiscussionPost::get($oid);
			$member = User::get($discussionpost->getUid());
		} catch (DiscussionPostException $e) {}
	} else $member = null;
} catch (UserException $e) {
	$member = null;
}

$result = array();

if ($discussionpost !== null && $member != null && $user->getStatus() != $USER_STATUS['UNREGISTERED']) {
	try {
		try {
			$mark = InsightfulMark::get($oid, $user->getUid());
			$result['status'] = 0;
		} catch (InsightfulMarkException $e) {
			$pointsvalue = PointsValue::get($POINTS_VALUE_ID['INSIGHTFUL_GIVE']);
			$points_insightful_give = $pointsvalue->getValue();
			
			$pointsvalue = PointsValue::get($POINTS_VALUE_ID['INSIGHTFUL_RECEIVE']);
			$points_insightful_receive = $pointsvalue->getValue();
		
			$user->givePoints($points_insightful_give);
			$member->givePoints($points_insightful_receive);
			
			$mark = new InsightfulMark($oid, $user->getUid());

			$nid = $discussionpost->getNid();
			$discussionthread = DiscussionThread::get($nid);
			
			$eid = $discussionthread->getEid();
			
			if ($eid !== null && isset($_REQUEST['hash'])) {
				$entry = Entry::get($eid);
				$result['hash'] = $_REQUEST['hash'];
				$result['comments'] = UI::RenderCommentThread($user, $entry, true, $oid);
				$result['status'] = 1;
				
				$competition = Competition::get($entry->getCid());
				
				Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$user->getUid().'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_INSIGHTFUL_COMMENT"><user_name uid="'.$user->getUid().'"/> marked a comment as insightful</translate></div>');
				
				if ($member->getStatus() == $USER_STATUS['ACTIVE']) {
					$alert = new Alert($ALERT_TEMPLATE_ID['INSIGHTFUL_COMMENT']);
					$aid = $alert->getAid();
					
					$persistenttoken = new PersistentToken($member->getUid().'-'.$eid);
					
					$alert_variable = new AlertVariable($aid, 'href', $PAGE['ENTRY'].'?lid='.$member->getLid().'&home=true#persistenttoken='.$persistenttoken->getHash());
					$alert_variable = new AlertVariable($aid, 'entry_href', $PAGE['ENTRY'].'?lid='.$member->getLid().'&home=true#persistenttoken='.$persistenttoken->getHash());
					$alert_variable = new AlertVariable($aid, 'tid', $competition->getTid());
			
					$alert_instance = new AlertInstance($aid, $member->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);
				}
			} else {
				$amount_per_page = UserPaging::getPagingValue($user->getUid(), 'DISCUSSION_THREAD_POSTS');
				
				$key = array_search($oid, array_keys(getAllPostsByNid($nid)));
				if ($key === false) $key = 0;
				$post_page_offset = ceil(($key + 1) / $amount_per_page);
	
				$result['status'] = 2;
				$result['url'] = $PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$nid.'&page='.$post_page_offset.'&scrollto=post_'.$oid;
				
				$amount_per_page = UserPaging::getPagingValue($member->getUid(), 'DISCUSSION_THREAD_POSTS');
				
				$key = array_search($oid, array_keys(getAllPostsByNid($nid)));
				if ($key === false) $key = 0;
				$post_page_offset = ceil(($key + 1) / $amount_per_page);
				
				Log::xmpp('GENERAL_ACTIVITY', '<profile_picture uid="'.$user->getUid().'" size="tiny"/><div class="real_time_update_text"><translate id="JABBER_INSIGHTFUL_POST"><user_name uid="'.$user->getUid().'"/> marked a discussion post as insightful</translate></div>');
				
				if ($member->getStatus() == $USER_STATUS['ACTIVE']) {
					$alert = new Alert($ALERT_TEMPLATE_ID['INSIGHTFUL_POST']);
					$aid = $alert->getAid();
					
					$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
					$alert_variable = new AlertVariable($aid, 'href', $PAGE['DISCUSSION_THREAD'].'?lid='.$member->getLid().'&nid='.$nid.'&page='.$post_page_offset.'&scrollto=post_'.$oid);
					
					$alert_instance = new AlertInstance($aid, $member->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);	
				}
			}
		}
	} catch (UserException $e) {
		$result['status'] = 0;
	}
} else $result['status'] = 0;

echo json_encode($result);

?>