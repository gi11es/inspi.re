<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Members can hide competitions that they don't want to enter, the information is stored in this class
*/

require_once(dirname(__FILE__).'/../entities/competitionhidelist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class CompetitionHideException extends Exception {}

class CompetitionHide implements Persistent {
	private $cid;
	private $uid;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'CompetitionHide-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of competition hide with cid='.$this->cid.' and uid='.$this->uid);
		
		try {
			Cache::replaceorset(CompetitionHide::cache_prefix.$this->cid.'-'.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of competition hide with cid='.$this->cid.' and uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($cid, $uid) {
		CompetitionHide::prepareStatement(CompetitionHide::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		CompetitionHide::$statement[CompetitionHide::statement_create]->execute(array($cid, $uid));
		Log::trace('DB', 'Executed CompetitionHide::statement_create ['.$cid.', "'.$uid.'"] ('.(microtime(true) - $start_timestamp).')');

		$this->setCid($cid);
		$this->setUid($uid);
		$this->saveCache();
		
		CompetitionHideList::deleteByCid($cid);
		CompetitionHideList::deleteByUid($uid);
	}
	
	public static function get($cid, $uid) {
		if ($cid === null || $uid === null) throw new CompetitionHideException('No competition hide for cid='.$cid.' and uid='.$uid);
		
		try {
			$competition_hide = Cache::get(CompetitionHide::cache_prefix.$cid.'-'.$uid);
		} catch (CacheException $e) {
			CompetitionHide::prepareStatement(CompetitionHide::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CompetitionHide::$statement[CompetitionHide::statement_get]->execute(array($cid, $uid));
			Log::trace('DB', 'Executed CompetitionHide::statement_get ['.$cid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new CompetitionHideException('No competition hide for cid='.$cid.' and uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$competition_hide = new CompetitionHide();
			$competition_hide->populateFields($row);
			$competition_hide->saveCache();
		}
		return $competition_hide;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setCid($row[$COLUMN['CID']]);
		$this->setUid($row[$COLUMN['UID']]);
	}
	
	public function delete() {
		CompetitionHide::prepareStatement(CompetitionHide::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = CompetitionHide::$statement[CompetitionHide::statement_delete]->execute(array($this->cid, $this->uid));
		Log::trace('DB', 'Executed CompetitionHide::statement_delete ['.$this->cid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(CompetitionHide::cache_prefix.$this->cid.'-'.$this->uid); } catch (CacheException $e) {}
		
		CompetitionHideList::deleteByCid($this->cid);
		CompetitionHideList::deleteByUid($this->uid);
	}
	
	public function getCid() { return $this->cid; }
	
	public function setCid($new_cid) { $this->cid = $new_cid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CompetitionHide::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CompetitionHide::statement_get:
					CompetitionHide::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CID'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMPETITION_HIDE']
						.' WHERE '.$COLUMN['CID'].' = ? AND '.$COLUMN['UID'].' = ?'
								, array('integer', 'text'));
					break;
				case CompetitionHide::statement_create:
					CompetitionHide::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['COMPETITION_HIDE']
						.'( '.$COLUMN['CID'].', '.$COLUMN['UID']
						.') VALUES(?, ?)', array('integer', 'text'));
					break;	
				case CompetitionHide::statement_delete:
					CompetitionHide::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['COMPETITION_HIDE']
						.' WHERE '.$COLUMN['CID'].' = ? AND '.$COLUMN['UID'].' = ?'
						, array('integer', 'text'));
					break;
			}
		}
	}
}

?>