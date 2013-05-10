<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Provides the original file for a given entry
*/

require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/picturefile.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();

$eid = isset($_REQUEST['eid'])?$_REQUEST['eid']:null;

$result = array();

if ($eid !== null) {
	try {
		$entry = Entry::get($eid);
		$author = User::get($entry->getUid());
		$picture = Picture::get($entry->getPid());
		$fid = $picture->getFid($PICTURE_SIZE['ORIGINAL']);
		$filename = $fid.'.jpg';
		$filepath = $PICTURE_LOCAL_PATH.$filename;
		$filesize = filesize($filepath); 
		
		header('Content-type: image/jpeg');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="inspire-'.$eid.'-'.$author->getUniqueName().'.jpg"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.$filesize);
		
		$file = @fopen($filepath,"rb");
		if ($file) {
			while(!feof($file)) {
				print(fread($file, 1024*8));
				flush();
				if (connection_status()!=0) {
					@fclose($file);
					die();
				}
			}
			@fclose($file);
		}
	} catch (EntryException $e) {}
}


?>