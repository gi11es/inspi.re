<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Articles for which the trackback has been received
*/

require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class TrackbackException extends Exception {}

class Trackback implements Persistent {
	private $url;
	private $creation_time;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	
    const cache_prefix = 'Trackback-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of trackback with url='.$this->url);
		
		try {
			Cache::replaceorset(Trackback::cache_prefix.$this->url, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of trackback with url='.$this->url);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 1)
			self::__construct2($argv[0]);
    }
	
	public function __construct2($url) {
		Trackback::prepareStatement(Trackback::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Trackback::$statement[Trackback::statement_create]->execute($url);
		Log::trace('DB', 'Executed Trackback::statement_create ['.$url.'] ('.(microtime(true) - $start_timestamp).')');

		$this->setUrl($url);
		$this->setCreationTime(time());
		$this->saveCache();
	}
	
	public static function get($url) {
		if ($url === null) throw new TrackbackException('No trackback for url='.$url);
		
		try {
			$trackback = Cache::get(Trackback::cache_prefix.$url);
		} catch (CacheException $e) {
			Trackback::prepareStatement(Trackback::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = Trackback::$statement[Trackback::statement_get]->execute($url);
			Log::trace('DB', 'Executed Trackback::statement_get ['.$url.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new TrackbackException('No trackback for url='.$url);
			
			$row = $result->fetchRow();
			$result->free();
			
			$trackback = new Trackback();
			$trackback->populateFields($row);
			$trackback->saveCache();
		}
		return $trackback;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setUrl($row[$COLUMN['URL']]);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
	}
	
	public function getUrl() { return $this->url; }
	
	public function setUrl($new_url) { $this->url = $new_url; }
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time) { $this->creation_time = $new_creation_time; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(Trackback::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case Trackback::statement_get:
					Trackback::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['URL']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['TRACKBACK']
						.' WHERE '.$COLUMN['URL'].' = ?'
								, array('text'));
					break;
				case Trackback::statement_create:
					Trackback::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['TRACKBACK']
						.'( '.$COLUMN['URL']
						.') VALUES(?)', array('text'));
					break;
			}
		}
	}
}

?>