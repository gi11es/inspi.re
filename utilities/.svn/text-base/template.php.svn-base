<?php
    
/* 
 	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Provides a basic templating engine
*/

class TemplateException extends Exception {}

class Template {

	/*
	 * Returns a version of the template filled with the values contained in the $variables hashmap
	 */
	public static function Templatize($text, $variables) {
		$templatized = $text;
	
		foreach ($variables as $pattern => $replacement) {
			$templatized = str_replace("#".$pattern, $replacement, $templatized);
		}
		return $templatized;
	}
}

?>
