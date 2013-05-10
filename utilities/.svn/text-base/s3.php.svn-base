<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Handles basic S3 operations, relies on s3funnel
*/

require_once(dirname(__FILE__).'/log.php');
require_once(dirname(__FILE__).'/../settings.php');

class S3 {
	// Adds a local file to S3
	public static function put($bucket, $file) {
		global $S3;
		
		Log::trace(__CLASS__, 'put '.$bucket.' '.$file);
		
		exec($S3['FUNNEL_PATH'].' '.$bucket.' put '.$file.' -a '.$S3['ID'].' -s '.$S3['KEY'], $last_line, $retval);
		
		return ($retval == 0);
	}
	
	// Remove a file from S3
	public static function delete($buket, $filename) {
		global $S3;
		
		Log::trace(__CLASS__, 'delete '.$bucket.' '.$file);
		
		exec($S3['FUNNEL_PATH'].' '.$bucket.' delete '.$file.' -a '.$S3['ID'].' -s '.$S3['KEY'], $last_line, $retval);
		
		return ($retval == 0);
	}
}

?>