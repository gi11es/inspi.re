<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2.php';

class DiscussionThreadListException extends Exception {}

class DiscussionThreadList {
	private static $statement = array();
	
	const statement_getByXidAndStatus = 1;
	const statement_getByUidAndStatus = 2;
	const statement_getByNullXidAndStatus = 3;
	const statement_getByEid = 4;
	const statement_getByXid = 5;
	const statement_getByIndexingStatusAndStatus = 6;
	
	const cache_prefix_xid_and_status = 'DiscussionThreadListByXidAndStatus-';
	const cache_prefix_uid_and_status = 'DiscussionThreadListByUidAndStatus-';
	const cache_prefix_eid = 'DiscussionThreadListByEid-';
	const cache_prefix_xid = 'DiscussionThreadListByXid-';
	const cache_prefix_indexing_status_and_status = 'DiscussionPostListByIndexingStatusAndStatus-';
	
	public static function deleteByXidAndStatus($xid, $status) {
		try { Cache::delete(DiscussionThreadList::cache_prefix_xid_and_status.($xid !== null?$xid:'').'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByXidAndStatus($xid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionThreadList::cache_prefix_xid_and_status.($xid !== null?$xid:'').'-'.$status);
		} catch (CacheException $e) { 
			if ($xid !== null) {
				DiscussionThreadList::prepareStatement(DiscussionThreadList::statement_getByXidAndStatus);
				
				$start_timestamp = microtime(true);
				DB::incrementRequestCount();
				$result = DiscussionThreadList::$statement[DiscussionThreadList::statement_getByXidAndStatus]->execute(array($xid, $status));
				Log::trace('DB', 'Executed DiscussionThreadList::statement_getByXidAndStatus ['.$xid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			}
			else {
				DiscussionThreadList::prepareStatement(DiscussionThreadList::statement_getByNullXidAndStatus);
				
				$start_timestamp = microtime(true);
				DB::incrementRequestCount();
				$result = DiscussionThreadList::$statement[DiscussionThreadList::statement_getByNullXidAndStatus]->execute($status);
				Log::trace('DB', 'Executed DiscussionThreadList::statement_getByNullXidAndStatus ['.$status.'] ('.(microtime(true) - $start_timestamp).')');
			}
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // NID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(DiscussionThreadList::cache_prefix_xid_and_status.($xid !== null?$xid:'').'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUidAndStatus($uid, $status) {
		try { Cache::delete(DiscussionThreadList::cache_prefix_uid_and_status.$uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndStatus($uid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionThreadList::cache_prefix_uid_and_status.$uid.'-'.$status);
		} catch (CacheException $e) { 
			DiscussionThreadList::prepareStatement(DiscussionThreadList::statement_getByUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionThreadList::$statement[DiscussionThreadList::statement_getByUidAndStatus]->execute(array($uid, $status));
			Log::trace('DB', 'Executed DiscussionThreadList::statement_getByUidAndStatus ['.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // NID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(DiscussionThreadList::cache_prefix_uid_and_status.$uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByEid($eid) {
		try { Cache::delete(DiscussionThreadList::cache_prefix_eid.$eid); } catch (CacheException $e) {}
	}
	
	public static function getByEid($eid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionThreadList::cache_prefix_eid.$eid);
		} catch (CacheException $e) { 
			DiscussionThreadList::prepareStatement(DiscussionThreadList::statement_getByEid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionThreadList::$statement[DiscussionThreadList::statement_getByEid]->execute($eid);
			Log::trace('DB', 'Executed DiscussionThreadList::statement_getByEid ['.$eid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // NID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(DiscussionThreadList::cache_prefix_eid.$eid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByXid($xid) {
		try { Cache::delete(DiscussionThreadList::cache_prefix_xid.$xid); } catch (CacheException $e) {}
	}
	
	public static function getByXid($xid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionThreadList::cache_prefix_xid.$xid);
		} catch (CacheException $e) { 
			DiscussionThreadList::prepareStatement(DiscussionThreadList::statement_getByXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionThreadList::$statement[DiscussionThreadList::statement_getByXid]->execute($xid);
			Log::trace('DB', 'Executed DiscussionThreadList::statement_getByXid ['.$xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // NID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(DiscussionThreadList::cache_prefix_xid.$xid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByIndexingStatusAndStatus($indexing_status, $status) {
		try { Cache::delete(DiscussionThreadList::cache_prefix_indexing_status_and_status.$indexing_status.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByIndexingStatusAndStatus($indexing_status, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionThreadList::cache_prefix_indexing_status_and_status.$indexing_status.'-'.$status);
		} catch (CacheException $e) { 
			DiscussionThreadList::prepareStatement(DiscussionThreadList::statement_getByIndexingStatusAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionThreadList::$statement[DiscussionThreadList::statement_getByIndexingStatusAndStatus]->execute(array($indexing_status, $status));
			Log::trace('DB', 'Executed DiscussionThreadList::statement_getByIndexingStatusAndStatus ['.$indexing_status.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // NID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(DiscussionThreadList::cache_prefix_indexing_status_and_status.$indexing_status.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(DiscussionThreadList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case DiscussionThreadList::statement_getByXidAndStatus:
					DiscussionThreadList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD']
						.' USE INDEX('.$COLUMN['XID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'integer'));
					break;
				case DiscussionThreadList::statement_getByNullXidAndStatus:
					DiscussionThreadList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD']
						.' USE INDEX('.$COLUMN['XID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['XID'].' IS NULL AND '.$COLUMN['STATUS'].' = ?'
								, array('integer'));
					break;
				case DiscussionThreadList::statement_getByUidAndStatus:
					DiscussionThreadList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case DiscussionThreadList::statement_getByEid:
					DiscussionThreadList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD']
						.' USE INDEX('.$COLUMN['EID'].')'
						.' WHERE '.$COLUMN['EID'].' = ?'
								, array('integer'));
					break;
				case DiscussionThreadList::statement_getByXid:
					DiscussionThreadList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD']
						.' USE INDEX('.$COLUMN['XID'].')'
						.' WHERE '.$COLUMN['XID'].' = ?'
								, array('integer'));
					break;
				case DiscussionThreadList::statement_getByIndexingStatusAndStatus:
					DiscussionThreadList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD']
						.' USE INDEX('.$COLUMN['INDEXING_STATUS'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['INDEXING_STATUS'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'integer'));
					break;
			}
		}
	}
}

?>