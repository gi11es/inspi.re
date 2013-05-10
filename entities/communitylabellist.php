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

class CommunityLabelListException extends Exception {}

class CommunityLabelList {
	private static $statement = array();
	
	const statement_getByCLid = 1;
	const statement_getByXid = 2;
	
	const cache_prefix_clid = 'CommunityLabelListByCLid-';
	const cache_prefix_xid = 'CommunityLabelListByXid-';
	
	public static function deleteByXid($xid) {
		try { Cache::delete(CommunityLabelList::cache_prefix_xid.$xid); } catch (CacheException $e) {}
	}
	
	public static function getByXid($xid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommunityLabelList::cache_prefix_xid.$xid);
		} catch (CacheException $e) { 
			CommunityLabelList::prepareStatement(CommunityLabelList::statement_getByXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityLabelList::$statement[CommunityLabelList::statement_getByXid]->execute($xid);
			Log::trace('DB', 'Executed CommunityLabelList::statement_getByXid ['.$xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['CLID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(CommunityLabelList::cache_prefix_xid.$xid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByCLid($clid) {
		try { Cache::delete(CommunityLabelList::cache_prefix_clid.$clid); } catch (CacheException $e) {}
	}
	
	public static function getByCLid($clid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommunityLabelList::cache_prefix_clid.$clid);
		} catch (CacheException $e) { 
			CommunityLabelList::prepareStatement(CommunityLabelList::statement_getByCLid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityLabelList::$statement[CommunityLabelList::statement_getByCLid]->execute($clid);
			Log::trace('DB', 'Executed CommunityLabelList::statement_getByCLid ['.$clid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['XID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(CommunityLabelList::cache_prefix_clid.$clid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CommunityLabelList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CommunityLabelList::statement_getByXid:
					CommunityLabelList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CLID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_LABEL']
						.' USE INDEX('.$COLUMN['XID'].')'
						.' WHERE '.$COLUMN['XID'].' = ?'
								, array('integer'));
					break;
				case CommunityLabelList::statement_getByCLid:
					CommunityLabelList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['XID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_LABEL']
						.' USE INDEX('.$COLUMN['CLID'].')'
						.' WHERE '.$COLUMN['CLID'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>