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

class ThemeListException extends Exception {}

class ThemeList {
	private static $statement = array();
	
	const statement_getByUidAndStatus = 1;
	const statement_getByXidAndStatus = 2;
	const statement_getByXid = 3;
	const statement_getByXidAndUidAndStatus = 6;
	
	const cache_prefix_uid_and_status = 'ThemeListByUidAndStatus-';
	const cache_prefix_xid_and_status = 'ThemeListByXidAndStatus-';
	const cache_prefix_xid = 'ThemeListByXid-';
	const cache_prefix_xid_and_uid_and_status = 'ThemeListByXidAndUidAndStatus-';
	
	public static function deleteByUidAndStatus($uid, $status) {
		try { Cache::delete(ThemeList::cache_prefix_uid_and_status.$uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndStatus($uid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(ThemeList::cache_prefix_uid_and_status.$uid.'-'.$status);
		} catch (CacheException $e) { 
			ThemeList::prepareStatement(ThemeList::statement_getByUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = ThemeList::$statement[ThemeList::statement_getByUidAndStatus]->execute(array($uid, $status));
			Log::trace('DB', 'Executed ThemeList::statement_getByUidAndStatus ['.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // TID => XID
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(ThemeList::cache_prefix_uid_and_status.$uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByXidAndStatus($xid, $status) {
		try { Cache::delete(ThemeList::cache_prefix_xid_and_status.$xid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByXidAndStatus($xid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(ThemeList::cache_prefix_xid_and_status.$xid.'-'.$status);
		} catch (CacheException $e) { 
			ThemeList::prepareStatement(ThemeList::statement_getByXidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = ThemeList::$statement[ThemeList::statement_getByXidAndStatus]->execute(array($xid, $status));
			Log::trace('DB', 'Executed ThemeList::statement_getByXidAndStatus ['.$xid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['TID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(ThemeList::cache_prefix_xid_and_status.$xid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByXidAndUidAndStatus($xid, $uid, $status) {
		try { Cache::delete(ThemeList::cache_prefix_xid_and_uid_and_status.'-'.$xid.'-'.$uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByXidAndUidAndStatus($xid, $uid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(ThemeList::cache_prefix_xid_and_uid_and_status.'-'.$xid.'-'.$uid.'-'.$status);
		} catch (CacheException $e) { 
			ThemeList::prepareStatement(ThemeList::statement_getByXidAndUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = ThemeList::$statement[ThemeList::statement_getByXidAndUidAndStatus]->execute(array($xid, $uid, $status));
			Log::trace('DB', 'Executed ThemeList::statement_getByXidAndUidAndStatus ['.$xid.', '.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['TID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(ThemeList::cache_prefix_xid_and_uid_and_status.'-'.$xid.'-'.$uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByXid($xid) {
		try { Cache::delete(ThemeList::cache_prefix_xid.$xid); } catch (CacheException $e) {}
	}
	
	public static function getByXid($xid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(ThemeList::cache_prefix_xid.$xid);
		} catch (CacheException $e) { 
			ThemeList::prepareStatement(ThemeList::statement_getByXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = ThemeList::$statement[ThemeList::statement_getByXid]->execute($xid);
			Log::trace('DB', 'Executed ThemeList::statement_getByXid ['.$xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['TID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(ThemeList::cache_prefix_xid.$xid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(ThemeList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case ThemeList::statement_getByUidAndStatus:
					ThemeList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['TID'].', '.$COLUMN['XID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['THEME']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case ThemeList::statement_getByXidAndStatus:
					ThemeList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['TID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['THEME']
						.' USE INDEX('.$COLUMN['XID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'integer'));
					break;
				case ThemeList::statement_getByXid:
					ThemeList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['TID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['THEME']
						.' USE INDEX('.$COLUMN['XID'].')'
						.' WHERE '.$COLUMN['XID'].' = ?'
								, array('integer'));
					break;
				case ThemeList::statement_getByXidAndUidAndStatus:
					ThemeList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['TID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['THEME']
						.' USE INDEX('.$COLUMN['XID'].'_and_'.$COLUMN['UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'text', 'integer'));
					break;
			}
		}
	}
}

?>