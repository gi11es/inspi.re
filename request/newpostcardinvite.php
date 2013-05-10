<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Registers a request to send a postcard
*/

require_once(dirname(__FILE__).'/../entities/emailcampaign.php');
require_once(dirname(__FILE__).'/../entities/emailcampaignlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../templates/emailtemplate.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$emailcampaignlist = array_keys(EmailCampaignList::getByETid($EMAIL_TEMPLATE['POSTCARD']));

if (isset($_REQUEST['address_input']) && !in_array($user->getUid(), $emailcampaignlist)) {
	$message = isset($_REQUEST['postcard_text_input'])?stripslashes($_REQUEST['postcard_text_input']):'';
	$address = stripslashes($_REQUEST['address_input']);
		
	$emailcampaign = new EmailCampaign($user->getUid(), $EMAIL_TEMPLATE['POSTCARD']);
	Email::mail('postcard@inspi.re', $user->getLid(), 
		'POSTCARD', 
		array('address' => $address, 'message' => $message)
		);
}

header('Location: '.$PAGE['INVITE'].'?lid='.$user->getLid());

?>