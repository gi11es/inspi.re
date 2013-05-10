<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page that flushes the WHOLE cache, it should be modified not to be publically available
 	Use for debugging and data structure updates only
*/

require_once(dirname(__FILE__).'/utilities/cache.php');

Cache::flush();

?>
