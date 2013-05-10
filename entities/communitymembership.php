<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class CommunityMembershipException extends Exception {}

class CommunityMembership implements Persistent {
	private $xid;
	private $uid;
	private $join_time;
	private $status;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setUid = 4;
	const statement_setStatus = 5;
	
    const cache_prefix = 'CommunityMembership-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of community_membership with xid='.$this->xid.' and uid='.$this->uid);
		
		try {
			Cache::replaceorset(CommunityMembership::cache_prefix.$this->xid.'-'.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of community_membership with xid='.$this->xid.' and uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 3)
			self::__construct2($argv[0], $argv[1], $argv[2]);
    }
	
	public function __construct2($xid, $uid, $status) {
		CommunityMembership::prepareStatement(CommunityMembership::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		CommunityMembership::$statement[CommunityMembership::statement_create]->execute(array($xid, $uid, $status));
		Log::trace('DB', 'Executed CommunityMembership::statement_create ['.$xid.', '.$uid.'], ('.(microtime(true) - $start_timestamp).')');

		$this->setXid($xid);
		$this->setUid($uid, false);
		$this->setStatus($status, false);
		$this->setJoinTime(time());
		$this->saveCache();
		
		CommunityMembershipList::deleteByXid($xid);
		CommunityMembershipList::deleteByXidAndStatus($xid, $status);
		CommunityMembershipList::deleteByUid($uid);
	}
	
	public static function get($xid, $uid, $cache = true) {
		if ($xid === null) throw new CommunityMembershipException('No community membership for that xid: '.$xid.' and uid='.$uid);
		
		try {
			$community_membership = Cache::get(CommunityMembership::cache_prefix.$xid.'-'.$uid);
		} catch (CacheException $e) {
			CommunityMembership::prepareStatement(CommunityMembership::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityMembership::$statement[CommunityMembership::statement_get]->execute(array($xid, $uid));
			Log::trace('DB', 'Executed CommunityMembership::statement_get ['.$xid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new CommunityMembershipException('No community membership for that xid: '.$xid.' and uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$community_membership = new CommunityMembership();
			$community_membership->populateFields($row);
			if ($cache) $community_membership->saveCache();
		}
		return $community_membership;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setXid($row[$COLUMN['XID']]);
		$this->setUid($row[$COLUMN['UID']], false);
		$this->setStatus($row[$COLUMN['STATUS']], false);
		$this->setJoinTime($row[$COLUMN['JOIN_TIME']]);
	}
	
	public function delete() {
		CommunityMembership::prepareStatement(CommunityMembership::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = CommunityMembership::$statement[CommunityMembership::statement_delete]->execute(array($this->xid, $this->uid));
		Log::trace('DB', 'Executed CommunityMembership::statement_delete ['.$this->xid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');

		try { Cache::delete(CommunityMembership::cache_prefix.$this->xid.'-'.$this->uid); } catch (CacheException $e) {}
		
		// Remove from associated lists
		
		CommunityMembershipList::deleteByXid($this->xid);
		CommunityMembershipList::deleteByXidAndStatus($this->xid, $this->status);
		CommunityMembershipList::deleteByUid($this->uid);
	}
	
	public function getXid() { return $this->xid; }
	
	public function setXid($new_xid) { $this->xid = $new_xid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid, $persist=true) {
		$old_uid = $this->uid;
		$this->uid = $new_uid;
		
		if ($persist) {
			CommunityMembership::prepareStatement(CommunityMembership::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			CommunityMembership::$statement[CommunityMembership::statement_setUid]->execute(array($this->uid, $this->xid, $old_uid));
			Log::trace('DB', 'Executed CommunityMembership::statement_setUid ['.$this->uid.', '.$this->xid.', '.$old_uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			CommunityMembershipList::deleteByUid($old_uid);
			CommunityMembershipList::deleteByUid($new_uid);
		}
	}
	
	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			CommunityMembership::prepareStatement(CommunityMembership::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			CommunityMembership::$statement[CommunityMembership::statement_setStatus]->execute(array($this->status, $this->xid, $this->uid));
			Log::trace('DB', 'Executed CommunityMembership::statement_setStatus ['.$this->status.', '.$this->xid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			CommunityMembershipList::deleteByXidAndStatus($this->xid, $old_status);
			CommunityMembershipList::deleteByXidAndStatus($this->xid, $new_status);
		}
	}
	
	public function getJoinTime() { return $this->join_time; }
	
	public function setJoinTime($new_join_time) { $this->join_time = $new_join_time; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CommunityMembership::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CommunityMembership::statement_get:
					CommunityMembership::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['XID'].', '.$COLUMN['UID'].', '.$COLUMN['STATUS'].', '
						.'UNIX_TIMESTAMP('.$COLUMN['JOIN_TIME'].') AS '.$COLUMN['JOIN_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MEMBERSHIP']
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['UID'].' = ?'
								, array('integer', 'text'));
					break;
				case CommunityMembership::statement_create:
					CommunityMembership::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MEMBERSHIP']
						.'( '.$COLUMN['XID'].', '.$COLUMN['UID'].', '.$COLUMN['STATUS']
						.') VALUES(?, ?, ?)', array('integer', 'text', 'integer'));
					break;	
				case CommunityMembership::statement_delete:
					CommunityMembership::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_MEMBERSHIP']
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['UID'].' = ?'
						, array('integer', 'text'));
					break;
				case CommunityMembership::statement_setUid:
					CommunityMembership::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY_MEMBERSHIP'], array($COLUMN['XID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['UID'], 'text');
					break;
				case CommunityMembership::statement_setStatus:
					CommunityMembership::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY_MEMBERSHIP'], array($COLUMN['XID'] => 'integer', $COLUMN['UID'] => 'text'), $COLUMN['STATUS'], 'integer');
					break;
			}
		}
	}
}

?>