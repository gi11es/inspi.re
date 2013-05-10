#!/usr/bin/php
<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Regenerate huge thumbnails to new size
*/

require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/picturelist.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();

$picturelist = PictureList::getByStatus($PICTURE_SIZE['HUGE'], $PICTURE_STATUS['THUMBNAILED']);

foreach ($picturelist as $pid) {
	$picture = Picture::get($pid);
	$picture->regenerateThumbnail($PICTURE_SIZE['HUGE'], true);
	echo 'Huge thumbnail for pid='.$pid.' was regenerated successfully'."\r\n";
}
?>