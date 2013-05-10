<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvote.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/favorite.php');
require_once(dirname(__FILE__).'/../entities/favoritelist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once('MDB2/Date.php');

class EntryException extends Exception {}

class Entry implements Persistent {
	private $eid;
	private $uid;
	private $cid;
	private $pid;
	private $rank;
	private $status;
	private $creation_time;
	private $deletion_points = 10;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setStatus = 4;
	const statement_setRank = 5;
	const statement_setUid = 6;
	const statement_setPid = 7;
	
    const cache_prefix = 'Entry-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of entry with eid='.$this->eid);
		
		try {
			Cache::replaceorset(Entry::cache_prefix.$this->eid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of entry with eid='.$this->eid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 5)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4]);
    }
	
	public function __construct2($uid, $cid, $pid, $status, $deletion_points) {
		global $ENTRY_STATUS;
		
		Entry::prepareStatement(Entry::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Entry::$statement[Entry::statement_create]->execute(array($uid, $cid, $pid, $status, $deletion_points));
		Log::trace('DB', 'Executed Entry::statement_create ['.$uid.', '.$cid.', '.$pid.', '.$status.', '.$deletion_points.'] ('.(microtime(true) - $start_timestamp).')');
		
		$eid = DB::insertid();

		$this->setEid($eid);
		$this->setUid($uid, false);
		$this->setCid($cid);
		$this->setPid($pid,false);
		$this->setStatus($status, false);
		$this->setCreationTime(time());
		$this->setDeletionPoints($deletion_points);
		$this->saveCache();
		
		EntryList::deleteByUidAndCidAndStatus($uid, $cid, $status);
		EntryList::deleteByCidAndStatus($cid, $status);
		EntryList::deleteByUidAndStatus($uid, $status);
		EntryList::deleteByCid($cid);
		EntryList::deleteByStatus($status);
		EntryList::addCreated($eid, time());
		EntryList::deleteByUid($uid);
	}
	
	public static function get($eid, $cache = true) {
		if ($eid === null) throw new EntryException('No entry for eid='.$eid);
		
		try {
			$entry = Cache::get(Entry::cache_prefix.$eid);
		} catch (CacheException $e) {
			Entry::prepareStatement(Entry::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = Entry::$statement[Entry::statement_get]->execute($eid);
			Log::trace('DB', 'Executed Entry::statement_get ['.$eid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new EntryException('No entry for eid='.$eid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$entry = new Entry();
			$entry->populateFields($row);
			if ($cache) $entry->saveCache();
		}
		return $entry;
	}
	
	public static function getArray($eidlist, $cache = true) {
		$result = array();
		$querylist = array();
		
		foreach ($eidlist as $eid) $querylist []= Entry::cache_prefix.$eid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($eidlist as $eid) try {
			if (isset($cacheresult[Entry::cache_prefix.$eid])) $result[$eid] = $cacheresult[Entry::cache_prefix.$eid];
			else $result[$eid] = Entry::get($eid, $cache);
		} catch (EntryException $e) {}
		
		return $result;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setEid($row[$COLUMN['EID']]);
		$this->setUid($row[$COLUMN['UID']], false);
		$this->setCid($row[$COLUMN['CID']]);
		$this->setPid($row[$COLUMN['PID']], false);
		$this->setRank($row[$COLUMN['RANK']], false);
		$this->setStatus($row[$COLUMN['STATUS']], false);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
		$this->setDeletionPoints($row[$COLUMN['DELETION_POINTS']]);
	}
	
	public function delete() {
		Entry::prepareStatement(Entry::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Entry::$statement[Entry::statement_delete]->execute($this->eid);
		Log::trace('DB', 'Executed Entry::statement_delete ['.$this->eid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try {
			$old_picture = Picture::get($this->pid);
			$old_picture->delete();
		} catch (PictureException $e) {}
		
		try { Cache::delete(Entry::cache_prefix.$this->eid); } catch (CacheException $e) {}
		
		$entryvotelist = EntryVoteList::getByEid($this->eid);
		foreach ($entryvotelist as $uid => $points) {
			try {
				$entry_vote = EntryVote::get($this->eid, $uid);
				$entry_vote->delete();
			} catch (EntryVoteException $e) {}
		}
		
		$discussionthreadlist = DiscussionThreadList::getByEid($this->eid);
		foreach ($discussionthreadlist as $nid => $creation_time) {
			try {
				$discussion_thread = DiscussionThread::get($nid);
				$discussion_thread->delete();
			} catch (DiscussionThreadException $e) {}
		}
		
		$favoritelist = FavoriteList::getByEid($this->eid);
		foreach ($favoritelist as $uid => $creation_time) {
			try {
				$favorite = Favorite::get($this->eid, $uid);
				$favorite->delete();
			} catch (FavoriteException $e) {}
		}
		
		EntryList::deleteByUidAndCidAndStatus($this->uid, $this->cid, $this->status);
		EntryList::deleteByCidAndStatus($this->cid, $this->status);
		EntryList::deleteByUidAndStatus($this->uid, $this->status);
		EntryList::deleteByCid($this->cid);
		EntryList::deleteByCidAndRank($this->cid, $this->rank);
		EntryList::deleteByUidAndRank($this->uid, $this->rank);
		EntryList::deleteByStatus($this->status);
		EntryList::deleteByUid($this->uid);
	}
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time) { $this->creation_time = $new_creation_time; }
	
	public function getEid() { return $this->eid; }
	
	public function setEid($new_eid) { $this->eid = $new_eid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid, $persist=true) {
		$old_uid = $this->uid;
		$this->uid = $new_uid;
		
		if ($persist) {
			Entry::prepareStatement(Entry::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Entry::$statement[Entry::statement_setUid]->execute(array($this->uid, $this->eid));
			Log::trace('DB', 'Executed Entry::statement_setUid ['.$this->uid.', '.$this->eid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			EntryList::deleteByUidAndCidAndStatus($old_uid, $this->cid, $this->status);
			EntryList::deleteByUidAndCidAndStatus($new_uid, $this->cid, $this->status);
			EntryList::deleteByUidAndStatus($old_uid, $this->status);
			EntryList::deleteByUidAndStatus($new_uid, $this->status);
			EntryList::deleteByUid($old_uid);
			EntryList::deleteByUid($new_uid);
		}
	}
	
	public function getCid() { return $this->cid; }
	
	public function setCid($new_cid) { $this->cid = $new_cid; }
	
	public function getPid() { return $this->pid; }
	
	public function setPid($new_pid, $persist=true) {
		$this->pid = $new_pid;
		
		if ($persist) {
			Entry::prepareStatement(Entry::statement_setPid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Entry::$statement[Entry::statement_setPid]->execute(array($this->pid, $this->eid));
			Log::trace('DB', 'Executed Entry::statement_setPid ['.$this->pid.', '.$this->eid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getDeletionPoints() { return $this->deletion_points; }
	
	public function setDeletionPoints($new_deletion_points) { $this->deletion_points = $new_deletion_points; }
	
	public function getBannedRank() {
		global $ENTRY_STATUS;
		global $ENTRY_VOTE_STATUS;
		global $ALERT_TEMPLATE_ID;
		global $ALERT_INSTANCE_STATUS;
		
		$entrylist = EntryList::getByCidAndStatus($this->cid, $ENTRY_STATUS['POSTED']);
		$entrylist += EntryList::getByCidAndStatus($this->cid, $ENTRY_STATUS['DELETED']);
		$entrylist += EntryList::getByCidAndStatus($this->cid, $ENTRY_STATUS['BANNED']);
		
		$entryscore = array();
		foreach ($entrylist as $uid => $eid) {
			$entryvotelist = EntryVoteList::getByEidAndStatus($eid, $ENTRY_VOTE_STATUS['CAST']);
			$entryscore[$eid] = array_sum($entryvotelist);
		}
		
		arsort($entryscore);
		
		$rank = 0;
		$last_score = null;
		$samerank = 1;
		
		foreach ($entryscore as $eid => $score) {
			$entry = Entry::get($eid);
			if ($score != $last_score) {
				$rank += $samerank;
				$samerank = 0;
			}
			$samerank++;
			
			if ($eid == $this->eid)
				return $rank;
			
			$last_score = $score;
		}
		
		return count($entrylist);
	}
	
	public function getRank() { return $this->rank; }
	
	public function setRank($new_rank, $persist=true) {
		$old_rank = $this->rank;
		$this->rank = $new_rank;
		
		if ($persist) {
			Entry::prepareStatement(Entry::statement_setRank);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Entry::$statement[Entry::statement_setRank]->execute(array($this->rank, $this->eid));
			Log::trace('DB', 'Executed Entry::statement_setRank ['.$this->rank.', '.$this->eid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			EntryList::deleteByCidAndRank($this->cid, $this->rank);
			EntryList::deleteByUidAndRank($this->uid, $this->rank);
			EntryList::deleteByCidAndRank($this->cid, $old_rank);
			EntryList::deleteByUidAndRank($this->uid, $old_rank);
		}
	}

	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			Entry::prepareStatement(Entry::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Entry::$statement[Entry::statement_setStatus]->execute(array($this->status, $this->eid));
			Log::trace('DB', 'Executed Entry::statement_setStatus ['.$this->status.', '.$this->eid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			EntryList::deleteByUidAndCidAndStatus($this->uid, $this->cid, $old_status);
			EntryList::deleteByUidAndCidAndStatus($this->uid, $this->cid, $new_status);
			EntryList::deleteByCidAndStatus($this->cid, $old_status);
			EntryList::deleteByCidAndStatus($this->cid, $new_status);
			EntryList::deleteByUidAndStatus($this->uid, $old_status);
			EntryList::deleteByUidAndStatus($this->uid, $new_status);
			EntryList::deleteByStatus($old_status);
			EntryList::deleteByStatus($new_status);
		}
	}
	
	public function getDiscussionThread() {
		global $DISCUSSION_THREAD_STATUS;
		
		$threadlist = DiscussionThreadList::getByEid($this->eid);
		if (empty($threadlist)) {
			$thread = new DiscussionThread(null, null, $DISCUSSION_THREAD_STATUS['ENTRY'], null, $this->eid);
		} else $thread = DiscussionThread::get(array_shift(array_keys($threadlist)));
		
		return $thread;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(Entry::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case Entry::statement_get:
					Entry::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID'].', '.$COLUMN['UID'].', '.$COLUMN['CID']
						.', '.$COLUMN['PID'].', '.$COLUMN['RANK']
						.', '.$COLUMN['STATUS']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.', '.$COLUMN['DELETION_POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' WHERE '.$COLUMN['EID'].' = ?'
								, array('integer'));
					break;
				case Entry::statement_create:
					Entry::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.'( '.$COLUMN['UID'].', '.$COLUMN['CID']
						.', '.$COLUMN['PID']
						.', '.$COLUMN['STATUS']
						.', '.$COLUMN['DELETION_POINTS']
						.') VALUES(?, ?, ?, ?, ?)', array('text', 'integer', 'integer', 'integer', 'integer'));
					break;	
				case Entry::statement_delete:
					Entry::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' WHERE '.$COLUMN['EID'].' = ?'
						, array('integer'));
					break;
				case Entry::statement_setRank:
					Entry::$statement[$statement] = DB::prepareSetter($TABLE['ENTRY'], array($COLUMN['EID'] => 'integer'), $COLUMN['RANK'], 'integer');
					break;
				case Entry::statement_setStatus:
					Entry::$statement[$statement] = DB::prepareSetter($TABLE['ENTRY'], array($COLUMN['EID'] => 'integer'), $COLUMN['STATUS'], 'integer');
					break;
				case Entry::statement_setUid:
					Entry::$statement[$statement] = DB::prepareSetter($TABLE['ENTRY'], array($COLUMN['EID'] => 'integer'), $COLUMN['UID'], 'text');
					break;
				case Entry::statement_setPid:
					Entry::$statement[$statement] = DB::prepareSetter($TABLE['ENTRY'], array($COLUMN['EID'] => 'integer'), $COLUMN['PID'], 'integer');
					break;
			}
		}
	}
}

?>