<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of favorites
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class PremiumCodeListException extends Exception {}

class PremiumCodeList {
	private static $statement = array();
	
	const statement_getByTXNid = 1;
	const statement_getByEid = 3;
	
	const cache_prefix_txnid = 'PremiumCodeListByTXNid-';
	
	public static function deleteByTXNid($txnid) {
		try { Cache::delete(PremiumCodeList::cache_prefix_txnid.$txnid); } catch (CacheException $e) {}
	}
	
	public static function getByTXNid($txnid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(PremiumCodeList::cache_prefix_txnid.$txnid);
		} catch (CacheException $e) { 
			PremiumCodeList::prepareStatement(PremiumCodeList::statement_getByTXNid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PremiumCodeList::$statement[PremiumCodeList::statement_getByTXNid]->execute($txnid);
			Log::trace('DB', 'Executed PremiumCodeList::statement_getByTXNid ['.$txnid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['CODE']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(PremiumCodeList::cache_prefix_txnid.$txnid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PremiumCodeList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PremiumCodeList::statement_getByTXNid:
					PremiumCodeList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CODE']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PREMIUM_CODE']
						.' USE INDEX('.$COLUMN['TXNID'].')'
						.' WHERE '.$COLUMN['TXNID'].' = ?'
								, array('text'));
					break;
			}
		}
	}
}

?>