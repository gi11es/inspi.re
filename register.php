<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Minimal registration page
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('REGISTER', 'HOME', $user);
$page->addJavascriptVariable('request_check_email', $REQUEST['CHECK_EMAIL']);
$page->startHTML();

if (isset($_REQUEST['reset']) && $_REQUEST['reset']) {
	$user->setEmail('');
	$user->setActivationCode(null);
	$user->setPassword('');
	$user->setStatus($USER_STATUS['UNREGISTERED']);
	header('Location: '.$PAGE['REGISTER']);
	exit(0);
}

if (strcmp($user->getEmail(), '') != 0) {
	header('Location: '.$PAGE['CONFIRM']);
	exit(0);
}

?>

<div class="hint hintmargin"><div class="hint_title"><translate id="REGISTER_HINT_TITLE">Account registration has never been that fast</translate></div><translate id="REGISTER_HINT_BODY_STEP1">Step 1 of 2: Submit the form below, this is the only information you need to provide.</translate></div>

<form id="registration" method="post" action="<?=$PAGE['CONFIRM']?>">

<label for="email"><translate id="REGISTER_EMAIL">Email:</translate></label>
<input type="text" class="text_field" id="email" name="email" value="" maximum="200"/>
<span class="icon" id="icon_email"><img src="<?=$GRAPHICS_PATH?>x-red.gif"></span> <span class="form_error" id="email_unavailable"><translate id="REGISTER_INVALID_EMAIL">Address is invalid or already registered</translate></span>
<br/>
<label for="verify_email" id="label_verify_email"><translate id="REGISTER_VERIFY_EMAIL">Verify email:</translate></label>
<input type="text" class="text_field" id="verify_email" name="verify_email" value=""  maximum="200"/>
<span class="icon" id="icon_verify_email"><img src="<?=$GRAPHICS_PATH?>x-red.gif"></span>
<br/>
<label for="password"><translate id="REGISTER_PASSWORD">Password:</translate></label>
<input type="password" class="password_field" id="password_original" name="password" value="" autocomplete="off" minimum="6"/>
<span class="icon" id="icon_password"><img src="<?=$GRAPHICS_PATH?>x-red.gif"></span> <span class="form_error" id="password_length"><translate id="REGISTER_INVALID_PASSWORD_LENGTH">6 characters minimum</translate></span>
<br/>
<label for="verify_password"><translate id="REGISTER_VERIFY_PASSWORD">Verify password:</translate></label>
<input type="password" class="password_field" id="verify_password" name="verify_password" value="" autocomplete="off" minimum="6"/>
<span class="icon" id="icon_verify_password"><img src="<?=$GRAPHICS_PATH?>x-red.gif"></span>
<br/>
<input type="text" class="text_field" id="username" name="username" value=""/>
<br/>
<input type="submit" name="submitbutton" class="button" id="submitbutton" value="<translate id="REGISTER_SUBMIT_BUTTON">Register your inspi.re account</translate>" disabled/> <span class="form_error"><translate id="REGISTER_TERMS_AGREEMENT">By registering you agree to all of inspi.re's <a href="/Terms-And-Conditions/s8-l<?=$user->getLid()?>">terms, conditions and legal documents</a></translate></span>

</form>

<?php
$page->endHTML();
$page->render();
?>
