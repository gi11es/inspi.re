<?php
    
/* 
 	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Provides string helpers
*/

require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

require_once 'HTMLPurifier.auto.php';

class String {
	// Makes the first letter of a string into a capital letter
	public static function capitalize($string) {
		return strtoupper(substr($string, 0, 1)).substr($string, 1);
	}
	
	// Remove spacing and carriage return characters from the beginning and end of a string
	public static function stripSpecialEnds($string) {
		$start_removed = preg_replace("/^[\n\r\t\s]*/i", "", $string);
		return preg_replace("/[\n\r\t\s]*$/i", "", $start_removed);
	}
	
	// Replaces the domain name part of an email address with "---"
	public static function hideEmail($email) {
		return preg_replace("/(@)(.*)(\.)/i", "$1---$3", $email);
	}
	
	// Since it's easy to forget specifying the extra parameters, this should always be used instead of the standard htmlentities
	public static function htmlentities($string) {
		return htmlentities($string, ENT_QUOTES, "UTF-8");
	}
	
	// Escape text to be passed to javascript
	public static function addJSslashes($string) {
		$pattern = array(
			"/\\\\/"  , "/\n/"    , "/\r/"    , "/\"/"    ,
			"/\'/"    , "/&/"     , "/</"     , "/>/"
		);
		$replace = array(
			"\\\\\\\\", "\\n"     , "\\r"     , "\\\""    ,
			"\\'"     , "\\x26"   , "\\x3C"   , "\\x3E"
		);
		return preg_replace($pattern, $replace, $string);
	}
	
	// Escape text pulled from the database that was originally generated form user-input form
	public static function fromaform($string, $htmlentities = true) {
		if ($htmlentities) $temp = String::htmlentities($string);
		else $temp = $string;
		
		 $temp = mb_ereg_replace("\n", "<br/>", $temp);
		 $temp = preg_replace("@((http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&amp;\@%\$#\=~:])*)@", "<a target=\"_blank\" href=\"$1\" rel=\"nofollow\">$1</a>", $temp);
		 	 
		 return $temp;
	}
	
	public static function cleanhtml($html) {
		$config = HTMLPurifier_Config::createDefault();
    	$config->set('Core.Encoding', 'UTF-8'); // replace with your encoding
    	$config->set('HTML.Doctype', 'HTML 4.01 Transitional'); // replace with your doctype
    	$config->set('HTML.ForbiddenElements', 'img,iframe,div');
    	$purifier = new HTMLPurifier($config);
    
    	return $purifier->purify($html);
	}
	
	// Generates a string of the form "1 year and 6 months ago" with only the two largest time elements
	public static function duration($duration) {
		// array of time period chunks
		$chunks = array (
			array (
				31536000, // 60 * 60 * 24 * 365
				'year'
			),
			array (
				2592000, // 60 * 60 * 24 * 30
				'month'
			),
			array (
				604800, // 60 * 60 * 24 * 7
				'week'
			),
			array (
				86400, // 60 * 60 * 24
				'day'
			),
			array (
				3600, // 60 * 60
				'hour'
			),
			array (
				60,
				'minute'
			),
			array (
				1,
				'second'
			),
			
		);

		// $j saves performing the count function each time around the loop
		for ($i = 0, $j = count($chunks); $i < $j; $i++) {

			$seconds = $chunks[$i][0];
			$name = $chunks[$i][1];

			// finding the biggest chunk (if the chunk fits, break)
			if (($count = floor($duration / $seconds)) != 0) {
				// DEBUG print "<!-- It's $name -->\n";
				break;
			}
		}

		$print = ($count == 1) ? '1 ' . '<translate id="TIME_SINCE_SINGULAR_'.$name.'">'.$name.'</translate>' : $count.' <translate id="TIME_SINCE_PLURAL_'.$name.'">'.$name.'s</translate>';

		if ($i +1 < $j) {
			// now getting the second item
			$seconds2 = $chunks[$i +1][0];
			$name2 = $chunks[$i +1][1];

			// add second item if it's greater than 0
			if (strcmp('second', $name2) != 0 && ($count2 = floor(($duration - ($seconds * $count)) / $seconds2)) != 0) {
				$print .= ' <translate id="TIME_SINCE_AND">and</translate>'.(($count2 == 1) ? ' 1 <translate id="TIME_SINCE_SINGULAR_'.$name2.'">'.$name2.'</translate>' : ' '.$count2.' <translate id="TIME_SINCE_PLURAL_'.$name2.'">'.$name2.'s</translate>');
			}
		}
		return $print;
	}
	
	public static function wordlist($string) {
		$text = mb_strtolower($string, 'UTF-8');
	
		$text = mb_ereg_replace("\n", ' ', $text);

		$text = mb_ereg_replace("\r", '', $text);

		$text = mb_ereg_replace('[.¿¡?!;:,()"*]', '', $text); // Remove non-alphanumeric characters

		$text = mb_ereg_replace('[\s]+', '$', $text); // Transform any spaces into one character

		return explode('$', $text);
	}
	
	public static function urlify($string) {
		return preg_replace('/[\/\s:?"\']/si', '-', $string);
	}
}

?>