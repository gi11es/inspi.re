<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page that lets users spread the word about inspi.re
*/

require_once(dirname(__FILE__).'/entities/emailcampaignlist.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/templates/emailtemplate.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['INDEX'].'?lid='.$user->getLid());
	exit(0);
}

$page = new Page('INVITE', 'INFORMATION', $user);
$page->setTitle('<translate id="INVITE_PAGE_TITLE">Spread the word about inspi.re</translate>');

$page->startHTML();

$uid = $user->getUid();

$custom_link = $WEBSITE_PATH.'?a='.(strlen($uid) < 13?dechex($user->getUid()):$user->getUid());

echo '<div class="hint hintbigmargin">';
echo '<div class="hint_title">';
echo '<translate id="INVITE_AFFILIATION_TITLE">';
echo 'Earn premium membership by bringing new users to inspi.re';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="INVITE_AFFILIATION_BODY">';
echo 'For each new <b>active</b> member you bring to inspi.re, you will automatically receive 7 days worth of free premium membership (or your current membership will be extended by 7 days if you already have one). This offer is cumulative and doesn\'t have any limit.';
echo '</translate>';
echo '</div> <!-- hint -->';

echo '<translate id="INVITE_AFFILIATION_CHUNK_1">';
echo 'In order to benefit from this, you need to give the following link to people when you talk about the website:';
echo '</translate><br/><br/>';
echo '<input id="affiliate_link" type="text" value="'.$custom_link.'" readonly/><br/><br/>';
echo '<translate id="INVITE_AFFILIATION_CHUNK_2">';
echo 'The link above contains a reference to you that will let us know that the people who clicked on it came to the website thanks to you. We encourage you to use it to spread the word about inspi.re on blogs, forums, other websites you frequently use or even your local press. The more people you reach, the more free premium membership you will get.<br/><br/>';
echo 'Please note that we only give the reward for users who become active on the website, if they only sign up it won\'t be enough for you to receive the 7 days of free premium membership. It\'s also pointless to try and create fake accounts to get the reward, as we will detect those and they won\'t be rewarded.<br/><br/>';
echo 'You will receive an alert for every sign up made through your custom link above and a second alert to notify you of your reward every time one of the people who signed up becomes an active member of inspi.re.';
echo '</translate><br/><br/>';

echo '<div class="hint hintbigmargin abovemargin">';
echo '<div class="hint_title">';
echo '<translate id="INVITE_SHARE_TITLE">';
echo 'Spread the word about inspi.re on your social networks';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="INVITE_SHARE_BODY">';
echo 'If you\'re a member of any of these websites, simply click on the logo below and you\'ll be able to post a link, a message or a note about inspi.re. Your custom link is already integrated into these buttons.';
echo '</translate>';
echo '</div> <!-- hint -->';

echo '<div id="share_links">';
echo '<div id="bebo" class="share_link">';
echo '<a target="_blank" href="http://www.bebo.com/c/share?Url='.$custom_link.'">';
echo '<img src="'.$GRAPHICS_PATH.'bebo_logo.png">';
echo '</a>';
echo '</div> <!-- bebo -->';

echo '<div id="digg" class="share_link">';
echo '<script type="text/javascript">'."\r\n";
echo 'digg_url = \''.$custom_link.'\';'."\r\n";
echo 'digg_bgcolor = \'#ffffff\';'."\r\n";
echo 'digg_window = \'new\';'."\r\n";
echo '</script>'."\r\n";
echo '<script src="http://digg.com/tools/diggthis.js" type="text/javascript"></script>';
echo '</div> <!-- digg -->';

echo '<div id="facebook" class="share_link">';
echo '<a target="_blank" href="http://www.facebook.com/share.php?u='.$custom_link.'">';
echo '<img src="'.$GRAPHICS_PATH.'facebook_logo.png">';
echo '</a>';
echo '</div> <!-- facebook -->';

echo '<div id="stumbleupon" class="share_link">';
echo '<a target="_blank" href="http://www.stumbleupon.com/submit?url='.urlencode($custom_link).'">';
echo '<img src="'.$GRAPHICS_PATH.'stumbleupon_logo.png">';
echo '</a>';
echo '</div> <!-- stumbleupon -->';

echo '<div id="linkedin" class="share_link">';
echo '<a target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&url='.urlencode($custom_link).'">';
echo '<img src="'.$GRAPHICS_PATH.'linkedin_logo.png">';
echo '</a>';
echo '</div> <!-- linkedin -->';

echo '<div id="myspace" class="share_link nomargin">';
echo '<a target="_blank" href="http://www.myspace.com/Modules/PostTo/Pages/?u='.urlencode($custom_link).'">';
echo '<img src="'.$GRAPHICS_PATH.'myspace_logo.png">';
echo '</a>';
echo '</div> <!-- myspace -->';
echo '</div> <!-- share_links -->';

echo '<div id="facebook_fan">';
echo '<translate id="INVITE_FACEBOOK_FAN">';
echo 'Additionally, if you\'re on Facebook you can become of fan of <a target="_blank" href="http://www.facebook.com/pages/inspire/45823662245?sid=a7ff4185e804f56d727879f2606c4aca&ref=s">the official inspi.re page</a>. By doing so, it will appear in your friends\' news feed and bring their attention towards the website.';
echo '</translate>';
echo '</div> <!-- facebook_fan -->';

echo '<div class="hint hintmargin abovemargin">';
echo '<div class="hint_title">';
echo '<translate id="INVITE_EMAIL_TITLE">';
echo 'Invite people by email';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="INVITE_EMAIL_BODY">';
echo 'Send an email invitation directly to your friends and family with a personalized message. It\'s a good idea to put in your message the custom link shown at the top of this page, thanks to it your friends could get you free premium membership if they become active members.';
echo '</translate>';
echo '</div> <!-- hint -->';

if (isset($_REQUEST['success'])) {
	echo '<div class="warning hintmargin clearboth highlight_item">';
	echo '<div class="warning_title">';
	echo '<translate id="INVITE_SENT">';
	echo 'Your email invitation was sent successfully';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
}

echo '<form id="new_invite" method="post" action="'.$REQUEST['NEW_EMAIL_INVITE'].'">';
echo '<label for="email_list_input"><translate id="NEW_EMAIL_INVITE_EMAIL_LIST">Email address list:</translate></label><textarea id="email_list_input" maximum="500" name="email_list_input" /></textarea>';
echo '<label for="invite_text_input"><translate id="NEW_EMAIL_INVITE_TEXT">Custom message:</translate></label><textarea id="invite_text_input" maximum="500" name="invite_text_input" /></textarea>';
echo '<input id="new_invite_submit" type="submit" value="<translate id="NEW_EMAIL_INVITE_SEND">Send email invitation</translate>" disabled="">';
echo '<div class="length_error" id="email_list_empty" style="display:none"><translate id="NEW_EMAIL_INVITE_EMAIL_LIST_EMPTY">There are no email addresses in the list</translate></div>';
echo '<div class="length_error" id="email_list_too_long" style="display:none"><translate id="NEW_EMAIL_INVITE_EMAIL_LIST_TOO_LONG">Email list is too long</translate></div>';
echo '<div class="length_error" id="invite_text_too_long" style="display:none"><translate id="NEW_EMAIL_INVITE_TEXT_TOO_LONG">Invitation text is too long</translate></div>';
echo '</form>';

echo '<div class="clearboth hint hintmargin abovemargin">';
echo '<div class="hint_title">';
echo '<translate id="INVITE_POSTCARD_TITLE">';
echo 'Invite someone with a postcard';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<translate id="INVITE_POSTCARD_BODY">';
echo 'We will print and send a real postcard free of charge to any address in the world with your custom message on it. This feature is limited to one postcard per account, so please choose with care who you want this postcard sent to. Remember to specify the destination country in the address.';
echo '</translate>';
echo '</div> <!-- hint -->';

$emailcampaignlist = array_keys(EmailCampaignList::getByETid($EMAIL_TEMPLATE['POSTCARD']));

if (in_array($user->getUid(), $emailcampaignlist)) {
	echo '<div class="warning clearboth">';
	echo '<div class="warning_title">';
	echo '<translate id="POSTCARD_SENT">';
	echo 'Your postcard request has been registered successfully.';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '</div> <!-- warning -->';
} else {
	echo '<form id="new_postcard" method="post" action="'.$REQUEST['NEW_POSTCARD_INVITE'].'">';
	echo '<label for="address_input"><translate id="NEW_POSTCARD_INVITE_ADDRESS">Postal address:</translate></label><textarea id="address_input" name="address_input" /></textarea>';
	echo '<label for="postcard_text_input"><translate id="NEW_POSTCARD_INVITE_TEXT">Custom message:</translate></label><textarea id="postcard_text_input" maximum="500" name="postcard_text_input" /></textarea>';
	echo '<input id="new_postcard_submit" type="submit" value="<translate id="NEW_POSTCARD_INVITE_SEND">Send postcard</translate>" disabled="">';
	echo '<div class="length_error" id="postcard_text_too_long" style="display:none"><translate id="NEW_POSTCARD_INVITE_TEXT_TOO_LONG">Custom message is too long</translate></div>';
	echo '</form>';
}

$page->endHTML();
$page->render();
?>
