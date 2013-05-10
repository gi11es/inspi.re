<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users can transfer their cash balance to paypal or to premium membership
*/

require_once(dirname(__FILE__).'/entities/picture.php');
require_once(dirname(__FILE__).'/entities/premiumcode.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['INDEX']);
	exit(0);
}

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);

$page = new Page('SETTINGS', 'HOME', $user);
$page->addJavascript('TRANSFER_BALANCE');
$page->addJavascriptVariable('infinite_text', '<translate id="TRANSFER_BALANCE_PREMIUM_INFINITE" escape="js">infinite</translate>');
$page->addJavascriptVariable('balance', $user->getBalance());
$page->addJavascriptVariable('request_generate_premium', $REQUEST['GENERATE_PREMIUM']);
$page->addJavascriptVariable('premium_link', $PAGE['TRANSFER_BALANCE'].'?lid='.$user->getLid().'&premiumcode=');
$page->addJavascriptVariable('paypal_link', $PAGE['TRANSFER_BALANCE'].'?lid='.$user->getLid().'&paypalresult=');
$page->addJavascriptVariable('request_paypal_transfer', $REQUEST['PAYPAL_TRANSFER']);

$page->startHTML();

if (isset($_REQUEST['premiumcode'])) try {
	$premiumcode = PremiumCode::getByDisplayCode($_REQUEST['premiumcode']);
	$days = $premiumcode->getDuration() / 86400;

	echo '<div class="warning hintmargin">';
	echo '<div class="warning_title">';
	echo '<translate id="TRANSFER_BALANCE_PREMIUM_SUCCESSFUL_TITLE">';
	echo 'Your premium membership code, valid for <integer value="'.$days.'"/> day(s) of membership, was successfully generated';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '<translate id="TRANSFER_BALANCE_PREMIUM_SUCCESSFUL_BODY">';
	echo 'Your can activate it on your own profile, or on someone else\'s if you want to sponsor another member.';
	echo '</translate>';
	echo '</div> <!-- warning -->';
	echo '<div class="premium_code">';
	echo String::fromaform($_REQUEST['premiumcode']);
	echo '</div> <!-- premium_code -->';
} catch (PremiumCodeException $e) {}

if (isset($_REQUEST['paypalresult'])) {
	$result = floatval($_REQUEST['paypalresult']);
	
	if ($result > 0) {
		echo '<div class="warning hintmargin">';
		echo '<div class="warning_title">';
		echo '<translate id="TRANSFER_BALANCE_PAYPAL_SUCCESSFUL_TITLE">';
		echo 'Your transfer of $<float value="'.$result.'"/> to paypal was successful';
		echo '</translate>';
		echo '</div> <!-- warning_title -->';
		echo '</div> <!-- warning -->';
	} else {
		echo '<div class="warning hintmargin">';
		echo '<div class="warning_title">';
		echo '<translate id="TRANSFER_BALANCE_PAYPAL_FAILED_TITLE">';
		echo 'The transfer to paypal failed, your account balance remained the same';
		echo '</translate>';
		echo '</div> <!-- warning_title -->';
		echo '<translate id="TRANSFER_BALANCE_PAYPAL_FAILED_BODY">';
		echo 'It could be a temporary issue with our system, please try again later.';
		echo '</translate>';
		echo '</div> <!-- warning -->';
	}
}

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo '<translate id="TRANSFER_BALANCE_PAYPAL_HINT_TITLE">';
echo 'Transfer your account balance to paypal';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="TRANSFER_BALANCE_PAYPAL_HINT_BODY">';
echo 'Enter the amount you want to transfer from your balance of $<float value="'.$user->getBalance().'"/> to a paypal account of your choice. Please note that there is a 2% fee on transfers to paypal (with a maximum fee of $1).';
echo '</translate>';
echo '</div> <!-- hint -->';

echo '<div class="balance_transfer">';
echo '<translate id="TRANSFER_BALANCE_PAYPAL_AMOUNT">';
echo 'Amount to tranfer: $<input type="text" float="true" id="paypal_amount"/> ($<span id="paypal_actual_amount"><integer value="0"/></span> will be taken from your balance)';
echo '</translate><br/>';

echo '<translate id="TRANSFER_BALANCE_PAYPAL_EMAIL">';
echo 'Email address of the paypal account: <input type="text" id="paypal_address"/>';
echo '</translate><br/>';

echo '</div> <!-- balance_transfer -->';
echo '<input id="paypal_amount_submit" type="submit" value="<translate id="TRANSFER_BALANCE_PAYPAL_SUBMIT">Transfer money to paypal</translate>">';
echo '<div class="transfer_error" id="paypal_amount_warning" style="display:none"><translate id="TRANSFER_BALANCE_TOO_LOW_PAYPAL">Your balance is too low to transfer that amount to paypal</translate></div>';
echo '<div class="transfer_error" id="paypal_address_warning"><translate id="TRANSFER_BALANCE_INVALID_PAYPAL_ADDRESS">Please enter a valid email address for the destination paypal account</translate></div>';

echo '<div class="hint hintmargin clearboth">';
echo '<div class="hint_title">';
echo '<translate id="TRANSFER_BALANCE_PREMIUM_HINT_TITLE">';
echo 'Convert your account balance into premium membership';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="TRANSFER_BALANCE_PREMIUM_HINT_BODY">';
echo 'You can convert any amount of money from your balance of $<float value="'.$user->getBalance().'"/> into a premium membership code. You can use that code for yourself or to sponsor someone. You will receive a copy of the premium membership code by email for safekeeping.';
echo '</translate>';
echo '</div> <!-- hint -->';

echo '<div class="balance_transfer">';
echo '<translate id="TRANSFER_BALANCE_PREMIUM_AMOUNT">';
echo 'Convert $<input type="text" float="true" id="premium_amount"/> from your account balance of $<float value="'.$user->getBalance().'"/> into <span id="premium_days"><integer value="0"/></span> day(s) of premium membership.';
echo '</translate>';
echo '</div> <!-- balance_transfer -->';
echo '<input id="premium_amount_submit" type="submit" value="<translate id="TRANSFER_BALANCE_PREMIUM_SUBMIT">Generate premium membership code</translate>">';
echo '<div class="transfer_error" id="premium_amount_warning" style="display:none"><translate id="TRANSFER_BALANCE_TOO_LOW">Your balance is too low to convert that amount</translate></div>';

$page->endHTML();
$page->render();
?>
