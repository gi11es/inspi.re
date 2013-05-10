<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	One-off emails sent to users
*/

require_once(dirname(__FILE__).'/../entities/emailcampaignlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class EmailCampaignException extends Exception {}

class EmailCampaign implements Persistent {
	private $uid;
	private $etid;
	private $creation_time;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'EmailCampaign-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of email campaign with uid='.$this->uid.' and etid='.$this->etid);
		
		try {
			Cache::replaceorset(EmailCampaign::cache_prefix.$this->uid.'-'.$this->etid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of email campaign with uid='.$this->uid.' and etid='.$this->etid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($uid, $etid) {
		EmailCampaign::prepareStatement(EmailCampaign::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		EmailCampaign::$statement[EmailCampaign::statement_create]->execute(array($uid, $etid));
		Log::trace('DB', 'Executed EmailCampaign::statement_create ['.$uid.', "'.$etid.'"] ('.(microtime(true) - $start_timestamp).')');

		$this->setUid($uid);
		$this->setETid($etid);
		$this->setCreationTime(time());
		$this->saveCache();
		
		EmailCampaignList::deleteByUid($uid);
		EmailCampaignList::deleteByETid($etid);
	}
	
	public static function get($uid, $etid, $cache = true) {
		if ($uid === null || $etid === null) throw new EmailCampaignException('No email campaign for uid='.$uid.' and etid='.$etid);
		
		try {
			$email_campaign = Cache::get(EmailCampaign::cache_prefix.$uid.'-'.$etid);
		} catch (CacheException $e) {
			EmailCampaign::prepareStatement(EmailCampaign::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EmailCampaign::$statement[EmailCampaign::statement_get]->execute(array($uid, $etid));
			Log::trace('DB', 'Executed EmailCampaign::statement_get ['.$uid.', '.$etid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new EmailCampaignException('No email campaign for uid='.$uid.' and etid='.$etid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$email_campaign = new EmailCampaign();
			$email_campaign->populateFields($row);
			if ($cache) $email_campaign->saveCache();
		}
		return $email_campaign;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setUid($row[$COLUMN['UID']]);
		$this->setETid($row[$COLUMN['ETID']]);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
	}
	
	public function delete() {
		EmailCampaign::prepareStatement(EmailCampaign::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = EmailCampaign::$statement[EmailCampaign::statement_delete]->execute(array($this->uid, $this->etid));
		Log::trace('DB', 'Executed EmailCampaign::statement_delete ['.$this->uid.', '.$this->etid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(EmailCampaign::cache_prefix.$this->etid); } catch (CacheException $e) {}
		
		EmailCampaignList::deleteByUid($this->uid);
		EmailCampaignList::deleteByETid($this->etid);
	}
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public function getETid() { return $this->etid; }
	
	public function setETid($new_etid) { $this->etid = $new_etid; }
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time) { $this->creation_time = $new_creation_time; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(EmailCampaign::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case EmailCampaign::statement_get:
					EmailCampaign::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['ETID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['EMAIL_CAMPAIGN']
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['ETID'].' = ?'
								, array('text', 'integer'));
					break;
				case EmailCampaign::statement_create:
					EmailCampaign::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['EMAIL_CAMPAIGN']
						.'( '.$COLUMN['UID'].', '.$COLUMN['ETID']
						.') VALUES(?, ?)', array('text', 'integer'));
					break;	
				case EmailCampaign::statement_delete:
					EmailCampaign::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['EMAIL_CAMPAIGN']
						.' WHERE '.$COLUMN['UID'].' = ? AND '.$COLUMN['ETID'].' = ?'
						, array('text', 'integer'));
					break;
			}
		}
	}
}

?>