<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Delete an existing theme
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

if (isset($_REQUEST['tid'])) {
	$theme = Theme::get($_REQUEST['tid']);
	
	$moderatedcommunitylist = CommunityModeratorList::getByUid($user->getUid());
	$ismoderator = in_array($theme->getXid(), $moderatedcommunitylist);
	
	if ($theme->getUid() != $user->getUid() && !$ismoderator) {
		header('Location: '.$PAGE['THEMES'].'?lid='.$user->getLid());
		exit(0);
	}
	
	if ($theme->getStatus() != $THEME_STATUS['DELETED']) {
		if ($theme->getUid() != $user->getUid()) {
			try {
				$theme_user = User::get($theme->getUid());
				
				if ($theme_user->getStatus() == $USER_STATUS['ACTIVE']) {
					$alert = new Alert($ALERT_TEMPLATE_ID['THEME_MODERATED']);
					$aid = $alert->getAid();
					$alert_variable = new AlertVariable($aid, 'tid', $theme->getTid());
					$alert_variable = new AlertVariable($aid, 'xid', $theme->getXid());
					$alert_instance = new AlertInstance($aid, $theme->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);
				}
			} catch (UserException $e) {
				$theme_user = null;
			}
		} else $theme_user = $user;
		
		if ($theme_user !== null) $theme_user->givePoints($theme->getDeletionPoints());
		$theme->setStatus($THEME_STATUS['DELETED']);
	}
	
	$community = Community::get($theme->getXid());
	
	header(I18N::translateHTML($user, 'Location: /'.String::urlify($community->getName()).'/<translate id="URL_THEMELIST" escape="urlify">Upcoming Themes</translate>/s1-l'.$user->getLid().'-x'.$theme->getXid()));	
} else header('Location: '.$PAGE['THEMES'].'?lid='.$user->getLid());

?>