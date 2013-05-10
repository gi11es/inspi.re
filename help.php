<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page that gives hints and tutorials on how to use the website
*/

require_once(dirname(__FILE__).'/entities/pointsvalue.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('HELP', 'INFORMATION', $user);
$page->setTitle('<translate id="HELP_PAGE_TITLE">Help on how to use inspi.re</translate>');

$page->startHTML();

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['ENTRY_POSTING']);
$points_entry_posting = $pointsvalue->getValue();

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['COMMUNITY_CREATING']);
$points_community_creating = $pointsvalue->getValue();

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['THEME_VOTING']);
$points_theme_voting = $pointsvalue->getValue();

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['ENTRY_VOTING']);
$points_entry_voting = $pointsvalue->getValue();

?>
<div id="help">
<h1 class="hint help" id="about">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#about">
<translate id="HELP_ABOUT_TITLE">
What is this website about?
</translate>
</a>
</h1>
<translate id="HELP_ABOUT_BODY">
Inspi.re is a platform for artists, budding artists and hobbyists to compete against each other, learn together and share knowledge. It originated as a photography competition and is now open to any visual artform (although there is no video support at the moment, it might come at a later stage). Unlike other websites that focus only on letting people showcase or sell their work, inspi.re focuses on the competitive aspect. We believe that competing against each other is not only a way for artists to see how good they really are but also an excellent mean to stimulate creativity. We want inspi.re and its communities to be the catalyst of your art, giving you the extra motivation you needed to keep at it and outdo yourself.
</translate>
<br/>
<br/>
<h1 class="hint help" id="points">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#points">
<translate id="HELP_POINTS_TITLE">
What do points represent? How to I earn them?
</translate>
</a>
</h1>
<translate id="HELP_POINTS_BODY">
Inspi.re is based on a points system. Actions on the website cost or earn you points, according to the following:<br/>
<h2>Actions that cost you points</h2>
<ul>
<li>Suggesting a theme: variable, defined by the community administrator</li>
<li>Entering an artwork into a competition: <integer value="<?=$points_entry_posting?>"/> point(s)</li>
<li>Creating a community: <integer value="<?=$points_community_creating?>"/> point(s)</li>
</ul>
<h2>Actions that earn you points</h2>
<ul>
<li>Voting on a theme: +<integer value="<?=$points_theme_voting?>"/> point(s)</li>
<li>Voting on an entry: +<integer value="<?=$points_entry_voting?>"/> point(s)</li>
<li>Deleting a theme, a community or an entry before the voting stage starts give you back the points you had spent</li>
</ul>
If you run out of points to do something in particular, just vote on entries, comment on them and you'll start earning the points you need. The points system was created to give some balance to the website. Before the system existed there were too many members who only sent entries and never voted or commented on anything. It bloated the competitions with too many entries and people were not receiving enough feedback.
</translate>
<br/>
<br/>
<h1 class="hint help" id="terms">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#terms">
<translate id="HELP_TERMS_TITLE">
What do "communities", "competitions" and "themes" represent and how do they work?
</translate>
</a>
</h1>

<ul>
<li>
<translate id="HELP_TERMS_CHUNK1">
Communities are groups of artists on inspi.re. For example they can be a group of members who share the same vision towards a specific art, artists from the same area or country, etc. There are no guidelines on what communities can be, as they are created by members of inspi.re. You can join and leave communities at any time and there is no limit on the amount of communities you can be a member of.
</translate>
</li>

<li>
<translate id="HELP_TERMS_CHUNK2">
Competitions on inspi.re all belong to specific communities. Each community can work differently. Some communities will decide to have daily competitions while others might decide to take the extreme of having only a yearly competition. The common part seen in any competition on inspi.re is its lifecycle. First a competition enters its "participation" stage where users can submit entries. At some point this stage closes and no more entries can be submitted. Then voting begins and any member of the community can cast a vote on some or all entries in the competition (except their own). Lastly, voting closes and the winners are designated. It's important to note that until the competition is completely over anonymity is kept for participants, in order to avoid sympathy voting.
</translate>
</li>

<li>
<translate id="HELP_TERMS_CHUNK3">
Themes represent what the entries in competitions should be about. They come with a title and description in order to help fellow members of a community understand what the entries in a competition should be about. It's very crucial do define themes properly in order to limit misunderstandings and arguments about what is on or off-topic. In addition to suggesting them, members of a community vote themes up and down in order to decide what the upcoming competitions should be about.
</translate>
</li>
</ul>

<h1 class="hint help" id="use">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#use">
<translate id="HELP_USE_TITLE">
How do I use this website?
</translate>
</a>
</h1>
<translate id="HELP_USE_BODY">
We advise you to start by looking at the list of available communities and to join one of your fancy. You can't create one straight away because we want community administrators to be members who have been on the website for a while and understand every aspect of it. Once you're a member of one or more communities, you can start by having a look at the hall of fame. If any competitions have finished already in those communities, you will be able to see how good the past winning entries were and get a feel of what these communities are like. Then you might want to vote and critique some of the competitions that are currently in the voting stage. This is important to do, not only because you will need to earn the points in order to submit your own entries, but also to become an active member of the communities. Once you're familiar with all of the above, you can take your chance and submit your own entry to a competition. If you intend to win, we advise you to make that artwork specifically for the competition. Experience has shown us that most of the winning entries were made specifically for the corresponding competition.
</translate>
<br/>
<br/>
<h1 class="hint help" id="voting">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#voting">
<translate id="HELP_VOTING_TITLE">
How does the voting system work?
</translate>
</a>
</h1>
<translate id="HELP__VOTING_BODY">
It is quite unusual because it's an additive system and not an average system. Each vote, be it 1 star or 5 stars, adds to the total score of an entry. The entry with the highest score wins the competition.
</translate>
<br/>
<br/>
<h1 class="hint help" id="multiple-voting">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#multiple-voting">
<translate id="HELP_MULTIPLE_VOTING_TITLE">
Why am I able to vote multiple times on the same entry?
</translate>
</a>
</h1>
<translate id="HELP_MULTIPLE_VOTING_BODY">
You aren't. However you can change your vote. Each time you do, your old vote is deleted and replaced by the new one. We allow you to do that in case you make a mistake when voting or want to readjust your vote after you judged more entries in the competition.
</translate>
<br/>
<br/>
<h1 class="hint help" id="sympathy-voting">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#sympathy-voting">
<translate id="HELP_SYMPATHY_VOTING_TITLE">
My friend told me he/she voted on my entry (like I asked) and it didn't add anything to my entry's score!
</translate>
</a>
</h1>
<translate id="HELP_SYMPATHY_VOTING_BODY">
You and your friend are violating the terms of use of inspi.re. Go <a href="/Terms-And-Conditions/s8">read them</a>. Your friend's vote being ignored is our fraud detection system at work. Keep behaving like that and you'll see our permanent website ban policy at work. We take cheating seriously.
</translate>
<br/>
<br/>
<h1 class="hint help" id="bug">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#bug">
<translate id="HELP_BUG_TITLE">
I've found a bug on the website, something is not working properly
</translate>
</a>
</h1>
<translate id="HELP_BUG_BODY">
All the information regarding bugs - and how to notify us when you find them - is on the <a href="/Bug-Report/s7-l<?=$user->getLid()?>">bug report page</a>.
</translate>
<br/>
<br/>
<h1 class="hint help" id="languages">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#languages">
<translate id="HELP_LANGUAGES_TITLE">
The website is available in several languages, but not mine!
</translate>
</a>
</h1>
<translate id="HELP_LANGUAGES_BODY">
Are you fluent enough in English that you think you can translate the website from English to your own language? The several languages you can already use have been translated by fellow members of inspi.re. Simply email us at <a href="mailto:translation@inspi.re">translation@inspi.re</a> and we'll tell you about the process if you want to help us get the website localized into your own language. <i>translation@inspi.re is only for translation-related matters, for anything else please contact support@inspi.re.</i>
</translate>
<br/>
<br/>
<h1 class="hint help" id="translation-mistake">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#translation-mistake">
<translate id="HELP_TRANSLATION_MISTAKE_TITLE">
There is a mistake in the translation of the website
</translate>
</a>
</h1>
<translate id="HELP_TRANSLATION_MISTAKE_BODY">
Inspi.re has been translated by volunteers, it may contain inaccurate parts in its translation. If you find such language-related issues please contacts us at <a href="mailto:translation@inspi.re">translation@inspi.re</a>. <i>translation@inspi.re is only for translation-related matters, for anything else please contact support@inspi.re.</i>
</translate>
<?php
	if ($user->getStatus() != $USER_STATUS['UNREGISTERED']) {
?>
<br/>
<br/>
<h1 class="hint help" id="suggestions">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#suggestions">
<translate id="HELP_SUGGESTIONS_TITLE">
I have a suggestion on how to improve the website, how can I contact you?
</translate>
</a>
</h1>
<translate id="HELP_SUGGESTIONS_BODY">
You can send your suggestions to <a href="mailto:ideas@inspi.re">ideas@inspi.re</a>, however it's a one-way suggestion box and we will not reply to the emails you send to that address due to lack of time. All suggestions will be read and taken into consideration when we decide which feature will be implemented next.
</translate>
<br/>
<br/>
<h1 class="hint help" id="delete-account">
<a href="<?=$PAGE['HELP'].'?lid='.$user->getLid()?>#delete-account">
<translate id="HELP_DELETE_ACCOUNT_TITLE">
I want to delete my account
</translate>
</a>
</h1>
<?php
    $token = new Token($user->getUid());
	echo '<a href="javascript:showConfirmation(\''.$REQUEST['DELETE_ACCOUNT'].'?token='.$token->getHash().'\'';
	echo ', \'<translate id="ACCOUNT_DELETE_CONFIRMATION_TITLE" escape="js">Do you really want to delete your user account?</translate>\'';
	echo ', \'<translate id="ACCOUNT_DELETE_CONFIRMATION_TEXT" escape="js">All your communities, entries, messages, and any past activity on the website will be gone forever. This cannot be undone!</translate>\'';
	echo ', \'<translate id="ACCOUNT_DELETE_CONFIRMATION_YES" escape="js">Yes, go ahead</translate>\'';
	echo ', \'<translate id="ACCOUNT_DELETE_CONFIRMATION_NO" escape="js">No</translate>\'';
	echo ');"><translate id="ACCOUNT_DELETE_LINK">Click here</a> if you wish to delete your inspi.re account permanently.</translate>';	
	}
	
echo '</div>';

$page->endHTML();
$page->render();
?>
