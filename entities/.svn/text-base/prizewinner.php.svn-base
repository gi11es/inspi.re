<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	People who won the monthly cash prize
*/

require_once(dirname(__FILE__).'/../entities/prizewinnerlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class PrizeWinnerException extends Exception {}

class PrizeWinner implements Persistent {
	private $eid;
	private $uid;
	private $creation_time;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'PrizeWinner-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of prizewinner with eid='.$this->eid);
		
		try {
			Cache::replaceorset(PrizeWinner::cache_prefix.$this->eid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of prizewinner with eid='.$this->eid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($eid, $uid) {
		PrizeWinner::prepareStatement(PrizeWinner::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		PrizeWinner::$statement[PrizeWinner::statement_create]->execute(array($eid, $uid));
		Log::trace('DB', 'Executed PrizeWinner::statement_create ['.$eid.', "'.$uid.'"] ('.(microtime(true) - $start_timestamp).')');

		$this->setEid($eid);
		$this->setUid($uid);
		$this->setCreationTime(time());
		$this->saveCache();
	}
	
	public static function get($eid, $cache = true) {
		if ($eid === null) throw new PrizeWinnerException('No prizewinner for eid='.$eid);
		
		try {
			$prizewinner = Cache::get(PrizeWinner::cache_prefix.$eid);
		} catch (CacheException $e) {
			PrizeWinner::prepareStatement(PrizeWinner::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PrizeWinner::$statement[PrizeWinner::statement_get]->execute($eid);
			Log::trace('DB', 'Executed PrizeWinner::statement_get ['.$eid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new PrizeWinnerException('No prizewinner for eid='.$eid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$prizewinner = new PrizeWinner();
			$prizewinner->populateFields($row);
			if ($cache) $prizewinner->saveCache();
		}
		return $prizewinner;
	}
	
	public static function getArray($eidlist, $cache = true) {
		$result = array();
		$querylist = array();
		
		foreach ($eidlist as $eid) $querylist []= PrizeWinner::cache_prefix.$eid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($eidlist as $eid) try {
			if (isset($cacheresult[PrizeWinner::cache_prefix.$eid])) $result[$eid] = $cacheresult[PrizeWinner::cache_prefix.$eid];
			else $result[$eid] = PrizeWinner::get($eid, $cache);
		} catch (PrizeWinnerException $e) {}
		
		return $result;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setEid($row[$COLUMN['EID']]);
		$this->setUid($row[$COLUMN['UID']]);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
	}
	
	public function delete() {
		PrizeWinner::prepareStatement(PrizeWinner::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = PrizeWinner::$statement[PrizeWinner::statement_delete]->execute($this->eid);
		Log::trace('DB', 'Executed PrizeWinner::statement_delete ['.$this->eid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(PrizeWinner::cache_prefix.$this->eid); } catch (CacheException $e) {}
	}
	
	public function getEid() { return $this->eid; }
	
	public function setEid($new_eid) { $this->eid = $new_eid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time) { $this->creation_time = $new_creation_time; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PrizeWinner::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PrizeWinner::statement_get:
					PrizeWinner::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID'].', '.$COLUMN['UID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PRIZE_WINNER']
						.' WHERE '.$COLUMN['EID'].' = ?'
								, array('integer'));
					break;
				case PrizeWinner::statement_create:
					PrizeWinner::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['PRIZE_WINNER']
						.'( '.$COLUMN['EID'].', '.$COLUMN['UID']
						.') VALUES(?, ?)', array('integer', 'text'));
					break;	
				case PrizeWinner::statement_delete:
					PrizeWinner::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['PRIZE_WINNER	']
						.' WHERE '.$COLUMN['EID'].' = ?'
						, array('integer', 'text'));
					break;
			}
		}
	}
}

?>