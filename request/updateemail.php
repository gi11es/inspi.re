<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Updates the email address of a given user
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

if (isset($_REQUEST['new_email'])) {
	$persistenttoken = new PersistentToken($user->getUid().'$'.$_REQUEST['new_email']);
	try {
		Email::mail($_REQUEST['new_email'], $user->getLid(), 
					'NEW_EMAIL', 
					array('new_email_link' => $REQUEST['UPDATE_EMAIL'].'?hash='.$persistenttoken->getHash())
					);
		header('Location: '.$PAGE['CHANGE_EMAIL'].'?lid='.$user->getLid().'&progress=true');
	} catch (EmailException $e) {
		header('Location: '.$PAGE['CHANGE_EMAIL'].'?lid='.$user->getLid().'&progress=false');
	}
} elseif (isset($_REQUEST['hash'])) {
	try {
		$persistenttoken = PersistentToken::get($_REQUEST['hash']);
		$persistenttoken = explode('$', $persistenttoken);
		if (isset($persistenttoken[0]) && isset($persistenttoken[1])) {
			try {
				$new_email_user = User::get($persistenttoken[0]);
				$new_email_user->setEmail($persistenttoken[1]);
				header('Location: '.$PAGE['CHANGE_EMAIL'].'?lid='.$new_email_user->getLid().'&success=true');
			} catch (UserException $e) {
				header('Location: '.$PAGE['CHANGE_EMAIL'].'?lid='.$user->getLid().'&progress=false');
			}
		} else header('Location: '.$PAGE['CHANGE_EMAIL'].'?lid='.$user->getLid().'&progress=false');
	} catch (TokenException $e) {
		header('Location: '.$PAGE['CHANGE_EMAIL'].'?lid='.$user->getLid().'&progress=false');
	}
} else header('Location: '.$PAGE['CHANGE_EMAIL'].'?lid='.$user->getLid().'&progress=false');

?>