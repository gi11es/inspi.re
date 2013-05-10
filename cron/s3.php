#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Batch-transfers local image files to Amazon S3
 */

require_once(dirname(__FILE__).'/../entities/picturefile.php');
require_once(dirname(__FILE__).'/../entities/picturefilelist.php');
require_once(dirname(__FILE__).'/../utilities/s3.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../utilities/url.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2/Date.php';

if (System::isOtherCopyRunning('s3.php')) {
	echo 'Had to abort S3 cron job, it was already running';
	exit(0);
} elseif (System::getFreeSpace() < 10) { // Only transfer to S3 if there's less than 10% disk space left
	$fids = PictureFileList::getByStatus($PICTURE_FILE_STATUS['LOCAL']);
	$fids = array_splice($fids, 0, 20); // transfer no more than 20 files in one go

	foreach ($fids as $fid) {
		try {
				$picture_file = PictureFile::get($fid);
				if (URL::check($S3_PATH.$fid.'.jpg')) {
					$picture_file->setStatus($PICTURE_FILE_STATUS['LOCAL_AND_S3']);
					$picture = Picture::get($picture_file->getPid());
					$picture->setTimestamp($picture_file->getSize(), time());
				}
				elseif (file_exists($PICTURE_LOCAL_PATH.$fid.'.jpg'))
					S3::put($S3_BUCKET['IMAGES'], $PICTURE_LOCAL_PATH.$fid.'.jpg');
		} catch (PictureFileException $e) {
			echo 'Exception occured on picture file with fid='.$fid;
		}
	}
}

if (System::isOtherCopyRunning('s3.php')) {
	echo 'Had to abort S3 cron job, it was already running';
	exit(0);
} else {
	$fids = PictureFileList::getByStatus($PICTURE_FILE_STATUS['LOCAL_AND_S3']);
	foreach ($fids as $fid) {
		try {
			$picture_file = PictureFile::get($fid);
			$picture = Picture::get($picture_file->getPid());
			
			Cache::lock('File-'.$PICTURE_LOCAL_PATH.$fid.'.jpg');

			// Only unlink file if more than one minute has passed since it was detected as present on S3
			// That way we avoid serving a URL to a user that points to our local file
			if ((time() - $picture->getTimestamp($picture_file->getSize())) > 60 && file_exists($PICTURE_LOCAL_PATH.$fid.'.jpg')) {
				unlink($PICTURE_LOCAL_PATH.$fid.'.jpg');
				$picture_file->setStatus($PICTURE_FILE_STATUS['S3']);
			}
			
			Cache::unlock('File-'.$PICTURE_LOCAL_PATH.$fid.'.jpg');
		} catch (PictureFileException $e) {
			echo 'Exception occured on picture file with fid='.$fid;
		}
	}
}

?>