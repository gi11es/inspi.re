<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Which members on the website are also on the official inspi.re team
*/

require_once(dirname(__FILE__).'/../entities/teammembershiplist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class TeamMembershipException extends Exception {}

class TeamMembership implements Persistent {
	private $uid;
	private $title;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setTitle = 4;
	
    const cache_prefix = 'TeamMembership-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of team membership with uid='.$this->uid);
		
		try {
			Cache::replaceorset(TeamMembership::cache_prefix.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of team membership with uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($uid, $title) {
		TeamMembership::prepareStatement(TeamMembership::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		TeamMembership::$statement[TeamMembership::statement_create]->execute(array($uid, $title));
		Log::trace('DB', 'Executed TeamMembership::statement_create ['.$uid.', "'.$title.'"] ('.(microtime(true) - $start_timestamp).')');

		$this->setUid($uid);
		$this->setTitle($title, false);
		$this->saveCache();
		
		TeamMembershipList::delete();
	}
	
	public static function get($uid) {
		if ($uid === null) throw new TeamMembershipException('No team membership for uid='.$uid);
		
		try {
			$team_membership = Cache::get(TeamMembership::cache_prefix.$uid);
		} catch (CacheException $e) {
			TeamMembership::prepareStatement(TeamMembership::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = TeamMembership::$statement[TeamMembership::statement_get]->execute($uid);
			Log::trace('DB', 'Executed TeamMembership::statement_get ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new TeamMembershipException('No team membership for uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$team_membership = new TeamMembership();
			$team_membership->populateFields($row);
			$team_membership->saveCache();
		}
		return $team_membership;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setUid($row[$COLUMN['UID']]);
		$this->setTitle($row[$COLUMN['TITLE']], false);
	}
	
	public function delete() {
		TeamMembership::prepareStatement(TeamMembership::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = TeamMembership::$statement[TeamMembership::statement_delete]->execute($this->uid);
		Log::trace('DB', 'Executed TeamMembership::statement_delete ['.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(TeamMembership::cache_prefix.$this->uid); } catch (CacheException $e) {}
		
		TeamMembershipList::delete();
	}
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public function getTitle() { return $this->title; }
	
	public function setTitle($new_title, $persist=true) {
		$this->title = $new_title;
		
		if ($persist) {
			TeamMembership::prepareStatement(TeamMembership::statement_setTitle);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			TeamMembership::$statement[TeamMembership::statement_setTitle]->execute(array($this->title, $this->uid));
			Log::trace('DB', 'Executed TeamMembership::statement_setTitle ["'.$this->title.'", '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(TeamMembership::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case TeamMembership::statement_get:
					TeamMembership::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['TITLE'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['TEAM_MEMBERSHIP']
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case TeamMembership::statement_create:
					TeamMembership::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['TEAM_MEMBERSHIP']
						.'( '.$COLUMN['UID'].', '.$COLUMN['TITLE']
						.') VALUES(?, ?)', array('text', 'text'));
					break;	
				case TeamMembership::statement_delete:
					TeamMembership::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['TEAM_MEMBERSHIP']
						.' WHERE '.$COLUMN['UID'].' = ?'
						, array('text'));
					break;
				case TeamMembership::statement_setTitle:
					TeamMembership::$statement[$statement] = DB::prepareSetter($TABLE['TEAM_MEMBERSHIP'], array($COLUMN['UID'] => 'text'), $COLUMN['TITLE'], 'text');
					break;
			}
		}
	}
}

?>