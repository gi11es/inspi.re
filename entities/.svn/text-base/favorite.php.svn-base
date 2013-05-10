<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Favorite entries saved by users
*/

require_once(dirname(__FILE__).'/../entities/favoritelist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class FavoriteException extends Exception {}

class Favorite implements Persistent {
	private $eid;
	private $uid;
	private $creation_time;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'Favorite-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of favorite with eid='.$this->eid.' and uid='.$this->uid);
		
		try {
			Cache::replaceorset(Favorite::cache_prefix.$this->eid.'-'.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of favorite with eid='.$this->eid.' and uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($eid, $uid) {
		Favorite::prepareStatement(Favorite::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Favorite::$statement[Favorite::statement_create]->execute(array($eid, $uid));
		Log::trace('DB', 'Executed Favorite::statement_create ['.$eid.', "'.$uid.'"] ('.(microtime(true) - $start_timestamp).')');

		$this->setEid($eid);
		$this->setUid($uid);
		$this->setCreationTime(time());
		$this->saveCache();
		
		FavoriteList::deleteByEid($eid);
		FavoriteList::deleteByUid($uid);
	}
	
	public static function get($eid, $uid) {
		if ($eid === null || $uid === null) throw new FavoriteException('No favorite for eid='.$eid.' and uid='.$uid);
		
		try {
			$favorite = Cache::get(Favorite::cache_prefix.$eid.'-'.$uid);
		} catch (CacheException $e) {
			Favorite::prepareStatement(Favorite::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = Favorite::$statement[Favorite::statement_get]->execute(array($eid, $uid));
			Log::trace('DB', 'Executed Favorite::statement_get ['.$eid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new FavoriteException('No favorite for eid='.$eid.' and uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$favorite = new Favorite();
			$favorite->populateFields($row);
			$favorite->saveCache();
		}
		return $favorite;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setEid($row[$COLUMN['EID']]);
		$this->setUid($row[$COLUMN['UID']]);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
	}
	
	public function delete() {
		Favorite::prepareStatement(Favorite::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = Favorite::$statement[Favorite::statement_delete]->execute(array($this->eid, $this->uid));
		Log::trace('DB', 'Executed Favorite::statement_delete ['.$this->eid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(Favorite::cache_prefix.$this->eid.'-'.$this->uid); } catch (CacheException $e) {}
		
		FavoriteList::deleteByEid($this->eid);
		FavoriteList::deleteByUid($this->uid);
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
		
		if (!isset(Favorite::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case Favorite::statement_get:
					Favorite::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID'].', '.$COLUMN['UID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['FAVORITE']
						.' WHERE '.$COLUMN['EID'].' = ? AND '.$COLUMN['UID'].' = ?'
								, array('integer', 'text'));
					break;
				case Favorite::statement_create:
					Favorite::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['FAVORITE']
						.'( '.$COLUMN['EID'].', '.$COLUMN['UID']
						.') VALUES(?, ?)', array('integer', 'text'));
					break;	
				case Favorite::statement_delete:
					Favorite::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['FAVORITE']
						.' WHERE '.$COLUMN['EID'].' = ? AND '.$COLUMN['UID'].' = ?'
						, array('integer', 'text'));
					break;
			}
		}
	}
}

?>