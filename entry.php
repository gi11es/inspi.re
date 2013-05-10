<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Where users pick the competition they're about to vote on
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitymoderator.php');
require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/competitionlist.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/entryvote.php');
require_once(dirname(__FILE__).'/entities/entryvotelist.php');
require_once(dirname(__FILE__).'/entities/favoritelist.php');
require_once(dirname(__FILE__).'/entities/picture.php');
require_once(dirname(__FILE__).'/entities/pointsvalue.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/image.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
$uid = $user->getUid();

$ishome = isset($_REQUEST['home']) && strcasecmp($_REQUEST['home'], 'true') == 0;

if (isset($_REQUEST['eid'])) {
	if (isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], 'facebookexternalhit')) {
		$entry = Entry::get($_REQUEST['eid']);

		$cid = $entry->getCid();
	
		$competition = Competition::get($cid);
		$community = Community::get($competition->getXid());
		$theme = Theme::get($competition->getTid());
		
		$author = User::get($entry->getUid());
		$author_name = $author->getUniqueName();

		$page = new Page('ENTRY', 'COMPETITIONS', $user);
		
		if ($competition->getStatus() == $COMPETITION_STATUS['CLOSED'])	
			$page->setTitle('<translate id="ENTRY_PAGE_TITLE_HOF"><string value="'.String::fromaform($author_name).'"/>\'s entry in the "<string value="'.String::fromaform($theme->getTitle()).'"/>" competition of the <string value="'.String::fromaform($community->getName()).'"/> community on inspi.re</translate>');
		
		$page->startHTML();
		
		echo '<picture pid="',$entry->getPid(),'" size="big"/>';
		
		$page->endHTML();
		$page->render();
	} else header('Location: '.$PAGE['ENTRY'].'?lid='.$user->getLid().(isset($_REQUEST['highlight'])?'&highlight='.$_REQUEST['highlight']:'').($ishome?'&home=true':'').'#eid='.$_REQUEST['eid']);
	exit(0);
} elseif (isset($_REQUEST['token'])) {
	header('Location: '.$PAGE['ENTRY'].'?lid='.$user->getLid().(isset($_REQUEST['highlight'])?'&highlight='.$_REQUEST['highlight']:'').($ishome?'&home=true':'').'#token='.$_REQUEST['token']);
	exit(0);
} elseif (isset($_REQUEST['persistenttoken'])) {
	header('Location: '.$PAGE['ENTRY'].'?lid='.$user->getLid().(isset($_REQUEST['highlight'])?'&highlight='.$_REQUEST['highlight']:'').($ishome?'&home=true':'').'#persistenttoken='.$_REQUEST['persistenttoken']);
	exit(0);
}

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);

$hideads = ($ispremium && $user->getHideAds());

$page = new Page('ENTRY', 'COMPETITIONS', $user);
$page->addStyle('VOTE');

$page->addJavascript('ENTRY');
$page->addHeadJavascript('TINY_MCE');

if ($user->getTranslate()) $page->addJavascriptVariable('translate', true);

$page->addJavascriptVariable('language', strtolower($LANGUAGE_CODE[$user->getLid()]));
$page->addJavascriptVariable('tiny_mce_language', strtolower($TINY_MCE_LANGUAGE[$user->getLid()]));

$page->addJavascriptVariable('viewer_uid', $uid);
$page->addJavascriptVariable('ishome', $ishome);
$page->addJavascriptVariable('request_entry', $REQUEST['ENTRY']);
$page->addJavascriptVariable('request_next_entry', $REQUEST['NEXT_ENTRY']);
$page->addJavascriptVariable('request_previous_entry', $REQUEST['PREVIOUS_ENTRY']);
$page->addJavascriptVariable('request_cast_entry_vote', $REQUEST['CAST_ENTRY_VOTE']);
$page->addJavascriptVariable('request_new_comment', $REQUEST['NEW_COMMENT']);
$page->addJavascriptVariable('request_transfer_points', $REQUEST['TRANSFER_POINTS']);
$page->addJavascriptVariable('request_reply_to_comment', $REQUEST['REPLY_TO_COMMENT']);
$page->addJavascriptVariable('request_persistent_token_to_eid', $REQUEST['PERSISTENT_TOKEN_TO_EID']);

$page->addJavascriptvariable('page_entry_order', $PAGE['ENTRY_ORDER'].'?lid='.$user->getLid().'&');

$page->addJavascriptVariable('edit_href', $PAGE['ENTER'].'?lid='.$user->getLid().'&cid=');

$page->addJavascriptVariable('delete_href', $REQUEST['DELETE_ENTRY'].'?');
$page->addJavascriptVariable('delete_title', '<translate id="ENTRY_DELETE_CONFIRMATION_TITLE" escape="htmlentities">Do you really want to delete this entry?</translate>');
$page->addJavascriptVariable('delete_text', '<translate id="ENTRY_DELETE_CONFIRMATION_TEXT" escape="htmlentities">This action can\'t be undone! This artwork will be permanently deleted form our server, however its ranking in the hall of fame will remain (rankings can\'t be deleted).</translate>');
$page->addJavascriptVariable('delete_yes', '<translate id="THEME_DELETE_CONFIRMATION_YES" escape="htmlentities">Yes, go ahead</translate>');
$page->addJavascriptVariable('delete_no', '<translate id="THEME_DELETE_CONFIRMATION_NO" escape="htmlentities">No</translate>');

$page->addJavascriptVariable('disqualify_href', $REQUEST['DISQUALIFY'].'?');
$page->addJavascriptVariable('disqualify_title', '<translate id="ENTRY_DISQUALIFY_CONFIRMATION_TITLE" escape="htmlentities">Do you really want to disqualify this entry?</translate>');
$page->addJavascriptVariable('disqualify_text', '<translate id="ENTRY_DISQUALIFY_CONFIRMATION_TEXT" escape="htmlentities">This action can later be undone. Please make sure that you leave a comment on the entry explaining why you\'re disqualifying it, so that the author can understand why this is happening to him/her.</translate>');
$page->addJavascriptVariable('disqualify_yes', '<translate id="ENTRY_DISQUALIFY_CONFIRMATION_YES" escape="htmlentities">Yes, go ahead</translate>');
$page->addJavascriptVariable('disqualify_no', '<translate id="ENTRY_DISQUALIFY_CONFIRMATION_NO" escape="htmlentities">No</translate>');

$page->addJavascriptVariable('too_fast_title', '<translate id="ENTRY_VOTING_TOO_FAST_TITLE" escape="htmlentities">You are voting too fast</translate>');
$page->addJavascriptVariable('too_fast_text', '<translate id="ENTRY_VOTING_TOO_FAST_TEXT" escape="htmlentities">Voting is not about earning points as fast as possible. The speed at which you\'ve been voting can\'t possibly allow you to truly judge the artworks you\'ve been looking at.<br/><br/>Your access to voting is blocked for the next 2 hours. We suggest that you critique artworks in the meantime. If your comments are marked as insightful, you\'ll earn points.</translate>');
$page->addJavascriptVariable('same_vote_title', '<translate id="ENTRY_VOTING_SAME_TITLE" escape="htmlentities">Artworks can\'t be all worth the same amount of stars</translate>');
$page->addJavascriptVariable('same_vote_text', '<translate id="ENTRY_VOTING_SAME_TEXT" escape="htmlentities">Voting is not about earning points as fast as possible. By rating every single entry with the same amount of stars, you\'re not giving useful feedback.<br/><br/>Your access to voting is blocked for the next 24 hours. We suggest that you critique artworks in the meantime. If your comments are marked as insightful, you\'ll earn points.</translate>');
$page->addJavascriptVariable('voting_warning_ok', '<translate id="ENTRY_VOTING_WARNING_OK" escape="htmlentities">OK</translate>');

$page->addJavascriptVariable('comment_discard_title', '<translate id="ENTRY_DISCARD_COMMENT_TITLE" escape="htmlentities">Are you sure that you want to discard your current comment?</translate>');
$page->addJavascriptVariable('comment_discard_text', '<translate id="ENTRY_DISCARD_COMMENT_TEXT" escape="htmlentities">Navigating away from the current entry will discard the comment you\'ve started writing.</translate>');

$page->addJavascriptVariable('requalify_href', $REQUEST['REQUALIFY'].'?');

$page->addJavascriptVariable('favorite_add', 
						 '<translate id="ENTRY_ADD_TO_FAVORITES" escape="htmlentities">Add this artwork to your favorites</translate>');

$page->addJavascriptVariable('favorite_remove', 
						 '<translate id="ENTRY_REMOVE_FROM_FAVORITES" escape="htmlentities">Remove this artwork from your favorites</translate>');

$page->addJavascriptVariable('request_add_to_favorites', $REQUEST['ADD_TO_FAVORITES']);
$page->addJavascriptVariable('request_remove_from_favorites', $REQUEST['REMOVE_FROM_FAVORITES']);
$page->addJavascriptVariable('request_toggle_entry_comment_notification', $REQUEST['TOGGLE_ENTRY_COMMENT_NOTIFICATION']);


if (isset($_REQUEST['highlight']))
	$page->addJavascriptVariable('highlight', $_REQUEST['highlight']);

$page->startHTML();

echo '<div id="vote_header" class="hint hintmargin" style="display:none">';
echo '<div class="hint_title">';

echo '<div style="float:left">';
echo '<translate id="ENTRY_HINT_TITLE">';
echo 'Cast your vote and critique this entry';
echo '</translate>';
echo '</div>';

echo '<div class="favorite">';
echo '<img class="favorite_icon" src="'.$GRAPHICS_PATH.'heart_inactive.png" alt="Not favourited yet"/>';
echo '</div>';

echo '<div class="purchase">';
echo '<img alt="<translate id="ENTRY_PURCHASE_LINK"  escape="htmlentities">Order a canvas print of this artwork</translate>"title="<translate id="ENTRY_PURCHASE_LINK"  escape="htmlentities">Order a canvas print of this artwork</translate>" class="purchase_icon" src="'.$GRAPHICS_PATH.'minicart.png"/>';
echo '</div>';

echo '</div> <!-- hint_title -->';
echo '<div class="hint_body clearboth">';
echo '<translate id="ENTRY_HINT_BODY">';
echo 'When judging the entry, remember well what the theme of the competition is and what the rules for this community are';
echo '</translate>';
echo '</div> <!-- hint_body -->';
echo '</div> <!-- hint -->';

echo '<div id="disqualified_entry" class="warning hintmargin" style="display:none">';
echo '<div class="warning_title">';
echo '<translate id="ENTRY_DISQUALIFIED_TITLE">';
echo 'This entry has been disqualified by a moderator of this community';
echo '</translate>';
echo '</div> <!-- warning_title -->';

echo '<div class="warning_body">';
echo '<translate id="ENTRY_DISQUALIFIED_BODY_REPLACE">';
echo 'Since the competition is still open, you can replace your current entry with another one';
echo '</translate>';
echo '</div> <!-- warning_body -->';
echo '</div> <!-- warning -->';

echo '<div id="disqualified_entry_moderator" class="warning hintmargin" style="display:none">';
echo '<div class="warning_title">';
echo '<translate id="ENTRY_DISQUALIFIED_TITLE">';
echo 'This entry has been disqualified by a moderator of this community';
echo '</translate>';
echo '</div> <!-- warning_title -->';

echo '<div class="warning_body">';
echo '<translate id="ENTRY_DISQUALIFIED_BODY_EXPLANATION">';
echo 'Please make sure that the participant was given an appropriate explanation as to why his/her entry has been disqualified.';
echo '</translate>';
echo '</div> <!-- warning_body -->';
echo '</div> <!-- warning -->';

echo '<div class="hint hintmargin" id="entry_author" style="display:none">';
echo '</div> <!-- entry_author -->';

echo '<div id="competition_description">';
echo '</div> <!-- competition_description -->';

echo '<div id="entry_container">';
echo '<div id="entry_loader" style="display:none">';
echo '<translate id="ENTRY_LOADING">';
echo 'Please wait while the artwork is loading';
echo '</translate>';
echo '<br/>';
echo '<img src="'.$GRAPHICS_PATH.'entryloader.gif" alt="The entry is loading"/>';
echo '</div>';
echo '<div id="entry_mold">';
echo '<div id="entry_disqualified_overlay" style="display: none; background-image: url(\''.Image::getDisqualificationOverlay($user).'\')"></div>';
echo '<img alt="Photo competition entry" id="entry" src=""/>';
echo '</div> <!-- entry_mold -->';
echo '</div>';

echo '<div id="stars" style="display:none">';
echo '<img alt="Previous competition entry" id="previous" src="'.$GRAPHICS_PATH.'previous.gif?2"/>';
echo '<div id="actual_stars" style="display:none">';

for ($i = 1; $i <= 5; $i++)	echo '<img alt="Give '.$i.' star to this competition entry" id="star_'.$i.'" class="star" src="'.$GRAPHICS_PATH.'star-off.gif"/>';

echo '</div> <!-- actual_stars -->';
echo '<img alt="Next competition entry" id="next" src="'.$GRAPHICS_PATH.'next.gif?2"/>';
echo '</div> <!-- stars -->';

echo '<div id="keyboard_hint" style="display:none">';
echo '<translate id="ENTRY_KEYBOARD_HINT">';
echo 'You can use left arrow, right arrow, 1, 2, 3, 4 and 5 on your keyboard to navigate and vote. Press X to cancel your vote, F to add to/remove from your favorites.';
echo '</translate>';
echo '</div>';

echo '<div id="entry_edit_link" style="display:none">';
echo '<a href="javascript:editEntry();">';
echo '<translate id="ENTRY_EDIT_LINK">';
echo 'Edit this entry\'s settings, replace it or delete it';
echo '</translate>';
echo '</a>';
echo '</div> <!-- entry_edit_link -->';

echo '<div id="entry_delete_link" class="entry_link" style="display:none">';

echo '<a href="javascript:deleteEntry();">';
echo '<translate id="DELETE_EDIT_LINK">';
echo 'Delete this entry';
echo '</translate>';
echo '</a>';
echo '</div> <!-- entry_edit_link -->';

echo '<div id="entry_exif_link" class="entry_link" style="display:none">';

echo '<a href="javascript:showEXIF();">';
echo '<translate id="ENTRY_EXIF_LINK">';
echo 'View EXIF data';
echo '</translate>';
echo '</a>';
echo '</div> <!-- entry_exif_link -->';

echo '<div id="entry_exif_link_hide" class="entry_link" style="display:none">';

echo '<a href="javascript:hideEXIF();">';
echo '<translate id="ENTRY_EXIF_LINK_HIDE">';
echo 'Hide EXIF data';
echo '</translate>';
echo '</a>';
echo '</div> <!-- entry_exif_link -->';

echo '<div id="exif" class="exif" style="display:none">';
echo '</div> <!-- exif -->';

// If an administrator or a moderator is viewing, he/she has the ability to disqualify the entry

echo '<div id="entry_disqualify_link" class="entry_link" style="display:none">';

echo '<a href="javascript:disqualifyEntry();">';
echo '<translate id="ENTRY_DISQUALIFY_LINK">';
echo 'Disqualify this entry';
echo '</translate>';
echo '</a>';

echo '</div> <!-- entry_disqualify_link -->';

echo '<div id="entry_requalify_link" class="entry_link" style="display:none">';

echo '<a href="javascript:requalifyEntry();">';
echo '<translate id="ENTRY_REQUALIFY_LINK">';
echo 'Requalify this entry';
echo '</translate>';
echo '</a>';

echo '</div> <!-- entry_requalify_link -->';

echo '<div id="entry_ability_link" class="entry_link" style="display:none">';
echo '<translate id="ENTRY_DISQUALIFY_ADMINISTRATOR">';
echo 'You need to <a href="'.UI::RenderUserLink($uid).'">nominate yourself</a> as a community moderator before you can disqualify entries';
echo '</translate>';
echo '</div> <!-- entry_edit_link -->';

echo '<div id="vote_repartition">';
echo '</div> <!-- vote_repartition -->';		

echo '<ad ad_id="ENTRY_BOTTOM"/>';

echo '<div id="big_commentator" class="unselectable warning belowmargin" style="display:none">';
echo '<div class="warning_title">';
echo '<translate id="ENTRY_BIG_COMMENTATOR_TITLE">';
echo 'You should write a comment/critique for this artwork';
echo '</translate>';
echo '</div> <!-- warning_title -->';
echo '<translate id="ENTRY_BIG_COMMENTATOR_BODY">';
echo 'The author of this entry has written more comments on other people\'s artwork than he/she has received. As a member of the inspi.re community, you should give back to this person by commenting on this artwork.';
echo '</translate>';
echo '</div> <!-- big_commentator -->';

echo '<div id="comments_header" class="comment_thread">';
echo '</div>';

echo '<textarea id="comment_text" class="tinymce_textarea" style="display:none" minimum="1" maximum="5000"></textarea>';

echo '<div id="comment_actions" class="clearboth" style="display:none">';

echo '<div class="comment_action">';
echo '<span id="post_comment">';
echo '<a href="javascript:postComment();">';
echo '<translate id="UI_COMMENT_THREAD_HEADER_POST">';
echo 'Post comment';
echo '</translate>';
echo '</a>';
echo '</span>';
echo '</div> <!-- comment_action -->';

echo '<div style="display:none" id="post_please_wait" class="comment_action">';
echo '<translate id="UI_COMMENT_THREAD_HEADER_PLEASE_WAIT">';
echo 'Please wait while your comment is being sent';
echo '</translate>';
echo '</div> <!-- comment_action -->';

echo '</div> <!-- comment_actions -->';

echo '<div class="wide_action">';
echo '<span id="receive_alerts" style="display:none">';
echo '<input type="checkbox" id="toggle_receive_alerts"> ';
echo '<translate id="UI_COMMENT_THREAD_HEADER_RECEIVE_ALERTS">';
echo 'Follow comments left on this entry';
echo '</translate>';
echo '</span>';
echo '</div> <!-- comment_action -->';

echo '<div id="comments" class="clearboth">';
echo '</div> <!-- comments -->';

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['INSIGHTFUL_GIVE']);
$points_insightful_give = -$pointsvalue->getValue();

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['INSIGHTFUL_RECEIVE']);
$points_insightful_receive = $pointsvalue->getValue();

echo '<div class="fixed_centered" id="transfer_points" style="display:none">';
echo '<div class="confirmation_title"><translate id="ENTRY_INSIGHTFUL_TITLE">Mark this comment as insightful</translate></div>';
echo '<div class="confirmation_description"><translate id="ENTRY_INSIGHTFUL_EXPLANATION">Marking this comment as insightful will cost you <integer value="'.$points_insightful_give.'"/> point(s) and will reward the comment\'s author with <integer value="'.$points_insightful_receive.'"/> point(s).</translate></div>';
echo '<div class="confirmation_buttons">';
echo '<input class="confirmation_button_left" type="button" value="<translate id="ENTRY_INSIGHTFUL_GIVE">Confirm</translate>" onclick="javascript:transferPoints();"/>';
echo '<input class="confirmation_button_right" type="button" value="<translate id="ENTRY_INSIGHTFUL_CANCEL">Cancel</translate>" onclick="javascript:hidePointsTransfer();"/>';
echo '</div> <!-- confirmation_buttons -->';
echo '</div> <!-- transfer_points -->';

$page->endHTML();
$page->render();
?>
