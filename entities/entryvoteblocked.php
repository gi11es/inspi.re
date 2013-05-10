<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../entities/entryvoteblockedlist.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class EntryVoteBlockedException extends Exception {}

class EntryVoteBlocked implements Persistent {
	private $voter_uid;
	private $author_uid;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'EntryVoteBlocked-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of entry_vote_blocked with voter_uid='.$this->voter_uid.' and author_uid='.$this->author_uid);
		
		try {
			Cache::replaceorset(EntryVoteBlocked::cache_prefix.$this->voter_uid.'-'.$this->author_uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of entry_vote_blocked with voter_uid='.$this->voter_uid.' and author_uid='.$this->author_uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($voter_uid, $author_uid) {
		EntryVoteBlocked::prepareStatement(EntryVoteBlocked::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		EntryVoteBlocked::$statement[EntryVoteBlocked::statement_create]->execute(array($voter_uid, $author_uid));
		Log::trace('DB', 'Executed EntryVoteBlocked::statement_create ['.$voter_uid.', '.$author_uid.'] ('.(microtime(true) - $start_timestamp).')');

		$this->setVoterUid($voter_uid);
		$this->setAuthorUid($author_uid);
		$this->saveCache();
		
		EntryVoteBlockedList::deleteByVoterUid($voter_uid);
		EntryVoteBlockedList::deletebyAuthorUid($author_uid);
	}
	
	public static function get($voter_uid, $author_uid, $cache = true) {
		if ($voter_uid === null || $author_uid === null) throw new EntryVoteBlockedException('No entry vote for voter_uid='.$voter_uid.' and author_uid='.$author_uid);
		
		try {
			$entryvoteblocked = Cache::get(EntryVoteBlocked::cache_prefix.$voter_uid.'-'.$author_uid);
		} catch (CacheException $e) {
			EntryVoteBlocked::prepareStatement(EntryVoteBlocked::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryVoteBlocked::$statement[EntryVoteBlocked::statement_get]->execute(array($voter_uid, $author_uid));
			Log::trace('DB', 'Executed EntryVoteBlocked::statement_get ['.$voter_uid.', '.$author_uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new EntryVoteException('No entry vote for voter_uid='.$voter_uid.' and author_uid='.$author_uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$entryvoteblocked = new EntryVoteBlocked();
			$entryvoteblocked->populateFields($row);
			if ($cache) $entryvoteblocked->saveCache();
		}
		
		return $entryvoteblocked;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setVoterUid($row[$COLUMN['VOTER_UID']]);
		$this->setAuthorUid($row[$COLUMN['AUTHOR_UID']]);
	}
	
	public function delete() {
		EntryVoteBlocked::prepareStatement(EntryVoteBlocked::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		EntryVoteBlocked::$statement[EntryVoteBlocked::statement_delete]->execute(array($this->voter_uid, $this->author_uid));
		Log::trace('DB', 'Executed EntryVoteBlocked::statement_delete ['.$this->voter_uid.', '.$this->author_uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(EntryVoteBlocked::cache_prefix.$this->voter_uid.'-'.$this->author_uid); } catch (CacheException $e) {}
		
		EntryVoteBlockedList::deleteByVoterUid($this->voter_uid);
		EntryVoteBlockedList::deleteByAuthorUid($this->author_uid);
	}
	
	public function getVoterUid() { return $this->voter_uid; }
	
	public function setVoterUid($new_voter_uid) { $this->voter_uid = $new_voter_uid; }
	
	public function getAuthorUid() { return $this->author_uid; }
	
	public function setAuthorUid($new_author_uid) { $this->author_uid = $new_author_uid; }
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(EntryVoteBlocked::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case EntryVoteBlocked::statement_get:
					EntryVoteBlocked::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['VOTER_UID']
						.', '.$COLUMN['AUTHOR_UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE_BLOCKED']
						.' WHERE '.$COLUMN['VOTER_UID'].' = ? AND '.$COLUMN['AUTHOR_UID'].' = ?'
								, array('text', 'text'));
					break;
				case EntryVoteBlocked::statement_create:
					EntryVoteBlocked::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE_BLOCKED']
						.'( '.$COLUMN['VOTER_UID'].', '.$COLUMN['AUTHOR_UID']
						.') VALUES(?, ?)', array('text', 'text'));
					break;	
				case EntryVoteBlocked::statement_delete:
					EntryVoteBlocked::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_VOTE_BLOCKED']
						.' WHERE '.$COLUMN['VOTER_UID'].' = ? AND '.$COLUMN['AUTHOR_UID'].' = ?'
						, array('text', 'text'));
					break;	
			}
		}
	}
}

?>