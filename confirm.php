<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Confirmation page for the user registration
*/

require_once(dirname(__FILE__).'/entities/alert.php');
require_once(dirname(__FILE__).'/entities/alertinstance.php');
require_once(dirname(__FILE__).'/entities/alertvariable.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlist.php');
require_once(dirname(__FILE__).'/utilities/email.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

if ($user->getStatus() != $USER_STATUS['UNREGISTERED'])
	header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());

$page = new Page('CONFIRM', 'HOME', $user);

$page->startHTML();

function activateUser($activation_code) {
	global $USER_STATUS;
	global $PAGE;
	global $page;
	global $user;
	global $ALERT_TEMPLATE_ID;
	global $ALERT_INSTANCE_STATUS;
	
	try {
		$user = User::getByActivationCode($activation_code);
		
		$affiliate_uid = $user->getAffiliateUid();
		if ($affiliate_uid !== null) try {
			// Send an alert to the person who told him/her about the website
			$affiliate = User::get($affiliate_uid);
			
			$alert = new Alert($ALERT_TEMPLATE_ID['AFFILIATE_JOIN']);
			$aid = $alert->getAid();
			$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
			$alert_instance = new AlertInstance($aid, $affiliate_uid, $ALERT_INSTANCE_STATUS['NEW']);
		} catch (UserException $e) {}
			
		$user->setStatus($USER_STATUS['ACTIVE']);
		$user->setCreationTime(time());
		UserList::addRecentlyRegistered($user->getUid(), $user->getCreationTime(), 14);
		UserList::addRegistered($user->getUid(), time());
		$user->setActivationCode(null);
		$page = new Page('CONFIRM', 'HOME', $user);
		
		echo '<div class="hint hintmargin">';
		echo '<div class="hint_title">';
		echo '<translate id="CONFIRM_HINT_EMAIL_SUCCESS">Your account is now registered!';
		echo '</translate>';
		echo '</div>';
		echo '<translate id="CONFIRM_HINT_BODY_EMAIL_SUCCESS">';
		echo 'Everything you\'ve done as an unregistered user is saved and you can keep ';
		echo 'using the website, as you\'re already logged in. You can ';
		echo '<a href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'">edit your profile and display name</a> ';
		echo 'if you want (for now you appear as <user_name uid="'.$user->getUid().'" link="false"/> to others).';
		echo '</translate>';
		echo '</div>';
		
		return true;
	} catch (UserException $e) {
		echo '<div class="warning hintmargin">';
		echo '<div class="warning_title">';
		echo '<translate id="CONFIRM_HINT_ACTIVATION_ERROR">';
		echo 'The activation code you\'ve provided is invalid';
		echo '</translate>';
		echo '</div>';
		echo '<translate id="CONFIRM_HINT_BODY_ACTIVATION_ERROR">';
		echo 'Please <a href="'.$PAGE['REGISTER'].'?reset=true">restart the registration process</a>.';
		echo '</translate>';
		echo '</div>';
	}
	
	return false;
}

function showActivationForm($user) {
	global $PAGE;
	
	echo '<div class="hint">';
	echo '<div class="hint_title">';
	echo '<translate id="CONFIRM_HINT_TITLE">An activation email has been sent to</translate> ';
	echo String::htmlentities($user->getEmail());
	echo '</div>';
	echo '<translate id="CONFIRM_HINT_BODY_STEP2">';
	echo 'Step 2 of 2: Open the email we\'ve sent you and click on the link provided. <b>Remember to check your junk/spam folder if you don\'t receive the activation email in your inbox</b>.';
	echo '</translate>';
	echo '<br/><br/>';
	$token = new PersistentToken($user->getActivationCode());
	$activation_address = $token->getHash().'@activation.inspi.re';
	echo '<translate id="CONFIRM_HINT_BODY_STEP2_ALTERNATIVE">';
	echo '<b>An alternative method to activate your account is for you to send an empty email to <a href="mailto:'.$activation_address.'"><string value="'.$activation_address.'"/></a> from the email address you\'ve registered with.</b> Once you\'ve sent the empty email, your account will be automatically activated within seconds. You can keep using the website after you\'ve sent the email, the warnings about being unregistered will disappear as soon as your account is active.';
	echo '</translate>';
	echo '<br/><br/>';
	echo '<translate id="CONFIRM_HINT_BODY_STEP2_RESET">';
	echo '<a href="'.$PAGE['REGISTER'].'?reset=true">Click here</a> if you want to start the registration process from scratch.';
	echo '</translate>';
	echo '</div>';
}

function sendActivationEmail($user) {
	global $PAGE;
	
	do {
		$activation_code = substr(sha1(microtime()), 0, 12);
		
		try {
			User::getByActivationCode($activation_code);
			$new = false;
		} catch (UserException $e) {
			$new = true;
		}
	} while (!$new);
	
	$user->setActivationCode($activation_code);
	
	// Send the activation email
	try {
		Email::mail($user->getEmail(), $user->getLid(), 
					'ACTIVATION', 
					array('activation_link' => $PAGE['CONFIRM'].'?activation_code='.$activation_code)
					);
		showActivationForm($user);
	} catch (EmailException $e) {
		echo '<div class="warning hintmargin">';
		echo '<div class="warning_title">';
		echo '<translate id="CONFIRM_HINT_TITLE_EMAIL_ERROR">';
		echo 'An error occrued while sending the activation email to';
		echo '</translate> ';
		echo String::htmlentities($user->getEmail());
		echo '</div>';
		echo '<translate id="CONFIRM_HINT_BODY_EMAIL_ERROR">';
		echo 'You can <a href="'.$PAGE['CONFIRM'].'?resend=true">click here</a>';
		echo ' to resend an activation email';
		echo '</translate>';
		
		echo '<br/><br/>';
		$token = new PersistentToken($user->getActivationCode());
		$activation_address = $token->getHash().'@activation.inspi.re';
		echo '<translate id="CONFIRM_HINT_BODY_STEP2_ALTERNATIVE">';
		echo '<b>An alternative method to activate your account is for you to send an empty email to <a href="mailto:'.$activation_address.'"><string value="'.$activation_address.'"/></a> from the email address you\'ve registered with.</b> Once you\'ve sent the empty email, your account will be automatically activated within seconds. You can keep using the website after you\'ve sent the email, the warnings about being unregistered will disappear as soon as your account is active.';
		echo '</translate>';
		echo '</div>';
	}
}

$showgoal = false;

if  (  isset($_REQUEST['email']) 
	&& isset($_REQUEST['verify_email']) 
	&& isset($_REQUEST['password']) 
	&& isset($_REQUEST['verify_password'])
	) {
	if (isset($_REQUEST['username']) && strcmp($_REQUEST['username'], '') != 0) {
		// This is a spambot
		$user->setStatus($USER_STATUS['BANNED']);
		header('Location: '.$PAGE['HOME'].'?lid'.$user->getLid());
	}
	
	try {
		$email_user = User::getByEmail(trim(mb_strtolower($_REQUEST['email'], 'UTF-8')));
		if ($email_user->getStatus() == $USER_STATUS['UNREGISTERED']) {
			$email_user->setPassword($_REQUEST['password']);
			
			sendActivationEmail($email_user);
		} else header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());
	} catch (UserException $e) {
		$user->setEmail(trim(mb_strtolower($_REQUEST['email'])));
		$user->setPassword($_REQUEST['password']);
		
		sendActivationEmail($user);
	}
} elseif (isset($_REQUEST['activation_code'])) {
	$showgoal = activateUser($_REQUEST['activation_code']);
} elseif (isset($_REQUEST['resend'])) {
	sendActivationEmail($user);
} elseif (strcmp($user->getEmail(), '') != 0) {
	showActivationForm($user);
}

?>

<?php
$page->endHTML();
$page->render();

if ($showgoal) {
?>

<script>
if(typeof(urchinTracker)!='function')document.write('<sc'+'ript src="'+
'http'+(document.location.protocol=='https:'?'s://ssl':'://www')+
'.google-analytics.com/urchin.js'+'"></sc'+'ript>')
</script>
<script>
try {
_uacct = 'UA-6590519-1';
urchinTracker("/2554980469/goal");
} catch (err) { }
</script>

<?php
}
?>


