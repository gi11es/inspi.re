<?php
    
/* 
 	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/
	
interface Persistent {
	function saveCache();
	static function prepareStatement($statement);
}

?>
