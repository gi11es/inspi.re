<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/communitylabellist.php');
require_once(dirname(__FILE__).'/../entities/persistent.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../constants.php');

class CommunityLabelException extends Exception {}

class CommunityLabel implements Persistent {
	private $xid;
	private $clid;
	
	private static $statement = array();
	
	const statement_create = 1;
	const statement_get = 2;
	const statement_delete = 3;
	
    const cache_prefix = 'CommunityLabel-';
	
	// Saves the current instance into the cache
	public function saveCache() {
		Log::trace(__CLASS__, 'inserting/updating cache entry of community_label with xid='.$this->xid.' and clid='.$this->clid);
		
		try {
			Cache::replaceorset(CommunityLabel::cache_prefix.$this->xid.'-'.$this->clid, $this);
		} catch (CacheException $ex) {
			Log::critical(__CLASS__, 'could not insert/update cache entry of community_label with xid='.$this->xid.' and clid='.$this->clid);
		}
	}
	
	public function __construct() {
		$argv = func_get_args();
		if (func_num_args() == 2)
			self::__construct2($argv[0], $argv[1]);
    }
	
	public function __construct2($xid, $clid) {
		CommunityLabel::prepareStatement(CommunityLabel::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		CommunityLabel::$statement[CommunityLabel::statement_create]->execute(array($xid, $clid));
		Log::trace('DB', 'Executed CommunityLabel::statement_create ['.$xid.', '.$clid.'], ('.(microtime(true) - $start_timestamp).')');

		$this->setXid($xid);
		$this->setCLid($clid, false);
		$this->saveCache();
		
		CommunityLabelList::deleteByXid($xid);
		CommunityLabelList::deleteByCLid($clid);
	}
	
	public static function get($xid, $clid) {
		if ($xid === null) throw new CommunityLabelException('No community label for that xid: '.$xid.' and clid='.$clid);
		
		try {
			$community_label = Cache::get(CommunityLabel::cache_prefix.$xid.'-'.$clid);
		} catch (CacheException $e) {
			CommunityLabel::prepareStatement(CommunityLabel::statement_get);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = CommunityLabel::$statement[CommunityLabel::statement_get]->execute(array($xid, $clid));
			Log::trace('DB', 'Executed CommunityLabel::statement_get ['.$xid.', '.$clid.'] ('.(microtime(true) - $start_timestamp).')');
			
			if (!$result || PEAR::isError($result) || $result->numRows() != 1) 
				throw new CommunityLabelException('No community label for that xid: '.$xid.' and clid='.$clid);
			
			$row = $result->fetchRow();
			$result->free();
			
			$community_label = new CommunityLabel();
			$community_label->populateFields($row);
			$community_label->saveCache();
		}
		return $community_label;
	}
	
	public function populateFields($row) {
		global $COLUMN;
		
		$this->setXid($row[$COLUMN['XID']]);
		$this->setCLid($row[$COLUMN['CLID']]);
	}
	
	public function delete() {
		CommunityLabel::prepareStatement(CommunityLabel::statement_delete);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = CommunityLabel::$statement[CommunityLabel::statement_delete]->execute(array($this->xid, $this->clid));
		Log::trace('DB', 'Executed CommunityLabel::statement_delete ['.$this->xid.', '.$this->clid.'] ('.(microtime(true) - $start_timestamp).')');

		try { Cache::delete(CommunityLabel::cache_prefix.$this->xid.'-'.$this->clid); } catch (CacheException $e) {}
		
		// Remove from associated lists
		
		CommunityLabelList::deleteByXid($this->xid);
		CommunityLabelList::deleteByCLid($this->clid);
	}
	
	public function getXid() { return $this->xid; }
	
	public function setXid($new_xid) { $this->xid = $new_xid; }
	
	public function getCLid() { return $this->clid; }
	
	public function setCLid($new_clid) { $this->clid = $new_clid;	}
		
	public static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(CommunityLabel::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case CommunityLabel::statement_get:
					CommunityLabel::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['XID'].', '.$COLUMN['CLID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_LABEL']
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['CLID'].' = ?'
								, array('integer', 'integer'));
					break;
				case CommunityLabel::statement_create:
					CommunityLabel::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_LABEL']
						.'( '.$COLUMN['XID'].', '.$COLUMN['CLID']
						.') VALUES(?, ?)', array('integer', 'integer'));
					break;	
				case CommunityLabel::statement_delete:
					CommunityLabel::$statement[$statement] = DB::prepareWrite( 
						'DELETE FROM '.$DATABASE['PREFIX'].$TABLE['COMMUNITY_LABEL']
						.' WHERE '.$COLUMN['XID'].' = ? AND '.$COLUMN['CLID'].' = ?'
						, array('integer', 'integer'));
					break;
			}
		}
	}
}

?>