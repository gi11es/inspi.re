<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2.php';

class CommentIndexListException extends Exception {}

class CommentIndexList {
	private static $statement = array();
	
	const statement_getByEidAndWord = 1;
	const statement_getByOid = 3;
	
	const cache_prefix_eid_and_word = 'CommentIndexListByEidAndWord-';
	const cache_prefix_oid = 'CommentIndexListByOid-';
	
	public static function deleteByEidAndWord($eid, $word) {
		try { Cache::delete(CommentIndexList::cache_prefix_eid_and_word.$eid.'-'.$word); } catch (CacheException $e) {}
	}
	
	public static function getByEidAndWord($eid, $word) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommentIndexList::cache_prefix_eid_and_word.$eid.'-'.$word);
		} catch (CacheException $e) { 
			CommentIndexList::prepareStatement(CommentIndexList::statement_getByEidAndWord);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommentIndexList::$statement[CommentIndexList::statement_getByEidAndWord]->execute(array($eid, $word));
			Log::trace('DB', 'Executed CommentIndexList::statement_getByEidAndWord ['.$eid.', '.$word.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // OID => COUNT
				$result->free();
			}
			
			try {
				Cache::setorreplace(CommentIndexList::cache_prefix_eid_and_word.$eid.'-'.$word, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByOid($oid) {
		try { Cache::delete(CommentIndexList::cache_prefix_oid.$oid); } catch (CacheException $e) {}
	}
	
	public static function getByOid($oid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(CommentIndexList::cache_prefix_oid.$oid);
		} catch (CacheException $e) { 
			CommentIndexList::prepareStatement(CommentIndexList::statement_getByOid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommentIndexList::$statement[CommentIndexList::statement_getByOid]->execute($oid);
			Log::trace('DB', 'Executed CommentIndexList::statement_getByOid ['.$oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['WORD']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(CommentIndexList::cache_prefix_oid.$oid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CommentIndexList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CommentIndexList::statement_getByEidAndWord:
					CommentIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['OID'].', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMENT_INDEX']
						.' USE INDEX('.$COLUMN['EID'].'_and_'.$COLUMN['WORD'].')'
						.' WHERE '.$COLUMN['EID'].' = ? AND '.$COLUMN['WORD'].' = ?'
								, array('integer', 'text'));
					break;
				case CommentIndexList::statement_getByOid:
					CommentIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['WORD']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMENT_INDEX']
						.' USE INDEX('.$COLUMN['OID'].')'
						.' WHERE '.$COLUMN['OID'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>