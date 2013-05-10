#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Count active members over the last 30 days for all active communities
 */

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitylist.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvote.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('activemembercount.php')) {
	echo 'Had to abort active member count cron job, it was already running';
} else {
	$onemonthago = time() - 2592000;
	$twomonthsago = time() - 5184000;
	$threemonthsago = time() - 7776000;
	$fourweeksago = time() - 2419200;

	$communitylist = CommunityList::getByStatus($COMMUNITY_STATUS['ACTIVE']);
	$communitylist = array_merge($communitylist, CommunityList::getByStatus($COMMUNITY_STATUS['INACTIVE']));
	
	$communitycache = Community::getArray($communitylist, false);
	
	foreach ($communitycache as $xid => $community) {
		$creation_time = $community->getCreationTime();
		
		$activememberlist = array();
		
		$discussionthreadlist = DiscussionThreadList::getByXidAndStatus($xid, $DISCUSSION_THREAD_STATUS['ACTIVE'], false);
		$discussionthreadcache = DiscussionThread::getArray(array_keys($discussionthreadlist), false);
		
		foreach ($discussionthreadcache as $nid => $discussionthread) {
			if ($discussionthread->getCreationTime() > $onemonthago) {
				$uid = $discussionthread->getUid();
				$activememberlist[]= $uid;
			}
			
			$discussionpostlist = DiscussionPostList::getByNidAndStatus($nid, $DISCUSSION_POST_STATUS['POSTED'], false);
			foreach ($discussionpostlist as $oid => $creation_time)
				if ($creation_time < $onemonthago) unset($discussionpostlist[$oid]);

			$discussionpostcache = DiscussionPost::getArray(array_keys($discussionpostlist), false);
			foreach ($discussionpostcache as $oid => $discussionpost) {
				$uid = $discussionpost->getUid();
				$activememberlist[]= $uid;
			}
		}
		
		$competitionlist = CompetitionList::getByXidAndStatus($xid, $COMPETITION_STATUS['OPEN'], false);
		$competitionlist += CompetitionList::getByXidAndStatus($xid, $COMPETITION_STATUS['VOTING'], false);
		$competitionlist += CompetitionList::getByXidAndStatus($xid, $COMPETITION_STATUS['CLOSED'], false);
		
		foreach ($competitionlist as $cid => $start_time) {		
			$entrieslist = EntryList::getByCidAndStatus($cid, $ENTRY_STATUS['DELETED'], false);
			$entrieslist += EntryList::getByCidAndStatus($cid, $ENTRY_STATUS['POSTED'], false);
			$entrieslist += EntryList::getByCidAndStatus($cid, $ENTRY_STATUS['DISQUALIFIED'], false);
			
			foreach ($entrieslist as $uid => $eid) {
				$entry = Entry::get($eid, false);
				if ($entry->getCreationTime() > $onemonthago) $activememberlist[]= $uid;
				
				$entryvotelist = EntryVoteList::getByEid($eid, false);
				foreach ($entryvotelist as $uid => $point) {
					$entryvote = EntryVote::get($eid, $uid, false);
					if ($entryvote->getCreationTime() > $onemonthago) $activememberlist[]= $uid;
				}
			}	
		}
		
		$activememberlist = array_unique($activememberlist);
		$activemembercount = count($activememberlist);
		
		$community->setActiveMembercount($activemembercount);
		
		// Check if the user is premium
		
		$levels = UserLevelList::getByUid($community->getUid());
		$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
		
		echo $ispremium,' ',$threemonthsago,' ',$creation_time,' ',$activemembercount;
		
		if (!$ispremium && (($creation_time < $onemonthago && $creation_time > $twomonthsago && $activemembercount < 15)
				    || ($creation_time >= $twomonthsago && $creation_time > $threemonthsago && $activemembercount < 30)
				    || ($creation_time <= $threemonthsago && $activemembercount < 50))) {

			if ($community->getInactiveSince() === null || ($community->getStatus() == $COMMUNITY_STATUS['ACTIVE']) && $community->getInactiveSince() <= $fourweeksago) {
				$community->setStatus($COMMUNITY_STATUS['INACTIVE']);
				$community->setInactiveSince(time());
				
				$alert = new Alert($ALERT_TEMPLATE_ID['INACTIVE_COMMUNITY']);
				$aid = $alert->getAid();
				$alert_variable = new AlertVariable($aid, 'xid', $xid);	
				$alert_instance = new AlertInstance($aid, $community->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);
			} elseif ($community->getStatus() == $COMMUNITY_STATUS['INACTIVE'] && $community->getInactiveSince() <= $fourweeksago) {
				// Send an alert stating that the community was deleted
				$alert = new Alert($ALERT_TEMPLATE_ID['INACTIVE_COMMUNITY_DELETE']);
				$aid = $alert->getAid();
				$alert_variable = new AlertVariable($aid, 'name', $community->getName());	
				$alert_instance = new AlertInstance($aid, $community->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);
				
				$community->delete();
			}
		} else {
			$community->setStatus($COMMUNITY_STATUS['ACTIVE']);
			$community->setInactiveSince(null);
		}
	}
}

?>
