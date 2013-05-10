<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Mark a private message in the outbox as deleted
*/

require_once(dirname(__FILE__).'/../entities/privatemessage.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

if (isset($_REQUEST['pmid'])) try {
	$privatemessage = PrivateMessage::get($_REQUEST['pmid']);
	if ($user->getUid() == $privatemessage->getSourceUid())
		$privatemessage->setOutboxStatus($PRIVATE_MESSAGE_OUTBOX_STATUS['DELETED']);
} catch (PrivateMessageException $e) {}

if (isset($_REQUEST['pmpage'])) {
	$page = $_REQUEST['pmpage'];
} else $page = 1;

header('Location: '.$PAGE['OUTBOX'].'?lid='.$user->getLid().'&pmpage='.$page);

?>