<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertinstancelist.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitylist.php');
require_once(dirname(__FILE__).'/../entities/communitymembership.php');
require_once(dirname(__FILE__).'/../entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvote.php');
require_once(dirname(__FILE__).'/../entities/entryvoteblocked.php');
require_once(dirname(__FILE__).'/../entities/entryvoteblockedlist.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/favorite.php');
require_once(dirname(__FILE__).'/../entities/favoritelist.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../entities/pointsvalue.php');
require_once(dirname(__FILE__).'/../entities/privatemessage.php');
require_once(dirname(__FILE__).'/../entities/privatemessagelist.php');
require_once(dirname(__FILE__).'/../entities/teammembership.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../entities/themevote.php');
require_once(dirname(__FILE__).'/../entities/themevotelist.php');
require_once(dirname(__FILE__).'/../entities/userblock.php');
require_once(dirname(__FILE__).'/../entities/userblocklist.php');
require_once(dirname(__FILE__).'/../entities/userlevel.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../entities/usernameindex.php');
require_once(dirname(__FILE__).'/../entities/usernameindexlist.php');
require_once(dirname(__FILE__).'/../entities/userpaging.php');
require_once(dirname(__FILE__).'/../entities/userpaginglist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../libraries/XMPPHP/XMPP.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/password.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

class UserException extends Exception {}

class User implements Persistent {
	private $uid;
	private $lid;
	private $pid = null;
	private $status;
	private $name = '';
	private $email = '';
	private $password = '';
	private $creation_time;
	private $activation_code = '';
	private $session_id = '';
	private $last_ip = '';
	private $last_user_agent = '';
	private $last_ip_last_time = 0;
	private $last_host_cookie = '';
	private $last_host_cookie_last_time = 0;
	private $web_history_check_last_time = 0;
	private $points = 0;
	private $community_filter_icons = false;
	private $impersonated_uid = null;
	private $display_rank = true;
	private $hide_ads = false;
	private $alert_email = false;
	private $description = null;
	private $display_general_discussion = false;
	private $comments_received = 0;
	private $premium_time = 0;
	private $affiliate_uid = null;
	private $allow_sales = false;
	private $markup = 0;
	private $balance = 0;
	private $vote_block_timestamp = 0;
	private $custom_url = '';
	private $savedUniqueName = array();
	private $translate = false;
	private $lazy = false;
	private $bosh_password = null;
	
	// Cached only, used to check bad voting behaviour
	private $vote_speed = array();
	private $vote_history = array();
	private $visit_history = array();
	private $submenu_history = array();
	
	private static $anonymous_session_id;
	private static $freshly_defined_id;
	
	private static $statement = array();
	
	const statement_create = 2;
	const statement_get = 3;
	const statement_delete = 4;
	const statement_setLid = 5;
	const statement_setStatus = 6;
	const statement_setName = 8;
	const statement_getByEmail = 9;
	const statement_setEmail = 10;
	const statement_setPassword = 11;
	const statement_setActivationCode = 12;
	const statement_getByActivationCode = 13;
	const statement_setSessionId = 14;
	const statement_getBySessionId = 15;
	const statement_setPid = 16;
	const statement_createNameHistory = 17;
	const statement_createIPHistory = 18;
	const statement_createHostCookieHistory = 19;
	const statement_createWebHistory = 22;
	const statement_getWebHistoryURLs = 23;
	const statement_getIPHistory = 25;
	const statement_setPoints = 26;
	const statement_setCommunityFilterIcons = 29;
	const statement_deleteIPHistory = 30;
	const statement_deleteNameHistory = 31;
	const statement_deleteWebHistory = 32;
	const statement_deleteHostCookieHistory = 33;
	const statement_addRememberSessionId = 34;
	const statement_getByRememberSessionId = 35;
	const statement_deleteRememberSessionId = 36;
	const statement_setDisplayRank = 37;
	const statement_setDescription = 38;
	const statement_setCreationTime = 40;
	const statement_setDisplayGeneralDiscussion = 43;
	const statement_setCommentsReceived = 44;
	const statement_setHideAds = 45;
	const statement_setPremiumTime = 46;
	const statement_setAffiliateUid = 47;
	const statement_setAlertEmail = 48;
	const statement_setAllowSales = 49;
	const statement_setMarkup = 50;
	const statement_setBalance = 51;
	const statement_setCustomURL = 52;
	const statement_setTranslate = 53;
	const statement_setVoteBlockTimestamp = 54;
	const statement_setLazy = 55;
	const statement_setBOSHPassword = 56;
        
    const cache_prefix = 'User-';
    const cache_prefix_email = 'EmailUser-';
    const cache_prefix_activation_code = 'ActivationCodeUser-';
    const cache_prefix_session_id = 'SessionIdUser-';
    const cache_prefix_web_history = 'UserWebHistory-';
    const cache_prefix_ip_history = 'UserIPHistory-';
    const cache_prefix_remember_session_id = 'RememberSessionIdUser-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of user with uid='.$this->uid);
		
		try {
			Cache::replaceorset(User::cache_prefix.$this->uid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of user with uid='.$this->uid);
		}
	}
	
	public static function getByEmail($email) {
		global $COLUMN;
		
		if (!$email || strcmp($email, '') == 0)
			throw new UserException('No user for that email address');
		
		try {
 			$uid = Cache::get(User::cache_prefix_email.$email);
 			$user = User::get($uid);
 			
 			Log::trace(__CLASS__, 'found user with uid='.$uid.' associated with this email address');
 			return $user;
 		} catch (Exception $e) {
 			User::prepareStatement(User::statement_getByEmail);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = User::$statement[User::statement_getByEmail]->execute($email);
			Log::trace('DB', 'Executed User::statement_getByEmail ["'.$email.'"] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new UserException('No user for that email address');
			
			$row = $result->fetchRow();
			$result->free();
			
			try {
				Cache::replaceorset(User::cache_prefix_email.$email, $row[$COLUMN['UID']]);
			} catch (CacheException $e) {
				Log::critical(__CLASS__, 'could not insert/update email cache entry of user with uid='. $row[$COLUMN['UID']]);
			}
			
			return User::get($row[$COLUMN['UID']]);
 		}
	}
	
	public static function getByActivationCode($activation_code) {
		global $COLUMN;
		
		if (!$activation_code || strcmp($activation_code, '') == 0)
			throw new UserException('No user for that activation_code');
		
		try {
 			$uid = Cache::get(User::cache_prefix_activation_code.$activation_code);
 			$user = User::get($uid);
 			
 			Log::trace(__CLASS__, 'found user with uid='.$uid.' associated with this activation code');
 			return $user;
 		} catch (Exception $e) {
 			User::prepareStatement(User::statement_getByActivationCode);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = User::$statement[User::statement_getByActivationCode]->execute($activation_code);
			Log::trace('DB', 'Executed User::statement_getByActivationCode ["'.$activation_code.'"] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new UserException('No user for that activation code');
			
			$row = $result->fetchRow();
			$result->free();
			
			try {
				Cache::replaceorset(User::cache_prefix_activation_code.$activation_code, $row[$COLUMN['UID']]);
			} catch (CacheException $e) {
				Log::critical(__CLASS__, 'could not insert/update activation code cache entry of user with uid='. $row[$COLUMN['UID']]);
			}
			
			return User::get($row[$COLUMN['UID']]);
 		}
	}
	
	public static function getBySessionId($session_id) {
		global $COLUMN;
		
		if (!$session_id || strcmp($session_id, '') == 0)
			throw new UserException('No user for that session id');
		
		try {
 			$uid = Cache::get(User::cache_prefix_session_id.$session_id);
 			$user = User::get($uid);
 			
 			Log::trace(__CLASS__, 'found user with uid='.$uid.' associated with this session id');
 			return $user;
 		} catch (Exception $e) {
 			User::prepareStatement(User::statement_getBySessionId);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = User::$statement[User::statement_getBySessionId]->execute($session_id);
			Log::trace('DB', 'Executed User::statement_getBySessionId ["'.$session_id.'"] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new UserException('No user for that session id');
			
			$row = $result->fetchRow();
			$result->free();
			
			try {
				Cache::replaceorset(User::cache_prefix_session_id.$session_id, $row[$COLUMN['UID']]);
			} catch (CacheException $e) {
				Log::critical(__CLASS__, 'could not insert/update session id cache entry of user with uid='. $row[$COLUMN['UID']]);
			}
			
			return User::get($row[$COLUMN['UID']]);
 		}
	}
	
	public static function getByRememberSessionId($session_id) {
		global $COLUMN;
		
		if (!$session_id || strcmp($session_id, '') == 0)
			throw new UserException('No user for that session id');
		
		try {
 			$uid = Cache::get(User::cache_prefix_remember_session_id.$session_id);
 			$user = User::get($uid);
 			
 			Log::trace(__CLASS__, 'found user with uid='.$uid.' associated with this session id');
 			return $user;
 		} catch (Exception $e) {
 			User::prepareStatement(User::statement_getByRememberSessionId);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = User::$statement[User::statement_getByRememberSessionId]->execute($session_id);
			Log::trace('DB', 'Executed User::statement_getByRememberSessionId ["'.$session_id.'"] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new UserException('No user for that remember session id');
			
			$row = $result->fetchRow();
			$result->free();
			
			try {
				Cache::replaceorset(User::cache_prefix_remember_session_id.$session_id, $row[$COLUMN['UID']]);
			} catch (CacheException $e) {
				Log::critical(__CLASS__, 'could not insert/update remember session id cache entry of user with uid='. $row[$COLUMN['UID']]);
			}
			
			return User::get($row[$COLUMN['UID']]);
 		}
	}
	
	public static function getSessionUser($allow_spoofing=true) {
		global $LANGUAGE;
		global $LANGUAGE_HIDDEN;
		global $USER_STATUS;
		global $USER_LEVEL;
		
		Log::trace(__CLASS__, 'retrieving the user associated with the session');
	
 		try {
 			$user = User::getBySessionId(User::getAnonymousSessionId());
 			
 			Log::trace(__CLASS__, 'found user with uid='.$user->getUid().' associated with the session');
 		} catch (UserException $e) {
 			try {
 				$user = User::getByRememberSessionId(User::getAnonymousSessionId());
 				$user->setSessionUser();
 				
 				Log::trace(__CLASS__, 'found user with uid='.$user->getUid().' associated with the remembered session');
 			} catch (UserException $e) {
 				$language_name = User::getBrowserLanguageName();
 				if (isset($LANGUAGE[$language_name]) && !in_array($LANGUAGE[$language_name], $LANGUAGE_HIDDEN)) {
 					$lid = $LANGUAGE[$language_name];
 				} else $lid = $LANGUAGE['EN'];
				
				$user = new User($lid, $USER_STATUS['UNREGISTERED']);
				$user->setSessionId(User::getAnonymousSessionId());
 			}
 		}
 		
 		$levels = UserLevelList::getByUid($user->getUid()); 
 		
 		if ($allow_spoofing && in_array($USER_LEVEL['ADMINISTRATOR'], $levels) && $user->getImpersonatedUid() !== null)
 			$user = User::get($user->getImpersonatedUid());
 		
 		return $user;
	}
	
	public function setSessionUser() {
		$this->setSessionId(User::getAnonymousSessionId());
	}
	
	public static function getAnonymousSessionId() {
		global $_COOKIE;
		global $SESSION_COOKIE_NAME;
		global $COOKIE_DOMAIN;
		
		if (isset($_COOKIE[$SESSION_COOKIE_NAME])) {
			return $_COOKIE[$SESSION_COOKIE_NAME];
		} else if (User::$freshly_defined_id === null) {
			do {
				$value = sha1(microtime());
				try {
					$result = User::getBySessionId($value);
					$result = User::getByRememberSessionId($value);
				} catch (UserException $e) {
					$result = false;
				}
			} while ($result !== false);
			
			setcookie($SESSION_COOKIE_NAME, $value, 0, '/', $COOKIE_DOMAIN, false, true);
			
			User::$freshly_defined_id = $value;
			
			return $value;
		} else return User::$freshly_defined_id;
	}
	
	public function stayLoggedIn() {
		global $SESSION_COOKIE_NAME;
		global $COOKIE_DOMAIN;
		
		$sessionid = User::getAnonymousSessionId();
		
		setcookie($SESSION_COOKIE_NAME, $sessionid, time()+2592000, '/', $COOKIE_DOMAIN, false, true);
		
		try {
			User::getByRememberSessionId($sessionid);
		} catch (UserException $e) {	
			User::prepareStatement(User::statement_addRememberSessionId);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_addRememberSessionId]->execute(array($this->uid, $sessionid));
			Log::trace('DB', 'Executed User::statement_addRememberSessionId ['.$this->uid.', '.$sessionid.'] ('.(microtime(true) - $start_timestamp).')');
		}
	}
	
	public static function getBrowserLanguageName() {
		$language_preferences = null;
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			$language_preferences = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		if ($language_preferences === null) $selected_language_preference = 'EN';
		else {
			$language_prioritized_preferences = array();
			foreach ($language_preferences as $language_preference) {
				$priority = explode(';', $language_preference);
				if (!isset($priority[1]))
					$priority[1] = '1.0';
				else {
					preg_match("/[0-9]*\.?[0-9]+$/", $priority[1], $matches);
					$priority[1] = $matches[0];
				}
					
				$language_prioritized_preferences[$priority[0]] = $priority[1];
			}
			asort($language_prioritized_preferences);
			
			$selected_language_preference = array_pop(array_keys($language_prioritized_preferences));
			$selected_language_preference = explode('-', $selected_language_preference);
			if (!$selected_language_preference) $selected_language_preference = 'EN';
			else $selected_language_preference = strtoupper($selected_language_preference[0]);
		}
		
		return $selected_language_preference;
	}
	
	public function checkPassword($password_check) {
		return (strcmp($this->password, $password_check) == 0);
	}
	
	public function __construct2($lid, $status) {
		global $USER_STATUS;
		global $POINTS_VALUE_ID;
		global $COMMUNITY_MEMBERSHIP_STATUS;
		
		$uid = uniqid();
		
		User::prepareStatement(User::statement_create);
		
		$pointsvalue = PointsValue::get($POINTS_VALUE_ID['ENTRY_POSTING']);
		$default_points_amount = -$pointsvalue->getValue() * 4;
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		User::$statement[User::statement_create]->execute(array($uid, $lid, $status, $default_points_amount, true));
		Log::trace('DB', 'Executed User::statement_create ['.$uid.', '.$lid.', '.$status.', '.$default_points_amount.', 1] ('.(microtime(true) - $start_timestamp).')');

		$this->setUid($uid);
		$this->setLid($lid, false);
		$this->setStatus($status, false);
		$this->setCreationTime(time(), false);
		$this->setPoints($default_points_amount, false);
		$this->setAlertEmail(true, false);
		$this->saveCache();
		
		UserList::deleteByStatus($status);
		UserList::deleteByLid($lid);
		UserList::deleteByLessThan5CommentsReceived();
		
		try {
			$membership = new CommunityMembership(267, $uid, $COMMUNITY_MEMBERSHIP_STATUS['UNREGISTERED']);
		} catch (CommunityMembershipException $e) {}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public static function get($uid, $cache = true) {
		if ($uid === null) throw new UserException('No user for that uid: '.$uid);
		
		try {
			$user = Cache::get(User::cache_prefix.$uid);
		} catch (CacheException $e) {
			User::prepareStatement(User::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = User::$statement[User::statement_get]->execute($uid);
			Log::trace('DB', 'Executed User::statement_get ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new UserException('No user for that uid: '.$uid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$user = new User();
			$user->populateFields($row);
			if ($cache) $user->saveCache();
		}
		return $user;
	}
	
	public static function getArray($uidlist, $cache = true) {
		$result = array();
		$querylist = array();
		
		foreach ($uidlist as $uid) $querylist []= User::cache_prefix.$uid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($uidlist as $uid) try {
			if (isset($cacheresult[User::cache_prefix.$uid])) $result[$uid] = $cacheresult[User::cache_prefix.$uid];
			else $result[$uid] = User::get($uid, $cache);
		} catch (UserException $e) {}
		
		return $result;
	}
	
	public function populateFields($row) {
		global $COLUMN;
	
		$this->setUid($row[$COLUMN['UID']]);
		$this->setPid($row[$COLUMN['PID']], false);
		$this->setLid($row[$COLUMN['LID']], false);
		$this->setStatus($row[$COLUMN['STATUS']], false);
		$this->setName($row[$COLUMN['NAME']], false);
		$this->setEmail($row[$COLUMN['EMAIL']], false);
		$this->setPassword($row[$COLUMN['PASSWORD']], false);
		$this->setCreationTime($row[$COLUMN['CREATION_TIME']], false);
		$this->setActivationCode($row[$COLUMN['ACTIVATION_CODE']], false);
		$this->setSessionId($row[$COLUMN['SESSION_ID']], false);
		$this->setCommunityFilterIcons($row[$COLUMN['COMMUNITY_FILTER_ICONS']], false);
		$this->setPoints($row[$COLUMN['POINTS']], false);		
		$this->setDisplayRank($row[$COLUMN['DISPLAY_RANK']], false);
		$this->setDescription($row[$COLUMN['DESCRIPTION']], false);
		$this->setDisplayGeneralDiscussion($row[$COLUMN['DISPLAY_GENERAL_DISCUSSION']], false);
		$this->setCommentsReceived($row[$COLUMN['COMMENTS_RECEIVED']], false);
		$this->setHideAds($row[$COLUMN['HIDE_ADS']], false);
		$this->setPremiumTime($row[$COLUMN['PREMIUM_TIME']], false);
		$this->setAffiliateUid($row[$COLUMN['AFFILIATE_UID']], false);
		$this->setAlertEmail($row[$COLUMN['ALERT_EMAIL']], false);
		$this->setAllowSales($row[$COLUMN['ALLOW_SALES']], false);
		$this->setMarkup($row[$COLUMN['MARKUP']], false);
		$this->setBalance($row[$COLUMN['BALANCE']], false);
		$this->setCustomURL($row[$COLUMN['CUSTOM_URL']], false);
		$this->setTranslate($row[$COLUMN['TRANSLATE']], false);
		$this->setVoteBlockTimestamp($row[$COLUMN['VOTE_BLOCK_TIMESTAMP']], false);
		$this->setLazy($row[$COLUMN['LAZY']], false);
		$this->setBOSHPassword($row[$COLUMN['BOSH_PASSWORD']], false);
	}
	
	public function delete() {
		global $DISCUSSION_THREAD_STATUS;
		global $DISCUSSION_POST_STATUS;
		global $COMMUNITY_STATUS;
		global $THEME_STATUS;
		global $THEME_VOTE_STATUS;
		global $ENTRY_STATUS;
		global $ENTRY_VOTE_STATUS;
		global $COMPETITION_STATUS;
		
		User::prepareStatement(User::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		User::$statement[User::statement_delete]->execute($this->uid);
		Log::trace('DB', 'Executed User::statement_delete ['.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try { Cache::delete(User::cache_prefix.$this->uid); } catch (CacheException $e) {}
		try { Cache::delete(User::cache_prefix_email.$this->email); } catch (CacheException $e) {}
		try { Cache::delete(User::cache_prefix_activation_code.$this->activation_code); } catch (CacheException $e) {}
		try { Cache::delete(User::cache_prefix_session_id.$this->session_id); } catch (CacheException $e) {}
		
		// Delete all data associated to user
		$this->deleteIPHistory();
		$this->deleteNameHistory();
		$this->deleteHostCookieHistory();
		$this->deleteWebHistory();
		
		$threads = DiscussionThreadList::getByUidAndStatus($this->uid, $DISCUSSION_THREAD_STATUS['ANONYMOUS']);
		$threads += DiscussionThreadList::getByUidAndStatus($this->uid, $DISCUSSION_THREAD_STATUS['DELETED']);
		$threads += DiscussionThreadList::getByUidAndStatus($this->uid, $DISCUSSION_THREAD_STATUS['ACTIVE']);
		
		foreach ($threads as $nid => $timestamp) {
			try {
				$thread = DiscussionThread::get($nid);
				$thread->delete();
			} catch (DiscussionThreadException $e) {}
		}
		
		$posts = DiscussionPostList::getByUidAndStatus($this->uid, $DISCUSSION_POST_STATUS['ANONYMOUS']);
		$posts += DiscussionPostList::getByUidAndStatus($this->uid, $DISCUSSION_POST_STATUS['DELETED']);
		$posts += DiscussionPostList::getByUidAndStatus($this->uid, $DISCUSSION_POST_STATUS['POSTED']);
		
		foreach ($posts as $oid => $timestamp) {
			try {
				$post = DiscussionPost::get($oid);
				$post->delete();
			} catch (DiscussionPostException $e) {}
		}
		
		$communities = CommunityList::getByUidAndStatus($this->uid, $COMMUNITY_STATUS['ANONYMOUS']);
		$communities = array_merge($communities, CommunityList::getByUidAndStatus($this->uid, $COMMUNITY_STATUS['ACTIVE']));
		$communities = array_merge($communities, CommunityList::getByUidAndStatus($this->uid, $COMMUNITY_STATUS['INACTIVE']));
		$communities = array_merge($communities, CommunityList::getByUidAndStatus($this->uid, $COMMUNITY_STATUS['DELETED']));
		
		foreach ($communities as $xid) {
			try {
				$community = Community::get($xid);
				$community->delete();
			} catch (CommunityException $e) {}
		}
		
		$memberships = CommunityMembershipList::getByUid($this->uid);
		foreach ($memberships as $xid => $join_time) {
			try {
				$membership = CommunityMembership::get($xid, $this->uid);
				$membership->delete();
			} catch (CommunityMembershipException $e) {}
		}
		
		$themes = ThemeList::getByUidAndStatus($this->uid, $THEME_STATUS['ANONYMOUS'], false);
		$themes += ThemeList::getByUidAndStatus($this->uid, $THEME_STATUS['SUGGESTED'], false);
		$themes += ThemeList::getByUidAndStatus($this->uid, $THEME_STATUS['DELETED'], false);
		
		foreach ($themes as $tid => $xid) {
			try {
				$theme = Theme::get($tid);
				$theme->delete();
			} catch (ThemeException $e) {}
		}
			
		$entrylist = EntryList::getByUidAndStatus($this->uid, $ENTRY_STATUS['ANONYMOUS'], false);
		$entrylist += EntryList::getByUidAndStatus($this->uid, $ENTRY_STATUS['POSTED'], false);
		$entrylist += EntryList::getByUidAndStatus($this->uid, $ENTRY_STATUS['DELETED'], false);
		$entrylist += EntryList::getByUidAndStatus($this->uid, $ENTRY_STATUS['BANNED'], false);
		
		foreach ($entrylist as $cid => $eid) {
			try {
				$entry = Entry::get($eid);
				$cid = $entry->getCid();
				$competition = Competition::get($cid);
				if ($competition->getStatus() == $COMPETITION_STATUS['CLOSED']) {
					$pid = $entry->getPid();
					try {
						$picture = Picture::get($pid);
						$picture->delete();
					} catch (PictureException $e) {}
					
					$entry->setPid(null);
					if ($entry->getStatus() != $ENTRY_STATUS['BANNED'] && $entry->getStatus() != $ENTRY_STATUS['DISQUALIFIED'])
					$entry->setStatus($ENTRY_STATUS['DELETED']);
				} else $entry->delete();
			} catch (EntryException $e) {}
		}
		
		try {
			$team_membership = TeamMembership::get($this->uid);
			$team_membership->delete();
		} catch (TeamMembershipException $e) {}
		
		$userpaginglist = UserPagingList::getByUid($this->uid);
		foreach ($userpaginglist as $pgid => $value) {
			$userpaging = UserPaging::get($this->uid, $pgid);
			$userpaging->delete();
		}
		
		$favoritelist = FavoriteList::getByUid($this->uid);
		foreach ($favoritelist as $eid => $creation_time) try {
			$favorite = Favorite::get($eid, $this->uid);
			$favorite->delete();
		} catch (FavoriteException $e) {}
		
		$alertinstancelist = AlertInstanceList::getByUid($this->uid);
		foreach ($alertinstancelist as $aid) try {
			$alertinstance = AlertInstance::get($aid, $this->uid);
			$alertinstance->delete();
		} catch (AlertInstanceException $e) {}
		
		$privatemessagelist = PrivateMessageList::getBySourceUid($this->uid);
		$privatemessagecache = PrivateMessage::getArray(array_keys($privatemessagelist));
		foreach ($privatemessagecache as $pmid => $privatemessage) $privatemessage->delete();
		
		$privatemessagelist = PrivateMessageList::getByDestinationUid($this->uid);
		$privatemessagecache = PrivateMessage::getArray(array_keys($privatemessagelist));
		foreach ($privatemessagecache as $pmid => $privatemessage) $privatemessage->delete();
		
		$userblocklist = UserBlockList::getByUid($this->uid);
		foreach ($userblocklist as $uid) try {
			$userblock = UserBlock::get($this->uid, $uid);
			$userblock->delete();
		} catch (UserBlockException $e) {}
		
		$userblocklist = UserBlockList::getByBlockedUid($this->uid);
		foreach ($userblocklist as $uid) try {
			$userblock = UserBlock::get($uid, $this->uid);
			$userblock->delete();
		} catch (UserBlockException $e) {}
		
		$entryvoteblockedlist = EntryVoteBlockedList::getByVoterUid($this->uid, false);
		foreach ($entryvoteblockedlist as $author_uid) try {
			$entryvoteblocked = EntryVoteBlocked::get($this->uid, $author_uid);
			$entryvoteblocked->delete();
		} catch (EntryVoteBlockedException $e) {}
		
		$entryvoteblockedlist = EntryVoteBlockedList::getByAuthorUid($this->uid, false);
		foreach ($entryvoteblockedlist as $voter_uid) try {
			$entryvoteblocked = EntryVoteBlocked::get($voter_uid, $this->uid);
			$entryvoteblocked->delete();
		} catch (EntryVoteBlockedException $e) {}
		
		// TODO: set discussion posts and comments to a new state in order to hide actual text
		
		// Once completely deleted, remove from associated lists
		
		UserList::deleteByStatus($this->status);
		UserList::deleteByLid($this->lid);
		UserList::deleteRecentlyRegistered(14);
		UserList::deleteByLessThan5CommentsReceived();
		UserList::deleteByCustomURL($this->custom_url);
		
		$oldchunklist = UserNameIndexList::getByUid($this->uid);
		foreach ($oldchunklist as $chunk => $count) try {
			$usernameindex = UserNameIndex::get($chunk, $this->uid);
			$usernameindex->delete();
		} catch (UserNameIndexException $e) {}
	}
	
	public function getUid() { return $this->uid; }
	
	public function setUid($new_uid) { $this->uid = $new_uid; }
	
	public function getCreationTime() { return $this->creation_time; }
	
	public function setCreationTime($new_creation_time, $persist=true) {	
		$this->creation_time = $new_creation_time;
		
		if ($persist) {
			User::prepareStatement(User::statement_setCreationTime);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setCreationTime]->execute(array(MDB2_Date::unix2Mdbstamp($this->creation_time), $this->uid));
			Log::trace('DB', 'Executed User::statement_setCreationTime ['.$this->creation_time.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getPremiumTime() { return $this->premium_time; }
	
	public function setPremiumTime($new_premium_time, $persist=true) {	
		$this->premium_time = $new_premium_time;
		
		if ($persist) {
			User::prepareStatement(User::statement_setPremiumTime);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setPremiumTime]->execute(array(MDB2_Date::unix2Mdbstamp($this->premium_time), $this->uid));
			Log::trace('DB', 'Executed User::statement_setPremiumTime ['.$this->premium_time.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getVoteBlockTimestamp() { return $this->vote_block_timestamp; }
	
	public function setVoteBlockTimestamp($new_vote_block_timestamp, $persist=true) {	
		$this->vote_block_timestamp = $new_vote_block_timestamp;
		
		if ($persist) {
			User::prepareStatement(User::statement_setVoteBlockTimestamp);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setVoteBlockTimestamp]->execute(array(MDB2_Date::unix2Mdbstamp($this->vote_block_timestamp), $this->uid));
			Log::trace('DB', 'Executed User::statement_setVoteBlockTimestamp ['.$this->vote_block_timestamp.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getAffiliateUid() { return $this->affiliate_uid; }
	
	public function setAffiliateUid($new_affiliate_uid, $persist=true) {	
		$this->affiliate_uid = $new_affiliate_uid;
		
		if ($persist) {
			User::prepareStatement(User::statement_setAffiliateUid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setAffiliateUid]->execute(array($this->affiliate_uid, $this->uid));
			Log::trace('DB', 'Executed User::statement_setAffiliateUid ['.$this->affiliate_uid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getLid() { return $this->lid; }
	
	public function setLid($new_lid, $persist=true) {	
		$old_lid = $this->lid;
		$this->lid = $new_lid;
		
		if ($persist) {
			User::prepareStatement(User::statement_setLid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setLid]->execute(array($this->lid, $this->uid));
			Log::trace('DB', 'Executed User::statement_setLid ['.$this->lid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			UserList::deleteByLid($this->lid);
			UserList::deleteByLid($old_lid);
		}
	}
	
	public function getPid() { return $this->pid; }
	
	public function setPid($new_pid, $persist=true) {	
		$this->pid = $new_pid;
		
		if ($persist) {
			User::prepareStatement(User::statement_setPid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setPid]->execute(array($this->pid, $this->uid));
			Log::trace('DB', 'Executed User::statement_setPid ['.$this->pid.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getCommunityFilterIcons() { return $this->community_filter_icons; }
	
	public function setCommunityFilterIcons($new_community_filter_icons, $persist=true) {	
		$this->community_filter_icons = $new_community_filter_icons;
		
		if ($persist) {
			User::prepareStatement(User::statement_setCommunityFilterIcons);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setCommunityFilterIcons]->execute(array($this->community_filter_icons, $this->uid));
			Log::trace('DB', 'Executed User::statement_setCommunityFilterIcons ['.$this->community_filter_icons.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getDisplayRank() { return $this->display_rank; }
	
	public function setDisplayRank($new_display_rank, $persist=true) {	
		$this->display_rank = $new_display_rank;
		
		if ($persist) {
			User::prepareStatement(User::statement_setDisplayRank);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setDisplayRank]->execute(array($this->display_rank, $this->uid));
			Log::trace('DB', 'Executed User::statement_setDisplayRank ['.$this->display_rank.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getHideAds() { return $this->hide_ads; }
	
	public function setHideAds($new_hide_ads, $persist=true) {	
		$this->hide_ads = $new_hide_ads;
		
		if ($persist) {
			User::prepareStatement(User::statement_setHideAds);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setHideAds]->execute(array($this->hide_ads, $this->uid));
			Log::trace('DB', 'Executed User::statement_setHideAds ['.$this->hide_ads.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getTranslate() { return $this->translate; }
	
	public function setTranslate($new_translate, $persist=true) {	
		$this->translate = $new_translate;
		
		if ($persist) {
			User::prepareStatement(User::statement_setTranslate);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setTranslate]->execute(array($this->translate, $this->uid));
			Log::trace('DB', 'Executed User::statement_setTranslate ['.$this->translate.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getLazy() { return $this->lazy; }
	
	public function setLazy($new_lazy, $persist=true) {	
		$this->lazy = $new_lazy;
		
		if ($persist) {
			User::prepareStatement(User::statement_setLazy);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setLazy]->execute(array($this->lazy, $this->uid));
			Log::trace('DB', 'Executed User::statement_setLazy ['.$this->lazy.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getAlertEmail() { return $this->alert_email; }
	
	public function setAlertEmail($new_alert_email, $persist=true) {	
		$this->alert_email = $new_alert_email;
		
		if ($persist) {
			User::prepareStatement(User::statement_setAlertEmail);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setAlertEmail]->execute(array($this->alert_email, $this->uid));
			Log::trace('DB', 'Executed User::statement_setAlertEmail ['.$this->alert_email.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getAllowSales() { return $this->allow_sales; }
	
	public function setAllowSales($new_allow_sales, $persist=true) {	
		$this->allow_sales = $new_allow_sales;
		
		if ($persist) {
			User::prepareStatement(User::statement_setAllowSales);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setAllowSales]->execute(array($this->allow_sales, $this->uid));
			Log::trace('DB', 'Executed User::statement_setAllowSales ['.$this->allow_sales.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getMarkup() { return $this->markup; }
	
	public function setMarkup($new_markup, $persist=true) {	
		$this->markup = $new_markup;
		
		if ($persist) {
			User::prepareStatement(User::statement_setMarkup);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setMarkup]->execute(array($this->markup, $this->uid));
			Log::trace('DB', 'Executed User::statement_setMarkup ['.$this->markup.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getBalance() { return $this->balance; }
	
	public function setBalance($new_balance, $persist=true) {	
		$this->balance = $new_balance;
		
		if ($persist) {
			User::prepareStatement(User::statement_setBalance);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setBalance]->execute(array($this->balance, $this->uid));
			Log::trace('DB', 'Executed User::statement_setBalance ['.$this->balance.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function decrementBalance($value) {
		Cache::lock('Balance-'.$this->getUid());
		
		$newvalue = $this->getBalance() - abs($value);
		if ($newvalue < 0) $newvalue = 0;
		$this->setBalance($newvalue);
		
		Cache::unlock('Balance-'.$this->getUid());
	}
	
	public function incrementBalance($value) {
		Cache::lock('Balance-'.$this->getUid());
		
		$newvalue = $this->getBalance() + abs($value);
		$this->setBalance($newvalue);
		
		Cache::unlock('Balance-'.$this->getUid());
	}
	
	public function getDisplayGeneralDiscussion() { return $this->display_general_discussion; }
	
	public function setDisplayGeneralDiscussion($new_display_general_discussion, $persist=true) {	
		$this->display_general_discussion = $new_display_general_discussion;
		
		if ($persist) {
			User::prepareStatement(User::statement_setDisplayGeneralDiscussion);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setDisplayGeneralDiscussion]->execute(array($this->display_general_discussion, $this->uid));
			Log::trace('DB', 'Executed User::statement_setDisplayGeneralDiscussion ['.$this->display_general_discussion.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getPoints() { return $this->points; }
	
	public function setPoints($new_points, $persist=true) {	
		$this->points = $new_points;
		
		if ($persist) {
			User::prepareStatement(User::statement_setPoints);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setPoints]->execute(array($this->points, $this->uid));
			Log::trace('DB', 'Executed User::statement_setPoints ['.$this->points.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getCommentsReceived() { return $this->comments_received; }
	
	public function setCommentsReceived($new_comments_received, $persist=true) {	
		$this->comments_received = $new_comments_received;
		
		if ($persist) {
			User::prepareStatement(User::statement_setCommentsReceived);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setCommentsReceived]->execute(array($this->comments_received, $this->uid));
			Log::trace('DB', 'Executed User::statement_setCommentsReceived ['.$this->comments_received.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}

	public function getStatus() { return $this->status; }
	
	public function setStatus($new_status, $persist=true) {
		global $USER_STATUS;
		
		$old_status = $this->status;
		$this->status = $new_status;
		
		if ($persist) {
			User::prepareStatement(User::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setStatus]->execute(array($this->status, $this->uid));
			Log::trace('DB', 'Executed User::statement_setStatus ['.$this->status.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			UserList::deleteByStatus($this->status);
			UserList::deleteByStatus($old_status);
			UserList::deleteByLessThan5CommentsReceived();
			
			$membershiplist = CommunityMembershipList::getByUid($this->uid);
			foreach ($membershiplist as $xid => $join_time) try {
				$membership = CommunityMembership::get($xid, $this->uid);
				$membership->setStatus($this->status);
			} catch (CommunityMembershipExcepyion $e) {}
			
			if ($this->status != $USER_STATUS['ACTIVE']) {
				$oldchunklist = UserNameIndexList::getByUid($this->uid);
				foreach ($oldchunklist as $chunk => $count) try {
					$usernameindex = UserNameIndex::get($chunk, $this->uid);
					$usernameindex->delete();
				} catch (UserNameIndexException $e) {}
			} elseif ($this->name !== null) {
				$new_name = mb_strtolower($this->name, 'UTF-8');
				$oldchunklist = UserNameIndexList::getByUid($this->uid);
				$newchunklist = array();
				
				for ($i = 1; $i <= mb_strlen($new_name, 'UTF-8'); $i++) {
					for ($j = 0; $j <= mb_strlen($new_name, 'UTF-8') - $i; $j++) {
						$chunk = mb_substr($new_name, $j, $i, 'UTF-8');
						if (!isset($newchunklist[$chunk])) $newchunklist[$chunk] = 1;
						else $newchunklist[$chunk]++;	
					}
				}
				
				foreach ($newchunklist as $chunk => $count) {
					if (!isset($oldchunklist[$chunk])) {
						$usernameindex = new UserNameIndex($chunk, $this->uid, $count);
					} elseif ($oldchunklist[$chunk] != $count) try {
						$usernameindex = UserNameIndex::get($chunk, $this->uid);
						$usernameindex->setCount($count);
					} catch (UserNameIndexException $e) {}
				}
				
				$chunkstodelete = array_diff_key($oldchunklist, $newchunklist);
				
				foreach ($chunkstodelete as $chunk => $count) try {
					$usernameindex = UserNameIndex::get($chunk, $this->uid);
					$usernameindex->delete();
				} catch (UserNameIndexException $e) {}
			}
		}
	}
	
	public function getName() { return $this->name; }
	
	public function setName($new_name, $persist=true) {	
		$old_name = $this->name;
		$this->name = $new_name;
		
		if ($persist) {
			User::prepareStatement(User::statement_setName);
			User::prepareStatement(User::statement_createNameHistory);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setName]->execute(array($this->name, $this->uid));
			Log::trace('DB', 'Executed User::statement_setName ["'.$this->name.'", '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_createNameHistory]->execute(array($this->uid, $this->name));
			Log::trace('DB', 'Executed User::statement_createNameHistory ['.$this->uid.', "'.$this->name.'"] ('.(microtime(true) - $start_timestamp).')');
			
			
			UserList::deleteByName($old_name);
			UserList::deleteByName($new_name);
			
			$this->savedUniqueName = array();
			$uniquename = $this->getUniqueName();
			
			// Let's update the index for user name search
			
			if ($new_name === null) { // Delete old indexing if the user is going back to the email address display
				$oldchunklist = UserNameIndexList::getByUid($this->uid);
				foreach ($oldchunklist as $chunk => $count) try {
					$usernameindex = UserNameIndex::get($chunk, $this->uid);
					$usernameindex->delete();
				} catch (UserNameIndexException $e) {}
			} else { // Calculate the difference between the old and the new indexing and update/delete what needs to be
				$new_name = mb_strtolower($new_name, 'UTF-8');
				$oldchunklist = UserNameIndexList::getByUid($this->uid);
				$newchunklist = array();
				
				for ($i = 1; $i <= mb_strlen($new_name, 'UTF-8'); $i++) {
					for ($j = 0; $j <= mb_strlen($new_name, 'UTF-8') - $i; $j++) {
						$chunk = mb_substr($new_name, $j, $i, 'UTF-8');
						if (!isset($newchunklist[$chunk])) $newchunklist[$chunk] = 1;
						else $newchunklist[$chunk]++;	
					}
				}
				
				foreach ($newchunklist as $chunk => $count) {
					if (!isset($oldchunklist[$chunk])) {
						$usernameindex = new UserNameIndex($chunk, $this->uid, $count);
					} elseif ($oldchunklist[$chunk] != $count) try {
						$usernameindex = UserNameIndex::get($chunk, $this->uid);
						$usernameindex->setCount($count);
					} catch (UserNameIndexException $e) {}
				}
				
				$chunkstodelete = array_diff_key($oldchunklist, $newchunklist);
				
				foreach ($chunkstodelete as $chunk => $count) try {
					$usernameindex = UserNameIndex::get($chunk, $this->uid);
					$usernameindex->delete();
				} catch (UserNameIndexException $e) {}
			}
		}
	}
	
	public function getDescription() { return $this->description; }
	
	public function setDescription($new_description, $persist=true) {	
		$this->description = $new_description;
		
		if ($persist) {
			User::prepareStatement(User::statement_setDescription);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setDescription]->execute(array($this->description, $this->uid));
			Log::trace('DB', 'Executed User::statement_setDescription ["'.$this->description.'", '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
					
			$this->saveCache();
		}
	}
	
	public function deleteNameHistory() {
		User::prepareStatement(User::statement_deleteNameHistory);
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		User::$statement[User::statement_deleteNameHistory]->execute($this->uid);
		Log::trace('DB', 'Executed User::statement_deleteNameHistory ['.$this->uid.'"] ('.(microtime(true) - $start_timestamp).')');
			
		UserList::deleteByName($this->name);
	}
	
	public function getEmail() { return $this->email; }
	
	public function getSafeEmail() { 
		return String::hideEmail($this->email); 
	}
	
	public function setEmail($new_email, $persist=true) {	
		$old_email = $this->email;
		$this->email = $new_email;
		
		if ($persist) {
			User::prepareStatement(User::statement_setEmail);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setEmail]->execute(array($this->email, $this->uid));
			Log::trace('DB', 'Executed User::statement_setEmail ['.$this->email.', '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			try { Cache::delete(User::cache_prefix_email.$old_email); } catch (CacheException $e) {}
			
			try {
				Cache::replaceorset(User::cache_prefix_email.$this->email, $this->uid);
			} catch (CacheException $e) {
				Log::critical(__CLASS__, 'could not insert/update email cache entry of user with uid='.$this->uid);
			}
		}
	}
	
	public function getActivationCode() { return $this->activation_code; }
	
	public function setActivationCode($new_activation_code, $persist=true) {	
		$old_activation_code = $this->activation_code;
		$this->activation_code = $new_activation_code;
		
		if ($persist) {
			User::prepareStatement(User::statement_setActivationCode);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setActivationCode]->execute(array($this->activation_code, $this->uid));
			Log::trace('DB', 'Executed User::statement_setActivationCode ["'.$this->activation_code.'", '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			try { Cache::delete(User::cache_prefix_activation_code.$old_activation_code); } catch (CacheException $e) {}
			
			try {
				Cache::replaceorset(User::cache_prefix_activation_code.$this->activation_code, $this->uid);
			} catch (CacheException $e) {
				Log::critical(__CLASS__, 'could not insert/update activation code cache entry of user with uid='.$this->uid);
			}
		}
	}
	
	public function setSessionId($new_session_id, $persist=true) {	
		$old_session_id = $this->session_id;
		$this->session_id = $new_session_id;
		
		if ($persist) {
			User::prepareStatement(User::statement_setSessionId);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setSessionId]->execute(array($this->session_id, $this->uid));
			Log::trace('DB', 'Executed User::statement_setSessionId ["'.$this->session_id.'", '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			try { Cache::delete(User::cache_prefix_session_id.$old_session_id); } catch (CacheException $e) {}
			
			try {
				Cache::replaceorset(User::cache_prefix_session_id.$this->session_id, $this->uid);
			} catch (CacheException $e) {
				Log::critical(__CLASS__, 'could not insert/update session id cache entry of user with uid='.$this->uid);
			}
		}
	}
	
	public function setPassword($new_password, $persist=true) {	
		$this->password = $new_password;
		
		if ($persist) {
			$this->password = sha1($new_password);
			User::prepareStatement(User::statement_setPassword);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setPassword]->execute(array($this->password, $this->uid));
			Log::trace('DB', 'Executed User::statement_setPassword ["'.$this->password.'", '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function isPassword($password_to_check) {
		return (strcmp(sha1($password_to_check), $this->password) == 0);
	}
	
	public function updateIP($new_ip, $new_user_agent) {
		global $IP_MAXIMUM_AGE;
		
		if (strcmp($new_ip, $this->last_ip) != 0 
			|| strcmp($new_user_agent, $this->last_user_agent) != 0  
			|| time() - $this->last_ip_last_time > $IP_MAXIMUM_AGE) {
			User::prepareStatement(User::statement_createIPHistory);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_createIPHistory]->execute(array($this->uid, $new_ip, $new_user_agent));
			Log::trace('DB', 'Executed User::statement_createIPHistory ['.$this->uid.' "'.$new_ip.'", "'.$new_user_agent.'"] ('.(microtime(true) - $start_timestamp).')');
			
			$this->last_ip = $new_ip;
			$this->last_user_agent = $new_user_agent;
			$this->last_ip_last_time = time();
			$this->saveCache();
			try { Cache::delete(User::cache_prefix_ip_history.$this->uid); } catch (CacheException $e) {}
			
			UserList::addActive($this->uid, time());
		}		
	}
	
	public function updateHostCookie($new_host_cookie) {
		global $HOST_COOKIE_MAXIMUM_AGE;
		
		if (strcmp($new_host_cookie, $this->last_host_cookie) != 0 
			|| time() - $this->last_ip_last_time > $HOST_COOKIE_MAXIMUM_AGE) {
			User::prepareStatement(User::statement_createHostCookieHistory);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_createHostCookieHistory]->execute(array($this->uid, $new_host_cookie));
			Log::trace('DB', 'Executed User::statement_createHostCoookieHistory ['.$this->uid.', "'.$new_host_cookie.'"] ('.(microtime(true) - $start_timestamp).')');
			
			$this->last_host_cookie = $new_host_cookie;
			$this->last_host_cookie_last_time = time();
			$this->saveCache();
			
			if (strcmp($new_host_cookie, $this->last_host_cookie) != 0)
				UserList::deleteByHostCookie($new_host_cookie);
		}		
	}
	
	public function deleteHostCookieHistory() {
		User::prepareStatement(User::statement_deleteHostCookieHistory);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		User::$statement[User::statement_deleteHostCookieHistory]->execute($this->uid);
		Log::trace('DB', 'Executed User::statement_deleteHostCoookieHistory ['.$this->uid.'"] ('.(microtime(true) - $start_timestamp).')');
		
		UserList::deleteByHostCookie($this->last_host_cookie);
	}
	
	public function insertWebHistoryURL($new_url) {
		User::prepareStatement(User::statement_createWebHistory);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		User::$statement[User::statement_createWebHistory]->execute(array($this->uid, $new_url));
		Log::trace('DB', 'Executed User::statement_createWebHistory ['.$this->uid.', '.$new_url.'] ('.(microtime(true) - $start_timestamp).')');
		
		$this->web_history_check_last_time = time();
		$this->saveCache();	
		try { Cache::delete(User::cache_prefix_web_history.$this->uid); } catch (CacheException $e) {}
	}
	
	public function deleteWebHistory() {
		try {
			Cache::delete(User::cache_prefix_web_history.$this->uid);
		} catch (CacheException $e) {}
		User::prepareStatement(User::statement_deleteWebHistory);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		User::$statement[User::statement_deleteWebHistory]->execute($this->uid);
		Log::trace('DB', 'Executed User::statement_deleteWebHistory ['.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
	}
	
	public function getWebHistoryCheckLastTime() {
		return $this->web_history_check_last_time;
	}
	
	public function updateWebHistoryCheckLastTime() {
		$this->web_history_check_last_time = time();
		$this->saveCache();
	}
	
	public function getLastIP() {
		return $this->last_ip;
	}
	
	public function getLastHostCookie() {
		return $this->last_host_cookie;
	}
	
	public function getIPHistory() {
		global $COLUMN;
		
		try {
			$ip_history = Cache::get(User::cache_prefix_ip_history.$this->uid);
		} catch (CacheException $e) {
			User::prepareStatement(User::statement_getIPHistory);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = User::$statement[User::statement_getIPHistory]->execute($this->uid);
			Log::trace('DB', 'Executed User::statement_getIPHistory ['.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$ip_history = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0)
			while ($row = $result->fetchRow()) $ip_history [$row[$COLUMN['IP']]]= $row[$COLUMN['LAST_TIME']];
			
			try {
				Cache::setorreplace(User::cache_prefix_ip_history.$this->uid, $ip_history);
			} catch (CacheException $e) {}
		}
		
		return $ip_history;
	}
	
	public function deleteIpHistory() {
		try {
			Cache::delete(User::cache_prefix_ip_history.$this->uid);
		} catch (CacheException $e) {}
		
		User::prepareStatement(User::statement_deleteIPHistory);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = User::$statement[User::statement_deleteIPHistory]->execute($this->uid);
		Log::trace('DB', 'Executed User::statement_deleteIPHistory ['.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
	}
	
	public function getWebHistoryURLs() {
		global $COLUMN;
		
		try {
			$web_history_urls = Cache::get(User::cache_prefix_web_history.$this->uid);
		} catch (CacheException $e) {
			User::prepareStatement(User::statement_getWebHistoryURLs);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = User::$statement[User::statement_getWebHistoryURLs]->execute($this->uid);
			Log::trace('DB', 'Executed User::statement_getWebHistoryURLs ['.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$web_history_urls = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0)
			while ($row = $result->fetchRow()) $web_history_urls []= $row[$COLUMN['URL']];
			
			try {
				Cache::setorreplace(User::cache_prefix_web_history.$this->uid, $web_history_urls);
			} catch (CacheException $e) {}
		}
		
		return $web_history_urls;
	}
	
	public function getCommunityList() {
		global $COMMUNITY_STATUS;
		global $USER_STATUS;
		
		$member_of = array_keys(CommunityMembershipList::getByUid($this->uid));
		if ($this->status == $USER_STATUS['UNREGISTERED']) {
			$owner = CommunityList::getByUidAndStatus($this->uid, $COMMUNITY_STATUS['ANONYMOUS']);
		} else {
			$owner = CommunityList::getByUidAndStatus($this->uid, $COMMUNITY_STATUS['ACTIVE']);
			$owner = array_merge($owner, CommunityList::getByUidAndStatus($this->uid, $COMMUNITY_STATUS['INACTIVE']));
		}
		
		$community_list = array_unique(array_merge($member_of, $owner));	
		
		return $community_list;
	}
	
	public function getUniqueName() {
		if (isset($this->savedUniqueName['timestamp']) && $this->savedUniqueName['timestamp'] > time())
			return $this->savedUniqueName['name'];
			
		$result = '';
	
		if (strcmp($this->name, '') == 0) $result = $this->getSafeEmail();
		else {
			// Check if multiple users have the same name and add a (X) where X is a number to the copycats
			$list = UserList::getByName($this->name);
			$key = array_search($this->uid, $list);
			if ($key > 0) $result = $this->name.' ('.($key + 1).')';
			else $result = $this->name;
			
			$this->savedUniqueName['timestamp'] = time() + 86400; // The unique name can be one day stale
			$this->savedUniqueName['name'] = $result;
			$this->saveCache();
		}
		
		return $result;
	}
	
	public function givePoints($points) {
		Cache::lock('UserPoints-'.$this->getUid());
		
		if ($this->getPoints() + $points < 0) {
			Cache::unlock('UserPoints-'.$this->getUid());
			throw new UserException('Insufficient funds');
		}
		else $this->setPoints($this->getPoints() + $points);
		
		Cache::unlock('UserPoints-'.$this->getUid());
	}
	
	public function decrementCommentsReceived() {
		Cache::lock('UserCommentsReceived-'.$this->getUid());
		
		$newvalue = $this->getCommentsReceived() - 1;
		if ($newvalue == 4) UserList::deleteByLessThan5CommentsReceived();
		$this->setCommentsReceived($newvalue);
		
		Cache::unlock('UserCommentsReceived-'.$this->getUid());
	}
	
	public function incrementCommentsReceived() {
		Cache::lock('UserCommentsReceived-'.$this->getUid());
		
		$newvalue = $this->getCommentsReceived() + 1;
		if ($newvalue == 5) UserList::deleteByLessThan5CommentsReceived();
		$this->setCommentsReceived($newvalue);
		
		Cache::unlock('UserCommentsReceived-'.$this->getUid());
	}
	
	public function setLastActivity() {
		global $USER_STATUS;
		
		try {
			Cache::replaceorset('UserLastActivity-'.$this->uid, gmmktime());
		} catch (CacheException $e) {}
		
		if ($this->status == $USER_STATUS['ACTIVE']) UserList::addLive($this->uid, gmmktime());
	}
	
	public function getLastActivity() {
		$last_activity = null;
		try {
			$last_activity = Cache::get('UserLastActivity-'.$this->uid);
		} catch (CacheException $e) {}
		return $last_activity;
	}
	
	public function logout() {
		global $_SERVER;
		global $PAGE;
		global $REDIRECT_BLACKLIST;
		global $SESSION_COOKIE_NAME;
		global $COOKIE_DOMAIN;
		
		$old_session_id = $this->session_id;
		$this->setSessionId('');
		
		setcookie($SESSION_COOKIE_NAME, '', time() - 86400, '/', $COOKIE_DOMAIN, false, true);
		
		try {
			Cache::delete(User::cache_prefix_remember_session_id.$old_session_id);
		} catch (CacheException $e) {}
		
		User::prepareStatement(User::statement_deleteRememberSessionId);
			
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		User::$statement[User::statement_deleteRememberSessionId]->execute($old_session_id);
		Log::trace('DB', 'Executed User::statement_deleteRememberSessionId ['.$old_session_id.'] ('.(microtime(true) - $start_timestamp).')');
		
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:false;
		
		$location = $PAGE['INDEX'];
		
		if ($referer) foreach ($PAGE as $key => $url) {
			if (strstr($referer, $url) && !in_array($key, $REDIRECT_BLACKLIST))
				$location = $referer;
		}
		
		header('Location: '.$location);
	}
	
	public function getImpersonatedUid() {
		return $this->impersonated_uid;
	}
	
	public function setImpersonatedUid($new_impersonated_uid) {
		$this->impersonated_uid = $new_impersonated_uid;
		$this->saveCache();
	}
	
	public function getCustomURL() { return $this->custom_url; }
	
	public function setCustomURL($new_custom_url, $persist=true) {	
		$old_custom_url = $this->custom_url;
		$this->custom_url = $new_custom_url;
		
		if ($persist) {
			User::prepareStatement(User::statement_setCustomURL);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setCustomURL]->execute(array($this->custom_url, $this->uid));
			Log::trace('DB', 'Executed User::statement_setCustomURL ["'.$this->custom_url.'", '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
					
			$this->saveCache();
			
			UserList::deleteByCustomURL($old_custom_url);
			UserList::deleteByCustomURL($this->custom_url);
		}
	}
	
	public function getBOSHPassword() { return $this->bosh_password; }
	
	public function setBOSHPassword($new_bosh_password, $persist=true) {	
		$this->bosh_password = $new_bosh_password;
		
		if ($persist) {
			User::prepareStatement(User::statement_setBOSHPassword);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			User::$statement[User::statement_setBOSHPassword]->execute(array($this->bosh_password, $this->uid));
			Log::trace('DB', 'Executed User::statement_setBOSHPassword ["'.$this->bosh_password.'", '.$this->uid.'] ('.(microtime(true) - $start_timestamp).')');
					
			$this->saveCache();
		}
	}
	
	public function voteSpeedCheck($time) {
		$this->vote_speed []= $time;
		$this->vote_speed = array_slice($this->vote_speed, -20, 20);
		$this->saveCache();
		
		if ($this->getPoints() < 400) $threshold = 26; // 1.3 seconds per picture for people probably looking for points
		else $threshold = 12; // 0.6 seconds for people who don't really need the points
		
		return (count($this->vote_speed) == 20 && array_sum($this->vote_speed) < $threshold && array_sum($this->vote_speed) > 0);
	}
	
	public function addLastVote($points) {
		$this->vote_history []= $points;
		$this->vote_history = array_slice($this->vote_history, -25, 25);
		$this->saveCache();
		
		if (count($this->vote_history) == 25) {
			$same = true;
			foreach ($this->vote_history as $old_points) if ($old_points != $points) $same = false;
			return $same;
		}
		
		return false;
	}
	
	public function updateLastVote($points) {
		array_pop($this->vote_history);
		return $this->addLastVote($points);
	}
	
	public function blockVoting($duration) {
		$this->vote_speed = array();
		$this->vote_history = array();
		$this->setVoteBlockTimestamp(time() + $duration);
	}
	
	public function isVotingBlocked() {
		return (time() <= $this->getVoteBlockTimestamp());
	}
	
	public function addVisitedPage($page_name) {
		$this->visit_history[time()]= $page_name;
		$this->visit_history = array_slice($this->visit_history, -5, 5, true);
		$this->saveCache();
	}
	
	public function getRecentlyVisitedPages() {
		return $this->visit_history;
	}
	
	public function setSubmenuHistory($submenu, $page) {
		$this->submenu_history[$submenu] = $page;
		$this->saveCache();
	}
	
	public function getSubmenuHistory($submenu) {
		return isset($this->submenu_history[$submenu])?$this->submenu_history[$submenu]:null;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(User::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case User::statement_get:
					User::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['LID'].', '.$COLUMN['UID'].', '.$COLUMN['STATUS']
						.', '.$COLUMN['NAME'].', '.$COLUMN['PASSWORD'].', '.$COLUMN['EMAIL'].', '.$COLUMN['ACTIVATION_CODE']
						.', '.$COLUMN['SESSION_ID'].', '.$COLUMN['PID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.', '.$COLUMN['POINTS']
						.', '.$COLUMN['COMMUNITY_FILTER_ICONS']
						.', '.$COLUMN['DISPLAY_RANK']
						.', '.$COLUMN['DESCRIPTION']
						.', '.$COLUMN['DISPLAY_GENERAL_DISCUSSION']
						.', '.$COLUMN['COMMENTS_RECEIVED']
						.', '.$COLUMN['HIDE_ADS']
						.', '.$COLUMN['TRANSLATE']
						.', '.$COLUMN['AFFILIATE_UID']
						.', '.$COLUMN['ALERT_EMAIL']
						.', '.$COLUMN['ALLOW_SALES']
						.', '.$COLUMN['MARKUP']
						.', '.$COLUMN['BALANCE']
						.', UNIX_TIMESTAMP('.$COLUMN['PREMIUM_TIME'].') AS '.$COLUMN['PREMIUM_TIME']
						.', UNIX_TIMESTAMP('.$COLUMN['VOTE_BLOCK_TIMESTAMP'].') AS '.$COLUMN['VOTE_BLOCK_TIMESTAMP']
						.', '.$COLUMN['CUSTOM_URL']
						.', '.$COLUMN['LAZY']
						.', '.$COLUMN['BOSH_PASSWORD']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case User::statement_create:
					User::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['USER'].'( '.$COLUMN['UID'].', '.$COLUMN['LID'].', '.$COLUMN['STATUS'].', '.$COLUMN['POINTS'].', '.$COLUMN['ALERT_EMAIL']
						.') VALUES(?, ?, ?, ?, ?)', array('text', 'integer', 'integer', 'integer', 'boolean'));
					break;	
				case User::statement_setLid:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['LID'], 'integer');
					break;
				case User::statement_setPid:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['PID'], 'integer');
					break;
				case User::statement_setStatus:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['STATUS'], 'integer');
					break;
				case User::statement_setName:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['NAME'], 'text');
					break;
				case User::statement_createNameHistory:
					User::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['USER_NAME_HISTORY'].'( '.$COLUMN['UID'].', '.$COLUMN['NAME']
						.') VALUES(?, ?)', array('text', 'text'));
					break;	
				case User::statement_getByEmail:
					User::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' USE INDEX('.$COLUMN['EMAIL'].')'
						.' WHERE '.$COLUMN['EMAIL'].' = ?'
								, array('text'));
					break;
				case User::statement_setEmail:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['EMAIL'], 'text');
					break;
				case User::statement_setPassword:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['PASSWORD'], 'text');
					break;
				case User::statement_delete:
					User::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' WHERE '.$COLUMN['UID'].' = ?'
						, array('text'));
					break;	
				case User::statement_setActivationCode:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['ACTIVATION_CODE'], 'text');
					break;
				case User::statement_getByActivationCode:
					User::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' USE INDEX('.$COLUMN['ACTIVATION_CODE'].')'
						.' WHERE '.$COLUMN['ACTIVATION_CODE'].' = ?'
								, array('text'));
					break;
				case User::statement_setSessionId:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['SESSION_ID'], 'text');
					break;
				case User::statement_getBySessionId:
					User::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' USE INDEX('.$COLUMN['SESSION_ID'].')'
						.' WHERE '.$COLUMN['SESSION_ID'].' = ?'
								, array('text'));
					break;
				case User::statement_createIPHistory:
					User::$statement[$statement] = DB::prepareWrite( 
						'REPLACE INTO '.$DATABASE['PREFIX'].$TABLE['USER_IP_HISTORY']
						.'( '.$COLUMN['UID'].', '.$COLUMN['IP'].', '.$COLUMN['USER_AGENT']
						.') VALUES(?, ?, ?)', array('text', 'text', 'text'));
					break;
				case User::statement_createHostCookieHistory:
					User::$statement[$statement] = DB::prepareWrite( 
						'REPLACE INTO '.$DATABASE['PREFIX'].$TABLE['USER_HOST_COOKIE_HISTORY']
						.'( '.$COLUMN['UID'].', '.$COLUMN['HOST_COOKIE']
						.') VALUES(?, ?)', array('text', 'text'));
					break;
				case User::statement_createWebHistory:
					User::$statement[$statement] = DB::prepareWrite( 
						'REPLACE INTO '.$DATABASE['PREFIX'].$TABLE['USER_WEB_HISTORY']
						.'( '.$COLUMN['UID'].', '.$COLUMN['URL']
						.') VALUES(?, ?)', array('text', 'text'));
					break;
				case User::statement_setCommunityFilterIcons:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['COMMUNITY_FILTER_ICONS'], 'boolean');
					break;
				case User::statement_getWebHistoryURLs:
					User::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['URL']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_WEB_HISTORY']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case User::statement_getIPHistory:
					User::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['IP'].', UNIX_TIMESTAMP('.$COLUMN['LAST_TIME'].') AS '.$COLUMN['LAST_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_IP_HISTORY']
						.' USE INDEX('.$COLUMN['UID'].')'
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case User::statement_setPoints:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['POINTS'], 'integer');
					break;
				case User::statement_deleteIPHistory:
					User::$statement[$statement] = DB::prepareRead( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['USER_IP_HISTORY']
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case User::statement_deleteNameHistory:
					User::$statement[$statement] = DB::prepareRead( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['USER_NAME_HISTORY']
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case User::statement_deleteHostCookieHistory:
					User::$statement[$statement] = DB::prepareRead( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['USER_HOST_COOKIE_HISTORY']
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case User::statement_deleteWebHistory:
					User::$statement[$statement] = DB::prepareRead( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['USER_WEB_HISTORY']
						.' WHERE '.$COLUMN['UID'].' = ?'
								, array('text'));
					break;
				case User::statement_deleteRememberSessionId:
					User::$statement[$statement] = DB::prepareRead( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['USER_REMEMBER_SESSION_ID']
						.' WHERE '.$COLUMN['SESSION_ID'].' = ?'
								, array('text'));
					break;
				case User::statement_getByRememberSessionId:
					User::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_REMEMBER_SESSION_ID']
						.' WHERE '.$COLUMN['SESSION_ID'].' = ?'
								, array('text'));
					break;
				case User::statement_addRememberSessionId:
					User::$statement[$statement] = DB::prepareWrite( 
						'REPLACE INTO '.$DATABASE['PREFIX'].$TABLE['USER_REMEMBER_SESSION_ID']
						.'( '.$COLUMN['UID'].', '.$COLUMN['SESSION_ID']
						.') VALUES(?, ?)', array('text', 'text'));
					break;
				case User::statement_setDisplayRank:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['DISPLAY_RANK'], 'boolean');
					break;
				case User::statement_setDescription:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['DESCRIPTION'], 'text');
					break;
				case User::statement_setCreationTime:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['CREATION_TIME'], 'timestamp');
					break;
				case User::statement_setDisplayGeneralDiscussion:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['DISPLAY_GENERAL_DISCUSSION'], 'boolean');
					break;
				case User::statement_setCommentsReceived:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['COMMENTS_RECEIVED'], 'integer');
					break;
				case User::statement_setHideAds:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['HIDE_ADS'], 'boolean');
					break;
				case User::statement_setPremiumTime:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['PREMIUM_TIME'], 'timestamp');
					break;
				case User::statement_setAffiliateUid:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['AFFILIATE_UID'], 'text');
					break;
				case User::statement_setAlertEmail:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['ALERT_EMAIL'], 'boolean');
					break;
				case User::statement_setAllowSales:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['ALLOW_SALES'], 'boolean');
					break;
				case User::statement_setMarkup:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['MARKUP'], 'integer');
					break;
				case User::statement_setBalance:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['BALANCE'], 'float');
					break;
				case User::statement_setCustomURL:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['CUSTOM_URL'], 'text');
					break;
				case User::statement_setTranslate:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['TRANSLATE'], 'boolean');
					break;
				case User::statement_setVoteBlockTimestamp:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['VOTE_BLOCK_TIMESTAMP'], 'timestamp');
					break;
				case User::statement_setLazy:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['LAZY'], 'boolean');
					break;
				case User::statement_setBOSHPassword:
					User::$statement[$statement] = DB::prepareSetter($TABLE['USER'], array($COLUMN['UID'] => 'text'), $COLUMN['BOSH_PASSWORD'], 'text');
					break;
			}
		}
	}
}

?>