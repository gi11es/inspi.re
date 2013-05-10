<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Statistics about the website
*/

require_once(dirname(__FILE__).'/../entities/statisticlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class StatisticException extends Exception {}

class Statistic implements Persistent {
	private $sid;
	private $value;
	private $timestamp;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'Statistic-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of statistic with sid='.$this->sid.' and timestamp='.$this->timestamp);
		
		try {
			Cache::replaceorset(Statistic::cache_prefix.$this->sid.'-'.$this->timestamp, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of statistic with sid='.$this->sid.' and timestamp='.$this->timestamp);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($sid, $value) {
		Statistic::prepareStatement(Statistic::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Statistic::$statement[Statistic::statement_create]->execute(array($sid, $value));
		Log::trace('DB', 'Executed Statistic::statement_create ['.$sid.', "'.$value.'"] ('.(microtime(true) - $start_timestamp).')');

		$this->setSid($sid);
		$this->setValue($value);
		$this->setTimestamp(time());
		$this->saveCache();
		
		StatisticList::deleteBySid($sid);
	}
	
	public static function get($sid, $timestamp) {
		if ($sid === null || $value === null) throw new StatisticException('No statistic for sid='.$sid.' and timestamp='.$timestamp);
		
		try {
			$statistic = Cache::get(Statistic::cache_prefix.$sid.'-'.$timestamp);
		} catch (CacheException $e) {
			Statistic::prepareStatement(Statistic::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = Statistic::$statement[Statistic::statement_get]->execute(array($sid, $timestamp));
			Log::trace('DB', 'Executed Statistic::statement_get ['.$sid.', '.$timestamp.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new StatisticException('No statistic for sid='.$sid.' and timestamp='.$timestamp);
			
			$row = $result->fetchRow();
			$result->free();
			
			$statistic = new Statistic();
			$statistic->populateFields($row);
			$statistic->saveCache();
		}
		return $statistic;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setSid($row[$COLUMN['SID']]);
		$this->setValue($row[$COLUMN['VALUE']]);
		$this->setTimestamp($row[$COLUMN['TIMESTAMP']]);
	}
	
	public function delete() {
		Statistic::prepareStatement(Statistic::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = Statistic::$statement[Statistic::statement_delete]->execute(array($this->sid, $this->timestamp));
		Log::trace('DB', 'Executed Statistic::statement_delete ['.$this->sid.', '.$this->timestamp.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(Statistic::cache_prefix.$this->timestamp); } catch (CacheException $e) {}
		
		StatisticList::deleteBySid($this->sid);
	}
	
	public function getSid() { return $this->sid; }
	
	public function setSid($new_sid) { $this->sid = $new_sid; }
	
	public function getValue() { return $this->value; }
	
	public function setValue($new_value) { $this->value = $new_value; }
	
	public function getTimestamp() { return $this->timestamp; }
	
	public function setTimestamp($new_timestamp) { $this->timestamp = $new_timestamp; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(Statistic::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case Statistic::statement_get:
					Statistic::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['SID'].', '.$COLUMN['VALUE']
						.', UNIX_TIMESTAMP('.$COLUMN['TIMESTAMP'].') AS '.$COLUMN['TIMESTAMP']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['STATISTIC']
						.' WHERE '.$COLUMN['SID'].' = ? AND '.$COLUMN['TIMESTAMP'].' = ?'
								, array('integer', 'timestamp'));
					break;
				case Statistic::statement_create:
					Statistic::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['STATISTIC']
						.'( '.$COLUMN['SID'].', '.$COLUMN['VALUE']
						.') VALUES(?, ?)', array('integer', 'integer'));
					break;	
				case Statistic::statement_delete:
					Statistic::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['STATISTIC']
						.' WHERE '.$COLUMN['SID'].' = ? AND '.$COLUMN['TIMESTAMP'].' = ?'
						, array('integer', 'timestamp'));
					break;
			}
		}
	}
}

?>