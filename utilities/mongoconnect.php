<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	The class managing everything related to MongoDB
*/

require_once(dirname(__FILE__).'/log.php');
require_once(dirname(__FILE__).'/../settings.php');

class MongoConnectException extends Exception {}

class MongoConnect {
	private static $started = false;
	private static $connection = null;
	private static $request_count = 0;
	private static $database = null;

	// Since the class is static we use that method to replace a constructor
	private static function initCheck() {
		global $MONGODB;
	
		// Create a new connection to memMongoDBd if there isn't any	
		if (!MongoConnect::$started) {
			Log::trace(__CLASS__, '*** starting ***');
			
			try {
				MongoConnect::$connection = new Mongo($MONGODB['SERVER'], true);
				MongoConnect::$database = MongoConnect::$connection->selectDB($MONGODB['DATABASE']);
			} catch (MongoConnectionException $e) {
				Log::critical(__CLASS__, 'MongoDB missing on '.$MONGODB['SERVER']);
				throw new MongoConnectException('Could not locate MongoDB on '.$MONGODB['SERVER']);
			}

			MongoConnect::$started = true;
			
			// Register a cleanup method that will be called automatically upon class destruction
			register_shutdown_function(array('MongoConnect', 'shutdown'));
		}
	}

	// Cleanup method, equivalent of a destructor
	public static function shutdown() {
		Log::trace(__CLASS__, '*** stopping ***');
		// Close the connection to MongoDB if it's still alive
		if (MongoConnect::$started && MongoConnect::$connection !== null) {
			MongoConnect::$connection->close();
		}
	}

	public static function getDB() {
		MongoConnect::initCheck();
		
		return MongoConnect::$database;
	}
}

?>