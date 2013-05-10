<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of alert instances, lets us gather all alerts for a specific user
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class AlertInstanceListException extends Exception {}

class AlertInstanceList {
	private static $statement = array();
	
	const statement_getByUid = 1;
	const statement_getByUidAndStatus = 2;
	const statement_getByAid = 3;
	const statement_getByStatus = 4;
	
	const cache_prefix_uid = 'AlertInstanceListByUid-';
	const cache_prefix_uid_and_status = 'AlertInstanceListByUidAndStatus-';
	const cache_prefix_aid = 'AlertInstanceListByAid-';
	
	public static function deleteByUid($uid) {
		try { Cache::delete(AlertInstanceList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(AlertInstanceList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			AlertInstanceList::prepareStatement(AlertInstanceList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = AlertInstanceList::$statement[AlertInstanceList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed AlertInstanceList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['AID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(AlertInstanceList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByUidAndStatus($uid, $status) {
		try { Cache::delete(AlertInstanceList::cache_prefix_uid_and_status.$uid.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByUidAndStatus($uid, $status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(AlertInstanceList::cache_prefix_uid_and_status.$uid.'-'.$status);
		} catch (CacheException $e) { 
			AlertInstanceList::prepareStatement(AlertInstanceList::statement_getByUidAndStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = AlertInstanceList::$statement[AlertInstanceList::statement_getByUidAndStatus]->execute(array($uid, $status));
			Log::trace('DB', 'Executed AlertInstanceList::statement_getByUidAndStatus ['.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['AID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(AlertInstanceList::cache_prefix_uid_and_status.$uid.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByAid($aid) {
		try { Cache::delete(AlertInstanceList::cache_prefix_aid.$aid); } catch (CacheException $e) {}
	}
	
	public static function getByAid($aid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(AlertInstanceList::cache_prefix_aid.$aid);
		} catch (CacheException $e) { 
			AlertInstanceList::prepareStatement(AlertInstanceList::statement_getByAid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = AlertInstanceList::$statement[AlertInstanceList::statement_getByAid]->execute($aid);
			Log::trace('DB', 'Executed AlertInstanceList::statement_getByAid ['.$aid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['UID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(AlertInstanceList::cache_prefix_aid.$aid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getByStatus($status) {
		global $COLUMN;

		AlertInstanceList::prepareStatement(AlertInstanceList::statement_getByStatus);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = AlertInstanceList::$statement[AlertInstanceList::statement_getByStatus]->execute($status);
		Log::trace('DB', 'Executed AlertInstanceList::statement_getByStatus ['.$status.'] ('.(microtime(true) - $start_timestamp).')');
		
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[] = array('aid' => $row[$COLUMN['AID']], 'uid' => $row[$COLUMN['UID']]);
			$result->free();
		}
		
		return $list;
	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(AlertInstanceList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case AlertInstanceList::statement_getByUid:
					AlertInstanceList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['AID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ALERT_INSTANCE']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case AlertInstanceList::statement_getByUidAndStatus:
					AlertInstanceList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['AID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ALERT_INSTANCE']
						.' USE INDEX('.$COLUMN['UID'].'_and_'.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['STATUS'].' = ?'
								, array('text', 'integer'));
					break;
				case AlertInstanceList::statement_getByAid:
					AlertInstanceList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ALERT_INSTANCE']
						.' USE INDEX('.$COLUMN['AID'].')'
						.' WHERE '.$COLUMN['AID'].' = ?'
								, array('integer'));
					break;
				case AlertInstanceList::statement_getByStatus:
					AlertInstanceList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['AID'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ALERT_INSTANCE']
						.' USE INDEX('.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['STATUS'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>