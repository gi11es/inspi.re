<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users can edit their personal information and preferences
*/

require_once(dirname(__FILE__).'/entities/picture.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/utilities/system.php');
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

$page->setTitle('<translate id="EDIT_SETTINGS_PAGE_TITLE">Your settings on inspi.re</translate>');

$page->addJavascript('EDIT_SETTINGS');
$page->addJavascriptVariable('request_update_custom_url', $REQUEST['UPDATE_CUSTOM_URL']);
$page->addJavascriptVariable('request_update_name', $REQUEST['UPDATE_NAME']);
$page->addJavascriptVariable('request_update_profile', $REQUEST['UPDATE_PROFILE']);

$persistenttoken = new PersistentToken($user->getUid());

$page->addJavascriptVariable('persistenttoken', $persistenttoken->getHash());
$page->addStyle('EDITABLE_PICTURE');
$page->addJavascript('EDITABLE_PICTURE');

$page->startHTML();

?>

<div class="hint hintmargin">
<div class="hint_title">
<translate id="EDIT_PROFILE_HINT_TITLE">
Personal information and preferences
</translate>
</div> <!-- hint_title -->
<translate id="EDIT_PROFILE_HINT_BODY">
There is no save button on this page because all the changes you perform are being saved automatically.
</translate>
</div> <!-- hint -->

<div id="left_side">
<?php
echo UI::RenderEditablePicture($page, $user->getPid(), $PICTURE_CATEGORY['PROFILE'], true, $REQUEST['PROFILE_PICTURE_UPLOAD'], $REQUEST['PROFILE_PICTURE_RESET'], $PAGE['EDIT_CROPPING'], $persistenttoken->getHash());

if ($ispremium) {
	$ownprofilelink = 'http://inspi.re/'.$user->getCustomURL();
} else {
	$ownprofilelink = '/Member/s2-l'.$user->getLid().'-u'.$user->getUid();
}
?>
</div> <!-- left_side -->

<div id="information_form">

<div id="member_since">
<translate id="EDIT_PROFILE_MEMBER_SINCE">You've been a member of inspi.re for <duration value="<?=time() - $user->getCreationTime()?>" />.</translate> 
<translate id="EDIT_PROFILE_PUBLIC_LINK"><a href="<?=$ownprofilelink?>">Click here</a> to view your public profile.</translate>
<br/>
<br/>
<?php

if ($ispremium) {
	$membershipduration = max(0, $user->getPremiumTime() - time());
	
	if ($membershipduration > 94608000) {
		echo '<translate id="EDIT_PROFILE_PREMIUM_MEMBERSHIP_LIFETIME">';
		echo 'You\'re a <b>lifetime premium member</b>.';
		echo '</translate>';
	} else {
		echo '<translate id="EDIT_PROFILE_PREMIUM_MEMBERSHIP">';
		echo 'You\'re a <b>premium member</b> and your membership is valid for <duration value="'.$membershipduration.'"/>. If you want to extend your premium membership you can <a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'">purchase another premium membership code</a> and <a href="'.$PAGE['PREMIUM_ACTIVATE'].'?lid='.$user->getLid().'&uid='.$user->getUid().'">activate it</a>.';
		echo '</translate>';
	}
} else {
	echo '<translate id="EDIT_PROFILE_STANDARD_MEMBERSHIP">';
	echo 'You\'re a <b>standard member</b>. To upgrade your account to premium membership, you need to <a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'">purchase a premium membership code</a> and then to <a href="'.$PAGE['PREMIUM_ACTIVATE'].'?lid='.$user->getLid().'&uid='.$user->getUid().'">activate it</a>.';
	echo '</translate>';
}

echo '</div> <!-- member_since -->';

echo '<div id="name_field">';
echo '<label for="name">';
echo '<translate id="EDIT_PROFILE_NAME">Full name:</translate>';
echo '</label>';
echo '<input maximum="150" type="text" class="text_field" id="name" name="name" lefttrimmed="true" value="'.String::htmlentities($user->getName()).'" maximum="150"/>';
echo '</div> <!-- name_field -->';

echo '<div id="email_field">';
echo '<label for="email">';
echo '<translate id="EDIT_PROFILE_EMAIL">Email address:</translate>';
echo '</label>';
echo '<div id="email">';
echo $user->getEmail().' (<a href="'.$PAGE['CHANGE_EMAIL'].'?lid='.$user->getLid().'"><translate id="EDIT_PROFILE_EMAIL_CHANGE">change address</translate></a>)';
echo '</div>';
echo '</div> <!-- email_field -->';

if ($ispremium) {
	echo '<div id="custom_url_field">';
	echo '<label for="custom_url">';
	echo '<translate id="EDIT_CUSTOM_URL">Custom profile URL:</translate>';
	echo '</label>';
	
	echo '<div id="custom_url_container">';
	echo '<span id="url_prefix">http://inspi.re/</span>';
	echo '<input maximum="150" type="text" class="url_field" id="custom_url" name="custom_url" lefttrimmed="true" value="'.String::htmlentities(rawurldecode($user->getCustomURL())).'" maximum="150"/>';
	echo '</div> <!-- custom_url_container -->';
	
	echo '<div id="custom_url_valid" style="display: none"><img src="'.$GRAPHICS_PATH.'checkmark.gif"/></div>';
	echo '<div id="custom_url_invalid" style="display: none"><img src="'.$GRAPHICS_PATH.'cross.png"/> <translate id="EDIT_PROFILE_CUSTOM_URL_UNAVAILABLE">this URL is unavailable</translate></div>';
	echo '<div id="custom_url_progress" style="display: none"><img src="'.$GRAPHICS_PATH.'ajax-loader.gif"/></div>';

	echo '</div> <!-- custom_url_field -->';
}

?>

<div id="description_field">
<label for="description">
<translate id="EDIT_PROFILE_DESCRIPTION">About yourself:</translate>
</label>
<textarea class="text_field" id="description" name="description" maximum="2000"><?=$user->getDescription()?></textarea>
</div> <!-- description_field -->

<div id="length_errors">
<div class="length_error" id="name_too_long" style="display:none">
<translate id="EDIT_PROFILE_NAME_TOO_LONG">
Full name is too long
</translate>
</div> <!-- name_too_long -->

<div class="length_error" id="description_too_long" style="display:none">
<translate id="EDIT_PROFILE_DESCRIPTION_TOO_LONG">
Section about yourself is too long
</translate>
</div> <!-- description_too_long -->
</div> <!-- length_errors -->

<div class="hint hintmargin preference_header">
<div class="hint_title">
<translate id="EDIT_PROFILE_PREFERENCES">Preferences</translate>
</div> <!-- hint_title -->
</div> <!-- hint -->
<div class="discussion_board_preference">
<input type="checkbox" id="alert_email" name="alert_email" value="on" <?=$user->getAlertEmail()?'checked="yes"':''?>/> <translate id="EDIT_PROFILE_ALERT_EMAIL">Receive an email notification for each alert</translate>
</div>
<div class="discussion_board_preference">
<input type="checkbox" id="community_filter_icons" name="community_filter_icons" value="on" <?=$user->getCommunityFilterIcons()?'checked="yes"':''?>/> <translate id="EDIT_PROFILE_COMPETITIONS_PREFERENCES_FITLERS">Display the community filters as icons instead of text on the "compete" page</translate>
</div>
<div class="discussion_board_preference">
<input type="checkbox" id="display_rank" name="display_rank" value="on" <?=$user->getDisplayRank()?'checked="yes"':''?>/> <translate id="EDIT_PROFILE_DISPLAY_RANK">Display the ranks of all your entries publicly</translate>
</div>

<?php
	echo '<div class="discussion_board_preference" '.($ispremium?'':'style="display:none"').'>';
	echo '<input type="checkbox" id="hide_ads" name="hide_ads" value="on" '.($user->getHideAds()?'checked="yes"':'').'/> <translate id="EDIT_PROFILE_HIDE_ADS">Hide advertisements throughout the website</translate>';
	echo '</div>';
	
	echo '<div class="discussion_board_preference">';
	echo '<input type="checkbox" id="translation" name="translation" value="on" '.($user->getTranslate()?'checked="yes"':'').'/> <translate id="EDIT_PROFILE_TRANSLATION">Automatically translate comments and discussion posts to your language (powered by <img src="http://www.google.com/uds/css/small-logo.png" style="vertical-align: middle;"/>).</translate>';
	echo '</div>';

	echo '<div class="hint hintmargin preference_header">';
	echo '<div class="hint_title">';
	echo '<translate id="EDIT_PROFILE_CANVAS">Canvas prints</translate>';
	echo '</div> <!-- hint_title -->';
	echo '</div> <!-- hint -->';
	
	echo '<p id="canvas_explanation">';
	echo '<translate id="EDIT_PROFILE_CANVAS_BODY">';
	echo 'By default you can order canvas prints of your own artworks. You can also make these prints available for anyone to buy and add a markup to the base price if you want to earn a share on the sales. Copyright information including your name will be printed on the back of every print. You will receive an alert on every sale.';
	echo '</translate>';
	echo '</p> <!-- canvas_explanation -->';

	echo '<div class="discussion_board_preference">';
	echo '<input type="checkbox" id="allow_sales" name="allow_sales" value="on" '.($user->getAllowSales()?'checked="yes"':'').'/> <translate id="EDIT_PROFILE_ALLOW_SALES">Allow visitors and members to purchase prints of the artworks you post on inspi.re</translate>';
	echo '</div>';
	echo '<div class="discussion_board_preference">';
	echo '<input type="checkbox" id="markup" name="markup" value="on" '.($user->getMarkup() > 0?'checked="yes"':'').'/> <translate id="EDIT_PROFILE_MARKUP'.($ispremium?'_PREMIUM':'').'">Add a markup of <input maximum="4" id="markup_value" name="markup_value" type="text" numerical="true" value="'.($ispremium?$user->getMarkup():10).'" '.($ispremium?'':'disabled').'/>% to the retail price of the prints</translate>';
	if (!$ispremium) echo ' (<translate id="EDIT_PROFILE_MARKUP_HINT"><a href="'.$PAGE['PREMIUM'].'?lid='.$user->getLid().'">premium members</a> can apply a custom markup</translate>)';
	echo '</div>';
	echo '<span id="balance_summary">';
	echo '<translate id="EDIT_PROFILE_SALES_BALANCE">';
	echo '<b>Your current print sales balance is $<span id="balance"><float value="'.$user->getBalance().'"/></span> (US dollars).</b> You can <a href="'.$PAGE['TRANSFER_BALANCE'].'?lid='.$user->getLid().'">transfer that money to a paypal account or convert it into premium membership</a>.';
	echo '</translate>';
	echo '</span>';
	
	echo '</div> <!-- information_form -->';

$page->endHTML();
$page->render();
?>
