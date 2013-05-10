<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of prize winners
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class PrizeWinnerListException extends Exception {}

class PrizeWinnerList {
	private static $statement = array();
	
	const statement_getAll = 4;
	
	public static function getAll() {
		global $COLUMN;
		
		PrizeWinnerList::prepareStatement(PrizeWinnerList::statement_getAll);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = PrizeWinnerList::$statement[PrizeWinnerList::statement_getAll]->execute();
		Log::trace('DB', 'Executed PrizeWinnerList::statement_getAll [] ('.(microtime(true) - $start_timestamp).')');
		
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['EID']];
			$result->free();
		}
		
		return $list;
	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PrizeWinnerList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PrizeWinnerList::statement_getAll:
					PrizeWinnerList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PRIZE_WINNER']
								, array());
					break;
			}
		}
	}
}

?>