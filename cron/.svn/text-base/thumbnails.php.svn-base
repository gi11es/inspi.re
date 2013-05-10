#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Batch-convert pictures to generate thumbnails
 */

require_once(dirname(__FILE__).'/../entities/picturelist.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../utilities/url.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('thumbnails.php')) {
	echo 'Had to abort thumbnails cron job, it was already running';
} else {
	$sizes = $PICTURE_SIZE;
	unset($sizes['ORIGINAL']);
	unset($sizes['TINY']);
	
	foreach ($sizes as $size) {
		$pids = PictureList::getByStatus($size, $PICTURE_STATUS['FIRST']);
		$picturecache = Picture::getArray($pids);
		
		foreach ($picturecache as $pid => $picture) try {
			$picture->regenerateThumbnail($size);
		} catch (PictureException $e) {
			$picture->delete();
		}
	}
	
	unset($sizes['HUGE']);
	
	foreach ($sizes as $size) {
		$pids = PictureList::getByStatus($size, $PICTURE_STATUS['RAW']);
		$picturecache = Picture::getArray($pids);
		
		foreach ($picturecache as $pid => $picture) $picture->regenerateThumbnail($size);
	}
}

?>