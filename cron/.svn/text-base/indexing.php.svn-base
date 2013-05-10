#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Index all the new discussion posts for the search results
 */

require_once(dirname(__FILE__).'/../entities/commentindex.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostindex.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadindex.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../constants.php');

$discussionpostlist = DiscussionPostList::getByIndexingStatusAndStatus($INDEXING_STATUS['UNINDEXED'], $DISCUSSION_POST_STATUS['POSTED']);
$processcount = 0;

foreach ($discussionpostlist as $oid => $creation_time) {
	try {
		$discussionpost = DiscussionPost::get($oid);
		$discussionthread = DiscussionThread::get($discussionpost->getNid());
		
		$wordlist = String::wordlist($discussionpost->getText());
		
		// Check that it's not a comment on an entry
		if ($discussionthread->getEid() === null) {
			foreach (array_count_values($wordlist) as $word => $count) {
				$discussionpostindex = new DiscussionPostIndex($word, $oid, $discussionthread->getXid(), $count);
				unset($discussionpostindex);
			}
			$discussionpost->setIndexingStatus($INDEXING_STATUS['INDEXED']);
			
			$processcount++;
		} else {
			foreach (array_count_values($wordlist) as $word => $count) {
				$commentindex = new CommentIndex($word, $oid, $discussionthread->getEid(), $count);
				unset($commentindex);
			}
			$discussionpost->setIndexingStatus($INDEXING_STATUS['INDEXED']);
			
			$processcount++;
		}
		
		unset($discussionpost);
		unset($discussionthread);
		unset($wordlist);
	} catch (DiscussionThreadException $e) {}
	catch (DiscussionPostException $e) {}
}

unset($discussionpostlist);

$discussionthreadlist = DiscussionThreadList::getByIndexingStatusAndStatus($INDEXING_STATUS['UNINDEXED'], $DISCUSSION_THREAD_STATUS['ACTIVE']);

foreach ($discussionthreadlist as $nid => $creation_time) {
	try {
		$discussionthread = DiscussionThread::get($nid);
		$wordlist = String::wordlist($discussionthread->getTitle());
		foreach (array_count_values($wordlist) as $word => $count) {
			$discussionthreadindex = new DiscussionThreadIndex($word, $nid, $discussionthread->getXid(), $count);
			unset($discussionthreadindex);
		}
		$discussionthread->setIndexingStatus($INDEXING_STATUS['INDEXED']);
		$processcount++;
	} catch (DiscussionThreadException $e) {}
}

unset($discussionthreadlist);

//echo $processcount.' items indexed';

?>