<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2.php';

class UserNameIndexListException extends Exception {}

class UserNameIndexList {
	private static $statement = array();
	
	const statement_getByChunk = 1;
	const statement_getByUid = 2;
	const statement_getAll = 3;
	
	const cache_prefix_chunk = 'UserNameIndexListByChunk-';
	const cache_prefix_uid = 'UserNameIndexListByUid-';
	
	public static function deleteByChunk($chunk) {
		try { Cache::delete(UserNameIndexList::cache_prefix_chunk.$chunk); } catch (CacheException $e) {}
	}
	
	public static function getByChunk($chunk) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserNameIndexList::cache_prefix_chunk.$chunk);
		} catch (CacheException $e) { 
			UserNameIndexList::prepareStatement(UserNameIndexList::statement_getByChunk);
				
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserNameIndexList::$statement[UserNameIndexList::statement_getByChunk]->execute($chunk);
			Log::trace('DB', 'Executed UserNameIndexList::statement_getByChunk ['.$chunk.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => COUNT
				$result->free();
			}
			
			try {
				Cache::setorreplace(UserNameIndexList::cache_prefix_chunk.$chunk, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUid($uid) {
		try { Cache::delete(UserNameIndexList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserNameIndexList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			UserNameIndexList::prepareStatement(UserNameIndexList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserNameIndexList::$statement[UserNameIndexList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed UserNameIndexList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // CHUNK => COUNT
				$result->free();
			}
			
			try {
				Cache::setorreplace(UserNameIndexList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getAll() {
		global $COLUMN;
		
		UserNameIndexList::prepareStatement(UserNameIndexList::statement_getAll);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = UserNameIndexList::$statement[UserNameIndexList::statement_getAll]->execute();
		Log::trace('DB', 'Executed UserNameIndexList::statement_getAll [] ('.(microtime(true) - $start_timestamp).')');
		
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[] = array('chunk' => $row[$COLUMN['CHUNK']], 'uid' => $row[$COLUMN['UID']]);
			$result->free();
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(UserNameIndexList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case UserNameIndexList::statement_getByChunk:
					UserNameIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_NAME_INDEX']
						.' USE INDEX('.$COLUMN['CHUNK'].')'
						.' WHERE '.$COLUMN['CHUNK'].' = ?'
								, array('text'));
					break;
				case UserNameIndexList::statement_getByUid:
					UserNameIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CHUNK'].', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_NAME_INDEX']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case UserNameIndexList::statement_getAll:
					UserNameIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CHUNK'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_NAME_INDEX']
								, array());
					break;
			}
		}
	}
}

?>