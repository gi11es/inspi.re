<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of statistics
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class StatisticListException extends Exception {}

class StatisticList {
	private static $statement = array();
	
	const statement_getBySid = 1;
	
	const cache_prefix_sid = 'StatisticListBySid-';
	
	public static function deleteBySid($sid) {
		try { Cache::delete(StatisticList::cache_prefix_sid.$sid); } catch (CacheException $e) {}
	}
	
	public static function getBySid($sid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(StatisticList::cache_prefix_sid.$sid);
		} catch (CacheException $e) { 
			StatisticList::prepareStatement(StatisticList::statement_getBySid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = StatisticList::$statement[StatisticList::statement_getBySid]->execute($sid);
			Log::trace('DB', 'Executed StatisticList::statement_getBySid ['.$sid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // CREATION_TIME => VALUE
				$result->free();
			}
			
			try {
				Cache::setorreplace(StatisticList::cache_prefix_sid.$sid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(StatisticList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case StatisticList::statement_getBySid:
					StatisticList::$statement[$statement] = DB::prepareRead( 
						'SELECT UNIX_TIMESTAMP('.$COLUMN['TIMESTAMP'].') AS '.$COLUMN['TIMESTAMP']
						.', '.$COLUMN['VALUE']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['STATISTIC']
						.' USE INDEX('.$COLUMN['SID'].')'
						.' WHERE '.$COLUMN['SID'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>