<?php

/** 
 * Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 *	
 * Provides a way to prevent an attacker from redirecting a logged-in user to an unwanted action url
 *	
 * For example if the password reset is triggered by a request on /request/resetpassword.php we'd add
 * a persistent token as a parameter to make sure that the link was clicked on the page it should 
 * have come from.
 *	
 * It also allows to create links that are unique to a given user and can't be given to others (eg.
 * a link to a member's own entry when the voting is still on and anonymity should be respected).
 *	
 * There's a more volatile version of this class (Token) that only stores the data into the cache, for
 * different use cases where it's ok to lose them if the cache fails.
 */

require_once(dirname(__FILE__)."/cache.php");
require_once(dirname(__FILE__)."/db.php");
require_once(dirname(__FILE__)."/../constants.php");

class PersistentTokenException extends Exception {}

class PersistentToken {
	private $hash = "";
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	
	const cache_prefix = 'PersistentToken-';

	public function __construct($value) {
		/** 
		 * Works as long as all out host IPs are unique, could use something else specific to the machine
		 * It's still very unlikely that uniqid would collide even on seperate machines, but having the 
		 * host-specific prefix prevents that from happening 
		 */
		$this->hash = str_replace('.', '', $_SERVER['SERVER_ADDR']).uniqid();
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		PersistentToken::prepareStatement(PersistentToken::statement_create);
		$result = PersistentToken::$statement[PersistentToken::statement_create]->execute(array($this->hash, $value));
		Log::trace('DB', 'Executed PersistentToken::statement_create ['.$this->hash.', '.$value.'] ('.(microtime(true) - $start_timestamp).')');
		
		// Cache it if possible
		try { 
			Cache::set(PersistentToken::cache_prefix.$this->hash, $value);
		} catch (CacheException $e) {}
	}
	
	/**
	 * The hash is what we pass in URL parameters. The idea is that the content can't be guessed from it
	 */
	public function getHash() {
		return $this->hash;
	}

	/**
	 * Return true if the token exists, false otherwise
	 */
	public static function isValid($hash) {
		try {
			return (PersistentToken::get($hash) != null);
		} catch (PersistentTokenException $e) {
			return false;
		}
	}

	/**
	 * Returns the contents of a token entry (and not the object itself)
	 * It makes sense to do it that way because on the receiving URL we only care about the token's content
	 * to check if it's what we expected (eg. user id collated with target page name)
	 */
	public static function get($hash) {
		global $COLUMN;
		
		$token_value = null;
		
		// We try looking into the cache first
		try {
			$token_value = Cache::get(PersistentToken::cache_prefix.$hash);
		} catch (CacheException $e) {
			PersistentToken::prepareStatement(PersistentToken::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PersistentToken::$statement[PersistentToken::statement_get]->execute($hash);
			Log::trace('DB', 'Executed PersistentToken::statement_get ['.$hash.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new PersistentTokenException('No persistent token for hash = '.$hash);
			
			$row = $result->fetchRow();
			$result->free();
			
			$token_value = $row[$COLUMN['VALUE']];
			
			// Try to cache it, since it was missing from the cache when we looked for it
			try {
				Cache::setorreplace(PersistentToken::cache_prefix.$hash, $token_value);
			} catch (CacheException $e) {}
		}
		
		return $token_value;
	}
	
	/**
	 * Process prepared DB statements when needed
	 */
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PersistentToken::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PersistentToken::statement_get:
					PersistentToken::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['VALUE']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['TOKEN']
						.' WHERE '.$COLUMN['HASH'].' = ?'
								, array('text'));
					break;
				case PersistentToken::statement_create:
					PersistentToken::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['TOKEN'].'( '.$COLUMN['HASH'].', '.$COLUMN['VALUE']
						.') VALUES(?, ?)', array('text', 'text'));
					break;	
			}
		}
	}

}
?>
