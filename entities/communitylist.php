<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class CommunityListException extends Exception {}

class CommunityList {
	private static $statement = array();
	
	const statement_getByUidAndStatus = 1;
	const statement_getByStatus = 2;
	const statement_getByLidAndStatus = 3;
	
	const cache_prefix_uid_and_status = 'CommunityListByUidAndStatus-';
	const cache_prefix_status = 'CommunityListByStatus-';
	const cache_prefix_lid_and_status = 'CommunityListByLidAndStatus-';
	
	public static function deleteByStatus($status) {
		try { Cache::delete(CommunityList::cache_prefix_status.$status); } catch (CacheException $e) {}
	}
	
	public static function getByStatus($status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommunityList::cache_prefix_status.$status);
		} catch (CacheException $e) { 
			CommunityList::prepareStatement(CommunityList::statement_getByStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityList::$statement[CommunityList::statement_getByStatus]->execute($status);
			Log::trace('DB', 'Executed CommunityList::statement_getByStatus ['.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['XID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(CommunityList::cache_prefix_status.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUidAndStatus($uid, $status) {
		try { Cache::delete(CommunityList::cache_prefix_uid_and_status.$uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndStatus($uid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommunityList::cache_prefix_uid_and_status.$uid.'-'.$status);
		} catch (CacheException $e) { 
			CommunityList::prepareStatement(CommunityList::statement_getByUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityList::$statement[CommunityList::statement_getByUidAndStatus]->execute(array($uid, $status));
			Log::trace('DB', 'Executed CommunityList::statement_getByUidAndStatus ['.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['XID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(CommunityList::cache_prefix_uid_and_status.$uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByLidAndStatus($lid, $status) {
		try { Cache::delete(CommunityList::cache_prefix_lid_and_status.$lid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByLidAndStatus($lid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommunityList::cache_prefix_lid_and_status.$lid.'-'.$status);
		} catch (CacheException $e) { 
			CommunityList::prepareStatement(CommunityList::statement_getByLidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityList::$statement[CommunityList::statement_getByLidAndStatus]->execute(array($lid, $status));
			Log::trace('DB', 'Executed CommunityList::statement_getByLidAndStatus ['.$lid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['XID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(CommunityList::cache_prefix_lid_and_status.$lid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CommunityList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CommunityList::statement_getByStatus:
					CommunityList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['XID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY']
						.' USE INDEX('.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['STATUS'].' = ?'
								, array('integer'));
					break;
				case CommunityList::statement_getByUidAndStatus:
					CommunityList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['XID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case CommunityList::statement_getByLidAndStatus:
					CommunityList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['XID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY']
						.' USE INDEX('.$COLUMN['LID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['LID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'integer'));
					break;
			}
		}
	}
}

?>