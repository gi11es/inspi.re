<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Activate a premium membership code
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/premiumcode.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevel.php');
require_once(dirname(__FILE__).'/../utilities/inml.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();

if (isset($_REQUEST['code']) && isset($_REQUEST['uid'])) {
	try {
		$premiumcode = PremiumCode::getByDisplayCode($_REQUEST['code']);
		try {
			$uid = $_REQUEST['uid'];
			$member = User::get($uid);
			$duration = $premiumcode->getDuration();
			
			if ($premiumcode->getUid() === null) {
				$referencetime = max(time(), $member->getPremiumTime());
				$member->setPremiumTime($referencetime + $duration);
				$premiumcode->setUid($member->getUid());
				$premiumcode->setMembershipAge(time() - $member->getCreationTime());
				$userlevel = new UserLevel($member->getUid(), $USER_LEVEL['PREMIUM']);
				
				if ($member->getUid() != $user->getUid()) {
					if ($duration > 94608000) {
						$alert = new Alert($ALERT_TEMPLATE_ID['PREMIUM_SPONSORED_LIFETIME']);
						$aid = $alert->getAid();
						$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
						$alert_instance = new AlertInstance($aid, $member->getUid(), $ALERT_INSTANCE_STATUS['NEW']);					
					} else {
						$alert = new Alert($ALERT_TEMPLATE_ID['PREMIUM_SPONSORED']);
						$aid = $alert->getAid();
						$alert_variable = new AlertVariable($aid, 'duration', $duration);
						$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
						$alert_instance = new AlertInstance($aid, $member->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
					}
				} else {
					if ($duration > 94608000) {
						$alert = new Alert($ALERT_TEMPLATE_ID['PREMIUM_ACTIVATED_LIFETIME']);
						$aid = $alert->getAid();
						$alert_instance = new AlertInstance($aid, $member->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
					} else {
						$alert = new Alert($ALERT_TEMPLATE_ID['PREMIUM_ACTIVATED']);
						$aid = $alert->getAid();
						$alert_variable = new AlertVariable($aid, 'duration', $duration);
						$alert_instance = new AlertInstance($aid, $member->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
					}
				}
			} else {
				$uid = $premiumcode->getUid();
				$member = User::get($uid);
			}
			
			if ($member->getUid() == $user->getUid()) {
				if ($duration > 94608000) {
					$result = '<div class="activation_success">';
					$result .= '<translate id="PREMIUM_ACTIVATE_SUCCESS_YOURSELF_LIFETIME">';
					$result .= 'Congratulations! You\'ve successfully activated your premium membership, which is valid indefinitely.';
					$result .= '</translate>';
					$result .= '</div> <!-- activation_success -->';
				
				} else {
					$result = '<div class="activation_success">';
					$result .= '<translate id="PREMIUM_ACTIVATE_SUCCESS_YOURSELF">';
					$result .= 'Congratulations! You\'ve successfully activated your premium membership, which is valid for <duration value="'.$duration.'"/>.';
					$result .= '</translate>';
					$result .= '</div> <!-- activation_success -->';
				}
			} else {
				if ($duration > 94608000) {
					$result = '<div class="activation_success">';
					$result .= '<translate id="PREMIUM_ACTIVATE_SUCCESS_SOMEONE_LIFETIME">';
					$result .= 'Congratulations! You\'ve successfully activated <user_name class="blackusername" uid="'.$uid.'"/>\'s premium membership, which is valid indefinitely.';
					$result .= '</translate>';
					$result .= '</div> <!-- activation_success -->';
				} else {
					$result = '<div class="activation_success">';
					$result .= '<translate id="PREMIUM_ACTIVATE_SUCCESS_SOMEONE">';
					$result .= 'Congratulations! You\'ve successfully activated <user_name class="blackusername" uid="'.$uid.'"/>\'s premium membership, which is now valid for <duration value="'.$duration.'"/>.';
					$result .= '</translate>';
					$result .= '</div> <!-- activation_success -->';
				}
			}
		} catch (UserException $e) {
			$result = '<div class="activation_error">';
			$result .= '<translate id="PREMIUM_ACTIVATE_ERROR_NO_USER">';
			$result .= 'The user you\'re trying to use this premium membership code on doesn\'t exist.';
			$result .= '</translate>';
			$result .= '</div> <!-- activation_error -->';
		}
	} catch (PremiumCodeException $e) {
		$result = '<div class="activation_error">';
		$result .= '<translate id="PREMIUM_ACTIVATE_ERROR">';
		$result .= 'This premium membership code is invalid. Please make sure that you\'ve copied and pasted all of it. It should consist of 4 groups of 5 letters and numbers.';
		$result .= '</translate>';
		$result .= '</div> <!-- activation_error -->';
	}
}

$translated_html = I18N::translateHTML($user, $result);
$translated_html = INML::processHTML($user, $translated_html);
echo I18N::translateHTML($user, $translated_html);

?>