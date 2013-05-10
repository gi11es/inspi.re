<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

class PictureListException extends Exception {}

class PictureList {
	private static $statement = array();
	
	const statement_getByHugeStatus = 1;
	const statement_getByBigStatus = 2;
	const statement_getByMediumStatus = 3;
	const statement_getBySmallStatus = 4;
	
	const cache_prefix_status = 'PictureListByStatus-';
	
	public static function deleteByStatus($size, $status) {
		try { Cache::delete(PictureList::cache_prefix_status.$size.'-'.$status); } catch (CacheException $e) {}
	}
	
	public static function getByStatus($size, $status) {
		global $COLUMN;
		global $PICTURE_SIZE;
		
		try {
			 $list = Cache::get(PictureList::cache_prefix_status.$size.'-'.$status);
		} catch (CacheException $e) { 
			switch ($size) {
				case $PICTURE_SIZE['HUGE']:
					$statement = PictureList::statement_getByHugeStatus;
					$trace_name = 'Huge';
					break;
				case $PICTURE_SIZE['BIG']:
					$statement = PictureList::statement_getByBigStatus;
					$trace_name = 'Big';
					break;
				case $PICTURE_SIZE['MEDIUM']:
					$statement = PictureList::statement_getByMediumStatus;
					$trace_name = 'Medium';
					break;
				case $PICTURE_SIZE['SMALL']:
					$statement = PictureList::statement_getBySmallStatus;
					$trace_name = 'Small';
					break;
				default:
					$trace_name = 'ERROR';
					throw new PictureListException('Invalid picture size passed to getByStatus');
					break;
			}
			PictureList::prepareStatement($statement);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PictureList::$statement[$statement]->execute($status);
			Log::trace('DB', 'Executed PictureList::statement_getBy'.$trace_name.'Status ['.$status.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				while ($row = $result->fetchRow()) $list []= $row[$COLUMN['PID']];
				$result->free();
			}
			
			try {
				Cache::setorreplace(PictureList::cache_prefix_status.$size.'-'.$status, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PictureList::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PictureList::statement_getByHugeStatus:
					PictureList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PICTURE']
						.' USE INDEX('.$COLUMN['HUGE_STATUS'].')'
						.' WHERE '.$COLUMN['HUGE_STATUS'].' = ?'
								, array('integer'));
					break;
				case PictureList::statement_getByBigStatus:
					PictureList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PICTURE']
						.' USE INDEX('.$COLUMN['BIG_STATUS'].')'
						.' WHERE '.$COLUMN['BIG_STATUS'].' = ?'
								, array('integer'));
					break;
				case PictureList::statement_getByMediumStatus:
					PictureList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PICTURE']
						.' USE INDEX('.$COLUMN['MEDIUM_STATUS'].')'
						.' WHERE '.$COLUMN['MEDIUM_STATUS'].' = ?'
								, array('integer'));
					break;
				case PictureList::statement_getBySmallStatus:
					PictureList::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['PID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PICTURE']
						.' USE INDEX('.$COLUMN['SMALL_STATUS'].')'
						.' WHERE '.$COLUMN['SMALL_STATUS'].' = ?'
								, array('integer'));
					break;
			}
		}
	}
}

?>