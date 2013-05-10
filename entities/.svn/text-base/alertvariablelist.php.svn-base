<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of variables associated with a specific alert
 	These variables let us populate the alert's template with actual values
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class AlertVariableListException extends Exception {}

class AlertVariableList {
	private static $statement = array();
	
	const statement_getByAid = 1;
	const statement_getByName = 2;
	
	const cache_prefix_aid = 'AlertVariableListByAid-';
	const cache_prefix_name = 'AlertVariableListByName-';
	
	public static function deleteByAid($aid) {
		try { Cache::delete(AlertVariableList::cache_prefix_aid.$aid); } catch (CacheException $e) {}
	}
	
	public static function getByAid($aid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(AlertVariableList::cache_prefix_aid.$aid);
		} catch (CacheException $e) { 
			AlertVariableList::prepareStatement(AlertVariableList::statement_getByAid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = AlertVariableList::$statement[AlertVariableList::statement_getByAid]->execute($aid);
			Log::trace('DB', 'Executed AlertVariableList::statement_getByAid ['.$aid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['NAME']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(AlertVariableList::cache_prefix_aid.$aid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByName($name) {
		try { Cache::delete(AlertVariableList::cache_prefix_name.$name); } catch (CacheException $e) {}
	}
	
	public static function getByName($name) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(AlertVariableList::cache_prefix_name.$name);
		} catch (CacheException $e) { 
			AlertVariableList::prepareStatement(AlertVariableList::statement_getByName);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = AlertVariableList::$statement[AlertVariableList::statement_getByName]->execute($name);
			Log::trace('DB', 'Executed AlertVariableList::statement_getByName ['.$name.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['AID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(AlertVariableList::cache_prefix_name.$name, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(AlertVariableList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case AlertVariableList::statement_getByAid:
					AlertVariableList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NAME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ALERT_VARIABLE']
						.' USE INDEX('.$COLUMN['AID'].')'
						.' WHERE '.$COLUMN['AID'].' = ?'
								, array('integer'));
					break;
				case AlertVariableList::statement_getByName:
					AlertVariableList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['AID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ALERT_VARIABLE']
						.' USE INDEX('.$COLUMN['NAME'].')'
						.' WHERE '.$COLUMN['NAME'].' = ?'
								, array('text'));
					break;
			}
		}
	}
}

?>