<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/entrycommentnotificationlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class EntryCommentNotificationException extends Exception {}

class EntryCommentNotification implements Persistent {
	private $eid;
	private $uid;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'EntryCommentNotification-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of entry comment notification with eid='.$this->eid.' and uid='.$this->uid);
		
		try {
			Cache::replaceorset(EntryCommentNotification::cache_prefix.$this->eid.'-'.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of entry comment notification with eid='.$this->eid.' and uid='.$this->uid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($eid, $uid) {
		
		EntryCommentNotification::prepareStatement(EntryCommentNotification::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		EntryCommentNotification::$statement[EntryCommentNotification::statement_create]->execute(array($eid, $uid));
		Log::trace('DB', 'Executed EntryCommentNotification::statement_create ['.$eid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');

		$this->setEid($eid);
		$this->setUid($uid);
		$this->saveCache();
		
		EntryCommentNotificationList::deleteByEid($eid);
	}
	
	public static function get($eid, $uid) {
		try {
			$entry_comment_notification = Cache::get(EntryCommentNotification::cache_prefix.$eid.'-'.$uid);
		} catch (CacheException $e) {
			EntryCommentNotification::prepareStatement(EntryCommentNotification::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryCommentNotification::$statement[EntryCommentNotification::statement_get]->execute(array($eid, $uid));
			Log::trace('DB', 'Executed EntryCommentNotification::statement_get ['.$eid.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new EntryCommentNotificationException('No entry comment notification for that eid='.$eid.' and uid='.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$entry_comment_notification = new EntryCommentNotification();
			$entry_comment_notification->populateFields($row);
		}
		return $entry_comment_notification;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setEid($row[$COLUMN['EID']]);
		$this->setUid($row[$COLUMN['UID']]);
	}
	
	public function delete() {
		global $INDEXING_STATUS;
		
		EntryCommentNotification::prepareStatement(EntryCommentNotification::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		EntryCommentNotification::$statement[EntryCommentNotification::statement_delete]->execute(array($this->eid, $this->uid));
		Log::trace('DB', 'Executed EntryCommentNotification::statement_delete ['.$this->eid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(EntryCommentNotification::cache_prefix.$this->eid.'-'.$this->uid); } catch (CacheException $e) {}
		
		EntryCommentNotificationList::deleteByEid($this->eid);
	}
	
	public function getEid() { return $this->eid; }
	
	public function setEid($new_eid) { $this->eid = $new_eid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(EntryCommentNotification::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case EntryCommentNotification::statement_get:
					EntryCommentNotification::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_COMMENT_NOTIFICATION']
						.' WHERE '.$COLUMN['EID'].' = ? AND '.$COLUMN['UID'].' = ?'
								, array('integer', 'text'));
					break;
				case EntryCommentNotification::statement_create:
					EntryCommentNotification::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['ENTRY_COMMENT_NOTIFICATION'].'( '.$COLUMN['EID']
						.', '.$COLUMN['UID']
						.') VALUES(?, ?)', array('integer', 'text'));
					break;	
				case EntryCommentNotification::statement_delete:
					EntryCommentNotification::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_COMMENT_NOTIFICATION']
						.' WHERE '.$COLUMN['EID'].' = ? AND '.$COLUMN['UID'].' = ?'
						, array('integer', 'text'));
					break;	
			}
		}
	}
}

?>