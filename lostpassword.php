<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page that lets users request an email to retrieve their lost password
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

if ($user->getStatus() != $USER_STATUS['UNREGISTERED'])
	header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());

$page = new Page('LOST_PASSWORD', 'HOME', $user);

$page->startHTML();

if (isset($_REQUEST['email'])) {

$display_email = String::htmlentities($_REQUEST['email']);

try {
	$email_user = User::getByEmail(trim(mb_strtolower($_REQUEST['email'], 'UTF-8')));
	$activation_code = substr(sha1(microtime()), 0, 12);
	$email_user->setActivationCode($activation_code);
	Email::mail($email_user->getEmail(), $email_user->getLid(), 
				'LOST_PASSWORD', 
				array('change_password_link' => $PAGE['CHANGE_PASSWORD'].'?activation_code='.$activation_code));

	?>
	
	<div class="hint hintmargin">
	<div class="hint_title">
	<translate id="LOST_PASSWORD_SENT_TITLE">
	The password-reset email was sent to
	</translate> 
	<?=$display_email?>
	</div> <!-- hint_title -->
	<translate id="LOST_PASSWORD_SENT_BODY">
	Check your inbox for the email that will help you reset your password
	</translate>
	</div> <!-- hint -->
	
	<?php

	} catch (Exception $e) {
	
	?>

	<div class="warning hintmargin">
	<div class="warning_title">
	<?=$display_email?> 
	<translate id="LOST_PASSWORD_ERROR_TITLE">
	is not registered on inspi.re
	</translate>
	</div> <!-- warning_title -->
	<translate id="LOST_PASSWORD_ERROR_BODY">
	The email address you've provided doesn't correspond to any account on inspi.re. 
	You can re-enter your email address below and retry.
	</translate>
	</div> <!-- warning -->
	
	<form class="basic_form" method="post">
	<input name="email" value="" type="text" maximum="200"/>
	<input type="submit" value="<translate id="LOST_PASSWORD_SEND_ME">Send me the password-reset email</translate>"/>
	</form>
	
	<?php
	
	}

} else {

if (isset($_REQUEST['wrongdata']) && strcmp($_REQUEST['wrongdata'], 'true') == 0) {
?>

<div class="warning hintmargin">
<div class="warning_title">
<translate id="LOST_PASSWORD_WRONG_DATA">
The email address and/or password you've entered are incorrect
</translate> 
</div> <!-- warning_title -->
</div> <!-- warning -->

<?php
}

?>

<div class="hint hintmargin">
<div class="hint_title">
<translate id="LOST_PASSWORD_HINT_TITLE">
Have you lost your password?
</translate>
</div> <!-- hint_title -->
<translate id="LOST_PASSWORD_HINT_BODY">
Enter your email address below and we'll send you an email that will let you reset your password
</translate>
</div> <!-- hint -->

<form class="basic_form" method="post">
<input name="email" value="" type="text" maximum="200" />
<input type="submit" value="<translate id="LOST_PASSWORD_SEND_ME">Send me the password-reset email</translate>"/>
</form>

<?php

}

$page->endHTML();
$page->render();
?>
