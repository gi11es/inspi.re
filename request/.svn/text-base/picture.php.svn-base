<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Redirects the big picture request to the correct location
*/

require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../settings.php');

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) < time() - 172800) {
	header('Status: 304 Not Modified'); 
	header('Content-Type: image/jpeg');
	header("Expires: Sat, 26 Jul 2099 05:00:00 GMT");
	header("Last-Modified: Mon, 26 Jul 1999 05:00:00 GMT");
	header('Cache-Control: max-age=3600, must-revalidate');
	exit(0);
}

function sendImage($filename) {
	global $PICTURE_LOCAL_PATH;
	
	header('Content-Type: image/jpeg');
	header("Expires: Sun, 26 Jul 2099 05:00:00 GMT");
	header("Last-Modified: Mon, 26 Jul 1999 05:00:00 GMT");
	header('Cache-Control: max-age=3600, must-revalidate');
	
	$temp = fopen($PICTURE_LOCAL_PATH.$filename.'.jpg',"rb");
	fpassthru($temp);
}

if (isset($_REQUEST['filename']) && isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_USER_AGENT'])) {
	$real_filename = explode('-', $_REQUEST['filename']);
	if (file_exists($PICTURE_LOCAL_PATH.$real_filename[0].'.jpg')) try {
		Cache::get('ip-'.$_SERVER['REMOTE_ADDR'].'-'.$_SERVER['HTTP_USER_AGENT']);
		sendImage($real_filename[0]);
	} catch (CacheException $e) {
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'TinEye') !== false) sendImage($real_filename[0]); 
		else header("HTTP/1.0 404 Not Found");
	} else header("HTTP/1.0 404 Not Found");
} else header("HTTP/1.0 404 Not Found");

?>