<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page that lets users request an email that will let them retrieve their lost password
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('CHANGE_PASSWORD', 'HOME', $user);

$page->startHTML();

if (isset($_REQUEST['password']) && isset($_REQUEST['verify_password'])) {
	$user->setPassword($_REQUEST['password']);
?>

<div class="hint hintmargin">
<div class="hint_title">
<translate id="CHANGE_PASSWORD_SUCCESS_TITLE">
Your password was succesfully changed
</translate>
</div>
<translate id="CHANGE_PASSWORD_SUCCESS_BODY">
You can keep using the website, as you're already logged in.
</translate>
</div>


<?php
} elseif (isset($_REQUEST['activation_code'])) {

$display_activation_code = String::htmlentities($_REQUEST['activation_code']);

try {
	$activation_code_user = User::getByActivationCode($_REQUEST['activation_code']);
	$activation_code_user->setSessionUser();
	
	?>
	
<div class="hint hintmargin">
<div class="hint_title">
<translate id="CHANGE_PASSWORD_TITLE">
Change your password
</translate>
</div>
<translate id="CHANGE_PASSWORD_BODY">
Please enter below the new password you wish to use for your account.
</translate>
</div>

<form id="change_password" method="post">
<label for="password"><translate id="REGISTER_PASSWORD">Password:</translate></label>
<input type="password" class="password_field" id="password" name="password" value="" autocomplete="off"/>
<span class="icon" id="icon_password"><img src="<?=$GRAPHICS_PATH?>x-red.gif"></span> 
<span class="form_error" id="password_length">
<translate id="REGISTER_INVALID_PASSWORD_LENGTH">6 characters minimum</translate>
</span>

<label for="verify_password">
<translate id="REGISTER_VERIFY_PASSWORD">Verify password:</translate>
</label>
<input type="password" class="password_field" id="verify_password" name="verify_password" value="" autocomplete="off"/>
<span class="icon" id="icon_verify_password"><img src="<?=$GRAPHICS_PATH?>x-red.gif"></span>

<input type="submit" name="submitbutton" class="button" id="submitbutton" value="<translate id="CHANGE_PASSWORD_SUBMIT_BUTTON">save your new password</translate>" disabled/>

</form>
	
	<?php

	} catch (UserException $e) { 
	// Retrieving the user by its activation code failed, display error message
	
	?>

<div class="warning hintmargin">
<div class="warning_title">
<?=$display_activation_code?> 
<translate id="CHANGE_PASSWORD_ERROR_TITLE">
is not a valid activation code for this password-reset procedure
</translate>
</div>

<translate id="CHANGE_PASSWORD_ERROR_BODY">
You can <a href="<?=$PAGE['LOST_PASSWORD']?>">go back to the lost password page</a> and retry.
</translate>
</div>
	
	<?php
	
	}

// If the page was accessed without the necessary parameters, redirect to the main page
} else header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid()); 

$page->endHTML();
$page->render();
?>
