<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2.php';

class UserLevelListException extends Exception {}

class UserLevelList {
	private static $statement = array();
	
	const statement_getByUid = 1;
	const statement_getByLevel = 2;
	
	const cache_prefix_uid = 'UserLevelListByUid-';
	const cache_prefix_level = 'UserLevelListByLevel-';
	
	public static function deleteByLevel($level) {
		try { Cache::delete(UserLevelList::cache_prefix_level.$level); } catch (CacheException $e) {}
	}
	
	public static function getByLevel($level, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserLevelList::cache_prefix_level.$level);
		} catch (CacheException $e) { 
			UserLevelList::prepareStatement(UserLevelList::statement_getByLevel);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserLevelList::$statement[UserLevelList::statement_getByLevel]->execute(array($level));
			Log::trace('DB', 'Executed UserLevelList::statement_getByLevel ['.$level.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['UID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(UserLevelList::cache_prefix_level.$level, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUid($uid) {
		try { Cache::delete(UserLevelList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserLevelList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			UserLevelList::prepareStatement(UserLevelList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserLevelList::$statement[UserLevelList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed UserLevelList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['LEVEL']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(UserLevelList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(UserLevelList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case UserLevelList::statement_getByLevel:
					UserLevelList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_LEVEL']
						.' USE INDEX('.$COLUMN['LEVEL'].')'
						.' WHERE '.$COLUMN['LEVEL'].' = ?'
								, array('integer'));
					break;
				case UserLevelList::statement_getByUid:
					UserLevelList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['LEVEL']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_LEVEL']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
			}
		}
	}
}

?>