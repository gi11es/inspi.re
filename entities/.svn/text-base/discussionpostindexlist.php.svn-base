<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2.php';

class DiscussionPostIndexListException extends Exception {}

class DiscussionPostIndexList {
	private static $statement = array();
	
	const statement_getByXidAndWord = 1;
	const statement_getByNullXidAndWord = 2;
	const statement_getByOid = 3;
	
	const cache_prefix_xid_and_word = 'DiscussionPostIndexListByXidAndWord-';
	const cache_prefix_oid = 'DiscussionPostIndexListByOid-';
	
	public static function deleteByXidAndWord($xid, $word) {
		try { Cache::delete(DiscussionPostIndexList::cache_prefix_xid_and_word.$xid.'-'.$word); } catch (CacheException $e) {}
	}
	
	public static function getByXidAndWord($xid, $word) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionPostIndexList::cache_prefix_xid_and_word.$xid.'-'.$word);
		} catch (CacheException $e) { 
			if ($xid !== null) {
				DiscussionPostIndexList::prepareStatement(DiscussionPostIndexList::statement_getByXidAndWord);
				
				$start_timestamp = microtime(true);
				DB::incrementRequestCount();
				$result = DiscussionPostIndexList::$statement[DiscussionPostIndexList::statement_getByXidAndWord]->execute(array($xid, $word));
				Log::trace('DB', 'Executed DiscussionPostIndexList::statement_getByXidAndWord ['.$xid.', '.$word.'] ('.(microtime(true) - $start_timestamp).')');
			} else {
				DiscussionPostIndexList::prepareStatement(DiscussionPostIndexList::statement_getByNullXidAndWord);
				
				$start_timestamp = microtime(true);
				DB::incrementRequestCount();
				$result = DiscussionPostIndexList::$statement[DiscussionPostIndexList::statement_getByNullXidAndWord]->execute($word);
				Log::trace('DB', 'Executed DiscussionPostIndexList::statement_getByNullXidAndWord ['.$word.'] ('.(microtime(true) - $start_timestamp).')');			
			}
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // OID => COUNT
				$result->free();
			}
			
			try {
				Cache::setorreplace(DiscussionPostIndexList::cache_prefix_xid_and_word.$xid.'-'.$word, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByOid($oid) {
		try { Cache::delete(DiscussionPostIndexList::cache_prefix_oid.$oid); } catch (CacheException $e) {}
	}
	
	public static function getByOid($oid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionPostIndexList::cache_prefix_oid.$oid);
		} catch (CacheException $e) { 
			DiscussionPostIndexList::prepareStatement(DiscussionPostIndexList::statement_getByOid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionPostIndexList::$statement[DiscussionPostIndexList::statement_getByOid]->execute($oid);
			Log::trace('DB', 'Executed DiscussionPostIndexList::statement_getByOid ['.$oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['WORD']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(DiscussionPostIndexList::cache_prefix_oid.$oid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(DiscussionPostIndexList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case DiscussionPostIndexList::statement_getByXidAndWord:
					DiscussionPostIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['OID'].', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST_INDEX']
						.' USE INDEX('.$COLUMN['XID'].'_and_'.$COLUMN['WORD'].')'
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['WORD'].' = ?'
								, array('integer', 'text'));
					break;
				case DiscussionPostIndexList::statement_getByNullXidAndWord:
					DiscussionPostIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['OID'].', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST_INDEX']
						.' USE INDEX('.$COLUMN['XID'].'_and_'.$COLUMN['WORD'].')'
						.' WHERE '.$COLUMN['XID'].' IS NULL AND '.$COLUMN['WORD'].' = ?'
								, array('text'));
					break;
				case DiscussionPostIndexList::statement_getByOid:
					DiscussionPostIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['WORD']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST_INDEX']
						.' USE INDEX('.$COLUMN['OID'].')'
						.' WHERE '.$COLUMN['OID'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>