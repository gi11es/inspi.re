<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/usernameindexlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class UserNameIndexException extends Exception {}

class UserNameIndex implements Persistent {
	private $chunk;
	private $uid;
	private $count;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setCount = 4;
	
    const cache_prefix = 'UserNameIndex-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of discussion post index with chunk='.$this->chunk.' and uid='.$this->uid);
		
		try {
			Cache::replaceorset(UserNameIndex::cache_prefix.$this->chunk.'-'.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of discussion post index with chunk='.$this->chunk.' and uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 3)
			self::__construct2($argv[0], $argv[1], $argv[2]);
    }
	
	public function __construct2($chunk, $uid, $count) {
		UserNameIndex::prepareStatement(UserNameIndex::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		UserNameIndex::$statement[UserNameIndex::statement_create]->execute(array($chunk, $uid, $count));
		Log::trace('DB', 'Executed UserNameIndex::statement_create ['.$chunk.', '.$uid.'", '.$count.'] ('.(microtime(true) - $start_timestamp).')');
		$uid = DB::insertid();

		$this->setChunk($chunk);
		$this->setUid($uid);
		$this->setCount($count, false);
		$this->saveCache();
		
		UserNameIndexList::deleteByChunk($chunk);
		UserNameIndexList::deleteByUid($uid);
	}
	
	public static function get($chunk, $uid) {
		if ($uid === null || $chunk === null) throw new UserNameIndexException('No user name index for chunk='.$chunk.' and uid='.$uid);
		
		try {
			$usernameindex = Cache::get(UserNameIndex::cache_prefix.$chunk.'-'.$uid);
		} catch (CacheException $e) {
			UserNameIndex::prepareStatement(UserNameIndex::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserNameIndex::$statement[UserNameIndex::statement_get]->execute(array($chunk, $uid));
			Log::trace('DB', 'Executed UserNameIndex::statement_get ['.$chunk.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new UserNameIndexException('No discussion post index for chunk='.$chunk.' and uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$usernameindex = new UserNameIndex();
			$usernameindex->populateFields($row);
			$usernameindex->saveCache();
		}
		
		return $usernameindex;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setChunk($row[$COLUMN['CHUNK']]);
		$this->setUid($row[$COLUMN['UID']]);
		$this->setCount($row[$COLUMN['COUNT']], false);
	}
	
	public function delete() {
		UserNameIndex::prepareStatement(UserNameIndex::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		UserNameIndex::$statement[UserNameIndex::statement_delete]->execute(array($this->chunk, $this->uid));
		Log::trace('DB', 'Executed UserNameIndex::statement_delete ['.$this->chunk.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(UserNameIndex::cache_prefix.$this->chunk.'-'.$this->uid); } catch (CacheException $e) {}
		
		// Remove from associated lists
		
		UserNameIndexList::deleteByChunk($this->chunk);
		UserNameIndexList::deleteByUid($this->uid);
	}
	
	public function getChunk() { return $this->chunk; }
	
	public function setChunk($new_chunk) { $this->chunk = $new_chunk; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public function getCount() { return $this->count; }
	
	public function setCount($new_count, $persist=true) {	
		$this->count = $new_count;
		
		if ($persist) {
			UserNameIndex::prepareStatement(UserNameIndex::statement_setCount);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			UserNameIndex::$statement[UserNameIndex::statement_setCount]->execute(array($this->count, $this->chunk, $this->uid));
			Log::trace('DB', 'Executed User::statement_setCount ['.$this->count.', '.$this->chunk.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(UserNameIndex::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case UserNameIndex::statement_get:
					UserNameIndex::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CHUNK'].', '.$COLUMN['UID']
						.', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_NAME_INDEX']
						.' WHERE '.$COLUMN['CHUNK'].' = ? AND '.$COLUMN['UID'].' = ?'
								, array('text', 'text'));
					break;
				case UserNameIndex::statement_create:
					UserNameIndex::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['USER_NAME_INDEX'].'( '.$COLUMN['CHUNK']
						.', '.$COLUMN['UID'].', '.$COLUMN['COUNT']
						.') VALUES(?, ?, ?)', array('text', 'text', 'integer'));
					break;	
				case UserNameIndex::statement_delete:
					UserNameIndex::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['USER_NAME_INDEX']
						.' WHERE '.$COLUMN['CHUNK'].' = ? AND '.$COLUMN['UID'].' = ?'
						, array('text', 'text'));
					break;	
				case UserNameIndex::statement_setCount:
					UserNameIndex::$statement[$statement] = DB::prepareSetter($TABLE['USER_NAME_INDEX'], array($COLUMN['CHUNK'] => 'text', $COLUMN['UID'] => 'text'), $COLUMN['COUNT'], 'integer');
					break;
			}
		}
	}
}

?>