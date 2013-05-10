<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Updates the language preference of a given user
*/

require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');

if (isset($_REQUEST['left']) && isset($_REQUEST['top']) && isset($_REQUEST['width']) && isset($_REQUEST['height']) && isset($_REQUEST['pid'])) {
	$picture = Picture::get($_REQUEST['pid']);
		
	$original = PictureFile::get($picture->getFid($PICTURE_SIZE['ORIGINAL']));
	$huge = PictureFile::get($picture->getFid($PICTURE_SIZE['HUGE']));
	
	$ratio = floatval($original->getWidth()) / floatval($huge->getWidth());
	
	$picture->setOffsetX(intval(floatval($_REQUEST['left']) * $ratio));
	$picture->setOffsetY(intval(floatval($_REQUEST['top']) * $ratio));
	$picture->setDimension(intval(floatval($_REQUEST['width']) * $ratio));
	$picture->setStatus($PICTURE_SIZE['BIG'], $PICTURE_STATUS['RAW']);
	$picture->setStatus($PICTURE_SIZE['MEDIUM'], $PICTURE_STATUS['RAW']);
	$picture->setStatus($PICTURE_SIZE['SMALL'], $PICTURE_STATUS['RAW']);
}

?>

0