<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2.php';

class PrivateMessageListException extends Exception {}

class PrivateMessageList {
	private static $statement = array();
	
	const statement_getBySourceUid = 1;
	const statement_getByDestinationUid = 2;
	const statement_getByDestinationUidAndStatus = 3;
	const statement_getDestinationUidBySourceUid = 4;
	const statement_getSourceUidByDestinationUid = 5;
	const statement_getBySourceUidAndOutboxStatus = 6;
	const statement_getAll = 7;
	
	const cache_prefix_source_uid = 'PrivateMessageListBySourceUid-';
	const cache_prefix_destination_uid = 'PrivateMessageListByDestinationUid-';
	const cache_prefix_destination_uid_and_status = 'PrivateMessageListByDestinationUidAndStatus-';
	const cache_prefix_source_uid_by_destination_uid = 'PrivateMessageListSourceUidByDestinationUid-';
	const cache_prefix_destination_uid_by_source_uid = 'PrivateMessageListDestinationUidBySourceUid-';
	const cache_prefix_source_uid_and_outbox_status = 'PrivateMessageListBySourceUidAndOutboxStatus-';
	
	public static function deleteDestinationUidBySourceUid($source_uid) {
		Cache::lock('PrivateMessageListDestinationUidBySourceUid'.$source_uid);
		try {
			Cache::delete(PrivateMessageList::cache_prefix_destination_uid_by_source_uid.$source_uid);
		} catch (CacheException $e) {}
		Cache::unlock('PrivateMessageListDestinationUidBySourceUid'.$source_uid);
	}
	
	public static function addDestinationUidBySourceUid($destination_uid, $source_uid) {
		if ($destination_uid === null || $source_uid === null) return;
		
		$list = PrivateMessageList::getDestinationUidBySourceUid($source_uid);
		
		if (!in_array($destination_uid, $list)) {
			Cache::lock('PrivateMessageListDestinationUidBySourceUid'.$source_uid);
			
			$list []= $destination_uid;
	
			try {
				Cache::replaceorset(PrivateMessageList::cache_prefix_destination_uid_by_source_uid.$source_uid, $list);
			} catch (CacheException $e) {}
			Cache::unlock('PrivateMessageListDestinationUidBySourceUid'.$source_uid);
		}
	}
	
	public static function getDestinationUidBySourceUid($source_uid) {
		global $COLUMN;
		
		Cache::lock('PrivateMessageListDestinationUidBySourceUid'.$source_uid);
		
		try {
			$list = Cache::get(PrivateMessageList::cache_prefix_destination_uid_by_source_uid.$source_uid);
		} catch (CacheException $e) {
			PrivateMessageList::prepareStatement(PrivateMessageList::statement_getDestinationUidBySourceUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PrivateMessageList::$statement[PrivateMessageList::statement_getDestinationUidBySourceUid]->execute($source_uid);
			Log::trace('DB', 'Executed UserList::statement_getDestinationUidBySourceUid ['.$source_uid.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['DESTINATION_UID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(PrivateMessageList::cache_prefix_destination_uid_by_source_uid.$source_uid, $list);
			} catch (CacheException $e) {}
		}
		
		Cache::unlock('PrivateMessageListDestinationUidBySourceUid'.$source_uid);
		return $list;
	}
	
	public static function deleteSourceUidByDestinationUid($destination_uid) {
		Cache::lock('PrivateMessageListSourceUidByDestinationUid'.$destination_uid);
		try {
			Cache::delete(PrivateMessageList::cache_prefix_source_uid_by_destination_uid.$destination_uid);
		} catch (CacheException $e) {}
		Cache::unlock('PrivateMessageListSourceUidByDestinationUid'.$destination_uid);
	}
	
	public static function addSourceUidByDestinationUid($source_uid, $destination_uid) {
		if ($destination_uid === null || $source_uid === null) return;
		
		$list = PrivateMessageList::getSourceUidByDestinationUid($destination_uid);
		
		if (!in_array($source_uid, $list)) {
			Cache::lock('PrivateMessageListSourceUidByDestinationUid'.$destination_uid);
			
			$list []= $source_uid;
	
			try {
				Cache::replaceorset(PrivateMessageList::cache_prefix_source_uid_by_destination_uid.$destination_uid, $list);
			} catch (CacheException $e) {}
			Cache::unlock('PrivateMessageListSourceUidByDestinationUid'.$destination_uid);
		}
	}
	
	public static function getSourceUidByDestinationUid($destination_uid) {
		global $COLUMN;
		
		Cache::lock('PrivateMessageListSourceUidByDestinationUid'.$destination_uid);
		
		try {
			$list = Cache::get(PrivateMessageList::cache_prefix_source_uid_by_destination_uid.$destination_uid);
		} catch (CacheException $e) {
			PrivateMessageList::prepareStatement(PrivateMessageList::statement_getSourceUidByDestinationUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PrivateMessageList::$statement[PrivateMessageList::statement_getSourceUidByDestinationUid]->execute($destination_uid);
			Log::trace('DB', 'Executed UserList::statement_getSourceUidByDestinationUid ['.$destination_uid.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['SOURCE_UID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(PrivateMessageList::cache_prefix_source_uid_by_destination_uid.$destination_uid, $list);
			} catch (CacheException $e) {}
		}
		
		Cache::unlock('PrivateMessageListSourceUidByDestinationUid'.$destination_uid);
		return $list;
	}
	
	public static function deleteBySourceUid($source_uid) {
		try { Cache::delete(PrivateMessageList::cache_prefix_source_uid.$source_uid); } catch (CacheException $e) {}
	}
	
	public static function getBySourceUid($source_uid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(PrivateMessageList::cache_prefix_source_uid.$source_uid);
		} catch (CacheException $e) { 
			PrivateMessageList::prepareStatement(PrivateMessageList::statement_getBySourceUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PrivateMessageList::$statement[PrivateMessageList::statement_getBySourceUid]->execute($source_uid);
			Log::trace('DB', 'Executed PrivateMessageList::statement_getBySourceUid ['.$source_uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // PMID => CREATION_TIME
				$result->free();
			}
			
			try {
				Cache::setorreplace(PrivateMessageList::cache_prefix_source_uid.$source_uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByDestinationUid($destination_uid) {
		try { Cache::delete(PrivateMessageList::cache_prefix_destination_uid.$destination_uid); } catch (CacheException $e) {}
	}
	
	public static function getByDestinationUid($destination_uid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(PrivateMessageList::cache_prefix_destination_uid.$destination_uid);
		} catch (CacheException $e) { 
			PrivateMessageList::prepareStatement(PrivateMessageList::statement_getByDestinationUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PrivateMessageList::$statement[PrivateMessageList::statement_getByDestinationUid]->execute($destination_uid);
			Log::trace('DB', 'Executed PrivateMessageList::statement_getByDestinationUid ['.$destination_uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // PMID => CREATION_TIME
				$result->free();
			}
			
			try {
				Cache::setorreplace(PrivateMessageList::cache_prefix_destination_uid.$destination_uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByDestinationUidAndStatus($destination_uid, $status) {
		try { Cache::delete(PrivateMessageList::cache_prefix_destination_uid_and_status.$destination_uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByDestinationUidAndStatus($destination_uid, $status) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(PrivateMessageList::cache_prefix_destination_uid_and_status.$destination_uid.'-'.$status);
		} catch (CacheException $e) { 
			PrivateMessageList::prepareStatement(PrivateMessageList::statement_getByDestinationUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PrivateMessageList::$statement[PrivateMessageList::statement_getByDestinationUidAndStatus]->execute(array($destination_uid, $status));
			Log::trace('DB', 'Executed PrivateMessageList::statement_getByDestinationUidAndStatus ['.$destination_uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // PMID => CREATION_TIME
				$result->free();
			}
			
			try {
				Cache::setorreplace(PrivateMessageList::cache_prefix_destination_uid_and_status.$destination_uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteBySourceUidAndOutboxStatus($source_uid, $outbox_status) {
		try { Cache::delete(PrivateMessageList::cache_prefix_source_uid_and_outbox_status.$source_uid.'-'.$outbox_status); } catch (CacheException $e) {}
	}
	
	public static function getBySourceUidAndOutboxStatus($source_uid, $outbox_status) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(PrivateMessageList::cache_prefix_source_uid_and_outbox_status.$source_uid.'-'.$outbox_status);
		} catch (CacheException $e) { 
			PrivateMessageList::prepareStatement(PrivateMessageList::statement_getBySourceUidAndOutboxStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PrivateMessageList::$statement[PrivateMessageList::statement_getBySourceUidAndOutboxStatus]->execute(array($source_uid, $outbox_status));
			Log::trace('DB', 'Executed PrivateMessageList::statement_getBySourceUidAndOutboxStatus ['.$source_uid.', '.$outbox_status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // PMID => CREATION_TIME
				$result->free();
			}
			
			try {
				Cache::setorreplace(PrivateMessageList::cache_prefix_source_uid_and_outbox_status.$source_uid.'-'.$outbox_status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getAll() {
		global $COLUMN;
		
		PrivateMessageList::prepareStatement(PrivateMessageList::statement_getAll);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = PrivateMessageList::$statement[PrivateMessageList::statement_getAll]->execute();
		Log::trace('DB', 'Executed PrivateMessageList::statement_getAll [] ('.(microtime(true) - $start_timestamp).')');
		
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['PMID']];
			$result->free();
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PrivateMessageList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PrivateMessageList::statement_getBySourceUid:
					PrivateMessageList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PMID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PRIVATE_MESSAGE']
						.' USE INDEX('.$COLUMN['SOURCE_UID'].')'
						.' WHERE '.$COLUMN['SOURCE_UID'].' = ?'
								, array('text'));
					break;
				case PrivateMessageList::statement_getByDestinationUid:
					PrivateMessageList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PMID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PRIVATE_MESSAGE']
						.' USE INDEX('.$COLUMN['DESTINATION_UID'].')'
						.' WHERE '.$COLUMN['DESTINATION_UID'].' = ?'
								, array('text'));
					break;
				case PrivateMessageList::statement_getByDestinationUidAndStatus:
					PrivateMessageList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PMID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PRIVATE_MESSAGE']
						.' USE INDEX('.$COLUMN['DESTINATION_UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['DESTINATION_UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case PrivateMessageList::statement_getDestinationUidBySourceUid:
					PrivateMessageList::$statement[$statement] = DB::prepareRead( 
						'SELECT DISTINCT '.$COLUMN['DESTINATION_UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PRIVATE_MESSAGE']
						.' USE INDEX('.$COLUMN['SOURCE_UID'].')'
						.' WHERE '.$COLUMN['SOURCE_UID'].' = ?'
								, array('text'));
					break;
				case PrivateMessageList::statement_getSourceUidByDestinationUid:
					PrivateMessageList::$statement[$statement] = DB::prepareRead( 
						'SELECT DISTINCT '.$COLUMN['SOURCE_UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PRIVATE_MESSAGE']
						.' USE INDEX('.$COLUMN['DESTINATION_UID'].')'
						.' WHERE '.$COLUMN['DESTINATION_UID'].' = ?'
								, array('text'));
					break;
				case PrivateMessageList::statement_getBySourceUidAndOutboxStatus:
					PrivateMessageList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PMID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PRIVATE_MESSAGE']
						.' USE INDEX('.$COLUMN['SOURCE_UID'].'_and_'.$COLUMN['OUTBOX_STATUS'].')'
						.' WHERE '.$COLUMN['SOURCE_UID'].' = ? AND '.$COLUMN['OUTBOX_STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case PrivateMessageList::statement_getAll:
					PrivateMessageList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PMID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PRIVATE_MESSAGE']
								, array());
					break;
			}
		}
	}
}

?>