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

class CommunityMembershipListException extends Exception {}

class CommunityMembershipList {
	private static $statement = array();
	
	const statement_getByUid = 1;
	const statement_getByXidAndStatus = 2;
	const statement_getByXid = 3;
	
	const cache_prefix_uid = 'CommunityMembershipListByUid-';
	const cache_prefix_xid_and_status = 'CommunityMembershipListByXidAndStatus-';
	const cache_prefix_xid = 'CommunityMembershipListByXid-';
	
	public static function deleteByXidAndStatus($xid, $status) {
		try { Cache::delete(CommunityMembershipList::cache_prefix_xid_and_status.$xid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByXidAndStatus($xid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommunityMembershipList::cache_prefix_xid_and_status.$xid.'-'.$status);
		} catch (CacheException $e) { 
			CommunityMembershipList::prepareStatement(CommunityMembershipList::statement_getByXidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityMembershipList::$statement[CommunityMembershipList::statement_getByXidAndStatus]->execute(array($xid, $status));
			Log::trace('DB', 'Executed CommunityMembershipList::statement_getByXidAndStatus ['.$xid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => JOIN_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(CommunityMembershipList::cache_prefix_xid_and_status.$xid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByXid($xid) {
		try { Cache::delete(CommunityMembershipList::cache_prefix_xid.$xid); } catch (CacheException $e) {}
	}
	
	public static function getByXid($xid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommunityMembershipList::cache_prefix_xid.$xid);
		} catch (CacheException $e) { 
			CommunityMembershipList::prepareStatement(CommunityMembershipList::statement_getByXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityMembershipList::$statement[CommunityMembershipList::statement_getByXid]->execute(array($xid));
			Log::trace('DB', 'Executed CommunityMembershipList::statement_getByXid ['.$xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => JOIN_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(CommunityMembershipList::cache_prefix_xid.$xid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUid($uid) {
		try { Cache::delete(CommunityMembershipList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommunityMembershipList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			CommunityMembershipList::prepareStatement(CommunityMembershipList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityMembershipList::$statement[CommunityMembershipList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed CommunityMembershipList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // XID => JOIN_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(CommunityMembershipList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CommunityMembershipList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CommunityMembershipList::statement_getByXidAndStatus:
					CommunityMembershipList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.', UNIX_TIMESTAMP('.$COLUMN['JOIN_TIME'].') AS '.$COLUMN['JOIN_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MEMBERSHIP']
						.' USE INDEX('.$COLUMN['XID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer'));
					break;
				case CommunityMembershipList::statement_getByXid:
					CommunityMembershipList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.', UNIX_TIMESTAMP('.$COLUMN['JOIN_TIME'].') AS '.$COLUMN['JOIN_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MEMBERSHIP']
						.' USE INDEX('.$COLUMN['XID'].')'
						.' WHERE '.$COLUMN['XID'].' = ?'
								, array('integer'));
					break;
				case CommunityMembershipList::statement_getByUid:
					CommunityMembershipList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['XID']
						.', UNIX_TIMESTAMP('.$COLUMN['JOIN_TIME'].') AS '.$COLUMN['JOIN_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MEMBERSHIP']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
			}
		}
	}
}

?>