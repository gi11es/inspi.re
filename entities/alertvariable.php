<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	A variable to be used in an alert's instance. It specifies the current value of one of the 
 	alert template's dynamic elements
*/

require_once(dirname(__FILE__).'/../entities/alertvariablelist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once('MDB2/Date.php');

class AlertVariableException extends Exception {}

class AlertVariable implements Persistent {
	private $aid;
	private $name;
	private $value;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setValue = 4;
	
    const cache_prefix = 'AlertVariable-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache alert of alert variable with aid='.$this->aid.' and name='.$this->name);
		
		try {
			Cache::replaceorset(AlertVariable::cache_prefix.$this->aid.'-'.$this->name, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache alert of alert variable with aid='.$this->aid.' and name='.$this->name);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 3)
			self::__construct2($argv[0], $argv[1], $argv[2]);
    }
	
	public function __construct2($aid, $name, $value) {
		global $ENTRY_STATUS;
		
		AlertVariable::prepareStatement(AlertVariable::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		AlertVariable::$statement[AlertVariable::statement_create]->execute(array($aid, $name, $value));
		Log::trace('DB', 'Executed AlertVariable::statement_create ['.$aid.', '.$name.', '.$value.'] ('.(microtime(true) - $start_timestamp).')');

		$this->setAid($aid);
		$this->setName($name);
		$this->setValue($value, false);
		$this->saveCache();
		AlertVariableList::deleteByAid($aid);
		AlertVariableList::deleteByName($name);
	}
	
	public static function get($aid, $name) {
		if ($aid === null) throw new AlertVariableException('No alert variable for aid='.$aid.' and name='.$name);
		
		try {
			$alert_variable = Cache::get(AlertVariable::cache_prefix.$aid.'-'.$name);
		} catch (CacheException $e) {
			AlertVariable::prepareStatement(AlertVariable::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = AlertVariable::$statement[AlertVariable::statement_get]->execute(array($aid, $name));
			Log::trace('DB', 'Executed AlertVariable::statement_get ['.$aid.', '.$name.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new AlertVariableException('No alert variable for aid='.$aid.' ane name='.$name);
			
			$row = $result->fetchRow();
			$result->free();
			
			$alert_variable = new AlertVariable();
			$alert_variable->populateFields($row);
			$alert_variable->saveCache();
		}
		return $alert_variable;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setAid($row[$COLUMN['AID']]);
		$this->setName($row[$COLUMN['NAME']]);
		$this->setValue($row[$COLUMN['VALUE']], false);
	}
	
	public function delete() {
		AlertVariable::prepareStatement(AlertVariable::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		AlertVariable::$statement[AlertVariable::statement_delete]->execute(array($this->aid, $this->name));
		Log::trace('DB', 'Executed AlertVariable::statement_delete ['.$this->aid.', '.$this->name.'] ('.(microtime(true) - $start_timestamp).')');
		
		AlertVariableList::deleteByAid($this->aid);
		AlertVariableList::deleteByName($this->name);
		
		try { Cache::delete(AlertVariable::cache_prefix.$this->aid.'-'.$this->name); } catch (CacheException $e) {}
	}
	
	public function getAid() { return $this->aid; }
	
	public function setAid($new_aid) { $this->aid = $new_aid; }
	
	public function getName() { return $this->name; }
	
	public function setName($new_name) { $this->name = $new_name; }
	
	public function getValue() { return $this->value; }
	
	public function setValue($new_value, $persist=true) {
		$old_value = $this->value;
		$this->value = $new_value;
		
		if ($persist) {
			AlertVariable::prepareStatement(AlertVariable::statement_setValue);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			AlertVariable::$statement[AlertVariable::statement_setValue]->execute(array($this->value, $this->aid, $this->name));
			Log::trace('DB', 'Executed AlertVariable::statement_setValue ['.$this->value.', '.$this->aid.', '.$this->name.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(AlertVariable::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case AlertVariable::statement_get:
					AlertVariable::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['AID'].', '.$COLUMN['NAME'].', '.$COLUMN['VALUE']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ALERT_VARIABLE']
						.' WHERE '.$COLUMN['AID'].' = ? AND '.$COLUMN['NAME'].' = ?'
								, array('integer', 'text'));
					break;
				case AlertVariable::statement_create:
					AlertVariable::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['ALERT_VARIABLE']
						.'( '.$COLUMN['AID'].', '.$COLUMN['NAME'].', '.$COLUMN['VALUE']
						.') VALUES(?, ?, ?)', array('integer', 'text', 'text'));
					break;	
				case AlertVariable::statement_delete:
					AlertVariable::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['ALERT_VARIABLE']
						.' WHERE '.$COLUMN['AID'].' = ? AND '.$COLUMN['NAME'].' = ?'
						, array('integer', 'text'));
					break;
				case AlertVariable::statement_setValue:
					AlertVariable::$statement[$statement] = DB::prepareSetter($TABLE['ALERT_VARIABLE'], array($COLUMN['AID'] => 'integer', $COLUMN['NAME'] => 'text'), $COLUMN['VALUE'], 'text');
					break;
			}
		}
	}
}

?>