<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Mirrors an image from uncle and delivers it
*/

if (isset($_REQUEST['fid'])) {
	if (!file_exists('/home/daruma/pictures/'.$_REQUEST['fid'].'.jpg')) {
		$shell_result = array();
		exec('curl http://uncle.inspi.re/'.$_REQUEST['fid'].'.jpg -o /home/daruma/pictures/'.$_REQUEST['fid'].'.jpg', $shell_result);
	}
	header('Location: http://kirby.inspi.re/'.$_REQUEST['fid'].'.jpg');
}

?>
