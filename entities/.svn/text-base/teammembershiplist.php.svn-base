<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/functions.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class TeamMembershipListException extends Exception {}

class TeamMembershipList {
	private static $statement = array();
	
	const statement_get = 1;
	
	const cache_prefix = 'TeamMembershipList-';
	
	public static function delete() {
		try { Cache::delete(TeamMembershipList::cache_prefix); } catch (CacheException $e) {}
	}
	
	public static function get() {
		global $COLUMN;
		
		try {
			 $list = Cache::get(TeamMembershipList::cache_prefix);
		} catch (CacheException $e) { 
			TeamMembershipList::prepareStatement(TeamMembershipList::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = TeamMembershipList::$statement[TeamMembershipList::statement_get]->execute(true);
			Log::trace('DB', 'Executed TeamMembershipList::statement_get ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[] = $row[$COLUMN['UID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(TeamMembershipList::cache_prefix, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(TeamMembershipList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case TeamMembershipList::statement_get:
					TeamMembershipList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['TEAM_MEMBERSHIP']
						.' WHERE ?'
								, array('boolean'));
					break;
			}
		}
	}
}

?>