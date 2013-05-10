<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	This class stores premium membership coupon codes
*/

require_once(dirname(__FILE__)."/../entities/premiumcodelist.php");
require_once(dirname(__FILE__)."/../utilities/cache.php");
require_once(dirname(__FILE__)."/../utilities/db.php");
require_once(dirname(__FILE__)."/../constants.php");

class PremiumCodeException extends Exception {}

class PremiumCode {
	private $code;
	private $duration;
	private $txnid = null;
	private $uid = null;
	private $membership_age = 0;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_setTXNid = 3;
	const statement_setUid = 4;
	const statement_setMembershipAge = 5;
	
	const cache_prefix = 'PremiumCode-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of premium code with code='.$this->code);
		
		try {
			Cache::replaceorset(PremiumCode::cache_prefix.$this->code, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of premium code with code='.$this->code);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 1)
			self::__construct2($argv[0]);
    }

	public function __construct2($duration) {
		// loop to make sure that this new code is unique
		do {
			$code = substr(strtolower(sha1(microtime())), 0, 20);

			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			PremiumCode::prepareStatement(PremiumCode::statement_create);
			$result = PremiumCode::$statement[PremiumCode::statement_create]->execute(array($code, $duration));
			Log::trace('DB', 'Executed PremiumCode::statement_create ['.$code.', '.$duration.'] ('.(microtime(true) - $start_timestamp).')');
		} while(PEAR::isError($result));
		
		$this->setCode($code);
		$this->setDuration($duration);
		$this->saveCache();
	}
	
	public function getDisplayCode() {
		$localcode = $this->getCode();
		return strtoupper(substr($localcode, 0, 5).'-'.substr($localcode, 5, 5).'-'.substr($localcode, 10, 5).'-'.substr($localcode, 15, 5));
	}
	
	public function getCode() {
		return $this->code;
	}
	
	public function setCode($new_code) {
		$this->code = $new_code;
	}
	
	public function getDuration() {
		return $this->duration;
	}
	
	public function setDuration($new_duration) {
		$this->duration = $new_duration;
	}
	
	public function getTXNid() {
		return $this->txnid;
	}
	
	public function setTXNid($new_txnid, $persist=true) {	
		$this->txnid = $new_txnid;
		
		if ($persist) {
			PremiumCode::prepareStatement(PremiumCode::statement_setTXNid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			PremiumCode::$statement[PremiumCode::statement_setTXNid]->execute(array($this->txnid, $this->code));
			Log::trace('DB', 'Executed PremiumCode::statement_setTXNid ['.$this->txnid.', '.$this->code.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			PremiumCodeList::deleteByTXNid($this->txnid);
		}
	}
	
	public function getUid() {
		return $this->uid;
	}
	
	public function setUid($new_uid, $persist=true) {	
		$this->uid = $new_uid;
		
		if ($persist) {
			PremiumCode::prepareStatement(PremiumCode::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			PremiumCode::$statement[PremiumCode::statement_setUid]->execute(array($this->uid, $this->code));
			Log::trace('DB', 'Executed PremiumCode::statement_setUid ['.$this->uid.', '.$this->code.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getMembershipAge() {
		return $this->membership_age;
	}
	
	public function setMembershipAge($new_membership_age, $persist=true) {	
		$this->membership_age = $new_membership_age;
		
		if ($persist) {
			PremiumCode::prepareStatement(PremiumCode::statement_setMembershipAge);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			PremiumCode::$statement[PremiumCode::statement_setMembershipAge]->execute(array($this->membership_age, $this->code));
			Log::trace('DB', 'Executed PremiumCode::statement_setMembershipAge ['.$this->membership_age.', '.$this->code.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public static function getByDisplayCode($displaycode) {
		$localcode = trim($displaycode);
		$localcode = mb_ereg_replace('[^\d\w]+', '', $localcode);
		return PremiumCode::get($localcode);
	}
	
	public static function get($code) {
		if ($code === null) throw new PremiumCodeException('No premium code for code='.$code);
		
		try {
			$premiumcode = Cache::get(PremiumCode::cache_prefix.$code);
		} catch (CacheException $e) {
			PremiumCode::prepareStatement(PremiumCode::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PremiumCode::$statement[PremiumCode::statement_get]->execute($code);
			Log::trace('DB', 'Executed PremiumCode::statement_get ['.$code.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new PremiumCodeException('No premium code for code='.$code);
			
			$row = $result->fetchRow();
			$result->free();
			
			$premiumcode = new PremiumCode();
			$premiumcode->populateFields($row);
			$premiumcode->saveCache();
		}
		return $premiumcode;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setCode($row[$COLUMN['CODE']]);
		$this->setDuration($row[$COLUMN['DURATION']]);
		$this->setTXNid($row[$COLUMN['TXNID']], false);
		$this->setUid($row[$COLUMN['UID']], false);
		$this->setMembershipAge($row[$COLUMN['MEMBERSHIP_AGE']], false);
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PremiumCode::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PremiumCode::statement_get:
					PremiumCode::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CODE'].', '.$COLUMN['DURATION']
						.', '.$COLUMN['TXNID'].', '.$COLUMN['UID'].', '.$COLUMN['MEMBERSHIP_AGE']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PREMIUM_CODE']
						.' WHERE '.$COLUMN['CODE'].' = ?'
								, array('text'));
					break;
				case PremiumCode::statement_create:
					PremiumCode::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['PREMIUM_CODE'].'( '.$COLUMN['CODE'].', '.$COLUMN['DURATION']
						.') VALUES(?, ?)', array('text', 'integer'));
					break;	
				case PremiumCode::statement_setTXNid:
					PremiumCode::$statement[$statement] = DB::prepareSetter($TABLE['PREMIUM_CODE'], array($COLUMN['CODE'] => 'text'), $COLUMN['TXNID'], 'text');
					break;
				case PremiumCode::statement_setUid:
					PremiumCode::$statement[$statement] = DB::prepareSetter($TABLE['PREMIUM_CODE'], array($COLUMN['CODE'] => 'text'), $COLUMN['UID'], 'text');
					break;
				case PremiumCode::statement_setMembershipAge:
					PremiumCode::$statement[$statement] = DB::prepareSetter($TABLE['PREMIUM_CODE'], array($COLUMN['CODE'] => 'text'), $COLUMN['MEMBERSHIP_AGE'], 'integer');
					break;
			}
		}
	}

}
?>
