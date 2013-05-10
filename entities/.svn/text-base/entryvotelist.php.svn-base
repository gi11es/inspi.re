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
require_once 'MDB2/Date.php';

class EntryVoteListException extends Exception {}

class EntryVoteList {
	private static $statement = array();
	
	const statement_getByEidAndStatus = 1;
	const statement_getByUidAndStatus = 2;
	const statement_getByEid = 3;
	const statement_getByStatus = 4;
	const statement_getByStatusCount = 5;
	const statement_getByUidAndCid = 6;
	const statement_getByAuthorUidAndStatus = 7;
	const statement_getByCreationTimeAndStatus = 8;
	const statement_getByEidAndStatusAndCreationTime = 9;
	
	const cache_prefix_eid_and_status = 'EntryVoteListByEidAndStatus-';
	const cache_prefix_uid_and_status = 'EntryVoteListByUidAndStatus-';
	const cache_prefix_eid = 'EntryVoteListByEid-';
	const cache_prefix_status_count = 'EntryVoteListByStatusCount-';
	const cache_prefix_uid_and_cid = 'EntryVoteListByUidAndCid-';
	const cache_prefix_author_uid_and_status = 'EntryVoteListByAuthorUidAndStatus-';
	
	public static function deleteByEidAndStatus($eid, $status) {
		try { Cache::delete(EntryVoteList::cache_prefix_eid_and_status.$eid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByEidAndStatus($eid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryVoteList::cache_prefix_eid_and_status.$eid.'-'.$status);
		} catch (CacheException $e) { 
			EntryVoteList::prepareStatement(EntryVoteList::statement_getByEidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryVoteList::$statement[EntryVoteList::statement_getByEidAndStatus]->execute(array($eid, $status));
			Log::trace('DB', 'Executed EntryVoteList::statement_getByEidAndStatus ['.$eid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => POINTS
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryVoteList::cache_prefix_eid_and_status.$eid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getByEidAndStatusAndCreationTime($eid, $status, $creation_time) {
		global $COLUMN;

		EntryVoteList::prepareStatement(EntryVoteList::statement_getByEidAndStatusAndCreationTime);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = EntryVoteList::$statement[EntryVoteList::statement_getByEidAndStatusAndCreationTime]->execute(array($eid, $status, MDB2_Date::unix2Mdbstamp($creation_time)));
		Log::trace('DB', 'Executed EntryVoteList::statement_getByEidAndStatusAndCreationTime ['.$eid.', '.$status.', '.$creation_time.'] ('.(microtime(true) - $start_timestamp).')');

		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => POINTS
			$result->free();
		}
		
		return $list;
	}
	
	public static function deleteByEid($eid) {
		try { Cache::delete(EntryVoteList::cache_prefix_eid.$eid); } catch (CacheException $e) {}
	}
	
	public static function getByEid($eid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryVoteList::cache_prefix_eid.$eid);
		} catch (CacheException $e) { 
			EntryVoteList::prepareStatement(EntryVoteList::statement_getByEid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryVoteList::$statement[EntryVoteList::statement_getByEid]->execute($eid);
			Log::trace('DB', 'Executed EntryVoteList::statement_getByEid ['.$eid.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => POINTS
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryVoteList::cache_prefix_eid.$eid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUidAndStatus($uid, $status) {
		try { Cache::delete(EntryVoteList::cache_prefix_uid_and_status.$uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndStatus($uid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryVoteList::cache_prefix_uid_and_status.$uid.'-'.$status);
		} catch (CacheException $e) { 
			EntryVoteList::prepareStatement(EntryVoteList::statement_getByUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryVoteList::$statement[EntryVoteList::statement_getByUidAndStatus]->execute(array($uid, $status));
			Log::trace('DB', 'Executed EntryVoteList::statement_getByUidAndStatus ['.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list [$row[$COLUMN['EID']]]= array('author_uid' => $row[$COLUMN['AUTHOR_UID']], 'points' => $row[$COLUMN['POINTS']], 'creation_time' => $row[$COLUMN['CREATION_TIME']]);
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryVoteList::cache_prefix_uid_and_status.$uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByAuthorUidAndStatus($uid, $status) {
		try { Cache::delete(EntryVoteList::cache_prefix_author_uid_and_status.$uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByAuthorUidAndStatus($uid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryVoteList::cache_prefix_author_uid_and_status.$uid.'-'.$status);
		} catch (CacheException $e) { 
			EntryVoteList::prepareStatement(EntryVoteList::statement_getByAuthorUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryVoteList::$statement[EntryVoteList::statement_getByAuthorUidAndStatus]->execute(array($uid, $status));
			Log::trace('DB', 'Executed EntryVoteList::statement_getByAuthorUidAndStatus ['.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) {
					if (!isset($list [$row[$COLUMN['EID']]]))
						$list[$row[$COLUMN['EID']]] = array($row[$COLUMN['POINTS']]);
					else
						$list[$row[$COLUMN['EID']]] []= $row[$COLUMN['POINTS']];
				}
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryVoteList::cache_prefix_author_uid_and_status.$uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUidAndCid($uid, $cid) {
		try { Cache::delete(EntryVoteList::cache_prefix_uid_and_cid.$uid.'-'.$cid); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndCid($uid, $cid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryVoteList::cache_prefix_uid_and_cid.$uid.'-'.$cid);
		} catch (CacheException $e) { 
			EntryVoteList::prepareStatement(EntryVoteList::statement_getByUidAndCid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryVoteList::$statement[EntryVoteList::statement_getByUidAndCid]->execute(array($uid, $cid));
			Log::trace('DB', 'Executed EntryVoteList::statement_getByUidAndCid ['.$uid.', '.$cid.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list [$row[$COLUMN['EID']]]= $row[$COLUMN['POINTS']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryVoteList::cache_prefix_uid_and_cid.$uid.'-'.$cid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function incrementStatusCount($status) {
		Cache::lock('EntryVoteListByStatusCount');
		try {
			Cache::increment(EntryVoteList::cache_prefix_status_count.$status);
		} catch (CacheException $e) {
			try { Cache::delete(EntryVoteList::cache_prefix_status_count.$status);
			} catch (CacheException $e) {}
		}
		Cache::unlock('EntryVoteListByStatusCount');
	}
	
	public static function decrementStatusCount($status) {
		Cache::lock('EntryVoteListByStatusCount');
		try {
			Cache::decrement(EntryVoteList::cache_prefix_status_count.$status);
		} catch (CacheException $e) {
			try { Cache::delete(EntryVoteList::cache_prefix_status_count.$status);
			} catch (CacheException $e) {}
		}
		Cache::unlock('EntryVoteListByStatusCount');
	}
	
	public static function getByStatusCount($status, $cache = true) {
		try {
			$count = Cache::get(EntryVoteList::cache_prefix_status_count.$status);
			return $count;
		} catch (CacheException $e) {
			EntryVoteList::prepareStatement(EntryVoteList::statement_getByStatusCount);
		
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryVoteList::$statement[EntryVoteList::statement_getByStatusCount]->execute($status);
			Log::trace('DB', 'Executed EntryVoteList::statement_getByStatusCount ['.$status.'] ('.(microtime(true) - $start_timestamp).')');
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$row = $result->fetchRow();
				$count = $row['count'];
			} else $count = 0;
			
			Cache::lock('EntryVoteListByStatusCount');
			try {
				Cache::setorreplace(EntryVoteList::cache_prefix_status_count.$status, $count);
			} catch (CacheException $e) {}
			Cache::unlock('EntryVoteListByStatusCount');

			return $count;
		}
	}
	
	public static function getByCreationTimeAndStatus($creation_time, $status, $cache = true) {
		global $COLUMN;
		
		EntryVoteList::prepareStatement(EntryVoteList::statement_getByCreationTimeAndStatus);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = EntryVoteList::$statement[EntryVoteList::statement_getByCreationTimeAndStatus]->execute(array(MDB2_Date::unix2Mdbstamp($creation_time), $status));
		Log::trace('DB', 'Executed EntryVoteList::statement_getByCreationTimeAndStatus ['.$creation_time.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) 
				if (!isset($list [$row[$COLUMN['EID']]]))
						$list[$row[$COLUMN['EID']]] = array($row[$COLUMN['POINTS']]);
					else
						$list[$row[$COLUMN['EID']]] []= $row[$COLUMN['POINTS']];
			$result->free();
		}
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(EntryVoteList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case EntryVoteList::statement_getByEidAndStatus:
					EntryVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.' USE INDEX('.$COLUMN['EID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['EID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'integer'));
					break;
				case EntryVoteList::statement_getByEidAndStatusAndCreationTime:
					EntryVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.' USE INDEX('.$COLUMN['EID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['EID'].' = ? AND '.$COLUMN['STATUS'].' = ? AND '.$COLUMN['CREATION_TIME'].' < ?'
								, array('integer', 'integer', 'timestamp'));
					break;
				case EntryVoteList::statement_getByUidAndStatus:
					EntryVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID'].', '.$COLUMN['POINTS'].', '.$COLUMN['AUTHOR_UID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case EntryVoteList::statement_getByEid:
					EntryVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.' USE INDEX('.$COLUMN['EID'].')'
						.' WHERE '.$COLUMN['EID'].' = ?'
								, array('integer'));
					break;
				case EntryVoteList::statement_getByStatusCount:
					EntryVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT COUNT(*) AS count'
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.' USE INDEX('.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['STATUS'].' = ?'
								, array('integer'));
					break;
				case EntryVoteList::statement_getByUidAndCid:
					EntryVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID'].', '.$COLUMN['POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['CID'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['CID'].' = ?'
								, array('text', 'integer'));
					break;
				case EntryVoteList::statement_getByAuthorUidAndStatus:
					EntryVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID'].', '.$COLUMN['POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.' USE INDEX('.$COLUMN['AUTHOR_UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['AUTHOR_UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case EntryVoteList::statement_getByCreationTimeAndStatus:
					EntryVoteList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID'].', '.$COLUMN['POINTS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE']
						.' USE INDEX('.$COLUMN['CREATION_TIME'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['CREATION_TIME'].' > ? AND '.$COLUMN['STATUS'].' = ?'
								, array('timestamp', 'integer'));
					break;
			}
		}
	}
}

?>