<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	List of etids
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class EmailCampaignListException extends Exception {}

class EmailCampaignList {
	private static $statement = array();
	
	const statement_getByUid = 1;
	const statement_getByETid = 3;
	
	const cache_prefix_uid = 'EmailCampaignListByUid-';
	const cache_prefix_etid = 'EmailCampaignListByETid-';
	
	public static function deleteByUid($uid) {
		try { Cache::delete(EmailCampaignList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EmailCampaignList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			EmailCampaignList::prepareStatement(EmailCampaignList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EmailCampaignList::$statement[EmailCampaignList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed EmailCampaignList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // ETID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EmailCampaignList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByETid($etid) {
		try { Cache::delete(EmailCampaignList::cache_prefix_etid.$etid); } catch (CacheException $e) {}
	}
	
	public static function getByETid($etid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EmailCampaignList::cache_prefix_etid.$etid);
		} catch (CacheException $e) { 
			EmailCampaignList::prepareStatement(EmailCampaignList::statement_getByETid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EmailCampaignList::$statement[EmailCampaignList::statement_getByETid]->execute($etid);
			Log::trace('DB', 'Executed EmailCampaignList::statement_getByETid ['.$etid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => CREATION_TIME
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EmailCampaignList::cache_prefix_etid.$etid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(EmailCampaignList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case EmailCampaignList::statement_getByUid:
					EmailCampaignList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['ETID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['EMAIL_CAMPAIGN']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case EmailCampaignList::statement_getByETid:
					EmailCampaignList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['EMAIL_CAMPAIGN']
						.' USE INDEX('.$COLUMN['ETID'].')'
						.' WHERE '.$COLUMN['ETID'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>