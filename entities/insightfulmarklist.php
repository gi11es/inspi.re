<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class InsightfulMarkListException extends Exception {}

class InsightfulMarkList {
	private static $statement = array();
	
	const statement_getByOid = 1;
	
	const cache_prefix_oid = 'InsightfulMarkListByOid-';
	
	public static function deleteByOid($oid) {
		try { Cache::delete(InsightfulMarkList::cache_prefix_oid.$oid); } catch (CacheException $e) {}
	}
	
	public static function getByOid($oid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(InsightfulMarkList::cache_prefix_oid.$oid);
		} catch (CacheException $e) { 
			InsightfulMarkList::prepareStatement(InsightfulMarkList::statement_getByOid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = InsightfulMarkList::$statement[InsightfulMarkList::statement_getByOid]->execute($oid);
			Log::trace('DB', 'Executed InsightfulMarkList::statement_getByOid ['.$oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['UID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(InsightfulMarkList::cache_prefix_oid.$oid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(InsightfulMarkList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case InsightfulMarkList::statement_getByOid:
					InsightfulMarkList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['INSIGHTFUL_MARK']
						.' USE INDEX('.$COLUMN['OID'].')'
						.' WHERE '.$COLUMN['OID'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>