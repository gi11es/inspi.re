<?php
/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Handles rendering common pieces of UI present throughout the website, like header/footer
*/

require_once(dirname(__FILE__).'/../entities/alertinstancelist.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitylabellist.php');
require_once(dirname(__FILE__).'/../entities/communitylist.php');
require_once(dirname(__FILE__).'/../entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/insightfulmark.php');
require_once(dirname(__FILE__).'/../entities/insightfulmarklist.php');
require_once(dirname(__FILE__).'/../entities/pointsvalue.php');
require_once(dirname(__FILE__).'/../entities/privatemessage.php');
require_once(dirname(__FILE__).'/../entities/privatemessagelist.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../entities/themevote.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../entities/userpaging.php');
require_once(dirname(__FILE__).'/../utilities/inml.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/url.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

class UIException extends Exception {}

class UI {
	// Top part of the page, content can change depending on the fact that the user is logged in or not
	public static function RenderHeaderTop($title='<translate id="PAGE_TITLE">inspi.re</translate>') {
		global $JS_3RDPARTY_PATH;
		global $JS_PATH;
		global $ANALYTICS_CODE;
		
		$result = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$result .= '<html xmlns="http://www.w3.org/1999/xhtml">';
		$result .= '<head>';
		$result .= '<meta name="verify-v1" content="84d0gKcyy7J9OoNPxgLDepOQxwzKdWs8kdMCS2W4+AA=" />';
		$result .= '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
		
		$result .= '<script type="text/javascript" src="http://www.google-analytics.com/ga.js"></script>';
		
		$result .= '<script type="text/javascript">
					var pageTracker = _gat._getTracker("'.$ANALYTICS_CODE.'");
					pageTracker._initData();
					pageTracker._trackPageview();
					</script>';
		
		$result .= '<title>'.$title.' - <translate id="UI_WEBSITE_SLOGAN">photo competitions for all levels</translate></title>';

		return $result;
	}
	
	public static function RenderAlertsCounter($user, $alertcount, $preprocess=false) {
		$result = '<div id="alerts_counter" class="unselectable '.($alertcount > 0?'alerts_counter_positive':'alerts_counter_null').'">';
		if ($alertcount == 0) {
			$result .= '<translate id="ALERTS_NOTIFICATION_NONE">';
			$result .= 'You have no alerts';
			$result .= '</translate>';
		} elseif ($alertcount == 1) {
			$result .= '<a id="alerts_counter_link" href="javascript:showAlerts();">';
			$result .= '<translate id="ALERTS_NOTIFICATION_SINGULAR">';
			$result .= 'You have 1 alert';
			$result .= '</translate>';
			$result .= '</a>';
		} else {
			$result .= '<a id="alerts_counter_link" class="unselectable" href="javascript:showAlerts();">';
			$result .= '<translate id="ALERTS_NOTIFICATION_PLURAL">';
			$result .= 'You have <integer value="'.$alertcount.'"/> alerts';
			$result .= '</translate>';
			$result .= '</a>';
		}
		
		$result .= '</div> <!-- alerts_counter -->';
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$result = INML::processHTML($user, $translated_html);
		}
		
		return $result;
	}
	
	public static function RenderAlerts($user, $newlist, $preprocess=false) {
		global $GRAPHICS_PATH;
		
		$orderedlist = array();
		
		$alert = Alert::getArray($newlist);
		
		foreach ($newlist as $aid) if (isset($alert[$aid])) $orderedlist[$aid] = $alert[$aid]->getCreationTime();
		
		arsort($orderedlist);
		
		$result = '<div id="alerts" class="unselectable" style="display:none">';
		foreach ($orderedlist as $aid => $creation_time) {
			$result .= '<div class="alert_item" id ="alert_'.$aid.'">';
			$result .= '<alert class="alert" aid="'.$aid.'"/>';
			$result .= '<span class="alert_delete"><a href="javascript:deleteAlert('.$aid.');"><img src="'.$GRAPHICS_PATH.'cross.png"/></a></span>';
			$result .= '<span class="alert_since"><translate id="ALERTS_TIME_SINCE">[<duration value="'.(time() - $creation_time).'"/> ago]</translate></span>';

			$result .= '</div> <!-- alert_item -->';
		}
		$result .= '</div> <!-- alerts -->';
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$result = INML::processHTML($user, $translated_html);
			$result = I18N::translateHTML($user, $result);
		}
				
		return $result;
	}
	
	public static function RenderPrivateMessageCount($user, $preprocess = false) {
		global $PRIVATE_MESSAGE_STATUS;
		global $PAGE;
		
		$privatemessagelist = PrivateMessageList::getByDestinationUidAndStatus($user->getUid(), $PRIVATE_MESSAGE_STATUS['NEW']);
		$privatemessagecount = count($privatemessagelist);
		
		$result = '<div id="private_message_counter" class="unselectable">';
		
		if ($privatemessagecount == 0) {
			$result .= '<translate id="HEADER_NEW_PRIVATE_MESSAGE_NULL">';
			$result .= 'You have no unread private messages';
			$result .= '</translate>';
		} elseif ($privatemessagecount == 1) {
			$result .= '<a id="private_message_counter_link" href="'.$PAGE['PRIVATE_MESSAGING'].'?lid='.$user->getLid().'">';
			$result .= '<translate id="HEADER_NEW_PRIVATE_MESSAGE_SINGULAR">';
			$result .= 'You have 1 unread private message';
			$result .= '</translate>';
			$result .= '</a>';
		} else {
			$result .= '<a id="private_message_counter_link" href="'.$PAGE['PRIVATE_MESSAGING'].'?lid='.$user->getLid().'">';
			$result .= '<translate id="HEADER_NEW_PRIVATE_MESSAGE_PLURAL">';
			$result .= 'You have <integer value="'.$privatemessagecount.'"/> unread private messages';
			$result .= '</translate>';
			$result .= '</a>';
		}
		
		$result .= '</div> <!-- private_message_counter -->';
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$result = INML::processHTML($user, $translated_html);
		}
		
		return $result;
	}

	public static function RenderHeaderBottom($page, $submenu, $user) {
		global $REQUEST;
		global $GRAPHICS_PATH;
		global $PAGE;
		global $WEBSITE_PATH;
		global $PAGE_AD_CODE;
		global $AD_CODE;
		global $LANGUAGE;
		global $LANGUAGE_NAME;
		global $LANGUAGE_FLAG;
		global $LANGUAGE_HIDDEN;
		global $REQUEST;
		global $USER_LEVEL;
		global $USER_STATUS;
		global $DISPLAY_BETA;
		global $WARNING_BLACKLIST;
		global $ALERT_INSTANCE_STATUS;
		global $SUBMENU;

		$result = '</head>';
		$result .= '<body>';
		
		$result .= '<div class="fixed_centered" id="confirmation_message" style="display:none">';
		$result .= '<div id="confirmation_title" class="confirmation_title">title</div>';
		$result .= '<div id="confirmation_text" class="confirmation_text">text</div>';
		$result .= '<div id="confirmation_buttons" class="confirmation_buttons">';
		$result .= '<input id="confirmation_button_left" class="confirmation_button_left" type="button" value="yes" onclick="javascript:actConfirmation();"/>';
		$result .= '<input id="confirmation_button_right" class="confirmation_button_right" type="button" value="no" onclick="javascript:hideConfirmation();"/>';
		$result .= '</div> <!-- confirmation_buttons -->';
		$result .= '</div> <!-- confirmation_message -->';
		
		if ($page !== null) {
		
			$result .= '<div id="fullscreen_shade" style="display:none"></div>';
			$result .= '<div class="wide_black">';
			$result .= '<div class="container">';
			
			$result .= UI::RenderDonationPun($user);
				
			$result .= '<div id="points_and_language">';
			
			$result .= UI::RenderPointsleft($user);

			$result .= '<div class="language_selection dropdown unselectable">';
			
			$levels = UserLevelList::getByUid($user->getUid());
			
			$unselected = array();
			foreach ($LANGUAGE_NAME as $code => $name) {
				if (!in_array($code, $LANGUAGE_HIDDEN) || in_array($USER_LEVEL['TRANSLATOR'][$code], $levels))
				if ($code == $user->getLid()) $selected = $code;
				else $unselected [$code] = $name;
			}
			
			asort($unselected);
			
			$result .= '<div id="language_'.$selected.'" class="language_selected"><img src="'.$LANGUAGE_FLAG[$selected].'" alt="'.$LANGUAGE_NAME[$selected].'"/>'.$LANGUAGE_NAME[$selected].'</div> <img alt="drop-down" id="language_dropdown" src="'.$GRAPHICS_PATH.'dropdown.gif" />';
			foreach ($unselected as $code => $name) $result .= '<div id="language_'.$code.'" class="language_option highlighted option" style="display:none"><img alt="'.$name.'" src="'.$LANGUAGE_FLAG[$code].'" />'.$name.'</div>';
			
			$result .= '</div> <!-- language_selection -->';
	
			$result .= '</div> <!-- points_and_language -->';
			
			$result .= '<div id="header">';			 
				
			$result .= '<a href="'.$WEBSITE_PATH.'"><img class="unselectable" id="logo" alt="inspi.re logo" src="'.$GRAPHICS_PATH.'logo-small.gif" /></a>';
			
			$result .= '<div id="header_dynamic">';
			
			if ($user->getStatus() == $USER_STATUS['UNREGISTERED'])
				$result .= '<div id="greeting" class="unselectable"><translate id="HEADER_GREETING_UNREGISTERED">Welcome, <a id="header_user_name" href="'.$PAGE['REGISTER'].'"><user_name id="header_user_name_dynamic" link="false" uid="'.$user->getUid().'"/> (register a new account)</a></translate>';			
			else
				$result .= '<div id="greeting_connected" class="unselectable"><translate id="HEADER_GREETING_REGISTERED">Welcome, <a id="header_user_name" href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'"><user_name id="header_user_name_dynamic" link="false" uid="'.$user->getUid().'"/></a></translate>';
	
			if ($user->getStatus() != $USER_STATUS['UNREGISTERED'])
				$result .= '<form id="logout" method="post" action="'.$REQUEST['LOGOUT'].'"><input type="submit" id="logout_submit" name="logout_submit" value="<translate id="HEADER_LOGOUT">Logout</translate>"/></form>';
			else
				$result .= '<a id="lost_password" href="'.$PAGE['LOST_PASSWORD'].'"><translate id="HEADER_LOST_PASSWORD">Lost password?</translate></a>';
		
			$result .= '</div> <!-- greeting -->';
			
			if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
				$result .= '<div id="login"><form method="post" action="'.$REQUEST['LOGIN'].'"><translate id="HEADER_LOGIN_EMAIL">Email address:</translate> <input id="login_user_name" name="login_user_name" value=""/> <translate id="HEADER_LOGIN_PASSWORD">Password:</translate> <input type="password" id="login_password" name="login_password" value=""/><input type="submit" id="login_submit" name="login_submit" value="<translate id="HEADER_LOGIN_LOGIN">Login</translate>"/> <span id="login_remember" ><input type="checkbox" name="login_remember" /></span> <translate id="HEADER_LOGIN_REMEMBER">Remember me</translate></form></div>';
			} else {
				$newlist = AlertInstanceList::getByUidAndStatus($user->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
		
				$alertcount = count($newlist);
			
				$result .= UI::RenderAlertsCounter($user, $alertcount);
				$result .= UI::RenderPrivateMessageCount($user);
				
				$result .= '<div id="alerts" class="unselectable" style="display:none">';
				$result .= '</div> <!-- alerts -->';
				
				//$result .= UI::RenderAlerts($user, $newlist);
			}
			
			$result .= '</div> <!-- header_dynamic -->';
			
			$result .= '<div id="menu_top" class="unselectable"><ul>';
			if ($user->getStatus() == $USER_STATUS['UNREGISTERED'])
				$result .= '<li> <a id="menu_top_1" class="menu_top_element'.($submenu == $SUBMENU['HOME']?'_selected':' ').'" href="'.$PAGE['INDEX'].'"><translate id="MENU_HOME">Home</translate></a></li>';			
			else
				$result .= '<li> <a id="menu_top_1" class="menu_top_element'.($submenu == $SUBMENU['HOME']?'_selected':' ').'" href="'.$PAGE['HOME'].'?lid='.$user->getLid().'"><translate id="MENU_HOME">Home</translate></a></li>';

			if ($user->getStatus() != $USER_STATUS['UNREGISTERED']) {
				$result .= '<li> <a id="menu_top_2" class="menu_top_element'.($submenu == $SUBMENU['MESSAGING']?'_selected ':' ').'" href="'.$PAGE['PRIVATE_MESSAGING'].'?lid='.$user->getLid().'"><translate id="MENU_MESSAGING">Messaging</translate></a></li>';
			}
			
			$result .= '<li> <a id="menu_top_5" class="menu_top_element'.($submenu == $SUBMENU['COMMUNITIES']?'_selected ':' ').'" href="'.$PAGE['COMMUNITIES'].'?lid='.$user->getLid().'"><translate id="MENU_COMMUNITIES">Communities</translate></a></li>';
			
			$result .= '<li> <a id="menu_top_6" class="menu_top_element'.($submenu == $SUBMENU['COMPETITIONS']?'_selected ':' ').'" href="'.$PAGE['COMPETE'].'?lid='.$user->getLid().'"><translate id="MENU_COMPETITIONS">Competitions</translate></a></li>';
				
			//$result .= '<li> <a id="menu_top_7" class="menu_top_element'.($submenu == $SUBMENU['NEXT']?'_selected ':' ').'" href="'.$PAGE['CRITIQUE'].'?lid='.$user->getLid().'"><translate id="MENU_NEXT">Next!</translate></a></li>';
			
			$result .= '<li> <a id="menu_top_8" class="menu_top_element'.($submenu == $SUBMENU['INFORMATION']?'_selected ':' ').'" href="'.$PAGE['HELP'].'?lid='.$user->getLid().'"><translate id="MENU_ABOUT_THIS_WEBSITE">About inspi.re</translate></a></li>';

			$result .= '</ul></div>';
			
			$result .= '</div> <!-- header -->';
			
			$result .= '</div> <!-- container -->';
			$result .= '</div> <!-- wide_black -->';
			
			$result .= '<div class="wide_white">';
			$result .= '<div class="menu_bar"></div>';
			
			$result .= '<div id="submenu_background">';
			$result .= '<div id="submenu_container">';
			
			$result .= '<div id="submenu" class="unselectable"><ul>';
			
			switch ($submenu) {
				case $SUBMENU['HOME']:
					if ($user->getStatus() != $USER_STATUS['UNREGISTERED'])
						$result .= '<li> <a class="submenu_element'.(strcmp($page, 'HOME') == 0?'_selected ':'').'" href="'.$PAGE['HOME'].'?lid='.$user->getLid().'"><translate id="SUBMENU_YOUR_ENTRIES">Your entries</translate></a></li>';
					else
						$result .= '<li> <a class="submenu_element'.(strcmp($page, 'HOME') == 0?'_selected ':'').'" href="'.$PAGE['INDEX'].'?lid='.$user->getLid().'"><translate id="SUBMENU_WELCOME">Welcome</translate></a></li>';
					if ($user->getStatus() != $USER_STATUS['UNREGISTERED']) $result .= '<li> <a class="submenu_element'.(strcmp($page, 'FAVORITES') == 0?'_selected ':'').'" href="'.$PAGE['FAVORITES'].'?lid='.$user->getLid().'"><translate id="SUBMENU_FAVORITES">Favorites</translate></a></li>';
					if ($user->getStatus() != $USER_STATUS['UNREGISTERED']) $result .= '<li> <a class="submenu_element'.(strcmp($page, 'SETTINGS') == 0?'_selected ':'').'" href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'"><translate id="MENU_SETTINGS">Settings</translate></a></li>';
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'PREMIUM') == 0?'_selected ':'').'" href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="MENU_PREMIUM">Premium membership</translate></a></li>';
					
					if (in_array($USER_LEVEL['TRANSLATOR'][$user->getLid()], $levels)) $result .= '<li> <a class="submenu_element'.(strcmp($page, 'TRANSLATE') == 0?'_selected ':'').'" href="'.$PAGE['TRANSLATE'].'?lid='.$user->getLid().'"><translate id="MENU_TRANSLATE">Translate</translate></a></li>';
					if (in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) $result .= '<li> <a class="submenu_element'.(strcmp($page, 'STATISTICS') == 0?'_selected ':'').'" href="'.$PAGE['STATISTICS'].'?lid='.$user->getLid().'"><translate id="MENU_STATISTICS">Statistics</translate></a></li>';
					break;
				case $SUBMENU['MESSAGING']:
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'PRIVATE_MESSAGING') == 0?'_selected ':'').'" href="'.$PAGE['PRIVATE_MESSAGING'].'?lid='.$user->getLid().'"><translate id="SUBMENU_PRIVATE_MESSAGING">Inbox</translate></a></li>';
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'OUTBOX') == 0?'_selected ':'').'" href="'.$PAGE['OUTBOX'].'?lid='.$user->getLid().'"><translate id="SUBMENU_OUTBOX">Outbox</translate></a></li>';
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'CONTACTS') == 0?'_selected ':'').'" href="'.$PAGE['CONTACTS'].'?lid='.$user->getLid().'"><translate id="SUBMENU_CONTACTS">Contacts</translate></a></li>';
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'COMMENTS') == 0?'_selected ':'').'" href="'.$PAGE['COMMENTS'].'?lid='.$user->getLid().'"><translate id="SUBMENU_COMMENTS">Comments</translate></a></li>';
					break;
				case $SUBMENU['COMMUNITIES']:
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'COMMUNITIES') == 0?'_selected ':'').'" href="'.$PAGE['COMMUNITIES'].'?lid='.$user->getLid().'"><translate id="SUBMENU_YOUR_COMMUNITIES">Your communities</translate></a></li>';
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'JOIN_COMMUNITIES') == 0?'_selected ':'').'" href="'.$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid().'"><translate id="SUBMENU_JOIN_COMMUNITIES">Join communities</translate></a></li>';
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'DISCUSS') == 0?'_selected ':'').'" href="'.$PAGE['DISCUSS'].'?lid='.$user->getLid().'"><translate id="MENU_DISCUSS">Announcements</translate></a></li>';
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'MEMBERS') == 0?'_selected ':'').'" href="/Members/s3-l'.$user->getLid().'"><translate id="MENU_MEMBERS">Members</translate></a></li>';
					break;
				case $SUBMENU['COMPETITIONS']:
					$result .= '<li> <a id="submenu_themes" class="submenu_element'.(strcmp($page, 'THEMES') == 0?'_selected ':'').'" href="'.$PAGE['THEMES'].'?lid='.$user->getLid().'"><translate id="MENU_THEMES">Themes</translate></a></li>';
					$result .= '<li> <a id="submenu_compete" class="submenu_element'.(strcmp($page, 'COMPETE') == 0?'_selected ':'').'" href="'.$PAGE['COMPETE'].'?lid='.$user->getLid().'"><translate id="MENU_COMPETE">Compete</translate></a></li>';
					$result .= '<li> <a id="submenu_vote" class="submenu_element'.(strcmp($page, 'VOTE') == 0?'_selected ':'').'" href="'.$PAGE['VOTE'].'?lid='.$user->getLid().'"><translate id="MENU_VOTE">Vote</translate></a></li>';
					$result .= '<li> <a id="submenu_hall_of_fame" class="submenu_element'.(strcmp($page, 'HALL_OF_FAME') == 0?'_selected ':'').'" href="'.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid().'"><translate id="MENU_HALL_OF_FAME">Hall of fame</translate></a></li>';
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'PRIZE') == 0?'_selected ':'').'" href="/Prize/s4-l'.$user->getLid().'"><translate id="MENU_PRIZE">Prize</translate></a></li>';
					break;
				case $SUBMENU['INFORMATION']:
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'HELP') == 0?'_selected ':'').'" href="'.$PAGE['HELP'].'?lid='.$user->getLid().'"><translate id="MENU_HELP">Help</translate></a></li>';
					$result .= '<li> <a target=_blank class="submenu_element'.(strcmp($page, 'BLOG') == 0?'_selected ':'').'" href="'.$PAGE['BLOG'].'"><translate id="MENU_BLOG">Official blog</translate></a></li>';
					if ($user->getStatus() != $USER_STATUS['UNREGISTERED']) $result .= '<li> <a class="submenu_element'.(strcmp($page, 'INVITE') == 0?'_selected ':'').'" href="'.$PAGE['INVITE'].'?lid='.$user->getLid().'"><translate id="MENU_INVITE">Spread the word</translate></a></li>';
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'LEGAL') == 0?'_selected ':'').'" href="/<translate id="URL_LEGAL" escape="urlify">Terms And Conditions</translate>/s8-l'.$user->getLid().'"><translate id="MENU_LEGAL">Terms and conditions</translate></a></li>';
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'PRESS') == 0?'_selected ':'').'" href="/<translate id="URL_ABOUT_US" escape="urlify">About Us</translate>/s5-l'.$user->getLid().'"><translate id="MENU_PRESS">About us</translate></a></li>';
					break;
					
/*				case $SUBMENU['NEXT']:
					$result .= '<li> <a class="submenu_element'.(strcmp($page, 'CRITIQUE') == 0?'_selected ':'').'" href="'.$PAGE['CRITIQUE'].'?lid='.$user->getLid().'"><translate id="MENU_CRITIQUE">Critique</translate></a></li>';
					break;*/
			}
			$result .= '</ul></div> <!-- submenu -->';
			
			$result .= '</div> <!-- submenu_container -->';
			$result .= '</div> <!-- submenu_background -->';
			
			$result .= '<div class="top_bar"></div>';
			
			$result .= '<div class="container">';
			$result .= '<div class="content_container">';
			
			$result .= '<!--[if IE 6]>';
			$result .= '<div class="warning hintmargin"><div class="warning_title"><translate id="HEADER_BROWSER_WARNING_TITLE">Warning: your web browser is not supported</translate></div><translate id="HEADER_BROWSER_WARNING_BODY">You are using Internet Explorer 6, an outdated web browser that doesn\'t comply with modern web standards. This website won\'t work or display properly because of this. For a better experience and safer web browsing in general, we suggest that you install and use any of the following browsers instead:</translate> <a href="http://www.apple.com/safari/">Safari</a>, <a href="http://www.opera.com/">Opera</a>, <a href="http://www.google.com/chrome">Chrome</a>, <a href="http://www.mozilla.org/firefox/">Firefox</a>, <a href="http://www.microsoft.com/windows/downloads/ie/getitnow.mspx">Internet Explorer 7/8</a>.</div>';
			$result .= '<![endif]-->';
			
			if ($user->getStatus() == $USER_STATUS['UNREGISTERED']  && !in_array($page, $WARNING_BLACKLIST))
				$result .= '<div class="warning hintmargin"><div class="warning_title"><translate id="HEADER_UNREGISTERED_WARNING_TITLE">You are using the website as an unregistered user</translate></div><translate id="HEADER_UNREGISTERED_WARNING_BODY">Everything you\'ve done so far on the website will be lost if you close your browser window now. In order to save all the actions you\'ve already done, you need to <a href="'.$PAGE['REGISTER'].'">register a new account</a> or log into your existing account.</translate></div>';
		}

		return $result;
	}

	public static function RenderFooterTop($page, $user) {	
		global $PAGE;
		global $JS_3RDPARTY_PATH;
		global $USER_LEVEL;
		global $LANGUAGE;
		global $USER_STATUS;
		
		if ($page === null) return '';
		
		$result = '</div> <!-- content_container -->';
		$result .= '</div> <!-- container -->';
		
		$result .= '<div id="copyright_notice" class="unselectable">&#169; 2008-'.date('Y').' * Photo competitions for all levels on inspi.re * <translate id="FOOTER_COPYRIGHT_NOTICE">all rights reserved</translate> </div>';
		
		$result .= '</div> <!-- wide_white -->';

		return $result;
	}
	
	public static function RenderLensbabyAd($user) {
		global $GRAPHICS_PATH;
		global $PAGE;
		
		$result = '<div class="lensbaby_ad_header" onclick="window.location = \''.$PAGE['MARCH_PRIZE'].'?lid='.$user->getLid().'\';">';
		$result .= '<img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png">';
		$result .= '<span class="lensbaby_ad_header_text">';
		$result .= '<translate id="LENSBABY_AD_HEADER">inspi.re all stars</translate>';
		$result .= '</span>';
		$result .= '<img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png">';
		$result .= '</div>';
		
		$result .= '<div id="lensbaby_ad" onclick="window.location = \''.$PAGE['MARCH_PRIZE'].'?lid='.$user->getLid().'\';">';
		$result .= '<img id="lensbaby_ad_left" src="'.$GRAPHICS_PATH.'lensbaby-composer.png?update=2"/>';
		$result .= '<div id="lensbaby_ad_middle">';
		$result .= '<translate id="LENSBABY_MARCH_AD">';
		$result .= 'Enter any competition on inspi.re between March 15th and April 15th and you could win the Composer, Lensbaby\'s latest DSLR lens!';
		$result .= '<br/><br/>';
		$result .= '<span id="lensbaby_ad_jury">The winner will be selected by John Arnold from photowalkthrough.com</span>';
		$result .= '</translate>';
		$result .= '</div> <!-- lensbaby_ad_middle -->';
		$result .= '<img id="lensbaby_ad_right" src="'.$GRAPHICS_PATH.'john-arnold.png?update=2"/>';
		$result .= '</div>';
		
		$result .= '<div class="lensbaby_ad_header" onclick="window.location = \''.$PAGE['MARCH_PRIZE'].'?lid='.$user->getLid().'\';">';
		$result .= '<img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png">';
		$result .= '<span class="lensbaby_ad_header_text">';
		$result .= '<translate id="LENSBABY_AD_HEADER">inspi.re all stars</translate>';
		$result .= '</span>';
		$result .= '<img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png"><img class="lensbaby_ad_star" src="'.$GRAPHICS_PATH.'small-star.png">';
		$result .= '</div>';
		
		return $result;
	}

	public static function RenderFooterBottom($page, $user) {
		global $USER_LEVEL;
		global $ANALYTICS_CODE;
		
		$result = '';
		
		$levels = UserLevelList::getByUid($user->getUid());
		
		if (!in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
			/*$result .= '<script type="text/javascript">';
			$result .= 'var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");';
			$result .= 'document.write("\<script src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'>\<\/script>" );';
			$result .= '</script>';
			$result .= '<script type="text/javascript">';
			$result .= 'var pageTracker = _gat._getTracker("'.$ANALYTICS_CODE.'");';
			$result .= 'pageTracker._initData();';
			$result .= 'pageTracker._trackPageview();';
			$result .= '</script>';*/
			/*$result .= '<!-- Start Quantcast tag -->'."\r\n";
			$result .= '<script type="text/javascript">'."\r\n";
			$result .= '_qoptions={'."\r\n";
			$result .= 'qacct:"p-8fqlCkMkKNrKI"'."\r\n";
			$result .= '};'."\r\n";
			$result .= '</script>'."\r\n";
			$result .= '<script type="text/javascript" src="http://edge.quantserve.com/quant.js"></script>'."\r\n";
			$result .= '<noscript>'."\r\n";
			$result .= '<img src="http://pixel.quantserve.com/pixel/p-8fqlCkMkKNrKI.gif" style="display: none;" border="0" height="1" width="1" alt="Quantcast"/>'."\r\n";
			$result .= '</noscript>'."\r\n";
			$result .= '<!-- End Quantcast tag -->'."\r\n";*/
		}
		
		$result .= '</body>';
		$result .= '</html>';
		return $result;
	}

	// Renders an error message box for the user
	public static function RenderError($message) {
		return '<div class="error">'.$message.'</div>';
	}

	// Renders a confirmation message box for the user
	public static function RenderConfirmation($message) {
		return '<div class="confirmation">'.$message.'</div>';
	}
	
	public static function RenderEditablePicture($page, $pid, $picture_category, $show, $upload_url, $reset_url, $edit_cropping_url, $persistenttoken) {
		global $PICTURE_CATEGORY;
		global $PICTURE_CATEGORY_INML_OPTION;
		global $GRAPHICS_PATH;
		global $PAGE;
		global $REQUEST;
		
		$result = '<div id="picture_holder">';
		$result .= '<div id="picture_loader" style="display:none">';
		$result .= '<div><img src="'.$GRAPHICS_PATH.'ajax-horizontal-loader.gif"></div>';
		$result .= '<div><translate id="EDIT_PROFILE_PREPARING_THUMBNAIL">preparing thumbnail</translate></div>';
		$result .= '</div> <!-- picture_loader -->';
		$result .= '<picture size="big" '.($pid === null?'':'pid="'.$pid.'"').' category="'.$PICTURE_CATEGORY_INML_OPTION[$picture_category].'" link="false" prepare="true" id="picture_big" />';
		$result .= '</div> <!-- picture_holder -->';
		
		if ($show) {
			$page->addJavascriptVariable('button_text', '<translate id="EDIT_PROFILE_UPLOAD_PICTURE" escape="htmlentities">Upload new picture (10 MB maximum)</translate>');
			$page->addJavascriptVariable('upload_url', $upload_url);
			$page->addJavascriptVariable('reset_url', $reset_url);
			$page->addJavascriptVariable('request_upload_progress', $REQUEST['UPLOAD_PROGRESS']);
			$page->addJavascriptVariable('uploaded_text', 
							 '<translate id="EDIT_PROFILE_UPLOADED" escape="htmlentities">uploaded</translate>');
			$page->addJavascriptVariable('upload_error', 
							 '<translate id="UPLOAD_PICTURE_FILE_ERROR" escape="htmlentities">An error occured during your upload. Either the file format is not supported or the competition is not open anymore.</translate>');
			$page->addJavascriptVariable('upload_size_error', 
							 '<translate id="UPLOAD_PICTURE_FILE_SIZE_ERROR" escape="htmlentities">Your file is too big, 10MB is the maximum size</translate>');
			$result .= '<div id="progress_bar_container" style="display:none">';
			$result .= '<div id="progress_bar" style="width: 50%"></div>';
			$result .= '<div id="progress_bar_text" style="width: 100%"></div>';
			$result .= '</div> <!-- progress_bar_container -->';
		
			$result .= '<div id="profile_picture_controls">';
			
			$cropping_holder_style = '';
			$reset_holder_style = '';
			
			// Hide picture edit controls if the user has the default profile picture
			if ($pid === null) { 
				$cropping_holder_style = 'display: none';
				$reset_holder_style = 'display: none';
			}	
	
			$result .= '<div id="edit_cropping_holder" style="'.$cropping_holder_style.'">';
			$result .= '<a href="'.$edit_cropping_url.'">';
			$result .= '<translate id="EDIT_PROFILE_CROP_PICTURE">Edit picture\'s cropping</translate>';
			$result .= '</a>';
			$result .= '</div> <!-- edit_cropping_holder -->';
		
			$result .= '<div id="reset_picture_holder" style="'.$reset_holder_style.'">';
			$result .= '<a id="reset_picture" href="javascript:resetPicture();">';
			$result .= '<translate id="EDIT_PROFILE_DELETE_PICTURE">Delete current picture</translate>';
			$result .= '</a>';
			$result .= '</div> <!-- reset_picture_holder -->';
		
			$result .= '<div>';
			$result .= '<div id="upload_alternative">';
			$result .= '<translate id="EDIT_PROFILE_UPLOAD_PICTURE">Upload new picture (10 MB maximum)</translate>';
			$upload_url.= strstr($upload_url, '?')?'&':'?';
			$result .= '<form target="uploadframe" id="picture_upload_form" action="" enctype="multipart/form-data" method="post">';
			$result .= '<input type="hidden" id="originalaction" value="'.$upload_url.'persistenttoken='.$persistenttoken.'"/>';
			$result .= '<input type="file" id="picture_upload_browse" name="Filedata" size="10"><br/>';
			$result .= '</form>';
			$result .= '<iframe id="uploadframe" name="uploadframe" width="0" height="0" frameborder="0" border="0" src="about:blank"></iframe>';
			$result .= '</div> <!-- upload_alternative -->';
			$result .= '</div>';
		
			$result .= '</div> <!-- profile_picture_controls -->';
		}
		
		return $result;
	}
	
	public static function RenderThemeList($user, $themes, $scores, $xid, $ismoderator, $preprocess = false) {
		global $GRAPHICS_PATH;
		global $PAGE;
		global $REQUEST;
		global $THEME_STATUS;
		global $USER_STATUS;
		
		$result = '<div id="theme_list">';
			
		if (empty($scores)) {
		
			$result .= '<div class="marginless_item">';
			$result .= '<div class="listing_header">';
			
			if ($xid !== null) {
				$result .= '<translate id="THEMELIST_SUGGEST_LONG">';
				$result .= 'There is currently no upcoming competition theme in this community. You should <a href="'.$PAGE['NEW_THEME'].'?lid='.$user->getLid().'&amp;xid='.$xid.'">suggest one</a>.';
				$result .= '</translate>';
			} else {
				$result .= '<translate id="FEATURELIST_SUGGEST_LONG">';
				$result .= 'There is currently no feature suggestion. You should <a href="'.$PAGE['NEW_THEME'].'?lid='.$user->getLid().'">suggest one</a>.';
				$result .= '</translate>';			
			}
			
			$result .= '</div> <!-- listing_header -->';
			$result .= '</div> <!-- listing_item -->';
		
		} else {
			$first = true;
			
			foreach ($scores as $tid => $score) {
				$theme = $themes[$tid];

				$result .= '<div class="theme_container" id="theme_'.$tid.'">';
				$result .= '<div class="theme_listing">';
				$result .= '<div class="'.($first?'marginless_item':'listing_item').'">';
				if ($first) $first = false;
				
				$result .= '<profile_picture  class="listing_thumbnail" size="small" uid="'.$theme->getUid().'"/>';
				$result .= '<div class="listing_header listing_header_thumbnail_margin">';
				if ($xid == 267) {
					$result .= '<translate id="PRIZE_COMMUNITY_THEME_TITLE'.$tid.'">'.$theme->getTitle().'</translate>';
				} else {
					$result .= String::fromaform($theme->getTitle());
				}
				if ($ismoderator && $user->getUid() != $theme->getUid()) {
					$result .= ' (';
					$result .= '<a href="javascript:showConfirmation(\''.$REQUEST['DELETE_THEME'].'?tid='.$tid.'\'';
					$result .= ', \'<translate id="THEME_DELETE_CONFIRMATION_TITLE" escape="js">Do you really want to delete this theme?</translate>\'';
					$result .= ', \'<translate id="THEME_MODERATE_CONFIRMATION_TEXT" escape="js">This action can\'t be undone! This theme will be deleted permanently, along with the votes cast on it. We strongly recommend that you explain your decision to the user who suggested that theme via a private message.</translate>\'';
					$result .= ', \'<translate id="THEME_DELETE_CONFIRMATION_YES" escape="js">Yes, go ahead</translate>\'';
					$result .= ', \'<translate id="THEME_DELETE_CONFIRMATION_NO" escape="js">No</translate>\'';
					$result .= ');">';
					$result .= '<translate id="THEME_LIST_MODERATE">';
					$result .= 'delete';
					$result .= '</translate>';
					$result .= '</a>';
					$result .= ')';
				}
				$result .= '</div> <!-- listing_header -->';
				$result .= '<div class="listing_subheader listing_header_thumbnail_margin">';
				$result .= '<translate id="THEME_LIST_SUBHEADER">';
				$result .= 'Suggested by <user_name link="true" uid="'.$theme->getUid().'" />';
				$result .= '</translate>';
				$result .= '</div> <!-- listing_subheader -->';
				$result .= '<div class="community_listing_description">';
				if ($xid == 267) {
					$result .= '<translate id="PRIZE_COMMUNITY_THEME_DESCRIPTION'.$tid.'">'.$theme->getDescription().'</translate>';
				} else {
					$result .= String::fromaform($theme->getDescription());
				}
				$result .= '</div> <!-- community_listing_description -->';
				$result .= '</div> <!-- listing_item -->';
				$result .= '</div> <!-- theme_listing -->';
				
				
				if ($user->getUid() == $theme->getUid()) {
					$result .= '<div class="theme_delete">';
					$result .= '<a href="javascript:showConfirmation(\''.$REQUEST['DELETE_THEME'].'?tid='.$tid.'\'';
					if ($xid !== null) {
						$result .= ', \'<translate id="THEME_DELETE_CONFIRMATION_TITLE" escape="js">Do you really want to delete this theme?</translate>\'';
						$result .= ', \'<translate id="THEME_DELETE_CONFIRMATION_TEXT" escape="js">This action can\'t be undone! The theme you\'ve posted will be deleted permanently.</translate>\'';					
					} else {
						$result .= ', \'<translate id="FEATURE_DELETE_CONFIRMATION_TITLE" escape="js">Do you really want to delete this feature suggestion?</translate>\'';
						$result .= ', \'<translate id="FEATURE_DELETE_CONFIRMATION_TEXT" escape="js">This action can\'t be undone! The feature suggestion you\'ve posted will be deleted permanently.</translate>\'';					
					}
					$result .= ', \'<translate id="THEME_DELETE_CONFIRMATION_YES" escape="js">Yes, go ahead</translate>\'';
					$result .= ', \'<translate id="THEME_DELETE_CONFIRMATION_NO" escape="js">No</translate>\'';
					$result .= ');">';
					$result .= '<img id="delete_'.$tid.'" class="delete" src="'.$GRAPHICS_PATH.'delete-big.png"></a>';
					$result .= '</div> <!-- theme_delete -->';
				} else {
					$result .= '<div class="theme_vote">';
					try {
						$vote = ThemeVote::get($tid, $user->getUid());
						if ($vote->getPoints() < 0) {
							$result .= '<img id="up_'.$tid.'" class="up_vote" src="'.$GRAPHICS_PATH.'up-grey.gif">';
							$result .= '<img id="down_'.$tid.'" class="down_vote effective_vote" src="'.$GRAPHICS_PATH.'down.gif">';
						} else {
							$result .= '<img id="up_'.$tid.'" class="up_vote effective_vote" src="'.$GRAPHICS_PATH.'up.gif">';
							$result .= '<img id="down_'.$tid.'" class="down_vote" src="'.$GRAPHICS_PATH.'down-grey.gif">';
						}
					} catch (ThemeVoteException $e) {
						$result .= '<img id="up_'.$tid.'" class="up_vote" src="'.$GRAPHICS_PATH.'up-grey.gif">';
						$result .= '<img id="down_'.$tid.'" class="down_vote" src="'.$GRAPHICS_PATH.'down-grey.gif">';
					}
					$result .= '</div> <!-- theme_vote -->';
				}
				
				$result .= '<div id="score_'.$tid.'" class="'.($score < 0?'theme_negative_score':'theme_score').'">';
				$result .= $score;
				$result .= '</div>';
				$result .= '</div>';
			}
		}
		
		$result .= '</div> <!-- theme_list -->	';
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
        	$result = I18N::translateHTML($user, $tagged_html);
		}
		
		return $result;
	}
	
	public static function RenderExif($competition, $user, $exif, $preprocess=false) {
		global $EXIF_NAME;
		global $EXIF_FLASH;
		global $COMPETITION_STATUS;
		
		$result = '';
		
		$written = false;
		
		$exif_names = array_keys($exif);
		
		sort($exif_names);
		
		foreach ($exif_names as $name) {
			$value = $exif[$name];
		
			if ($value !== null) {
				$written = true;
				$result .= '<div><span class="exif_name">';
				$result .= '<translate id="EXIF_'.$name.'">';
				$result .= $EXIF_NAME[$name];
				$result .= '</translate>';
				if (strcmp($name,'DateTimeOriginal') == 0)
					$value = gmdate('j M Y', $value);
				elseif (strcmp($name,'ExposureTime') == 0) {
					if ($value >= 1)
						$value = $value.'s';
					elseif ($value == 0)
						$value = '0ms';
					else
						$value = ($value * 1000).'ms (1/'.round(1.0/$value).')';
				}
					
				elseif (strcmp($name,'Flash') == 0) {
					if (isset($EXIF_FLASH[$value]))
						$value = '<translate id="EXIF_FLASH_'.$value.'">'.$EXIF_FLASH[$value].'</translate>';
				} elseif (strcmp($name,'FocalLength') == 0)
					$value .= ' mm';
					
				if ((strcasecmp($name, 'Make') == 0 || strcasecmp($name, 'Model') == 0 || strcasecmp($name, 'Software') == 0) && $competition->getStatus() == $COMPETITION_STATUS['VOTING'])
					$value = '<i><translate id="EXIF_HIDDEN">hidden until the end of the voting stage</translate></i>';
					
				$result .= '</span>: '.$value.'</div>';
			}
		}
		
		if (!$written)
			$result .= '<translate id="EXIF_NO_DATA">No EXIF data available for this entry</translate>';
			
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
        	$result = I18N::translateHTML($user, $tagged_html);
		}
			
		return $result;
	}
	
	public static function RenderCommentThreadHeader($user, $entry, $preprocess=false, $reply_to_post=null) {
		global $COMPETITION_STATUS;
		
		$uid = $user->getUid();
		
		$result = '<div id="comments_header" class="comment_thread">';
		$result .= '<div class="listing_thumbnail">';
		$result .= '<profile_picture uid="'.$uid.'" size="small"/>';
		$result .= '</div> <!-- listing_thumbnail -->';
		$result .= '<div class="listing_header listing_header_thumbnail_margin">';
		
		if ($reply_to_post === null) {
			$result .= '<translate id="UI_COMMENT_THREAD_HEADER">';
			$result .= '<user_name uid="'.$uid.'"/> wrote';
			$result .= '</translate>';
		} else {	
			$competition = Competition::get($entry->getCid());
			if ($competition->getStatus() != $COMPETITION_STATUS['CLOSED'] && $reply_to_post->getUid() == $entry->getUid()) {
				$result .= '<translate id="UI_COMMENT_THREAD_HEADER_REPLY_TO_AUTHOR">';
				$result .= 'In reply to the author\'s <a href="javascript:highlightItem($(\'comment_'.$reply_to_post->getOid().'\'), true);">comment</a> <user_name uid="'.$uid.'"/> wrote';
				$result .= '</translate>';
			} else {
				$result .= '<translate id="UI_COMMENT_THREAD_HEADER_REPLY_TO">';
				$result .= 'In reply to <user_name uid="'.$reply_to_post->getUid().'"/>\'s <a href="javascript:highlightItem($(\'comment_'.$reply_to_post->getOid().'\'), true);">comment</a> <user_name uid="'.$uid.'"/> wrote';
				$result .= '</translate>';
			}
		}
		
		$result .= '</div> <!-- listing_header -->';
		
/*		$result .= '<div class="comment_actions">';
		$result .= '<div class="post_action">';
		$result .= '<span id="post_comment">';
		$result .= '<a href="javascript:postComment('.($reply_to_post === null?0:$reply_to_post->getOid()).');">';
		$result .= '<translate id="UI_COMMENT_THREAD_HEADER_POST">';
		$result .= 'Post comment';
		$result .= '</translate>';
		$result .= '</a>';
		$result .= '</span>';
		$result .= '</div> <!-- post_action -->';
		
		if ($reply_to_post !== null) {
			$result .= '<div class="post_action">';
			$result .= '<span id="cancel_reply_to">';
			$result .= '<a href="javascript:replyToComment(0);">';
			$result .= '<translate id="UI_COMMENT_THREAD_HEADER_CANCEL_REPLY_TO">';
			$result .= 'Cancel replying to someone';
			$result .= '</translate>';
			$result .= '</a>';
			$result .= '</span>';
			$result .= '</div> <!-- post_action -->';
		}
		
		$result .= '<div style="display:none" id="post_please_wait" class="post_action">';
		$result .= '<translate id="UI_COMMENT_THREAD_HEADER_PLEASE_WAIT">';
		$result .= 'Please wait while your comment is being sent';
		$result .= '</translate>';
		$result .= '</div> <!-- post_action -->';
		$result .= '</div> <!-- comment_actions -->';*/
		$result .= '</div> <!-- listing_item -->';
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
        	$result = I18N::translateHTML($user, $tagged_html);
		}
		
		return $result;
	}
	
	public static function RenderPointsLeft($user, $preprocess=false) {
		global $PAGE;
		
		$result = '<div id="points_left"><translate id="HEADER_POINTS_LEFT">You have <integer class="points" value="'.$user->getPoints().'"/> points (<a href="'.$PAGE['HELP'].'#points">?</a>)</translate></div>';
	
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
        	$result = I18N::translateHTML($user, $tagged_html);
		}
		
		return $result;
	}
	
	public static function RenderTranslationPercentLeft($user, $percent, $preprocess=false) {
		$result =  '<div id="percent_left">';
		$result .= '<translate id="TRANSLATION_PERCENT_LEFT">';
		$result .= '<float value="'.$percent.'"/>% of the content has been translated into <language_name lid="'.$user->getLid().'"/> so far';
		$result .= '</translate>';
		$result .= '</div>';
	
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
        	$result = I18N::translateHTML($user, $tagged_html);
		}
		
		return $result;
	}
	
	public static function RenderCommentThread($user, $entry, $preprocess=false, $highlight_oid=null, $display_time = true) {
		global $DISCUSSION_POST_STATUS;
		global $USER_STATUS;
		global $COMPETITION_STATUS;
		global $REQUEST;
		
		$eid = $entry->getEid();
		$cid = $entry->getCid();
		$competition = ($cid === null?null:Competition::get($cid));

		$thread = $entry->getDiscussionThread();
		
		$discussionpostlist = DiscussionPostList::getByNidAndStatus($thread->getNid(), $DISCUSSION_POST_STATUS['POSTED']);
		if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
			$anonymouspostlist = DiscussionPostList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['ANONYMOUS']);
			foreach ($anonymouspostlist as $oid => $creation_time) {
				$post = DiscussionPost::get($oid);
				if ($post->getNid() == $thread->getNid())
					$discussionpostlist[$oid] = $creation_time;
			}
		} elseif($user->getStatus() == $USER_STATUS['BANNED']) {
			$anonymouspostlist = DiscussionPostList::getByUidAndStatus($user->getUid(), $DISCUSSION_POST_STATUS['BANNED']);
			foreach ($anonymouspostlist as $oid => $creation_time) {
				$post = DiscussionPost::get($oid);
				if ($post->getNid() == $thread->getNid())
					$discussionpostlist[$oid] = $creation_time;
			}
		}
			
		arsort($discussionpostlist);
		
		$oids = array_keys($discussionpostlist);
		$last_post_oid = array_shift($oids);
		
		$result = '';
		
		foreach ($discussionpostlist as $oid => $creation_time) {
			try {
				$post = DiscussionPost::get($oid);
				$result .= '<div id="comment_'.$oid.'" class="listing_item'.($oid == $highlight_oid?' highlight_item':'').'">';
				
				$insightfulmarklist = InsightfulMarkList::getByOid($oid);
				$insightfulmarkcount = count($insightfulmarklist);
				$insightfulmarked = in_array($user->getUid(), $insightfulmarklist);
				
				if (($competition === null || $competition->getStatus() != $COMPETITION_STATUS['CLOSED']) && $post->getUid() == $entry->getUid()) {
					$result .= '<div class="listing_thumbnail">';
					$result .= '<picture category="profile" size="small"/>';
					$result .= '</div> <!-- listing_thumbnail -->';
					$result .= '<div class="listing_header listing_header_thumbnail_margin author_header">';
					
					$default = false;
					if ($post->getReplyToOid() !== null) {
						try {
							$reply_to_post = DiscussionPost::get($post->getReplyToOid());
							if ($reply_to_post->getUid() == $entry->getUid()) {
								if ($display_time) {
									$result .= '<translate id="DISCUSSION_THREAD_HEADER_ARTIST_REPLY_TO_AUTHOR">';
									$result .= '<duration value="'.(time() - $post->getCreationTime()).'" /> ago, in reply to the author\'s <a href="javascript:highlightItem($(\'comment_'.$post->getReplyToOid().'\'), true);">comment</a>, the author wrote';
									$result .= '</translate>';
								} else {
									$result .= '<translate id="DISCUSSION_THREAD_HEADER_ARTIST_REPLY_TO_AUTHOR_TIMELESS">';
									$result .= 'In reply to the author\'s <a href="javascript:highlightItem($(\'comment_'.$post->getReplyToOid().'\'), true);">comment</a>, the author wrote';
									$result .= '</translate>';								
								}
							} else {
								if ($display_time) {
									$result .= '<translate id="DISCUSSION_THREAD_HEADER_ARTIST_REPLY_TO">';
									$result .= '<duration value="'.(time() - $post->getCreationTime()).'" /> ago, in reply to <user_name uid="'.$reply_to_post->getUid().'"/>\'s <a href="javascript:highlightItem($(\'comment_'.$post->getReplyToOid().'\'), true);">comment</a>, the author wrote';
									$result .= '</translate>';
								} else {
									$result .= '<translate id="DISCUSSION_THREAD_HEADER_ARTIST_REPLY_TO_TIMELESS">';
									$result .= 'In reply to <user_name uid="'.$reply_to_post->getUid().'"/>\'s <a href="javascript:highlightItem($(\'comment_'.$post->getReplyToOid().'\'), true);">comment</a>, the author wrote';
									$result .= '</translate>';								
								}
							}
						} catch (DiscussionPostException $e) {
							$default = true;
						}
					} else $default = true;
					
					if ($default) {
						if ($display_time) {
							$result .= '<translate id="DISCUSSION_THREAD_HEADER_ARTIST">';
							$result .= '<duration value="'.(time() - $post->getCreationTime()).'" /> ago the author wrote';
							$result .= '</translate>';
						} else {
							$result .= '<translate id="DISCUSSION_THREAD_HEADER_ARTIST_TIMELESS">';
							$result .= 'The author wrote';
							$result .= '</translate>';						
						}
					}
					$result .= '</div> <!-- listing_header -->';
				} else {
					$result .= '<div class="listing_thumbnail">';
					$result .= '<profile_picture uid="'.$post->getUid().'" size="small"/>';
					$result .= '</div> <!-- listing_thumbnail -->';
					
					if ($post->getUid() == $entry->getUid()) $style = 'author_header';
					elseif ($insightfulmarkcount > 0) $style = 'insightful_header';
					else $style = '';
					
					$result .= '<div class="listing_header listing_header_thumbnail_margin '.$style.'">';
					
					$default = false;
					if ($post->getReplyToOid() !== null) {
						try {
							$reply_to_post = DiscussionPost::get($post->getReplyToOid());
							if ($reply_to_post->getUid() == $entry->getUid() && ($competition === null || $competition->getStatus() != $COMPETITION_STATUS['CLOSED'])) {
								if ($display_time) {
									$result .= '<translate id="DISCUSSION_THREAD_HEADER_COMMENT_REPLY_TO_AUTHOR">';
									$result .= '<duration value="'.(time() - $post->getCreationTime()).'" /> ago, in reply to the author\'s <a href="javascript:highlightItem($(\'comment_'.$post->getReplyToOid().'\'), true);">comment</a>, <user_name uid="'.$post->getUid().'"/> wrote';
									$result .= '</translate>';
								} else {
									$result .= '<translate id="DISCUSSION_THREAD_HEADER_COMMENT_REPLY_TO_AUTHOR_TIMELESS">';
									$result .= 'In reply to the author\'s <a href="javascript:highlightItem($(\'comment_'.$post->getReplyToOid().'\'), true);">comment</a>, <user_name uid="'.$post->getUid().'"/> wrote';
									$result .= '</translate>';								
								}
							} else {
								if ($display_time) {
									$result .= '<translate id="DISCUSSION_THREAD_HEADER_COMMENT_REPLY_TO">';
									$result .= '<duration value="'.(time() - $post->getCreationTime()).'" /> ago, in reply to <user_name uid="'.$reply_to_post->getUid().'"/>\'s <a href="javascript:highlightItem($(\'comment_'.$post->getReplyToOid().'\'), true);">comment</a>, <user_name uid="'.$post->getUid().'"/> wrote';
									$result .= '</translate>';
								} else {
									$result .= '<translate id="DISCUSSION_THREAD_HEADER_COMMENT_REPLY_TO_TIMELESS">';
									$result .= 'In reply to <user_name uid="'.$reply_to_post->getUid().'"/>\'s <a href="javascript:highlightItem($(\'comment_'.$post->getReplyToOid().'\'), true);">comment</a>, <user_name uid="'.$post->getUid().'"/> wrote';
									$result .= '</translate>';
								
								}
							}
						} catch (DiscussionPostException $e) {
							$default = true;
						}
					} else $default = true;
					
					if ($default) {
						if ($display_time) {
							$result .= '<translate id="DISCUSSION_THREAD_HEADER">';
							$result .= '<duration value="'.(time() - $post->getCreationTime()).'" /> ago <user_name uid="'.$post->getUid().'"/> wrote';
							$result .= '</translate>';
						} else {
							$result .= '<translate id="DISCUSSION_THREAD_HEADER_TIMELESS">';
							$result .= '<user_name uid="'.$post->getUid().'"/> wrote';
							$result .= '</translate>';						
						}
					}
					
					$result .= '</div> <!-- listing_header -->';
				}
				
				if ($insightfulmarkcount > 0) {
					$result .= '<div class="listing_subheader listing_header_thumbnail_margin '.($post->getUid() == $entry->getUid()?'author_header':'insightful_subheader').'">';
					if ($insightfulmarkcount == 1) {
						$result .= '<translate id="ENTRY_INSIGHTFUL_POINTS_SINGULAR">';
						$result .= '1 person marked this comment as insightful';
						$result .= '</translate>';				
					} else {
						$result .= '<translate id="ENTRY_INSIGHTFUL_POINTS">';
						$result .= '<integer value="'.$insightfulmarkcount.'"/> people marked this comment as insightful';
						$result .= '</translate>';
					}
					$result .= '</div> <!-- listing_subheader -->';
				}
				
				$result .= '<div class="post_text">';
				if (strstr($post->getText(), '<p>'))
					$result .= String::cleanhtml($post->getText(), false);
				else
					$result .= String::fromaform($post->getText());
				$result .= '</div> <!-- post_text -->';
				
				if ($post->getUid() == $entry->getUid()) $style = 'author_action';
				elseif ($insightfulmarkcount > 0) $style = 'insightful_action';
				else $style = '';
				
				$result .= '<div class="post_actions">';
				
				if ($user->getUid() == $post->getUid() && $oid == $last_post_oid) {
					$result .= '<div class="post_action'.($post->getUid() == $entry->getUid()?' author_action':'').' '.$style.'">';
					$result .= '<a href="javascript:showConfirmation(\''.$REQUEST['DELETE_COMMENT'].'?oid='.$oid.'\'';
					$result .= ', \'<translate id="DISCUSSION_THREAD_DELETE_CONFIRMATION_TITLE" escape="js">Do you really want to delete this discussion post?</translate>\'';
					$result .= ', \'<translate id="DISCUSSION_THREAD_DELETE_CONFIRMATION_TEXT" escape="js">This action can\'t be undone! The text you\'ve written will be deleted permanently.</translate>\'';
					$result .= ', \'<translate id="DISCUSSION_THREAD_DELETE_CONFIRMATION_YES" escape="js">Yes, go ahead</translate>\'';
					$result .= ', \'<translate id="DISCUSSION_THREAD_DELETE_CONFIRMATION_NO" escape="js">No</translate>\'';
					$result .= ');">';
					$result .= '<translate id="DISCUSSION_THREAD_DELETE_POST">Delete this post</translate></a>';
					$result .= '</div> <!-- post_action -->';
				} elseif ($user->getUid() != $post->getUid() && $user->getStatus() == $USER_STATUS['ACTIVE'] && !$insightfulmarked) {
					$result .= '<div class="post_action '.$style.'">';
					$result .= '<a href="javascript:showPointsTransfer('.$oid.');">';
					$result .= '<translate id="DISCUSSION_THREAD_INSIGHTFUL_MARK">';
					$result .= 'Mark as insightful';
					$result .= '</translate>';
					$result .= '</a>';
					$result .= '</div> <!-- post_action -->';	
				}
				
				if (!$user->getTranslate()) {
					$result .= '<div class="post_action '.$style.'">';
					$result .= '<a href="#" onclick="translateComment('.$oid.'); blur(); return false;">';
					$result .= '<translate id="DISCUSSION_THREAD_TRANSLATE">';
					$result .= 'Translate with <img src="http://www.google.com/uds/css/small-logo.png" style="vertical-align: middle; border: none;"/>';
					$result .= '</translate>';
					$result .= '</a>';
					$result .= '</div> <!-- post_action -->';
				}
				
				$result .= '</div> <!-- post_actions -->';
				
				$result .= '</div> <!-- listing_item -->';
			} catch (DiscussionPostException $e) {}
		}
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
        	$result = I18N::translateHTML($user, $tagged_html);
		}
		
		return $result;
	}
	
	public static function RenderCompetitionShortDescription($user, $competition, $link = null, $show_rules = false, $preprocess = false) {
		global $PAGE;
		global $COMPETITION_STATUS;
		
		$theme = Theme::get($competition->getTid());
		$community = Community::get($competition->getXid());
		
		$result = '<div id="competition_description">';
		$result .= '<div class="listing_item nomargin">';
		$result .= '<picture href="'.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&amp;xid='.$competition->getXid().'" category="community" class="listing_thumbnail" size="small" '.($community->getPid() === null?'':'pid="'.$community->getPid().'"').' />';
		$result .= '<div class="listing_header">';
		if ($community->getXid() == 267) {
			$result .= '<a '.($link !== null?'href="'.$link.'"':'').'><translate id="PRIZE_COMMUNITY_THEME_TITLE'.$theme->getTid().'">'.$theme->getTitle().'</translate></a> ';
		} else {
			$result .= '<theme_title '.($link !== null?'href="'.$link.'"':'').' tid="'.$competition->getTid().'"/>';
		}
		$result .= '</div> <!-- listing_header -->';
		$result .= '<div class="listing_subheader">';
		if ($competition->getStatus() == $COMPETITION_STATUS['OPEN']) {
			$result .= '<translate id="COMPETITION_SHORT_DESCRIPTION_OPEN">';
			$result .= 'Suggested by <user_name uid="'.$theme->getUid().'"/> for <community_name link="true" xid="'.$competition->getXid().'"/>. <duration value="'.($competition->getVoteTime() - gmmktime()).'"/> left to enter this competition.';
			$result .= '</translate>';
		} elseif ($competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
			$result .= '<translate id="GRID_VOTE_SUBHEADER">';
			$result .= 'Suggested by <user_name uid="'.$theme->getUid().'"/> for <community_name link="true" xid="'.$competition->getXid().'"/>. <duration value="'.($competition->getEndTime() - gmmktime()).'"/> left to vote on this competition.';
			$result .= '</translate>';
		} else {
			$result .= '<translate id="COMPETITION_SHORT_DESCRIPTION_CLOSED">';
			$result .= 'Suggested by <user_name uid="'.$theme->getUid().'"/> for <community_name link="true" xid="'.$competition->getXid().'"/>. This competition closed <duration value="'.(gmmktime() - $competition->getEndTime()).'"/> ago.';
			$result .= '</translate>';
		}
		$result .= '</div> <!-- listing_subheader -->';
		$result .= '<div class="listing_content">';
		if ($community->getXid() == 267) {
			$result .= '<translate id="PRIZE_COMMUNITY_THEME_DESCRIPTION'.$theme->getTid().'">'.$theme->getDescription().'</translate>';
		} else {
			$result .= String::fromaform($theme->getDescription());
		}
		$result .= '</div> <!-- listing_content -->';
		$result .= '</div> <!-- listing_item -->';
		$result .= '</div> <!-- competition_description -->';
		
		if ($show_rules) {
			$rules = $community->getRules();

			if (strcmp(trim($rules), '') !=0) {
				$result .= '<div class="warning hintmargin">';
				$result .= '<div class="warning_title">';
				$result .= '<translate id="ENTER_WARNING_RULES">';
				$result .= '<community_name link="true" xid="'.$competition->getXid().'"/> has specific rules';
				$result .= '</translate>';
				$result .= '</div> <!-- warning_title -->';
				$result .= '</div> <!-- warning -->';
				$result .= '<div id="rules">';
				$result .= String::fromaform($rules);
				$result .= '</div> <!-- rules -->';
			}
		}
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
        	$result = I18N::translateHTML($user, $tagged_html);
		}
		
		return $result;
	}
	
	public static function RenderEntryAuthor($user, $uid, $rank, $amount, $preprocess = false, $hideactions = false) {
		global $GRAPHICS_PATH;
		
		$result = '<div class="hint_title">';
		if ($uid !== null)
			$result .= '<profile_picture class="entry_author_picture" rounded="false" uid="'.$uid.'" size="tiny"/>';
		else
			$result .= '<profile_picture class="entry_author_picture" rounded="false" size="tiny"/>';
			
		$result .= '<div class="entry_author_name">';
		if ($uid !== null) {
			try {
				$author = User::get($uid);
			} catch (UserException $e) {
				$author = null;
			}	
			
			if (($author !== null && $author->getDisplayRank()) || $rank <= 3) {
				$result .= '<translate id="ENTRY_AUTHOR_HEADER_RANK">';
				$result .= '<user_name uid="'.$uid.'"/> ranked <b><rank value="'.$rank.'"/> out of <integer value="'.$amount.'" /></b> with this artwork in the following competition';
				$result .= '</translate>';			
			} else {
				$result .= '<translate id="ENTRY_AUTHOR_HEADER">';
				$result .= '<user_name uid="'.$uid.'"/> entered this artwork into the following competition';
				$result .= '</translate>';
			}
		} else {
			$result .= '<translate id="ENTRY_AUTHOR_HEADER_UNKNOWN">';
			$result .= 'A former member entered this artwork into the following competition';
			$result .= '</translate>';
		}
		$result .= '</div> <!-- entry_author_name -->';

		if (!$hideactions) {
			$result .=  '<div class="share">';
			$result .=  '<img class="twitter_icon icon" title="<translate id="ENTRY_SHARE_TWITTER">Share this artwork on twitter</translate>" src="'.$GRAPHICS_PATH.'twitter_32.png">';
			$result .=  '</div>';
			
			$result .=  '<div class="share">';
			$result .=  '<img class="facebook_icon icon" title="<translate id="ENTRY_SHARE_FACEBOOK">Share this artwork on facebook</translate>" src="'.$GRAPHICS_PATH.'facebook_32.png">';
			$result .=  '</div>';
		
			$result .=  '<div class="favorite">';
			$result .=  '<img class="favorite_icon icon" src="'.$GRAPHICS_PATH.'heart_inactive.png">';
			$result .=  '</div>';
			
			$result .= '<div class="purchase">';
			$result .= '<img title="<translate id="ENTRY_PURCHASE_LINK"  escape="htmlentities">Order a canvas print of this artwork</translate>" class="purchase_icon icon" src="'.$GRAPHICS_PATH.'minicart.png">';
			$result .= '</div>';
		}
		
		$result .= '</div> <!-- hint_title -->';
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
        	$result = I18N::translateHTML($user, $tagged_html);
		}
		
		return $result;
	}
	
	public static function RenderDonationPun($user) {
		global $USER_STATUS;
		global $USER_LEVEL;
		global $PAGE;
		global $LANGUAGE;
				
		$membershiplist = CommunityMembershipList::getByUid($user->getUid());
		$levels = UserLevelList::getByUid($user->getUid());
		$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
		
		$outdated_translation = false;
		/*if ($user->getLid() != $LANGUAGE['EN']) {
			$outdated = I18N::getOutdated($user->getLid());
			if (count($outdated) > 50) $outdated_translation = true;
		}*/
		
		$messagelist = array();
		
		if ($user->getStatus() != $USER_STATUS['UNREGISTERED'] && empty($membershiplist)) {
			$messagelist []= '<translate id="TIP_TUTORIAL_1">The first thing you should do is to <a href="'.$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid().'">join a community</a>. It will unlock most of the website\'s features.</translate>';
			$messagelist []= '<translate id="TIP_TUTORIAL_2">You must <a href="'.$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid().'">pick a community you like</a> before you can vote on or enter competitions.</translate>';
		} 
		
		if ($user->getStatus() != $USER_STATUS['UNREGISTERED'] && strcmp(trim($user->getName()), '') == 0) {
			$messagelist []= '<translate id="TIP_TUTORIAL_3">Your name is currently displayed as <string value="'.$user->getUniqueName().'"/>. In order to change that, simply go to the <a href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'">settings</a> page.</translate>';
			$messagelist []= '<translate id="TIP_TUTORIAL_4">Feeling anonymous? Fill in your name and put your profile picture up on the <a href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'">settings</a> page.</translate>';
			$messagelist []= '<translate id="TIP_TUTORIAL_7"><string value="'.$user->getUniqueName().'"/> is hard to pronounce. Why not choose a name on the <a href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'">settings</a> page?</translate>';
		} 
		
		if ($user->getStatus() != $USER_STATUS['UNREGISTERED'] && $user->getPid() === null) {
			$messagelist []= '<translate id="TIP_TUTORIAL_5">Don\'t be the question mark person that nobody remembers! Put a profile picture up on the <a href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'">settings</a> page.</translate>';
			$messagelist []= '<translate id="TIP_TUTORIAL_6">Did you know that you look like a question mark to everyone on this website? Change that on the <a href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'">settings</a> page.</translate>';
			$messagelist []= '<translate id="TIP_TUTORIAL_8">Help people know who you are, put a profile picture on the <a href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'">settings</a> page.</translate>';
		} 
		
		if (!$ispremium && $user->getStatus() != $USER_STATUS['UNREGISTERED'] && time() - $user->getCreationTime() > 86400) {
			$messagelist []= '<translate id="PREMIUM_MESSAGE_1">If you find yourself here at 3am thinking "one more and I go to bed", maybe premium membership is for you.</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_2">Premium members have a star displayed on their profile picture. You could be a star too.</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_3">If you never know what gift to get for his/her birthday, why not give premium membership?</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_4">Premium membership, now with crisis-friendly prices.</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_5">6 months of premium membership is cheaper than most UV filters.</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_6">Premium membership gives you detailed statistics about your progress.</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_7">Premium membership gives you free advertisement for your communities.</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_8">Premium membership gives you unlimited storage.</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_9">(Fact) Premium members are sexier than standard members.</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_10">Premium (noun): superior in quality, higher in value.</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_11">Sponsor your favorite artist on inspi.re and give him/her premium membership.</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
			$messagelist []= '<translate id="PREMIUM_MESSAGE_12">Did you know you can get premium membership for free thanks to a few offers?</translate></span><span id="tip_button"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'#free_membership"><translate id="PREMIUM_LEARN">Learn more</translate></a>';
		} 
		
		if ($outdated_translation) {
			$messagelist []= '<translate id="OUTDATED_TRANSLATION">The website is partially in English because the <language_name lid="'.$user->getLid().'"/> translation is late. If you want to help translate the website, email us at <a href="mailto:translation@inspi.re">translation@inspi.re</a></translate>';
		}
		
		if (!empty($messagelist)) {
			$result = '<div id="tip">';
			$result .= '<span id="tip_title">'.$messagelist[array_rand($messagelist)].'</span>';
			$result .= '</div> <!-- tip -->';
			$result .= '<div class="rounded_bottom_tip"></div>';
			return $result;
		} else return '';
	}
	
	public static function RenderCommunityListing($user, $community) {
		global $PAGE;
		global $LANGUAGE_NAME_FROM_ID;
		global $USER_STATUS;
		global $COMMUNITY_LABEL_NAME;
		global $COMMUNITY_MEMBERSHIP_STATUS;
		
		$xid = $community->getXid();
		$members = CommunityMembershipList::getByXidAndStatus($xid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);
		$member_count = count($members);
		
		if ($user->getStatus() == $USER_STATUS['BANNED']) {
			$members += CommunityMembershipList::getByXidAndStatus($xid, $COMMUNITY_MEMBERSHIP_STATUS['BANNED']);
			$member_count = count($members);
		}
	
		$result = '<div class="listing_item">';
		$result .= '<picture href="'.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&amp;xid='.$xid.'" category="community" class="listing_thumbnail" size="small" '.($community->getPid() === null?'':'pid="'.$community->getPid().'"').' />';
		$result .= '<div class="listing_header"><community_name link="true" xid="'.$xid.'"/></div>';
		$result .= '<translate id="COMMUNITIES_DESCRIPTION_WHEN2">';
		$result .= 'Created <duration value="'.(time() - $community->getCreationTime()).'"/> ago. Administrated by <user_name class="community_administrator" uid="'.$community->getUid().'" /><br/>';
		$result .= '</translate>';
		$result .= '<translate id="COMMUNITIES_DESCRIPTION_LANGUAGE2">';
		$result .= 'The primary language is <language_name lid="'.$community->getLid().'"/>.';
		$result .= '</translate>';
		$labellist = CommunityLabelList::getByXid($xid);
		if (!empty($labellist)) {
			$result .= ' <translate id="COMMUNITIES_DESCRIPTION_LABELS">';
			$result .= 'The list of keywords for this community is:';
			$result .= ' </translate>';
			$current = 0;
			foreach ($labellist as $clid) {
				$result .= '<translate id="COMMUNITY_LABEL_'.$clid.'">'.$COMMUNITY_LABEL_NAME[$clid].'</translate>';
				$current++;
				if ($current != count($labellist)) {
					$result .= ', ';
				} else {
					$result .= '.';
				}
			}
			
		}
		$result .= '<br/>';
		
		$active_member_count = $community->getActiveMemberCount();
		
		if ($active_member_count == 1) {
			$result .= '<translate id="COMMUNITIES_DESCRIPTION_MEMBERSHIP_SINGULAR">';
			$result .= '1 active member (<integer value="'.$member_count.'"/> registered)';
			$result .= '</translate>';
		} else {
			$result .= '<translate id="COMMUNITIES_DESCRIPTION_MEMBERSHIP_PLURAL">';
			$result .= '<integer value="'.$active_member_count.'"/> active members (<integer value="'.$member_count.'"/> registered)';
			$result .= '</translate>';
		}
		
		$result .= '</div> <!-- listing_item -->';
		
		return $result;
	}
	
	public static function RenderJoinableCommunityLink($i, $page_offset, $page_count) {
		return ($i == $page_offset?'<b>'.$i.'</b>':'<a href="javascript:orderJoinableCommunities(current_order, '.$i.', restrict_language, restrict_labels);">'.$i.'</a>').($i == $page_count?'':' ');
	}
	
	public static function RenderJoinableCommunityPaging($user, $page_offset, $restrict_language = false, $restrict_labels = array(), $preprocess = false) {
		global $COMMUNITY_STATUS;
		global $USER_STATUS;
		
		$member_of = array_keys(CommunityMembershipList::getByUid($user->getUid()));
		if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
			$owner = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ANONYMOUS']);
		} else {
			$owner = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ACTIVE']);
			$owner = array_merge($owner, CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['INACTIVE']));
		}
		
		$community_list = array_unique(array_merge($member_of, $owner));
		
		// We merge the "all joinable" list and the "own language joinable" into a single list so that the same language floats to the top
		$all_community_list = array_merge(CommunityList::getByStatus($COMMUNITY_STATUS['INACTIVE']), CommunityList::getByStatus($COMMUNITY_STATUS['ACTIVE']));
		$all_community_list = array_diff($all_community_list, $community_list);
		$own_language_community_list = array_merge(CommunityList::getByLidAndStatus($user->getLid(), $COMMUNITY_STATUS['INACTIVE']), CommunityList::getByLidAndStatus($user->getLid(), $COMMUNITY_STATUS['ACTIVE']));
		$own_language_community_list = array_diff($own_language_community_list, $community_list);
		$community_list = array_unique(array_merge($own_language_community_list, $all_community_list));

		if ($restrict_language) {
			$filtered_list = array();
			
			foreach ($community_list as $xid) try {
				$community = Community::get($xid);
				if ($community->getLid() == $user->getLid())
					$filtered_list []= $xid;
			} catch (CommunityException $e) {}
			
			$community_list = $filtered_list;
		}
		
		if (!empty($restrict_labels)) {
			$filtered_list = array();
			
			foreach ($community_list as $xid) {
				$labellist = CommunityLabelList::getByXid($xid);
				if (count(array_intersect($labellist, $restrict_labels)) == count($restrict_labels))
					$filtered_list []= $xid;
			}
			
			$community_list = $filtered_list;
		}

		$pagecount = ceil(count($community_list) / UserPaging::getPagingValue($user->getUid(), 'COMMUNITIES_COMMUNITIES'));
		
		$result = UI::RenderPaging($page_offset, $pagecount, array('UI', 'RenderJoinableCommunityLink'));
					
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
			$result = I18N::translateHTML($user, $tagged_html);
		}
		
		return $result;
	}
	
	public static function RenderJoinableCommunityList($user, $order, $page, $restrict_language = false, $restrict_labels = array(), $preprocess = false) {
		global $COMMUNITY_STATUS;
		global $POINTS_VALUE_ID;
		global $PAGE;
		global $USER_LEVEL;
		global $USER_STATUS;
		global $COMMUNITY_ORDER;
		global $COMMUNITY_MEMBERSHIP_STATUS;
		
		$result = '<div id="joinable_community_list">';
	
		$member_of = array_keys(CommunityMembershipList::getByUid($user->getUid()));
		if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
			$owner = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ANONYMOUS']);
		} else {
			$owner = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['INACTIVE']);
			$owner = array_merge($owner, CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ACTIVE']));
		}
		
		$community_list = array_unique(array_merge($member_of, $owner));
		
		// We merge the "all joinable" list and the "own language joinable" into a single list so that the same language floats to the top
		$all_community_list = array_merge(CommunityList::getByStatus($COMMUNITY_STATUS['INACTIVE']), CommunityList::getByStatus($COMMUNITY_STATUS['ACTIVE']));
		$all_community_list = array_diff($all_community_list, $community_list);
		$own_language_community_list = array_merge(CommunityList::getByLidAndStatus($user->getLid(), $COMMUNITY_STATUS['INACTIVE']), CommunityList::getByLidAndStatus($user->getLid(), $COMMUNITY_STATUS['ACTIVE']));
		$own_language_community_list = array_diff($own_language_community_list, $community_list);
		$community_list = array_unique(array_merge($own_language_community_list, $all_community_list));
		
		// Sort the communities according to the user's criteria
		
		$communities = array();
		$sortedcommunities = array();
		
		foreach ($community_list as $xid) try {
			$communities[$xid] = Community::get($xid);
			$labellist = CommunityLabelList::getByXid($xid);
			
			if ($restrict_language && $communities[$xid]->getLid() != $user->getLid()) {
				unset($communities[$xid]);
			} elseif (!empty($restrict_labels) && count(array_intersect($labellist, $restrict_labels)) != count($restrict_labels)) {
				unset($communities[$xid]);
			} else {
				if ($order == $COMMUNITY_ORDER['RECENT'] || $order == $COMMUNITY_ORDER['OLD']) {
					$sortedcommunities[$xid] = $communities[$xid]->getCreationTime();
				} elseif ($COMMUNITY_ORDER['BIG'] || $order == $COMMUNITY_ORDER['SMALL']) {
					$sortedcommunities[$xid] = $communities[$xid]->getActiveMemberCount();
				}
			}
		} catch (CommunityException $e) {}
		
		switch ($order) {
			case $COMMUNITY_ORDER['RECENT']:
			case $COMMUNITY_ORDER['BIG']:
				arsort($sortedcommunities);
				break;
			case $COMMUNITY_ORDER['OLD']:
			case $COMMUNITY_ORDER['SMALL']:
				asort($sortedcommunities);
				break;
		}
		
		$count_per_page = UserPaging::getPagingValue($user->getUid(), 'COMMUNITIES_COMMUNITIES');
		if (count($sortedcommunities) > $count_per_page)
			$sortedcommunities = array_slice($sortedcommunities, ($page - 1) * $count_per_page, $count_per_page, true);
			
		if (empty($sortedcommunities)) {
			$result .= '<div class="listing_item">';
			$result .= '<div class="listing_header">';
			$result .= '<translate id="COMMUNITIES_NO_JOINABLE">';
			$result .= 'There are no such communities that you can join.';
			$result .= '</translate> ';
			
			$pointsvalue = PointsValue::get($POINTS_VALUE_ID['COMMUNITY_CREATING']);
			$points_create_community = -$pointsvalue->getValue();
			
			$levels = UserLevelList::getByUid($user->getUid());
			
			if (in_array($USER_LEVEL['ADMINISTRATOR'], $levels) || $user->getPoints() >= $points_create_community) {
				$result .= '<translate id="COMMUNITIES_NO_JOINABLE_SUGGESTION">';
				$result .= 'Why not <a href="'.$PAGE['EDIT_COMMUNITY'].'?lid='.$user->getLid().'">create one</a>?';
				$result .= '</translate>';
			}
			$result .= '</div> <!-- listing_header -->';
			$result .= '</div> <!-- listing_item -->';
		} else foreach ($sortedcommunities as $xid => $ignored) {
			$result .= UI::RenderCommunityListing($user, $communities[$xid]);
		}
		
		$result .= '</div>';
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
        	$result = I18N::translateHTML($user, $tagged_html);
		}
		
		return $result;
	}
	
	public static function RenderPaging($page_offset, $page_count, $user_func, $above=false) {
		$result = '';
		
		if ($page_count > 1 && $page_count < 10) {
			$result .= '<div id="page_navigation" class="'.($above?'above_menu page_above_navigation':'hanging_menu page_navigation').'"> <translate id="PAGE_NAVIGATION">Page</translate> ';
			for ($i = 1; $i <= $page_count; $i++) $result .= call_user_func($user_func, $i, $page_offset, $page_count);
			$result .= '</div>';
		} elseif ($page_count > 9) {
			$result .= '<div id="page_navigation" class="'.($above?'above_menu page_above_navigation':'hanging_menu page_navigation').'"> <translate id="PAGE_NAVIGATION">Page</translate> ';
			$page_ids = array(1, $page_count);
			
			for ($i = max($page_offset - 3, 1); $i <= min($page_offset + 3, $page_count); $i++)
				if (!in_array($i, $page_ids)) $page_ids[]=$i;
				
			sort($page_ids);
			
			$last_id = 1;
			foreach ($page_ids as $id) {
				if (abs($last_id - $id) > 1) $result .= '... ';
				$last_id = $id;
				
				$result .= call_user_func($user_func, $id, $page_offset, $page_count);
			}
			$result .= '</div>';
		} else return '<div id="page_navigation" class="'.($above?'above_menu page_above_navigation':'hanging_menu page_navigation').'" style="display:none"></div>';
		
		return $result;
	}
	
	public static function RenderPrivateMessageList($pmlist, $page_offset, $folded, $inbox=true) {
		global $PAGE;
		global $REQUEST;
		global $PRIVATE_MESSAGE_STATUS;
		
		$result = '';
		
		$privatemessage = PrivateMessage::getArray(array_keys($pmlist));
		
		foreach ($pmlist as $pmid => $creation_time) if (isset($privatemessage[$pmid])) {
			
			$result .= '<div class="listing_item clearboth">';
			$result .= '<div class="listing_thumbnail">';
			$result .= '<profile_picture uid="'.($inbox?$privatemessage[$pmid]->getSourceUid():$privatemessage[$pmid]->getDestinationUid()).'" size="small"/>';
			$result .= '</div> <!-- listing_thumbnail -->';
			
			if ($inbox && $privatemessage[$pmid]->getStatus() == $PRIVATE_MESSAGE_STATUS['NEW']) $style = 'insightful_header';
			else $style = '';
			
			$result .= '<div id="message_header_'.$pmid.'" class="listing_header listing_header_thumbnail_margin '.$style.'">';
			
			$result .= String::fromaform($privatemessage[$pmid]->getTitle());
			
			if (!$inbox) {
				$result .= '<span class="delete_private_message">(<a href="javascript:showConfirmation(\''.$REQUEST['DELETE_OUTBOX_MESSAGE'].'?pmid='.$pmid.'&pmpage='.$page_offset.'\'';
				$result .= ', \'<translate id="OUTBOX_DELETE_MESSAGE_TITLE" escape="js">Do you really want to delete this message?</translate>\'';
				$result .= ', \'<translate id="OUTBOX_DELETE_MESSAGE_TEXT" escape="js">The message will be removed from your outbox permanently. This cannot be undone!</translate>\'';
				$result .= ', \'<translate id="ACCOUNT_DELETE_CONFIRMATION_YES" escape="js">Yes, go ahead</translate>\'';
				$result .= ', \'<translate id="ACCOUNT_DELETE_CONFIRMATION_NO" escape="js">No</translate>\'';
				$result .= ');"><translate id="OUTBOX_DELETE_MESSAGE">delete</translate></a>)</span>';	
			}
			
			$result .= '</div> <!-- listing_header -->';
			
			$result .= '<div class="listing_subheader listing_header_thumbnail_margin">';
	
			if ($inbox) {
				$result .= '<translate id="HOME_PRIVATE_MESSAGE_SUBHEADER">';
				$result .= 'Private message sent <duration value="'.(time() - $creation_time).'" /> ago by <user_name uid="'.$privatemessage[$pmid]->getSourceUid().'"/>';
				$result .= '</translate>';
			} else {
				$result .= '<translate id="HOME_PRIVATE_MESSAGE_SUBHEADER_DESTINATION">';
				$result .= 'Private message sent <duration value="'.(time() - $creation_time).'" /> ago to <user_name uid="'.$privatemessage[$pmid]->getDestinationUid().'"/>';
				$result .= '</translate>';
			}
			
			$result .= '</div> <!-- listing_subheader -->';
			
			if ($folded) {
				$result .= '<div class="message_actions">';
				$result .= '<a id="open_'.$pmid.'" href="javascript:showPrivateMessage('.$pmid.');">';
				$result .= '<translate id="HOME_PRIVATE_MESSAGE_OPEN">';
				$result .= 'Open';
				$result .= '</translate>';
				$result .= '</a>';
				$result .= ' - ';
				$result .= '<a href="'.$PAGE['NEW_PRIVATE_MESSAGE'].'?pmid='.$pmid.'&home=true">';
				$result .= '<translate id="HOME_PRIVATE_MESSAGE_REPLY">';
				$result .= 'Reply';
				$result .= '</translate>';
				$result .= '</a>';
				$result .= ' - ';
				$result .= '<a href="javascript:showConfirmation(\''.$REQUEST['DELETE_PRIVATE_MESSAGE'].'?pmid='.$pmid.'&pmpage='.$page_offset.'\'';
				$result .= ', \'<translate id="PRIVATE_MESSAGE_DELETE_CONFIRMATION_TITLE" escape="js">Do you really want to delete this private message?</translate>\'';
				$result .= ', \'<translate id="PRIVATE_MESSAGE_DELETE_CONFIRMATION_TEXT" escape="js">This can\'t be undone!</translate>\'';
				$result .= ', \'<translate id="PRIVATE_MESSAGE_DELETE_CONFIRMATION_YES" escape="js">Yes, go ahead</translate>\'';
				$result .= ', \'<translate id="PRIVATE_MESSAGE_DELETE_CONFIRMATION_NO" escape="js">No</translate>\'';
				$result .= ');"><translate id="PRIVATE_MESSAGE_DELETE_LINK">Delete</translate></a>';	
				$result .= '</div> <!-- message_actions -->';
			}
			
			$result .= '<div id="message_'.$pmid.'" class="message_contents" '.($folded?'style="display:none"':'').'>';
			
			$result .= String::fromaform($privatemessage[$pmid]->getText());
			
			$result .= '</div> <!-- message_contents -->';
			
			$result .= '</div> <!-- listing_item -->';
		}
		
		return $result;
	}
	
	public static function RenderCurrencyPayment($user, $currency, $urladdition, $preprocess = false) {
		global $CURRENCY;
		global $GRAPHICS_PATH;
		
		switch ($currency) {
			case $CURRENCY['EUR']:
				$result = '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597086'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_MONTH_EUR">Purchase one month of premium membership for <integer value="4"/> € (Euros)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597131'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_6_MONTHS_EUR">Purchase six months of premium membership for <integer value="20"/> € (Euros)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597158'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_YEAR_EUR">Purchase one year of premium membership for <integer value="30"/> € (Euros)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597198'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_LIFETIME_EUR">Purchase lifetime premium membership for <integer value="100"/> € (Euros)</translate></a><br/>';
				break;
			case $CURRENCY['USD']:
				$result = '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597100'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_MONTH_USD">Purchase one month of premium membership for $<integer value="5"/> (US dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597141'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_6_MONTHS_USD">Purchase six months of premium membership for $<integer value="25"/> (US dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597164'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_YEAR_USD">Purchase one year of premium membership for $<integer value="40"/> (US dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597201'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_LIFETIME_USD">Purchase lifetime premium membership for $<integer value="125"/> (US dollars)</translate></a><br/>';
				break;
			case $CURRENCY['CAD']:
				$result = '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597106'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_MONTH_CAD">Purchase one month of premium membership for $<integer value="6"/> (Canadian dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597142'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_6_MONTHS_CAD">Purchase six months of premium membership for $<integer value="30"/> (Canadian dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597168'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_YEAR_CAD">Purchase one year of premium membership for $<integer value="50"/> (Canadian dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597210'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_LIFETIME_CAD">Purchase lifetime premium membership for $<integer value="150"/> (Canadian dollars)</translate></a><br/>';
				break;
			case $CURRENCY['AUD']:
				$result = '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597109'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_MONTH_AUD">Purchase one month of premium membership for $<integer value="8"/> (Australian dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597146'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_6_MONTHS_AUD">Purchase six months of premium membership for $<integer value="40"/> (Australian dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597171'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_YEAR_AUD">Purchase one year of premium membership for $<integer value="60"/> (Australian dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597215'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_LIFETIME_AUD">Purchase lifetime premium membership for $<integer value="200"/> (Australian dollars)</translate></a><br/>';
				break;
			case $CURRENCY['NZD']:
				$result = '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597117'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_MONTH_NZD">Purchase one month of premium membership for $<integer value="10"/> (New-Zealand dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597149'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_6_MONTHS_NZD">Purchase six months of premium membership for $<integer value="50"/> (New-Zealand dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597184'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_YEAR_NZD">Purchase one year of premium membership for $<integer value="75"/> (New-Zealand dollars)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597220'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_LIFETIME_NZD">Purchase lifetime premium membership for $<integer value="250"/> (New-Zealand dollars)</translate></a><br/>';
				break;
			case $CURRENCY['GBP']:
				$result = '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597122'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_MONTH_GBP">Purchase one month of premium membership for £<integer value="4"/> (British pounds)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597156'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_6_MONTHS_GBP">Purchase six months of premium membership for £<integer value="20"/> (British pounds)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597193'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_1_YEAR_GBP">Purchase one year of premium membership for £<integer value="25"/> (British pounds)</translate></a><br/>';
				$result .= '<a class="purchase_link" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3597223'.$urladdition.'">';
				$result .= '<img src="'.$GRAPHICS_PATH.'cart.png"> <translate id="PREMIUM_LIFETIME_GBP">Purchase lifetime premium membership for £<integer value="90"/> (British pounds)</translate></a><br/>';
				break;
		}
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$result = INML::processHTML($user, $translated_html);
		}
		
		return $result;
	}
	
	public static function RenderUserLink($uid, $suffix=false) {
		global $USER_LEVEL;
		global $PAGE;
		global $WEBSITE_PATH;
		
		$levels = UserLevelList::getByUid($uid);
		$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
		
		try {
			$user = User::get($uid);
			
			if ($ispremium && strcmp($user->getCustomURL(), '') != 0)
				return $WEBSITE_PATH.$user->getCustomURL().($suffix?'?b0':'');
		} catch (UserException $e) {}
		
		return '/Member/s2-u'.$uid;
	}

	public static function RenderVoteRepartition($user, $eid, $creation_time = null, $preprocess = false) {
		global $ENTRY_VOTE_STATUS;
		
		if ($creation_time !== null)
			$votelist = EntryVoteList::getByEidAndStatusAndCreationTime($eid, $ENTRY_VOTE_STATUS['CAST'], $creation_time);
		else
			$votelist = EntryVoteList::getByEidAndStatus($eid, $ENTRY_VOTE_STATUS['CAST']);
	
		$votequantities = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
		
		foreach ($votelist as $uid => $vote) $votequantities[$vote]++;
		
		$maxvotequantity = max($votequantities);
		
		$result = '<img src="http://chart.apis.google.com/chart?chs=160x150&chxr=0,0,'.($maxvotequantity + 1).'&chd=t:'.implode(',', $votequantities).'&chds=0,'.($maxvotequantity + 1).'&cht=bvg&chl=1|2|3|4|5&chco=ffcffc&chm=t'.$votequantities[1].',000000,0,0,11|t'.$votequantities[2].',000000,0,1,11|t'.$votequantities[3].',000000,0,2,11|t'.$votequantities[4].',000000,0,3,11|t'.$votequantities[5].',000000,0,4,11"/>';
		$result .= '</div>';
		$result .= '<div class="vote_repartition">';
		$result .= '<translate id="ENTRY_STATISTICS_EXPLANATION">';
		$result .= 'The chart above represents how people voted. The horizontal axis is the amount of stars voters gave. The vertical axis shows how many voters picked each star option.';
		$result .= '</translate>';
		
		if (count($votelist) > 0) {
			$result .= '<br/><br/>';
			$result .= '<translate id="ENTRY_STATISTICS_TOTAL">';
			$result .= '<integer value="'.count($votelist).'"/> vote(s) have been cast on this entry, bringing it to a total score of <integer value="'.array_sum($votelist).'"/>.';
			$result .= '</translate>';
			$result .= '<br/>';
			$result .= '<translate id="ENTRY_STATISTICS_AVERAGE">';
			$result .= 'The average vote for this entry is <float value="'.round(array_sum($votelist)/count($votelist), 2).'"/> star(s).';
			$result .= '</translate>';
		}
		
		if ($preprocess) {
			$translated_html = I18N::translateHTML($user, $result);
			$tagged_html = INML::processHTML($user, $translated_html);
        	$result = I18N::translateHTML($user, $tagged_html);
		}
		
		return $result;
	}
}
?>
