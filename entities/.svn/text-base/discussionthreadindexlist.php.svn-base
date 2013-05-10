<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2.php';

class DiscussionThreadIndexListException extends Exception {}

class DiscussionThreadIndexList {
	private static $statement = array();
	
	const statement_getByXidAndWord = 1;
	const statement_getByNullXidAndWord = 2;
	const statement_getByNid = 3;
	
	const cache_prefix_xid_and_word = 'DiscussionThreadIndexListByXidAndWord-';
	const cache_prefix_nid = 'DiscussionThreadIndexListByNid-';
	
	public static function deleteByXidAndWord($xid, $word) {
		try { Cache::delete(DiscussionThreadIndexList::cache_prefix_xid_and_word.$xid.'-'.$word); } catch (CacheException $e) {}
	}
	
	public static function getByXidAndWord($xid, $word) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionThreadIndexList::cache_prefix_xid_and_word.$xid.'-'.$word);
		} catch (CacheException $e) { 
			if ($xid !== null) {
				DiscussionThreadIndexList::prepareStatement(DiscussionThreadIndexList::statement_getByXidAndWord);
				
				$start_timestamp = microtime(true);
				DB::incrementRequestCount();
				$result = DiscussionThreadIndexList::$statement[DiscussionThreadIndexList::statement_getByXidAndWord]->execute(array($xid, $word));
				Log::trace('DB', 'Executed DiscussionThreadIndexList::statement_getByXidAndWord ['.$xid.', '.$word.'] ('.(microtime(true) - $start_timestamp).')');
			} else {
				DiscussionThreadIndexList::prepareStatement(DiscussionThreadIndexList::statement_getByNullXidAndWord);
				
				$start_timestamp = microtime(true);
				DB::incrementRequestCount();
				$result = DiscussionThreadIndexList::$statement[DiscussionThreadIndexList::statement_getByNullXidAndWord]->execute($word);
				Log::trace('DB', 'Executed DiscussionThreadIndexList::statement_getByNullXidAndWord ['.$word.'] ('.(microtime(true) - $start_timestamp).')');			
			}
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // NID => COUNT
				$result->free();
			}
			
			try {
				Cache::setorreplace(DiscussionThreadIndexList::cache_prefix_xid_and_word.$xid.'-'.$word, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByNid($nid) {
		try { Cache::delete(DiscussionThreadIndexList::cache_prefix_nid.$nid); } catch (CacheException $e) {}
	}
	
	public static function getByNid($nid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(DiscussionThreadIndexList::cache_prefix_nid.$nid);
		} catch (CacheException $e) { 
			DiscussionThreadIndexList::prepareStatement(DiscussionThreadIndexList::statement_getByNid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionThreadIndexList::$statement[DiscussionThreadIndexList::statement_getByNid]->execute($nid);
			Log::trace('DB', 'Executed DiscussionThreadIndexList::statement_getByNid ['.$nid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['WORD']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(DiscussionThreadIndexList::cache_prefix_nid.$nid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(DiscussionThreadIndexList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case DiscussionThreadIndexList::statement_getByXidAndWord:
					DiscussionThreadIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NID'].', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD_INDEX']
						.' USE INDEX('.$COLUMN['XID'].'_and_'.$COLUMN['WORD'].')'
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['WORD'].' = ?'
								, array('integer', 'text'));
					break;
				case DiscussionThreadIndexList::statement_getByNullXidAndWord:
					DiscussionThreadIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NID'].', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD_INDEX']
						.' USE INDEX('.$COLUMN['XID'].'_and_'.$COLUMN['WORD'].')'
						.' WHERE '.$COLUMN['XID'].' IS NULL AND '.$COLUMN['WORD'].' = ?'
								, array('text'));
					break;
				case DiscussionThreadIndexList::statement_getByNid:
					DiscussionThreadIndexList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['WORD']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD_INDEX']
						.' USE INDEX('.$COLUMN['NID'].')'
						.' WHERE '.$COLUMN['NID'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>