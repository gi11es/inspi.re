<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadindex.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadindexlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class DiscussionThreadException extends Exception {}

class DiscussionThread implements Persistent {
	private $nid;
	private $xid;
	private $eid;
	private $uid;
	private $title;
	private $status;
	private $creation_time;
	private $indexing_status = 0;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setTitle = 4;
	const statement_setStatus = 5;
	const statement_setUid = 6;
	const statement_setIndexingStatus = 7;
	const statement_setXid = 8;
	
    const cache_prefix = 'DiscussionThread-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of discussion thread with nid='.$this->nid);
		
		try {
			Cache::replaceorset(DiscussionThread::cache_prefix.$this->nid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of discussion thread with nid='.$this->nid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 5)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4]);
		elseif (func_num_args() == 4)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3]);
		elseif (func_num_args() == 3)
			self::__construct2($argv[0], $argv[1], $argv[2]);
    }
	
	public function __construct2($title, $uid, $status, $xid = null, $eid = null) {
		global $INDEXING_STATUS;
		
		DiscussionThread::prepareStatement(DiscussionThread::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		DiscussionThread::$statement[DiscussionThread::statement_create]->execute(array($title, $uid, $xid, $eid, $status));
		Log::trace('DB', 'Executed DiscussionThread::statement_create ["'.$title.'", '.$uid.', '.$xid.', '.$eid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
		$nid = DB::insertid();

		$this->setNid($nid);
		$this->setUid($uid, false);
		$this->setXid($xid, false);
		$this->setEid($eid);
		$this->setTitle($title, false);
		$this->setStatus($status, false);
		$this->setCreationTime(time());
		$this->setIndexingStatus($INDEXING_STATUS['UNINDEXED'], false);
		$this->saveCache();
		
		DiscussionThreadList::deleteByIndexingStatusAndStatus($INDEXING_STATUS['UNINDEXED'], $status);
		DiscussionThreadList::deleteByXidAndStatus($xid, $status);
		DiscussionThreadList::deleteByUidAndStatus($uid, $status);
		DiscussionThreadList::deleteByEid($eid);
		DiscussionThreadList::deleteByXid($xid);
	}
	
	public static function get($nid, $cache = true) {
		if ($nid === null) throw new DiscussionThreadException('No discussion thread for that nid: '.$nid);
		
		try {
			$discussion_thread = Cache::get(DiscussionThread::cache_prefix.$nid);
		} catch (CacheException $e) {
			DiscussionThread::prepareStatement(DiscussionThread::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionThread::$statement[DiscussionThread::statement_get]->execute($nid);
			Log::trace('DB', 'Executed DiscussionThread::statement_get ['.$nid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new DiscussionThreadException('No discussion thread for that nid: '.$nid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$discussion_thread = new DiscussionThread();
			$discussion_thread->populateFields($row);
			if ($cache) $discussion_thread->saveCache();
		}
		return $discussion_thread;
	}
	
	public static function getArray($nidlist, $cache = true) {
		$result = array();
		$querylist = array();
		
		foreach ($nidlist as $nid) $querylist []= DiscussionThread::cache_prefix.$nid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($nidlist as $nid) try {
			if (isset($cacheresult[DiscussionThread::cache_prefix.$nid])) $result[$nid] = $cacheresult[DiscussionThread::cache_prefix.$nid];
			else $result[$nid] = DiscussionThread::get($nid, $cache);
		} catch (DiscussionThreadException $e) {}
		
		return $result;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setNid($row[$COLUMN['NID']]);
		$this->setUid($row[$COLUMN['UID']], false);
		$this->setXid($row[$COLUMN['XID']], false);
		$this->setEid($row[$COLUMN['EID']]);
		$this->setStatus($row[$COLUMN['STATUS']], false);
		$this->setTitle($row[$COLUMN['TITLE']], false);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
		$this->setIndexingStatus($row[$COLUMN['INDEXING_STATUS']], false);
	}
	
	public function delete() {
		global $INDEXING_STATUS;
		
		DiscussionThread::prepareStatement(DiscussionThread::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		DiscussionThread::$statement[DiscussionThread::statement_delete]->execute($this->nid);
		Log::trace('DB', 'Executed DiscussionThread::statement_delete ['.$this->nid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(DiscussionThread::cache_prefix.$this->nid); } catch (CacheException $e) {}
		
		$postlist = DiscussionPostList::getByNid($this->nid);
		foreach ($postlist as $oid => $creation_time) {
			try {
				$post = DiscussionPost::get($oid);
				$post->delete();
			} catch (DiscussionPostException $e) {}
		}
		
		if ($this->indexing_status == $INDEXING_STATUS['INDEXED']) {
			$wordlist = DiscussionThreadIndexList::getByNid($this->nid);
			foreach ($wordlist as $word) try {
				$discussionthreadindex = DiscussionThreadIndex::get($word, $oid);
				$discussionthreadindex->delete();
			} catch (DiscussionThreadIndexException $e) {}
		}
		
		// Remove from associated lists
		
		DiscussionThreadList::deleteByIndexingStatusAndStatus($this->indexing_status, $this->status);
		DiscussionThreadList::deleteByXidAndStatus($this->xid, $this->status);
		DiscussionThreadList::deleteByUidAndStatus($this->uid, $this->status);
		DiscussionThreadList::deleteByEid($this->eid);
		DiscussionThreadList::deleteByXid($this->xid);
	}
	
	public function getNid() { return $this->nid; }
	
	public function setNid($new_nid) { $this->nid = $new_nid; }
	
	public function getXid() { return $this->xid; }
	
	public function setXid($new_xid, $persist=true) {
		$old_xid = $this->xid;
		$this->xid = $new_xid;
		
		if ($persist) {
			DiscussionThread::prepareStatement(DiscussionThread::statement_setXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionThread::$statement[DiscussionThread::statement_setXid]->execute(array($this->xid, $this->nid));
			Log::trace('DB', 'Executed DiscussionThread::statement_setXid ['.$this->xid.', '.$this->nid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			DiscussionThreadList::deleteByXidAndStatus($this->xid, $this->status);
			DiscussionThreadList::deleteByXid($this->xid);
			DiscussionThreadList::deleteByXidAndStatus($old_xid, $this->status);
			DiscussionThreadList::deleteByXid($old_xid);
		}
	}
	
	public function getEid() { return $this->eid; }
	
	public function setEid($new_eid) { $this->eid = $new_eid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid, $persist=true) {
		$old_uid = $this->uid;
		$this->uid = $new_uid;
		
		if ($persist) {
			DiscussionThread::prepareStatement(DiscussionThread::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionThread::$statement[DiscussionThread::statement_setUid]->execute(array($this->uid, $this->nid));
			Log::trace('DB', 'Executed DiscussionThread::statement_setUid ['.$this->uid.', '.$this->nid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			DiscussionThreadList::deleteByUidAndStatus($old_uid, $this->status);
			DiscussionThreadList::deleteByUidAndStatus($this->uid, $this->status);
		}
	}
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time) { $this->creation_time = $new_creation_time; }

	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			DiscussionThread::prepareStatement(DiscussionThread::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionThread::$statement[DiscussionThread::statement_setStatus]->execute(array($this->status, $this->nid));
			Log::trace('DB', 'Executed DiscussionThread::statement_setStatus ['.$this->status.', '.$this->nid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			DiscussionThreadList::deleteByXidAndStatus($this->xid, $old_status);
			DiscussionThreadList::deleteByXidAndStatus($this->xid, $this->status);
			DiscussionThreadList::deleteByUidAndStatus($this->uid, $old_status);
			DiscussionThreadList::deleteByUidAndStatus($this->uid, $this->status);
		}
	}
	
	public function getTitle() { return $this->title; }
	
	public function setTitle($new_title, $persist=true) {
		$this->title = $new_title;
		
		if ($persist) {
			DiscussionThread::prepareStatement(DiscussionThread::statement_setTitle);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionThread::$statement[DiscussionThread::statement_setTitle]->execute(array($this->title, $this->nid));
			Log::trace('DB', 'Executed DiscussionThread::statement_setTitle ['.$this->title.', '.$this->nid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getIndexingStatus() { return $this->indexing_status; }
	
	public function setIndexingStatus($new_indexing_status, $persist=true) {
		$old_indexing_status = $this->indexing_status;
		$this->indexing_status = $new_indexing_status;
		
		if ($persist) {
			DiscussionThread::prepareStatement(DiscussionThread::statement_setIndexingStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionThread::$statement[DiscussionThread::statement_setIndexingStatus]->execute(array($this->indexing_status, $this->nid));
			Log::trace('DB', 'Executed DiscussionThread::statement_setIndexingStatus ['.$this->indexing_status.', '.$this->nid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();

			DiscussionThreadList::deleteByIndexingStatusAndStatus($old_indexing_status, $this->status);
			DiscussionThreadList::deleteByIndexingStatusAndStatus($this->indexing_status, $this->status);
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(DiscussionThread::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case DiscussionThread::statement_get:
					DiscussionThread::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NID'].', '.$COLUMN['XID'].', '.$COLUMN['EID'].', '.$COLUMN['UID'].', '.$COLUMN['TITLE']
						.', '.$COLUMN['STATUS']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.', '.$COLUMN['INDEXING_STATUS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD']
						.' WHERE '.$COLUMN['NID'].' = ?'
								, array('integer'));
					break;
				case DiscussionThread::statement_create:
					DiscussionThread::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD']
						.'( '.$COLUMN['TITLE'].', '.$COLUMN['UID'].', '.$COLUMN['XID'].', '.$COLUMN['EID'].', '.$COLUMN['STATUS']
						.') VALUES(?, ?, ?, ?, ?)', array('text', 'text', 'integer', 'integer', 'integer'));
					break;	
				case DiscussionThread::statement_setTitle:
					DiscussionThread::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_THREAD'], array($COLUMN['NID'] => 'integer'), $COLUMN['TITLE'], 'text');
					break;
				case DiscussionThread::statement_setStatus:
					DiscussionThread::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_THREAD'], array($COLUMN['NID'] => 'integer'), $COLUMN['STATUS'], 'integer');
					break;
				case DiscussionThread::statement_delete:
					DiscussionThread::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD']
						.' WHERE '.$COLUMN['NID'].' = ?'
						, array('integer'));
					break;	
				case DiscussionThread::statement_setUid:
					DiscussionThread::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_THREAD'], array($COLUMN['NID'] => 'integer'), $COLUMN['UID'], 'text');
					break;
				case DiscussionThread::statement_setIndexingStatus:
					DiscussionThread::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_THREAD'], array($COLUMN['NID'] => 'integer'), $COLUMN['INDEXING_STATUS'], 'integer');
					break;
				case DiscussionThread::statement_setXid:
					DiscussionThread::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_THREAD'], array($COLUMN['NID'] => 'integer'), $COLUMN['XID'], 'integer');
					break;
			}
		}
	}
}

?>