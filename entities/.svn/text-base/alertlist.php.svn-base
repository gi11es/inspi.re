<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of alerts, lets us gather all alerts
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class AlertListException extends Exception {}

class AlertList {
	private static $statement = array();
	
	const statement_getAll = 1;
	
	const cache_prefix_all = 'AlertList-';
	
	public static function getAll() {
		global $COLUMN;
		
		AlertList::prepareStatement(AlertList::statement_getAll);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = AlertList::$statement[AlertList::statement_getAll]->execute();
		Log::trace('DB', 'Executed AlertList::statement_getAll [] ('.(microtime(true) - $start_timestamp).')');
		
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['AID']];
			$result->free();
		}
		
		return $list;
	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(AlertList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case AlertList::statement_getAll:
					AlertList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['AID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ALERT']
								, array());
					break;
			}
		}
	}
}

?>