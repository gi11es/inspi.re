<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class CommunityModeratorException extends Exception {}

class CommunityModerator implements Persistent {
	private $xid;
	private $uid;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setUid = 4;
	
    const cache_prefix = 'CommunityModerator-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of community_moderator with xid='.$this->xid.' and uid='.$this->uid);
		
		try {
			Cache::replaceorset(CommunityModerator::cache_prefix.$this->xid.'-'.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of community_moderator with xid='.$this->xid.' and uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($xid, $uid) {
		CommunityModerator::prepareStatement(CommunityModerator::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		CommunityModerator::$statement[CommunityModerator::statement_create]->execute(array($xid, $uid));
		Log::trace('DB', 'Executed CommunityModerator::statement_create ['.$xid.', '.$uid.'], ('.(microtime(true) - $start_timestamp).')');

		$this->setXid($xid);
		$this->setUid($uid, false);
		$this->saveCache();
		
		CommunityModeratorList::deleteByXid($xid);
		CommunityModeratorList::deleteByUid($uid);
	}
	
	public static function get($xid, $uid) {
		if ($xid === null) throw new CommunityModeratorException('No community moderator for that xid: '.$xid.' and uid='.$uid);
		
		try {
			$community_moderator = Cache::get(CommunityModerator::cache_prefix.$xid.'-'.$uid);
		} catch (CacheException $e) {
			CommunityModerator::prepareStatement(CommunityModerator::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityModerator::$statement[CommunityModerator::statement_get]->execute(array($xid, $uid));
			Log::trace('DB', 'Executed CommunityModerator::statement_get ['.$xid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new CommunityModeratorException('No community moderator for that xid: '.$xid.' and uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$community_moderator = new CommunityModerator();
			$community_moderator->populateFields($row);
			$community_moderator->saveCache();
		}
		return $community_moderator;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setXid($row[$COLUMN['XID']]);
		$this->setUid($row[$COLUMN['UID']], false);
	}
	
	public function delete() {
		CommunityModerator::prepareStatement(CommunityModerator::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = CommunityModerator::$statement[CommunityModerator::statement_delete]->execute(array($this->xid, $this->uid));
		Log::trace('DB', 'Executed CommunityModerator::statement_delete ['.$this->xid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');

		try { Cache::delete(CommunityModerator::cache_prefix.$this->xid.'-'.$this->uid); } catch (CacheException $e) {}
		
		// Remove from associated lists
		
		CommunityModeratorList::deleteByXid($this->xid);
		CommunityModeratorList::deleteByUid($this->uid);
	}
	
	public function getXid() { return $this->xid; }
	
	public function setXid($new_xid) { $this->xid = $new_xid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid, $persist=true) {
		$old_uid = $this->uid;
		$this->uid = $new_uid;
		
		if ($persist) {
			CommunityModerator::prepareStatement(CommunityModerator::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			CommunityModerator::$statement[CommunityModerator::statement_setUid]->execute(array($this->uid, $this->xid));
			Log::trace('DB', 'Executed CommunityModerator::statement_setUid ['.$this->uid.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			CommunityModeratorList::deleteByUid($old_uid);
			CommunityModeratorList::deleteByUid($new_uid);
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CommunityModerator::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CommunityModerator::statement_get:
					CommunityModerator::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['XID'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MODERATOR']
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['UID'].' = ?'
								, array('integer', 'text'));
					break;
				case CommunityModerator::statement_create:
					CommunityModerator::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MODERATOR']
						.'( '.$COLUMN['XID'].', '.$COLUMN['UID']
						.') VALUES(?, ?)', array('integer', 'text'));
					break;	
				case CommunityModerator::statement_delete:
					CommunityModerator::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MODERATOR']
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['UID'].' = ?'
						, array('integer', 'text'));
					break;
				case CommunityModerator::statement_setUid:
					CommunityModerator::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY_MODERATOR'], array($COLUMN['XID'] => 'integer'), $COLUMN['UID'], 'text');
					break;
			}
		}
	}
}

?>