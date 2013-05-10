<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/privatemessagelist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class PrivateMessageException extends Exception {}

class PrivateMessage implements Persistent {
	private $pmid;
	private $source_uid;
	private $destination_uid;
	private $status;
	private $creation_time;
	private $title;
	private $text;
	private $outbox_status;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setStatus = 4;
	const statement_setOutboxStatus = 5;
	
    const cache_prefix = 'PrivateMessage-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of private message with pmid='.$this->pmid);
		
		try {
			Cache::replaceorset(PrivateMessage::cache_prefix.$this->pmid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of private message with pmid='.$this->pmid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 5)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4]);
    }
	
	public function __construct2($source_uid, $destination_uid, $title, $text, $status) {
		global $PRIVATE_MESSAGE_STATUS;
		global $PRIVATE_MESSAGE_OUTBOX_STATUS;
		
		PrivateMessage::prepareStatement(PrivateMessage::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		PrivateMessage::$statement[PrivateMessage::statement_create]->execute(array($source_uid, $destination_uid, $title, $text, $status));
		Log::trace('DB', 'Executed PrivateMessage::statement_create ['.$source_uid.', '.$destination_uid.', "'.$title.'", "'.$text.'", '.$status.'] ('.(microtime(true) - $start_timestamp).')');
		$pmid = DB::insertid();

		$this->setPMid($pmid);
		$this->setSourceUid($source_uid);
		$this->setDestinationUid($destination_uid);
		$this->setStatus($status, false);
		$this->setCreationTime(time());
		$this->setTitle($title);
		$this->setText($text);
		$this->setOutboxStatus($PRIVATE_MESSAGE_OUTBOX_STATUS['SENT'], false);
		$this->saveCache();
		
		PrivateMessageList::deleteBySourceUid($source_uid);
		PrivateMessageList::deleteByDestinationUid($destination_uid);
		PrivateMessageList::deleteByDestinationUidAndStatus($destination_uid, $status);
		PrivateMessageList::addDestinationUidBySourceUid($destination_uid, $source_uid);
		PrivateMessageList::addSourceUidByDestinationUid($source_uid, $destination_uid);
		PrivateMessageList::deleteBySourceUidAndOutboxStatus($source_uid, $PRIVATE_MESSAGE_OUTBOX_STATUS['SENT']);
	}
	
	public static function get($pmid) {
		if ($pmid === null) throw new PrivateMessageException('No private message for that pmid: '.$pmid);
		
		try {
			$private_message = Cache::get(PrivateMessage::cache_prefix.$pmid);
		} catch (CacheException $e) {
			PrivateMessage::prepareStatement(PrivateMessage::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PrivateMessage::$statement[PrivateMessage::statement_get]->execute($pmid);
			Log::trace('DB', 'Executed PrivateMessage::statement_get ['.$pmid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new PrivateMessageException('No private message for that pmid: '.$pmid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$private_message = new PrivateMessage();
			$private_message->populateFields($row);
			$private_message->saveCache();
		}
		return $private_message;
	}
	
	public static function getArray($pmidlist) {
		$result = array();
		$querylist = array();
		
		foreach ($pmidlist as $pmid) $querylist []= PrivateMessage::cache_prefix.$pmid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($pmidlist as $pmid) try {
			if (isset($cacheresult[PrivateMessage::cache_prefix.$pmid])) $result[$pmid] = $cacheresult[PrivateMessage::cache_prefix.$pmid];
			else $result[$pmid] = PrivateMessage::get($pmid);
		} catch (PrivateMessageException $e) {}
		
		return $result;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setPMid($row[$COLUMN['PMID']]);
		$this->setSourceUid($row[$COLUMN['SOURCE_UID']]);
		$this->setDestinationUid($row[$COLUMN['DESTINATION_UID']]);
		$this->setStatus($row[$COLUMN['STATUS']], false);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
		$this->setText($row[$COLUMN['TEXT']]);
		$this->setTitle($row[$COLUMN['TITLE']]);
		$this->setOutboxStatus($row[$COLUMN['OUTBOX_STATUS']], false);
	}
	
	public function delete() {
		global $INDEXING_STATUS;
		
		PrivateMessage::prepareStatement(PrivateMessage::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		PrivateMessage::$statement[PrivateMessage::statement_delete]->execute($this->pmid);
		Log::trace('DB', 'Executed PrivateMessage::statement_delete ['.$this->pmid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(PrivateMessage::cache_prefix.$this->pmid); } catch (CacheException $e) {}
		
		// Remove from associated lists
		
		PrivateMessageList::deleteBySourceUid($this->source_uid);
		PrivateMessageList::deleteByDestinationUid($this->destination_uid);
		PrivateMessageList::deleteByDestinationUidAndStatus($this->destination_uid, $this->status);
		PrivateMessageList::deleteDestinationUidBySourceUid($this->source_uid);
		PrivateMessageList::deleteSourceUidByDestinationUid($this->destination_uid);
		PrivateMessageList::deleteBySourceUidAndOutboxStatus($this->source_uid, $this->outbox_status);
	}
	
	public function getPMid() { return $this->pmid; }
	
	public function setPMid($new_pmid) { $this->pmid = $new_pmid; }
	
	public function getSourceUid() { return $this->source_uid; }
	
	public function setSourceUid($new_source_uid) { $this->source_uid = $new_source_uid; }
	
	public function getDestinationUid() { return $this->destination_uid; }
	
	public function setDestinationUid($new_destination_uid) { $this->destination_uid = $new_destination_uid; }
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time) { $this->creation_time = $new_creation_time; }

	public function getText() { return $this->text; }
	
	public function setText($new_text) { $this->text = $new_text; }
	
	public function getTitle() { return $this->title; }
	
	public function setTitle($new_title) { $this->title = $new_title; }

	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			PrivateMessage::prepareStatement(PrivateMessage::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			PrivateMessage::$statement[PrivateMessage::statement_setStatus]->execute(array($this->status, $this->pmid));
			Log::trace('DB', 'Executed PrivateMessage::statement_setStatus ['.$this->status.', '.$this->pmid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			PrivateMessageList::deleteByDestinationUidAndStatus($this->destination_uid, $old_status);
			PrivateMessageList::deleteByDestinationUidAndStatus($this->destination_uid, $this->status);
		}
	}
	
	public function getOutboxStatus() { return $this->outbox_status; }
	
	public function setOutboxStatus($new_status, $persist=true) {
		$old_status = $this->outbox_status;
		$this->outbox_status = $new_status;
		
		if ($persist) {
			PrivateMessage::prepareStatement(PrivateMessage::statement_setOutboxStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			PrivateMessage::$statement[PrivateMessage::statement_setOutboxStatus]->execute(array($this->outbox_status, $this->pmid));
			Log::trace('DB', 'Executed PrivateMessage::statement_setOutboxStatus ['.$this->outbox_status.', '.$this->pmid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			PrivateMessageList::deleteBySourceUidAndOutboxStatus($this->source_uid, $old_status);
			PrivateMessageList::deleteBySourceUidAndOutboxStatus($this->source_uid, $this->outbox_status);
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PrivateMessage::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PrivateMessage::statement_get:
					PrivateMessage::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PMID'].', '.$COLUMN['SOURCE_UID'].', '.$COLUMN['DESTINATION_UID']
						.', '.$COLUMN['STATUS'].', '.$COLUMN['TITLE']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.', '.$COLUMN['TEXT']
						.', '.$COLUMN['OUTBOX_STATUS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PRIVATE_MESSAGE']
						.' WHERE '.$COLUMN['PMID'].' = ?'
								, array('integer'));
					break;
				case PrivateMessage::statement_create:
					PrivateMessage::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['PRIVATE_MESSAGE'].'( '.$COLUMN['SOURCE_UID']
						.', '.$COLUMN['DESTINATION_UID'].', '.$COLUMN['TITLE'].', '.$COLUMN['TEXT'].', '.$COLUMN['STATUS']
						.') VALUES(?, ?, ?, ?, ?)', array('text', 'text', 'text', 'text', 'integer'));
					break;	
				case PrivateMessage::statement_setStatus:
					PrivateMessage::$statement[$statement] = DB::prepareSetter($TABLE['PRIVATE_MESSAGE'], array($COLUMN['PMID'] => 'integer'), $COLUMN['STATUS'], 'integer');
					break;
				case PrivateMessage::statement_delete:
					PrivateMessage::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['PRIVATE_MESSAGE']
						.' WHERE '.$COLUMN['PMID'].' = ?'
						, array('integer'));
					break;	
				case PrivateMessage::statement_setOutboxStatus:
					PrivateMessage::$statement[$statement] = DB::prepareSetter($TABLE['PRIVATE_MESSAGE'], array($COLUMN['PMID'] => 'integer'), $COLUMN['OUTBOX_STATUS'], 'integer');
					break;
			}
		}
	}
}

?>