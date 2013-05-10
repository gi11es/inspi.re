<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	This is the error page reached when a user requests a non-existing page
*/

require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('MAINTENANCE', 'HOME', $user);

$page->startHTML();
?>
	
<div class="warning hintmargin">
<div class="warning_title">
Inspi.re is undergoing some maintenance
</div> <!-- warning_title -->
Please come back in 5 minutes.
<i>Gilles</i>
</div> <!-- warning -->

<?php
$page->endHTML();
$page->render();
?>