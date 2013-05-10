<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Deletes an existing entry
*/

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();
$entry = null;

try {
	if (isset($_REQUEST['eid'])) {
		$entry = Entry::get($_REQUEST['eid']);
	} elseif (isset($_REQUEST['token'])) {
		$token = Token::get($_REQUEST['token']);
		$exploded = explode('-', $token);
		if (count($exploded) == 2) {
			$token_uid = $exploded[0];
			$eid = $exploded[1];
			if ($token_uid == $user->getUid() || $user->getUid() == $GOOGLE_UID)
				$entry = Entry::get($eid);
		}
	} elseif (isset($_REQUEST['persistenttoken'])) {
		$token = PersistentToken::get($_REQUEST['persistenttoken']);
		$exploded = explode('-', $token);
		if (count($exploded) == 2) {
			$token_uid = $exploded[0];
			$eid = $exploded[1];
			if ($token_uid == $user->getUid() || $user->getUid() == $GOOGLE_UID)
				$entry = Entry::get($eid);
		}
	}
} catch (EntryException $e) {} catch (TokenException $f) {} catch (PersistentTokenException $g) {}

if ($entry !== null) {
	if ($user->getUid() == $entry->getUid()) {
		$cid = $entry->getCid();
		$competition = Competition::get($cid);
		if ($competition->getStatus() == $COMPETITION_STATUS['CLOSED']) {
			$pid = $entry->getPid();
			try {
				//echo 'Deleting picture with pid='.$pid.'<br/>';
				$picture = Picture::get($pid);
				$picture->delete();
			} catch (PictureException $e) {}
			
			//echo 'Setting entry\'s pid to null<br/>';
			$entry->setPid(null);
			//echo 'Setting entry\'s status to deleted<br/>';
			if ($entry->getStatus() != $ENTRY_STATUS['BANNED'] && $entry->getStatus() != $ENTRY_STATUS['DISQUALIFIED'])
				$entry->setStatus($ENTRY_STATUS['DELETED']);
		}
	}
	header('Location: '.$PAGE['ENTRY'].'?lid='.$user->getLid().'&home=true#eid='.$entry->getEid());
} else header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());

?>