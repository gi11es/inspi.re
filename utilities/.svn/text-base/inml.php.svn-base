<?php
    
/* 
 	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Templating functions
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/alertvariablelist.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitylist.php');
require_once(dirname(__FILE__).'/../entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/timecounter.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

class Inml {
	private static $cache = array();

	public static function processHTML($user, $html) {
		$current_html = $html;
		
		$tag_names = array('rank', 'location', 'ad', 'uppercase', 'lowercase', 'user_name', 'picture', 'time_since', 'integer', 'string', 'float', 'gmt_time', 'theme_description', 'theme_title', 'language_name', 'community_name', 'duration', 'profile_picture', 'thread_title', 'alert');
		
		do {
			$processed = 0;
			
			$result = Inml::processTags($user, $current_html, $tag_names);
			$current_html = $result['html'];
			$processed += $result['processed'];
		} while ($processed > 0);
		
		return $current_html;
	}
	
	private static function error($text) {
		return '<span class="inml_error">INML parsing error, '.$text.'</span>';
	}
	
	private static function processTags($user, $html, $tag_names) {

		$tags = Inml::getTags($html);

		if (isset($tags['tags'])) {
			$new_html = $tags['split_html'][0];
			$current_chunk = 1;
			$processed_chunk = 1;
			
			$cacherequestlist = array();
			foreach ($tags['tags'] as $tag) {
				if (isset($tag['parameters']['uid'])) {
					$cacherequestlist []= 'User-'.$tag['parameters']['uid'];
					$cacherequestlist []= 'UserLevelListByUid-'.$tag['parameters']['uid'];
				}
				
				if (isset($tag['parameters']['pid']))
					$cacherequestlist []= 'Picture-'.$tag['parameters']['pid'];
				
				if (isset($tag['parameters']['xid']))
					$cacherequestlist []= 'Community-'.$tag['parameters']['xid'];
					
				if (isset($tag['parameters']['tid']))
					$cacherequestlist []= 'Theme-'.$tag['parameters']['tid'];
					
				if (isset($tag['parameters']['nid']))
					$cacherequestlist []= 'DiscussionThread-'.$tag['parameters']['nid'];
					
				if (isset($tag['parameters']['aid'])) {
					$cacherequestlist []= 'Alert-'.$tag['parameters']['aid'];
					$cacherequestlist []= 'AlertVariableListByAid-'.$tag['parameters']['aid'];
				}					
			}
			
			$cacherequestlist = array_unique($cacherequestlist);
			
			Inml::$cache = Cache::getArray($cacherequestlist);
			
			foreach ($tags['tags'] as $tag) {
				if (is_callable(array('Inml', strtolower($tag['tag'])))) {
					$new_html .= call_user_func_array(array('Inml', strtolower($tag['tag'])), array($user, $tag['parameters']));
					$processed_chunk++;
				} else
					$new_html .= $tag['original'];
					
				$new_html .= $tags['split_html'][$current_chunk];
				$current_chunk++;
			}
			return array('html' => $new_html, 'processed' => ($processed_chunk - 1));
		} else return array('html' => $html, 'processed' => 0);
	}
	
	private static function getTags($html) {
		$result = array();
		$result['split_html'] = preg_split("/<\s*([\w_]+)\s*([\w-]+\s*=\s*\"[^\"]*\"\s*)*\/\s*>/ism", $html);
		preg_match_all("/<\s*([\w_]+)\s*(([\w-]+\s*=\s*\"[^\"]*\"\s*)*)\/\s*>/ism", $html, $matches);

		$matches_originals = $matches[0];
		$matches_tagnames = $matches[1];
		$matches_parameters = $matches[2];
		
		$result['tags'] = array();
		
		if (isset($matches_tagnames)) {
			foreach ($matches_tagnames as $index => $value) {
				preg_match_all("/\s*([\w-]+)\s*=\s*\"([^\"]*)\"/im", $matches_parameters[$index], $matchez);
				
				$parameters = array();
				if (isset($matchez[1])) {
					foreach ($matchez[1] as $key2 => $value2) {
						$parameters[$value2] = $matchez[2][$key2];
					}
				}
			
				$result['tags'][$index] = array('tag' => $value, 'original' => $matches_originals[$index], 'parameters' => $parameters);
			}
		}
		
		return $result;
	}
	
	public static function time_since($user, $parameters) {
		return Inml::duration($user, $parameters);
	}
	
	public static function duration($user, $parameters) {
		if (!isset($parameters['value'])) return Inml::error('value parameter missing on a duration tag, it must be specified');
		
		return String::duration($parameters['value']);
	}
	
	public static function integer($user, $parameters) {
		if (!isset($parameters['value'])) return Inml::error('value parameter missing on an integer tag, it must be specified');
		$id = isset($parameters['id'])?'id="'.$parameters['id'].'"':null;
		$class = isset($parameters['class'])?'class="'.$parameters['class'].'"':null;
		
		if ($class !== null || $id !== null)
			return '<span '.$id.' '.$class.'>'.intval($parameters['value']).'</span>';
		else
			return intval($parameters['value']);
	}
	
	public static function rank($user, $parameters) {
		global $LANGUAGE;
		
		if (!isset($parameters['value'])) return Inml::error('value parameter missing on a rank tag, it must be specified');
		$id = isset($parameters['id'])?'id="'.$parameters['id'].'"':null;
		$class = isset($parameters['class'])?'class="'.$parameters['class'].'"':null;
		
		if ($user->getLid() == $LANGUAGE['EN']) {
			$value = intval($parameters['value']);
			$last_digit = $value % 10;
			$last_two_digits = $value % 100;
			
			if ($last_digit == 1 && $last_two_digits != 11)
				$value .= 'st';
			elseif ($last_digit == 2 && $last_two_digits != 12)
				$value .= 'nd';
			elseif ($last_digit == 3 && $last_two_digits != 13)
				$value .= 'rd';
			else $value .= 'th';
		} elseif ($user->getLid() == $LANGUAGE['FR']) {
			$value = intval($parameters['value']);
			if ($value == 1)
				$value .= 'ère';
			else
				$value .= 'ème';
		} elseif ($user->getLid() == $LANGUAGE['DE'] || $user->getLid() == $LANGUAGE['FI']) {
			$value = intval($parameters['value']).'.';
		} elseif ($user->getLid() == $LANGUAGE['ES']) {
			$value = intval($parameters['value']);
			$last_digit = $value % 10;
			if ($last_digit == 1 || $last_digit == 3) {
				$value .= 'ro';
			} elseif ($last_digit == 4 || $last_digit == 5 || $last_digit == 6) {
				$value .= 'to';
			} elseif ($last_digit == 2) {
				$value .= 'do';
			} elseif ($last_digit == 7 || $last_digit == 0) {
				$value .= 'mo';
			} elseif ($last_digit == 8) {
				$value .= 'vo';
			} else {
				$value .= 'no';
			}
		} else $value = intval($parameters['value']);
		
		if ($class !== null || $id !== null)
			return '<span '.$id.' '.$class.'>'.$value.'</span>';
		else
			return $value;
	}
	
	public static function float($user, $parameters) {
		if (!isset($parameters['value'])) return Inml::error('value parameter missing on a float tag, it must be specified');
		
		return floatval($parameters['value']);
	}
	
	public static function string($user, $parameters) {
		if (!isset($parameters['value'])) return Inml::error('value parameter missing on a string tag, it must be specified');
		
		return $parameters['value'];
	}
	
	public static function gmt_time($user, $parameters) {
		if (!isset($parameters['timestamp'])) return Inml::error('timestamp parameter missing on a gmt_time tag, it must be specified');
		
		return gmdate('H:i', $parameters['timestamp']);
	}
	
	public static function ad($user, $parameters) {
		global $AD_CODE;
		global $USER_LEVEL;
		global $USER_STATUS;
		global $ADSENSE_CODE;
		global $COMMUNITY_STATUS;
		global $PAGE;
		global $REQUEST;
		
		$levels = UserLevelList::getByUid($user->getUid());
		$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
		
		if (!isset($parameters['ad_id'])) return Inml::error('ad_id parameter missing on an ad tag, it must be specified');

		if ($ispremium && $user->getHideAds() && strcasecmp($parameters['ad_id'], 'PREMIUM') != 0) return '';
		
		$id = isset($parameters['id'])?'id="'.$parameters['id'].'"':'';
		$class = isset($parameters['class'])?'class="'.$parameters['class'].'"':'class="ad_centered"';
			
		$result = '</div> <!-- content_container -->';
		$result .= '<div class="ad_container">';
		
		$shown = false;
		
		if (isset($AD_CODE[$parameters['ad_id']])) {
			$adcode = $AD_CODE[$parameters['ad_id']];
		} else {
			$adcode = $AD_CODE['LEADERBOARD'];
		}
		
		$probability = $adcode['PROBABILITY'];
		
		if ($user->getLazy()) $probability = $probability * 2;
		
		if (rand(1,10) > $probability) {
			// Internal community promotion
			$totalcommunitylist = array();
				
			$premiumlist = UserLevelList::getByLevel($USER_LEVEL['PREMIUM']);
				
			foreach ($premiumlist as $premiumuid) {
				$communitylist = CommunityList::getByUidAndStatus($premiumuid, $COMMUNITY_STATUS['ACTIVE']);
				$communitylist = array_merge($communitylist, CommunityList::getByUidAndStatus($premiumuid, $COMMUNITY_STATUS['INACTIVE']));
				if (count($communitylist) > 1) {
					shuffle($communitylist);
					$totalcommunitylist[]= array_pop($communitylist);
				} else $totalcommunitylist = array_merge($totalcommunitylist, $communitylist);
			}
			
			$membershiplist = array_keys(CommunityMembershipList::getByUid($user->getUid()));
			
			// Pick a community at random among this user's
			do {
				shuffle($totalcommunitylist);
				$xid = array_pop($totalcommunitylist);
				try {
					$community = Community::get($xid);
					if ($community->getLid() == $user->getLid() && !in_array($xid, $membershiplist) ) $totalcommunitylist = array();
				} catch (CommunityException $e) {
					$totalcommunitylist = array();
				}
			} while (!empty($totalcommunitylist));
			
			if (!in_array($xid, $membershiplist) || $probability == 0) {
				$result .= '<div class="community_ad">';
				$result .= '<picture class="community_ad_picture" href="'.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&amp;xid='.$xid.'" category="community" class="listing_thumbnail" size="medium" '.($community->getPid() === null?'':'pid="'.$community->getPid().'"').' />';
				$result .= '<div class="community_ad_body">';
				$result .= '<a class="community_ad_body_title" href="'.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&amp;xid='.$xid.'">'.String::fromaform($community->getName()).'</a>';
				$result .= '<div class="community_ad_body_description">';
				$result .= String::fromaform($community->getDescription());
				$result .= '</div> <!-- community_ad_body_description -->';
				$result .= '<div class="community_ad_join_link_container"><a class="community_ad_join_link" href="'.$REQUEST['JOIN_COMMUNITY'].'?xid='.$xid.'">';
				$result .= '<translate id="INML_AD_JOIN_COMMUNITY">';
				$result .= 'Join this community';
				$result .= '</translate>';
				$result .='</a></div>';
				$result .= '</div> <!-- community_ad_body -->';
				$result .= '</div> <!-- community_ad -->';
				$shown = true;
			}
		}
		
		if (!$shown) {
			if ($user->getStatus() == $USER_STATUS['ACTIVE'] && !$ispremium)
				$result .= '<input type="hidden" id="var_check_block" value="true"/>'."\r\n";
		
			$result .= '<div '.$class.' '.$id.' style="margin: 0 auto; width: '.$adcode['WIDTH'].'px; height: '.$adcode['HEIGHT'].'px;">'."\r\n";
			$result .= '<script type="text/javascript"><!--'."\r\n";
			$result .= 'google_ad_client = "'.$ADSENSE_CODE.'";'."\r\n";
			$result .= '/* inspi.re-COMMUNITIES */'."\r\n";
			$result .= 'google_ad_slot = "'.$adcode['ID'].'";'."\r\n";
			$result .= 'google_ad_width = '.$adcode['WIDTH'].';'."\r\n";
			$result .= 'google_ad_height = '.$adcode['HEIGHT'].';'."\r\n";
			$result .= '//-->'."\r\n";
			$result .= '</script>'."\r\n";
			$result .= '<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">'."\r\n";
			$result .= '</script>';		
			$result .= '</div>';
		}
		
		$result .= '</div> <!-- ad_container -->';
		$result .= '<div class="content_container">';
		
		return $result;
	}
	
	public static function language_name($user, $parameters) {
		global $LANGUAGE_NAME_FROM_ID;
		
		if (!isset($parameters['lid'])) return Inml::error('lid parameter missing on a language_name tag, it must be specified');
		
		return '<translate id="INML_LANGUAGE_NAME_'.$parameters['lid'].'">'.$LANGUAGE_NAME_FROM_ID[$parameters['lid']].'</translate>';
	}
	
	public static function profile_picture($user, $parameters) {
		global $PICTURE_SIZE;
		global $PAGE;
		global $GRAPHICS_PATH;
		global $USER_STATUS;
		global $USER_LEVEL;
				
		$uid = isset($parameters['uid'])?$parameters['uid']:false;
		$id = isset($parameters['id'])?'id="'.$parameters['id'].'"':'';
		$class = isset($parameters['class'])?$parameters['class']:'';
		$href = isset($parameters['href'])?$parameters['href']:UI::RenderUserLink($uid);
		$rounded = isset($parameters['rounded'])?strcasecmp('true', $parameters['rounded']) == 0:true;
		
		$size = $PICTURE_SIZE['SMALL'];
		$sizeclass = '';
		
		$tiny = false;
		$nobody = false;
		
		if (isset($parameters['size'])) {
			if (strcasecmp($parameters['size'], 'original') == 0)
				$size = $PICTURE_SIZE['ORIGINAL'];
			elseif (strcasecmp($parameters['size'], 'big') == 0) {
				$size = $PICTURE_SIZE['BIG'];
				$sizeclass = 'picture_big';
			} elseif (strcasecmp($parameters['size'], 'medium') == 0) {
				$size = $PICTURE_SIZE['MEDIUM'];
				$sizeclass = 'picture_medium';
			} elseif (strcasecmp($parameters['size'], 'huge') == 0) {
				$size = $PICTURE_SIZE['HUGE'];
			} elseif (strcasecmp($parameters['size'], 'small') == 0) {
				$size = $PICTURE_SIZE['SMALL'];
				$sizeclass = 'picture_small';
			} elseif (strcasecmp($parameters['size'], 'tiny') == 0) {
				$size = $PICTURE_SIZE['SMALL'];
				$sizeclass = 'picture_tiny';
				$tiny = true;
			}
		}
		
		if (!$uid) {
			$pid = null;
			$ispremium = false;
			$title = '<translate id="INML_AUTHOR_USER">The author</translate>';
			$nobody = true;
		} else try {
			if (isset(Inml::$cache['User-'.$uid])) $displayed_user = Inml::$cache['User-'.$uid];
			else $displayed_user = User::get($uid);
			
			if (isset(Inml::$cache['UserLevelListByUid-'.$uid])) $levels = Inml::$cache['UserLevelListByUid-'.$uid];
			else $levels = UserLevelList::getByUid($uid);
			
			$ispremium  = in_array($USER_LEVEL['PREMIUM'], $levels);
			
			$title = String::htmlentities($displayed_user->getUniqueName());
			$pid = $displayed_user->getPid();
		} catch (UserException $e) {
			$ispremium = false;
			$title = '<translate id="INML_UNKNOWN_USER">A former member</translate>';
			$pid = null;
			$nobody = true;
		}
		
		$default = true;
		
		if ($pid === null) $default = true; else try {
			$picture = Picture::get($pid);
			$picture_path = $picture->getRealThumbnail($size);
			$default = false;
		} catch (PictureException $e) {
			$default = true;
		}  catch (PictureFileException $e) {
			$default = true;
		}
		
		if ($default) switch ($size) {
				case $PICTURE_SIZE['ORIGINAL']:
					$picture_path = $GRAPHICS_PATH.'default-user-picture-original.png';
					break;
				case $PICTURE_SIZE['HUGE']:
					$picture_path = $GRAPHICS_PATH.'default-user-picture-huge.png';
					break;
				case $PICTURE_SIZE['BIG']:
					$picture_path = $GRAPHICS_PATH.'default-user-picture-big.png';
					break;
				case $PICTURE_SIZE['MEDIUM']:
					$picture_path = $GRAPHICS_PATH.'default-user-picture-medium.png';
					break;
				case $PICTURE_SIZE['SMALL']:
					$picture_path = $GRAPHICS_PATH.'default-user-picture-small.gif';
					break;
				default:
					$picture_path = $GRAPHICS_PATH.'default-user-picture-small.gif';
					break;
			}
		
		if ($ispremium && ($size == $PICTURE_SIZE['BIG'] || $size ==  $PICTURE_SIZE['MEDIUM'] || $size ==  $PICTURE_SIZE['SMALL'])) {
			$result = '<div '.$id.' class="'.$class.' '.$sizeclass.'">';
			$result .= '<div style="position:relative">';
			$result .= '<img class="'.$sizeclass.'" style="border-style: none;  '.($tiny?'width: 32px; height: 32px;':'').'" src="'.$picture_path.'" alt="Profile picture mask"/>';
			if (!$nobody) {
				$result .= '<a title="'.$title.'" href="'.$href.'">';
			}
			$picture_path = '';
			
			switch ($size) {
				case $PICTURE_SIZE['BIG']:
					$picture_path = $GRAPHICS_PATH.'star-user-picture-big.png';
					$rounded_picture_path = $GRAPHICS_PATH.'rounded-big.png';
					break;
				case $PICTURE_SIZE['MEDIUM']:
					$picture_path = $GRAPHICS_PATH.'star-user-picture-medium.png';
					$rounded_picture_path = $GRAPHICS_PATH.'rounded-medium.png';
					break;
				case $PICTURE_SIZE['SMALL']:
					if ($tiny) {
						$picture_path = $GRAPHICS_PATH.'star-user-picture-tiny.png';
						$rounded_picture_path = $GRAPHICS_PATH.'rounded-tiny.png';
					} else {
						$picture_path = $GRAPHICS_PATH.'star-user-picture-small.png';
						$rounded_picture_path = $GRAPHICS_PATH.'rounded-small.png';
					}
					break;
			}

			if ($rounded) $result .= '<img alt="'.$title.'" title="'.$title.'" style="border-style: none; position:relative; position: absolute; left: 0;  '.($tiny?'width: 32px; height: 32px;':'').'" src="'.$rounded_picture_path.'"/>';
			
			$result .= '<img alt="'.$title.'" class="'.$sizeclass.'" title="'.$title.'" style="border-style: none; position:relative; position: absolute; left: 0;  '.($tiny?'width: 32px; height: 32px;':'').'" src="'.$picture_path.'"/>';
			if (!$nobody) {
				$result .= '</a>';
			}
			$result .= '</div>';
			$result .= '</div>';
		} else {
			switch ($size) {
				case $PICTURE_SIZE['BIG']:
					$rounded_picture_path = $GRAPHICS_PATH.'rounded-big.png';
					break;
				case $PICTURE_SIZE['MEDIUM']:
					$rounded_picture_path = $GRAPHICS_PATH.'rounded-medium.png';
					break;
				case $PICTURE_SIZE['SMALL']:
					$rounded_picture_path = $GRAPHICS_PATH.'rounded-small.png';
					break;
			}
			
			$result = '<div '.$id.' class="'.$class.' '.$sizeclass.'">';
			if ($rounded) {
				$result .= '<div style="position:relative">';
				$result .= '<img alt="'.$title.'" class="'.$sizeclass.'" title="'.$title.'" style="border-style: none;  '.($tiny?'width: 32px; height: 32px;':'').'" src="'.$picture_path.'"/>';
				if (!$nobody) {
					$result .= '<a title="'.$title.'" href="'.$href.'">';
				}
				$result .= '<img alt="'.$title.'" title="'.$title.'" style="border-style: none; position:relative; position: absolute; left: 0;  '.($tiny?'width: 32px; height: 32px;':'').'" src="'.$rounded_picture_path.'"/>';
				if (!$nobody) {
					$result .= '</a>';
				}
				$result .= '</div>';
			} else {
				if (!$nobody) {
					$result .= '<a title="'.$title.'" href="'.$href.'">';
				}
				$result .= '<img alt="'.$title.'" class="'.$sizeclass.'" title="'.$title.'" style="border-style: none; '.($tiny?'width: 32px; height: 32px;':'').'" src="'.$picture_path.'"/>';
				if (!$nobody) {
					$result .= '</a>';
				}
			}
			$result .= '</div>';
		}
		
		
		return $result;
	}
	
	public static function picture($user, $parameters) {
		global $PICTURE_SIZE;
		global $GRAPHICS_PATH;
		global $PICTURE_CATEGORY;
		global $PAGE;
		
		$category = isset($parameters['category'])?$PICTURE_CATEGORY[strtoupper($parameters['category'])]:false;
		$id = isset($parameters['id'])?$parameters['id']:false;
		$class = isset($parameters['class'])?$parameters['class']:'';
		$pid = isset($parameters['pid'])?$parameters['pid']:null;
		$href = isset($parameters['href'])?$parameters['href']:false;
		$title = isset($parameters['title'])?$parameters['title']:false;
		
		$size = $PICTURE_SIZE['SMALL'];
		$sizeclass = '';
		
		if (isset($parameters['size'])) {
			if (strcasecmp($parameters['size'], 'original') == 0) {
				$size = $PICTURE_SIZE['ORIGINAL'];
			} elseif (strcasecmp($parameters['size'], 'big') == 0) {
				$size = $PICTURE_SIZE['BIG'];
				$sizeclass = 'picture_big';
			} elseif (strcasecmp($parameters['size'], 'medium') == 0) {
				$size = $PICTURE_SIZE['MEDIUM'];
				$sizeclass = 'picture_medium';
			} elseif (strcasecmp($parameters['size'], 'huge') == 0) {
				$size = $PICTURE_SIZE['HUGE'];
			} elseif (strcasecmp($parameters['size'], 'small') == 0) {
				$size = $PICTURE_SIZE['SMALL'];
				$sizeclass = 'picture_small';
			}
		}
		
		$default = true;
		
		if ($pid == null) $default = true; else try {
			if (isset(Inml::$cache['Picture-'.$pid])) $picture = Inml::$cache['Picture-'.$pid];
			else $picture = Picture::get($pid);
			
			$picture_path = $picture->getRealThumbnail($size);
			$default = false;
		} catch (PictureException $e) {
			$default = true;
		} catch (PictureFileException $f) {
			$default = true;
		}
		
		if ($default) switch ($size) {
				case $PICTURE_SIZE['ORIGINAL']:
					if ($category == $PICTURE_CATEGORY['PROFILE'])
						$picture_path = $GRAPHICS_PATH.'default-user-picture-original.png';
					else $picture_path = $GRAPHICS_PATH.'default-community-picture-big.png';
					break;
				case $PICTURE_SIZE['HUGE']:
					if ($category == $PICTURE_CATEGORY['PROFILE'])
						$picture_path = $GRAPHICS_PATH.'default-user-picture-huge.png';
					else $picture_path = $GRAPHICS_PATH.'default-community-picture-big.png';
					break;
				case $PICTURE_SIZE['BIG']:
					if ($category == $PICTURE_CATEGORY['PROFILE'])
						$picture_path = $GRAPHICS_PATH.'default-user-picture-big.png';
					else $picture_path = $GRAPHICS_PATH.'default-community-picture-big.png';
					break;
				case $PICTURE_SIZE['MEDIUM']:
					if ($category == $PICTURE_CATEGORY['PROFILE'])
						$picture_path = $GRAPHICS_PATH.'default-user-picture-medium.png';
					else $picture_path = $GRAPHICS_PATH.'default-community-picture-medium.png';
					break;
				case $PICTURE_SIZE['SMALL']:
					if ($category == $PICTURE_CATEGORY['PROFILE'])
						$picture_path = $GRAPHICS_PATH.'default-user-picture-small.gif';
					else $picture_path = $GRAPHICS_PATH.'default-community-picture-small.png';
					break;
				default:
					if ($category == $PICTURE_CATEGORY['PROFILE'])
						$picture_path = $GRAPHICS_PATH.'default-user-picture-small.gif';
					else $picture_path = $GRAPHICS_PATH.'default-community-picture-small.png';
					break;
			}

		if ($href) return '<div '.($id?'id="'.$id.'"':' ').'class="'.$class.' '.$sizeclass.'"'.'><a '.($title?'title="'.$title.'"':'').' href="'.$href.'"><img alt="photo competition entry" style="border-style: none;" src="'.$picture_path.'"/></a></div>';		
		else return '<img alt="photo competition entry" '.($id?'id="'.$id.'"':' ').'class="'.$class.' '.$sizeclass.'"'.'src="'.$picture_path.'"/>';
	}
	
	public static function community_name($user, $parameters) {
		global $PAGE;
		global $COMMUNITY_STATUS;
		
		if (!isset($parameters['xid'])) return Inml::error('xid parameter missing on a community_name tag, it must be specified');
		$id = isset($parameters['id'])?$parameters['id']:false;
		$link = isset($parameters['link'])?strcasecmp($parameters['link'], 'true') == 0:false;
		$href = isset($parameters['href'])?$parameters['href']:false;
		$class = isset($parameters['class'])?$parameters['class']:'inml_community_name';
		$xid = $parameters['xid'];
		
		try {
			if (isset(Inml::$cache['Community-'.$xid])) $community = Inml::$cache['Community-'.$xid];
			else $community = Community::get($xid);
			
			$name = $community->getName();
			if ($xid == 267) {
				$name = '<translate id="PRIZE_COMMUNITY_NAME">'.$community->getName().'</translate>';
				$name = I18N::translateHTML($user, $name);
			}	
		} catch (CommunityException $e) {
			Log::critical(__CLASS__, 'xid='.$xid.' doesn\'t correspond to any existing community');
			return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><translate id="INML_UNKNOWN_COMMUNITY">unknown community</translate></span>';
		}
		
		if ($link || $href) {
			$realhref = $PAGE['COMMUNITY'].'?lid='.$user->getLid().'&amp;xid='.$parameters['xid'];
			if ($href) $realhref = $href;
			
			return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><a href="'.$realhref.'">'.String::htmlentities($name).'</a></span>';
		} else {
			return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'">'.String::htmlentities($name).'</span>';
		}
	}
	
	public static function theme_title($user, $parameters) {
		global $PAGE;
		global $COMMUNITY_STATUS;
		
		if (!isset($parameters['tid'])) return Inml::error('tid parameter missing on a theme_title tag, it must be specified');
		$id = isset($parameters['id'])?$parameters['id']:false;
		$class = isset($parameters['class'])?$parameters['class']:'inml_theme_title';
		$href = isset($parameters['href'])?$parameters['href']:false;
		$tid = $parameters['tid'];
		
		try {
			if (isset(Inml::$cache['Theme-'.$tid])) $theme = Inml::$cache['Theme-'.$tid];
			else $theme = Theme::get($tid);
			
			if ($theme->getXid() == 267) {
				$title = I18N::translateHTML($user, '<translate id="PRIZE_COMMUNITY_THEME_TITLE'.$theme->getTid().'">'.$theme->getTitle().'</translate>');
			} else {
				$title = $theme->getTitle();
			}
		} catch (ThemeException $e) {
			Log::critical(__CLASS__, 'tid='.$tid.' doesn\'t correspond to any existing theme');
			return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><translate id="INML_UNKNOWN_THEME">unknown theme</translate></span>';
		}
		
		if ($href) return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><a href="'.$href.'">'.String::htmlentities($title).'</a></span>';
		else return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'">'.String::htmlentities($title).'</span>';
	}
	
	public static function theme_description($user, $parameters) {
		global $PAGE;
		global $COMMUNITY_STATUS;
		
		if (!isset($parameters['tid'])) return Inml::error('tid parameter missing on a theme_description tag, it must be specified');
		$id = isset($parameters['id'])?$parameters['id']:false;
		$class = isset($parameters['class'])?$parameters['class']:'inml_theme_description';
		$tid = $parameters['tid'];
		
		try {
			if (isset(Inml::$cache['Theme-'.$tid])) $theme = Inml::$cache['Theme-'.$tid];
			else $theme = Theme::get($tid);
			
			$description = $theme->getDescription();
		} catch (CompetitionException $e) {
			Log::critical(__CLASS__, 'tid='.$tid.' doesn\'t correspond to any existing theme');
			return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><translate id="INML_UNKNOWN_THEME">unknown theme</translate></span>';
		}
		
		return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'">'.String::htmlentities($description).'</span>';
	}

	public static function user_name($user, $parameters) {
		global $PAGE;
		global $USER_STATUS;
		global $USER_LEVEL;
		
		if (!isset($parameters['uid'])) return Inml::error('uid parameter missing on a user_name tag, it must be specified');
		$uid = $parameters['uid'];
		$id = isset($parameters['id'])?$parameters['id']:false;
		$class = isset($parameters['class'])?$parameters['class']:'inml_user_name';
		$href = isset($parameters['href'])?$parameters['href']:false;
		$link = (!(isset($parameters['link']) && strcasecmp($parameters['link'], 'false') == 0));
		
		$ispremium = false;
		
		try {
			if (isset(Inml::$cache['User-'.$uid])) $displayed_user = Inml::$cache['User-'.$uid];
			else $displayed_user = User::get($uid);
			
			$uniquename = $displayed_user->getUniqueName();
			
			if (strcmp('', $uniquename) == 0)
				return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><translate id="INML_UNREGISTERED_USER">Unregistered user</translate></span>';
			else $name = String::htmlentities($uniquename);
		} catch (UserException $e) {
			return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><translate id="INML_UNKNOWN_USER">A former member</translate></span>';
		}
	
		if ($href) return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><a href="'.$href.'">'.$name.'</a>'.($ispremium?' &#8471;':'').'</span>';	
		elseif ($link) return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><a href="'.UI::RenderUserLink($parameters['uid']).'">'.$name.'</a>'.($ispremium?' &#8471;':'').'</span>';	
		return  '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'">'.$name.($ispremium?' &#8471;':'').'</span>';
	}	
	
	public static function location($user, $parameters) {
		if (!isset($parameters['ip'])) return Inml::error('ip parameter missing on a location tag, it must be specified');
		$ip = $parameters['ip'];
		
		$record = @geoip_record_by_name($ip);
		
		if (empty($record) || !isset($record['country_code3'])) $place = '?';
		else $place = '<translate id="COUNTRY_SOLO_'.$record['country_code3'].'">'.utf8_encode($record['country_name']).'</translate>';
		
		return '<a target=_blank href="http://maps.google.com/maps?f=q&geocode=&ie=UTF8&ll='.$record['latitude'].','.$record['longitude'].'&t=h&z=11&iwloc=addr">'.$place.'</a>';
	}
	
	public static function thread_title($user, $parameters) {
		global $PAGE;
		
		if (!isset($parameters['nid'])) return Inml::error('nid parameter missing on a thread_title tag, it must be specified');
		$id = isset($parameters['id'])?$parameters['id']:false;
		$class = isset($parameters['class'])?$parameters['class']:'inml_theme_title';
		$link = (!(isset($parameters['link']) && strcasecmp($parameters['link'], 'false') == 0));
		$nid = $parameters['nid'];
		
		try {
			if (isset(Inml::$cache['DiscussionThread-'.$nid])) $discussion_thread = Inml::$cache['DiscussionThread-'.$nid];
			else $discussion_thread = DiscussionThread::get($nid);
			
			$title = $discussion_thread->getTitle();
		} catch (DiscussionThreadException $e) {
			Log::critical(__CLASS__, 'nid='.$parameters['nid'].' doesn\'t correspond to any existing discussion thread');
			return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><translate id="INML_UNKNOWN_THREAD">unknown discussion thread</translate></span>';
		}
		
		if ($link) return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><a href="'.$PAGE['DISCUSSION_THREAD'].'?lid='.$user->getLid().'&nid='.$parameters['nid'].'">'.String::htmlentities($title).'</a></span>';
		else return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'">'.String::htmlentities($title).'</span>';
	}
	
	public static function alert($user, $parameters) {
		global $ALERT_TEMPLATE;
		
		if (!isset($parameters['aid'])) return Inml::error('aid parameter missing on a alert tag, it must be specified');
		
		$aid = $parameters['aid'];
		$id = isset($parameters['id'])?$parameters['id']:false;
		$class = isset($parameters['class'])?$parameters['class']:'inml_theme_title';
		
		try {
			if (isset(Inml::$cache['Alert-'.$aid])) $alert = Inml::$cache['Alert-'.$aid];
			else $alert = Alert::get($aid);
			
			$atid = $alert->getATid();
			$template = $ALERT_TEMPLATE[$atid];
			
			if (isset(Inml::$cache['AlertVariableListByAid-'.$aid])) $variablelist = Inml::$cache['AlertVariableListByAid-'.$aid];
			else $variablelist = AlertVariableList::getByAid($aid);
			
			$variables = array();
			if (!empty($variablelist)) foreach ($variablelist as $name) {
				$alert_variable = AlertVariable::get($aid, $name);
				$variables[$name] = $alert_variable->getValue();
			}
			
			$alert_message = Template::templatize($template, $variables);
			
			$result = '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'">';
			$result .= '<translate id="ALERT_'.$atid.'">'.$alert_message.'</translate>';
			$result .= '</span>';
			
			$result = I18N::translateHTML($user, $result);
			
			return $result;
		} catch (AlertException $e) {
			Log::critical(__CLASS__, 'aid='.$parameters['aid'].' doesn\'t correspond to any existing alert');
			return '<span '.($id?'id="'.$id.'"':'').' class="'.$class.'"><translate id="INML_UNKNOWN_ALERT">unknown alert</translate></span>';
		}
	}
}

?>
