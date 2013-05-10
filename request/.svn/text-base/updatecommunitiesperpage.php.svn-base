<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Updates the communities paging preference of a given user
*/

require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userpaging.php');
require_once(dirname(__FILE__).'/../utilities/inml.php');

if (isset($_REQUEST['amount'])) {
	
	$user = User::getSessionUser();
	
	$amount_per_page = max(1, intval($_REQUEST['amount']));
	
	UserPaging::setPagingValue($user->getUid(), 'COMMUNITIES_COMMUNITIES', $amount_per_page);
	
	$result = '<div id="communities_current_amount">';
	if ($amount_per_page > 1) {
		$result .= '<translate id="COMMUNITIES_AMOUNT_PLURAL">';
		$result .= 'Currently displaying <integer value="'.$amount_per_page.'"/> communities per page.';
		$result .= '</translate>';
	} else {
		$result .= '<translate id="COMMUNITIES_AMOUNT_SINGULAR">';
		$result .= 'Currently displaying <integer value="'.$amount_per_page.'"/> community per page.';
		$result .= '</translate>';
	}
	$result .= '</div>';
	
	$translated_html = I18N::translateHTML($user, $result);
	$tagged_html = INML::processHTML($user, $translated_html);
    echo I18N::translateHTML($user, $tagged_html);
} else echo '0';

?>