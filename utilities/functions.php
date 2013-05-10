<?php
    
/* 
 	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Misc. functions helper functions
*/

class Functions {
	public static function makeGreaterThanFunction($limit, $equals=true) {
		return create_function('$value', 'return $value >'.($equals?'=':'').' '.$limit.';');
	}
}

?>