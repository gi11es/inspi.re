<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Alerts are short messages intended to let a user know that something related to their content 
 	(entry, discussion post) happened
 	
 	Since an alert can be sent to multiple users at once (members of a community, people who've 
 	entered a specific competition), the destination information is contained in another class
 	
 	Since an alert contains dynamic elements, it has to potentially use multiple variables to 
 	populate its template, they are to be found in the AlertVariable object
*/

require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertinstancelist.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/alertvariablelist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once('MDB2/Date.php');

class AlertException extends Exception {}

class Alert implements Persistent {
	private $aid;
	private $atid;
	private $creation_time;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'Alert-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache alert of alert with aid='.$this->aid);
		
		try {
			Cache::replaceorset(Alert::cache_prefix.$this->aid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache alert of alert with aid='.$this->aid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 1)
			self::__construct2($argv[0]);
    }
	
	public function __construct2($atid) {
		global $ENTRY_STATUS;
		
		Alert::prepareStatement(Alert::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Alert::$statement[Alert::statement_create]->execute($atid);
		Log::trace('DB', 'Executed Alert::statement_create ['.$atid.'] ('.(microtime(true) - $start_timestamp).')');
		
		$aid = DB::insertid();

		$this->setAid($aid);
		$this->setATid($atid);
		$this->setCreationTime(time());
		$this->saveCache();
	}
	
	public static function get($aid, $cache = true) {
		if ($aid === null) throw new AlertException('No alert for aid='.$aid);
		
		try {
			$alert = Cache::get(Alert::cache_prefix.$aid);
		} catch (CacheException $e) {
			Alert::prepareStatement(Alert::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = Alert::$statement[Alert::statement_get]->execute($aid);
			Log::trace('DB', 'Executed Alert::statement_get ['.$aid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new AlertException('No alert for aid='.$aid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$alert = new Alert();
			$alert->populateFields($row);
			if ($cache) $alert->saveCache();
		}
		
		return $alert;
	}
	
	public static function getArray($aidlist, $cache = true) {
		$result = array();
		$querylist = array();
		
		foreach ($aidlist as $aid) $querylist []= Alert::cache_prefix.$aid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($aidlist as $aid) try {
			if (isset($cacheresult[Alert::cache_prefix.$aid])) $result[$aid] = $cacheresult[Alert::cache_prefix.$aid];
			else $result[$aid] = Alert::get($aid, $cache);
		} catch (AlertException $e) {}
		
		return $result;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setAid($row[$COLUMN['AID']]);
		$this->setATid($row[$COLUMN['ATID']]);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
	}
	
	public function delete() {
		Alert::prepareStatement(Alert::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Alert::$statement[Alert::statement_delete]->execute($this->aid);
		Log::trace('DB', 'Executed Alert::statement_delete ['.$this->aid.'] ('.(microtime(true) - $start_timestamp).')');
		
		// Delete variables associated with this alert
		
		$alertvariablelist = AlertVariableList::getByAid($this->aid);
		foreach ($alertvariablelist as $name) {
			$alertvariable = AlertVariable::get($this->aid, $name);
			$alertvariable->delete();
		}
		
		// Delete instances (recipients) associated with this alert
		
		$alertinstancelist = AlertInstanceList::getByAid($this->aid);
		foreach ($alertinstancelist as $uid) {
			$alertinstance = AlertInstance::get($this->aid, $uid);
			$alertinstance->delete();
		}
		
		try { Cache::delete(Alert::cache_prefix.$this->aid); } catch (CacheException $e) {}
	}
	
	public function getAid() { return $this->aid; }
	
	public function setAid($new_aid) { $this->aid = $new_aid; }
	
	public function getATid() { return $this->atid; }
	
	public function setATid($new_atid) { $this->atid = $new_atid; }
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time) { $this->creation_time = $new_creation_time; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(Alert::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case Alert::statement_get:
					Alert::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['AID'].', '.$COLUMN['ATID'].', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ALERT']
						.' WHERE '.$COLUMN['AID'].' = ?'
								, array('integer'));
					break;
				case Alert::statement_create:
					Alert::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['ALERT']
						.'( '.$COLUMN['ATID']
						.') VALUES(?)', array('integer'));
					break;	
				case Alert::statement_delete:
					Alert::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['ALERT']
						.' WHERE '.$COLUMN['AID'].' = ?'
						, array('integer'));
					break;
			}
		}
	}
}

?>