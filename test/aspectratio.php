#!/usr/bin/php
<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Calculate the most common aspect ratios
*/

require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/picturefile.php');
require_once(dirname(__FILE__).'/../entities/picturelist.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();

$picturelist = PictureList::getByStatus($PICTURE_SIZE['HUGE'], $PICTURE_STATUS['THUMBNAILED']);

$ratios = array();

foreach ($picturelist as $pid) try {
	$picture = Picture::get($pid);
	$picturefile = PictureFile::get($picture->getFid($PICTURE_SIZE['ORIGINAL']));
	$width = $picturefile->getWidth();
	$height = $picturefile->getHeight();
	$aspectratio = floatval(min($width, $height)) / floatval(max($width, $height));
	if (!isset($ratios[strval(round($aspectratio, 2))])) $ratios[strval(round($aspectratio, 2))] = 1;
	else $ratios[strval(round($aspectratio, 2))]++;
} catch (PictureFileException $e) {}

foreach ($ratios as $aspectratio => $count) {
	echo $aspectratio.','.$count."\r\n";
}
?>