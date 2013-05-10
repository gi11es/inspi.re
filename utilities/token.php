<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	This class provides a way to prevent from spoofing by generating and checking tokens across requests
*/

require_once(dirname(__FILE__)."/cache.php");
require_once(dirname(__FILE__)."/db.php");
require_once(dirname(__FILE__)."/../constants.php");

class TokenException extends Exception {}

class Token {
	private $hash = "";
	
	const cache_prefix = 'Token-';

	// Creates a token, which lives as an entry in the cache. It can have a value and a seed for the hash generation
	public function __construct($value) {
		// loop to make sure that this new token id is unique
		$this->hash = str_replace('.', '', $_SERVER['SERVER_ADDR']).uniqid();
		
		try {
			Cache::set(Token::cache_prefix.$this->hash, $value, false, 3600);
		} catch (CacheException $e) {}
	}
	
	public function getHash() {
		return $this->hash;
	}

	// Returns true if the token exists, false otherwise
	public static function isValid($hash) {
		return (Token::get($hash) != null);
	}

	// Returns the contents of a token entry	
	public static function get($hash) {
		global $COLUMN;
		
		$token_value = null;
		
		try {
			$token_value = Cache::get(Token::cache_prefix.$hash);
		} catch (CacheException $e) {
			throw new TokenException('No volatile token for hash='.$hash);
		}
		
		return $token_value;
	}
}
?>
