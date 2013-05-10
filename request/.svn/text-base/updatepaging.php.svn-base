<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Updates the discussion threads paging preference of a given user
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userpaging.php');

if (isset($_REQUEST['amount']) && isset($_REQUEST['paging'])) {
	
	$user = User::getSessionUser();
	
	UserPaging::setPagingValue($user->getUid(), $_REQUEST['paging'], max(1, intval($_REQUEST['amount'])));
}

?>
0