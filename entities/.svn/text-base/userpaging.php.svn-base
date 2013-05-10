<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Stores the user paging preferences (amount of elements displayed per page)
*/

require_once(dirname(__FILE__).'/../entities/userpaginglist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class UserPagingException extends Exception {}

class UserPaging implements Persistent {
	private $uid;
	private $pgid;
	private $value;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setValue = 4;
	
    const cache_prefix = 'UserPaging-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of paging setting with uid='.$this->uid.' and pgid='.$this->pgid);
		
		try {
			Cache::replaceorset(UserPaging::cache_prefix.$this->uid.'-'.$this->pgid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of paging setting with uid='.$this->uid.' and pgid='.$this->pgid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 3)
			self::__construct2($argv[0], $argv[1], $argv[2]);
    }
	
	public function __construct2($uid, $pgid, $value) {
		UserPaging::prepareStatement(UserPaging::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		UserPaging::$statement[UserPaging::statement_create]->execute(array($uid, $pgid, $value));
		Log::trace('DB', 'Executed UserPaging::statement_create ['.$uid.', '.$pgid.'", '.$value.'] ('.(microtime(true) - $start_timestamp).')');
		$pgid = DB::insertid();

		$this->setUid($uid);
		$this->setPGid($pgid);
		$this->setValue($value, false);
		$this->saveCache();
		
		UserPagingList::deleteByUid($uid);
	}
	
	public static function get($uid, $pgid) {
		if ($pgid === null || $uid === null) throw new UserPagingException('No paging setting for uid='.$uid.' and pgid='.$pgid);
		
		try {
			$userpaging = Cache::get(UserPaging::cache_prefix.$uid.'-'.$pgid);
		} catch (CacheException $e) {
			UserPaging::prepareStatement(UserPaging::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserPaging::$statement[UserPaging::statement_get]->execute(array($uid, $pgid));
			Log::trace('DB', 'Executed UserPaging::statement_get ['.$uid.', '.$pgid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new UserPagingException('No paging setting for uid='.$uid.' and pgid='.$pgid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$userpaging = new UserPaging();
			$userpaging->populateFields($row);
			$userpaging->saveCache();
		}
		
		return $userpaging;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setUid($row[$COLUMN['UID']]);
		$this->setPGid($row[$COLUMN['PGID']]);
		$this->setValue($row[$COLUMN['VALUE']], false);
	}
	
	public function delete() {
		UserPaging::prepareStatement(UserPaging::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		UserPaging::$statement[UserPaging::statement_delete]->execute(array($this->uid, $this->pgid));
		Log::trace('DB', 'Executed UserPaging::statement_delete ['.$this->uid.', '.$this->pgid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(UserPaging::cache_prefix.$this->uid.'-'.$this->pgid); } catch (CacheException $e) {}
		
		// Remove from associated lists
		
		UserPagingList::deleteByUid($this->uid);
	}
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public function getPGid() { return $this->pgid; }
	
	public function setPGid($new_pgid) { $this->pgid = $new_pgid; }
	
	public function getValue() { return $this->value; }
	
	public function setValue($new_value, $persist=true) {	
		$this->value = $new_value;
		
		if ($persist) {
			UserPaging::prepareStatement(UserPaging::statement_setValue);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			UserPaging::$statement[UserPaging::statement_setValue]->execute(array($this->value, $this->uid, $this->pgid));
			Log::trace('DB', 'Executed UserPaging::statement_setValue ['.$this->value.', '.$this->uid.', '.$this->pgid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			UserPagingList::deleteByUid($this->uid);
		}
	}
	
	public function getPagingValue($uid, $paging_name) {
		global $PAGING;
		global $PAGING_DEFAULT;
		
		if (!isset($PAGING[$paging_name]))
			throw new UserPagingException($paging_name.' is not a valid paging settings name');
		
		$valuelist = UserPagingList::getByUid($uid);
		
		if (!isset($valuelist[$PAGING[$paging_name]])) return $PAGING_DEFAULT[$paging_name];
		else return $valuelist[$PAGING[$paging_name]];
	}
	
	public function setPagingValue($uid, $paging_name, $value) {
		global $PAGING;
		global $PAGING_DEFAULT;
		
		if (!isset($PAGING[$paging_name]))
			throw new UserPagingException($paging_name.' is not a valid paging settings name');
		
		$valuelist = UserPagingList::getByUid($uid);
		
		try {	
			if (!isset($valuelist[$PAGING[$paging_name]])) {
				if ($value != $PAGING_DEFAULT[$paging_name]) $userpaging = new UserPaging($uid, $PAGING[$paging_name], $value);
			} elseif ($value != $PAGING_DEFAULT[$paging_name]) {
				$userpaging = UserPaging::get($uid, $PAGING[$paging_name]);
				$userpaging->setValue($value);
			} else {
				$userpaging = UserPaging::get($uid, $PAGING[$paging_name]);
				$userpaging->delete();
			} 
		} catch (UserPagingException $e) {}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(UserPaging::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case UserPaging::statement_get:
					UserPaging::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['PGID']
						.', '.$COLUMN['VALUE']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_PAGING']
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['PGID'].' = ?'
								, array('text', 'integer'));
					break;
				case UserPaging::statement_create:
					UserPaging::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['USER_PAGING'].'( '.$COLUMN['UID']
						.', '.$COLUMN['PGID'].', '.$COLUMN['VALUE']
						.') VALUES(?, ?, ?)', array('text', 'integer', 'integer'));
					break;	
				case UserPaging::statement_delete:
					UserPaging::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['USER_PAGING']
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['PGID'].' = ?'
						, array('text', 'integer'));
					break;	
				case UserPaging::statement_setValue:
					UserPaging::$statement[$statement] = DB::prepareSetter($TABLE['USER_PAGING'], array($COLUMN['UID'] => 'text', $COLUMN['PGID'] => 'integer'), $COLUMN['VALUE'], 'integer');
					break;
			}
		}
	}
}

?>