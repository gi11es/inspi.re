<?php
    
/* 
 	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Provides shell helper functions
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');

class System {
	// Check if a process or script with a given name is currently running
	public static function isOtherCopyRunning($string) {
		$shell_result = array();
		exec('ps ax | grep '.$string.' | grep -v grep | grep -v '.posix_getpid(), $shell_result);
		
		return (!empty($shell_result));
	}
	
	// Returns the percentage of free space on the host
	public static function getFreeSpace() {
		global $SERVER_DISK;
		$shell_result = array();
		exec('df -h | grep '.$SERVER_DISK.' | awk \'{print $5}\'', $shell_result);
		if (isset($shell_result[0]))
			return 100 - intval(str_replace('%', '', $shell_result[0]));
		else
			return 100;
	}

	// Combine two text files into one	
	public static function mergeFiles($destination, $sources) {
		$first = true;
		foreach ($sources as $source) {
			exec('cat '.$source.' '.($first?'':'>').'> '.$destination);
			$first = false;
		}
	}
}

?>