<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/commentindexlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class CommentIndexException extends Exception {}

class CommentIndex implements Persistent {
	private $word;
	private $oid;
	private $eid;
	private $count;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'CommentIndex-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of discussion post index with word='.$this->word.' and oid='.$this->oid);
		
		try {
			Cache::replaceorset(CommentIndex::cache_prefix.$this->word.'-'.$this->oid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of discussion post index with word='.$this->word.' and oid='.$this->oid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 4)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3]);
    }
	
	public function __construct2($word, $oid, $eid, $count) {
		CommentIndex::prepareStatement(CommentIndex::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		CommentIndex::$statement[CommentIndex::statement_create]->execute(array($word, $oid, $eid, $count));
		Log::trace('DB', 'Executed CommentIndex::statement_create ['.$word.', '.$oid.', "'.$eid.'", '.$count.'] ('.(microtime(true) - $start_timestamp).')');
		$oid = DB::insertid();

		$this->setWord($word);
		$this->setOid($oid);
		$this->setEid($eid);
		$this->setCount($count);
		$this->saveCache();
		
		CommentIndexList::deleteByEidAndWord($eid, $word);
		CommentIndexList::deleteByOid($oid);
	}
	
	public static function get($word, $oid) {
		if ($oid === null || $word === null) throw new CommentIndexException('No discussion post index for word='.$word.' and oid='.$oid);
		
		try {
			$commentindex = Cache::get(CommentIndex::cache_prefix.$word.'-'.$oid);
		} catch (CacheException $e) {
			CommentIndex::prepareStatement(CommentIndex::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommentIndex::$statement[CommentIndex::statement_get]->execute(array($word, $oid));
			Log::trace('DB', 'Executed CommentIndex::statement_get ['.$word.', '.$oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new CommentIndexException('No comment index for word='.$word.' and oid='.$oid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$commentindex = new CommentIndex();
			$commentindex->populateFields($row);
			$commentindex->saveCache();
		}
		
		return $commentindex;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setWord($row[$COLUMN['WORD']]);
		$this->setOid($row[$COLUMN['OID']]);
		$this->setEid($row[$COLUMN['EID']]);
		$this->setCount($row[$COLUMN['COUNT']]);
	}
	
	public function delete() {
		CommentIndex::prepareStatement(CommentIndex::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		CommentIndex::$statement[CommentIndex::statement_delete]->execute(array($this->word, $this->oid));
		Log::trace('DB', 'Executed CommentIndex::statement_delete ['.$this->word.', '.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(CommentIndex::cache_prefix.$this->word.'-'.$this->oid); } catch (CacheException $e) {}
		
		// Remove from associated lists
		
		CommentIndexList::deleteByEidAndWord($this->eid, $this->word);
		CommentIndexList::deleteByOid($this->oid);
	}
	
	public function getWord() { return $this->word; }
	
	public function setWord($new_word) { $this->word = $new_word; }
	
	public function getOid() { return $this->oid; }
	
	public function setOid($new_oid) { $this->oid = $new_oid; }
	
	public function getEid() { return $this->eid; }
	
	public function setEid($new_eid) { $this->eid = $new_eid; }
	
	public function getCount() { return $this->count; }
	
	public function setCount($new_count) { $this->count = $new_count; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CommentIndex::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CommentIndex::statement_get:
					CommentIndex::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['WORD'].', '.$COLUMN['OID'].', '.$COLUMN['EID']
						.', '.$COLUMN['COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMENT_INDEX']
						.' WHERE '.$COLUMN['WORD'].' = ? AND '.$COLUMN['OID'].' = ?'
								, array('integer', 'integer'));
					break;
				case CommentIndex::statement_create:
					CommentIndex::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['COMMENT_INDEX'].'( '.$COLUMN['WORD']
						.', '.$COLUMN['OID'].', '.$COLUMN['EID'].', '.$COLUMN['COUNT']
						.') VALUES(?, ?, ?, ?)', array('text', 'integer', 'integer', 'integer'));
					break;	
				case CommentIndex::statement_delete:
					CommentIndex::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['COMMENT_INDEX']
						.' WHERE '.$COLUMN['WORD'].' = ? AND '.$COLUMN['OID'].' = ?'
						, array('integer', 'integer'));
					break;	
			}
		}
	}
}

?>