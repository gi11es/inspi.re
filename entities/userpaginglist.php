<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2.php';

class UserPagingListException extends Exception {}

class UserPagingList {
	private static $statement = array();
	
	const statement_getByUid = 1;
	
	const cache_prefix_uid = 'UserPagingListByUid-';
	
	public static function deleteByUid($uid) {
		try { Cache::delete(UserPagingList::cache_prefix_uid.$uid); } catch (CacheException $e) {}
	}
	
	public static function getByUid($uid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserPagingList::cache_prefix_uid.$uid);
		} catch (CacheException $e) { 
			UserPagingList::prepareStatement(UserPagingList::statement_getByUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserPagingList::$statement[UserPagingList::statement_getByUid]->execute($uid);
			Log::trace('DB', 'Executed UserPagingList::statement_getByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // PGID => VALUE
				$result->free();
			}
			
			try {
				Cache::setorreplace(UserPagingList::cache_prefix_uid.$uid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(UserPagingList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case UserPagingList::statement_getByUid:
					UserPagingList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PGID'].', '.$COLUMN['VALUE']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_PAGING']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
			}
		}
	}
}

?>