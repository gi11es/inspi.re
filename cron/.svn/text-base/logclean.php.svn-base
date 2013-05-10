#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Remove old log files
 */

require_once(dirname(__FILE__).'/../settings.php');
require_once(dirname(__FILE__).'/../constants.php');

$valid_dates = array();

for ($day_shift = 0; $day_shift < $LOG_CYCLE; $day_shift++) {
	$valid_dates[]= date($LOG_DATE_FORMAT, time() - $day_shift * 86400);
}

foreach (scandir($LOG_FILE_PATH) as $filename) {
	$valid_date_found = false;
	foreach($valid_dates as $valid_date) {
		if (strstr($filename, $valid_date)) {
			$valid_date_found = true;
			break;
		}
	}
	
	if (!$valid_date_found) {
		if (!is_dir($filename))
			unlink($LOG_FILE_PATH.$filename);
	}
}

?>