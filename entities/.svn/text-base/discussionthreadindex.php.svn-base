<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/discussionthreadindexlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class DiscussionThreadIndexException extends Exception {}

class DiscussionThreadIndex implements Persistent {
	private $word;
	private $nid;
	private $xid;
	private $count;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setXid = 4;
	
    const cache_prefix = 'DiscussionThreadIndex-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of discussion thread index with word='.$this->word.' and nid='.$this->nid);
		
		try {
			Cache::replaceorset(DiscussionThreadIndex::cache_prefix.$this->word.'-'.$this->nid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of discussion thread index with word='.$this->word.' and nid='.$this->nid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 4)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3]);
    }
	
	public function __construct2($word, $nid, $xid, $count) {
		DiscussionThreadIndex::prepareStatement(DiscussionThreadIndex::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		DiscussionThreadIndex::$statement[DiscussionThreadIndex::statement_create]->execute(array($word, $nid, $xid, $count));
		Log::trace('DB', 'Executed DiscussionThreadIndex::statement_create ['.$word.', '.$nid.', "'.$xid.'", '.$count.'] ('.(microtime(true) - $start_timestamp).')');
		$nid = DB::insertid();

		$this->setWord($word);
		$this->setNid($nid);
		$this->setXid($xid, false);
		$this->setCount($count);
		$this->saveCache();
		
		DiscussionThreadIndexList::deleteByXidAndWord($xid, $word);
		DiscussionThreadIndexList::deleteByNid($nid);
	}
	
	public static function get($word, $nid) {
		if ($nid === null || $word === null) throw new DiscussionThreadIndexException('No discussion thread index for word='.$word.' and nid='.$nid);
		
		try {
			$discussionthreadindex = Cache::get(DiscussionThreadIndex::cache_prefix.$word.'-'.$nid);
		} catch (CacheException $e) {
			DiscussionThreadIndex::prepareStatement(DiscussionThreadIndex::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionThreadIndex::$statement[DiscussionThreadIndex::statement_get]->execute(array($word, $nid));
			Log::trace('DB', 'Executed DiscussionThreadIndex::statement_get ['.$word.', '.$nid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new DiscussionThreadIndexException('No discussion thread index for word='.$word.' and nid='.$nid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$discussionthreadindex = new DiscussionThreadIndex();
			$discussionthreadindex->populateFields($row);
			$discussionthreadindex->saveCache();
		}
		
		return $discussionthreadindex;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setWord($row[$COLUMN['WORD']]);
		$this->setNid($row[$COLUMN['NID']]);
		$this->setXid($row[$COLUMN['XID']], false);
		$this->setCount($row[$COLUMN['COUNT']]);
	}
	
	public function delete() {
		DiscussionThreadIndex::prepareStatement(DiscussionThreadIndex::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		DiscussionThread::$statement[DiscussionThreadIndex::statement_delete]->execute(array($this->word, $this->nid));
		Log::trace('DB', 'Executed DiscussionThreadIndex::statement_delete ['.$this->word.', '.$this->nid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(DiscussionThreadIndex::cache_prefix.$this->word.'-'.$this->nid); } catch (CacheException $e) {}
		
		// Remove from associated lists
		
		DiscussionThreadIndexList::deleteByXidAndWord($this->xid, $this->word);
		DiscussionThreadIndexList::deleteByNid($this->nid);
	}
	
	public function getWord() { return $this->word; }
	
	public function setWord($new_word) { $this->word = $new_word; }
	
	public function getNid() { return $this->nid; }
	
	public function setNid($new_nid) { $this->nid = $new_nid; }
	
	public function getXid() { return $this->xid; }
	
	public function setXid($new_xid, $persist=true) {
		$old_xid = $this->xid;
		$this->xid = $new_xid;
		
		if ($persist) {
			DiscussionThreadIndex::prepareStatement(DiscussionThreadIndex::statement_setXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionThreadIndex::$statement[DiscussionThreadIndex::statement_setXid]->execute(array($this->xid, $this->word, $this->nid));
			Log::trace('DB', 'Executed DiscussionThreadIndex::statement_setXid ['.$this->xid.', '.$this->word.', '.$this->nid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			DiscussionThreadIndexList::deleteByXidAndWord($this->xid, $this->word);
			DiscussionThreadIndexList::deleteByXidAndWord($old_xid, $this->word);
		}
	}
	
	public function getCount() { return $this->count; }
	
	public function setCount($new_count) { $this->count = $new_count; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(DiscussionThreadIndex::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case DiscussionThreadIndex::statement_get:
					DiscussionThreadIndex::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['WORD'].', '.$COLUMN['NID'].', '.$COLUMN['XID']
						.', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD_INDEX']
						.' WHERE '.$COLUMN['WORD'].' = ? AND '.$COLUMN['NID'].' = ?'
								, array('text', 'integer'));
					break;
				case DiscussionThreadIndex::statement_create:
					DiscussionThreadIndex::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD_INDEX'].'( '.$COLUMN['WORD']
						.', '.$COLUMN['NID'].', '.$COLUMN['XID'].', '.$COLUMN['COUNT']
						.') VALUES(?, ?, ?, ?)', array('text', 'integer', 'integer', 'integer'));
					break;	
				case DiscussionThreadIndex::statement_delete:
					DiscussionThreadIndex::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_THREAD_INDEX']
						.' WHERE '.$COLUMN['WORD'].' = ? AND '.$COLUMN['NID'].' = ?'
						, array('text', 'integer'));
					break;	
				case DiscussionThreadIndex::statement_setXid:
					DiscussionThreadIndex::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_THREAD_INDEX'], array($COLUMN['WORD'] => 'text', $COLUMN['NID'] => 'integer'), $COLUMN['XID'], 'integer');
					break;
			}
		}
	}
}

?>