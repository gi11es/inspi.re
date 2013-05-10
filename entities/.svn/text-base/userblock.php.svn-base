<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Used when a user blocks another from private messaging
*/

require_once(dirname(__FILE__).'/../entities/userblocklist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class UserBlockException extends Exception {}

class UserBlock implements Persistent {
	private $uid;
	private $blocked_uid;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'UserBlock-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of user block with uid='.$this->uid.' and blocked_uid='.$this->blocked_uid);
		
		try {
			Cache::replaceorset(UserBlock::cache_prefix.$this->uid.'-'.$this->blocked_uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of user block with uid='.$this->uid.' and blocked_uid='.$this->blocked_uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($uid, $blocked_uid) {
		UserBlock::prepareStatement(UserBlock::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		UserBlock::$statement[UserBlock::statement_create]->execute(array($uid, $blocked_uid));
		Log::trace('DB', 'Executed UserBlock::statement_create ['.$uid.', "'.$blocked_uid.'"] ('.(microtime(true) - $start_timestamp).')');

		$this->setUid($uid);
		$this->setBlockedUid($blocked_uid);
		$this->saveCache();
		
		UserBlockList::deleteByUid($uid);
		UserBlockList::deleteByBlockedUid($blocked_uid);
	}
	
	public static function get($uid, $blocked_uid) {
		if ($uid === null || $blocked_uid === null) throw new UserBlockException('No user block for uid='.$uid.' and blocked_uid='.$blocked_uid);
		
		try {
			$user_block = Cache::get(UserBlock::cache_prefix.$uid.'-'.$blocked_uid);
		} catch (CacheException $e) {
			UserBlock::prepareStatement(UserBlock::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserBlock::$statement[UserBlock::statement_get]->execute(array($uid, $blocked_uid));
			Log::trace('DB', 'Executed UserBlock::statement_get ['.$uid.', '.$blocked_uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new UserBlockException('No user block for uid='.$uid.' and blocked_uid='.$blocked_uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$user_block = new UserBlock();
			$user_block->populateFields($row);
			$user_block->saveCache();
		}
		return $user_block;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setUid($row[$COLUMN['UID']]);
		$this->setBlockedUid($row[$COLUMN['BLOCKED_UID']]);
	}
	
	public function delete() {
		UserBlock::prepareStatement(UserBlock::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = UserBlock::$statement[UserBlock::statement_delete]->execute(array($this->uid, $this->blocked_uid));
		Log::trace('DB', 'Executed UserBlock::statement_delete ['.$this->uid.', '.$this->blocked_uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(UserBlock::cache_prefix.$this->blocked_uid); } catch (CacheException $e) {}
		
		UserBlockList::deleteByUid($this->uid);
		UserBlockList::deleteByBlockedUid($this->blocked_uid);
	}
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public function getBlockedUid() { return $this->blocked_uid; }
	
	public function setBlockedUid($new_blocked_uid) { $this->blocked_uid = $new_blocked_uid; }
	
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(UserBlock::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case UserBlock::statement_get:
					UserBlock::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['BLOCKED_UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_BLOCK']
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['BLOCKED_UID'].' = ?'
								, array('text', 'text'));
					break;
				case UserBlock::statement_create:
					UserBlock::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['USER_BLOCK']
						.'( '.$COLUMN['UID'].', '.$COLUMN['BLOCKED_UID']
						.') VALUES(?, ?)', array('text', 'text'));
					break;	
				case UserBlock::statement_delete:
					UserBlock::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['USER_BLOCK']
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['BLOCKED_UID'].' = ?'
						, array('text', 'text'));
					break;
			}
		}
	}
}

?>