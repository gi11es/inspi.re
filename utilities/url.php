<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Handles basic URL operations (mostly to crawl pages), based on curl
*/

require_once(dirname(__FILE__).'/log.php');
require_once(dirname(__FILE__).'/../settings.php');

class URL {
	private static $handles = array();
	private static $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6';
	private static $timeout = 5;
	private static $started = false;

	// Cleanup function, lets us close the curl session
	public static function shutdown() {
		Log::trace(__CLASS__, '*** stopping ***');
		foreach (URL::$handles as $ch)
    		curl_close($ch);
	}
	
	// Clear the cookies if we need to reset the 'browser' session
	public static function clearCookies() {
		global $COOKIE_FILE;
	
		unlink($COOKIE_FILE);
	}
	
	private static function checkInit() {
		if (URL::$started == false) {
			Log::trace(__CLASS__, '*** starting ***');
			register_shutdown_function(array('URL', 'shutdown'));
			URL::$started  = true;
		}
	}

	/*
	 * Returns the contents of a given URL
	 * $request containg the URL request, along with urlencoded get parameters
	 * $post contains optional post parameters in a hashmap
	 * $authstring is used for BASIC authentication
	 * $referer specifies a fake referer to be used in the request
	 */ 
	public static function get($request, $post=null, $authstring=null, $referer=null) {
		global $COOKIE_FILE;
	
		URL::checkInit();
		
		$ch = curl_init();
		Log::trace(__CLASS__, 'get '.$request);
		
		curl_setopt_array($ch, array(CURLOPT_URL => $request, 
									CURLOPT_FAILONERROR => true, 
									CURLOPT_RETURNTRANSFER => true,
									CURLOPT_TIMEOUT => URL::$timeout,
									CURLOPT_SSL_VERIFYPEER => false,
									CURLOPT_USERAGENT =>  URL::$user_agent));
		
		if ($referer !== null)
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		
		// If a cookie file is specified in settings.php we use it to store the cookies
		if ($COOKIE_FILE !== null)
			curl_setopt_array($ch, array(CURLOPT_COOKIEJAR => $COOKIE_FILE, CURLOPT_COOKIEFILE => $COOKIE_FILE));
		
		if ($post !== null)
			curl_setopt_array($ch, array(CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post));
		
		if ($authstring !== null)
			curl_setopt(URL::$ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.base64_encode($authstring)));
		
		URL::$handles[]= $ch;

		return curl_exec($ch);
	}
	
	// Returns true if the HTTP code returned by that URL is 200, doesn't actually download the URL's contents
	public static function check($url) {
		URL::checkInit();
		
		Log::trace(__CLASS__, 'check '.$url);
		
		$ch = curl_init();
		curl_setopt_array($ch, array(CURLOPT_URL => $url, 
									CURLOPT_FOLLOWLOCATION => true, 
									CURLOPT_NOBODY => true, 
									CURLOPT_TIMEOUT => URL::$timeout));
		curl_exec ($ch);
		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		URL::$handles[]= $ch;
		
		return ($response_code == 200);
	}
	
	// Save a remote file on local storage
	public static function download($url, $destination_filename) {
		$shell_result = array();
		exec('wget -O '.$destination_filename.' '.$url, $shell_result);
	}
	
	public static function getBaseURL() {
		global $_SERVER;
		
		if( $_SERVER['SERVER_PORT'] == "443" )
		  $abs_path = "https://" ;
	   else
		  $abs_path = "http://" ;
	
	   $abs_path .= $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']; //$_SERVER["REQUEST_URI"] ;
	   if( !empty($_SERVER['QUERY_STRING']) )
		  $abs_path .= '?' . $_SERVER['QUERY_STRING'];
	
	   return $abs_path ;
	}
}

?>