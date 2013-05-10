<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Who's online, who's just joined the website and who's achieved something special recently
*/

require_once(dirname(__FILE__).'/entities/communitylist.php');
require_once(dirname(__FILE__).'/entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/i18n.php');
require_once(dirname(__FILE__).'/entities/specialuser.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/entities/userlist.php');
require_once(dirname(__FILE__).'/utilities/inml.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

function RenderSpecialUserEntries($uid) {
	global $ENTRY_STATUS;
	global $COMPETITION_STATUS;
	global $PAGE;
	global $user;
	
	$entrylist = EntryList::getByUidAndStatus($uid, $ENTRY_STATUS['POSTED']);
	$entry_user = User::get($uid);
	
	$cleanentrylist = array();
	
	$competitionlist = Competition::getArray(array_keys($entrylist));
	
	foreach ($entrylist as $cid => $eid) 
		if (isset($competitionlist[$cid]) && $competitionlist[$cid]->getStatus() == $COMPETITION_STATUS['CLOSED'])
			$cleanentrylist[$eid] = $competitionlist[$cid]->getEndTime();
	
	arsort($cleanentrylist);
	
	$cleanentrylist = array_slice($cleanentrylist, 0, 24, true);
	
	$entry = Entry::getArray(array_keys($cleanentrylist));
	
	echo '<div class="special_user_entries">';
	foreach ($cleanentrylist as $eid => $end_time) if (isset($entry[$eid])) try {
		$theme = Theme::get($competitionlist[$entry[$eid]->getCid()]->getTid());
		$competitionname = $theme->getTitle();
		$title = '<translate id="MEMBERS_SPECIAL_USER_ENTRY_TITLE">'
				.'<string value="'.String::htmlentities($entry_user->getUniqueName()).'"/> ranked '
				.'<rank value="'.$entry[$eid]->getRank().'"/> out of '
				.'<integer value="'.$competitionlist[$entry[$eid]->getCid()]->getEntriesCount().'"/> '
				.'in the <string value="'.String::htmlentities('"'.$competitionname.'"').'"/> competition '
				.'<duration value="'.(gmmktime() - $competitionlist[$entry[$eid]->getCid()]->getEndTime()).'"/> ago</translate>';
		$title = INML::processHTML($user, I18N::translateHTML($user, $title));
		echo '<picture title="',$title,'" class="special_user_entry" category="entry" pid="'
			,$entry[$eid]->getPid(),'" size="small" href="',$PAGE['ENTRY']
			,'?lid='.$user->getLid(),'#eid=',$eid,'"/>';
	} catch (ThemeException $e) {}
	echo '</div> <!-- special_user_entries -->';
}

$user = User::getSessionUser();

$page = new Page('MEMBERS', 'COMMUNITIES', $user);
$page->setTitle('<translate id="MEMBERS_PAGE_TITLE">Members of inspi.re</translate>');
$page->addJavascriptVariable('comet_url', $COMET_URL);
$page->addJavascriptVariable('comet_channel_activity', $COMET_CHANNEL['GENERAL_ACTIVITY']);
$page->addJavascriptVariable('comet_channel_user_on', $COMET_CHANNEL['USER_ON']);
$page->addJavascriptVariable('comet_channel_user_off', $COMET_CHANNEL['USER_OFF']);
$page->addJavascriptVariable('comet_channel_user_registered', $COMET_CHANNEL['USER_REGISTERED']);
$page->addJavascriptVariable('uid', $user->getUid());
$page->addJavascriptVariable('lid', $user->getLid());

$page->startHTML(10); // Don't update the page more often than every 10 seconds, serve a cached copy instead

$member_of = array_keys(CommunityMembershipList::getByUid($user->getUid()));
if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
	$owner = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ANONYMOUS']);
} else {
	$owner = CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['ACTIVE']);
	$owner = array_merge(CommunityList::getByUidAndStatus($user->getUid(), $COMMUNITY_STATUS['INACTIVE']));
}

$community_list = array_unique(array_merge($member_of, $owner));

echo '<div class="hint hintmargin">',
	 '<div class="hint_title">',
	 '<translate id="MEMBERS_SEARCH_TITLE">',
	 'Member search',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '<translate id="MEMBERS_SEARCH_BODY">',
	 'Enter part of or the whole name of the member you\'re looking for',
	 '</translate>',
	 '</div> <!-- hint -->',

	 '<div id="member_search">',
	 '<form method="GET" action="',$PAGE['MEMBER_SEARCH'],'">',
	 '<input type="hidden" name="lid" value="',$user->getLid(),'"/>',
	 '<input type="text" id="search" name="search"/>',
	 '<input type="submit" value="<translate id="MEMBERS_SEARCH">Find member</translate>"/>',
	 '</form>',
	 '</div> <!-- member_search -->';

try {
	$specialuser = SpecialUser::get($SPECIAL_USER['MOST_HELPFUL']);
	$helpfuluid = $specialuser->getUid();
	$helpfulcount = $specialuser->getValue();
	
	echo '<div id="most_helpful">',
		 '<div class="hint hintmargin">',
		 '<div class="hint_title">',
		 '<translate id="MEMBERS_MOST_HELPFUL_TITLE">',
		 'The most helpful member (updated hourly)',
		 '</translate>',
		 '</div> <!-- hint_title -->',
		 '<translate id="MEMBERS_MOST_HELPFUL_BODY">',
		 '<user_name uid="',$helpfuluid,'"/> is all about giving. ',
		 '<integer value="',$helpfulcount,'"/> words of comments and critiques posted in the last ',
		 '24 hours! Check out <user_name uid="',$helpfuluid,'"/>\'s work, it deserves comments too. ',
		 'His/her generosity earns him/her one day of free premium membership for every hour being ',
		 'listed as the most helpful member.',
		 '</translate>',
		 '</div> <!-- hint -->',
		 '<profile_picture class="special_user" uid="',$helpfuluid,'" size="medium"/>';
	
	try {
		RenderSpecialUserEntries($helpfuluid);
	} catch (UserException $e) {}
	
	echo '</div> <!-- most_helpful -->';
} catch (SpecialUserException $e) {}

try {
	$specialuser = SpecialUser::get($SPECIAL_USER['BIGGEST_VOTER']);
	$biggestvoteruid = $specialuser->getUid();
	$votecount = $specialuser->getValue();
	
	echo '<div id="biggest_voter">',
		 '<div class="hint hintmargin">',
		 '<div class="hint_title">',
		 '<translate id="MEMBERS_BIGGEST_VOTER_TITLE">',
		 'The biggest voter (updated hourly)',
		 '</translate>',
		 '</div> <!-- hint_title -->',
		 '<translate id="MEMBERS_BIGGEST_VOTER_BODY">',
		 '<user_name uid="',$biggestvoteruid,'"/> voted on ',
		 '<integer value="',$votecount,'"/> artworks in the last 24 hours!',
		 '</translate>',
		 '</div> <!-- hint -->',
		 '<profile_picture class="special_user" uid="',$biggestvoteruid,'" size="medium"/>';
	
	try {
		RenderSpecialUserEntries($biggestvoteruid);
	} catch (UserException $e) {}
	
	echo '</div> <!-- biggest_voter -->';
} catch (SpecialUserException $e) {}

try {
	$specialuser = SpecialUser::get($SPECIAL_USER['MOST_PROLIFIC']);
	$prolificuid = $specialuser->getUid();
	$prolificcount = $specialuser->getValue();
	
	echo '<div id="most_prolific">',
		 '<div class="hint hintmargin">',
		 '<div class="hint_title">',
		 '<translate id="MEMBERS_MOST_PROLIFIC_TITLE">',
		 'The most prolific member (updated hourly)',
		 '</translate>',
		 '</div> <!-- hint_title -->',
		 '<translate id="MEMBERS_MOST_PROLIFIC_BODY">',
		 '<user_name uid="',$prolificuid,'"/> is in a competitive mood. ',
		 '<integer value="',$prolificcount,'"/> artworks posted in the last 24 hours!',
		 '</translate>',
		 '</div> <!-- hint -->',
		 '<profile_picture class="special_user" uid="',$prolificuid,'" size="medium"/>';
	
	try {
		RenderSpecialUserEntries($prolificuid);
	} catch (UserException $e) {}
	
	echo '</div> <!-- most_prolific -->';
} catch (SpecialUserException $e) {}

echo '<div class="clearboth"></div>',
	 '<ad ad_id="MEMBERS"/>',

	 '<div class="hint hintmargin">',
	 '<div class="hint_title">',
	 '<translate id="MEMBERS_RECENT">',
	 'Members who have joined inspi.re recently',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<div id="registration_recent_members" class="members">';

$recentlyregisteredlist = UserList::getRecentlyRegistered(14); // Get the 14 most recent members to register

arsort($recentlyregisteredlist);
	
foreach ($recentlyregisteredlist as $uid => $creation_time)
	echo '<profile_picture class="member_thumbnail" uid="',$uid,'" size="small" id="registered_user_',$uid,'"/>';

echo '</div> <!-- members -->',

	'<div class="hint hintmargin">',
	 '<div class="hint_title">',
	 '<translate id="MEMBERS_REAL_TIME_UPDATES">',
	 'What\'s happening right now',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<div id="real_time_updates"></div>',

	 '<div class="hint hintmargin">',
	 '<div class="hint_title">',
	 '<translate id="MEMBERS_LIVE">',
	 'Members who are currently online',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<div class="members" id="live_users">';
	 
$livelist = UserList::getLive();
if ($user->getStatus() == $USER_STATUS['BANNED']) $livelist [$user->getUid()] = gmmktime();

foreach ($livelist as $uid => $last_activity)
	echo '<profile_picture class="member_thumbnail" uid="',$uid,'" size="small" id="user_',$uid,'"/>';
echo '</div> <!-- members -->';

$page->endHTML();
$page->render();
?>
