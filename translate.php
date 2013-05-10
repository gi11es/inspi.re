<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	This is an example page with all the menus and empty content
*/

require_once(dirname(__FILE__).'/entities/i18n.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());

if (!in_array($USER_LEVEL['TRANSLATOR'][$user->getLid()], $levels))
	header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());

$page = new Page('TRANSLATE', 'HOME', $user);
$page->addHeadJavascript('WEBTOOLKIT');
$page->addJavascriptVariable('request_translate', $REQUEST['TRANSLATE']);
$page->addJavascriptVariable('request_search_translation', $REQUEST['SEARCH_TRANSLATION']);
$page->addJavascriptVariable('template', rawurlencode(I18N::translateHTML($user, '<div class="translation_block" style="display: block">'
.'<input class="translation_name" type="HIDDEN" value="#{name}"/>'
.'<div class="translation_id"><translate id="TRANSLATION_TEXT_ID">Text ID:</translate> #{name}</div>'
.'<div class="translation_english"><translate id="TRANSLATION_ENGLISH_LATEST">latest original</translate> <br/><textarea readonly class="translation_english_input" style="height: #{english_height}px">#{english}</textarea></div>'
.'<div class="translation_current"><translate id="TRANSLATION_LOCAL_LATEST">current translation (please update):</translate> <br/><textarea class="translation_current_input" style="height: #{translation_height}px">#{translation}</textarea></div>'
.'<input class="translation_save" type="SUBMIT" value="<translate id="TRANSLATION_SAVE">Save translation</translate>"/>'
.'</div>')));
$page->addJavascriptVariable('no_results', '<translate id="TRANSLATE_NO_RESULTS">No results for that search</translate>');

$lid = $user->getLid();

$outdated = I18N::getOutdated($lid);

$total_amount = count(I18N::getAllNames($LANGUAGE_SOURCE[$lid]));
$percent = round(100 * ($total_amount - count($outdated)) / $total_amount, 3);

$page->startHTML();

?>

<div id="explanation">
<translate id="TRANSLATION_EXPLANATION_1">In order to help translate inspi.re, please make sure that you have your target language (the one you're a native speaker of) selected at the top-right corner of the website.</translate><br/><br/>
<translate id="TRANSLATION_EXPLANATION_2">By default the list below shows text excerpts that need to be translated or whose translation needs to be updated.</translate><br/><br/>
<translate id="TRANSLATION_EXPLANATION_3">If you want to change an existing translation on the website, you can use the search box below (leave the field blank to see all translations).</translate><br/>
<form onSubmit="javascript:SearchTranslations(); return false;"><input id="search_text" type="text" value=""/> <input id="search" type="submit" value="<translate id="TRANSLATION_SEARCH_BUTTON">search</translate>"/></form>
<?php
	echo UI::RenderTranslationPercentLeft($user, $percent);
?>
</div>

<div id="results">

<?php

$wordcount = 0;

if (count($outdated) > 50)
	echo '<b><translate id="TRANSLATION_MORE_THAN_50">Only the first 50 chunks of text to be translated are currently displayed (otherwise the page would be too slow to load).</translate></b>';

$outdated = array_slice($outdated, 0, 50);

if (empty($outdated)) {
	echo '<b><translate id="TRANSLATION_EMPTY">There is nothing new to translate right now!</translate></b>';
} else foreach ($outdated as $outdated_name => $translation) {
	try {
		$source_translation = str_replace('"', '&quot;', $translation[$LANGUAGE_SOURCE[$lid]]);
		
		if (isset($translation[$lid])) {
			$current_translation = str_replace('"', '&quot;',  $translation[$lid]);
		} else {
			$current_translation = $source_translation;
		}

		echo '<div class="translation_block" style="display: block">';
		echo '<input class="translation_name" type="HIDDEN" value="'.$outdated_name.'"/>';
		echo '<div class="translation_id"><translate id="TRANSLATION_TEXT_ID">Text ID:</translate> '.$outdated_name.'</div>';
		echo '<div class="translation_english"><translate id="TRANSLATION_ENGLISH_LATEST">latest original</translate> <br/><textarea readonly class="translation_english_input" style="height: '.(14 *(floor(strlen($source_translation) / 100) + 1)).'px">'.$source_translation.'</textarea></div>';
		echo '<div class="translation_current"><translate id="TRANSLATION_LOCAL_LATEST">current translation (please update):</translate> <br/><textarea class="translation_current_input" style="height: '.(14 *(floor(strlen($current_translation) / 100) + 3)).'px">'.$current_translation.'</textarea></div>';
		echo '<input class="translation_save" type="SUBMIT" value="<translate id="TRANSLATION_SAVE">Save translation</translate>"/>';
		echo '</div>';
	} catch (I18NException $e) {}
}

?>

</div>

<?php
$page->endHTML();
$page->render();
?>
