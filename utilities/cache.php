<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	The class managing everything related to caching (with memcached), mostly static
*/

require_once(dirname(__FILE__).'/log.php');
require_once(dirname(__FILE__).'/timecounter.php');
require_once(dirname(__FILE__).'/../settings.php');

class CacheException extends Exception {}

class Cache {
	private static $started = false;
	private static $memcache = null;
	private static $request_count = 0;
	private static $canbestale = array('Alert-', 'I18N-', 'User-', 'UserLevelListByUid-', 'Competition-', 'Community-', 'CommunityMembershipListByXidAndStatus-', 'CommunityLabelListByXid-');
	private static $stale = array();

	// Since the class is static we use that method to replace a constructor
	private static function initCheck() {
		global $MEMCACHE;
	
		// Create a new connection to memcached if there isn't any	
		if (!Cache::$started) {
			Log::trace(__CLASS__, '*** starting ***');
			
			Cache::$memcache = new Memcache;
			$result = Cache::$memcache->pconnect($MEMCACHE['HOST'], $MEMCACHE['PORT']);

			if (!$result) {
				Log::critical(__CLASS__, 'Cache missing on '.$MEMCACHE['HOST'].':'.$MEMCACHE['PORT']);
				throw new CacheException('Could not locate memcache on '.$MEMCACHE['HOST'].':'.$MEMCACHE['PORT']);
			}
			Cache::$started = true;
			
			// Register a cleanup method that will be called automatically upon class destruction
			register_shutdown_function(array('Cache', 'shutdown'));
		}
	}

	// Cleanup method, equivalent of a destructor
	public static function shutdown() {
		Log::trace(__CLASS__, '*** stopping ***');
		// Close the connection to memcached if it's still alive
		if (Cache::$started && Cache::$memcache) {
			Cache::$memcache->close();
		}
	}

	// Retrieves an entry from the cache	
	public static function get($key) {
		global $MEMCACHE;
		
		TimeCounter::start();
		
		$isstale = false;
		foreach (Cache::$canbestale as $prefix) if (strpos($key, $prefix) === 0) $isstale = true;
		
		if ($isstale && isset(Cache::$stale[$key])) return Cache::$stale[$key];
		
		Cache::initCheck();
		
		Cache::$request_count++;
		
		$start_time = microtime(true);

		$result = Cache::$memcache->get($MEMCACHE['PREFIX'].$key);
		if (!$result && is_bool($result))
			throw new CacheException('This key is missing or has expired');
			
		$time_difference = microtime(true) - $start_time;
		Log::trace(__CLASS__, 'retrieved object with key='.$MEMCACHE['PREFIX'].$key.' ('.$time_difference.')');

		if ($isstale) Cache::$stale[$key] = $result;
		
		TimeCounter::stop();

		return $result;
	}
	
	// Retrieves an entry from the cache	
	public static function getArray($keys) {
		global $MEMCACHE;
		
		TimeCounter::start();
		
		$results = array();
		$isstale = array();
		
		foreach ($keys as $key) {
			$isstale[$key] = false;
			foreach (Cache::$canbestale as $prefix) if (strpos($key, $prefix) === 0) $isstale[$key] = true;
			
			if ($isstale[$key] && isset(Cache::$stale[$key])) $results[$key] = Cache::$stale[$key];
		}
		
		$prefixed_keys = array();
		foreach ($keys as $key) if (!isset($results[$key])) $prefixed_keys[]= $MEMCACHE['PREFIX'].$key;
		
		if (!empty($prefixed_keys)) {
			Cache::initCheck();
		
			Cache::$request_count++;
			
			$start_time = microtime(true);
		
			$result = Cache::$memcache->get($prefixed_keys);
			if (!$result && is_bool($result))
				throw new CacheException('The keys is missing or has expired');
			else {
				foreach ($result as $key => $value) {
					$newkey = substr($key, strlen($MEMCACHE['PREFIX']));
					$results[$newkey] = $value;
					
					if ($isstale[$newkey]) Cache::$stale[$newkey] = $value;
				}
				
				$time_difference = microtime(true) - $start_time;
				
				Log::trace(__CLASS__, 'getting objects with keys '.implode(', ', $keys).' ('.$time_difference.')');			
			}
		}
		
		TimeCounter::stop();
		
		return $results;
	}

	// Retrieves the memcached stats (performance, hit and misses, amount of entities currently cached, etc)	
	public static function getStats() {
		global $MEMCACHE;
		Cache::initCheck();
		
		Cache::$request_count++;
		
		Log::trace(__CLASS__, 'getting stats');
		$result = Cache::$memcache->getStats();
		if (!$result && is_bool($result))
			throw new CacheException('Failed to obtain server stats');
			
		return $result;
	}
	
	// Assigns a cache entry to a given value/object, this can raise an exception if the entry is already set
	public static function set($key, $obj, $compressed=false, $duration=86400) {
		global $MEMCACHE;
		Cache::initCheck();
		
		Cache::$request_count++;
		
		Log::trace(__CLASS__, 'setting object with key='.$MEMCACHE['PREFIX'].$key);
		if (!Cache::$memcache->set($MEMCACHE['PREFIX'].$key, $obj, $compressed, $duration)) {
			Log::error(__CLASS__, 'Failed to set value in the cache for key='.$key);
			throw new CacheException('Failed to set value in the cache for key='.$key);
		}
		
		if (isset(Cache::$stale[$key])) Cache::$stale[$key] = $obj;
	}
	
	// Replace the value of an existing cache entry, this can raise an exception if the entry is not set yet
	public static function replace($key, $obj, $compressed=false, $duration=18000) {
		global $MEMCACHE;
		Cache::initCheck();
		
		Cache::$request_count++;
		
		Log::trace(__CLASS__, 'replacing object with key='.$MEMCACHE['PREFIX'].$key);
		if (!Cache::$memcache->replace($MEMCACHE['PREFIX'].$key, $obj, $compressed, $duration)) {
			Log::error(__CLASS__, 'Failed to replace value in the cache for key='.$key);
			throw new CacheException('Failed to replace value in the cache for key='.$key);
		}
		
		if (isset(Cache::$stale[$key])) Cache::$stale[$key] = $obj;
	}
	
	// Sets or replaces a cache entry, works regardless of the entry being already defined or not
	public static function setorreplace($key, $obj, $compressed=false, $duration=18000) {
		global $MEMCACHE;
		Cache::initCheck();
		
		Log::trace(__CLASS__, 'setting object with key='.$MEMCACHE['PREFIX'].$key);
		if (!Cache::$memcache->set($MEMCACHE['PREFIX'].$key, $obj, $compressed, $duration))
			Cache::replace($key, $obj, $compressed, $duration);
			
		if (isset(Cache::$stale[$key])) Cache::$stale[$key] = $obj;
	}
	
	// Sets or replaces a cache entry, works regardless of the entry being already defined or not
	public static function replaceorset($key, $obj, $compressed=false, $duration=18000) {
		global $MEMCACHE;
		Cache::initCheck();
		
		Log::trace(__CLASS__, 'setting object with key='.$MEMCACHE['PREFIX'].$key);
		if (!Cache::$memcache->replace($MEMCACHE['PREFIX'].$key, $obj, $compressed, $duration))
			Cache::set($key, $obj, $compressed, $duration);
			
		if (isset(Cache::$stale[$key])) Cache::$stale[$key] = $obj;
	}
	
	// Increments an integer value for a given key
	public static function increment($key) {
		global $MEMCACHE;
		Cache::initCheck();
		
		Cache::$request_count++;
		
		Log::trace(__CLASS__, 'incrementing object with key='.$MEMCACHE['PREFIX'].$key);
		if (!Cache::$memcache->increment($MEMCACHE['PREFIX'].$key))
			throw new CacheException('Failed to increment value in the cache for key='.$key);
	}
	
	// Decrements an integer value for a given key
	public static function decrement($key) {
		global $MEMCACHE;
		Cache::initCheck();
		
		Cache::$request_count++;
		
		Log::trace(__CLASS__, 'decrementing object with key='.$MEMCACHE['PREFIX'].$key);
		if (!Cache::$memcache->decrement($MEMCACHE['PREFIX'].$key))
			throw new CacheException('Failed to decrement value in the cache for key='.$key);
	}
	
	// Deletes a cache entry, raises an exception if the entry is not defined
	public static function delete($key) {
		global $MEMCACHE;
		Cache::initCheck();
		
		Cache::$request_count++;
		
		Log::trace(__CLASS__, 'deleting object with key='.$MEMCACHE['PREFIX'].$key);
		if (!Cache::$memcache->delete($MEMCACHE['PREFIX'].$key, 0))
			throw new CacheException('Failed to delete value in the cache for key='.$key);
	}
	
	// Flushed the whole cache, USE VERY CAUTIOUSLY, flushing the whole cache will slow down the website considerably
	public static function flush() {
		Cache::initCheck();
		
		Cache::$request_count++;
		
		Log::trace(__CLASS__, 'Flushing cache');
		if (!Cache::$memcache->flush())
			throw new CacheException('Failed to flush the cache');
	}
	
	public static function lock($key, $wait_time=5) {
		global $MEMCACHE;
		Cache::initCheck();
		
		Cache::$request_count++;

		$start_time = time();
		$time_lost = 0;
		
		$locked = false;
		
		do {
			$locked = Cache::$memcache->add($MEMCACHE['PREFIX'].$key, time());
			if (!$locked) {
				sleep(1);
				$time_lost++;
			}
		} while (!$locked && (time() - $start_time) < $wait_time);
		
		if ($time_lost > 0)
			Log::debug(__CLASS__, $time_lost.' second(s) lost in waiting for lock on key='.$key);
		
		if (!$locked) Log::error(__CLASS__, 'Had to give up on locking key='.$key.' after '.$wait_time.' second(s)');
		else Log::trace(__CLASS__, 'Locked key='.$MEMCACHE['PREFIX'].$key);
		
		return $locked;
	}
	
	public static function unlock($key) {
		try {
			Cache::delete($key);
		} catch (CacheException $e) {}
		
		Log::trace(__CLASS__, 'Unlocked key='.$key);
	}
	
	public static function getRequestCount() {
		return Cache::$request_count;
	}
}

?>