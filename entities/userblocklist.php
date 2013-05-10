<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of blocked users
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class UserBlockListException extends Exception {}

class UserBlockList {
	private static $statement = array();
	
	const statement_getByBlockedUid = 1;
	const statement_getByUid = 3;
	const statement_getAll = 4;
	
	const cache_prefix_blocked_uid = 'UserBlockListByBlockedUid-';
	const cache_prefix_uid = 'UserBlockListByUid-';
	
	public static function deleteByBlockedUid($blocked_uid) {
		try { Cache::delete(UserBlockList::cache_prefix_blocked_uid.$blocked_uid); } catch (CacheException $e) {}
	}
	
	public static function getByBlockedUid($blocked_uid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserBlockList::cache_prefix_blocked_uid.$blocked_uid);
		} catch (CacheException $e) { 
			UserBlockList::prepareStatement(UserBlockList::statement_getByBlockedUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserBlockList::$statement[UserBlockList::statement_getByBlockedUid]->execute($blocked_uid);
			Log::trace('DB', 'Executed UserBlockList::statement_getByBlockedUid ['.$blocked_uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['UID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(UserBlockList::cache_prefix_blocked_uid.$blocked_uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUid($uid) {
		try { Cache::delete(UserBlockList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserBlockList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			UserBlockList::prepareStatement(UserBlockList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserBlockList::$statement[UserBlockList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed UserBlockList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['BLOCKED_UID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(UserBlockList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getAll() {
		global $COLUMN;
		
		UserBlockList::prepareStatement(UserBlockList::statement_getAll);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = UserBlockList::$statement[UserBlockList::statement_getAll]->execute();
		Log::trace('DB', 'Executed UserBlockList::statement_getAll [] ('.(microtime(true) - $start_timestamp).')');
		
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[] = array('uid' => $row[$COLUMN['UID']], 'blocked_uid' => $row[$COLUMN['BLOCKED_UID']]);
			$result->free();
		}
		
		return $list;
	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(UserBlockList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case UserBlockList::statement_getByBlockedUid:
					UserBlockList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_BLOCK']
						.' USE INDEX('.$COLUMN['BLOCKED_UID'].')'
						.' WHERE '.$COLUMN['BLOCKED_UID'].' = ?'
								, array('text'));
					break;
				case UserBlockList::statement_getByUid:
					UserBlockList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['BLOCKED_UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_BLOCK']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case UserBlockList::statement_getAll:
					UserBlockList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['BLOCKED_UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_BLOCK']
								, array());
					break;
			}
		}
	}
}

?>