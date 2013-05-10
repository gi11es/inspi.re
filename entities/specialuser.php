<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Stores a user's reference
*/

require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class SpecialUserException extends Exception {}

class SpecialUser implements Persistent {
	private $category;
	private $uid;
	private $value;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setUid = 4;
	const statement_setValue = 5;
	
    const cache_prefix = 'SpecialUser-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of special user with category='.$this->category);
		
		try {
			Cache::replaceorset(SpecialUser::cache_prefix.$this->category, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of special user with category='.$this->category);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 3)
			self::__construct2($argv[0], $argv[1], $argv[2]);
    }
	
	public function __construct2($category, $uid, $value) {
		SpecialUser::prepareStatement(SpecialUser::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		SpecialUser::$statement[SpecialUser::statement_create]->execute(array($category, $uid, $value));
		Log::trace('DB', 'Executed SpecialUser::statement_create ['.$category.', '.$uid.', '.$value.'] ('.(microtime(true) - $start_timestamp).')');

		$this->setCategory($category);
		$this->setUid($uid, false);
		$this->setValue($value, false);
		$this->saveCache();
	}
	
	public static function get($category) {
		if ($category === null) throw new SpecialUserException('No special user for category='.$category);
		
		try {
			$specialuser = Cache::get(SpecialUser::cache_prefix.$category);
		} catch (CacheException $e) {
			SpecialUser::prepareStatement(SpecialUser::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = SpecialUser::$statement[SpecialUser::statement_get]->execute($category);
			Log::trace('DB', 'Executed SpecialUser::statement_get ['.$category.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new SpecialUserException('No special user for category='.$category);
			
			$row = $result->fetchRow();
			$result->free();
			
			$specialuser = new SpecialUser();
			$specialuser->populateFields($row);
			$specialuser->saveCache();
		}
		return $specialuser;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setCategory($row[$COLUMN['CATEGORY']]);
		$this->setUid($row[$COLUMN['UID']], false);
		$this->setValue($row[$COLUMN['VALUE']], false);
	}
	
	public function delete() {
		SpecialUser::prepareStatement(SpecialUser::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = SpecialUser::$statement[SpecialUser::statement_delete]->execute($this->category);
		Log::trace('DB', 'Executed SpecialUser::statement_delete ['.$this->category.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(SpecialUser::cache_prefix.$this->category); } catch (CacheException $e) {}
	}
	
	public function getCategory() { return $this->category; }
	
	public function setCategory($new_category) { $this->category = $new_category; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid, $persist=true) {
		$old_uid = $this->uid;
		$this->uid = $new_uid;
		
		if ($persist) {
			SpecialUser::prepareStatement(SpecialUser::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			SpecialUser::$statement[SpecialUser::statement_setUid]->execute(array($this->uid, $this->category));
			Log::trace('DB', 'Executed SpecialUser::statement_setUid ['.$this->uid.', '.$this->category.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getValue() { return $this->value; }
	
	public function setValue($new_value, $persist=true) {
		$old_value = $this->value;
		$this->value = $new_value;
		
		if ($persist) {
			SpecialUser::prepareStatement(SpecialUser::statement_setValue);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			SpecialUser::$statement[SpecialUser::statement_setValue]->execute(array($this->value, $this->category));
			Log::trace('DB', 'Executed SpecialUser::statement_setValue ['.$this->value.', '.$this->category.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(SpecialUser::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case SpecialUser::statement_get:
					SpecialUser::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CATEGORY'].', '.$COLUMN['UID'].', '.$COLUMN['VALUE']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['SPECIAL_USER']
						.' WHERE '.$COLUMN['CATEGORY'].' = ?'
								, array('integer'));
					break;
				case SpecialUser::statement_create:
					SpecialUser::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['SPECIAL_USER']
						.'( '.$COLUMN['CATEGORY'].', '.$COLUMN['UID'].', '.$COLUMN['VALUE']
						.') VALUES(?, ?, ?)', array('integer', 'text', 'integer'));
					break;	
				case SpecialUser::statement_delete:
					SpecialUser::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['SPECIAL_USER']
						.' WHERE '.$COLUMN['CATEGORY'].' = ?'
						, array('integer'));
					break;
				case SpecialUser::statement_setUid:
					SpecialUser::$statement[$statement] = DB::prepareSetter($TABLE['SPECIAL_USER'], array($COLUMN['CATEGORY'] => 'integer'), $COLUMN['UID'], 'text');
					break;
				case SpecialUser::statement_setValue:
					SpecialUser::$statement[$statement] = DB::prepareSetter($TABLE['SPECIAL_USER'], array($COLUMN['CATEGORY'] => 'integer'), $COLUMN['VALUE'], 'integer');
					break;
			}
		}
	}
}

?>