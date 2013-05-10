<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users can create a new community or edit an existing one
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitylabellist.php');
require_once(dirname(__FILE__).'/entities/pointsvalue.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$page = new Page('COMMUNITIES', 'COMMUNITIES', $user);
$page->addJavascript('EDIT_COMMUNITY');
$page->startHTML();

$xid = (isset($_REQUEST['xid'])?$_REQUEST['xid']:null);

if ($xid === null) {
$page->setTitle('<translate id="EDIT_COMMUNITY_PAGE_TITLE_CREATE">Create a new community on inspi.re</translate>');
$name = "";
$description = "";
$rules = "";
$frequency = 1;
$vote_length = 2;
$enter_length = 5;
$lid = $user->getLid();
$time_shift = 0;
$maximum_theme_count = null;
$maximum_theme_count_per_member = null;
$theme_minimum_score = null;
$theme_restrict_users = false;
$theme_cost = 5;
$url = $REQUEST['EDIT_COMMUNITY'];

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['COMMUNITY_CREATING']);
$points_create_community = -$pointsvalue->getValue();

if ($user->getPoints() < $points_create_community) {
	echo '<div class="warning hintmargin">';
	echo '<div class="warning_title">';
	echo '<translate id="NEW_COMMUNITY_LACK_OF_FUNDS_TITLE">';
	echo 'You do not have enough points to create your own community';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '<translate id="LACK_OF_FUNDS">';
	echo '<integer value="'.$points_create_community.'"/> points are needed and you only have <integer value="'.$user->getPoints().'"/>. We suggest that you <a href="'.$PAGE['VOTE'].'?lid='.$user->getLid().'">vote and critique entries</a> in order to earn points.';
	echo '</translate>';
	echo '</div> <!-- warning -->';
	$page->endHTML();
	$page->render();
	exit(0);
}

?>
<div class="hint hintmargin">
<div class="hint_title">
<translate id="NEW_COMMUNITY_HINT">Create a new community</translate>
</div> <!-- hint_title -->
<translate id="NEW_COMMUNITY_HINT_BODY">Define the name, description and rules of the new inspi.re community you're about to create.</translate>
 <span id="points_warning"><translate id="NEW_COMMUNITY_POINTS_WARNING">Creating this community will cost you <integer value="<?=$points_create_community?>"/> points.</translate></span>
</div> <!-- hint -->
<?php
} else {
$community = Community::get($xid);
$name = String::fromaform($community->getName());

$page->setTitle('<translate id="EDIT_COMMUNITY_PAGE_TITLE">Change settings for the <string value="'.$name.'"/> community on inspi.re</translate>');

$description = $community->getDescription();
$rules = $community->getRules();
$lid = $community->getLid();
$time_shift = $community->getTimeShift();
$frequency = $community->getFrequency();
$vote_length = $community->getVoteLength();
$enter_length = $community->getEnterLength();
$maximum_theme_count = $community->getMaximumThemeCount();
$maximum_theme_count_per_member = $community->getMaximumThemeCountPerMember();
$theme_minimum_score = $community->getThemeMinimumScore();
$theme_restrict_users = $community->getThemeRestrictUsers();
$theme_cost = $community->getThemeCost();
$url = $REQUEST['EDIT_COMMUNITY'].'?xid='.$xid;

?>
<div class="hint hintmargin">
<div class="hint_title">
<translate id="EDIT_COMMUNITY_HINT">Modify your community</translate>
</div> <!-- hint_title -->
<translate id="EDIT_COMMUNITY_HINT_BODY">Make changes to the parameters or the rules of your community</translate>
</div> <!-- hint -->
<?php
}
?>

<form id="new_community" method="post" onSubmit="return checkFields();" action="<?=$url?>">
<label for="community_name_input"><translate id="NEW_COMMUNITY_NAME">Name:</translate></label><input id="community_name_input" type="text" lefttrimmed="true" minimum="5" maximum="70" name="name" value="<?=$name?>" />
<label for="community_description_input"><translate id="NEW_COMMUNITY_DESCRIPTION">Description:</translate></label><textarea minimumrows="4" autoexpand="true" id="community_description_input" maximum="2000" name="description"><?=$description?></textarea>
<label for="community_rules_input"><translate id="NEW_COMMUNITY_RULES">Rules:</translate></label><textarea id="community_rules_input" minimumrows="5" autoexpand="true" maximum="2000" name="rules"><?=$rules?></textarea>

<div class="left_margin" id="language">
<?php

echo '<translate id="NEW_COMPETITION_LANGUAGE">Community\'s main language:</translate> <select name="lid">';
foreach ($LANGUAGE as $code => $value)
	echo '<option '.($value == $lid?'selected':'').' value="'.$value.'"><language_name lid="'.$value.'" /></option>';
echo '</select>';

?>
</div> <!-- language -->

<div class="left_margin" id="labels">
<div class="community_label_header">
<translate id="EDIT_COMPETITION_LABEL_HEADER">
Keywords that define this community best (select 5 at most):
</translate>
</div> <!-- community_label_header -->
<?php

if ($xid !== null)
	$labellist = CommunityLabelList::getByXid($xid);
else
	$labellist = array();

foreach ($COMMUNITY_LABEL_NAME as $clid => $name) {
	echo '<div class="community_label'.(in_array($clid, $labellist)?'_selected':'').'" id="label_'.$clid.'">';
	echo '<a href="javascript:selectLabel('.$clid.');">';
	echo '<translate id="COMMUNITY_LABEL_'.$clid.'">'.$name.'</translate>';
	echo '</a>';
	echo '</div> <!-- label_'.$clid.' -->';
}

?>
</div> <!-- labels -->

<div class="hint hintmargin hint_left_margin">
<div class="hint_title">
<translate id="EDIT_COMMUNITY_COMPETITIONS_HINT">Competitions options</translate>
</div> <!-- hint_title -->
</div> <!-- hint -->

<div class="left_margin" id="frequency">
<translate id="NEW_COMMUNITY_FREQUENCY">
There is a new competition every <input class="option_field" float="true" id="frequency_field" name="frequency" maximum="4" type="text" value="<?=$frequency?>" /> day(s)
</translate>
</div> <!-- frequency -->

<div class="left_margin" id="enter_length">
<translate id="NEW_COMMUNITY_ENTER_LENGTH">
Members can enter each competition for <input class="option_field" float="true" id="enter_length_field" name="enter_length" maximum="4" type="text" value="<?=$enter_length?>" /> day(s)
</translate>
</div> <!-- enter_length -->

<div class="left_margin" id="vote_length">
<translate id="NEW_COMMUNITY_VOTE_LENGTH">
Members can vote on the entries of each competition for <input class="option_field" float="true" id="vote_length_field" name="vote_length" maximum="4" type="text" value="<?=$vote_length?>" /> day(s)
</translate>
</div> <!-- vote_length -->

<div class="left_margin" id="time_shift">
<?php

echo '<translate id="NEW_COMPETITION_TIME_SHIFT">Competitions will start at</translate> <select name="time_shift">';
for ($i = 0; $i < 24; $i++) {
	$value = $i * 3600;
	echo '<option '.($value == $time_shift?'selected':'').' value="'.$value.'">'.($i<10?'0'.$i:$i).':00</option>';
}
echo '</select> GMT (<translate id="NEW_COMPETITION_TIME_SHIFT_NOW">time now is <gmt_time timestamp="'.time().'" /> GMT</translate>)';

?>
</div> <!-- time_shift -->

<div class="hint hintmargin hint_left_margin">
<div class="hint_title">
<translate id="EDIT_COMMUNITY_THEMES_HINT">Theme suggestions options</translate>
</div> <!-- hint_title -->
</div> <!-- hint -->

<div class="left_margin" id="theme_cost">
<translate id="NEW_COMMUNITY_THEME_COST">
Themes cost <input class="option_field" numerical="true" id="theme_cost_field" name="theme_cost" maximum="4" type="text" value="<?=$theme_cost?>" /> point(s) each to suggest
</translate>
</div> <!-- theme_cost -->

<div class="left_margin" id="theme_count">
<input type="checkbox" id="maximum_theme_count_checkbox"  name="maximum_theme_count_checkbox" value="on" <?=$maximum_theme_count === null?'':'checked="yes"'?>/> <translate id="NEW_COMPETITION_MAXIMUM_THEME_COUNT">The maximum amount of themes in the suggestions queue is <input class="option_field" numerical="true" type="text" name="maximum_theme_count" id="maximum_theme_count_field" maximum="4" value="<?=$maximum_theme_count === null?200:$maximum_theme_count?>"/></translate>
</div> <!-- theme_count -->

<div class="left_margin" id="theme_count_per_member">
<input type="checkbox" id="maximum_theme_count_per_member_checkbox" name="maximum_theme_count_per_member_checkbox" value="on" <?=$maximum_theme_count_per_member === null?'':'checked="yes"'?>/> <translate id="NEW_COMPETITION_MAXIMUM_THEME_COUNT_PER_MEMBER">The maximum amount of themes per member in the suggestions queue is <input class="option_field" numerical="true" type="text" name="maximum_theme_count_per_member" id="maximum_theme_count_per_member_field" maximum="4" value="<?=$maximum_theme_count_per_member === null?2:$maximum_theme_count_per_member?>"/></translate>
</div> <!-- theme_count_per_member -->

<div class="left_margin" id="theme_minimum_score">
<input type="checkbox" id="theme_minimum_score_checkbox" name="theme_minimum_score_checkbox" value="on" <?=$theme_minimum_score === null?'':'checked="yes"'?>/> <translate id="NEW_COMPETITION_THEME_MINIMUM_SCORE">Automatically delete theme suggestions whose score goes below <input class="option_field" signed="true" type="text" name="theme_minimum_score" id="theme_minimum_score_field" maximum="4" value="<?=$theme_minimum_score === null?-10:$theme_minimum_score?>"/></translate>
</div> <!-- theme_minimum_score -->

<div class="left_margin" id="theme_restrict_users">
<input type="checkbox" id="theme_restrict_users_checkbox"  name="theme_restrict_users_checkbox" value="on" <?=$theme_restrict_users?'checked="yes"':''?>/> <translate id="NEW_COMPETITION_RESTRICT_THEME_USERS">Only the administrator and moderators can suggest competition themes</translate>
</div> <!-- theme_restrict_users -->

<div id="invalid_messages">
<span class="invalid_message" id="community_name_too_short"><translate id="NEW_COMMUNITY_NAME_TOO_SHORT">Name is too short</translate></span>
<span class="invalid_message" id="community_name_too_long" style="display:none"><translate id="NEW_COMMUNITY_NAME_TOO_LONG">Name is too long</translate></span>
<span class="invalid_message" id="community_description_too_long" style="display:none"><translate id="NEW_COMMUNITY_DESCRIPTION_TOO_LONG">Description is too long</translate></span>
<span class="invalid_message" id="community_rules_too_long" style="display:none"><translate id="NEW_COMMUNITY_RULES_TOO_LONG">Rules are too long</translate></span>

<span class="invalid_message" id="frequency_invalid" style="display:none"><translate id="NEW_COMMUNITY_FREQUENCY_INVALID">The competition frequency requires a value greater than 0</translate></span>
<span class="invalid_message" id="enter_length_invalid" style="display:none"><translate id="NEW_COMMUNITY_ENTER_LENGTH_INVALID">The duration during which members can enter a competition requires a value greater than 0</translate></span>
<span class="invalid_message" id="vote_length_invalid" style="display:none"><translate id="NEW_COMMUNITY_VOTE_LENGTH_INVALID">The duration during which members can vote on entries requires a value greater than 0</translate></span>
<span class="invalid_message" id="maximum_theme_count_invalid" style="display:none"><translate id="NEW_COMMUNITY_THEME_COUNT_INVALID">The maximum amount of themes requires a value greater than 0</translate></span>
<span class="invalid_message" id="maximum_theme_count_per_member_invalid" style="display:none"><translate id="NEW_COMMUNITY_THEME_COUNT_PER_MEMBER_INVALID">The maximum amount of themes per member requires a value greater than 0</translate></span>
<span class="invalid_message" id="theme_minimum_score_invalid" style="display:none"><translate id="NEW_COMMUNITY_THEME_MINIMUM_SCORE_INVALID">The minimum score for a theme requires a value lesser than or equal to 0</translate></span>
<span class="invalid_message" id="theme_cost_invalid" style="display:none"><translate id="NEW_COMMUNITY_THEME_COST_INVALID">The cost of suggesting a theme requires a value greater than or equal to 0</translate></span>
</div> <!-- invalid_message -->

<input type="hidden" id="labels_input" name="labels_input" value="<?=json_encode($labellist)?>">

<?php
if ($xid === null) {
?>
<div>
<input id="new_community_submit" type="submit" value="<translate id="NEW_COMMUNITY_SUBMIT">Create new community</translate>" disabled="">
</div>
<?php
} else {
?>
<div>
<input id="new_community_submit" type="submit" value="<translate id="EDIT_COMMUNITY_SUBMIT">Update community</translate>" disabled="">
</div>
<?php
}
?>


</form>

<?php

$page->endHTML();
$page->render();
?>