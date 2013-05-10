<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../entities/themevotelist.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class ThemeVoteException extends Exception {}

class ThemeVote implements Persistent {
	private $tid;
	private $uid;
	private $points;
	private $status;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setUid = 4;
	const statement_setPoints = 5;
	const statement_setStatus = 6;
	
    const cache_prefix = 'ThemeVote-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of theme vote with tid='.$this->tid.' and uid='.$this->uid);
		
		try {
			Cache::replaceorset(ThemeVote::cache_prefix.$this->tid.'-'.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of theme vote with tid='.$this->tid.' and uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 4)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3]);
    }
	
	public function __construct2($tid, $uid, $points, $status) {
		ThemeVote::prepareStatement(ThemeVote::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		ThemeVote::$statement[ThemeVote::statement_create]->execute(array($tid, $uid, $points, $status));
		Log::trace('DB', 'Executed ThemeVote::statement_create ['.$tid.', '.$uid.', '.$points.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');

		$this->setTid($tid);
		$this->setUid($uid, false);
		$this->setPoints($points, false);
		$this->setStatus($status, false);
		$this->saveCache();
		
		ThemeVoteList::deleteByTidAndStatus($tid, $status);
		ThemeVoteList::deleteByUidAndStatus($uid, $status);
		ThemeVoteList::deleteByTid($tid);
	}
	
	public static function get($tid, $uid, $cache = true) {
		if ($tid === null || $uid === null) throw new ThemeVoteException('No theme vote for tid='.$tid.' and uid='.$uid);
		
		try {
			$themevote = Cache::get(ThemeVote::cache_prefix.$tid.'-'.$uid);
		} catch (CacheException $e) {
			ThemeVote::prepareStatement(ThemeVote::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = ThemeVote::$statement[ThemeVote::statement_get]->execute(array($tid, $uid));
			Log::trace('DB', 'Executed ThemeVote::statement_get ['.$tid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new ThemeVoteException('No theme vote for tid='.$tid.' and uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$themevote = new ThemeVote();
			$themevote->populateFields($row);
			if ($cache) $themevote->saveCache();
		}
		return $themevote;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setTid($row[$COLUMN['TID']]);
		$this->setUid($row[$COLUMN['UID']], false);
		$this->setPoints($row[$COLUMN['POINTS']], false);
		$this->setStatus($row[$COLUMN['STATUS']], false);
	}
	
	public function delete() {
		ThemeVote::prepareStatement(ThemeVote::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		ThemeVote::$statement[ThemeVote::statement_delete]->execute(array($this->tid, $this->uid));
		Log::trace('DB', 'Executed ThemeVote::statement_delete ['.$this->tid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(ThemeVote::cache_prefix.$this->tid.'-'.$this->uid); } catch (CacheException $e) {}
		
		ThemeVoteList::deleteByTidAndStatus($this->tid, $this->status);
		ThemeVoteList::deleteByUidAndStatus($this->uid, $this->status);
		ThemeVoteList::deleteByTid($this->tid);
	}
	
	public function getTid() { return $this->tid; }
	
	public function setTid($new_tid) { $this->tid = $new_tid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid, $persist=true) {
		$old_uid = $this->uid;
		$this->uid = $new_uid;
		
		if ($persist) {
			ThemeVote::prepareStatement(ThemeVote::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			ThemeVote::$statement[ThemeVote::statement_setUid]->execute(array($this->uid, $this->tid, $old_uid));
			Log::trace('DB', 'Executed ThemeVote::statement_setUid ['.$this->uid.', '.$this->tid.', '.$old_uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			ThemeVoteList::deleteByUidAndStatus($old_uid, $this->status);
			ThemeVoteList::deleteByUidAndStatus($new_uid, $this->status);
		}
	}
	
	public function getPoints() { return $this->points; }
	
	public function setPoints($new_points, $persist=true) {
		$this->points = $new_points;
		
		if ($persist) {
			ThemeVote::prepareStatement(ThemeVote::statement_setPoints);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			ThemeVote::$statement[ThemeVote::statement_setPoints]->execute(array($this->points, $this->tid, $this->uid));
			Log::trace('DB', 'Executed ThemeVote::statement_setPoints ['.$this->points.', '.$this->tid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			ThemeVoteList::deleteByUidAndStatus($this->uid, $this->status);
			ThemeVoteList::deleteByTidAndStatus($this->tid, $this->status);
		}
	}

	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			ThemeVote::prepareStatement(ThemeVote::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			ThemeVote::$statement[ThemeVote::statement_setStatus]->execute(array($this->status, $this->tid, $this->uid));
			Log::trace('DB', 'Executed ThemeVote::statement_setStatus ['.$this->status.', '.$this->tid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			ThemeVoteList::deleteByUidAndStatus($this->uid, $old_status);
			ThemeVoteList::deleteByUidAndStatus($this->uid, $new_status);
			ThemeVoteList::deleteByTidAndStatus($this->tid, $old_status);
			ThemeVoteList::deleteByTidAndStatus($this->tid, $new_status);
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(ThemeVote::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case ThemeVote::statement_get:
					ThemeVote::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['POINTS']
						.', '.$COLUMN['TID'].', '.$COLUMN['STATUS']
						.', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['THEME_VOTE']
						.' WHERE '.$COLUMN['TID'].' = ? AND '.$COLUMN['UID'].' = ?'
								, array('integer', 'text'));
					break;
				case ThemeVote::statement_create:
					ThemeVote::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['THEME_VOTE']
						.'( '.$COLUMN['TID'].', '.$COLUMN['UID'].', '.$COLUMN['POINTS']
						.', '.$COLUMN['STATUS']
						.') VALUES(?, ?, ?, ?)', array('integer', 'text', 'integer', 'integer'));
					break;	
				case ThemeVote::statement_delete:
					ThemeVote::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['THEME_VOTE']
						.' WHERE '.$COLUMN['TID'].' = ? AND '.$COLUMN['UID'].' = ?'
						, array('integer', 'text'));
					break;	
				case ThemeVote::statement_setPoints:
					ThemeVote::$statement[$statement] = DB::prepareSetter($TABLE['THEME_VOTE'], array($COLUMN['TID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['POINTS'], 'integer');
					break;
				case ThemeVote::statement_setUid:
					ThemeVote::$statement[$statement] = DB::prepareSetter($TABLE['THEME_VOTE'], array($COLUMN['TID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['UID'], 'text');
					break;
				case ThemeVote::statement_setStatus:
					ThemeVote::$statement[$statement] = DB::prepareSetter($TABLE['THEME_VOTE'], array($COLUMN['TID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['STATUS'], 'integer');
					break;
			}
		}
	}
}

?>