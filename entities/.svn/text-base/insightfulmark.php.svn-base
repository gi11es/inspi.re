<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/insightfulmarklist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class InsightfulMarkException extends Exception {}

class InsightfulMark implements Persistent {
	private $oid;
	private $uid;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	
    const cache_prefix = 'InsightfulMark-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of insightful mark with oid='.$this->oid.' and uid='.$this->uid);
		
		try {
			Cache::replaceorset(InsightfulMark::cache_prefix.$this->oid.'-'.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of insightful mark with oid='.$this->oid.' and uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($oid, $uid) {
		InsightfulMark::prepareStatement(InsightfulMark::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		InsightfulMark::$statement[InsightfulMark::statement_create]->execute(array($oid, $uid));
		Log::trace('DB', 'Executed InsightfulMark::statement_create ['.$oid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');

		$this->setOid($oid);
		$this->setUid($uid);
		$this->saveCache();
		
		InsightfulMarkList::deleteByOid($oid);
	}
	
	public static function get($oid, $uid) {
		if ($oid === null || $uid === null) throw new InsightfulMarkException('No insightful mark for oid='.$oid.' and uid='.$uid);
		
		try {
			$insightfulmark = Cache::get(InsightfulMark::cache_prefix.$oid.'-'.$uid);
		} catch (CacheException $e) {
			InsightfulMark::prepareStatement(InsightfulMark::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = InsightfulMark::$statement[InsightfulMark::statement_get]->execute(array($oid, $uid));
			Log::trace('DB', 'Executed InsightfulMark::statement_get ['.$oid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new InsightfulMarkException('No insightful mark for oid='.$oid.' and uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$insightfulmark = new InsightfulMark();
			$insightfulmark->populateFields($row);
			$insightfulmark->saveCache();
		}
		return $insightfulmark;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setOid($row[$COLUMN['OID']]);
		$this->setUid($row[$COLUMN['UID']]);
	}
	
	public function getOid() { return $this->oid; }
	
	public function setOid($new_oid) { $this->oid = $new_oid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(InsightfulMark::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case InsightfulMark::statement_get:
					InsightfulMark::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['OID'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['INSIGHTFUL_MARK']
						.' WHERE '.$COLUMN['OID'].' = ? AND '.$COLUMN['UID'].' = ?'
								, array('integer', 'text'));
					break;
				case InsightfulMark::statement_create:
					InsightfulMark::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['INSIGHTFUL_MARK']
						.'( '.$COLUMN['OID'].', '.$COLUMN['UID']
						.') VALUES(?, ?)', array('integer', 'text'));
					break;	
			}
		}
	}
}

?>