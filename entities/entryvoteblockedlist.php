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

class EntryVoteBlockedListException extends Exception {}

class EntryVoteBlockedList {
	private static $statement = array();
	
	const statement_getByVoterUid = 1;
	const statement_getByAuthorUid = 2;
	const statement_getAll = 3;
	
	const cache_prefix_voter_uid = 'EntryVoteListByVoterUid-';
	const cache_prefix_author_uid = 'EntryVoteListByAuthorUid-';
	
	public static function deleteByVoterUid($voter_uid) {
		try { Cache::delete(EntryVoteBlockedList::cache_prefix_voter_uid.$voter_uid); } catch (CacheException $e) {}
	}
	
	public static function getByVoterUid($voter_uid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryVoteBlockedList::cache_prefix_voter_uid.$voter_uid);
		} catch (CacheException $e) { 
			EntryVoteBlockedList::prepareStatement(EntryVoteBlockedList::statement_getByVoterUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryVoteBlockedList::$statement[EntryVoteBlockedList::statement_getByVoterUid]->execute($voter_uid);
			Log::trace('DB', 'Executed EntryVoteBlockedList::statement_getByVoterUid ['.$voter_uid.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['AUTHOR_UID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryVoteBlockedList::cache_prefix_voter_uid.$voter_uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByAuthorUid($author_uid) {
		try { Cache::delete(EntryVoteBlockedList::cache_prefix_author_uid.$author_uid); } catch (CacheException $e) {}
	}
	
	public static function getByAuthorUid($author_uid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryVoteBlockedList::cache_prefix_author_uid.$author_uid);
		} catch (CacheException $e) { 
			EntryVoteBlockedList::prepareStatement(EntryVoteBlockedList::statement_getByAuthorUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryVoteBlockedList::$statement[EntryVoteBlockedList::statement_getByAuthorUid]->execute($author_uid);
			Log::trace('DB', 'Executed EntryVoteBlockedList::statement_getByAuthorUid ['.$author_uid.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['VOTER_UID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryVoteBlockedList::cache_prefix_author_uid.$author_uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getAll() {
		global $COLUMN;
		
		EntryVoteBlockedList::prepareStatement(EntryVoteBlockedList::statement_getAll);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = EntryVoteBlockedList::$statement[EntryVoteBlockedList::statement_getAll]->execute();
		Log::trace('DB', 'Executed EntryVoteBlockedList::statement_getAll [] ('.(microtime(true) - $start_timestamp).')');
		
		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list[] = array('voter_uid' => $row[$COLUMN['VOTER_UID']], 'author_uid' => $row[$COLUMN['AUTHOR_UID']]);
			$result->free();
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(EntryVoteBlockedList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case EntryVoteBlockedList::statement_getByVoterUid:
					EntryVoteBlockedList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['AUTHOR_UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE_BLOCKED']
						.' USE INDEX('.$COLUMN['VOTER_UID'].')'
						.' WHERE '.$COLUMN['VOTER_UID'].' = ?'
								, array('text'));
					break;
				case EntryVoteBlockedList::statement_getByAuthorUid:
					EntryVoteBlockedList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['VOTER_UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE_BLOCKED']
						.' USE INDEX('.$COLUMN['VOTER_UID'].')'
						.' WHERE '.$COLUMN['AUTHOR_UID'].' = ?'
								, array('text'));
					break;
				case EntryVoteBlockedList::statement_getAll:
					EntryVoteBlockedList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['VOTER_UID']
						.', '.$COLUMN['AUTHOR_UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE_BLOCKED']
								, array());
					break;
			}
		}
	}
}

?>