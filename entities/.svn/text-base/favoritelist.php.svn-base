<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of favorites
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class FavoriteListException extends Exception {}

class FavoriteList {
	private static $statement = array();
	
	const statement_getByUid = 1;
	const statement_getByEid = 3;
	const statement_getAll = 4;
	
	const cache_prefix_uid = 'FavoriteListByUid-';
	const cache_prefix_eid = 'FavoriteListByEid-';
	
	public static function deleteByUid($uid) {
		try { Cache::delete(FavoriteList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(FavoriteList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			FavoriteList::prepareStatement(FavoriteList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = FavoriteList::$statement[FavoriteList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed FavoriteList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // EID => CREATION_TIME
				$result->free();
			}
			
			try {
				Cache::setorreplace(FavoriteList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByEid($eid) {
		try { Cache::delete(FavoriteList::cache_prefix_eid.$eid); } catch (CacheException $e) {}
	}
	
	public static function getByEid($eid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(FavoriteList::cache_prefix_eid.$eid);
		} catch (CacheException $e) { 
			FavoriteList::prepareStatement(FavoriteList::statement_getByEid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = FavoriteList::$statement[FavoriteList::statement_getByEid]->execute($eid);
			Log::trace('DB', 'Executed FavoriteList::statement_getByEid ['.$eid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => CREATION_TIME
				$result->free();
			}
			
			try {
				Cache::setorreplace(FavoriteList::cache_prefix_eid.$eid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getAll() {
		global $COLUMN;
		
		FavoriteList::prepareStatement(FavoriteList::statement_getAll);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = FavoriteList::$statement[FavoriteList::statement_getAll]->execute();
		Log::trace('DB', 'Executed FavoriteList::statement_getAll [] ('.(microtime(true) - $start_timestamp).')');
		
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[] = array('eid' => $row[$COLUMN['EID']], 'uid' => $row[$COLUMN['UID']]);
			$result->free();
		}
		
		return $list;
	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(FavoriteList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case FavoriteList::statement_getByUid:
					FavoriteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['FAVORITE']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case FavoriteList::statement_getByEid:
					FavoriteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['FAVORITE']
						.' USE INDEX('.$COLUMN['EID'].')'
						.' WHERE '.$COLUMN['EID'].' = ?'
								, array('integer'));
					break;
				case FavoriteList::statement_getAll:
					FavoriteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['FAVORITE']
								, array());
					break;
			}
		}
	}
}

?>