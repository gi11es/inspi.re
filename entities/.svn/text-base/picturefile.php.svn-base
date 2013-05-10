<?php
    
/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Picture file support
*/

require_once(dirname(__FILE__).'/../entities/picturefilelist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/image.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/s3.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

class PictureFileException extends Exception {}

class PictureFile implements Persistent {
	private $fid;
	private $status;
	private $width = 0;
	private $height = 0;
	private $pid;
	public $invalid = false;
	
	const cache_prefix = 'PictureFile-';
	
	const statement_get = 1;
	const statement_setStatus = 2;
	const statement_create = 3;
	const statement_delete = 4;
	const statement_setPid = 5;
	
	private static $statement = array();
	
	public function __construct() {
		$argv = func_get_args();
		switch (func_num_args()) {
			case 1:
				self::__construct2($argv[0]);
				break;
			case 5:
				self::__construct3($argv[0], $argv[1], $argv[2], $argv[3], $argv[4]);
				break;
			case 8:
				self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6], $argv[7]);
				break;
		}
    }
	
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of picture file with fid='.$this->fid);
		
		try {
			Cache::replaceorset(PictureFile::cache_prefix.$this->fid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of picture file with fid='.$this->fid);
		}
	}
	
	public function __construct2($original_file, $pid=null, $offset_x=null, $offset_y=null, $size_x=null, $size_y=null, $dimension_x=null, $dimension_y=null) {
		global $PICTURE_FILE_STATUS;
		global $PICTURE_LOCAL_PATH;
		global $DEV_SERVER;
		
		$info = Image::getInfo($original_file);
		
		if ($info === null)
			$this->invalid = true;
		else {		
			$width = $info['width'];
			$height = $info['height'];
			
			if ($dimension_x !== null && $dimension_y !== null) {
				if ($size_x >= $size_y) {
					$width = $dimension_x;
					$height = intval(floatval($size_y) / (floatval($size_x) / floatval($width)));
					if ($height > $dimension_y) {
						$new_height = $height;
						$height = $dimension_y;
						$width = intval(floatval($width) / (floatval($new_height) / floatval($height)));
					}
				} else if ($size_x < $size_y) {
					$height = $dimension_y;
					$width = intval(floatval($size_x) / (floatval($size_y) / floatval($height)));
					
					if ($width > $dimension_x) {
						$new_width = $width;
						$width = $dimension_x;
						$height = intval(floatval($height) / (floatval($new_width) / floatval($width)));
					}
				}
				
				
			}
			
			if (!$DEV_SERVER) $fid = uniqid();
			else $fid = 'dv_'.uniqid();
			
			PictureFile::prepareStatement(PictureFile::statement_create);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			PictureFile::$statement[PictureFile::statement_create]->execute(array($fid, $pid, $PICTURE_FILE_STATUS['LOCAL'], $width, $height));
			Log::trace('DB', 'Executed PictureFile::statement_create ['.$fid.', '.$pid.', '.$PICTURE_FILE_STATUS['LOCAL']. ', '.$width.', '.$height.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (($offset_x === null && $offset_y === null && $size_x === null && $size_y === null && $dimension_x === null && $dimension_y === null) || ($size_x == $width && $size_y == $height)) {
				$start_time = microtime(true);
				Image::convert($original_file, $PICTURE_LOCAL_PATH.$fid.'.jpg');
	
				Log::trace(__CLASS__, 'Time taken to do plain jpg conversion of the image: '.(microtime(true) - $start_time));
			} else {
				$start_time = microtime(true);
				
				$picture = new Imagick($original_file);
				
				Log::trace(__CLASS__, 'Time taken to load the image: '.(microtime(true) - $start_time));
				
				$picture->stripImage();
				
				Log::trace(__CLASS__, 'Cropping properties: '.$offset_x.' '.$offset_y.' '.$size_x.' '.$size_y);
				if ($size_x != 0 && $size_y != 0 && !($size_x == $width && $size_y == $height)) {
					$start_time = microtime(true);
					$picture->cropImage($size_x, $size_y, $offset_x, $offset_y);
					Log::trace(__CLASS__, 'Time taken to crop the image: '.(microtime(true) - $start_time));
				}
				
				if ($dimension_x !== null && $dimension_y !== null) {
					$picture->setCompression(Imagick::COMPRESSION_JPEG);
					$picture->setCompressionQuality(90);
					$picture->resizeImage($width, $height, Imagick::FILTER_CATROM, 0.8); // best so far: catrom
					Log::trace(__CLASS__, 'Time taken to resize the image: '.(microtime(true) - $start_time));
				}
				
				$start_time = microtime(true);
				$picture->writeImage($PICTURE_LOCAL_PATH.$fid.'.jpg');
				Log::trace(__CLASS__, 'Time taken to write the image: '.(microtime(true) - $start_time));
				$picture->clear();
				$picture->destroy();		
			}
			
			$this->setPid($pid, false);
			$this->setFid($fid);
			$this->setStatus($PICTURE_FILE_STATUS['LOCAL'], false);
			$this->setWidth($width);
			$this->setHeight($height);
			$this->saveCache();
			
			PictureFileList::deleteByStatus($PICTURE_FILE_STATUS['LOCAL']);
			PictureFileList::deleteByPid($pid);
		}
	}
	
	public function __construct3($fid, $pid, $status, $width, $height) {
		$this->fid = $fid;
		$this->pid = $pid;
		$this->status = $status;
		$this->width = $width;
		$this->height = $height;
	}
	
	public function setFid($fid) { $this->fid = $fid; }
	
	public function getFid() { return $this->fid; }
	
	public function setPid($pid, $persist=true) {
		$this->pid = $pid;
		
		if ($persist) {
			PictureFile::prepareStatement(PictureFile::statement_setPid);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			PictureFile::$statement[PictureFile::statement_setPid]->execute(array($this->pid, $this->fid));
			Log::trace('DB', 'Executed PictureFile::statement_setPid ['.$this->pid.', '.$this->fid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			PictureFileList::deleteByPid($pid);
		}
	}
	
	public function getPid() { return $this->pid; }
	
	public function getStatus() { return $this->status; }
	
	public function setStatus($status, $persist=true) {
		$old_status = $this->status;
		$this->status = $status;
		
		if ($persist) {
			PictureFile::prepareStatement(PictureFile::statement_setStatus);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			PictureFile::$statement[PictureFile::statement_setStatus]->execute(array($this->status, $this->fid));
			Log::trace('DB', 'Executed PictureFile::statement_setStatus ['.$this->status.', '.$this->fid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$this->saveCache();
			
			PictureFileList::deleteByStatus($old_status);
			PictureFileList::deleteByStatus($status);
		}
	}
	
	public function getWidth() { return $this->width; }
	
	public function setWidth($width) { $this->width = $width; }
	
	public function getHeight() { return $this->height; }
	
	public function setHeight($height) { $this->height = $height; }
	
	public function getURL() {
		global $PICTURE_FILE_STATUS;
		global $PICTURE_PATH;
		global $S3_PATH;
		
		$last_modified = @filemtime($this->getLocalURL());
		return ($this->getStatus() == $PICTURE_FILE_STATUS['LOCAL']?$PICTURE_PATH:$S3_PATH).$this->getFid().'-'.$last_modified.'.jpg';
	}
	
	public function getLocalURL() {
		global $PICTURE_LOCAL_PATH;
		
		return $PICTURE_LOCAL_PATH.$this->getFid().'.jpg';
	}
	
	public static function get($fid) {
		global $COLUMN;
		
		try {
			return Cache::get(PictureFile::cache_prefix.$fid);
		} catch (CacheException $e) {
			PictureFile::prepareStatement(PictureFile::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = PictureFile::$statement[PictureFile::statement_get]->execute($fid);
			Log::trace('DB', 'Executed PictureFile::statement_get ['.$fid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if ($result && !PEAR::isError($result) && $result->numRows() != 0) {
				$row = $result->fetchRow();
				$picfile = new PictureFile($fid, $row[$COLUMN['PID']], $row[$COLUMN['STATUS']], $row[$COLUMN['WIDTH']], $row[$COLUMN['HEIGHT']]);
				try {
					Cache::setorreplace(PictureFile::cache_prefix.$fid, $picfile);
				} catch (CacheException $e) {
					Log::critical(__CLASS__, 'Couldn\'t set cache entry for picture file with fid='.$fid);
				}
				return $picfile;
			} else {
				Log::critical(__CLASS__, 'No picture file for fid = '.$fid);
				throw new PictureFileException('No picture file for fid = '.$fid);
			}
		}
	}
	
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(PictureFile::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case PictureFile::statement_create:
					PictureFile::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['PICTURE_FILE'].'( '
						.$COLUMN['FID'].', '.$COLUMN['PID'].', '.$COLUMN['STATUS']
						.', '.$COLUMN['WIDTH'].', '.$COLUMN['HEIGHT']
						.') VALUES(?, ?, ?, ?, ?)', array('text', 'integer', 'integer', 'integer'));
					break;
				case PictureFile::statement_get:
					PictureFile::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['STATUS'].', '.$COLUMN['WIDTH'].', '.$COLUMN['HEIGHT'].', '.$COLUMN['PID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['PICTURE_FILE']
						.' WHERE '.$COLUMN['FID'].' = ?'
								, array('text'));
					break;
				case PictureFile::statement_setStatus:
					PictureFile::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE_FILE'], array($COLUMN['FID'] => 'text'), $COLUMN['STATUS'], 'integer');
					break;
				case PictureFile::statement_delete:
					PictureFile::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['PICTURE_FILE'].'  WHERE '.$COLUMN['FID']
						.' = ?', array('text'));
					break;
				case PictureFile::statement_setPid:
					PictureFile::$statement[$statement] = DB::prepareSetter($TABLE['PICTURE_FILE'], array($COLUMN['FID'] => 'text'), $COLUMN['PID'], 'integer');
					break;
			}
		}
	}
	
	public function delete() {
		global $S3_BUCKET;
		global $PICTURE_FILE_STATUS;
		global $PICTURE_LOCAL_PATH;
		global $DEV_SERVER;
		
		$fid = $this->getFid();
		
		if (!$DEV_SERVER) {
			if ($this->getStatus() == $PICTURE_FILE_STATUS['S3'])
				S3::delete($S3_BUCKET['IMAGES'], $fid.'.jpg');
			elseif (file_exists($PICTURE_LOCAL_PATH.$fid.'.jpg'))
				unlink($PICTURE_LOCAL_PATH.$fid.'.jpg');
		}
			
		PictureFile::prepareStatement(PictureFile::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		PictureFile::$statement[PictureFile::statement_delete]->execute($fid);
		Log::trace('DB', 'Executed PictureFile::statement_delete ['.$fid.'] ('.(microtime(true) - $start_timestamp).')');
		
		try {
			Cache::delete(PictureFile::cache_prefix.$fid);
		} catch (CacheException $e) {}
		
		PictureFileList::deleteByStatus($this->getStatus());
		PictureFileList::deleteByPid($this->pid);
	}
}

?>