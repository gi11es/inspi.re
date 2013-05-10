<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Remove an entry from a user's list of favorites
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/favorite.php');
require_once(dirname(__FILE__).'/../entities/favoritelist.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();
$entry = null;

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
	$favoritelist = FavoriteList::getByUid($user->getUid());

	if (in_array($entry->getEid(), array_keys($favoritelist))) {
		$favorite = Favorite::get($entry->getEid(), $user->getUid());
		$favorite->delete();
	}
}

?>
0