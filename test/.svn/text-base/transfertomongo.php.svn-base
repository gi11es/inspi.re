#!/usr/bin/php
<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Transfer all the SQL data to mongo
*/

require_once(dirname(__FILE__).'/../entities/picturefile.php');
require_once(dirname(__FILE__).'/../entities/picturefilelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$picturefilelist = PictureFileList::getByStatus($PICTURE_FILE_STATUS['LOCAL']);
foreach ($picturefilelist as $fid) {
	$picturefile = PictureFile::get($fid);
}
?>