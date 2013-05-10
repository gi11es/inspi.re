<?php
    
/* 
 	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Handles the page buffering and rendering so that everything in the page source is in the right place
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/db.php');
require_once(dirname(__FILE__).'/../utilities/inml.php');
require_once(dirname(__FILE__).'/../utilities/jsmin.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../utilities/template.php');
require_once(dirname(__FILE__).'/../utilities/timecounter.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');
    
class Page {
    protected $stylenames = array();
    protected $jsnames = array();
    protected $jsheadnames = array();
    protected $jsvariables = array();
    protected $rss = array();
    protected $page = null;
    protected $user = null;
    protected $title = null;
    protected $html = '';
    protected $start_time;
    protected $top_chunk = '';
    protected $bottom_chunk = '';
    protected $validity_time = 0;
    
    public function __construct($page, $submenu, $user) {
    	global $GRAPHICS_PATH;
    	global $JS_3RDPARTY_PATH;
    	global $JS_3RDPARTY_LOCAL_PATH;
    	global $JS_GENERATED_LOCAL_PATH;
    	global $JS_GENERATED_PATH;
    	global $JS;
    	global $_SERVER;
    	global $_COOKIE;
    	global $_REQUEST;
    	global $USER_STATUS;
    	global $HOST_COOKIE_NAME;
    	global $HOST_COOKIE_EXPIRY;
    	global $REQUEST;
    	global $WEB_HISTORY_CHECK_FREQUENCY;
    	global $WEB_HISTORY_CHECK;
    	global $GOOGLE_UID;
    	global $COOKIE_DOMAIN;
    	global $IP_BLACKLIST;
    	global $ENTRY_VOTE_STATUS;
    	global $ENTRY_STATUS;
    	global $ALERT_TEMPLATE_ID;
    	global $ALERT_INSTANCE_STATUS;
    	global $SUBMENU;
    	
    	if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $IP_BLACKLIST)) {
    		header('Location: http://www.google.com');
    		exit(0);
    	}
    	
    	$this->start_time = microtime(true);
        $this->user = $user;
        $this->page = $page;
        
        $this->submenu = $SUBMENU[$submenu];
        $this->user->setSubmenuHistory($this->submenu, $this->page);
	
        $this->addStyle('MAIN');
        
        $this->addJavascriptVariable('js_3rdparty_path', $JS_3RDPARTY_PATH);
        $this->addJavascriptVariable('request_update_lid', $REQUEST['UPDATE_LID']);
        $this->addJavascriptVariable('graphics_path', $GRAPHICS_PATH);
        
        $this->addJavascriptvariable('user_points', $this->user->getPoints());
        $this->addJavascriptVariable('request_get_alerts', $REQUEST['GET_ALERTS']);
        $this->addJavascriptVariable('request_get_points', $REQUEST['GET_POINTS']);
        $this->addJavascriptVariable('request_delete_alert', $REQUEST['DELETE_ALERT']);
        $this->addJavascriptVariable('translation_original', '<translate id="AUTO_TRANSLATION_ORIGINAL_TEXT" escape="htmlentities">Original text:</translate>');
        $this->addJavascriptVariable('translation_translated', '<translate id="AUTO_TRANSLATION_TRANSLATED" escape="htmlentities">Translation:</translate>');
        $this->addJavascriptVariable('translation_failed', '<translate id="AUTO_TRANSLATION_FAILED" escape="htmlentities">Original text (translation couldn\'t be performed):</translate>');
        $this->addJavascriptVariable('ads_blocked', '<translate id="ADS_BLOCKED" escape="htmlentities">You seem to be using ad blocking software. Inspi.re is supported by ads and premium membership. If too many members avoid both, we won\'t be able to pay for our infrastructure and our staff. Please disable ad blocking for http://inspi.re in your ad filtering software. This message will stop appearing when you do.</translate>');
        
        if ($user->getStatus() == $USER_STATUS['ACTIVE'] && time() - $user->getWebHistoryCheckLastTime() > $WEB_HISTORY_CHECK_FREQUENCY) {
        	$this->addJavascriptvariable('history_check', json_encode($WEB_HISTORY_CHECK));
        	$this->addJavascriptVariable('request_update_web_history', $REQUEST['UPDATE_WEB_HISTORY']);
        }

		$this->addStyle($page);
		$this->addJavascript($page);
	
		$time_signature = filemtime($JS_3RDPARTY_LOCAL_PATH.'prototype.js');
		$time_signature .= filemtime($JS_3RDPARTY_LOCAL_PATH.'builder.js');
		$time_signature .= filemtime($JS_3RDPARTY_LOCAL_PATH.'effects.js');
		$time_signature .= filemtime($JS_3RDPARTY_LOCAL_PATH.'dragdrop.js');
		$time_signature .= filemtime($JS_3RDPARTY_LOCAL_PATH.'controls.js');
		$time_signature .= filemtime($JS_3RDPARTY_LOCAL_PATH.'slider.js');
		$time_signature .= filemtime($JS_3RDPARTY_LOCAL_PATH.'sound.js');
		$time_signature .= filemtime($JS_3RDPARTY_LOCAL_PATH.'effect.scroll.js');
		$time_signature = md5($time_signature);
		
		if (!file_exists($JS_GENERATED_LOCAL_PATH.$time_signature.'.js')) {
			System::mergeFiles($JS_GENERATED_LOCAL_PATH.$time_signature.'.js', 
								array($JS_3RDPARTY_LOCAL_PATH.'prototype.js',
									$JS_3RDPARTY_LOCAL_PATH.'builder.js',
									$JS_3RDPARTY_LOCAL_PATH.'effects.js',
									$JS_3RDPARTY_LOCAL_PATH.'dragdrop.js',
									$JS_3RDPARTY_LOCAL_PATH.'controls.js',
									$JS_3RDPARTY_LOCAL_PATH.'slider.js',
									$JS_3RDPARTY_LOCAL_PATH.'sound.js',
									$JS_3RDPARTY_LOCAL_PATH.'effect.scroll.js',
									));
									
			file_put_contents($JS_GENERATED_LOCAL_PATH.$time_signature.'.js', 
			JSMin::minify(file_get_contents($JS_GENERATED_LOCAL_PATH.$time_signature.'.js'))
			);
		}
		
		$JS[$time_signature] = $JS_GENERATED_PATH.$time_signature.'.js';
		$this->addHeadJavascript($time_signature);
		$this->addHeadJavascript('GOOGLE');
		$this->addHeadJavascript('MAIN');
		
		

		if ($this->user !== null && $this->user->getStatus() == $USER_STATUS['UNREGISTERED'] && isset($_REQUEST['a']) && $this->user->getAffiliateUid() === null) {
			if (strlen($_REQUEST['a']) < 13)
				$aff_uid = hexdec($_REQUEST['a']);
			else
				$aff_uid = $_REQUEST['a'];
				
			$this->user->setAffiliateUid($aff_uid);
		}
		
		// Check if the user has been active enough for the affiliate to get the reward
		if ($this->user !== null && $this->user->getStatus() == $USER_STATUS['ACTIVE'] && $this->user->getAffiliateUid() !== null) {
			$votelist = EntryVoteList::getByUidAndStatus($this->user->getUid(), $ENTRY_VOTE_STATUS['CAST']);
			$entrylist = EntryList::getByUidAndStatus($this->user->getUid(), $ENTRY_STATUS['POSTED']);
			
			if (count($votelist) > 100 && count($entrylist) > 2) try {
				$affiliate_uid = $this->user->getAffiliateUid();
				$affiliate = User::get($affiliate_uid);
				
				$referencetime = max(time(), $affiliate->getPremiumTime());
				$affiliate->setPremiumTime($referencetime + 604800);
				
				if ($affiliate->getStatus() == $USER_STATUS['ACTIVE']) {
					$alert = new Alert($ALERT_TEMPLATE_ID['AFFILIATE_ACTIVE']);
					$aid = $alert->getAid();
					$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
					$alert_instance = new AlertInstance($aid, $affiliate_uid, $ALERT_INSTANCE_STATUS['ASYNC']);
				}
				$this->user->setAffiliateUid(null);
			} catch (UserException $e) {}
		}
		
		if ($this->user !== null && strcmp('82.224.178.228', $_SERVER['REMOTE_ADDR']) != 0 && $_SERVER['REMOTE_ADDR'] !== null && $this->user->getStatus() == $USER_STATUS['ACTIVE'])
			$this->user->updateIP($_SERVER['REMOTE_ADDR'], isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null);
			
		if ($this->user !== null && $this->user->getStatus() == $USER_STATUS['ACTIVE']) {
			$this->user->setLastActivity();
			$this->user->addVisitedPage($_SERVER['REQUEST_URI']);
		}
			
		if (!isset($_COOKIE[$HOST_COOKIE_NAME])) {
			do {
				$value = sha1(microtime());
				$result = UserList::getByHostCookie($value);
			} while (!empty($result));
			
			setcookie($HOST_COOKIE_NAME, $value, $HOST_COOKIE_EXPIRY, '/', $COOKIE_DOMAIN, false, true);
		} else {
			// Only update when we receive it, not when we send it
			// That way if there is a mismatch the flash cookie backup can intervene
			$value = $_COOKIE[$HOST_COOKIE_NAME];
		
			if ($this->user !== null && $this->user->getStatus() == $USER_STATUS['ACTIVE'] && $this->user->getUid() != $GOOGLE_UID)
				$this->user->updateHostCookie($value);
		}
    }
    
    public function addTopChunk($chunk) {
    	$this->top_chunk .= $chunk; 
    }
    
    public function addBottomChunk($chunk) {
    	$this->bottom_chunk .= $chunk; 
    }
    
    public function setUser($user) {
        $this->user = $user;
    }
    
    public function setPage($page) {
		$this->page = $page;   
    }
    
    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function addStyle($stylename) {
        if (!in_array($stylename, $this->stylenames))
          $this->stylenames[]= $stylename;
    }
    
    public function addJavascript($jsname) {
        if (!in_array($jsname, $this->jsnames))
          $this->jsnames[]= $jsname;
    }
    
    public function addRSS($rss) {
    	$this->rss []= $rss;
    }
    
    public function addHeadJavascript($jsheadname) {
        if (!in_array($jsheadname, $this->jsheadnames))
          $this->jsheadnames[]= $jsheadname;
    }
    
    public function addJavascriptVariable($name, $value) {
        $this->jsvariables[$name] = $value;
    }
    
    public function startHTML($validity_time = 0) {
    	global $MAINTENANCE;
    	global $_GET;
    	$this->validity_time = $validity_time;
    	
    	if (!$MAINTENANCE && $validity_time > 0) {
			try {
				$cachedpage = Cache::get('Page-'.$this->page.serialize($_GET));
				$cachedpageage = Cache::get('PageAge-'.$this->page.serialize($_GET));
				
				if ($cachedpageage > time() - $validity_time) {
					$this->html = $cachedpage;
					$this->render(false);
					exit(0);
				}
			} catch (CacheException $e) {}
		}
    	
        ob_start();
    }
    
    public function endHTML() {
        $this->html .= ob_get_contents();
        ob_end_clean();
    }
    
    public function render($cache = true) {
        global $CSS;
        global $CSS_LOCAL;
        global $JS;
        global $JS_LOCAL;
        global $_REQUEST;
        global $_SERVER;
        global $_GET;
        global $WEB_HISTORY_CHECK_FREQUENCY;
        global $USER_STATUS;
        global $MAINTENANCE;
        global $PAGE;
        global $GOOGLE_UID;
        
        if ($MAINTENANCE && strcmp($this->page, 'MAINTENANCE') != 0) {
        	header(I18N::translateHTML('Location: /<translate id="URL_MAINTENANCE" escape="urlify">Maintenance</translate>/s6'));
        	exit(0);
        }
        
        if ($cache && $this->validity_time > 0) {
        	Cache::setorreplace('Page-'.$this->page.serialize($_GET), $this->html);
        	Cache::setorreplace('PageAge-'.$this->page.serialize($_GET), time());
        }
        
        ob_start();

        if ($this->title !== null)
            echo UI::RenderHeaderTop($this->title);
        else
            echo UI::RenderHeaderTop();
            
        echo $this->top_chunk;
        
        // Render the CSS definitions
        foreach ($this->stylenames as $stylename) {
            if (isset($CSS[$stylename])) echo '<link rel="stylesheet" type="text/css" href="'.$CSS[$stylename].'-'.filemtime($CSS_LOCAL[$stylename]).'.css" />';
        }
        
        foreach ($this->rss as $rss) {
        	echo '<link rel="alternate" type="application/rss+xml" title="" id="gallery" href="'.$rss.'&lastchanged='.time().'" />';
        }
        
        // Render the javascript
        foreach ($this->jsheadnames as $jsheadname) {
            if (isset($JS[$jsheadname])) echo '<script type="text/javascript" src="'.$JS[$jsheadname].(isset($JS_LOCAL[$jsheadname])?'-'.filemtime($JS_LOCAL[$jsheadname]).'.js':'').'"></script>';
        }
        
        echo UI::RenderHeaderBottom($this->page, $this->submenu, $this->user);
        
        if ($this->user->getStatus() == $USER_STATUS['ACTIVE'] && time() - $this->user->getWebHistoryCheckLastTime() > $WEB_HISTORY_CHECK_FREQUENCY) echo '<div id="do_history_check"></div>'; // Adding this div triggers the CSS/javascript hack used to check previously visited URLs
        
        echo $this->html;
            
        echo UI::RenderFooterTop($this->page, $this->user);
        
        echo '<form style="display: none" action="">';
        foreach($this->jsvariables as $name => $value) {
            echo '<input type="hidden" id="var_'.$name.'" value=\''.$value.'\' />';
        }
        echo '</form>';
        
        // Render the javascript
        foreach ($this->jsnames as $jsname) {
            if (isset($JS[$jsname])) echo '<script type="text/javascript" src="'.$JS[$jsname].(isset($JS_LOCAL[$jsname])?'-'.filemtime($JS_LOCAL[$jsname]).'.js':'').'"></script>';
        }
        
        echo UI::RenderFooterBottom($this->page, $this->user);  
        
        $total_html = ob_get_contents();
        ob_end_clean();
        
        $force_lid = null;
        if (isset($_REQUEST['lid']) && $_REQUEST['lid'] != $this->user->getLid() && ($this->user->getStatus() == $USER_STATUS['UNREGISTERED'] || $this->user->getUid() == $GOOGLE_UID))
        	$force_lid = $_REQUEST['lid'];
        
        $translated_html = I18N::translateHTML($this->user, $total_html, $force_lid);
        
        $tagged_html = INML::processHTML($this->user, $translated_html);
        
        $final_html = I18N::translateHTML($this->user, $tagged_html, $force_lid);
        header ('Content-type: text/html; charset=utf-8');
        echo $final_html;
        
        $time_difference = microtime(true) - $this->start_time;

        $rendertime = round($time_difference, 3).' - '.Cache::getRequestCount().' - '.DB::getRequestCount().' - '.round(TimeCounter::getTime() / $time_difference * 100, 1).'%';
        
        //echo '<script type="text/javascript"> SetRenderTime(\'',$rendertime,'\'); </script>';
        
        echo $this->bottom_chunk;
    }
}

?>