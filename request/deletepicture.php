<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Deletes an existing picture
*/

require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');

$user = User::getSessionUser();

$pid = isset($_REQUEST['pid'])?$_REQUEST['pid']:null;

if ($pid !== null) {
	try {
		$pictures = Picture::get($pid);
	} catch (PictureException $e) {
		header('Location: '.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid());
		exit(0);
	}
	
	$levels = UserLevelList::getByUid($user->getUid());
	
	if (in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) { // Check that this is not a forged request
		$picture = Picture::get($pid);
		$picture->delete();
		
		echo 'Picture with pid='.$pid.' deleted';
	}
}

?>