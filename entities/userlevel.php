<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class UserLevelException extends Exception {}

class UserLevel implements Persistent {
	private $uid;
	private $level;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'UserLevel-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of user level with uid='.$this->uid.' and level='.$this->level);
		
		try {
			Cache::replaceorset(UserLevel::cache_prefix.$this->uid.'-'.$this->level, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of user level with uid='.$this->uid.' and level='.$this->level);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($uid, $level) {
		UserLevel::prepareStatement(UserLevel::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		UserLevel::$statement[UserLevel::statement_create]->execute(array($uid, $level));
		Log::trace('DB', 'Executed UserLevel::statement_create ['.$uid.', '.$level.'], ('.(microtime(true) - $start_timestamp).')');

		$this->setUid($uid);
		$this->setLevel($level, false);
		$this->saveCache();
		
		UserLevelList::deleteByUid($uid);
		UserLevelList::deleteByLevel($level);
	}
	
	public static function get($uid, $level, $cache = true) {
		if ($uid === null) throw new UserLevelException('No user level for that uid: '.$uid.' and level='.$level);
		
		try {
			$community_membership = Cache::get(UserLevel::cache_prefix.$uid.'-'.$level);
		} catch (CacheException $e) {
			UserLevel::prepareStatement(UserLevel::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserLevel::$statement[UserLevel::statement_get]->execute(array($uid, $level));
			Log::trace('DB', 'Executed UserLevel::statement_get ['.$uid.', '.$level.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new UserLevelException('No community membership for that uid: '.$uid.' and level='.$level);
			
			$row = $result->fetchRow();
			$result->free();
			
			$community_membership = new UserLevel();
			$community_membership->populateFields($row);
			if ($cache) $community_membership->saveCache();
		}
		return $community_membership;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setUid($row[$COLUMN['UID']]);
		$this->setLevel($row[$COLUMN['LEVEL']]);
	}
	
	public function delete() {
		UserLevel::prepareStatement(UserLevel::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = UserLevel::$statement[UserLevel::statement_delete]->execute(array($this->uid, $this->level));
		Log::trace('DB', 'Executed UserLevel::statement_delete ['.$this->uid.', '.$this->level.'] ('.(microtime(true) - $start_timestamp).')');

		try { Cache::delete(UserLevel::cache_prefix.$this->uid.'-'.$this->level); } catch (CacheException $e) {}
		
		// Remove from associated lists
		
		UserLevelList::deleteByUid($this->uid);
		UserLevelList::deleteByLevel($this->level);
	}
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public function getLevel() { return $this->level; }
	
	public function setLevel($new_level) { $this->level = $new_level; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(UserLevel::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case UserLevel::statement_get:
					UserLevel::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['LEVEL']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_LEVEL']
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['LEVEL'].' = ?'
								, array('text', 'integer'));
					break;
				case UserLevel::statement_create:
					UserLevel::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['USER_LEVEL']
						.'( '.$COLUMN['UID'].', '.$COLUMN['LEVEL']
						.') VALUES(?, ?)', array('text', 'integer'));
					break;	
				case UserLevel::statement_delete:
					UserLevel::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['USER_LEVEL']
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['LEVEL'].' = ?'
						, array('text', 'integer'));
					break;
			}
		}
	}
}

?>