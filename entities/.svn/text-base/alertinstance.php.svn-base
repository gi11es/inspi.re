<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Contains one alert destination and its associated status
 	If an alert is sent to 20 people, there should be 20 AlertInstance objects/entries
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstancelist.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/alertvariablelist.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../utilities/inml.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/template.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once('MDB2/Date.php');

class AlertInstanceException extends Exception {}

class AlertInstance implements Persistent {
	private $aid;
	private $uid;
	private $status;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setStatus = 4;
	
    const cache_prefix = 'AlertInstance-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache alert of alert instance with aid='.$this->aid.' and uid='.$this->uid);
		
		try {
			Cache::replaceorset(AlertInstance::cache_prefix.$this->aid.'-'.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache alert of alert instance with aid='.$this->aid.' and uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 3)
			self::__construct2($argv[0], $argv[1], $argv[2]);
    }
	
	public function __construct2($aid, $uid, $status) {
		global $ENTRY_STATUS;
		global $ALERT_INSTANCE_STATUS;
		
		AlertInstance::prepareStatement(AlertInstance::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		AlertInstance::$statement[AlertInstance::statement_create]->execute(array($aid, $uid, $status));
		Log::trace('DB', 'Executed AlertInstance::statement_create ['.$aid.', '.$uid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');

		$this->setAid($aid);
		$this->setUid($uid);
		$this->setStatus($status, false);
		$this->saveCache();
		
		AlertInstanceList::deleteByUid($this->uid);
		AlertInstanceList::deleteByUidAndStatus($this->uid, $status);
		AlertInstanceList::deleteByAid($this->aid);
		
		if ($status == $ALERT_INSTANCE_STATUS['NEW']) {
			$this->send();
		}
	}
	
	public function send() {
		global $ALERT_INSTANCE_STATUS;
		global $ALERT_TEMPLATE;
		global $DEV_SERVER;
		
		try {
			$user = User::get($this->getUid());
				
			// If the user wants so, he/she'll receive an email immediately about this alert
			if ($user->getAlertEmail() && !$DEV_SERVER) try {
				$alert = Alert::get($this->getAid());
				$atid = $alert->getATid();
				$template = $ALERT_TEMPLATE[$atid];
				
				$variablelist = AlertVariableList::getByAid($this->getAid());
				$variables = array();
				if (!empty($variablelist)) foreach ($variablelist as $name) {
					$alert_variable = AlertVariable::get($this->getAid(), $name);
					$variables[$name] = $alert_variable->getValue();
				}
				
				$alert_message = Template::templatize($template, $variables);
				
				$result = '<translate id="ALERT_'.$atid.'">'.$alert_message.'</translate>';
				$result = I18N::translateHTML($user, $result);
				$result = INML::processHTML($user, $result);
				
				$result = mb_ereg_replace('<[^>]+>', '', $result); // Remove any HTML tags
				
				try {
					Email::mail($user->getEmail(), $user->getLid(), 'ALERT', array('username' => $user->getUniqueName(), 'alerttext' => $result));
				} catch (EmailException $g) {}
			} catch (AlertException $f) {}
		} catch (UserException $e) {}
		
		if ($this->getStatus() != $ALERT_INSTANCE_STATUS['NEW']) {
			$this->setStatus($ALERT_INSTANCE_STATUS['NEW']);
		}
	}
	
	public static function get($aid, $uid) {
		if ($aid === null) throw new AlertInstanceException('No alert instance for aid='.$aid.' and uid='.$uid);
		
		try {
			$alert_instance = Cache::get(AlertInstance::cache_prefix.$aid.'-'.$uid);
		} catch (CacheException $e) {
			AlertInstance::prepareStatement(AlertInstance::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = AlertInstance::$statement[AlertInstance::statement_get]->execute(array($aid, $uid));
			Log::trace('DB', 'Executed AlertInstance::statement_get ['.$aid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new AlertInstanceException('No alert instance for aid='.$aid.' ane uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$alert_instance = new AlertInstance();
			$alert_instance->populateFields($row);
			$alert_instance->saveCache();
		}
		return $alert_instance;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setAid($row[$COLUMN['AID']]);
		$this->setUid($row[$COLUMN['UID']]);
		$this->setStatus($row[$COLUMN['STATUS']], false);
	}
	
	public function delete() {
		AlertInstance::prepareStatement(AlertInstance::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		AlertInstance::$statement[AlertInstance::statement_delete]->execute(array($this->aid, $this->uid));
		Log::trace('DB', 'Executed AlertInstance::statement_delete ['.$this->aid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		AlertInstanceList::deleteByUidAndStatus($this->uid, $this->status);
		AlertInstanceList::deleteByUid($this->uid);
		AlertInstanceList::deleteByAid($this->aid);
		
		try { Cache::delete(AlertInstance::cache_prefix.$this->aid.'-'.$this->uid); } catch (CacheException $e) {}
	}
	
	public function getAid() { return $this->aid; }
	
	public function setAid($new_aid) { $this->aid = $new_aid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			AlertInstance::prepareStatement(AlertInstance::statement_setStatus);
			$this->saveCache();
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			AlertInstance::$statement[AlertInstance::statement_setStatus]->execute(array($this->status, $this->aid, $this->uid));
			Log::trace('DB', 'Executed AlertInstance::statement_setStatus ['.$this->status.', '.$this->aid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			AlertInstanceList::deleteByUidAndStatus($this->uid, $old_status);
			AlertInstanceList::deleteByUidAndStatus($this->uid, $new_status);
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(AlertInstance::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case AlertInstance::statement_get:
					AlertInstance::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['AID'].', '.$COLUMN['UID'].', '.$COLUMN['STATUS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ALERT_INSTANCE']
						.' WHERE '.$COLUMN['AID'].' = ? AND '.$COLUMN['UID'].' = ?'
								, array('integer', 'text'));
					break;
				case AlertInstance::statement_create:
					AlertInstance::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['ALERT_INSTANCE']
						.'( '.$COLUMN['AID'].', '.$COLUMN['UID'].', '.$COLUMN['STATUS']
						.') VALUES(?, ?, ?)', array('integer', 'text', 'integer'));
					break;	
				case AlertInstance::statement_delete:
					AlertInstance::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['ALERT_INSTANCE']
						.' WHERE '.$COLUMN['AID'].' = ? AND '.$COLUMN['UID'].' = ?'
						, array('integer', 'text'));
					break;
				case AlertInstance::statement_setStatus:
					AlertInstance::$statement[$statement] = DB::prepareSetter($TABLE['ALERT_INSTANCE'], array($COLUMN['AID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['STATUS'], 'integer');
					break;
			}
		}
	}
}

?>
