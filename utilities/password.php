<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	This class handles the password hashing and comparison
*/

require_once(dirname(__FILE__).'/../settings.php');

class Password {
	// Returns the hash of the password, based on a salt defined in settings.php
	public static function hashPassword($password) {
		global $PASSWORD_SALT;

		return sha1($PASSWORD_SALT . $password);
	}

	// Generates a new alphanumerical password
	public static function generatePassword($length = 8) {
		$password = '';

		// define possible characters
		$possible = '0123456789bcdfghjkmnpqrstvwxyz';

		// set up a counter
		$i = 0;

		// add random characters to $password until $length is reached
		while ($i < $length) {

			// pick a random character from the possible ones
			$char = mb_substr($possible, mt_rand(0, mb_strlen($possible) - 1), 1);

			// we don't want this character if it's already in the password
			if (!strstr($password, $char)) {
				$password .= $char;
				$i++;
			}

		}

		// done!
		return $password;
	}
}
?>
