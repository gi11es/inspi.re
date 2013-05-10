<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/communitylist.php');
require_once(dirname(__FILE__).'/../entities/communitylabel.php');
require_once(dirname(__FILE__).'/../entities/communitylabellist.php');
require_once(dirname(__FILE__).'/../entities/communitymembership.php');
require_once(dirname(__FILE__).'/../entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/../entities/communitymoderator.php');
require_once(dirname(__FILE__).'/../entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once('MDB2/Date.php');

class CommunityException extends Exception {}

class Community implements Persistent {
	private $xid;
	private $name;
	private $description;
	private $rules;
	private $frequency;
	private $enter_length;
	private $vote_length;
	private $time_shift;
	private $uid;
	private $lid;
	private $pid;
	private $status;
	private $creation_time;
	private $maximum_theme_count;
	private $maximum_theme_count_per_member;
	private $theme_minimum_score;
	private $theme_restrict_users = false;
	private $theme_cost = 5;
	private $deletion_points = 250;
	private $active_member_count = 0;
	private $inactive_since = null;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	const statement_setName = 4;
	const statement_setRules = 5;
	const statement_setUid = 6;
	const statement_setPid = 7;
	const statement_setStatus = 8;
	const statement_setLid = 9;
	const statement_setDescription = 10;
	const statement_setFrequency = 11;
	const statement_setTimeShift = 12;
	const statement_setMaximumThemeCount = 13;
	const statement_setMaximumThemeCountPerMember = 14;
	const statement_setThemeMinimumScore = 15;
	const statement_setEnterLength = 16;
	const statement_setVoteLength = 17;
	const statement_setThemeRestrictUsers = 18;
	const statement_setThemeCost = 19;
	const statement_setActiveMemberCount = 20;
	const statement_setInactiveSince = 21;
	
    const cache_prefix = 'Community-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of community with xid='.$this->xid);
		
		try {
			Cache::replaceorset(Community::cache_prefix.$this->xid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of community with xid='.$this->xid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 17)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6], $argv[7], $argv[8], $argv[9], $argv[10], $argv[11], $argv[12], $argv[13], $argv[14], $argv[15], $argv[16]);
		elseif (func_num_args() == 16)
			self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6], $argv[7], $argv[8], $argv[9], $argv[10], $argv[11], $argv[12], $argv[13], $argv[14], $argv[15]);
    }
	
	public function __construct2($name, $description, $rules, $frequency, $enter_length, $vote_length, $time_shift, $maximum_theme_count, $maximum_theme_count_per_member, $theme_minimum_score, $theme_restrict_users, $theme_cost, $uid, $lid, $status, $deletion_points, $pid = null) {
		Community::prepareStatement(Community::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Community::$statement[Community::statement_create]->execute(array($name, $description, $rules, $frequency, $enter_length, $vote_length, $time_shift, $maximum_theme_count, $maximum_theme_count_per_member, $theme_minimum_score, $theme_restrict_users, $theme_cost, $uid, $lid, $pid, $status, $deletion_points));
		Log::trace('DB', 'Executed Community::statement_create ["'.$name.'", "'.$description.'", "'.$rules.'", '.$frequency.', '.$enter_length.', '.$vote_length.', '.$time_shift.', '.$maximum_theme_count.', '.$maximum_theme_count_per_member.', '.$theme_minimum_score.', '.$theme_restrict_users.', '.$theme_cost.', '.$uid.', '.$lid.', '.$pid.', '.$status.', '.$deletion_points.'] ('.(microtime(true) - $start_timestamp).')');
		$xid = DB::insertid();

		$this->setXid($xid);
		$this->setUid($uid, false);
		$this->setLid($lid, false);
		$this->setName($name, false);
		$this->setDescription($description, false);
		$this->setRules($rules, false);
		$this->setFrequency($frequency, false);
		$this->setEnterLength($enter_length, false);
		$this->setVoteLength($vote_length, false);
		$this->setTimeShift($time_shift, false);
		$this->setMaximumThemeCount($maximum_theme_count, false);
		$this->setMaximumThemeCountPerMember($maximum_theme_count_per_member, false);
		$this->setThemeMinimumScore($theme_minimum_score, false);
		$this->setThemeRestrictUsers($theme_restrict_users, false);
		$this->setThemeCost($theme_cost, false);
		$this->setStatus($status, false);
		$this->setCreationTime(time());
		$this->setDeletionPoints($deletion_points);
		$this->saveCache();
		
		CommunityList::deleteByStatus($status);
		CommunityList::deleteByUidAndStatus($uid, $status);
		CommunityList::deleteByLidAndStatus($lid, $status);
	}
	
	public static function get($xid, $cache = true) {
		if ($xid === null) throw new CommunityException('No community for that xid: '.$xid);
		
		try {
			$community = Cache::get(Community::cache_prefix.$xid);
		} catch (CacheException $e) {
			Community::prepareStatement(Community::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = Community::$statement[Community::statement_get]->execute($xid);
			Log::trace('DB', 'Executed Community::statement_get ['.$xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new CommunityException('No community for that xid: '.$xid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$community = new Community();
			$community->populateFields($row);
			
			if ($cache) $community->saveCache();
		}
		return $community;
	}
	
	public static function getArray($xidlist, $cache = true) {
		$result = array();
		$querylist = array();
		
		foreach ($xidlist as $xid) $querylist []= Community::cache_prefix.$xid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($xidlist as $xid) try {
			if (isset($cacheresult[Community::cache_prefix.$xid])) $result[$xid] = $cacheresult[Community::cache_prefix.$xid];
			else $result[$xid] = Community::get($xid, $cache);
		} catch (CommunityException $e) {}
		
		return $result;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setXid($row[$COLUMN['XID']]);
		$this->setName($row[$COLUMN['NAME']], false);
		$this->setDescription($row[$COLUMN['DESCRIPTION']], false);
		$this->setRules($row[$COLUMN['RULES']], false);
		$this->setFrequency($row[$COLUMN['FREQUENCY']], false);
		$this->setEnterLength($row[$COLUMN['ENTER_LENGTH']], false);
		$this->setVoteLength($row[$COLUMN['VOTE_LENGTH']], false);
		$this->setTimeShift($row[$COLUMN['TIME_SHIFT']], false);
		$this->setMaximumThemeCount($row[$COLUMN['MAXIMUM_THEME_COUNT']], false);
		$this->setMaximumThemeCountPerMember($row[$COLUMN['MAXIMUM_THEME_COUNT_PER_MEMBER']], false);
		$this->setThemeMinimumScore($row[$COLUMN['THEME_MINIMUM_SCORE']], false);
		$this->setUid($row[$COLUMN['UID']], false);
		$this->setLid($row[$COLUMN['LID']], false);
		$this->setPid($row[$COLUMN['PID']], false);
		$this->setStatus($row[$COLUMN['STATUS']], false);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']]);
		$this->setDeletionPoints($row[$COLUMN['DELETION_POINTS']]);
		$this->setThemeRestrictUsers($row[$COLUMN['THEME_RESTRICT_USERS']], false);
		$this->setThemeCost($row[$COLUMN['THEME_COST']], false);
		$this->setActiveMemberCount($row[$COLUMN['ACTIVE_MEMBER_COUNT']], false);
		$this->setInactiveSince($row[$COLUMN['INACTIVE_SINCE']], false);
	}
	
	public function delete() {
		global $ALERT_TEMPLATE_ID;
		global $ALERT_INSTANCE_STATUS;
		global $USER_STATUS;
		
		Community::prepareStatement(Community::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Community::$statement[Community::statement_delete]->execute($this->xid);
		Log::trace('DB', 'Executed Community::statement_delete ['.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(Community::cache_prefix.$this->xid); } catch (CacheException $e) {}
		
		// Some values changed, we need to send an alert to the users
		$alert = new Alert($ALERT_TEMPLATE_ID['COMMUNITY_DELETE']);
		$aid = $alert->getAid();
		$alert_variable = new AlertVariable($aid, 'name', $this->name);
		
		$membershiplist = CommunityMembershipList::getByXid($this->xid);
		foreach ($membershiplist as $uid => $join_time) {
			try {
				$user = User::get($uid);
			} catch (UserException $f) {
				continue;
			}
			
			// Don't send alerts to deleted/banned users
			if ($user->getStatus() != $USER_STATUS['ACTIVE']) {
				continue;
			}
			
			$alert_instance = new AlertInstance($aid, $uid, $ALERT_INSTANCE_STATUS['ASYNC']);
			
			try {
				$membership = CommunityMembership::get($this->xid, $uid);
			} catch (CommunityMembershipException $e) {
				continue;
			}
			$membership->delete();
		}	

		
		$moderatorlist = CommunityModeratorList::getByXid($this->xid);
		foreach ($moderatorlist as $uid) {
			try {
				$moderator = CommunityModerator::get($this->xid, $uid);
				$moderator->delete();
			} catch (CommunityModeratorException $e) {}
		}
		
		$labellist = CommunityLabelList::getByXid($this->xid);
		foreach ($labellist as $clid) {
			try {
				$label = CommunityLabel::get($this->xid, $clid);
				$label->delete();
			} catch (CommunityLabelException $e) {}
		}
		
		$discussionthreadlist = DiscussionThreadList::getByXid($this->xid);
		foreach ($discussionthreadlist as $nid => $creation_time) {
			try {
				$discussionthread = DiscussionThread::get($nid);
				$discussionthread->delete();
			} catch (DiscussionThreadException $e) {}
		}
		
		$themelist = ThemeList::getByXid($this->xid);
		foreach ($themelist as $tid) {
			try {
				$theme = Theme::get($tid);
				$theme->delete();
			} catch (ThemeException $e) {}
		}
		
		$competitionlist = CompetitionList::getByXid($this->xid);
		foreach ($competitionlist as $cid => $start_time) {
			try {
				$competition = Competition::get($cid);
				$competition->delete();
			} catch (CompetitionException $e) {}
		}
		
		// Remove from associated lists
		
		CommunityList::deleteByStatus($this->status);
		CommunityList::deleteByUidAndStatus($this->uid, $this->status);
		CommunityList::deleteByLidAndStatus($this->lid, $this->status);
	}
	
	public function getXid() { return $this->xid; }
	
	public function setXid($new_xid) { $this->xid = $new_xid; }
	
	public function getName() { return $this->name; }
	
	public function setName($new_name, $persist=true) {
		$this->name = $new_name;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setName);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setName]->execute(array($this->name, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setName ["'.$this->name.'", '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getDescription() { return $this->description; }
	
	public function setDescription($new_description, $persist=true) {
		$this->description = $new_description;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setDescription);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setDescription]->execute(array($this->description, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setDescription ["'.$this->description.'", '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			$this->saveCache();
		}
	}
	
	public function getRules() { return $this->rules; }
	
	public function setRules($new_rules, $persist=true) {
		$this->rules = $new_rules;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setRules);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setRules]->execute(array($this->rules, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setRules ["'.$this->rules.'", '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getFrequency() { return $this->frequency; }
	
	public function setFrequency($new_frequency, $persist=true) {
		$this->frequency = $new_frequency;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setFrequency);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setFrequency]->execute(array($this->frequency, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setFrequency ['.$this->frequency.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getEnterLength() { return $this->enter_length; }
	
	public function setEnterLength($new_enter_length, $persist=true) {
		$this->enter_length = $new_enter_length;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setEnterLength);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setEnterLength]->execute(array($this->enter_length, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setEnterLength ['.$this->enter_length.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getVoteLength() { return $this->vote_length; }
	
	public function setVoteLength($new_vote_length, $persist=true) {
		$this->vote_length = $new_vote_length;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setVoteLength);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setVoteLength]->execute(array($this->vote_length, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setVoteLength ['.$this->vote_length.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getTimeShift() { return $this->time_shift; }
	
	public function setTimeShift($new_time_shift, $persist=true) {
		$this->time_shift = $new_time_shift;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setTimeShift);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setTimeShift]->execute(array($this->time_shift, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setTimeShift ['.$this->time_shift.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getMaximumThemeCount() { return $this->maximum_theme_count; }
	
	public function setMaximumThemeCount($new_maximum_theme_count, $persist=true) {
		$this->maximum_theme_count = $new_maximum_theme_count;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setMaximumThemeCount);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setMaximumThemeCount]->execute(array($this->maximum_theme_count, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setMaximumThemeCount ['.$this->maximum_theme_count.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getMaximumThemeCountPerMember() { return $this->maximum_theme_count_per_member; }
	
	public function setMaximumThemeCountPerMember($new_maximum_theme_count_per_member, $persist=true) {
		$this->maximum_theme_count_per_member = $new_maximum_theme_count_per_member;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setMaximumThemeCountPerMember);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setMaximumThemeCountPerMember]->execute(array($this->maximum_theme_count_per_member, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setMaximumThemeCountPerMember ['.$this->maximum_theme_count_per_member.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getThemeMinimumScore() { return $this->theme_minimum_score; }
	
	public function setThemeMinimumScore($new_theme_minimum_score, $persist=true) {
		$this->theme_minimum_score = $new_theme_minimum_score;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setThemeMinimumScore);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setThemeMinimumScore]->execute(array($this->theme_minimum_score, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setThemeMinimumScore ['.$this->theme_minimum_score.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getThemeRestrictUsers() { return $this->theme_restrict_users; }
	
	public function setThemeRestrictUsers($new_theme_restrict_users, $persist=true) {
		$this->theme_restrict_users = $new_theme_restrict_users;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setThemeRestrictUsers);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setThemeRestrictUsers]->execute(array($this->theme_restrict_users, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setThemeRestrictUsers ['.$this->theme_restrict_users.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getThemeCost() { return $this->theme_cost; }
	
	public function setThemeCost($new_theme_cost, $persist=true) {
		$this->theme_cost = $new_theme_cost;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setThemeCost);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setThemeCost]->execute(array($this->theme_cost, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setThemeCost ['.$this->theme_cost.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid, $persist=true) {
		$old_uid = $this->uid;
		$this->uid = $new_uid;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setUid]->execute(array($this->uid, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setUid ['.$this->uid.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			CommunityList::deleteByUidAndStatus($old_uid, $this->status);
			CommunityList::deleteByUidAndStatus($new_uid, $this->status);
		}
	}
	
	public function getLid() { return $this->lid; }
	
	public function setLid($new_lid, $persist=true) {
		$old_lid = $this->lid;
		$this->lid = $new_lid;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setLid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setLid]->execute(array($this->lid, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setLid ['.$this->lid.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			CommunityList::deleteByLidAndStatus($old_lid, $this->status);
			CommunityList::deleteByLidAndStatus($new_lid, $this->status);
		}
	}
	
	public function getPid() { return $this->pid; }
	
	public function setPid($new_pid, $persist=true) {
		$old_pid = $this->pid;
		$this->pid = $new_pid;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setPid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setPid]->execute(array($this->pid, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setPid ['.$this->pid.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time) { $this->creation_time = $new_creation_time; }
	
	public function getDeletionPoints() { return $this->deletion_points; }
	
	public function setDeletionPoints($new_deletion_points) { $this->deletion_points = $new_deletion_points; }

	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setStatus]->execute(array($this->status, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setStatus ['.$this->status.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			CommunityList::deleteByStatus($old_status);
			CommunityList::deleteByStatus($new_status);
			CommunityList::deleteByUidAndStatus($this->uid, $old_status);
			CommunityList::deleteByUidAndStatus($this->uid, $new_status);
			CommunityList::deleteByLidAndStatus($this->lid, $old_status);
			CommunityList::deleteByLidAndStatus($this->lid, $new_status);
		}
	}
	
	public function getNextCompetitionTime($timestamp=null) {
		$reference_time = $timestamp == null ?gmmktime():$timestamp;
		$next_time = gmmktime(0, 0, 0, gmdate('n', $reference_time), gmdate('j', $reference_time), gmdate('Y', $reference_time)) + $this->getTimeShift();

		while ($next_time < $reference_time) {
			$next_time += 86400; // Add 24 hours until we reach the next date
		}
		
		return $next_time;
	}
	
	public function getActiveMemberCount() { return $this->active_member_count; }
	
	public function setActiveMemberCount($new_active_member_count, $persist=true) {
		$this->active_member_count = $new_active_member_count;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setActiveMemberCount);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setActiveMemberCount]->execute(array($this->active_member_count, $this->xid));
			Log::trace('DB', 'Executed Community::statement_setActiveMemberCount ['.$this->active_member_count.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getInactiveSince() { return $this->inactive_since; }
	
	public function setInactiveSince($new_inactive_since, $persist=true) {
		$this->inactive_since = $new_inactive_since;
		
		if ($persist) {
			Community::prepareStatement(Community::statement_setInactiveSince);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Community::$statement[Community::statement_setInactiveSince]->execute(array(MDB2_Date::unix2Mdbstamp($this->inactive_since), $this->xid));
			Log::trace('DB', 'Executed Community::statement_setInactiveSince ['.$this->inactive_since.', '.$this->xid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function startNextCompetition() {
		global $THEME_STATUS;
		global $USER_STATUS;
		global $ALERT_TEMPLATE_ID;
		global $ALERT_INSTANCE_STATUS;
		global $PAGE;
		
		$themelist = Theme::getArray(ThemeList::getByXidAndStatus($this->xid, $THEME_STATUS['SUGGESTED']));
		
		if (!empty($themelist)) {
			$score = array();
			
			foreach ($themelist as $tid => $theme) {
				$score[$tid] = $theme->getScore();
			}
		
			arsort($score);
			
			$top_tid = array_shift(array_keys($score));
			
			$start_time = gmmktime();
			$vote_time = $start_time + $this->getEnterLength() * 86400;
			$end_time = $vote_time + $this->getVoteLength() * 86400;
							
			$competition = new Competition($this->xid, $top_tid, $start_time, $vote_time, $end_time);
			$themelist[$top_tid]->setStatus($THEME_STATUS['SELECTED']);
			
			try {
				$theme_author = User::get($themelist[$top_tid]->getUid());
				if ($theme_author->getStatus() == $USER_STATUS['ACTIVE']) {
					$alert = new Alert($ALERT_TEMPLATE_ID['THEME_TRANSITIONED']);
					$aid = $alert->getAid();
					$alert_variable = new AlertVariable($aid, 'href', $PAGE['COMPETE'].'?lid='.$theme_author->getLid().'&highlight='.$competition->getCid());
					$alert_variable = new AlertVariable($aid, 'tid', $themelist[$top_tid]->getTid());
					$alert_variable = new AlertVariable($aid, 'xid', $themelist[$top_tid]->getXid());
					$alert_instance = new AlertInstance($aid, $themelist[$top_tid]->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);
				}
			} catch (UserException $e) {}
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(Community::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case Community::statement_get:
					Community::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['NAME'].', '.$COLUMN['DESCRIPTION'].', '.$COLUMN['RULES']
						.', '.$COLUMN['FREQUENCY']
						.', '.$COLUMN['ENTER_LENGTH']
						.', '.$COLUMN['VOTE_LENGTH']
						.', '.$COLUMN['TIME_SHIFT']
						.', '.$COLUMN['UID'].', '.$COLUMN['PID']
						.', '.$COLUMN['STATUS'].', '.$COLUMN['LID']
						.', '.$COLUMN['XID'].', '.$COLUMN['MAXIMUM_THEME_COUNT']
						.', '.$COLUMN['MAXIMUM_THEME_COUNT_PER_MEMBER']
						.', '.$COLUMN['THEME_MINIMUM_SCORE']
						.', '.$COLUMN['THEME_RESTRICT_USERS']
						.', '.$COLUMN['THEME_COST']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.', '.$COLUMN['DELETION_POINTS']
						.', '.$COLUMN['ACTIVE_MEMBER_COUNT']
						.', UNIX_TIMESTAMP('.$COLUMN['INACTIVE_SINCE'].') AS '.$COLUMN['INACTIVE_SINCE']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY']
						.' WHERE '.$COLUMN['XID'].' = ?'
								, array('integer'));
					break;
				case Community::statement_create:
					Community::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['COMMUNITY']
						.'( '.$COLUMN['NAME'].', '.$COLUMN['DESCRIPTION'].', '.$COLUMN['RULES']
						.', '.$COLUMN['FREQUENCY']
						.', '.$COLUMN['ENTER_LENGTH']
						.', '.$COLUMN['VOTE_LENGTH']
						.', '.$COLUMN['TIME_SHIFT']
						.', '.$COLUMN['MAXIMUM_THEME_COUNT']
						.', '.$COLUMN['MAXIMUM_THEME_COUNT_PER_MEMBER']
						.', '.$COLUMN['THEME_MINIMUM_SCORE']
						.', '.$COLUMN['THEME_RESTRICT_USERS']
						.', '.$COLUMN['THEME_COST']
						.', '.$COLUMN['UID']
						.', '.$COLUMN['LID']
						.', '.$COLUMN['PID']
						.', '.$COLUMN['STATUS']
						.', '.$COLUMN['DELETION_POINTS']
						.') VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array('text', 'text', 'text', 'float', 'float', 'float', 'integer', 'integer', 'integer', 'integer', 'boolean', 'integer', 'text', 'integer', 'integer', 'integer', 'integer'));
					break;	
				case Community::statement_setName:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['NAME'], 'text');
					break;
				case Community::statement_setRules:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['RULES'], 'text');
					break;
				case Community::statement_setPid:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['PID'], 'integer');
					break;
				case Community::statement_setStatus:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['STATUS'], 'integer');
					break;
				case Community::statement_delete:
					Community::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY']
						.' WHERE '.$COLUMN['XID'].' = ?'
						, array('integer'));
					break;	
				case Community::statement_setUid:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['UID'], 'text');
					break;
				case Community::statement_setLid:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['LID'], 'integer');
					break;
				case Community::statement_setDescription:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['DESCRIPTION'], 'text');
					break;
				case Community::statement_setFrequency:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['FREQUENCY'], 'float');
					break;
				case Community::statement_setTimeShift:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['TIME_SHIFT'], 'integer');
					break;
				case Community::statement_setMaximumThemeCount:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['MAXIMUM_THEME_COUNT'], 'integer');
					break;
				case Community::statement_setMaximumThemeCountPerMember:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['MAXIMUM_THEME_COUNT_PER_MEMBER'], 'integer');
					break;
				case Community::statement_setThemeMinimumScore:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['THEME_MINIMUM_SCORE'], 'integer');
					break;
				case Community::statement_setEnterLength:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['ENTER_LENGTH'], 'float');
					break;
				case Community::statement_setVoteLength:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['VOTE_LENGTH'], 'float');
					break;
				case Community::statement_setThemeRestrictUsers:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['THEME_RESTRICT_USERS'], 'boolean');
					break;
				case Community::statement_setThemeCost:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['THEME_COST'], 'integer');
					break;
				case Community::statement_setActiveMemberCount:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['ACTIVE_MEMBER_COUNT'], 'integer');
					break;
				case Community::statement_setInactiveSince:
					Community::$statement[$statement] = DB::prepareSetter($TABLE['COMMUNITY'], array($COLUMN['XID'] => 'integer'), $COLUMN['INACTIVE_SINCE'], 'timestamp');
					break;
			}
		}
	}
}

?>