<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Debugging tool that allows us to time something specific on pages, to check if it needs optimization
*/

require_once(dirname(__FILE__).'/log.php');
require_once(dirname(__FILE__).'/../settings.php');

class TimeCounter {
	private static $time = 0.0;
	private static $start = 0.0;

	public static function getTime() {
		return TimeCounter::$time;
	}
	
	public static function start() {
		TimeCounter::$start = microtime(true);
	}
	
	public static function stop() {
		if (TimeCounter::$start > 0.0)
			TimeCounter::$time += microtime(true) - TimeCounter::$start;
	}
}

?>