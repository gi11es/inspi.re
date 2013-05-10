<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2.php';
require_once 'MDB2/Date.php';

class DiscussionPostListException extends Exception {}

class DiscussionPostList {
	private static $statement = array();
	
	const statement_getByNidAndStatus = 1;
	const statement_getByUidAndStatus = 2;
	const statement_getByNid = 3;
	const statement_getByReplyToOid = 4;
	const statement_getByIndexingStatusAndStatus = 5;
	const statement_getByCreationTimeAndStatus = 6;
	
	const cache_prefix_nid_and_status = 'DiscussionPostListByNidAndStatus-';
	const cache_prefix_uid_and_status = 'DiscussionPostListByUidAndStatus-';
	const cache_prefix_nid = 'DiscussionPostListByNid-';
	const cache_prefix_reply_to_oid = 'DiscussionPostListByReplyToOid-';
	const cache_prefix_indexing_status_and_status = 'DiscussionPostListByIndexingStatusAndStatus-';
	
	public static function deleteByNidAndStatus($nid, $status) {
		try { Cache::delete(DiscussionPostList::cache_prefix_nid_and_status.$nid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByNidAndStatus($nid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionPostList::cache_prefix_nid_and_status.$nid.'-'.$status);
		} catch (CacheException $e) { 
			DiscussionPostList::prepareStatement(DiscussionPostList::statement_getByNidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionPostList::$statement[DiscussionPostList::statement_getByNidAndStatus]->execute(array($nid, $status));
			Log::trace('DB', 'Executed DiscussionPostList::statement_getByNidAndStatus ['.$nid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // OID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(DiscussionPostList::cache_prefix_nid_and_status.$nid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByNid($nid) {
		try { Cache::delete(DiscussionPostList::cache_prefix_nid.$nid); } catch (CacheException $e) {}
	}
	
	public static function getByNid($nid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionPostList::cache_prefix_nid.$nid);
		} catch (CacheException $e) { 
			DiscussionPostList::prepareStatement(DiscussionPostList::statement_getByNid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionPostList::$statement[DiscussionPostList::statement_getByNid]->execute($nid);
			Log::trace('DB', 'Executed DiscussionPostList::statement_getByNid ['.$nid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // OID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(DiscussionPostList::cache_prefix_nid.$nid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByReplyToOid($reply_to_oid) {
		try { Cache::delete(DiscussionPostList::cache_prefix_reply_to_oid.$reply_to_oid); } catch (CacheException $e) {}
	}
	
	public static function getByReplyToOid($reply_to_oid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionPostList::cache_prefix_reply_to_oid.$reply_to_oid);
		} catch (CacheException $e) { 
			DiscussionPostList::prepareStatement(DiscussionPostList::statement_getByReplyToOid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionPostList::$statement[DiscussionPostList::statement_getByReplyToOid]->execute($reply_to_oid);
			Log::trace('DB', 'Executed DiscussionPostList::statement_getByReplyToOid ['.$reply_to_oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // OID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(DiscussionPostList::cache_prefix_reply_to_oid.$reply_to_oid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUidAndStatus($uid, $status) {
		try { Cache::delete(DiscussionPostList::cache_prefix_uid_and_status.$uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndStatus($uid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionPostList::cache_prefix_uid_and_status.$uid.'-'.$status);
		} catch (CacheException $e) { 
			DiscussionPostList::prepareStatement(DiscussionPostList::statement_getByUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionPostList::$statement[DiscussionPostList::statement_getByUidAndStatus]->execute(array($uid, $status));
			Log::trace('DB', 'Executed DiscussionPostList::statement_getByUidAndStatus ['.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // OID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(DiscussionPostList::cache_prefix_uid_and_status.$uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByIndexingStatusAndStatus($indexing_status, $status) {
		try { Cache::delete(DiscussionPostList::cache_prefix_indexing_status_and_status.$indexing_status.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByIndexingStatusAndStatus($indexing_status, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionPostList::cache_prefix_indexing_status_and_status.$indexing_status.'-'.$status);
		} catch (CacheException $e) { 
			DiscussionPostList::prepareStatement(DiscussionPostList::statement_getByIndexingStatusAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionPostList::$statement[DiscussionPostList::statement_getByIndexingStatusAndStatus]->execute(array($indexing_status, $status));
			Log::trace('DB', 'Executed DiscussionPostList::statement_getByIndexingStatusAndStatus ['.$indexing_status.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // OID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(DiscussionPostList::cache_prefix_indexing_status_and_status.$indexing_status.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getByCreationTimeAndStatus($creation_time, $status, $cache = true) {
		DiscussionPostList::prepareStatement(DiscussionPostList::statement_getByCreationTimeAndStatus);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = DiscussionPostList::$statement[DiscussionPostList::statement_getByCreationTimeAndStatus]->execute(array(MDB2_Date::unix2Mdbstamp($creation_time), $status));
		Log::trace('DB', 'Executed DiscussionPostList::statement_getByCreationTimeAndStatus ['.$creation_time.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // OID => CREATION_TIME
			$result->free();
		}
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(DiscussionPostList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case DiscussionPostList::statement_getByNidAndStatus:
					DiscussionPostList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['OID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST']
						.' USE INDEX('.$COLUMN['NID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['NID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'integer'));
					break;
				case DiscussionPostList::statement_getByUidAndStatus:
					DiscussionPostList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['OID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case DiscussionPostList::statement_getByNid:
					DiscussionPostList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['OID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST']
						.' USE INDEX('.$COLUMN['NID'].')'
						.' WHERE '.$COLUMN['NID'].' = ?'
								, array('integer'));
					break;
				case DiscussionPostList::statement_getByReplyToOid:
					DiscussionPostList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['OID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST']
						.' USE INDEX('.$COLUMN['REPLY_TO_OID'].')'
						.' WHERE '.$COLUMN['REPLY_TO_OID'].' = ?'
								, array('integer'));
					break;
				case DiscussionPostList::statement_getByIndexingStatusAndStatus:
					DiscussionPostList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['OID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST']
						.' USE INDEX('.$COLUMN['INDEXING_STATUS'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['INDEXING_STATUS'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'integer'));
					break;
				case DiscussionPostList::statement_getByCreationTimeAndStatus:
					DiscussionPostList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['OID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST']
						.' USE INDEX('.$COLUMN['CREATION_TIME'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['CREATION_TIME'].' > ? AND '.$COLUMN['STATUS'].' = ?'
								, array('timestamp', 'integer'));
					break;
			}
		}
	}
}

?>