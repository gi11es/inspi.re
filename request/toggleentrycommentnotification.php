<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Subscribe or unsubscribe someone from the alerts on a given entry
*/

require_once(dirname(__FILE__).'/../entities/entrycommentnotification.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();
$entry = null;
$value = isset($_REQUEST['value']) && strcasecmp($_REQUEST['value'], 'true') == 0;

if (isset($_REQUEST['hash'])) try {
	$vars = explode('=', $_REQUEST['hash']);
	if (isset($vars[0]) && isset($vars[1])) {
		if (strcasecmp($vars[0], 'eid') == 0) {
			$entry = Entry::get($vars[1]);
		} elseif (strcasecmp($vars[0], 'token') == 0) {
			$token = Token::get($vars[1]);
			$exploded = explode('-', $token);
			if (count($exploded) == 2) {
				$token_uid = $exploded[0];
				$eid = $exploded[1];
				if ($token_uid == $user->getUid() || $user->getUid() == $GOOGLE_UID)
					$entry = Entry::get($eid);
			}
		} elseif (strcasecmp($vars[0], 'persistenttoken') == 0) {
			$token = PersistentToken::get($vars[1]);
			$exploded = explode('-', $token);
			if (count($exploded) == 2) {
				$token_uid = $exploded[0];
				$eid = $exploded[1];
				if ($token_uid == $user->getUid() || $user->getUid() == $GOOGLE_UID)
					$entry = Entry::get($eid);
			}
		}
	}
} catch (EntryException $e) {} catch (TokenException $f) {} catch (PersistentTokenException $g) {}

if ($entry !== null) {
    if ($value) {
        try {
            $entrycommentnotification = EntryCommentNotification::get($entry->getEid(), $user->getUid());
        } catch (EntryCommentNotificationException $e) {
            $entrycommentnotification = new EntryCommentNotification($entry->getEid(), $user->getUid());
        }
    } else {
        try {
            $entrycommentnotification = EntryCommentNotification::get($entry->getEid(), $user->getUid());
            $entrycommentnotification->delete();
        } catch (EntryCommentNotificationException $e) {}
    }
}

?>
0