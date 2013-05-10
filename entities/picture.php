<?php
    
/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Picture support
*/

require_once(dirname(__FILE__).'/../entities/picturefile.php');
require_once(dirname(__FILE__).'/../entities/picturefilelist.php');
require_once(dirname(__FILE__).'/../entities/picturelist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/image.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../utilities/url.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

require_once('MDB2/Date.php');

class PictureException extends Exception {}

class Picture implements Persistent {
	private $pid = null;
	private $fid = array();
	private $offset_x = null;
	private $offset_y = null;
	private $dimension = null;
	private $status = array();
	private $timestamp = array();
	private $exif = array();
	public $invalid = false;
	
	const cache_prefix = 'Picture-';
	
	const statement_get = 1;
	const statement_create = 3;
	const statement_setOriginalFid = 4;
	const statement_setBigFid = 5;
	const statement_setMediumFid = 6;
	const statement_setSmallFid = 7;
	const statement_setHugeFid = 8;
	const statement_setOffsetX = 9;
	const statement_setOffsetY = 10;
	const statement_setDimension = 11;
	const statement_setBigTimestamp = 12;
	const statement_setMediumTimestamp = 13;
	const statement_setSmallTimestamp = 14;
	const statement_setHugeTimestamp = 15;
	const statement_setBigStatus = 16;
	const statement_setMediumStatus = 17;
	const statement_setSmallStatus = 18;
	const statement_setHugeStatus = 19;
	const statement_delete = 20;
	
	private static $statement = array();
	
	public function __construct() {
		$argv = func_get_args();
		switch (func_num_args()) {
			case 1:
				self::__construct2($argv[0]);
				break;
			case 8:
				self::__construct3($argv[0], $argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6], $argv[7]);
				break;
		}
    }
	
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of picture with pid='.$this->pid);
		
		try {
			Cache::replaceorset(Picture::cache_prefix.$this->pid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of picture file with pid='.$this->pid);
		}
	}
	
	public function __construct2($original_file) {
		global $PICTURE_STATUS;
		global $PICTURE_SIZE;
		global $EXIF;
		
		Picture::prepareStatement(Picture::statement_create);
		
		Log::trace(__CLASS__, "Creating picture based on file ".$original_file);
		
		$exif_source = @exif_read_data($original_file);
		
		$original = new PictureFile($original_file);
		if ($original->invalid) {
			$this->invalid = true;
		} else {
		
			// Load available exif data and put null on fields without a value
			foreach ($EXIF as $exif_tag) {
				if (isset($exif_source[$exif_tag])) {
					if ($exif_tag == $EXIF['EXPOSURE_TIME'] || $exif_tag == $EXIF['FNUMBER'] || $exif_tag == $EXIF['FOCAL_LENGTH'])
						$value = Image::evalEXIFRational($exif_source[$exif_tag]);
					elseif ($exif_tag == $EXIF['DATE_TIME_ORIGINAL'])
						$value = Image::evalEXIFDate($exif_source[$exif_tag]);
					else $value = $exif_source[$exif_tag];
				} else $value = null;
				$this->exif[$exif_tag] = $value;
			}
			
			$this->setFid($PICTURE_SIZE['ORIGINAL'], $original->getFid(), false);
			$this->setFid($PICTURE_SIZE['HUGE'], null, false);
			$this->setFid($PICTURE_SIZE['BIG'], null, false);
			$this->setFid($PICTURE_SIZE['MEDIUM'], null, false);
			$this->setFid($PICTURE_SIZE['SMALL'], null, false);
			
			$this->setStatus($PICTURE_SIZE['HUGE'], $PICTURE_STATUS['FIRST'], false);
			$this->setStatus($PICTURE_SIZE['BIG'], $PICTURE_STATUS['FIRST'], false);
			$this->setStatus($PICTURE_SIZE['MEDIUM'], $PICTURE_STATUS['FIRST'], false);
			$this->setStatus($PICTURE_SIZE['SMALL'], $PICTURE_STATUS['FIRST'], false);
			
			$current_timestamp = time();
			
			$this->setTimestamp($PICTURE_SIZE['HUGE'], $current_timestamp, false);
			$this->setTimestamp($PICTURE_SIZE['BIG'], $current_timestamp, false);
			$this->setTimestamp($PICTURE_SIZE['MEDIUM'], $current_timestamp, false);
			$this->setTimestamp($PICTURE_SIZE['SMALL'], $current_timestamp, false);
			
			if ($original->getWidth() > $original->getHeight()) {
				$this->setDimension($original->getHeight(), false);
				$this->setOffsetY(0, false);
				$this->setOffsetX(($original->getWidth() - $original->getHeight()) / 2, false);
			} elseif($original->getWidth() <= $original->getHeight()) {
				$this->setDimension($original->getWidth(), false);
				$this->setOffsetX(0, false);
				$this->setOffsetY(($original->getHeight() - $original->getWidth()) / 2, false);
			}
			
			$mdb2_timestamp = MDB2_Date::unix2Mdbstamp($current_timestamp);
			
			
			$date = $this->exif[$EXIF['DATE_TIME_ORIGINAL']] === null ? null : MDB2_Date::unix2Mdbstamp($this->exif[$EXIF['DATE_TIME_ORIGINAL']]);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Picture::$statement[Picture::statement_create]->execute(array($original->getFid(), 
																			$this->offset_x, 
																			$this->offset_y, 
																			$this->dimension, 
																			$mdb2_timestamp, 
																			$mdb2_timestamp, 
																			$mdb2_timestamp, 
																			$mdb2_timestamp,
																			$this->exif[$EXIF['MAKE']],
																			$this->exif[$EXIF['MODEL']],
																			$this->exif[$EXIF['SOFTWARE']],
																			$this->exif[$EXIF['EXPOSURE_TIME']],
																			$this->exif[$EXIF['FNUMBER']],
																			$date,
																			$this->exif[$EXIF['FOCAL_LENGTH']],
																			$this->exif[$EXIF['FLASH']],
																			$this->exif[$EXIF['ISO']]
																		));
			
			Log::trace('DB', 
				'Executed Picture::statement_create ['.$original->getFid()
				.', '.$this->offset_x
				.', '.$this->offset_y
				.', '.$this->dimension
				.', '.$this->exif[$EXIF['DATE_TIME_ORIGINAL']]
				.', '.$this->exif[$EXIF['DATE_TIME_ORIGINAL']]
				.', '.$this->exif[$EXIF['DATE_TIME_ORIGINAL']]
				.', '.$this->exif[$EXIF['DATE_TIME_ORIGINAL']]
				.', '.$this->exif[$EXIF['MAKE']]
				.', '.$this->exif[$EXIF['MODEL']]
				.', '.$this->exif[$EXIF['SOFTWARE']]
				.', '.$this->exif[$EXIF['EXPOSURE_TIME']]
				.', '.$this->exif[$EXIF['FNUMBER']]
				.', '.$date
				.', '.$this->exif[$EXIF['FOCAL_LENGTH']]
				.', '.$this->exif[$EXIF['FLASH']]
				.', '.$this->exif[$EXIF['ISO']].'] ('.(microtime(true) - $start_timestamp).')');
			
			$pid = DB::insertid();
			
			$original->setPid($pid);
			
			$this->setPid($pid);
			
			$this->saveCache();
			
			PictureList::deleteByStatus($PICTURE_SIZE['HUGE'], $PICTURE_STATUS['FIRST']);
			PictureList::deleteByStatus($PICTURE_SIZE['BIG'], $PICTURE_STATUS['FIRST']);
			PictureList::deleteByStatus($PICTURE_SIZE['MEDIUM'], $PICTURE_STATUS['FIRST']);
			PictureList::deleteByStatus($PICTURE_SIZE['SMALL'], $PICTURE_STATUS['FIRST']);
		}
	}
	
	public function __construct3($pid, $fid, $offset_x, $offset_y, $dimension, $status, $timestamp, $exif) {
		$this->pid = $pid;
		$this->fid = $fid;
		$this->offset_x = $offset_x;
		$this->offset_y = $offset_y;
		$this->dimension = $dimension;
		$this->status = $status;
		$this->timestamp = $timestamp;
		$this->exif = $exif;
	}
	
	public function getExif() { return $this->exif; }
	
	public function setPid($pid) { $this->pid = $pid; }
	
	public function getPid() { return $this->pid; }
	
	public function getFid($size) { return $this->fid[$size]; }
	
	public function setFid($size, $fid, $persist=true) {
		global $PICTURE_SIZE;
		
		$this->fid[$size] = $fid;
		
		if ($persist) {
			switch ($size) {
				case $PICTURE_SIZE['HUGE']:
					$statement = Picture::statement_setHugeFid;
					$size_name = 'Huge';
					break;
				case $PICTURE_SIZE['BIG']:
					$statement = Picture::statement_setBigFid;
					$size_name = 'Big';
					break;
				case $PICTURE_SIZE['MEDIUM']:
					$statement = Picture::statement_setMediumFid;
					$size_name = 'Medium';
					break;
				case $PICTURE_SIZE['SMALL']:
					$statement = Picture::statement_setSmallFid;
					$size_name = 'Small';
					break;
				case $PICTURE_SIZE['ORIGINAL']:
					$statement = Picture::statement_setOriginalFid;
					$size_name = 'Original';
					break;
				default:
					throw new PictureException('Invalid picture size passed to setFid');
					break;
			}
			Picture::prepareStatement($statement);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Picture::$statement[$statement]->execute(array($this->fid[$size], $this->getPid()));
			Log::trace('DB', 'Executed Picture::statement_set'.$size_name.'Fid ['.$this->fid[$size].', '.$this->getPid().'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getOffsetX() { return $this->offset_x; }
	
	public function setOffsetX($offset, $persist=true) {
		$this->offset_x = $offset;
		
		if ($persist) {
			Picture::prepareStatement(Picture::statement_setOffsetX);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Picture::$statement[Picture::statement_setOffsetX]->execute(array($this->offset_x, $this->getPid()));
			Log::trace('DB', 'Executed Picture::statement_setOffsetX ['.$this->offset_x.', '.$this->getPid().'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getOffsetY() { return $this->offset_y; }
	
	public function setOffsetY($offset, $persist=true) {
		$this->offset_y = $offset;
		
		if ($persist) {
			Picture::prepareStatement(Picture::statement_setOffsetY);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Picture::$statement[Picture::statement_setOffsetY]->execute(array($this->offset_y, $this->getPid()));
			Log::trace('DB', 'Executed Picture::statement_setOffsetY ['.$this->offset_y.', '.$this->getPid().'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getDimension() { return $this->dimension; }
	
	public function setDimension($dimension, $persist=true) {
		$this->dimension = $dimension;
		
		if ($persist) {
			Picture::prepareStatement(Picture::statement_setDimension);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Picture::$statement[Picture::statement_setDimension]->execute(array($this->dimension, $this->getPid()));
			Log::trace('DB', 'Executed Picture::statement_setDimension ['.$this->dimension.', '.$this->getPid().'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public function getStatus($size) { return $this->status[$size]; }
	
	public function setStatus($size, $status, $persist=true) {
		global $PICTURE_SIZE;
		
		if (isset($this->status[$size]))
			$old_status = $this->status[$size];
		else $old_status = null;
		$this->status[$size] = $status;
		
		if ($persist) {
			switch ($size) {
				case $PICTURE_SIZE['HUGE']:
					$statement = Picture::statement_setHugeStatus;
					$trace_name = 'Huge';
					break;
				case $PICTURE_SIZE['BIG']:
					$statement = Picture::statement_setBigStatus;
					$trace_name = 'Big';
					break;
				case $PICTURE_SIZE['MEDIUM']:
					$statement = Picture::statement_setMediumStatus;
					$trace_name = 'Medium';
					break;
				case $PICTURE_SIZE['SMALL']:
					$statement = Picture::statement_setSmallStatus;
					$trace_name = 'Small';
					break;
				default:
					$trace_name = 'ERROR';
					throw new PictureException('Invalid picture size passed to setStatus');
					break;
			}
			
			Picture::prepareStatement($statement);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Picture::$statement[$statement]->execute(array($this->status[$size], $this->getPid()));
			Log::trace('DB', 'Executed Picture::statement_set'.$trace_name.'Status ['.$this->status[$size].', '.$this->getPid().'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			if ($old_status !== null) PictureList::deleteByStatus($size, $old_status);
			PictureList::deleteByStatus($size, $this->status[$size]);
		}
	}
	
	public function getTimestamp($size) { return $this->timestamp[$size]; }
	
	public function setTimestamp($size, $timestamp, $persist=true) {
		global $PICTURE_SIZE;
		
		$this->timestamp[$size] = $timestamp;
		
		if ($persist) {
			switch ($size) {
				case $PICTURE_SIZE['HUGE']:
					$statement = Picture::statement_setHugeTimestamp;
					$trace_name = 'Huge';
					break;
				case $PICTURE_SIZE['BIG']:
					$statement = Picture::statement_setBigTimestamp;
					$trace_name = 'Big';
					break;
				case $PICTURE_SIZE['MEDIUM']:
					$statement = Picture::statement_setMediumTimestamp;
					$trace_name = 'Medium';
					break;
				case $PICTURE_SIZE['SMALL']:
					$statement = Picture::statement_setSmallTimestamp;
					$trace_name = 'Small';
					break;
				default:
					$trace_name = 'ERROR';
					throw new PictureException('Invalid picture size passed to setTimestamp');
					break;
			}
			
			Picture::prepareStatement($statement);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			Picture::$statement[$statement]->execute(array(MDB2_Date::unix2Mdbstamp($this->timestamp[$size]), $this->getPid()));
			Log::trace('DB', 'Executed Picture::statement_set'.$trace_name.'Timestamp ['.$this->timestamp[$size].', '.$this->getPid().'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
		}
	}
	
	public static function get($pid) {
		global $COLUMN;
		global $PICTURE_SIZE;
		global $EXIF;
		
		if ($pid === null)
			throw new PictureException('No picture for pid = '.$pid);
		
		try {
			return Cache::get(Picture::cache_prefix.$pid);
		} catch (CacheException $e) {
			Picture::prepareStatement(Picture::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = Picture::$statement[Picture::statement_get]->execute($pid);
			Log::trace('DB', 'Executed Picture::statement_get ['.$pid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$row = $result->fetchRow();
				$pic = new Picture($pid, array( $PICTURE_SIZE['ORIGINAL'] => $row[$COLUMN['ORIGINAL_FID']]
										, $PICTURE_SIZE['HUGE'] => $row[$COLUMN['HUGE_FID']]
										, $PICTURE_SIZE['BIG'] => $row[$COLUMN['BIG_FID']]
										, $PICTURE_SIZE['MEDIUM'] => $row[$COLUMN['MEDIUM_FID']]
										, $PICTURE_SIZE['SMALL'] => $row[$COLUMN['SMALL_FID']]
											)
										, $row[$COLUMN['OFFSET_X']]
										, $row[$COLUMN['OFFSET_Y']]
										, $row[$COLUMN['DIMENSION']]
										, array($PICTURE_SIZE['HUGE'] => $row[$COLUMN['HUGE_STATUS']],
											$PICTURE_SIZE['BIG'] => $row[$COLUMN['BIG_STATUS']],
											$PICTURE_SIZE['MEDIUM'] => $row[$COLUMN['MEDIUM_STATUS']],
											$PICTURE_SIZE['SMALL'] => $row[$COLUMN['SMALL_STATUS']]
											)
										, array($PICTURE_SIZE['HUGE'] => $row[$COLUMN['HUGE_TIMESTAMP']],
											$PICTURE_SIZE['BIG'] => $row[$COLUMN['BIG_TIMESTAMP']],
											$PICTURE_SIZE['MEDIUM'] => $row[$COLUMN['MEDIUM_TIMESTAMP']],
											$PICTURE_SIZE['SMALL'] => $row[$COLUMN['SMALL_TIMESTAMP']]
											)
										, array($EXIF['MAKE'] => $row[$COLUMN['EXIF_MAKE']],
												$EXIF['MODEL'] => $row[$COLUMN['EXIF_MODEL']],
												$EXIF['SOFTWARE'] => $row[$COLUMN['EXIF_SOFTWARE']],
												$EXIF['EXPOSURE_TIME'] => $row[$COLUMN['EXIF_EXPOSURE_TIME']],
												$EXIF['FNUMBER'] => $row[$COLUMN['EXIF_FNUMBER']],
												$EXIF['DATE_TIME_ORIGINAL'] => $row[$COLUMN['EXIF_DATE_TIME_ORIGINAL']],
												$EXIF['FOCAL_LENGTH'] => $row[$COLUMN['EXIF_FOCAL_LENGTH']],
												$EXIF['FLASH'] => $row[$COLUMN['EXIF_FLASH']],
												$EXIF['ISO'] => $row[$COLUMN['EXIF_ISO']],
											)
									);
				try {
					Cache::setorreplace(Picture::cache_prefix.$pid, $pic);
				} catch (CacheException $e) {
					Log::critical(__CLASS__, 'Couldn\'t set cache entry for picture with pid='.$pid);
				}
				return $pic;
			} else {
				Log::critical(__CLASS__, 'No picture for pid = '.$pid);
				throw new PictureException('No picture for pid = '.$pid);
			}
		}
	}
	
	public static function getArray($pidlist) {
		$result = array();
		$querylist = array();
		
		foreach ($pidlist as $pid) $querylist []= Picture::cache_prefix.$pid;
		
		$cacheresult = Cache::getArray($querylist);
		
		foreach ($pidlist as $pid) try {
			if (isset($cacheresult[Picture::cache_prefix.$pid])) $result[$pid] = $cacheresult[Picture::cache_prefix.$pid];
			else $result[$pid] = Picture::get($pid);
		} catch (PictureException $e) {}
		
		return $result;
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(Picture::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case Picture::statement_create:
					Picture::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['PICTURE']
						.'( '.$COLUMN['ORIGINAL_FID']
						.', '.$COLUMN['OFFSET_X']
						.', '.$COLUMN['OFFSET_Y']
						.', '.$COLUMN['DIMENSION']
						.', '.$COLUMN['HUGE_TIMESTAMP']
						.', '.$COLUMN['BIG_TIMESTAMP']
						.', '.$COLUMN['MEDIUM_TIMESTAMP']
						.', '.$COLUMN['SMALL_TIMESTAMP']
						.', '.$COLUMN['EXIF_MAKE']
						.', '.$COLUMN['EXIF_MODEL']
						.', '.$COLUMN['EXIF_SOFTWARE']
						.', '.$COLUMN['EXIF_EXPOSURE_TIME']
						.', '.$COLUMN['EXIF_FNUMBER']
						.', '.$COLUMN['EXIF_DATE_TIME_ORIGINAL']
						.', '.$COLUMN['EXIF_FOCAL_LENGTH']
						.', '.$COLUMN['EXIF_FLASH']
						.', '.$COLUMN['EXIF_ISO']
						.') VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
						, array('text', 'integer', 'integer', 'integer', 'timestamp', 'timestamp', 'timestamp', 'timestamp', 'text', 'text', 'text', 'float', 'float', 'timestamp', 'float', 'integer', 'integer'));
					break;
				case Picture::statement_get:
					Picture::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['ORIGINAL_FID'].', '.$COLUMN['BIG_FID']
						.', '.$COLUMN['MEDIUM_FID'].', '.$COLUMN['SMALL_FID']
						.', '.$COLUMN['HUGE_FID'].', '.$COLUMN['OFFSET_X']
						.', '.$COLUMN['OFFSET_Y'].', '.$COLUMN['DIMENSION']
						.', '.$COLUMN['HUGE_STATUS'].', '.$COLUMN['BIG_STATUS']
						.', '.$COLUMN['MEDIUM_STATUS'].', '.$COLUMN['SMALL_STATUS']
						.', UNIX_TIMESTAMP('.$COLUMN['HUGE_TIMESTAMP'].') AS '.$COLUMN['HUGE_TIMESTAMP']
						.', UNIX_TIMESTAMP('.$COLUMN['BIG_TIMESTAMP'].') AS '.$COLUMN['BIG_TIMESTAMP']
						.', UNIX_TIMESTAMP('.$COLUMN['MEDIUM_TIMESTAMP'].') AS '.$COLUMN['MEDIUM_TIMESTAMP']
						.', UNIX_TIMESTAMP('.$COLUMN['SMALL_TIMESTAMP'].') AS '.$COLUMN['SMALL_TIMESTAMP']
						.', '.$COLUMN['EXIF_MAKE'].', '.$COLUMN['EXIF_MODEL']
						.', '.$COLUMN['EXIF_SOFTWARE'].', '.$COLUMN['EXIF_EXPOSURE_TIME']
						.', '.$COLUMN['EXIF_FNUMBER']
						.', UNIX_TIMESTAMP('.$COLUMN['EXIF_DATE_TIME_ORIGINAL'].') AS '.$COLUMN['EXIF_DATE_TIME_ORIGINAL']
						.', '.$COLUMN['EXIF_FOCAL_LENGTH'].', '.$COLUMN['EXIF_FLASH']
						.', '.$COLUMN['EXIF_ISO']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PICTURE']
						.' WHERE '.$COLUMN['PID'].' = ?'
								, array('integer'));
					break;
				case Picture::statement_setOriginalFid:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['ORIGINAL_FID'], 'text');
					break;
				case Picture::statement_setHugeFid:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['HUGE_FID'], 'text');
					break;
				case Picture::statement_setBigFid:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['BIG_FID'], 'text');
					break;
				case Picture::statement_setMediumFid:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['MEDIUM_FID'], 'text');
					break;
				case Picture::statement_setSmallFid:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['SMALL_FID'], 'text');
					break;
				case Picture::statement_setOffsetX:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['OFFSET_X'], 'integer');
					break;
				case Picture::statement_setOffsetY:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['OFFSET_Y'], 'integer');
					break;
				case Picture::statement_setDimension:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['DIMENSION'], 'integer');
					break;
				case Picture::statement_setHugeStatus:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['HUGE_STATUS'], 'integer');
					break;
				case Picture::statement_setBigStatus:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['BIG_STATUS'], 'integer');
					break;
				case Picture::statement_setMediumStatus:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['MEDIUM_STATUS'], 'integer');
					break;
				case Picture::statement_setSmallStatus:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['SMALL_STATUS'], 'integer');
					break;
				case Picture::statement_setHugeTimestamp:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['HUGE_TIMESTAMP'], 'timestamp');
					break;
				case Picture::statement_setBigTimestamp:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['BIG_TIMESTAMP'], 'timestamp');
					break;
				case Picture::statement_setMediumTimestamp:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['MEDIUM_TIMESTAMP'], 'timestamp');
					break;
				case Picture::statement_setSmallTimestamp:
					Picture::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE'], array($COLUMN['PID'] => 'integer'), $COLUMN['SMALL_TIMESTAMP'], 'timestamp');
					break;
				case Picture::statement_delete:
					Picture::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['PICTURE'].'  WHERE '.$COLUMN['PID']
						.' = ?', array('integer'));
					break;
			}
		}
	}
	
	public function getRealThumbnail($size) {
		global $PICTURE_STATUS;
		global $PICTURE_SIZE;
		
		$fid = $this->getFid($size);
		
		if ($fid === null || $this->getStatus($size) == $PICTURE_STATUS['FIRST'] || $this->getStatus($size) == $PICTURE_STATUS['RAW']) {
			$this->regenerateThumbnail($size);

			// There's a possibility that another process took care of it
			// That's why the data in $this could be stale
			$picture = Picture::get($this->getPid());
			$fid = $picture->getFid($size);
		}

		if ($fid === null) return '';
		$picture_file = PictureFile::get($fid);
		return $picture_file->getURL();
	}
	
	public function delete() {
		global $PICTURE_SIZE;
		
		Picture::prepareStatement(Picture::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		Picture::$statement[Picture::statement_delete]->execute($this->pid);
		Log::trace('DB', 'Executed Picture::statement_delete ['.$this->pid.'] ('.(microtime(true) - $start_timestamp).')');
		
		$picturefiles = PictureFileList::getByPid($this->pid);
		foreach ($picturefiles as $fid) {
			try {
				$picturefile = PictureFile::get($fid);
				$picturefile->delete();
			} catch (PictureFileException $e) {}
		}
		
		try {
			Cache::delete(Picture::cache_prefix.$this->pid);
		} catch (CacheException $e) {}
		
		PictureList::deleteByStatus($PICTURE_SIZE['HUGE'], $this->status[$PICTURE_SIZE['HUGE']]);
		PictureList::deleteByStatus($PICTURE_SIZE['BIG'], $this->status[$PICTURE_SIZE['BIG']]);
		PictureList::deleteByStatus($PICTURE_SIZE['MEDIUM'], $this->status[$PICTURE_SIZE['MEDIUM']]);
		PictureList::deleteByStatus($PICTURE_SIZE['SMALL'], $this->status[$PICTURE_SIZE['SMALL']]);
	}
	
	public function regenerateThumbnail($size, $force=false) {
		global $PICTURE_STATUS;
		global $PICTURE_FILE_STATUS;
		global $PICTURE_LOCAL_PATH;
		global $S3_PATH;
		global $PICTURE_SIZE_DIMENSION_X;
		global $PICTURE_SIZE_DIMENSION_Y;
		global $PICTURE_SIZE;
		
		if ($size != $PICTURE_SIZE['HUGE'] && $this->getFid($PICTURE_SIZE['HUGE']) === null && !$force) $this->regenerateThumbnail($PICTURE_SIZE['HUGE']);
		if ($size == $PICTURE_SIZE['HUGE'] && $this->getFid($PICTURE_SIZE['HUGE']) !== null && !$force) return;
		
		$locked = Cache::lock('ThumbnailGeneration-'.$size.'-'.$this->getPid(), 10);
		
		if (!$locked) {
			Cache::unlock('ThumbnailGeneration-'.$size.'-'.$this->getPid());
			return;
		}
		
		if ($size == $PICTURE_SIZE['HUGE']) {
			$fid = $this->getFid($PICTURE_SIZE['ORIGINAL']);
			$file = PictureFile::get($fid);
			$filename = $fid.'.jpg';
			$reference_offset_x = 0;
			$reference_offset_y = 0;
			$reference_dimension_x = $file->getWidth();
			$reference_dimension_y = $file->getHeight();
		} else {
			$fid = $this->getFid($PICTURE_SIZE['ORIGINAL']);
			$file = PictureFile::get($fid);
			$filename = $fid.'.jpg';
			$reference_offset_x = $this->getOffsetX();
			$reference_offset_y = $this->getOffsetY();
			$reference_dimension_x = $this->getDimension();
			$reference_dimension_y = $reference_dimension_x;
		}

		if ($file->getStatus() == $PICTURE_FILE_STATUS['S3']) {
			URL::download($S3_PATH.$filename, $PICTURE_LOCAL_PATH.$filename);
			$to_delete = true;
		} else $to_delete = false;
		
		if (file_exists($PICTURE_LOCAL_PATH.$filename)) {			
			$old_fid = $this->getFid($size);
		
			if ($size != $PICTURE_SIZE['HUGE'] || ($force || $old_fid === null) ) {
				if ($old_fid !== null) {
					try {
						$old_file = PictureFile::get($old_fid);
						$old_file->delete();
					} catch (PictureFileException $e) {}
				}
				
				$file = new PictureFile($PICTURE_LOCAL_PATH.$filename, $this->getPid(), $reference_offset_x, $reference_offset_y, $reference_dimension_x, $reference_dimension_y, $PICTURE_SIZE_DIMENSION_X[$size], $PICTURE_SIZE_DIMENSION_Y[$size]);
				$this->setFid($size, $file->getFid());
		
				if ($this->getStatus($size) != $PICTURE_STATUS['FIRST'])
					$this->setTimestamp($size, time());
				$this->setStatus($size, $PICTURE_STATUS['THUMBNAILED']);
			}
		} else  {
			Cache::unlock('ThumbnailGeneration-'.$size.'-'.$this->getPid());
			throw new PictureException('Missing source file!');
		}
		
		if ($to_delete) {
			unlink($PICTURE_LOCAL_PATH.$filename);
		}
		
		Cache::unlock('ThumbnailGeneration-'.$size.'-'.$this->getPid());
	}
}

?>