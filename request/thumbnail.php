<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Redirects the thumbnail request to the correct location
*/

require_once(dirname(__FILE__).'/../entities/picture.php');

/*if (isset($_REQUEST['compound_id'])) {
	$value = explode('-', $_REQUEST['compound_id']);
	if (isset($value[0]) && isset($value[1])) {
		try {
			$picture = Picture::get($value[0]);
			header('HTTP/1.1 301 Moved Permanently');
			header('Content-Type: image/jpeg');
			header('Location: '.$picture->getRealThumbnail($value[1]));
		} catch (PictureException $e) {
			
		}
	}
}*/

if (isset($_REQUEST['compound_id'])) {
	$value = explode('-', $_REQUEST['compound_id']);
	if (isset($value[0]) && isset($value[1])) {
		try {
			$picture = Picture::get($value[0]);
			$thumbnail = $picture->getRealThumbnail($value[1]);
			header('HTTP/1.1 301 Moved Permanently');
			header('Content-Type: image/jpeg');
			header('Location: '.$thumbnail);
		} catch (PictureException $e) {
			
		}
	}
}

?>