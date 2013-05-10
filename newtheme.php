<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users can suggest new competition themes
*/

require_once(dirname(__FILE__)."/entities/community.php");
require_once(dirname(__FILE__)."/entities/communitymoderatorlist.php");
require_once(dirname(__FILE__)."/entities/pointsvalue.php");
require_once(dirname(__FILE__)."/entities/user.php");
require_once(dirname(__FILE__)."/utilities/page.php");
require_once(dirname(__FILE__)."/utilities/ui.php");
require_once(dirname(__FILE__)."/constants.php");
require_once(dirname(__FILE__)."/settings.php");

$user = User::getSessionUser();

$page = new Page('THEMES', 'COMPETITIONS', $user);
$page->addJavascript('NEW_THEME');

$page->startHTML();

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;
$page_offset = isset($_REQUEST['page'])?$_REQUEST['page']:1;

if ($xid !== null) {
	try {
		$community = Community::get($xid);
		$title = String::fromaform($community->getName());
	} catch (CommunityException $e) {
		header('Location: '.$PAGE['THEMES'].'?lid='.$user->getLid());
		exit(0);
	}
} else {
	header('Location: '.$PAGE['THEMES'].'?lid='.$user->getLid());
	exit(0);
}

$page->setTitle('<translate id="NEW_THEME_PAGE_TITLE">Suggest a new competition theme for the <string value="'.$title.'"/> community on inspi.re</translate>');

if ($community->getThemeRestrictUsers()) {
	$moderatedcommunitylist = CommunityModeratorList::getByUid($user->getUid());
	if (in_array($xid, $moderatedcommunitylist) || $community->getUid() == $user->getUid())
		$able_to_suggest = true;
	else {
		header('Location: '.$PAGE['THEMES'].'?lid='.$user->getLid());
		exit(0);
	}
} else {
	$able_to_suggest = true;
}

$points_suggest_theme = $community->getThemeCost();

if ($user->getPoints() < $points_suggest_theme) {
	echo '<div class="warning hintmargin">';
	echo '<div class="warning_title">';
	if ($xid !== null) {
		echo '<translate id="NEW_THEME_LACK_OF_FUNDS_TITLE">';
		echo 'You do not have enough points to suggest a theme';
		echo '</translate>';
	} else {
		echo '<translate id="UPCOMINGE_LACK_OF_FUNDS_TITLE">';
		echo 'You do not have enough points to suggest a new feature';
		echo '</translate>';	
	}
	echo '</div> <!-- warning_title -->';
	echo '<translate id="LACK_OF_FUNDS">';
	echo '<integer value="'.$points_suggest_theme.'"/> points are needed and you only have <integer value="'.$user->getPoints().'"/>. We suggest that you <a href="'.$PAGE['VOTE'].'?lid='.$user->getLid().'">vote and critique entries</a> in order to earn points.';
	echo '</translate>';
	echo '</div> <!-- warning -->';
	$page->endHTML();
	$page->render();
	exit(0);
}

echo '<div class="hint hintmargin">';
echo '<div class="hint_big_title">';
echo $title;
echo '</div> <!-- hint_big_title -->';
echo '<translate id="NEW_THEME_HINT_BODY">';
echo 'Suggest a new competition theme for this community.';
echo '</translate>';
echo ' <span id="points_warning"><translate id="NEW_THEME_POINTS_WARNING">Suggesting this theme will cost you <integer value="'.$points_suggest_theme.'"/> points.</translate></span>';
echo '</div> <!-- hint -->';

echo '<form id="new_theme" action="'.$REQUEST['NEW_THEME'].($xid !== null?'?xid='.$xid:'').'" onsubmit="return checkFields();" method="post">';
echo '<label for="theme_title_input">';

if ($xid !== null) {
	echo '<translate id="NEW_THEME_TITLE_LABEL">';
	echo 'Theme:';
	echo '</translate>';
} else {
	echo '<translate id="NEW_FEATURE_TITLE_LABEL">';
	echo 'Feature\'s name:';
	echo '</translate>';
}

echo '</label>';
echo '<input id="theme_title_input" type="text" value="" name="title" maximum="80" minimum="1" lefttrimmed="true" />';
echo '<label for="theme_description_input">';
echo '<translate id="NEW_THEME_DESCRIPTION_LABEL">';
echo 'Description:';
echo '</translate>';
echo '</label>';
echo '<textarea id="theme_description_input" name="description" maximum="1500" autoexpand="true" minimumrows="4"></textarea>';

if ($xid !== null)
	echo '<input id="new_theme_submit" type="submit" disabled value="<translate id="NEW_THEME_SUBMIT">Suggest this new theme</translate>" />';
else
	echo '<input id="new_theme_submit" type="submit" disabled value="<translate id="NEW_FEATURE_SUBMIT">Suggest this new feature</translate>" />';
	
echo '<div  class="length_error" id="theme_title_too_short">';

if ($xid !== null) {
	echo '<translate id="NEW_THEME_TITLE_TOO_SHORT">';
	echo 'Theme is too short';
	echo '</translate>';
} else {
	echo '<translate id="NEW_FEATURE_NAME_TOO_SHORT">';
	echo 'Feature\'s name is too short';
	echo '</translate>';
}

echo '</div>';

echo '<div class="length_error" id="theme_title_too_long" style="display:none">';

if ($xid !== null) {
	echo '<translate id="NEW_THEME_TITLE_TOO_LONG">';
	echo 'Theme is too long';
	echo '</translate>';
} else {
	echo '<translate id="NEW_FEATURE_NAME_TOO_LONG">';
	echo 'Feature\'s name is too long';
	echo '</translate>';
}

echo '</div>';

echo '<div class="length_error" id="theme_description_too_long" style="display:none">';

if ($xid !== null) {
	echo '<translate id="NEW_THEME_DESCRIPTION_TOO_LONG">';
	echo 'Description is too long';
	echo '</translate>';
} else {
	echo '<translate id="NEW_FEATURE_DESCRIPTION_TOO_LONG">';
	echo 'Feature\'s description is too long';
	echo '</translate>';
}

echo '</div>';


echo '</form>';

$page->endHTML();
$page->render();
?>
