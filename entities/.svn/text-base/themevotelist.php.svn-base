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

class ThemeVoteListException extends Exception {}

class ThemeVoteList {
	private static $statement = array();
	
	const statement_getByUidAndStatus = 1;
	const statement_getByTidAndStatus = 2;
	const statement_getByTid = 3;
	
	const cache_prefix_uid_and_status = 'ThemeVoteListByUidAndStatus-';
	const cache_prefix_tid_and_status = 'ThemeVoteListByTidAndStatus-';
	const cache_prefix_tid = 'ThemeVoteListByTid-';
	
	public static function deleteByUidAndStatus($uid, $status) {
		try { Cache::delete(ThemeVoteList::cache_prefix_uid_and_status.$uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndStatus($uid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(ThemeVoteList::cache_prefix_uid_and_status.$uid.'-'.$status);
		} catch (CacheException $e) { 
			ThemeVoteList::prepareStatement(ThemeVoteList::statement_getByUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = ThemeVoteList::$statement[ThemeVoteList::statement_getByUidAndStatus]->execute(array($uid, $status));
			Log::trace('DB', 'Executed ThemeVoteList::statement_getByUidAndStatus ['.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // TID => POINTS
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(ThemeVoteList::cache_prefix_uid_and_status.$uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByTidAndStatus($tid, $status) {
		try { Cache::delete(ThemeVoteList::cache_prefix_tid_and_status.$tid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByTidAndStatus($tid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(ThemeVoteList::cache_prefix_tid_and_status.$tid.'-'.$status);
		} catch (CacheException $e) { 
			ThemeVoteList::prepareStatement(ThemeVoteList::statement_getByTidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = ThemeVoteList::$statement[ThemeVoteList::statement_getByTidAndStatus]->execute(array($tid, $status));
			Log::trace('DB', 'Executed ThemeVoteList::statement_getByTidAndStatus ['.$tid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => POINTS
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(ThemeVoteList::cache_prefix_tid_and_status.$tid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByTid($tid) {
		try { Cache::delete(ThemeVoteList::cache_prefix_tid.$tid); } catch (CacheException $e) {}
	}
	
	public static function getByTid($tid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(ThemeVoteList::cache_prefix_tid.$tid);
		} catch (CacheException $e) { 
			ThemeVoteList::prepareStatement(ThemeVoteList::statement_getByTid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = ThemeVoteList::$statement[ThemeVoteList::statement_getByTid]->execute($tid);
			Log::trace('DB', 'Executed ThemeVoteList::statement_getByTid ['.$tid.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => POINTS
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(ThemeVoteList::cache_prefix_tid.$tid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(ThemeVoteList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case ThemeVoteList::statement_getByUidAndStatus:
					ThemeVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['TID'].', '.$COLUMN['POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['THEME_VOTE']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case ThemeVoteList::statement_getByTidAndStatus:
					ThemeVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['THEME_VOTE']
						.' USE INDEX('.$COLUMN['TID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['TID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'integer'));
					break;
				case ThemeVoteList::statement_getByTid:
					ThemeVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['THEME_VOTE']
						.' USE INDEX('.$COLUMN['TID'].')'
						.' WHERE '.$COLUMN['TID'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>