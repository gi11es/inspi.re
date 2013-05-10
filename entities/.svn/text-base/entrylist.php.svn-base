<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2/Date.php';
require_once 'MDB2.php';

class EntryStoredList {
	public $timestamp;
	public $list;
	
	public function __construct($list) {
		$this->timestamp = time();
		$this->list = $list;
	}
}

class EntryListException extends Exception {}

class EntryList {
	private static $statement = array();
	
	const statement_getByUidAndCidAndStatus = 1;
	const statement_getByCidAndStatus = 2;
	const statement_getByUidAndStatus = 3;
	const statement_getByCid = 4;
	const statement_getByCidAndRank = 5;
	const statement_getByUidAndRank = 6;
	const statement_getByStatus = 7;
	const statement_getCreated = 8;
	const statement_getByCreationTimeAndStatus = 9;
	const statement_getByUid = 10;
	
	const cache_prefix_uid_and_cid_and_status = 'EntryListByUidAndCidAndStatus-';
	const cache_prefix_cid_and_status = 'EntryListByCidAndStatus-';
	const cache_prefix_cid_and_status_randomized = 'EntryListByCidAndStatusRandomized-';
	const cache_prefix_cid_and_status_randomized_last_access = 'EntryListByCidAndStatusRandomizedLastAccess-';
	const cache_prefix_uid_and_status = 'EntryListByUidAndStatus-';
	const cache_prefix_cid = 'EntryListByCid-';
	const cache_prefix_cid_and_rank = 'EntryListByCidAndRank-';
	const cache_prefix_uid_and_rank = 'EntryListByUidAndRank-';
	const cache_prefix_status = 'EntryListByStatus-';
	const cache_prefix_cid_and_status_creation_time = 'EntryListByCidAndStatusCreationTime-';
	const cache_prefix_created_7_days = 'EntryListCreated7Days-';
	const cache_prefix_uid = 'EntryListByUid-';
	
	public static function deleteByUidAndCidAndStatus($uid, $cid, $status) {
		try { Cache::delete(EntryList::cache_prefix_uid_and_cid_and_status.$uid.'-'.$cid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndCidAndStatus($uid, $cid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryList::cache_prefix_uid_and_cid_and_status.$uid.'-'.$cid.'-'.$status);
		} catch (CacheException $e) { 
			EntryList::prepareStatement(EntryList::statement_getByUidAndCidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryList::$statement[EntryList::statement_getByUidAndCidAndStatus]->execute(array($uid, $cid, $status));
			Log::trace('DB', 'Executed EntryList::statement_getByUidAndCidAndStatus ['.$uid.', '.$cid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['EID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryList::cache_prefix_uid_and_cid_and_status.$uid.'-'.$cid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByCid($cid) {
		try { Cache::delete(EntryList::cache_prefix_cid.$cid); } catch (CacheException $e) {}
	}
	
	public static function getByCid($cid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryList::cache_prefix_cid.$cid);
		} catch (CacheException $e) { 
			EntryList::prepareStatement(EntryList::statement_getByCid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryList::$statement[EntryList::statement_getByCid]->execute($cid);
			Log::trace('DB', 'Executed EntryList::statement_getByCid ['.$cid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => EID
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryList::cache_prefix_cid.$cid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByCidAndStatus($cid, $status) {
		try { Cache::delete(EntryList::cache_prefix_cid_and_status.$cid.'-'.$status); } catch (CacheException $e) {}
		try { Cache::delete(EntryList::cache_prefix_cid_and_status_creation_time); } catch (CacheException $e) {}
	}
	
	public static function getByCidAndStatus($cid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryList::cache_prefix_cid_and_status.$cid.'-'.$status);
		} catch (CacheException $e) { 
			EntryList::prepareStatement(EntryList::statement_getByCidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryList::$statement[EntryList::statement_getByCidAndStatus]->execute(array($cid, $status));
			Log::trace('DB', 'Executed EntryList::statement_getByCidAndStatus ['.$cid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => EID
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryList::cache_prefix_cid_and_status.$cid.'-'.$status, $list);
			} catch (CacheException $e) {}
			
			if ($cache) try {
				Cache::setorreplace(EntryList::cache_prefix_cid_and_status_creation_time.$cid.'-'.$status, time());
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getCreationTimeByCidAndStatus($cid, $status, $cache = true) {
		try {
			$creation_time = Cache::get(EntryList::cache_prefix_cid_and_status_creation_time.$cid.'-'.$status);
			return $creation_time;
		} catch (CacheException $e) {
			return time();
		}
	}
	
	public static function deleteByUidAndStatus($uid, $status) {
		try { Cache::delete(EntryList::cache_prefix_uid_and_status.$uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndStatus($uid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryList::cache_prefix_uid_and_status.$uid.'-'.$status);
		} catch (CacheException $e) { 
			EntryList::prepareStatement(EntryList::statement_getByUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryList::$statement[EntryList::statement_getByUidAndStatus]->execute(array($uid, $status));
			Log::trace('DB', 'Executed EntryList::statement_getByUidAndStatus ['.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // CID => EID
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryList::cache_prefix_uid_and_status.$uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUid($uid) {
		try { Cache::delete(EntryList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			EntryList::prepareStatement(EntryList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryList::$statement[EntryList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed EntryList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // CID => EID
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getByCidAndStatusRandomized($uid, $cid, $status, $cache = true) {
		global $LAST_ENTRIES_ACCESS_MAXIMUM_AGE;
		global $COMPETITION_STATUS;
		
		$unshuffled_list = array_values(EntryList::getByCidAndStatus($cid, $status));
		$unshuffled_list_creation_time = EntryList::getCreationTimeByCidAndStatus($cid, $status);
		
		$reshuffle = true;
		$randomized = false;
		
		$last_access = 0;
		
		try {
			 $last_access = Cache::get(EntryList::cache_prefix_cid_and_status_randomized_last_access.$uid.'-'.$cid.'-'.$status);
			 if (time() - $last_access < $LAST_ENTRIES_ACCESS_MAXIMUM_AGE && $last_access >= $unshuffled_list_creation_time) {
			 	$reshuffle = false;
			 	Log::trace(__CLASS__, 'No need to reshuffle randomized list with uid='.$uid.' cid='.$cid.' and status='.$status.', it\'s recent enough');
			 }
		} catch (CacheException $e) {}
		
		try {
			Cache::setorreplace(EntryList::cache_prefix_cid_and_status_randomized_last_access.$uid.'-'.$cid.'-'.$status, time());
		} catch (CacheException $e) {}
		
		try {
			$list = Cache::get(EntryList::cache_prefix_cid_and_status_randomized.$uid.'-'.$cid.'-'.$status);
			$randomized = true;
		} catch (CacheException $e) {
			$list = $unshuffled_list;
			$reshuffle = true;
			if ($last_access) Log::debug(__CLASS__, 'Randomized list with uid='.$uid.' cid='.$cid.' and status='.$status.' couldn\'t be found in the cache, (last access: '.$last_access.') reshuffling needed');
		}
		
		if ($randomized && count($unshuffled_list) != count(array_intersect($list, $unshuffled_list)) || count($list) != count(array_intersect($unshuffled_list, $list))) {
			$newlist = array();
			
			foreach ($list as $eid) if (in_array($eid, $unshuffled_list)) $newlist []= $eid;
			
			foreach ($unshuffled_list as $eid) if (!in_array($eid, $newlist)) $newlist []= $eid;
			
			$list = $newlist;
			
			$reshuffle = false;
			
			Log::debug(__CLASS__, 'Size of randomized list with uid='.$uid.' cid='.$cid.' and status='.$status.' didn\'t match the current list of entries');
		} 
		
		if ($reshuffle) {
			shuffle($list);
			try {
				Cache::setorreplace(EntryList::cache_prefix_cid_and_status_randomized.$uid.'-'.$cid.'-'.$status, $list);
			} catch (CacheException $e) {}
			Log::trace(__CLASS__, 'Randomized list with uid='.$uid.' cid='.$cid.' and status='.$status.' was reshuffled');
		}
		
		return $list;
	}
	
	public static function deleteByCidAndRank($cid, $rank) {
		try { Cache::delete(EntryList::cache_prefix_cid_and_rank.$cid.'-'.$rank); } catch (CacheException $e) {}
	}
	
	public static function getByCidAndRank($cid, $rank, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryList::cache_prefix_cid_and_rank.$cid.'-'.$rank);
		} catch (CacheException $e) { 
			EntryList::prepareStatement(EntryList::statement_getByCidAndRank);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryList::$statement[EntryList::statement_getByCidAndRank]->execute(array($cid, $rank));
			Log::trace('DB', 'Executed EntryList::statement_getByCidAndRank ['.$cid.', '.$rank.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => EID
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryList::cache_prefix_cid_and_rank.$cid.'-'.$rank, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUidAndRank($uid, $rank) {
		try { Cache::delete(EntryList::cache_prefix_uid_and_rank.$uid.'-'.$rank); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndRank($uid, $rank, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryList::cache_prefix_uid_and_rank.$uid.'-'.$rank);
		} catch (CacheException $e) { 
			EntryList::prepareStatement(EntryList::statement_getByUidAndRank);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryList::$statement[EntryList::statement_getByUidAndRank]->execute(array($uid, $rank));
			Log::trace('DB', 'Executed EntryList::statement_getByUidAndRank ['.$uid.', '.$rank.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // CID => EID
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryList::cache_prefix_uid_and_rank.$uid.'-'.$rank, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByStatus($status) {
		try { Cache::delete(EntryList::cache_prefix_status.$status); } catch (CacheException $e) {}
	}
	
	public static function getByStatus($status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryList::cache_prefix_status.$status);
		} catch (CacheException $e) { 
			EntryList::prepareStatement(EntryList::statement_getByStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryList::$statement[EntryList::statement_getByStatus]->execute($status);
			Log::trace('DB', 'Executed EntryList::statement_getByStatus ['.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['EID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryList::cache_prefix_status.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function addCreated($eid, $timestamp) {
		if ($eid === null) return;
		
		$list = EntryList::getCreated7Days();
		Cache::lock('EntryListCreated7Days');
		$list[$eid] = $timestamp;

		foreach ($list as $eid => $timestamp) {
			if ($timestamp < (time() - 604800))
				unset($list[$eid]);
		}

		try {
			Cache::setorreplace(EntryList::cache_prefix_created_7_days, $list);
		} catch (CacheException $e) {}
		Cache::unlock('EntryListCreated7Days');
	}
	
	public static function getCreated7Days($cache = true) {
		global $COLUMN;
		
		Cache::lock('EntryListCreated7Days');
		
		try {
			$list = Cache::get(EntryList::cache_prefix_created_7_days);
			asort($list);
			
			$unset = false;
			foreach ($list as $uid => $timestamp) {
				if ($timestamp < (time() - 604800)) {
					unset($list[$uid]);
					$unset = true;
				}
			}
			
			if ($unset) try {
				Cache::setorreplace(EntryList::cache_prefix_created_7_days, $list);
			} catch (CacheException $e) {}
		} catch (CacheException $e) {
			EntryList::prepareStatement(EntryList::statement_getCreated);
			
			$timestamp = time() - 604800;
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryList::$statement[EntryList::statement_getCreated]->execute(MDB2_Date::unix2Mdbstamp($timestamp));
			Log::trace('DB', 'Executed UserList::statement_getCreated ['.$timestamp.'] ('.(microtime(true) - $start_timestamp).')');
		
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[$row[$COLUMN['EID']]]= $row[$COLUMN['CREATION_TIME']];
				$result->free();
			}

			try {
				Cache::setorreplace(EntryList::cache_prefix_created_7_days, $list);
			} catch (CacheException $e) {}
		}
		Cache::unlock('EntryListCreated7Days');
		return $list;
	}
	
	public static function getByCreationTimeAndStatus($creation_time, $status, $cache = true) {
		global $COLUMN;
		
		EntryList::prepareStatement(EntryList::statement_getByCreationTimeAndStatus);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = EntryList::$statement[EntryList::statement_getByCreationTimeAndStatus]->execute(array(MDB2_Date::unix2Mdbstamp($creation_time), $status));
		Log::trace('DB', 'Executed EntryList::statement_getByCreationTimeAndStatus ['.$creation_time.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[]= $row[$COLUMN['EID']];
			$result->free();
		}
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(EntryList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case EntryList::statement_getByUidAndCidAndStatus:
					EntryList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['CID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['CID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer', 'integer'));
					break;
				case EntryList::statement_getByCidAndStatus:
					EntryList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['EID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' USE INDEX('.$COLUMN['CID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['CID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'integer'));
					break;
				case EntryList::statement_getByUidAndStatus:
					EntryList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CID'].', '.$COLUMN['EID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case EntryList::statement_getByCid:
					EntryList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['EID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' USE INDEX('.$COLUMN['CID'].')'
						.' WHERE '.$COLUMN['CID'].' = ?'
								, array('integer'));
					break;
				case EntryList::statement_getByCidAndRank:
					EntryList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['EID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' USE INDEX('.$COLUMN['CID'].'_and_'.$COLUMN['RANK'].')'
						.' WHERE '.$COLUMN['CID'].' = ? AND '.$COLUMN['RANK'].' = ?'
								, array('integer', 'integer'));
					break;
				case EntryList::statement_getByUidAndRank:
					EntryList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CID'].', '.$COLUMN['EID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['RANK'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['RANK'].' = ?'
								, array('text', 'integer'));
					break;
				case EntryList::statement_getByStatus:
					EntryList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' USE INDEX('.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['STATUS'].' = ?'
								, array('integer'));
					break;
				case EntryList::statement_getCreated:
					EntryList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' WHERE '.$COLUMN['CREATION_TIME'].' >= ?'
								, array('timestamp'));
					break;
				case EntryList::statement_getByCreationTimeAndStatus:
					EntryList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' USE INDEX('.$COLUMN['CREATION_TIME'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['CREATION_TIME'].' > ? AND '.$COLUMN['STATUS'].' = ?'
								, array('timestamp', 'integer'));
					break;
				case EntryList::statement_getByUid:
					EntryList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CID'].', '.$COLUMN['EID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
			}
		}
	}
}

?>