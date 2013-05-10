<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Describes the prizes up for grabs and lists past winners
*/

require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/competitionlist.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/prizewinner.php');
require_once(dirname(__FILE__).'/entities/prizewinnerlist.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/inml.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('PRIZE', 'COMPETITIONS', $user);
$page->setTitle('<translate id="PRIZE_PAGE_TITLE">Monthly prize on inspi.re</translate>');

$page->startHTML(30); // Only update this page every 30 seconds at most

echo '<div id="cup">',
	 '<img src="',$GRAPHICS_PATH.'cup.png" alt="Prize cup"/>',
	 '</div> <!-- cup -->',

	 '<div id="title">',
	 '<translate id="PRIZE_TITLE">',
	 'Monthly prize for one of inspi.re\'s best artists',
	 '</translate>',
	 '</div> <!-- title -->';

if (gmmktime() < gmmktime(0, 0, 0, 11, 1, 2009)) {
	echo '<div id="explanations">',
		 '<translate id="PRIZE_EXPLANATIONS">',
		 'On the 1st of every month we give away <b>100 euros</b> to a member of inspi.re who won a competition the month before. This offer is available to any individual, worldwide. All you need to do is enter a competition on inspi.re in any community and if you rank 1st out of 15 participants or more, you will be automatically eligible for the prize draw. The winner will be picked at random among the eligible entries.',
		 '</translate>',
		 '</div> <!-- explanations -->';
} else {
	echo '<div id="explanations">',
		 '<translate id="PRIZE_EXPLANATIONS_2">',
		 'Everyone on inspi.re is automatically a member of the <community_name link="true" xid="267"/> community. For every competition that happens in that community, artworks ranked first share a <b>100 euros</b> prize, artworks ranked second share <b>6 months of premium membership</b> and artworks ranked 3rd share <b>2 months of premium membership</b>. The competitions are open to everyone. Respect the theme and create the best artwork possible for a chance to bring one of the prizes home!',
		 '</translate>',
		 '</div> <!-- explanations -->';
}

$competitionlist = CompetitionList::getByStatus($COMPETITION_STATUS['CLOSED']);
$eligiblelist = array();

$competition = Competition::getArray(array_keys($competitionlist));
unset($competitionlist);
$winnerlist = array();

foreach ($competition as $cid => $comp) if ($competition[$cid]->getEndTime() >= gmmktime(0, 0, 0, gmdate('n'), 1) && $competition[$cid]->getEntriesCount() >= 15)
	$winnerlist = array_merge($winnerlist, array_values(EntryList::getByCidAndRank($cid, 1)));

$entrycache = Entry::getArray($winnerlist);
unset($winnerlist);
foreach ($entrycache as $eid => $entry) if ($entry->getStatus() == $ENTRY_STATUS['POSTED'])
	$eligiblelist[]= $eid;

echo '<div class="hint clearboth">',
	 '<div class="hint_title">',
	 '<translate id="PRIZE_PAST_WINNERS_TITLE">',
	 'Past prize winners',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint-->';
	 
$prizewinnerlist = PrizeWinnerList::getAll();
$prizewinnercache = PrizeWinner::getArray($prizewinnerlist);
$prizewinnerdate = array();

foreach ($prizewinnercache as $eid => $prizewinner)
	$prizewinnerdate[$eid] = $prizewinner->getCreationTime();

arsort($prizewinnerdate);

foreach ($prizewinnerdate as $eid => $creation_time) {
	$entry = Entry::get($eid);
	$pid = $entry->getPid();
	
	$entry_user = User::get($entry->getUid());
	$theme = Theme::get($competition[$entry->getCid()]->getTid());
	$competitionname = $theme->getTitle();
	$title = '<translate id="INDEX_WINNING_ENTRY_TITLE"><string value="'.String::htmlentities($entry_user->getUniqueName()).'"/> won <string value="'.String::htmlentities('"'.$competitionname.'"').'"/> <duration value="'.(gmmktime() - $competition[$entry->getCid()]->getEndTime()).'"/> ago</translate>';
	$title = INML::processHTML($user, I18N::translateHTML($user, $title));
	
	echo '<div class="winner_container">';
	
	if ($pid !== null) {
		echo '<picture title="',$title,'" href="',$PAGE['ENTRY'],'?lid=',$user->getLid(),'#eid=',$entry->getEid(),'" class="winner_picture clearboth" ',($pid === null?'':'pid="'.$pid.'"'),' size="medium"/>',
			 '<profile_picture class="winner_picture" uid="',$entry->getUid(),'" size="medium" />',
			 '<div class="winner">',
			 '<translate id="PRIZE_MONTHLY_WINNER">',
			 '<user_name uid="',$entry->getUid(),'"/> took <b>100€</b> home in our monthly prize draw <duration value="'.(time() - $creation_time).'"/> ago.',
			 '</translate>',
			 '</div> <!-- winner -->';
	}
	
	echo '</div> <!-- winner_container -->';
}

$entry = Entry::get('65820');
$pid = $entry->getPid();

$entry_user = User::get($entry->getUid());
$theme = Theme::get($competition[$entry->getCid()]->getTid());
$competitionname = $theme->getTitle();
$title = '<translate id="INDEX_WINNING_ENTRY_TITLE"><string value="'.String::htmlentities($entry_user->getUniqueName()).'"/> won <string value="'.String::htmlentities('"'.$competitionname.'"').'"/> <duration value="'.(gmmktime() - $competition[$entry->getCid()]->getEndTime()).'"/> ago</translate>';
$title = INML::processHTML($user, I18N::translateHTML($user, $title));

echo '<div class="winner_container">';

if ($pid !== null) {
	echo '<picture title="',$title,'" href="',$PAGE['ENTRY'],'?lid=',$user->getLid(),'#eid=',$entry->getEid(),'" class="winner_picture clearboth" ',($pid === null?'':'pid="'.$pid.'"'),' size="medium"/>',
		 '<profile_picture class="winner_picture" uid="',$entry->getUid(),'" size="medium" />',
		 '<div class="winner">',
		 '<translate id="PRIZE_ALL_STARS_2009_WINNER">',
		 '<user_name uid="',$entry->getUid(),'"/> took a <a href="http://www.lensbaby.com/lenses-composer.php">Lensbaby Composer</a> home for winning <a href="',$PAGE['MARCH_PRIZE'],'?lid=',$user->getLid(),'">inspi.re all stars</a> in June 2009.',
		 '</translate>',
		 '</div> <!-- winner -->';
}

echo '</div> <!-- winner_container -->';

$entry = Entry::get('77754');
$pid = $entry->getPid();

$entry_user = User::get($entry->getUid());
$theme = Theme::get($competition[$entry->getCid()]->getTid());
$competitionname = $theme->getTitle();
$title = '<translate id="INDEX_WINNING_ENTRY_TITLE"><string value="'.String::htmlentities($entry_user->getUniqueName()).'"/> won <string value="'.String::htmlentities('"'.$competitionname.'"').'"/> <duration value="'.(gmmktime() - $competition[$entry->getCid()]->getEndTime()).'"/> ago</translate>';
$title = INML::processHTML($user, I18N::translateHTML($user, $title));

echo '<div class="winner_container">';

if ($pid !== null) {
	echo '<picture title="',$title,'" href="',$PAGE['ENTRY'],'?lid=',$user->getLid(),'#eid=',$entry->getEid(),'" class="winner_picture clearboth" ',($pid === null?'':'pid="'.$pid.'"'),' size="medium"/>',
		 '<profile_picture class="winner_picture" uid="',$entry->getUid(),'" size="medium" />',
		 '<div class="winner">',
		 '<translate id="PRIZE_ALL_STARS_2009_RUNNER_UP">',
		 '<user_name uid="',$entry->getUid(),'"/> took <a href="',$PAGE['PREMIUM'],'?lid=',$user->getLid(),'">6 months of premium membership</a> home for being a runner-up in <a href="',$PAGE['MARCH_PRIZE'],'?lid=',$user->getLid(),'">inspi.re all stars</a> in June 2009.',
		 '</translate>',
		 '</div> <!-- winner -->';
}

echo '</div> <!-- winner_container -->';

$entry = Entry::get('76517');
$pid = $entry->getPid();

$entry_user = User::get($entry->getUid());
$theme = Theme::get($competition[$entry->getCid()]->getTid());
$competitionname = $theme->getTitle();
$title = '<translate id="INDEX_WINNING_ENTRY_TITLE"><string value="'.String::htmlentities($entry_user->getUniqueName()).'"/> won <string value="'.String::htmlentities('"'.$competitionname.'"').'"/> <duration value="'.(gmmktime() - $competition[$entry->getCid()]->getEndTime()).'"/> ago</translate>';
$title = INML::processHTML($user, I18N::translateHTML($user, $title));

echo '<div class="winner_container">';

if ($pid !== null) {
	echo '<picture title="',$title,'" href="',$PAGE['ENTRY'],'?lid=',$user->getLid(),'#eid=',$entry->getEid(),'" class="winner_picture clearboth" ',($pid === null?'':'pid="'.$pid.'"'),' size="medium"/>',
		 '<profile_picture class="winner_picture" uid="',$entry->getUid(),'" size="medium" />',
		 '<div class="winner">',
		 '<translate id="PRIZE_ALL_STARS_2009_RUNNER_UP">',
		 '<user_name uid="',$entry->getUid(),'"/> took <a href="',$PAGE['PREMIUM'],'?lid=',$user->getLid(),'">6 months of premium membership</a> home for being a runner-up in <a href="',$PAGE['MARCH_PRIZE'],'?lid=',$user->getLid(),'">inspi.re all stars</a> in June 2009.',
		 '</translate>',
		 '</div> <!-- winner -->';
}

echo '</div> <!-- winner_container -->';

$page->endHTML();
$page->render();
?>
