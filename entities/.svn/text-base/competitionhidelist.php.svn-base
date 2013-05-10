<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of competition hiding preferences
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class CompetitionHideListException extends Exception {}

class CompetitionHideList {
	private static $statement = array();
	
	const statement_getByUid = 1;
	const statement_getByCid = 3;
	const statement_getAll = 4;
	
	const cache_prefix_uid = 'CompetitionHideListByUid-';
	const cache_prefix_cid = 'CompetitionHideListByCid-';
	
	public static function deleteByUid($uid) {
		try { Cache::delete(CompetitionHideList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CompetitionHideList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			CompetitionHideList::prepareStatement(CompetitionHideList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CompetitionHideList::$statement[CompetitionHideList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed CompetitionHideList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['CID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(CompetitionHideList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByCid($cid) {
		try { Cache::delete(CompetitionHideList::cache_prefix_cid.$cid); } catch (CacheException $e) {}
	}
	
	public static function getByCid($cid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CompetitionHideList::cache_prefix_cid.$cid);
		} catch (CacheException $e) { 
			CompetitionHideList::prepareStatement(CompetitionHideList::statement_getByCid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CompetitionHideList::$statement[CompetitionHideList::statement_getByCid]->execute($cid);
			Log::trace('DB', 'Executed CompetitionHideList::statement_getByCid ['.$cid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['UID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(CompetitionHideList::cache_prefix_cid.$cid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getAll() {
		global $COLUMN;
		
		CompetitionHideList::prepareStatement(CompetitionHideList::statement_getAll);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = CompetitionHideList::$statement[CompetitionHideList::statement_getAll]->execute();
		Log::trace('DB', 'Executed CompetitionHideList::statement_getAll [] ('.(microtime(true) - $start_timestamp).')');
		
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[] = array('cid' => $row[$COLUMN['CID']], 'uid' => $row[$COLUMN['UID']]);
			$result->free();
		}
		
		return $list;
	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CompetitionHideList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CompetitionHideList::statement_getByUid:
					CompetitionHideList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMPETITION_HIDE']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case CompetitionHideList::statement_getByCid:
					CompetitionHideList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMPETITION_HIDE']
						.' USE INDEX('.$COLUMN['CID'].')'
						.' WHERE '.$COLUMN['CID'].' = ?'
								, array('integer'));
					break;
				case CompetitionHideList::statement_getAll:
					CompetitionHideList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CID'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMPETITION_HIDE']
								, array());
					break;
			}
		}
	}
}

?>