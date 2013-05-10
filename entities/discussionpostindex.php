<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/discussionpostindexlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class DiscussionPostIndexException extends Exception {}

class DiscussionPostIndex implements Persistent {
	private $word;
	private $oid;
	private $xid;
	private $count;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setXid = 4;
	
    const cache_prefix = 'DiscussionPostIndex-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of discussion post index with word='.$this->word.' and oid='.$this->oid);
		
		try {
			Cache::replaceorset(DiscussionPostIndex::cache_prefix.$this->word.'-'.$this->oid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of discussion post index with word='.$this->word.' and oid='.$this->oid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 4)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3]);
    }
	
	public function __construct2($word, $oid, $xid, $count) {
		DiscussionPostIndex::prepareStatement(DiscussionPostIndex::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		DiscussionPostIndex::$statement[DiscussionPostIndex::statement_create]->execute(array($word, $oid, $xid, $count));
		Log::trace('DB', 'Executed DiscussionPostIndex::statement_create ['.$word.', '.$oid.', "'.$xid.'", '.$count.'] ('.(microtime(true) - $start_timestamp).')');
		$oid = DB::insertid();

		$this->setWord($word);
		$this->setOid($oid);
		$this->setXid($xid, false);
		$this->setCount($count);
		$this->saveCache();
		
		DiscussionPostIndexList::deleteByXidAndWord($xid, $word);
		DiscussionPostIndexList::deleteByOid($oid);
	}
	
	public static function get($word, $oid) {
		if ($oid === null || $word === null) throw new DiscussionPostIndexException('No discussion post index for word='.$word.' and oid='.$oid);
		
		try {
			$discussionpostindex = Cache::get(DiscussionPostIndex::cache_prefix.$word.'-'.$oid);
		} catch (CacheException $e) {
			DiscussionPostIndex::prepareStatement(DiscussionPostIndex::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionPostIndex::$statement[DiscussionPostIndex::statement_get]->execute(array($word, $oid));
			Log::trace('DB', 'Executed DiscussionPostIndex::statement_get ['.$word.', '.$oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new DiscussionPostIndexException('No discussion post index for word='.$word.' and oid='.$oid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$discussionpostindex = new DiscussionPostIndex();
			$discussionpostindex->populateFields($row);
			$discussionpostindex->saveCache();
		}
		
		return $discussionpostindex;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setWord($row[$COLUMN['WORD']]);
		$this->setOid($row[$COLUMN['OID']]);
		$this->setXid($row[$COLUMN['XID']], false);
		$this->setCount($row[$COLUMN['COUNT']]);
	}
	
	public function delete() {
		DiscussionPostIndex::prepareStatement(DiscussionPostIndex::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		DiscussionPostIndex::$statement[DiscussionPostIndex::statement_delete]->execute(array($this->word, $this->oid));
		Log::trace('DB', 'Executed DiscussionPostIndex::statement_delete ['.$this->word.', '.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(DiscussionPostIndex::cache_prefix.$this->word.'-'.$this->oid); } catch (CacheException $e) {}
		
		// Remove from associated lists
		
		DiscussionPostIndexList::deleteByXidAndWord($this->xid, $this->word);
		DiscussionPostIndexList::deleteByOid($this->oid);
	}
	
	public function getWord() { return $this->word; }
	
	public function setWord($new_word) { $this->word = $new_word; }
	
	public function getOid() { return $this->oid; }
	
	public function setOid($new_oid) { $this->oid = $new_oid; }
	
	public function getXid() { return $this->xid; }
	
	public function setXid($new_xid, $persist=true) {
		$old_xid = $this->xid;
		$this->xid = $new_xid;
		
		if ($persist) {
			DiscussionPostIndex::prepareStatement(DiscussionPostIndex::statement_setXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionPostIndex::$statement[DiscussionPostIndex::statement_setXid]->execute(array($this->xid, $this->word, $this->oid));
			Log::trace('DB', 'Executed DiscussionPostIndex::statement_setXid ['.$this->xid.', '.$this->word.', '.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			DiscussionPostIndexList::deleteByXidAndWord($this->xid, $this->word);
			DiscussionPostIndexList::deleteByXidAndWord($old_xid, $this->word);
		}
	}
	
	public function getCount() { return $this->count; }
	
	public function setCount($new_count) { $this->count = $new_count; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(DiscussionPostIndex::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case DiscussionPostIndex::statement_get:
					DiscussionPostIndex::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['WORD'].', '.$COLUMN['OID'].', '.$COLUMN['XID']
						.', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST_INDEX']
						.' WHERE '.$COLUMN['WORD'].' = ? AND '.$COLUMN['OID'].' = ?'
								, array('text', 'integer'));
					break;
				case DiscussionPostIndex::statement_create:
					DiscussionPostIndex::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST_INDEX'].'( '.$COLUMN['WORD']
						.', '.$COLUMN['OID'].', '.$COLUMN['XID'].', '.$COLUMN['COUNT']
						.') VALUES(?, ?, ?, ?)', array('text', 'integer', 'integer', 'integer'));
					break;	
				case DiscussionPostIndex::statement_delete:
					DiscussionPostIndex::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST_INDEX']
						.' WHERE '.$COLUMN['WORD'].' = ? AND '.$COLUMN['OID'].' = ?'
						, array('text', 'integer'));
					break;	
				case DiscussionPostIndex::statement_setXid:
					DiscussionPostIndex::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_POST_INDEX'], array($COLUMN['WORD'] => 'text', $COLUMN['OID'] => 'integer'), $COLUMN['XID'], 'integer');
					break;
			}
		}
	}
}

?>