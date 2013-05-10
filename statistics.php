<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	General statistics about the website
*/

require_once(dirname(__FILE__).'/entities/competitionlist.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/entities/userlist.php');
require_once(dirname(__FILE__).'/utilities/cache.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

require_once(dirname(__FILE__).'/libraries/open_flash_chart_object.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());

if (!in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	header('Location: '.$PAGE['404']);
	exit(0);
}

$page = new Page('STATISTICS', 'HOME', $user);

$page->startHTML();

$competitionlist = CompetitionList::getByStatus($COMPETITION_STATUS['CLOSED']);

$competitionentrycount = array();

arsort($competitionlist);
$competitionlist = array_slice($competitionlist, 0, 300, true);

foreach ($competitionlist as $cid => $start_time) {
	$competitionentrycount[$cid] = count(EntryList::getByCid($cid));
}

echo 'Average entries per competition = '.(array_sum($competitionentrycount) / count($competitionentrycount));

$users = UserList::getByStatus($USER_STATUS['ACTIVE']);
$recent_users = UserList::getRegistered24Hours();

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo '<translate id="STATISTICS_USERS">';
echo 'Users statistics';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

echo '<div>';
echo '<translate id="STATISTICS_TOTAL_REGISTERED">';
echo 'There are <integer value="'.count($users).'"/> registered users (<integer value="'.count($recent_users).'"/> in the last 24 hours)';
echo '</translate>';
echo '</div>';

unset($users);
unset($recent_users);

$active_users_30_days = UserList::getActive30Days();

$users = User::getArray(array_keys($active_users_30_days));
$points = array();
$morethanx = 0;

foreach ($users as $uid => $user) {
	$points[$uid] = $user->getPoints();
	if ($points[$uid] > 100) $morethanx++;
}

echo 'Average points per user = '.(array_sum($points)/count($points)).' '.((100 *$morethanx)/count($points)).'% have more than 100';

echo '<div>';
echo '<translate id="STATISTICS_ACTIVE_30_DAYS">';
echo '<integer value="'.count($active_users_30_days).'"/> monthly active users (used the website over the last 30 days)';
echo '</translate>';
echo '</div>';

unset($active_users_30_days);

$active_users_24_hours = UserList::getActive24Hours();

echo '<div class="hintmargin">';
echo '<translate id="STATISTICS_ACTIVE_24_HOURS">';
echo '<integer value="'.count($active_users_24_hours).'"/> daily active users (used the website over the last 24 hours)';
echo '</translate>';
echo '</div>';

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo 'Active members';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

echo '<div class="statistic_chart">';
open_flash_chart_object(930, 250, $REQUEST['STATISTIC_DATA'].'?sid='.$STATISTIC['ACTIVE_MEMBERS'], false);
echo '</div> <!-- statistic_chart -->';

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo 'Registrations';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

echo '<div class="statistic_chart">';
open_flash_chart_object(930, 250, $REQUEST['STATISTIC_DATA'].'?sid='.$STATISTIC['REGISTRATIONS'], false);
echo '</div> <!-- statistic_chart -->';

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo 'Entries uploaded';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

echo '<div class="statistic_chart">';
open_flash_chart_object(930, 250, $REQUEST['STATISTIC_DATA'].'?sid='.$STATISTIC['ENTRIES'], false);
echo '</div> <!-- statistic_chart -->';

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo 'Votes cast';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

echo '<div class="statistic_chart">';
open_flash_chart_object(930, 250, $REQUEST['STATISTIC_DATA'].'?sid='.$STATISTIC['VOTES'], false);
echo '</div> <!-- statistic_chart -->';

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo 'Comments word count';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

echo '<div class="statistic_chart">';
open_flash_chart_object(930, 250, $REQUEST['STATISTIC_DATA'].'?sid='.$STATISTIC['COMMENTS_WORDCOUNT'], false);
echo '</div> <!-- statistic_chart -->';

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo 'Average comment word count per entry';
echo '</div> <!-- hint_title -->';
echo '</div> <!-- hint -->';

echo '<div class="statistic_chart">';
open_flash_chart_object(930, 250, $REQUEST['STATISTIC_DATA'].'?sid='.$STATISTIC['COMMENTS_ENTRIES_RATIO'], false);
echo '</div> <!-- statistic_chart -->';

$page->endHTML();
$page->render();
?>