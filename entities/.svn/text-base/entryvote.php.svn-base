<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class EntryVoteException extends Exception {}

class EntryVote implements Persistent {
	private $eid;
	private $cid = null;
	private $author_uid = null;
	private $uid;
	private $points;
	private $status;
	private $deletion_points;
	private $creation_time;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setUid = 4;
	const statement_setPoints = 5;
	const statement_setStatus = 6;
	const statement_setAuthorUid = 7;
	const statement_setCid = 8;
	
    const cache_prefix = 'EntryVote-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of entry vote with eid='.$this->eid.' and uid='.$this->uid);
		
		try {
			Cache::replaceorset(EntryVote::cache_prefix.$this->eid.'-'.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of entry vote with eid='.$this->eid.' and uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 7)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6]);
    }
	
	public function __construct2($eid, $cid, $author_uid, $uid, $points, $status, $deletion_points) {
		EntryVote::prepareStatement(EntryVote::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		EntryVote::$statement[EntryVote::statement_create]->execute(array($eid, $cid, $author_uid, $uid, $points, $status, $deletion_points));
		Log::trace('DB', 'Executed EntryVote::statement_create ['.$eid.', '.$cid.', '.$author_uid.', '.$uid.', '.$points.', '.$status.', '.$deletion_points.'] ('.(microtime(true) - $start_timestamp).')');

		$this->setEid($eid);
		$this->setCid($cid, false);
		$this->setAuthorUid($author_uid, false);
		$this->setUid($uid, false);
		$this->setPoints($points, false);
		$this->setStatus($status, false);
		$this->setDeletionPoints($deletion_points);
		$this->setCreationTime(time());
		$this->saveCache();
		
		EntryVoteList::deleteByEidAndStatus($eid, $status);
		EntryVoteList::deletebyUidAndStatus($uid, $status);
		EntryVoteList::deleteByEid($eid);
		EntryVoteList::deleteByUidAndCid($uid, $cid);
		EntryVoteList::deletebyAuthorUidAndStatus($author_uid, $status);
		
		EntryVoteList::incrementStatusCount($status);
	}
	
	public static function get($eid, $uid, $cache = true) {
		if ($eid === null || $uid === null) throw new EntryVoteException('No entry vote for eid='.$eid.' and uid='.$uid);
		
		try {
			$entryvote = Cache::get(EntryVote::cache_prefix.$eid.'-'.$uid);
		} catch (CacheException $e) {
			EntryVote::prepareStatement(EntryVote::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryVote::$statement[EntryVote::statement_get]->execute(array($eid, $uid));
			Log::trace('DB', 'Executed EntryVote::statement_get ['.$eid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new EntryVoteException('No entry vote for eid='.$eid.' and uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$entryvote = new EntryVote();
			$entryvote->populateFields($row);
			if ($cache) $entryvote->saveCache();
		}
		return $entryvote;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setEid($row[$COLUMN['EID']]);
		$this->setCid($row[$COLUMN['CID']], false);
		$this->setAuthorUid($row[$COLUMN['AUTHOR_UID']], false);
		$this->setUid($row[$COLUMN['UID']], false);
		$this->setPoints($row[$COLUMN['POINTS']], false);
		$this->setStatus($row[$COLUMN['STATUS']], false);
		$this->setDeletionPoints($row[$COLUMN['DELETION_POINTS']]);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
	}
	
	public function delete() {
		EntryVote::prepareStatement(EntryVote::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		EntryVote::$statement[EntryVote::statement_delete]->execute(array($this->eid, $this->uid));
		Log::trace('DB', 'Executed EntryVote::statement_delete ['.$this->eid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(EntryVote::cache_prefix.$this->eid.'-'.$this->uid); } catch (CacheException $e) {}
		
		EntryVoteList::deleteByEidAndStatus($this->eid, $this->status);
		EntryVoteList::deleteByUidAndStatus($this->uid, $this->status);
		EntryVoteList::deleteByEid($this->eid);
		EntryVoteList::deleteByUidAndCid($this->uid, $this->cid);
		EntryVoteList::deletebyAuthorUidAndStatus($this->author_uid, $this->status);
		
		EntryVoteList::decrementStatusCount($this->status);
	}
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time) { $this->creation_time = $new_creation_time; }
	
	public function getEid() { return $this->eid; }
	
	public function setEid($new_eid) { $this->eid = $new_eid; }
	
	public function getAuthorUid() { return $this->author_uid; }
	
	public function setAuthorUid($new_author_uid, $persist=true) {
		$old_author_uid = $this->author_uid;
		$this->author_uid = $new_author_uid;
		
		if ($persist) {
			EntryVote::prepareStatement(EntryVote::statement_setAuthorUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			EntryVote::$statement[EntryVote::statement_setAuthorUid]->execute(array($this->author_uid, $this->eid, $this->uid));
			Log::trace('DB', 'Executed EntryVote::statement_setAuthorUid ['.$this->author_uid.', '.$this->eid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			EntryVoteList::deletebyAuthorUidAndStatus($this->author_uid, $this->status);
		}
	}
	
	public function getCid() { return $this->cid; }
	
	public function setCid($new_cid, $persist=true) {
		$old_cid = $this->cid;
		$this->cid = $new_cid;
		
		if ($persist) {
			EntryVote::prepareStatement(EntryVote::statement_setCid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			EntryVote::$statement[EntryVote::statement_setCid]->execute(array($this->cid, $this->eid, $this->uid));
			Log::trace('DB', 'Executed EntryVote::statement_setCid ['.$this->cid.', '.$this->eid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			EntryVoteList::deleteByUidAndCid($this->uid, $old_cid);
			EntryVoteList::deleteByUidAndCid($this->uid, $this->cid);
		}
	}
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid, $persist=true) {
		$old_uid = $this->uid;
		$this->uid = $new_uid;
		
		if ($persist) {
			EntryVote::prepareStatement(EntryVote::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			EntryVote::$statement[EntryVote::statement_setUid]->execute(array($this->uid, $this->eid, $old_uid));
			Log::trace('DB', 'Executed EntryVote::statement_setUid ['.$this->uid.', '.$this->eid.', '.$old_uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			EntryVoteList::deleteByUidAndStatus($this->uid, $this->status);
		}
	}
	
	public function getPoints() { return $this->points; }
	
	public function setPoints($new_points, $persist=true) {
		$this->points = $new_points;
		
		if ($persist) {
			EntryVote::prepareStatement(EntryVote::statement_setPoints);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			EntryVote::$statement[EntryVote::statement_setPoints]->execute(array($this->points, $this->eid, $this->uid));
			Log::trace('DB', 'Executed EntryVote::statement_setPoints ['.$this->points.', '.$this->eid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			EntryVote::prepareStatement(EntryVote::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			EntryVote::$statement[EntryVote::statement_setStatus]->execute(array($this->status, $this->eid, $this->uid));
			Log::trace('DB', 'Executed EntryVote::statement_setStatus ['.$this->status.', '.$this->eid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			EntryVoteList::deleteByUidAndStatus($this->uid, $old_status);
			EntryVoteList::deleteByUidAndStatus($this->uid, $this->status);
			EntryVoteList::deleteByEidAndStatus($this->eid, $old_status);
			EntryVoteList::deleteByEidAndStatus($this->eid, $this->status);
			EntryVoteList::incrementStatusCount($this->status);
			EntryVoteList::decrementStatusCount($old_status);
			EntryVoteList::deletebyAuthorUidAndStatus($this->author_uid, $old_status);
			EntryVoteList::deletebyAuthorUidAndStatus($this->author_uid, $this->status);
		}
	}
	
	public function getDeletionPoints() { return $this->deletion_points; }
	
	public function setDeletionPoints($new_deletion_points) { $this->deletion_points = $new_deletion_points; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(EntryVote::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case EntryVote::statement_get:
					EntryVote::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['POINTS']
						.', '.$COLUMN['EID']
						.', '.$COLUMN['CID']
						.', '.$COLUMN['AUTHOR_UID']
						.', '.$COLUMN['UID']
						.', '.$COLUMN['STATUS']
						.', '.$COLUMN['DELETION_POINTS']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.' WHERE '.$COLUMN['EID'].' = ? AND '.$COLUMN['UID'].' = ?'
								, array('integer', 'text'));
					break;
				case EntryVote::statement_create:
					EntryVote::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.'( '.$COLUMN['EID'].', '.$COLUMN['CID'].', '.$COLUMN['AUTHOR_UID'].', '.$COLUMN['UID'].', '.$COLUMN['POINTS'].', '.$COLUMN['STATUS'].', '.$COLUMN['DELETION_POINTS']
						.') VALUES(?, ?, ?, ?, ?, ?, ?)', array('integer', 'integer', 'text', 'text', 'integer', 'integer', 'integer'));
					break;	
				case EntryVote::statement_delete:
					EntryVote::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.' WHERE '.$COLUMN['EID'].' = ? AND '.$COLUMN['UID'].' = ?'
						, array('integer', 'text'));
					break;	
				case EntryVote::statement_setPoints:
					EntryVote::$statement[$statement] = DB::prepareSetter($TABLE['ENTRY_VOTE'], array($COLUMN['EID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['POINTS'], 'integer');
					break;
				case EntryVote::statement_setUid:
					EntryVote::$statement[$statement] = DB::prepareSetter($TABLE['ENTRY_VOTE'], array($COLUMN['EID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['UID'], 'text');
					break;
				case EntryVote::statement_setStatus:
					EntryVote::$statement[$statement] = DB::prepareSetter($TABLE['ENTRY_VOTE'], array($COLUMN['EID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['STATUS'], 'integer');
					break;
				case EntryVote::statement_setAuthorUid:
					EntryVote::$statement[$statement] = DB::prepareSetter($TABLE['ENTRY_VOTE'], array($COLUMN['EID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['AUTHOR_UID'], 'integer');
					break;
				case EntryVote::statement_setCid:
					EntryVote::$statement[$statement] = DB::prepareSetter($TABLE['ENTRY_VOTE'], array($COLUMN['EID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['CID'], 'integer');
					break;
			}
		}
	}
}

?>