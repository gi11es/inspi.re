<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/commentindex.php');
require_once(dirname(__FILE__).'/../entities/commentindexlist.php');
require_once(dirname(__FILE__).'/../entities/discussionpostindex.php');
require_once(dirname(__FILE__).'/../entities/discussionpostindexlist.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class DiscussionPostException extends Exception {}

class DiscussionPost implements Persistent {
	private $oid;
	private $reply_to_oid;
	private $nid;
	private $uid;
	private $status;
	private $creation_time;
	private $text;
	private $aid = null;
	private $reply_aid = null;
	private $indexing_status = 0;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setTitle = 4;
	const statement_setStatus = 5;
	const statement_setText = 6;
	const statement_setUid = 7;
	const statement_setReplyToOid = 8;
	const statement_setNullReplyToOid = 9;
	const statement_setAid = 10;
	const statement_setReplyAid = 11;
	const statement_setIndexingStatus = 12;
	
    const cache_prefix = 'DiscussionPost-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of discussion post with oid='.$this->oid);
		
		try {
			Cache::replaceorset(DiscussionPost::cache_prefix.$this->oid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of discussion thread with oid='.$this->oid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 5)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4]);
		elseif (func_num_args() == 4)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3]);
    }
	
	public function __construct2($nid, $uid, $text, $status, $reply_to_oid = null) {
		global $INDEXING_STATUS;
		
		DiscussionPost::prepareStatement(DiscussionPost::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		DiscussionPost::$statement[DiscussionPost::statement_create]->execute(array($nid, $uid, $text, $reply_to_oid, $status));
		Log::trace('DB', 'Executed DiscussionPost::statement_create ['.$nid.', '.$uid.', "'.$text.'", '.$reply_to_oid.', '.$status.'] ('.(microtime(true) - $start_timestamp).')');
		$oid = DB::insertid();

		$this->setOid($oid);
		$this->setReplyToOid($reply_to_oid, false);
		$this->setNid($nid);
		$this->setUid($uid, false);
		$this->setStatus($status, false);
		$this->setCreationTime(time());
		$this->setText($text, false);
		$this->setIndexingStatus($INDEXING_STATUS['UNINDEXED'], false);
		$this->saveCache();
		
		DiscussionPostList::deleteByIndexingStatusAndStatus($INDEXING_STATUS['UNINDEXED'], $status);
		DiscussionPostList::deleteByNidAndStatus($nid, $status);
		DiscussionPostList::deleteByUidAndStatus($uid, $status);
		DiscussionPostList::deleteByNid($nid);
		if ($reply_to_oid !== null)
			DiscussionPostList::deleteByReplyToOid($reply_to_oid);
	}
	
	public static function get($oid, $cache = true) {
		if ($oid === null) throw new DiscussionPostException('No discussion post for that oid: '.$oid);
		
		try {
			$discussion_post = Cache::get(DiscussionPost::cache_prefix.$oid);
		} catch (CacheException $e) {
			DiscussionPost::prepareStatement(DiscussionPost::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = DiscussionPost::$statement[DiscussionPost::statement_get]->execute($oid);
			Log::trace('DB', 'Executed DiscussionPost::statement_get ['.$oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new DiscussionPostException('No discussion post for that oid: '.$oid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$discussion_post = new DiscussionPost();
			$discussion_post->populateFields($row);
			if ($cache) $discussion_post->saveCache();
		}
		return $discussion_post;
	}
	
	public static function getArray($oidlist, $cache = true) {
		$result = array();
		$querylist = array();
		
		foreach ($oidlist as $oid) $querylist []= DiscussionPost::cache_prefix.$oid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($oidlist as $oid) try {
			if (isset($cacheresult[DiscussionPost::cache_prefix.$oid])) $result[$oid] = $cacheresult[DiscussionPost::cache_prefix.$oid];
			else $result[$oid] = DiscussionPost::get($oid, $cache);
		} catch (DiscussionPostException $e) {}
		
		return $result;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setOid($row[$COLUMN['OID']]);
		$this->setReplyToOid($row[$COLUMN['REPLY_TO_OID']], false);
		$this->setNid($row[$COLUMN['NID']]);
		$this->setUid($row[$COLUMN['UID']], false);
		$this->setStatus($row[$COLUMN['STATUS']], false);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
		$this->setText($row[$COLUMN['TEXT']], false);
		$this->setAid($row[$COLUMN['AID']], false);
		$this->setReplyAid($row[$COLUMN['REPLY_AID']], false);
		$this->setIndexingStatus($row[$COLUMN['INDEXING_STATUS']], false);
	}
	
	public function delete() {
		global $INDEXING_STATUS;
		
		DiscussionPost::prepareStatement(DiscussionPost::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		DiscussionPost::$statement[DiscussionPost::statement_delete]->execute($this->oid);
		Log::trace('DB', 'Executed DiscussionPost::statement_delete ['.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(DiscussionPost::cache_prefix.$this->oid); } catch (CacheException $e) {}
		
		$replypostlist = DiscussionPostList::getByReplyToOid($this->oid);
		foreach ($replypostlist as $oid => $creation_time) {
			$post = DiscussionPost::get($oid);
			$post->setReplyToOid(null);
		}
		
		if ($this->aid !== null) try {
			$alert = Alert::get($this->aid);
			$alert->delete();
		} catch (AlertException $e) {}
		
		if ($this->reply_aid !== null) try {
			$alert = Alert::get($this->reply_aid);
			$alert->delete();
		} catch (AlertException $e) {}
		
		if ($this->indexing_status == $INDEXING_STATUS['INDEXED']) {
			$wordlist = DiscussionPostIndexList::getByOid($this->oid);
			foreach ($wordlist as $word) {
				try {
					$discussionpostindex = DiscussionPostIndex::get($word, $this->oid);
					$discussionpostindex->delete();
				} catch (DiscussionPostIndexException $e) {}
			}
			$wordlist = CommentIndexList::getByOid($this->oid);
			foreach ($wordlist as $word) {
				try {
					$commentindex = CommentIndex::get($word, $this->oid);
					$commentindex->delete();
				} catch (CommentIndexException $e) {}
			}
		}
		
		// Remove from associated lists
		
		DiscussionPostList::deleteByIndexingStatusAndStatus($this->indexing_status, $this->status);
		DiscussionPostList::deleteByNidAndStatus($this->nid, $this->status);
		DiscussionPostList::deleteByUidAndStatus($this->uid, $this->status);
		DiscussionPostList::deleteByNid($this->nid);
	}
	
	public function getOid() { return $this->oid; }
	
	public function setOid($new_oid) { $this->oid = $new_oid; }
	
	public function getReplyToOid() { return $this->reply_to_oid; }
	
	public function setReplyToOid($new_reply_to_oid, $persist = true) { 
		$old_reply_to_oid = $this->reply_to_oid;
		$this->reply_to_oid = $new_reply_to_oid;
		
		if ($persist) {
			if ($new_reply_to_oid === null) {
				DiscussionPost::prepareStatement(DiscussionPost::statement_setNullReplyToOid);
				
				$start_timestamp = microtime(true);
				DB::incrementRequestCount();
				DiscussionPost::$statement[DiscussionPost::statement_setNullReplyToOid]->execute($this->oid);
				Log::trace('DB', 'Executed DiscussionPost::statement_setNullReplyToOid ['.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
			} else {
				DiscussionPost::prepareStatement(DiscussionPost::statement_setReplyToOid);
				
				$start_timestamp = microtime(true);
				DB::incrementRequestCount();
				DiscussionPost::$statement[DiscussionPost::statement_setReplyToOid]->execute(array($this->reply_to_oid, $this->oid));
				Log::trace('DB', 'Executed DiscussionPost::statement_setReplyToOid ['.$this->reply_to_oid.', '.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
			}
			
			$this->saveCache();

			DiscussionPostList::deleteByReplyToOid($old_reply_to_oid, $this->status);
			DiscussionPostList::deleteByReplyToOid($this->reply_to_oid, $this->status);
		}
	}
	
	public function getNid() { return $this->nid; }
	
	public function setNid($new_nid) { $this->nid = $new_nid; }
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid, $persist=true) {
		$old_uid = $this->uid;
		$this->uid = $new_uid;
		
		if ($persist) {
			DiscussionPost::prepareStatement(DiscussionPost::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionPost::$statement[DiscussionPost::statement_setUid]->execute(array($this->uid, $this->oid));
			Log::trace('DB', 'Executed DiscussionPost::statement_setUid ['.$this->uid.', '.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();

			DiscussionPostList::deleteByUidAndStatus($old_uid, $this->status);
			DiscussionPostList::deleteByUidAndStatus($this->uid, $this->status);
		}
	}
	
	public function getAid() { return $this->aid; }
	
	public function setAid($new_aid, $persist=true) {
		$this->aid = $new_aid;
		
		if ($persist) {
			DiscussionPost::prepareStatement(DiscussionPost::statement_setAid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionPost::$statement[DiscussionPost::statement_setAid]->execute(array($this->aid, $this->oid));
			Log::trace('DB', 'Executed DiscussionPost::statement_setAid ['.$this->aid.', '.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getReplyAid() { return $this->reply_aid; }
	
	public function setReplyAid($new_reply_aid, $persist=true) {
		$this->reply_aid = $new_reply_aid;
		
		if ($persist) {
			DiscussionPost::prepareStatement(DiscussionPost::statement_setReplyAid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionPost::$statement[DiscussionPost::statement_setReplyAid]->execute(array($this->reply_aid, $this->oid));
			Log::trace('DB', 'Executed DiscussionPost::statement_setReplyAid ['.$this->reply_aid.', '.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time) { $this->creation_time = $new_creation_time; }

	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			DiscussionPost::prepareStatement(DiscussionPost::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionPost::$statement[DiscussionPost::statement_setStatus]->execute(array($this->status, $this->oid));
			Log::trace('DB', 'Executed DiscussionPost::statement_setStatus ['.$this->status.', '.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			DiscussionPostList::deleteByNidAndStatus($this->nid, $old_status);
			DiscussionPostList::deleteByNidAndStatus($this->nid, $this->status);
			DiscussionPostList::deleteByUidAndStatus($this->uid, $old_status);
			DiscussionPostList::deleteByUidAndStatus($this->uid, $this->status);
		}
	}
	
	public function getText() { return $this->text; }
	
	public function setText($new_text, $persist=true) {
		$this->text = $new_text;
		
		if ($persist) {
			DiscussionPost::prepareStatement(DiscussionPost::statement_setText);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionPost::$statement[DiscussionPost::statement_setText]->execute(array($this->text, $this->oid));
			Log::trace('DB', 'Executed DiscussionPost::statement_setText ['.$this->text.', '.$this->oid.'], ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getIndexingStatus() { return $this->indexing_status; }
	
	public function setIndexingStatus($new_indexing_status, $persist=true) {
		$old_indexing_status = $this->indexing_status;
		$this->indexing_status = $new_indexing_status;
		
		if ($persist) {
			DiscussionPost::prepareStatement(DiscussionPost::statement_setIndexingStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			DiscussionPost::$statement[DiscussionPost::statement_setIndexingStatus]->execute(array($this->indexing_status, $this->oid));
			Log::trace('DB', 'Executed DiscussionPost::statement_setIndexingStatus ['.$this->indexing_status.', '.$this->oid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();

			DiscussionPostList::deleteByIndexingStatusAndStatus($old_indexing_status, $this->status);
			DiscussionPostList::deleteByIndexingStatusAndStatus($this->indexing_status, $this->status);
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(DiscussionPost::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case DiscussionPost::statement_get:
					DiscussionPost::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['REPLY_TO_OID'].', '.$COLUMN['NID'].', '.$COLUMN['UID']
						.', '.$COLUMN['STATUS'].', '.$COLUMN['OID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.', '.$COLUMN['TEXT'].', '.$COLUMN['AID'].', '.$COLUMN['REPLY_AID']
						.', '.$COLUMN['INDEXING_STATUS']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST']
						.' WHERE '.$COLUMN['OID'].' = ?'
								, array('integer'));
					break;
				case DiscussionPost::statement_create:
					DiscussionPost::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST'].'( '.$COLUMN['NID']
						.', '.$COLUMN['UID'].', '.$COLUMN['TEXT'].', '.$COLUMN['REPLY_TO_OID'].', '.$COLUMN['STATUS']
						.') VALUES(?, ?, ?, ?, ?)', array('integer', 'text', 'text', 'integer', 'integer'));
					break;	
				case DiscussionPost::statement_setText:
					DiscussionPost::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_POST'], array($COLUMN['OID'] => 'integer'), $COLUMN['TEXT'], 'text');
					break;
				case DiscussionPost::statement_setStatus:
					DiscussionPost::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_POST'], array($COLUMN['OID'] => 'integer'), $COLUMN['STATUS'], 'integer');
					break;
				case DiscussionPost::statement_delete:
					DiscussionPost::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST']
						.' WHERE '.$COLUMN['OID'].' = ?'
						, array('integer'));
					break;	
				case DiscussionPost::statement_setUid:
					DiscussionPost::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_POST'], array($COLUMN['OID'] => 'integer'), $COLUMN['UID'], 'text');
					break;
				case DiscussionPost::statement_setReplyToOid:
					DiscussionPost::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_POST'], array($COLUMN['OID'] => 'integer'), $COLUMN['REPLY_TO_OID'], 'integer');
					break;
				case DiscussionPost::statement_setNullReplyToOid:
					DiscussionPost::$statement[$statement] = DB::prepareWrite( 
						'UPDATE '.$DATABASE['PREFIX'].$TABLE['DISCUSSION_POST'].' SET '.$COLUMN['REPLY_TO_OID']
						.' = NULL WHERE '.$COLUMN['OID']
						.' = ?', array('integer'));
					break;	
				case DiscussionPost::statement_setAid:
					DiscussionPost::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_POST'], array($COLUMN['OID'] => 'integer'), $COLUMN['AID'], 'integer');
					break;
				case DiscussionPost::statement_setReplyAid:
					DiscussionPost::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_POST'], array($COLUMN['OID'] => 'integer'), $COLUMN['REPLY_AID'], 'integer');
					break;
				case DiscussionPost::statement_setIndexingStatus:
					DiscussionPost::$statement[$statement] = DB::prepareSetter($TABLE['DISCUSSION_POST'], array($COLUMN['OID'] => 'integer'), $COLUMN['INDEXING_STATUS'], 'integer');
					break;
			}
		}
	}
}

?>