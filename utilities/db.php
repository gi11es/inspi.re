<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	DB abstraction class, relies on PEAR::Mdb2
 	
 	One of the big advantages of MDB2 is prepared statements support, which provides all the heavy
 	duty SQL injection protection mechanisms.
*/

require_once(dirname(__FILE__).'/log.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

require_once 'MDB2.php';

class DBException extends Exception {}

class DB {
	private static $started = false;
	private static $mysql = null;
	private static $request_count = 0;

	// Since the class is static we use that method to replace a constructor
	private static function initCheck(){
		global $DATABASE;

		if (!DB::$started) {
			Log::trace(__CLASS__, '*** starting ***');

			 //MDB2 Data Source Name
	        $dsn = array(
	                'phptype'  => 'mysql',
	                'hostspec' => $DATABASE['HOST'],
	                'username' => $DATABASE['USER'],
	                'password' => $DATABASE['PASSWORD'],
	                'database' => $DATABASE['NAME'],
	                'charset' => 'UTF8'
	        );
	        //MDB2 options
	        $options = array(
	                'debug'       => 0,
	                'portability' => MDB2_PORTABILITY_ALL
	        );

			DB::$mysql = MDB2::singleton($dsn, $options);
			DB::$mysql->connect();
			if (PEAR::isError(DB::$mysql)) {
				Log::critical(__CLASS__, 'Can\'t connect to the database. '.DB::$mysql->getMessage());
				throw new DBException('Can\'t connect to the database. '.DB::$mysql->getMessage());
			}
				
			DB::$mysql->setFetchMode(MDB2_FETCHMODE_ASSOC);
			//DB::$mysql->query('set NAMES 'UTF8'');

			// Register a cleanup method that will be called automatically upon class destruction
			register_shutdown_function(array('DB', 'shutdown'));
			DB::$started = true;
		}
	}

	// Cleanup method, equivalent of a destructor
	public static function shutdown() {
		if (DB::$started) {
			Log::trace(__CLASS__, '*** stopping ***');
			DB::$mysql->disconnect();
			DB::$started = false;
		}
	}	
	
	// Prepare a write statement
	public static function prepareWrite($query, $types) {
		DB::initCheck();
	
		return DB::$mysql->prepare($query, $types, MDB2_PREPARE_MANIP);
	}
	
	public static function prepareSetter($table, $keys, $field, $field_type='integer') {
		global $DATABASE;
		
		$key_string = '';
		$types = array($field_type);
		foreach ($keys as $key => $key_type) {
			$key_string .= $key.' = ? AND ';
			$types []= $key_type;
		}
		$key_string = substr($key_string, 0, -5); // strip off the last "AND"

		return DB::prepareWrite( 
				'UPDATE '.$DATABASE['PREFIX'].$table.' SET '.$field.' = ? WHERE '.$key_string, $types);
	}

	// Prepare a read statement	
	public static function prepareRead($query, $types) {
		DB::initCheck();
	
		return DB::$mysql->prepare($query, $types, MDB2_PREPARE_RESULT);
	}

	// Oldschool unprepared query	
	public static function query($query) {
		DB::initCheck();
		
		DB::$request_count++;
	
		return DB::$mysql->query($query);
	}
	
	// Returns the latest auto-increment id that's been inserted
	public static function insertid() {
		return DB::$mysql->lastInsertID();
	}
	
	public static function incrementRequestCount() {
		DB::$request_count++;
	}
	
	public static function getRequestCount() {
		return DB::$request_count;
	}
}

?>