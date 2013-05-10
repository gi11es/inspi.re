<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

require_once 'MDB2/Date.php';
require_once 'MDB2.php';

class UserListException extends Exception {}

class UserList {
	private static $statement = array();
	
	const statement_getByStatus = 1;
	const statement_getByName = 3;
	const statement_getByHostCookie = 4;
	const statement_getByLid = 5;
	const statement_getRegistered = 6;
	const statement_getActive = 7;
	const statement_getRecentlyRegistered = 8;
	const statement_getByCommentsReceived = 9;
	const statement_getByCustomURL = 10;
	const statement_getDuplicateHostCookie = 11;
	
	const cache_prefix_status = 'UserListByStatus-';
	const cache_prefix_name = 'UserListByName-';
	const cache_prefix_host_cookie = 'UserListByHostCookie-';
	const cache_prefix_lid = 'UserListByLid-';
	const cache_prefix_active_24_hours = 'UserListActive24Hours-';
	const cache_prefix_active_30_days = 'UserListActive30Days-';
	const cache_prefix_registered_24_hours = 'UserListRegistered24Hours-';
	const cache_prefix_live = 'UserListLive-';
	const cache_prefix_recently_registered = 'UserListRecentlyRegistered-';
	const cache_prefix_less_than_5_comments_received = 'UserListLessThan5CommentsReceived-';
	const cache_prefix_custom_url = 'UserListByCustomURL-';
	
	public static function deleteByStatus($status) {
		try { Cache::delete(UserList::cache_prefix_status.$status); } catch (CacheException $e) {}
	}
	
	public static function getByStatus($status, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserList::cache_prefix_status.$status);
		} catch (CacheException $e) { 
			UserList::prepareStatement(UserList::statement_getByStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserList::$statement[UserList::statement_getByStatus]->execute($status);
			Log::trace('DB', 'Executed UserList::statement_getByStatus ['.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$list = $result->fetchAll(MDB2_FETCHMODE_ASSOC, true); // UID => CREATION_TIME
				$result->free();
			}

			if ($cache) try {
				Cache::setorreplace(UserList::cache_prefix_status.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByName($name) {
		try { Cache::delete(UserList::cache_prefix_name.$name); } catch (CacheException $e) {}
	}
	
	public static function getByName($name, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserList::cache_prefix_name.$name);
		} catch (CacheException $e) { 
			UserList::prepareStatement(UserList::statement_getByName);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserList::$statement[UserList::statement_getByName]->execute($name);
			Log::trace('DB', 'Executed UserList::statement_getByName ["'.$name.'"], ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['UID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(UserList::cache_prefix_name.$name, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByHostCookie($host_cookie) {
		try { Cache::delete(UserList::cache_prefix_host_cookie.$host_cookie); } catch (CacheException $e) {}
	}
	
	public static function getByHostCookie($host_cookie, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserList::cache_prefix_host_cookie.$host_cookie);
		} catch (CacheException $e) { 
			UserList::prepareStatement(UserList::statement_getByHostCookie);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserList::$statement[UserList::statement_getByHostCookie]->execute($host_cookie);
			Log::trace('DB', 'Executed UserList::statement_getByHostCookie ['.$host_cookie.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['UID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(UserList::cache_prefix_host_cookie.$host_cookie, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getDuplicateHostCookie() {
		global $COLUMN;
		
		UserList::prepareStatement(UserList::statement_getDuplicateHostCookie);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = UserList::$statement[UserList::statement_getDuplicateHostCookie]->execute();
		Log::trace('DB', 'Executed UserList::statement_getDuplicateHostCookie [] ('.(microtime(true) - $start_timestamp).')');

		$list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
			while ($row = $result->fetchRow()) $list []= $row[$COLUMN['HOST_COOKIE']];
			$result->free();
		}
		
		return $list;
	}
	
	public static function deleteByLid($lid) {
		try { Cache::delete(UserList::cache_prefix_lid.$lid); } catch (CacheException $e) {}
	}
	
	public static function getByLid($lid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserList::cache_prefix_lid.$lid);
		} catch (CacheException $e) { 
			UserList::prepareStatement(UserList::statement_getByLid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserList::$statement[UserList::statement_getByLid]->execute($lid);
			Log::trace('DB', 'Executed UserList::statement_getByLid ['.$lid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['UID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(UserList::cache_prefix_lid.$lid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByLessThan5CommentsReceived() {
		try { Cache::delete(UserList::cache_prefix_less_than_5_comments_received); } catch (CacheException $e) {}
	}
	
	public static function getByLessThan5CommentsReceived($cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserList::cache_prefix_less_than_5_comments_received);
		} catch (CacheException $e) { 
			UserList::prepareStatement(UserList::statement_getByCommentsReceived);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserList::$statement[UserList::statement_getByCommentsReceived]->execute(5);
			Log::trace('DB', 'Executed UserList::statement_getByCommentsReceived [5] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['UID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(UserList::cache_prefix_less_than_5_comments_received, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function addRegistered($uid, $timestamp) {
		if ($uid === null) return;
		
		$list = UserList::getRegistered24Hours();
		Cache::lock('UserListRegistered24Hours');
		$list[$uid] = $timestamp;
		asort($list);
		$original_count = count($list);
		foreach ($list as $uid => $timestamp) {
			if ($timestamp < time() - 86400)
				unset($list[$uid]);
		}

		try {
			Cache::setorreplace(UserList::cache_prefix_registered_24_hours, $list);
		} catch (CacheException $e) {}
		Cache::unlock('UserListRegistered24Hours');
	}
	
	public static function getRegistered24Hours($cache = true) {
		global $COLUMN;
		
		Cache::lock('UserListRegistered24Hours');
		
		try {
			$list = Cache::get(UserList::cache_prefix_registered_24_hours);
			asort($list);

			$unset = false;
			foreach ($list as $uid => $timestamp) {
				if ($timestamp < time() - 86400) {
					unset($list[$uid]);
					$unset = true;
				}
			}
			
			if ($unset) try {
				Cache::setorreplace(UserList::cache_prefix_registered_24_hours, $list);
			} catch (CacheException $e) {}
		} catch (CacheException $e) {
			UserList::prepareStatement(UserList::statement_getRegistered);
			
			$timestamp = time() - 86400;
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserList::$statement[UserList::statement_getRegistered]->execute(MDB2_Date::unix2Mdbstamp($timestamp));
			Log::trace('DB', 'Executed UserList::statement_getRegistered ['.$timestamp.'] ('.(microtime(true) - $start_timestamp).')');
		
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list[$row[$COLUMN['UID']]]= $row[$COLUMN['CREATION_TIME']];
				$result->free();
			}

			try {
				Cache::setorreplace(UserList::cache_prefix_registered_24_hours, $list);
			} catch (CacheException $e) {}
		}
		Cache::unlock('UserListRegistered24Hours');
		return $list;
	}
	
	public static function addActive($uid, $timestamp) {
		if ($uid === null) return;
		
		$list = UserList::getActive24Hours();
		Cache::lock('UserListActive24Hours');
		$list[$uid] = $timestamp;
		asort($list);
		$original_count = count($list);
		foreach ($list as $uid => $timestamp) {
			if ($timestamp < time() - 86400)
				unset($list[$uid]);
		}

		try {
			Cache::setorreplace(UserList::cache_prefix_active_24_hours, $list);
		} catch (CacheException $e) {}
		Cache::unlock('UserListActive24Hours');
		
		$list = UserList::getActive30Days();
		Cache::lock('UserListActive30Days');
		$list[$uid] = $timestamp;
		asort($list);
		$original_count = count($list);
		foreach ($list as $uid => $timestamp) {
			if ($timestamp < time() - 2592000)
				unset($list[$uid]);
		}

		try {
			Cache::setorreplace(UserList::cache_prefix_active_30_days, $list);
		} catch (CacheException $e) {}
		Cache::unlock('UserListActive30Days');
	}
	
	public static function getActive24Hours($cache = true) {
		global $COLUMN;
		
		Cache::lock('UserListActive24Hours');
		try {
			$list = Cache::get(UserList::cache_prefix_active_24_hours);
			asort($list);
			$original_count = count($list);
			foreach ($list as $uid => $timestamp) {
				if ($timestamp < time() - 86400)
					unset($list[$uid]);
			}
			
			if (count($list) < $original_count) try {
				Cache::setorreplace(UserList::cache_prefix_active_24_hours, $list);
			} catch (CacheException $e) {}
		} catch (CacheException $e) {
			UserList::prepareStatement(UserList::statement_getActive);
			
			$timestamp = time() - 86400;
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserList::$statement[UserList::statement_getActive]->execute(MDB2_Date::unix2Mdbstamp($timestamp));
			Log::trace('DB', 'Executed UserList::statement_getActive ['.$timestamp.'] ('.(microtime(true) - $start_timestamp).')');
		
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list [$row[$COLUMN['UID']]]= $row[$COLUMN['LAST_TIME']];
				$result->free();
			}
			
			
			try {
				Cache::setorreplace(UserList::cache_prefix_active_24_hours, $list);
			} catch (CacheException $e) {}
			
		}
		
		Cache::unlock('UserListActive24Hours');
		return $list;
	}
	
	public static function getActive30Days($cache = true) {
		global $COLUMN;
		
		Cache::lock('UserListActive30Days');
		
		try {
			$list = Cache::get(UserList::cache_prefix_active_30_days);
			asort($list);
			$original_count = count($list);
			foreach ($list as $uid => $timestamp) {
				if ($timestamp < time() - 2592000)
					unset($list[$uid]);
			}
			
			if (count($list) < $original_count) try {
				Cache::setorreplace(UserList::cache_prefix_active_30_days, $list);
			} catch (CacheException $e) {}
		} catch (CacheException $e) {
			UserList::prepareStatement(UserList::statement_getActive);
			
			$timestamp = time() - 2592000;
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserList::$statement[UserList::statement_getActive]->execute(MDB2_Date::unix2Mdbstamp($timestamp));
			Log::trace('DB', 'Executed UserList::statement_getActive ['.$timestamp.'] ('.(microtime(true) - $start_timestamp).')');
		
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list [$row[$COLUMN['UID']]]= $row[$COLUMN['LAST_TIME']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(UserList::cache_prefix_active_30_days, $list);
			} catch (CacheException $e) {}
			
		}
		
		Cache::unlock('UserListActive30Days');
		return $list;
	}
	
	public static function deleteRecentlyRegistered($count) {
		Cache::lock('UserListRecentlyRegistered'.$count);
		try {
			Cache::delete(UserList::cache_prefix_recently_registered.$count);
		} catch (CacheException $e) {}
		Cache::unlock('UserListRecentlyRegistered'.$count);
	}
	
	public static function addRecentlyRegistered($uid, $creation_time, $count) {
		if ($uid === null) return;
		
		Log::xmpp('USER_REGISTERED', '<profile_picture class="member_thumbnail" uid="'.$uid.'" size="small" id="registered_user_'.$uid.'" style="hidden"/>');	
		
		$list = UserList::getRecentlyRegistered($count);
		Cache::lock('UserListRecentlyRegistered'.$count);
		
		$list[$uid] = $creation_time;
		
		arsort($list);
		
		while (count($list) > $count) array_pop($list);

		try {
			Cache::replaceorset(UserList::cache_prefix_recently_registered.$count, $list);
		} catch (CacheException $e) {}
		Cache::unlock('UserListRecentlyRegistered'.$count);
	}
	
	public static function getRecentlyRegistered($count, $cache = true) {
		global $COLUMN;
		
		Cache::lock('UserListRecentlyRegistered'.$count);
		
		try {
			$list = Cache::get(UserList::cache_prefix_recently_registered.$count);
		} catch (CacheException $e) {
			UserList::prepareStatement(UserList::statement_getRecentlyRegistered);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserList::$statement[UserList::statement_getRecentlyRegistered]->execute($count);
			Log::trace('DB', 'Executed UserList::statement_getRecentlyRegistered ['.$count.'] ('.(microtime(true) - $start_timestamp).')');

			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list [$row[$COLUMN['UID']]]= $row[$COLUMN['CREATION_TIME']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(UserList::cache_prefix_recently_registered.$count, $list);
			} catch (CacheException $e) {}
		}
		
		Cache::unlock('UserListRecentlyRegistered'.$count);
		return $list;
	}
	
	// There could be inconsistencies in the list (users not added every now and then), but the accuracy of the list of live users is absolutely non-critical
	// We actually had a cache lock at some stage, but the waiting time it generated wasn't worth it
	public static function addLive($uid, $timestamp) {
		global $APPEARING_OFFLINE_DELAY;
		
		$changed = false;
		
		if ($uid === null) return;
		
		$list = UserList::getLive();
		
		if (!isset($list[$uid]) || $timestamp - $list[$uid] > 60) {
			$changed = true;
			$list[$uid] = $timestamp;
			
			Log::xmpp('USER_ON', '<profile_picture class="member_thumbnail" uid="'.$uid.'" size="small" id="user_'.$uid.'" style="hidden"/>');	
		}
		asort($list);
		$original_count = count($list);
		foreach ($list as $uid => $timestamp) {
			if ($timestamp < gmmktime() - $APPEARING_OFFLINE_DELAY) {
				$changed = true;
				unset($list[$uid]);
				Log::xmpp('USER_OFF', $uid);
			}
		}

		if ($changed) try {
			Cache::replaceorset(UserList::cache_prefix_live, $list);
		} catch (CacheException $e) {}
	}
	
	public static function getLive() {
		$list = array();
		try {
			$list = Cache::get(UserList::cache_prefix_live);
		} catch (CacheException $e) {}
		return $list;
	}
	
	public static function deleteByCustomURL($custom_url) {
		try { Cache::delete(UserList::cache_prefix_custom_url.$custom_url); } catch (CacheException $e) {}
	}
	
	public static function getByCustomURL($custom_url, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(UserList::cache_prefix_custom_url.$custom_url);
		} catch (CacheException $e) { 
			UserList::prepareStatement(UserList::statement_getByCustomURL);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = UserList::$statement[UserList::statement_getByCustomURL]->execute($custom_url);
			Log::trace('DB', 'Executed UserList::statement_getByCustomURL ["'.$custom_url.'"], ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['UID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(UserList::cache_prefix_custom_url.$custom_url, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		global $USER_STATUS;
		
		if (!isset(UserList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case UserList::statement_getByStatus:
					UserList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID'].', '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' USE INDEX('.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['STATUS'].' = ?'
								, array('integer'));
					break;
				case UserList::statement_getByName:
					UserList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_NAME_HISTORY']
						.' USE INDEX('.$COLUMN['NAME'].')'
						.' WHERE '.$COLUMN['NAME'].' = ? ORDER BY '.$COLUMN['NAME_TIME']
								, array('text'));
					break;
				case UserList::statement_getByHostCookie:
					UserList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_HOST_COOKIE_HISTORY']
						.' USE INDEX('.$COLUMN['HOST_COOKIE'].')'
						.' WHERE '.$COLUMN['HOST_COOKIE'].' = ?'
								, array('text'));
					break;
				case UserList::statement_getByLid:
					UserList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' USE INDEX('.$COLUMN['LID'].')'
						.' WHERE '.$COLUMN['LID'].' = ? AND '.$COLUMN['STATUS'].' = '.$USER_STATUS['ACTIVE']
								, array('integer'));
					break;
				case UserList::statement_getRegistered:
					UserList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' WHERE '.$COLUMN['CREATION_TIME'].' >= ? AND '.$COLUMN['STATUS'].' = '.$USER_STATUS['ACTIVE']
								, array('timestamp'));
					break;
				case UserList::statement_getActive:
					UserList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.', UNIX_TIMESTAMP('.$COLUMN['LAST_TIME'].') AS '.$COLUMN['LAST_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_IP_HISTORY']
						.' WHERE '.$COLUMN['LAST_TIME'].' >= ?'
								, array('timestamp'));
					break;
				case UserList::statement_getRecentlyRegistered:
					UserList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.', UNIX_TIMESTAMP('.$COLUMN['CREATION_TIME'].') AS '.$COLUMN['CREATION_TIME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' WHERE '.$COLUMN['STATUS'].' = '.$USER_STATUS['ACTIVE'].' ORDER BY '.$COLUMN['CREATION_TIME'].' DESC LIMIT 0, ?'
								, array('integer'));
					break;
				case UserList::statement_getByCommentsReceived:
					UserList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' USE INDEX('.$COLUMN['COMMENTS_RECEIVED'].')'
						.' WHERE '.$COLUMN['COMMENTS_RECEIVED'].' < ? AND '.$COLUMN['STATUS'].' = '.$USER_STATUS['ACTIVE']
								, array('integer'));
					break;
				case UserList::statement_getByCustomURL:
					UserList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER']
						.' USE INDEX('.$COLUMN['CUSTOM_URL'].')'
						.' WHERE '.$COLUMN['CUSTOM_URL'].' = ?'
								, array('text'));
					break;
				case UserList::statement_getDuplicateHostCookie:
					UserList::$statement[$statement] = DB::prepareRead( 
						'SELECT count('.$COLUMN['HOST_COOKIE'].') as count_cookie, '
						.$COLUMN['HOST_COOKIE']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['USER_HOST_COOKIE_HISTORY']
						.' USE INDEX('.$COLUMN['HOST_COOKIE'].')'
						.' GROUP BY '.$COLUMN['HOST_COOKIE']
						.' HAVING count_cookie > 1'
								, array());
					break;
			}
		}
	}
}

?>