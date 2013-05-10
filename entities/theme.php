<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../entities/themevote.php');
require_once(dirname(__FILE__).'/../entities/themevotelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class ThemeException extends Exception {}

class Theme implements Persistent {
	private $tid;
	private $xid;
	private $uid;
	private $title;
	private $description;
	private $status;
	private $deletion_points = 5;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setUid = 4;
	const statement_setTitle = 5;
	const statement_setDescription = 6;
	const statement_setStatus = 7;
	const statement_setXid = 8;
	
    const cache_prefix = 'Theme-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of theme with tid='.$this->tid);
		
		try {
			Cache::replaceorset(Theme::cache_prefix.$this->tid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of theme with tid='.$this->tid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 6)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4], $argv[5]);
    }
	
	public function __construct2($xid, $uid, $title, $description, $status, $deletion_points) {
		Theme::prepareStatement(Theme::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Theme::$statement[Theme::statement_create]->execute(array($xid, $uid, $title, $description, $status, $deletion_points));
		Log::trace('DB', 'Executed Theme::statement_create ['.$xid.', '.$uid.', "'.$title.'", "'.$description.'", '.$status.', '.$deletion_points.'] ('.(microtime(true) - $start_timestamp).')');
		
		$tid = DB::insertid();

		$this->setTid($tid);
		$this->setXid($xid, false);
		$this->setUid($uid, false);
		$this->setTitle($title, false);
		$this->setDescription($description, false);
		$this->setStatus($status, false);
		$this->setDeletionPoints($deletion_points);
		$this->saveCache();
		
		ThemeList::deleteByXidAndStatus($xid, $status);
		ThemeList::deleteByXidAndUidAndStatus($xid, $uid, $status);
			
		ThemeList::deleteByUidAndStatus($uid, $status);
		ThemeList::deleteByXid($xid);
	}
	
	public static function get($tid, $cache = true) {
		if ($tid === null) throw new ThemeException('No theme for that tid: '.$tid);
		
		try {
			$theme = Cache::get(Theme::cache_prefix.$tid);
		} catch (CacheException $e) {
			Theme::prepareStatement(Theme::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = Theme::$statement[Theme::statement_get]->execute($tid);
			Log::trace('DB', 'Executed Theme::statement_get ['.$tid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new ThemeException('No theme for that tid: '.$tid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$theme = new Theme();
			$theme->populateFields($row);
			if ($cache) $theme->saveCache();
		}
		return $theme;
	}
	
	public static function getArray($tidlist, $cache = true) {
		$result = array();
		$querylist = array();
		
		foreach ($tidlist as $tid) $querylist []= Theme::cache_prefix.$tid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($tidlist as $tid) try {
			if (isset($cacheresult[Theme::cache_prefix.$tid])) $result[$tid] = $cacheresult[Theme::cache_prefix.$tid];
			else $result[$tid] = Theme::get($tid, $cache);
		} catch (ThemeException $e) {}
		
		return $result;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setTid($row[$COLUMN['TID']]);
		$this->setXid($row[$COLUMN['XID']], false);
		$this->setUid($row[$COLUMN['UID']], false);
		$this->setTitle($row[$COLUMN['TITLE']], false);
		$this->setDescription($row[$COLUMN['DESCRIPTION']], false);
		$this->setStatus($row[$COLUMN['STATUS']], false);
	}
	
	public function delete() {
		Theme::prepareStatement(Theme::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Theme::$statement[Theme::statement_delete]->execute($this->tid);
		Log::trace('DB', 'Executed Theme::statement_delete ['.$this->tid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(Theme::cache_prefix.$this->tid); } catch (CacheException $e) {}
		
		$themevotelist = ThemeVoteList::getByTid($this->tid);
		foreach ($themevotelist as $uid => $points) {
			try {
				$theme_vote = ThemeVote::get($this->tid, $uid);
				$theme_vote->delete();
			} catch (ThemeVoteException $e) {}
		}
		
		ThemeList::deleteByXidAndStatus($this->xid, $this->status);
		ThemeList::deleteByXidAndUidAndStatus($this->xid, $this->uid, $this->status);
		
		ThemeList::deleteByUidAndStatus($this->uid, $this->status);
		ThemeList::deleteByXid($this->xid);
	}
	
	public function getTid() { return $this->tid; }
	
	public function setTid($new_tid) { $this->tid = $new_tid; }
	
	public function getXid() { return $this->xid; }
	
	public function setXid($new_xid, $persist=true) {
		$old_xid = $this->xid;
		$this->xid = $new_xid;
		
		if ($persist) {
			Theme::prepareStatement(Theme::statement_setXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Theme::$statement[Theme::statement_setXid]->execute(array($this->xid, $this->tid));
			Log::trace('DB', 'Executed Theme::statement_setXid ["'.$this->xid.'", '.$this->tid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			ThemeList::deleteByXidAndStatus($this->xid, $this->status);
			ThemeList::deleteByXidAndUidAndStatus($this->xid, $this->uid, $this->status);
			ThemeList::deleteByXid($this->xid);
			
			ThemeList::deleteByXidAndStatus($old_xid, $this->status);
			ThemeList::deleteByXidAndUidAndStatus($old_xid, $this->uid, $this->status);
			ThemeList::deleteByXid($old_xid);
		}
	}
	
	public function getTitle() { return $this->title; }
	
	public function setTitle($new_title, $persist=true) {
		$this->title = $new_title;
		
		if ($persist) {
			Theme::prepareStatement(Theme::statement_setTitle);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Theme::$statement[Theme::statement_setName]->execute(array($this->title, $this->tid));
			Log::trace('DB', 'Executed Theme::statement_setTitle ["'.$this->title.'", '.$this->tid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getDescription() { return $this->description; }
	
	public function setDescription($new_description, $persist=true) {
		$this->description = $new_description;
		
		if ($persist) {
			Theme::prepareStatement(Theme::statement_setDescription);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Theme::$statement[Theme::statement_setDescription]->execute(array($this->description, $this->tid));
			Log::trace('DB', 'Executed Theme::statement_setDescription ['.$this->description.', '.$this->tid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid, $persist=true) {
		$old_uid = $this->uid;
		$this->uid = $new_uid;
		
		if ($persist) {
			Theme::prepareStatement(Theme::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Theme::$statement[Theme::statement_setUid]->execute(array($this->uid, $this->tid));
			Log::trace('DB', 'Executed Theme::statement_setUid ['.$this->uid.', '.$this->tid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			ThemeList::deleteByUidAndStatus($old_uid, $this->status);
			ThemeList::deleteByUidAndStatus($new_uid, $this->status);
			ThemeList::deleteByXidAndUidAndStatus($this->xid, $old_uid, $this->status);
			ThemeList::deleteByXidAndUidAndStatus($this->xid, $new_uid, $this->status);
		}
	}

	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			Theme::prepareStatement(Theme::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Theme::$statement[Theme::statement_setStatus]->execute(array($this->status, $this->tid));
			Log::trace('DB', 'Executed Theme::statement_setStatus ['.$this->status.', '.$this->tid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			ThemeList::deleteByUidAndStatus($this->uid, $old_status);
			ThemeList::deleteByUidAndStatus($this->uid, $new_status);
			
			ThemeList::deleteByXidAndStatus($this->xid, $old_status);
			ThemeList::deleteByXidAndStatus($this->xid, $new_status);
			ThemeList::deleteByXidAndUidAndStatus($this->xid, $this->uid, $old_status);
			ThemeList::deleteByXidAndUidAndStatus($this->xid, $this->uid, $new_status);		
		}
	}
	
	public function getDeletionPoints() { return $this->deletion_points; }
	
	public function setDeletionPoints($new_deletion_points) { $this->deletion_points = $new_deletion_points; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(Theme::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case Theme::statement_get:
					Theme::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['TITLE'].', '.$COLUMN['DESCRIPTION'].', '.$COLUMN['STATUS']
						.', '.$COLUMN['TID'].', '.$COLUMN['XID']
						.', '.$COLUMN['UID']
						.', '.$COLUMN['DELETION_POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['THEME']
						.' WHERE '.$COLUMN['TID'].' = ?'
								, array('integer'));
					break;
				case Theme::statement_create:
					Theme::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['THEME']
						.'( '.$COLUMN['XID']
						.', '.$COLUMN['UID']
						.', '.$COLUMN['TITLE']
						.', '.$COLUMN['DESCRIPTION']
						.', '.$COLUMN['STATUS']
						.', '.$COLUMN['DELETION_POINTS']
						.') VALUES(?, ?, ?, ?, ?, ?)', array('integer', 'text', 'text', 'text', 'integer', 'integer'));
					break;	
				case Theme::statement_delete:
					Theme::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['THEME']
						.' WHERE '.$COLUMN['TID'].' = ?'
						, array('integer'));
					break;	
				case Theme::statement_setTitle:
					Theme::$statement[$statement] = DB::prepareSetter($TABLE['THEME'], array($COLUMN['TID'] => 'integer'), $COLUMN['TITLE'], 'text');
					break;
				case Theme::statement_setDescription:
					Theme::$statement[$statement] = DB::prepareSetter($TABLE['THEME'], array($COLUMN['TID'] => 'integer'), $COLUMN['DESCRIPTION'], 'text');
					break;
				case Theme::statement_setUid:
					Theme::$statement[$statement] = DB::prepareSetter($TABLE['THEME'], array($COLUMN['TID'] => 'integer'), $COLUMN['UID'], 'text');
					break;
				case Theme::statement_setStatus:
					Theme::$statement[$statement] = DB::prepareSetter($TABLE['THEME'], array($COLUMN['TID'] => 'integer'), $COLUMN['STATUS'], 'integer');
					break;
				case Theme::statement_setXid:
					Theme::$statement[$statement] = DB::prepareSetter($TABLE['THEME'], array($COLUMN['TID'] => 'integer'), $COLUMN['XID'], 'integer');
					break;
			}
		}
	}
	
	public function getScore($user=null) {
		global $THEME_VOTE_STATUS;
		global $USER_STATUS;
		
		$theme_vote_list = array_values(ThemeVoteList::getByTidAndStatus($this->tid, $THEME_VOTE_STATUS['CAST']));
		
		if ($user !== null && $user->getStatus() == $USER_STATUS['UNREGISTERED']) {
			$anonymous_theme_vote_list = ThemeVoteList::getByUidAndStatus($user->getUid(), $THEME_VOTE_STATUS['ANONYMOUS']);

			if (isset($anonymous_theme_vote_list[$this->tid])) $theme_vote_list[]= $anonymous_theme_vote_list[$this->tid];
		} elseif ($user !== null && $user->getStatus() == $USER_STATUS['BANNED']) {
			$anonymous_theme_vote_list = ThemeVoteList::getByUidAndStatus($user->getUid(), $THEME_VOTE_STATUS['BANNED']);

			if (isset($anonymous_theme_vote_list[$this->tid])) $theme_vote_list[]= $anonymous_theme_vote_list[$this->tid];		
		}
		
		return array_sum($theme_vote_list);
	}
}

?>