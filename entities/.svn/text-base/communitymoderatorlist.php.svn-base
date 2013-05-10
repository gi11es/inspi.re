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

class CommunityModeratorListException extends Exception {}

class CommunityModeratorList {
	private static $statement = array();
	
	const statement_getByUid = 1;
	const statement_getByXid = 2;
	const statement_getAll = 3;
	
	const cache_prefix_uid = 'CommunityModeratorListByUid-';
	const cache_prefix_xid = 'CommunityModeratorListByXid-';
	
	public static function deleteByXid($xid) {
		try { Cache::delete(CommunityModeratorList::cache_prefix_xid.$xid); } catch (CacheException $e) {}
	}
	
	public static function getByXidAndTimestamp($xid, $timestamp) {
		$list = CommunityModeratorList::getByXid($xid);
		return array_filter($list, Functions::makeGreaterThanFunction($timestamp));
	}
	
	public static function getByXid($xid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommunityModeratorList::cache_prefix_xid.$xid);
		} catch (CacheException $e) { 
			CommunityModeratorList::prepareStatement(CommunityModeratorList::statement_getByXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityModeratorList::$statement[CommunityModeratorList::statement_getByXid]->execute($xid);
			Log::trace('DB', 'Executed CommunityModeratorList::statement_getByXid ['.$xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['UID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(CommunityModeratorList::cache_prefix_xid.$xid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUid($uid) {
		try { Cache::delete(CommunityModeratorList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommunityModeratorList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			CommunityModeratorList::prepareStatement(CommunityModeratorList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityModeratorList::$statement[CommunityModeratorList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed CommunityModeratorList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['XID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(CommunityModeratorList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getAll() {
		global $COLUMN;
		
		CommunityModeratorList::prepareStatement(CommunityModeratorList::statement_getAll);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = CommunityModeratorList::$statement[CommunityModeratorList::statement_getAll]->execute();
		Log::trace('DB', 'Executed CommunityModeratorList::statement_getAll [] ('.(microtime(true) - $start_timestamp).')');
		
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[] = array('xid' => $row[$COLUMN['XID']], 'uid' => $row[$COLUMN['UID']]);
			$result->free();
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CommunityModeratorList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CommunityModeratorList::statement_getByXid:
					CommunityModeratorList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MODERATOR']
						.' USE INDEX('.$COLUMN['XID'].')'
						.' WHERE '.$COLUMN['XID'].' = ?'
								, array('integer'));
					break;
				case CommunityModeratorList::statement_getByUid:
					CommunityModeratorList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['XID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MODERATOR']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case CommunityModeratorList::statement_getAll:
					CommunityModeratorList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['XID'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MODERATOR']
								, array());
					break;
			}
		}
	}
}

?>