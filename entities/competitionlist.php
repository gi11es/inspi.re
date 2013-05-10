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

class CompetitionListException extends Exception {}

class CompetitionList {
	private static $statement = array();
	
	const statement_getByXidAndStatus = 1;
	const statement_getByXid = 2;
	const statement_getByStatus = 3;
	
	const cache_prefix_xid_and_status = 'CompetitionListByXidAndStatus-';
	const cache_prefix_xid = 'CompetitionListByXid-';
	const cache_prefix_status = 'CompetitionListByStatus-';
	
	public static function deleteByXidAndStatus($xid, $status) {
		try { Cache::delete(CompetitionList::cache_prefix_xid_and_status.$xid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByXidAndStatus($xid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CompetitionList::cache_prefix_xid_and_status.$xid.'-'.$status);
		} catch (CacheException $e) { 
			CompetitionList::prepareStatement(CompetitionList::statement_getByXidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CompetitionList::$statement[CompetitionList::statement_getByXidAndStatus]->execute(array($xid, $status));
			Log::trace('DB', 'Executed CompetitionList::statement_getByXidAndStatus ['.$xid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // CID => START_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(CompetitionList::cache_prefix_xid_and_status.$xid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByXid($xid) {
		try { Cache::delete(CompetitionList::cache_prefix_xid.$xid); } catch (CacheException $e) {}
	}
	
	public static function getByXid($xid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CompetitionList::cache_prefix_xid.$xid);
		} catch (CacheException $e) { 
			CompetitionList::prepareStatement(CompetitionList::statement_getByXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CompetitionList::$statement[CompetitionList::statement_getByXid]->execute($xid);
			Log::trace('DB', 'Executed CompetitionList::statement_getByXid ['.$xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // CID => START_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(CompetitionList::cache_prefix_xid.$xid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByStatus($status) {
		try { Cache::delete(CompetitionList::cache_prefix_status.$status); } catch (CacheException $e) {}
	}
	
	public static function getByStatus($status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CompetitionList::cache_prefix_status.$status);
		} catch (CacheException $e) { 
			CompetitionList::prepareStatement(CompetitionList::statement_getByStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CompetitionList::$statement[CompetitionList::statement_getByStatus]->execute($status);
			Log::trace('DB', 'Executed CompetitionList::statement_getByStatus ['.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // CID => START_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(CompetitionList::cache_prefix_status.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CompetitionList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CompetitionList::statement_getByXidAndStatus:
					CompetitionList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CID'].', UNIX_TIMESTAMP('.$COLUMN['START_TIME'].') AS '.$COLUMN['START_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMPETITION']
						.' USE INDEX('.$COLUMN['XID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('integer', 'integer'));
					break;
				case CompetitionList::statement_getByXid:
					CompetitionList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CID'].', UNIX_TIMESTAMP('.$COLUMN['START_TIME'].') AS '.$COLUMN['START_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMPETITION']
						.' USE INDEX('.$COLUMN['XID'].')'
						.' WHERE '.$COLUMN['XID'].' = ?'
								, array('integer'));
					break;
				case CompetitionList::statement_getByStatus:
					CompetitionList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CID'].', UNIX_TIMESTAMP('.$COLUMN['START_TIME'].') AS '.$COLUMN['START_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMPETITION']
						.' USE INDEX('.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['STATUS'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>