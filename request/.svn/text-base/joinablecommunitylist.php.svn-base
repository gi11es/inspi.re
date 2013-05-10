<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Return a list of communities that the user can join
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();

if (isset($_REQUEST['order']) && isset($_REQUEST['page']) && isset($_REQUEST['restrict_language']) && isset($_REQUEST['restrict_labels'])) {
	$restrict_language = strcasecmp($_REQUEST['restrict_language'], 'true') == 0;
	$restrict_labels = json_decode($_REQUEST['restrict_labels']);
	if (!is_array($restrict_labels)) $restrict_labels = array();
	
	$result = array();
	$result['communitylist'] = UI::RenderJoinableCommunityList($user, $_REQUEST['order'], $_REQUEST['page'], $restrict_language, $restrict_labels, true);
	$result['paging'] = UI::RenderJoinableCommunityPaging($user, $_REQUEST['page'], $restrict_language, $restrict_labels, true);
	echo json_encode($result);
}

?>