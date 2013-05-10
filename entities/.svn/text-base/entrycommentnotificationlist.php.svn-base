<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

require_once 'MDB2.php';
require_once 'MDB2/Date.php';

class EntryCommentNotificationListException extends Exception {}

class EntryCommentNotificationList {
	private static $statement = array();
	
	const statement_getByEid = 1;
	const statement_getCommentsByUid = 2;
	
	const cache_prefix_eid = 'EntryCommentNotificationListByEid-';
	
	public static function deleteByEid($eid) {
		try { Cache::delete(EntryCommentNotificationList::cache_prefix_eid.$eid); } catch (CacheException $e) {}
	}
	
	public static function getByEid($eid, $cache = true) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(EntryCommentNotificationList::cache_prefix_eid.$eid);
		} catch (CacheException $e) { 
			EntryCommentNotificationList::prepareStatement(EntryCommentNotificationList::statement_getByEid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = EntryCommentNotificationList::$statement[EntryCommentNotificationList::statement_getByEid]->execute($eid);
			Log::trace('DB', 'Executed EntryCommentNotificationList::statement_getByEid ['.$eid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['UID']];
				$result->free();
			}
			
			if ($cache) try {
				Cache::setorreplace(EntryCommentNotificationList::cache_prefix_eid.$eid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getCommentsByUid($uid) {
		global $COLUMN;
		

        EntryCommentNotificationList::prepareStatement(EntryCommentNotificationList::statement_getCommentsByUid);
        
        $start_timestamp = microtime(true);
        DB::incrementRequestCount();
        $result = EntryCommentNotificationList::$statement[EntryCommentNotificationList::statement_getCommentsByUid]->execute($uid);
        Log::trace('DB', 'Executed EntryCommentNotificationList::statement_getCommentsByUid ['.$uid.'] ('.(microtime(true) - $start_timestamp).')');
        
        $list = array();
		if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list [$row['oid']]= array('creation_time' => $row['creation_time'], 'eid' => $row['eid']);
				$result->free();
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(EntryCommentNotificationList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case EntryCommentNotificationList::statement_getByEid:
					EntryCommentNotificationList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['EID'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['ENTRY_COMMENT_NOTIFICATION']
						.' WHERE '.$COLUMN['EID'].' = ?'
								, array('integer'));
					break;
				case EntryCommentNotificationList::statement_getCommentsByUid:
				    EntryCommentNotificationList::$statement[$statement] = DB::prepareRead( 
                        'SELECT dp.oid, UNIX_TIMESTAMP(dp.creation_time) AS creation_time, nt.eid
                        FROM  `inspire_entry_comment_notification` nt
                        LEFT JOIN inspire_discussion_thread dt ON nt.eid = dt.eid
                        LEFT JOIN inspire_discussion_post dp ON dt.nid = dp.nid
                        WHERE nt.uid = ?
                        AND dp.oid IS NOT NULL', array('text'));
                    break;
			}
		}
	}
}

?>