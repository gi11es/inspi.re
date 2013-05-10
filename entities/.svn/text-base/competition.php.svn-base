<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once('MDB2/Date.php');

class CompetitionException extends Exception {}

class Competition implements Persistent {
	private $cid;
	private $xid;
	private $tid;
	private $start_time;
	private $vote_time;
	private $end_time;
	private $status;
	private $entries_count = 0;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setStatus = 4;
	const statement_setEntriesCount = 5;
	const statement_setXid = 6;
	
    const cache_prefix = 'Competition-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of competition with cid='.$this->cid);
		
		try {
			Cache::replaceorset(Competition::cache_prefix.$this->cid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of competition with cid='.$this->cid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 5)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4]);
    }
	
	public function __construct2($xid, $tid, $start_time, $vote_time, $end_time) {
		global $COMPETITION_STATUS;
		
		Competition::prepareStatement(Competition::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Competition::$statement[Competition::statement_create]->execute(array($xid, $tid, MDB2_Date::unix2Mdbstamp($start_time), MDB2_Date::unix2Mdbstamp($vote_time), MDB2_Date::unix2Mdbstamp($end_time), $COMPETITION_STATUS['OPEN']));
		Log::trace('DB', 'Executed Competition::statement_create ['.$xid.', '.$tid.', '.$start_time.', '.$vote_time.', '.$end_time.', '.$COMPETITION_STATUS['OPEN'].'] ('.(microtime(true) - $start_timestamp).')');
		
		$cid = DB::insertid();

		$this->setCid($cid);
		$this->setXid($xid, false);
		$this->setTid($tid);
		$this->setStartTime($start_time);
		$this->setVoteTime($vote_time);
		$this->setEndTime($end_time);
		$this->setStatus($COMPETITION_STATUS['OPEN'], false);
		$this->saveCache();
		
		CompetitionList::deleteByXidAndStatus($xid, $COMPETITION_STATUS['OPEN']);
		CompetitionList::deleteByXid($xid);
		CompetitionList::deleteByStatus($COMPETITION_STATUS['OPEN']);
	}
	
	public static function get($cid) {
		if ($cid === null) throw new CompetitionException('No competition for cid='.$cid);
		
		try {
			$competition = Cache::get(Competition::cache_prefix.$cid);
		} catch (CacheException $e) {
			Competition::prepareStatement(Competition::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = Competition::$statement[Competition::statement_get]->execute($cid);
			Log::trace('DB', 'Executed Competition::statement_get ['.$cid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new CompetitionException('No competition for cid='.$cid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$competition = new Competition();
			$competition->populateFields($row);
			$competition->saveCache();
		}
		return $competition;
	}
	
	public static function getArray($cidlist) {
		$result = array();
		$querylist = array();
		
		foreach ($cidlist as $cid) $querylist []= Competition::cache_prefix.$cid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($cidlist as $cid) try {
			if (isset($cacheresult[Competition::cache_prefix.$cid])) $result[$cid] = $cacheresult[Competition::cache_prefix.$cid];
			else $result[$cid] = Competition::get($cid);
		} catch (CompetitionException $e) {}
		
		return $result;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setCid($row[$COLUMN['CID']]);
		$this->setXid($row[$COLUMN['XID']], false);
		$this->setTid($row[$COLUMN['TID']]);
		$this->setStartTime($row[$COLUMN['START_TIME']]);
		$this->setVoteTime($row[$COLUMN['VOTE_TIME']]);
		$this->setEndTime($row[$COLUMN['END_TIME']]);
		$this->setStatus($row[$COLUMN['STATUS']], false);
		$this->setEntriesCount($row[$COLUMN['ENTRIES_COUNT']], false);
	}
	
	public function delete() {
		Competition::prepareStatement(Competition::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Competition::$statement[Competition::statement_delete]->execute($this->cid);
		Log::trace('DB', 'Executed Competition::statement_delete ['.$this->cid.'] ('.(microtime(true) - $start_timestamp).')');

		try { Cache::delete(Competition::cache_prefix.$this->cid); } catch (CacheException $e) {}
		
		$entrylist = EntryList::getByCid($this->cid);
		foreach ($entrylist as $uid => $eid) {
			try {
				$entry = Entry::get($eid);
				$entry->delete();
			} catch (EntryException $e) {}
		}
		
		try {
			$theme = Theme::get($this->tid);
			$theme->delete();
		} catch (ThemeException $e) {}
		
		// Remove from associated lists
		
		CompetitionList::deleteByXidAndStatus($this->xid, $this->status);
		CompetitionList::deleteByXid($this->xid);
		CompetitionList::deleteByStatus($this->status);
	}
	
	public function getCid() { return $this->cid; }
	
	public function setCid($new_cid) { $this->cid = $new_cid; }
	
	public function getXid() { return $this->xid; }
	
	public function setXid($new_xid, $persist=true) {
		$old_xid = $this->xid;
		$this->xid = $new_xid;
		
		if ($persist) {
			Competition::prepareStatement(Competition::statement_setXid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Competition::$statement[Competition::statement_setXid]->execute(array($this->xid, $this->cid));
			Log::trace('DB', 'Executed Competition::statement_setXid ['.$this->xid.', '.$this->cid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			CompetitionList::deleteByXidAndStatus($this->xid, $this->status);
			CompetitionList::deleteByXid($this->xid);
			CompetitionList::deleteByXidAndStatus($old_xid, $this->status);
			CompetitionList::deleteByXid($old_xid);
		}
	}
	
	public function getTid() { return $this->tid; }
	
	public function setTid($new_tid) { $this->tid = $new_tid; }
	
	public function getStartTime() { return $this->start_time; }
	
	public function setStartTime($new_start_time) { $this->start_time = $new_start_time; }
	
	public function getVoteTime() { return $this->vote_time; }
	
	public function setVoteTime($new_vote_time) { $this->vote_time = $new_vote_time; }
	
	public function getEndTime() { return $this->end_time; }
	
	public function setEndTime($new_end_time) { $this->end_time = $new_end_time; }

	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			Competition::prepareStatement(Competition::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Competition::$statement[Competition::statement_setStatus]->execute(array($this->status, $this->cid));
			Log::trace('DB', 'Executed Competition::statement_setStatus ['.$this->status.', '.$this->cid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			CompetitionList::deleteByXidAndStatus($this->xid, $old_status);
			CompetitionList::deleteByXidAndStatus($this->xid, $new_status);
			CompetitionList::deleteByStatus($old_status);
			CompetitionList::deleteByStatus($new_status);
		}
	}
	
	public function getEntriesCount() { return $this->entries_count; }
	
	public function setEntriesCount($new_entries_count, $persist=true) {
		$this->entries_count = $new_entries_count;
		
		if ($persist) {
			Competition::prepareStatement(Competition::statement_setEntriesCount);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Competition::$statement[Competition::statement_setEntriesCount]->execute(array($this->entries_count, $this->cid));
			Log::trace('DB', 'Executed Competition::statement_setEntriesCount ['.$this->entries_count.', '.$this->cid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getBannedRanks() {
		global $ENTRY_STATUS;
		global $ENTRY_VOTE_STATUS;
		global $ALERT_TEMPLATE_ID;
		global $ALERT_INSTANCE_STATUS;
		
		$entrylist = EntryList::getByCidAndStatus($this->cid, $ENTRY_STATUS['POSTED']);
		$entrylist += EntryList::getByCidAndStatus($this->cid, $ENTRY_STATUS['DELETED']);
		$entrylist += EntryList::getByCidAndStatus($this->cid, $ENTRY_STATUS['BANNED']);
		
		$entryscore = array();
		foreach ($entrylist as $uid => $eid) {
			$entryvotelist = EntryVoteList::getByEidAndStatus($eid, $ENTRY_VOTE_STATUS['CAST']);
			$entryscore[$eid] = array_sum($entryvotelist);
		}
		
		arsort($entryscore);
		
		$rank = 0;
		$last_score = null;
		$samerank = 1;
		$ranks = array();
		
		foreach ($entryscore as $eid => $score) {
			$entry = Entry::get($eid);
			if ($score != $last_score) {
				$rank += $samerank;
				$samerank = 0;
			}
			$samerank++;
			
			$ranks[$eid] = $rank;
			
			$last_score = $score;
		}
		
		arsort($ranks);
		
		return $ranks;
	}
	
	public function calculateRankings($disqualified=false) {
		global $ENTRY_STATUS;
		global $ENTRY_VOTE_STATUS;
		global $ALERT_TEMPLATE_ID;
		global $ALERT_INSTANCE_STATUS;
		global $PAGE;
		global $USER_STATUS;
		
		$entrylist = EntryList::getByCidAndStatus($this->cid, $ENTRY_STATUS['POSTED']);
		$entrylist += EntryList::getByCidAndStatus($this->cid, $ENTRY_STATUS['DELETED']);
		
		$entryscore = array();
		foreach ($entrylist as $uid => $eid) {
			$entryvotelist = EntryVoteList::getByEidAndStatus($eid, $ENTRY_VOTE_STATUS['CAST']);
			$entryscore[$eid] = array_sum($entryvotelist);
		}
		
		arsort($entryscore);
		
		$rank = 0;
		$last_score = null;
		$samerank = 1;
		
		$this->setEntriesCount(count($entrylist));
		
		foreach ($entryscore as $eid => $score) {
			$entry = Entry::get($eid);
			if ($score != $last_score) {
				$rank += $samerank;
				$samerank = 0;
			}
			$samerank++;
			$last_score = $score;
			
			if ($entry->getRank() != $rank) {
				$entry->setRank($rank);
				if ($disqualified) {
					try {
						$author = User::get($entry->getUid());
					} catch (UserException $e) {
						continue;
					}
					
					if ($author->getStatus() == $USER_STATUS['ACTIVE']) {
						$alert = new Alert($ALERT_TEMPLATE_ID['OTHER_DISQUALIFIED']);
						$aid = $alert->getAid();
						$alert_variable = new AlertVariable($aid, 'tid', $this->tid);
						$alert_variable = new AlertVariable($aid, 'rank', $rank);
						$alert_variable = new AlertVariable($aid, 'href', $PAGE['ENTRY'].'?lid='.$author->getLid().'#eid='.$eid);
						$alert_variable = new AlertVariable($aid, 'entries_count', count($entrylist));
						$alert_instance = new AlertInstance($aid, $entry->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);
					}
				}
			}
			
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(Competition::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case Competition::statement_get:
					Competition::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['CID'].', '.$COLUMN['XID'].', '.$COLUMN['TID']
						.', UNIX_TIMESTAMP('.$COLUMN['START_TIME'].') AS '.$COLUMN['START_TIME']
						.', UNIX_TIMESTAMP('.$COLUMN['VOTE_TIME'].') AS '.$COLUMN['VOTE_TIME']
						.', UNIX_TIMESTAMP('.$COLUMN['END_TIME'].') AS '.$COLUMN['END_TIME']
						.', '.$COLUMN['STATUS'].', '.$COLUMN['ENTRIES_COUNT']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMPETITION']
						.' WHERE '.$COLUMN['CID'].' = ?'
								, array('integer'));
					break;
				case Competition::statement_create:
					Competition::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['COMPETITION']
						.'( '.$COLUMN['XID'].', '.$COLUMN['TID']
						.', '.$COLUMN['START_TIME']
						.', '.$COLUMN['VOTE_TIME']
						.', '.$COLUMN['END_TIME'].', '.$COLUMN['STATUS']
						.') VALUES(?, ?, ?, ?, ?, ?)', array('integer', 'integer', 'timestamp', 'timestamp', 'timestamp', 'integer'));
					break;	
				case Competition::statement_delete:
					Competition::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['COMPETITION']
						.' WHERE '.$COLUMN['CID'].' = ?'
						, array('integer'));
					break;
				case Competition::statement_setStatus:
					Competition::$statement[$statement] = DB::prepareSetter($TABLE['COMPETITION'], array($COLUMN['CID'] => 'integer'), $COLUMN['STATUS'], 'integer');
					break;
				case Competition::statement_setEntriesCount:
					Competition::$statement[$statement] = DB::prepareSetter($TABLE['COMPETITION'], array($COLUMN['CID'] => 'integer'), $COLUMN['ENTRIES_COUNT'], 'integer');
					break;
				case Competition::statement_setXid:
					Competition::$statement[$statement] = DB::prepareSetter($TABLE['COMPETITION'], array($COLUMN['CID'] => 'integer'), $COLUMN['XID'], 'integer');
					break;
			}
		}
	}
}

?>