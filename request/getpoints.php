<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Returns the current amount of points for the user
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();

$result = array();
$result['points'] = $user->getPoints();
$result['div'] = UI::RenderPointsLeft($user, true);

echo json_encode($result);

?>