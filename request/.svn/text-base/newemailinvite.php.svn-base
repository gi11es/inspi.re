<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Send an email invitation to a list of email addresses
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

if (isset($_REQUEST['email_list_input'])) {
	$message = isset($_REQUEST['invite_text_input'])?stripslashes($_REQUEST['invite_text_input']):'';

	preg_match_all('/[\w-\.]+@([\w-]+\.)+[\w-]{2,4}/', stripslashes($_REQUEST['email_list_input']), $matches);
	if (isset($matches[0]) && is_array($matches[0])) foreach ($matches[0] as $destination) {
		try {
			// Don't send the email if that person is already registered
			$email_user = User::getByEmail(trim(mb_strtolower($destination)));
		} catch (UserException $e) {
			if (strcmp(trim($message), '') == 0) {
				Email::mail($destination, $user->getLid(), 
					'INVITE_SIMPLE', 
					array('username' => $user->getUniqueName())
					);
			} else {
				preg_match_all("@((http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&amp;\@%\$#\=~])*)@", $message, $matchez);

				if (isset($matchez[0])) foreach ($matchez[0] as $url) {
					if (strcmp(substr($url, 0, 15), 'http://inspi.re') != 0)
					$message = ereg_replace($url, '', $message);
				}
				
				Email::mail($destination, $user->getLid(), 
					'INVITE_MESSAGE', 
					array('username' => $user->getUniqueName(), 'message' => $message)
					);
			}
			
		}
	}
}

header('Location: '.$PAGE['INVITE'].'?lid='.$user->getLid().'&success=true');

?>