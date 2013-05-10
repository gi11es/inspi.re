<?php
    
/* 
	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	This class handles all the logging to files
*/

require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../libraries/XMPPHP/XMPP.php');
require_once(dirname(__FILE__).'/email.php');
require_once(dirname(__FILE__).'/inml.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

class Log {
	private static $jabber_connection = null;

	/**
	 * Appends an error message to a log file, check settings.php for the log rotation settings
	 * The logging is skipped if the current log level is higher than the level of the error
	 */
	private static function write($level, $classname, $message) {
		global $CURRENT_LOG_LEVEL;
		global $LOG_LEVEL;
		global $LOG_FILE;
		global $LOG_TIME_FORMAT;
		global $LOG_FILE_PATH;
		
		if ($CURRENT_LOG_LEVEL <= $LOG_LEVEL[$level]) {
			if (!file_exists($LOG_FILE[$classname])) {
				$fp = fopen($LOG_FILE[$classname], 'w+');
				fclose($fp);
				chmod($LOG_FILE[$classname], 0666);
			}
			$fp = fopen($LOG_FILE[$classname], 'a+');
			fwrite($fp, date($LOG_TIME_FORMAT).' '.$level.' '.$message."\n");
			fclose($fp);
		}
	}
	
	public static function xmpp($channel, $message) {
		global $COMET_SERVER;
		global $COMET_PORT;
		global $COMET_CHANNEL;
		global $LANGUAGE;
		
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$connected = @socket_connect($socket, $COMET_SERVER, $COMET_PORT);
		
		if ($connected) {	
			if (strcasecmp($channel, 'GENERAL_ACTIVITY') == 0) foreach ($LANGUAGE as $code => $lid) {
				$user = new User(); // Workaround since all I18N/INML methods expect a user
				$user->setLid($lid);
			
				$translated_html = I18N::translateHTML($user, $message);
				$tagged_html = INML::processHTML($user, $translated_html);
				$translated_message = I18N::translateHTML($user, $tagged_html);
	
				$command = array('command' => 'publish', 'channel' => $COMET_CHANNEL[$channel].$lid, 'text' => $translated_message);
				$command = json_encode($command);
				socket_write($socket, $command, strlen ($command));
				socket_write ($socket, "\r\n", strlen ("\r\n"));
			} else {
				$user = new User();
				$tagged_html = INML::processHTML($user, $message);
				
				$command = array('command' => 'publish', 'channel' => $COMET_CHANNEL[$channel], 'text' => $tagged_html);
				$command = json_encode($command);
				socket_write($socket, $command, strlen ($command));
				socket_write ($socket, "\r\n", strlen ("\r\n"));
			}
			
			socket_shutdown($socket, 2);
			socket_close($socket);
		}
	}
	
	public static function trace($classname, $message) {
		Log::write('TRACE', $classname, $message);
	}
	
	public static function debug($classname, $message) {
		Log::write('DEBUG', $classname, $message);
	}
	
	public static function info($classname, $message) {
		Log::write('INFO', $classname, $message);
	}
	
	public static function error($classname, $message) {
		Log::write('ERROR', $classname, $message);
	}

	/*
	 * If an error is critical (eg. cache or database down), we email the sysadmins. 
	 * Potential improvement: use the twitter API so that they receive it as a text message
	 */	
	public static function critical($classname, $message) {
		global $EMAIL_SUBJECT;
		global $USER_LEVEL;
		
		Log::write('CRITICAL', $classname, $message);
	}
}

?>