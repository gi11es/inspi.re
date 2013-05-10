<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/picturefile.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

class PictureFileListException extends Exception {}

class PictureFileList {
	private static $statement = array();
	
	const statement_getByStatus = 1;
	const statement_getByPid = 2;
	
	const cache_prefix_status = 'PictureFileListByStatus-';
	const cache_prefix_pid = 'PictureFileListByPid-';
	
	public static function deleteByStatus($status) {
		try { Cache::delete(PictureFileList::cache_prefix_status.$status); } catch (CacheException $e) {}
	}
	
	public static function getByStatus($status) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(PictureFileList::cache_prefix_status.$status);
		} catch (CacheException $e) { 
			PictureFileList::prepareStatement(PictureFileList::statement_getByStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PictureFileList::$statement[PictureFileList::statement_getByStatus]->execute($status);
			Log::trace('DB', 'Executed PictureFileList::statement_getByStatus ['.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['FID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(PictureFileList::cache_prefix_status.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function deleteByPid($pid) {
		try { Cache::delete(PictureFileList::cache_prefix_pid.$pid); } catch (CacheException $e) {}
	}
	
	public static function getByPid($pid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(PictureFileList::cache_prefix_pid.$pid);
		} catch (CacheException $e) { 
			PictureFileList::prepareStatement(PictureFileList::statement_getByPid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PictureFileList::$statement[PictureFileList::statement_getByPid]->execute($pid);
			Log::trace('DB', 'Executed PictureFileList::statement_getByPid ['.$pid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['FID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(PictureFileList::cache_prefix_pid.$pid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PictureFileList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PictureFileList::statement_getByStatus:
					PictureFileList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['FID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PICTURE_FILE']
						.' USE INDEX('.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['STATUS'].' = ?'
								, array('integer'));
					break;
				case PictureFileList::statement_getByPid:
					PictureFileList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['FID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PICTURE_FILE']
						.' USE INDEX('.$COLUMN['STATUS'].')'
						.' WHERE '.$COLUMN['PID'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>