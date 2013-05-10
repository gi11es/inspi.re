<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Press kit with key information about the website
*/

require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/entryvotelist.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('PRESS', 'INFORMATION', $user);
$page->setTitle('<translate id="PRESS_PAGE_TITLE">Press material and articles about inspi.re</translate>');

$page->startHTML();

?>
<div id="press">

<h1 class="hint help" id="description">
<a href="/<translate id="URL_ABOUT_US" escape="urlify">About Us</translate>/s5-l<?=$user->getLid()?>#description">
<translate id="PRESS_DESCRIPTION_TITLE">
Description and mission statement
</translate>
</a>
</h1>
<translate id="PRESS_DESCRIPTION_BODY">
Inspi.re is a social, competition-oriented art website. Focusing on the competitive aspect instead of 
having a portfolio-oriented approach brings a more challenging dimension to the way people use their art online. We believe that it's more rewarding and meaningful to have one's art rank high in a competition where anonymity is respected than simply receive compliments from friends and family.
<br/>
<br/>
The main goal of inspi.re is for people to get a better understanding of their current level in their craft
and to improve their artistic skills thanks to the help of fellow users. 
For it to work, everyone needs to provide feedback as well as receive it. The points system, which could be considered as a feedback currency, was introduced to keep such a balance in user interactions.<br/>
<br/>
Inspi.re was designed to be a very international website from the start, which is why it was already 
available in 6 languages at the launch of the beta version, thanks to the help of <a href="/About Us/s5-l<?=$user->getLid()?>#team">volunteers</a>.
</translate>

<h1 class="hint help" id="past">
<a href="/<translate id="URL_ABOUT_US" escape="urlify">About Us</translate>/s5-l<?=$user->getLid()?>#past">
<translate id="PRESS_PAST_TITLE">
Past coverage
</translate>
</a>
</h1>

<translate id="PRESS_PAST_BODY">
February 2009 - France - <a target="_blank" href="http://www.fabienthomas.com/2009/02/11/inspire-site-de-concours-de-photos-et-plus-si-affinite/">Fabienthomas.com</a><br/>
February 2009 - France - Le Dauphiné Libéré (newspaper)<br/>
February 2009 - Argentina - <a target="_blank" href="http://www.puntogeek.com/2009/02/02/inspire-red-social-para-amantes-de-la-fotografia/">PuntoGeek.com</a><br/>
January 2009 - France - <a target="_blank" href="http://www.everybodylovesphoto.com/307/site-photo/inspire-mesurez-vous-aux-autres-photographes/">Everybodylovesphoto.com</a><br/>
December 2008 - Germany - <a target="_blank" href="http://www.fotolism.us/index.php/wettbewerb/gegeneinander-antreten-lernen-austauschen-inspire">Fotolism.us</a>
</translate>

<?php

echo '<div class="hint abovemargin" id="team">',
	 '<div class="hint_title">',
	 '<a href="/<translate id="URL_ABOUT_US" escape="urlify">About Us</translate>/s5-l',$user->getLid(),'#team">',
	 '<translate id="TEAM_HINT_TITLE">',
	 'Members who contributed to make inspi.re what it is today',
	 '</translate>',
	 '</a>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->';

$teammembers = TeamMembershipList::get();

foreach ($teammembers as $uid) {
	$teammember = User::get($uid);
	$teammembership = TeamMembership::get($uid);
	echo '<div class="team_member">',
		 '<profile_picture class="team_member_picture" uid="',$uid,'" size="small"/>',
		 '<div class="team_member_info">',
		 '<user_name class="team_member_name" uid="',$uid,'"/>',
		 '<span class="team_member_title"><translate id="TEAM_MEMBER_TITLE_',$uid,'">',String::htmlentities($teammembership->getTitle()),'</translate></span>',
		 '</div> <!-- team_member_info -->',
		 '</div> <!-- team_member -->';
}

echo '<div class="warning topmargin hintmargin">',
	 '<div class="warning_title">',
	 '<translate id="TEAM_EXTRA_LANGUAGE">',
	 'Do you speak a language which isn\'t currently available on inspi.re? If you want to help us localize inspi.re for it to be available in your native language, contact us at translation@inspi.re. <i>Do not use translation@inspi.re to contact us about anything else than translation-related issues, otherwise we will not answer and your email address will be blacklisted permanently.</i>',
	 '</translate>',
	 '</div> <!-- warning_title -->',
	 '</div> <!-- warning -->',

	 '<div class="hint hintmargin abovemargin" id="donators">',
	 '<div class="hint_title">',
	 '<a href="/About Us/s5-l',$user->getLid(),'#donators">',
	 '<translate id="MEMBERS_DONATORS">',
	 'Donators',
	 '</translate>',
	 '</a>',
	 '</div> <!-- hint_title -->',
	 '<translate id="MEMBERS_DONATORS_SUBTITLE">',
	 'They\'ve helped the website early on and supported it with their donations. A big thanks to all of them!',
	 '</translate>',
	 '</div> <!-- hint -->',
	 '<div class="members">';

$donatorlist = UserLevelList::getByLevel($USER_LEVEL['DONATOR']);

foreach ($donatorlist as $uid) echo '<profile_picture class="member_thumbnail" uid="',$uid,'" size="small" />';

echo '</div> <!-- members -->',
	 '</div> <!-- press -->';

$page->endHTML();
$page->render();
?>
