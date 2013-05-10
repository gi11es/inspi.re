<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Contains the value in points for actions on the website
*/

require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class PointsValueException extends Exception {}

class PointsValue implements Persistent {
	private $pvid;
	private $value;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setValue = 4;
	
    const cache_prefix = 'PointsValue-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of PointsValue with pvid='.$this->pvid);
		
		try {
			Cache::replaceorset(PointsValue::cache_prefix.$this->pvid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of PointsValue with pvid='.$this->pvid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($pvid, $value) {
		PointsValue::prepareStatement(PointsValue::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		PointsValue::$statement[PointsValue::statement_create]->execute(array($pvid, $value));
		Log::trace('DB', 'Executed PointsValue::statement_create ['.$pvid.', '.$value.'] ('.(microtime(true) - $start_timestamp).')');

		$this->setPVid($pvid);
		$this->setValue($value, false);
		$this->saveCache();
	}
	
	public static function get($pvid, $cache = true) {
		if ($pvid === null) throw new PointsValueException('No PointsValue for pvid='.$pvid);
		
		try {
			$PointsValue = Cache::get(PointsValue::cache_prefix.$pvid);
		} catch (CacheException $e) {
			PointsValue::prepareStatement(PointsValue::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PointsValue::$statement[PointsValue::statement_get]->execute($pvid);
			Log::trace('DB', 'Executed PointsValue::statement_get ['.$pvid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new PointsValueException('No PointsValue for pvid='.$pvid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$PointsValue = new PointsValue();
			$PointsValue->populateFields($row);
			if ($cache) $PointsValue->saveCache();
		}
		return $PointsValue;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setPVid($row[$COLUMN['PVID']]);
		$this->setValue($row[$COLUMN['VALUE']], false);
	}
	
	public function delete() {
		PointsValue::prepareStatement(PointsValue::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		PointsValue::$statement[PointsValue::statement_delete]->execute($this->pvid);
		Log::trace('DB', 'Executed PointsValue::statement_delete ['.$this->pvid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(PointsValue::cache_prefix.$this->pvid); } catch (CacheException $e) {}
	}
	
	public function getPVid() { return $this->pvid; }
	
	public function setPVid($new_pvid) { $this->pvid = $new_pvid; }
	
	public function getValue() { return $this->value; }
	
	public function setValue($new_value, $persist=true) {	
		$this->value = $new_value;
		
		if ($persist) {
			PointsValue::prepareStatement(PointsValue::statement_setValue);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			PointsValue::$statement[PointsValue::statement_setValue]->execute(array($this->value, $this->pvid));
			Log::trace('DB', 'Executed PointsValue::statement_setValue ["'.$this->value.'", '.$this->pvid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PointsValue::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PointsValue::statement_get:
					PointsValue::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['VALUE'].', '.$COLUMN['PVID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['POINTS_VALUE']
						.' WHERE '.$COLUMN['PVID'].' = ?'
								, array('integer'));
					break;
				case PointsValue::statement_create:
					PointsValue::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['POINTS_VALUE']
						.'( '.$COLUMN['PVID'].', '
						.$COLUMN['VALUE']
						.') VALUES(?, ?)', array('integer', 'integer'));
					break;	
				case PointsValue::statement_delete:
					PointsValue::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['POINTS_VALUE']
						.' WHERE '.$COLUMN['PVID'].' = ?'
						, array('integer'));
					break;
				case PointsValue::statement_setValue:
					PointsValue::$statement[$statement] = DB::prepareSetter($TABLE['POINTS_VALUE'], array($COLUMN['PVID'] => 'integer'), $COLUMN['VALUE'], 'integer');
					break;
			}
		}
	}
}

?>