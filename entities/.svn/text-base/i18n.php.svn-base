<?php
    
/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Internationalization support
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/timecounter.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

class Translation {
	private $text;
	private $timestamp;
	private $default;
	private $uid;
	
	public function Translation($new_text, $new_timestamp, $default, $uid) {
		$this->text = $new_text;
		$this->timestamp = $new_timestamp;
		$this->default = $default;
		$this->uid = $uid;
	}
	
	public function getUid() { return $this->uid; }
	public function getText() { return $this->text; }
	public function getTimestamp() { return $this->timestamp; }
	public function isDefault() { return $this->default; }
}

class I18NException extends Exception {}

class I18N {
	const cache_prefix = 'I18N-';
	const cache_prefix_all_names = 'I18N_NAMES-';
	
	const statement_getLatest = 1;
	const statement_create = 2;
	const statement_getAllNames = 3;
	
	private static $statement = array();
	
	public static function translateHTML($user, $html, $force_lid = null) {
		//TimeCounter::start();
	    // Check if there are any translations to handle
	        
	    do {
			preg_match_all("/<\s*translate\s*id\s*=\s*\"([^\"]+)\"\s*(?:escape\s*=\s*\"([^\"]+)\"\s*|)>(.+?)(?!<\s*translate)<\/\s*translate\s*>/ism", $html, $matches);
			
			if (count($matches[1]) > 0) {
				$translate_ids = $matches[1];
				$translate_tags = array();
				$saved_parameters = array();
				foreach ($matches[3] as $translate_tag_key => $translate_tag) {
					$local_saved_parameters = array();
					preg_match_all("/(?:\s+([\w-]+)\s*=\s*\"([^\"]*)\")+/",  $translate_tag, $matchez);
					$chunks = preg_split("/(?:\s+[\w-]+\s*=\s*\"[^\"]*\")+/",  $translate_tag);
					
					if (count($matchez[0]) > 0) {
						$chunk_offset = 0;
						$new_translate_tag = $chunks[$chunk_offset];
						foreach ($matchez[0] as $match) {
							$parameters_id = count($local_saved_parameters);
							$local_saved_parameters[]= $match;
							$new_translate_tag .= ' #'.$parameters_id.$chunks[++$chunk_offset];
						}
						$translate_tags [$translate_ids[$translate_tag_key]]= $new_translate_tag;
						$saved_parameters[$translate_tag_key] = $local_saved_parameters;
					} else $translate_tags [$translate_ids[$translate_tag_key]]= $translate_tag;
				}
				
				//TimeCounter::stop();
				$translations = I18N::getArray($force_lid === null?$user->getLid():$force_lid, array_unique($translate_ids), $translate_tags);
				//TimeCounter::start();

				$processed_translate_tags = array();
				foreach ($translate_ids as $key => $translate_id) {
					if (isset($saved_parameters[$key]))
						$processed_translate_tags[$key] = Template::Templatize($translations[$translate_id]->getText(), $saved_parameters[$key]);
					else 
						$processed_translate_tags[$key] = $translations[$translate_id]->getText();
					if (isset($matches[2][$key]) && strcmp(strtolower($matches[2][$key]), 'js') ==0) 
						$processed_translate_tags[$key] = String::addJSslashes($processed_translate_tags[$key]);
					elseif (isset($matches[2][$key]) && strcmp(strtolower($matches[2][$key]), 'htmlentities') ==0) 
						$processed_translate_tags[$key] = String::htmlentities($processed_translate_tags[$key]);
					elseif (isset($matches[2][$key]) && strcmp(strtolower($matches[2][$key]), 'urlify') ==0) 
						$processed_translate_tags[$key] = String::urlify($processed_translate_tags[$key]);
				}
				
				$chunks = preg_split("/<\s*translate\s*id\s*=\s*\"([^\"]+)\"\s*(?:escape\s*=\s*\"([^\"]+)\"\s*|)>(.+?)<\/\s*translate\s*>/ism", $html);
				$chunk_offset = 0;
				$translated_html = $chunks[0];
				foreach ($processed_translate_tags as $translated_tag) {
					$translated_html .= $translated_tag.$chunks[++$chunk_offset];
				}
				$html = $translated_html;
			} 
		} while (count($matches[1]) > 0);
		
		//TimeCounter::stop();
		
		return $html;
	}
	
	public static function getArray($lid, $names, $texts=null) {
		global $LANGUAGE;
		global $LANGUAGE_SOURCE;
		
		$keys = array();
		$translations = array();
		
		foreach ($names as $name) $keys []= I18N::cache_prefix.$lid.'-'.$name;
		
		$keys = array_unique($keys);

		try {
			$results = Cache::getArray($keys);
			foreach ($results as $key => $translation) {
				$translated = substr($key, strlen(I18N::cache_prefix));
				$translated = explode('-', $translated);
				$name = $translated[1];
				
				$translations[$name] = $translation;
				
				if ($lid == $LANGUAGE['EN'] && isset($texts[$name]) && strcmp(String::stripSpecialEnds($translations[$name]->getText()), String::stripSpecialEnds($texts[$name])) != 0) {
					$translations[$name] = I18N::set($lid, $name, $texts[$name]);
				} elseif ($translation->isDefault()) {
					$original = I18N::getLatest($LANGUAGE_SOURCE[], $name);
					if (isset($texts[$name]) && strcmp(String::stripSpecialEnds($original->getText()), String::stripSpecialEnds($texts[$name])) != 0 && $LANGUAGE_SOURCE[$lid] == $LANGUAGE['EN'])
							I18N::set($LANGUAGE['EN'], $name, $texts[$name]);
				}
			}
			$untranslated_chunks = (array_diff($keys, array_keys($results)));
			
			foreach ($untranslated_chunks as $untranslated) {
				$untranslated = substr($untranslated, strlen(I18N::cache_prefix));
				$untranslated = explode('-', $untranslated);
				$name = $untranslated[1];
				
				try {
					$translation = I18N::getLatest($untranslated[0], $name);
				} catch (I18NException $e) {
					try {
						$translation = I18N::getLatest($LANGUAGE_SOURCE[$lid], $name);
					} catch (I18NException $e) {
						$translation = I18N::set($LANGUAGE['EN'], $name, $texts[$name]);
					}
				}
				
				$translations[$untranslated[1]] = $translation;
			}
		} catch (CacheException $e) {
			foreach ($names as $key => $name) try {
				$translations[$name] = I18N::getLatest($lid, $name);
			} catch (I18NException $e) {}
		}
		
		return $translations;
	}
	
	public static function getLatest($language, $name) {
		global $COLUMN;
		
		Log::trace(__CLASS__, 'retrieving the latest translation with name='.$name.' and language='.$language);
		
		try {
			return Cache::get(I18N::cache_prefix.$language.'-'.$name); 
		} catch (CacheException $e) {
			I18N::prepareStatement(I18N::statement_getLatest);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = I18N::$statement[I18N::statement_getLatest]->execute(array($language, $name));
			Log::trace('DB', 'Executed I18N::statement_getLatest ['.$language.', "'.$name.'"] ('.(microtime(true) - $start_timestamp).')');
			
			if ($result && !PEAR::isError($result) && $row = $result->fetchRow()) {
				$translation = new Translation($row[$COLUMN['TRANSLATION']], $row[$COLUMN['TIMESTAMP']], $row[$COLUMN['IS_DEFAULT']], null);
				try {
					Cache::setorreplace(I18N::cache_prefix.$language.'-'.$name, $translation);
				} catch (CacheException $e) {
					Log::error(__CLASS__, 'missed cache setorreplace on translation with name='.$name.', lid='.$language);
				}
				return $translation;
			}
			else
				throw new I18NException('translation with name='.$name.' and language='.$language.' is not set yet');
		}
	}
	
	public static function set($lid, $name, $text, $default=false, $uid=null) {
		Log::trace(__CLASS__, 'setting the new translation with name='.$name.', lid='.$lid);
		
		try { Cache::delete(I18N::cache_prefix_all_names.$lid); } catch (CacheException $e) {}
		
		I18N::prepareStatement(I18N::statement_create);
		
		$start_timestamp = microtime(true);
		DB::incrementRequestCount();
		$result = I18N::$statement[I18N::statement_create]->execute(array($lid, $name, $text, $default, $uid));
		Log::trace('DB', 'Executed I18N::statement_create ['.$lid.', "'.$name.'", "'.$text.'", '.$default.', '.$uid.'] ('.(microtime(true) - $start_timestamp).')');
		
		if ($result && PEAR::isError($result))
			Log::error(__CLASS__, 'couldn\'t write translation with name='.$name.', lid='.$lid);
		
		$translation = new Translation($text, time(), $default, $uid);
		
		try {
			Cache::setorreplace(I18N::cache_prefix.$lid.'-'.$name, $translation);
		} catch (CacheException $e) {
			Log::error(__CLASS__, 'missed cache setorreplace on translation with name='.$name.', lid='.$lid);
		}
		
		return $translation;
	}
	
	public static function getAllnames($lid) {
		global $COLUMN;
		
		try {
			 $list = Cache::get(I18N::cache_prefix_all_names.$lid);
		} catch (CacheException $e) { 
			I18N::prepareStatement(I18N::statement_getAllNames);
			
			$start_timestamp = microtime(true);
			DB::incrementRequestCount();
			$result = I18N::$statement[I18N::statement_getAllNames]->execute($lid);
			Log::trace('DB', 'Executed I18N::statement_getAllNames ['.$lid.'] ('.(microtime(true) - $start_timestamp).')');
			
			$list = array();
			if ($result && !PEAR::isError($result) && $result->numRows() != 0)
			while ($row = $result->fetchRow()) $list []= $row[$COLUMN['NAME']];
			
			try {
				Cache::setorreplace(I18N::cache_prefix_all_names.$lid, $list);
			} catch (CacheException $e) {}
		}
		
		return $list;
	}
	
	public static function getOutdated($lid) {
		global $LANGUAGE;
		global $LANGUAGE_SOURCE;
		
		$outdated = array();
		
		if ($lid == $LANGUAGE['EN']) return $outdated;
		
		$source_names = I18N::getAllNames($LANGUAGE_SOURCE[$lid]);
		$local_names = I18N::getAllNames($lid);
		
		$translations = I18N::getArray($lid, $local_names);
		$source_translations = I18N::getArray($LANGUAGE_SOURCE[$lid], $source_names);
		
		$outdated = array_merge($outdated, array_diff($source_names, $local_names));
		
		foreach ($local_names as $local_name) {
			$latest = $translations[$local_name];
			if ($latest->isDefault())
				$outdated[]=$local_name;
			else {
				$source_latest = $source_translations[$local_name];
				if ($source_latest->getTimestamp() > $latest->getTimestamp())
					$outdated[]=$local_name;
			}
		}
		$result = array();
		foreach ($outdated as $name) {
			$result[$name] = array($LANGUAGE_SOURCE[$lid] => $source_translations[$name]->getText());
			if (isset($translations[$name])) $result[$name][$lid] = $translations[$name]->getText();
		}
		
		return $result;
	}
	
	private static function prepareStatement($statement) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE;
		
		if (!isset(I18N::$statement[$statement])) {
			Log::trace(__CLASS__, 'Preparing DB statement '.$statement);
			
			switch ($statement) {
				case I18N::statement_getLatest:
					I18N::$statement[$statement] = DB::prepareRead( 
						'SELECT '.$COLUMN['TRANSLATION'].', UNIX_TIMESTAMP('.$COLUMN['TIMESTAMP'].') AS '.$COLUMN['TIMESTAMP']
						.', '.$COLUMN['IS_DEFAULT'].', '.$COLUMN['UID']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['I18N']
						.' WHERE '.$COLUMN['LID'].' = ? AND '.$COLUMN['NAME'].' = ?'
						.' ORDER BY '.$COLUMN['TIMESTAMP'].' DESC LIMIT 0, 1'
								, array('integer', 'text'));
					break;
				case I18N::statement_getAllNames:
					I18N::$statement[$statement] = DB::prepareRead( 
						'SELECT DISTINCT '.$COLUMN['NAME']
						.' FROM '.$DATABASE['PREFIX'].$TABLE['I18N']
						.' USE INDEX('.$COLUMN['LID'].')'
						.' WHERE '.$COLUMN['LID'].' = ?'
								, array('integer'));
					break;
				case I18N::statement_create:
					I18N::$statement[$statement] = DB::prepareWrite( 
						'INSERT INTO '.$DATABASE['PREFIX'].$TABLE['I18N'].' ( '.$COLUMN['LID']
						.', '.$COLUMN['NAME'].', '.$COLUMN['TRANSLATION'].', '.$COLUMN['IS_DEFAULT']
						.', '.$COLUMN['UID']
						.') VALUES(?, ?, ?, ?, ?)', array('integer', 'text', 'text', 'boolean', 'text'));
					break;
			}
		}
	}
	
}

?>