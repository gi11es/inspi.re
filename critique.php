<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where members can live-critique artworks
*/

require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/picture.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('CRITIQUE', 'NEXT', $user);
$page->setTitle('<translate id="CRITIQUE_PAGE_TITLE">Critique artworks on inspi.re</translate>');

$page->addStyle('ENTRY');
$page->addStyle('VOTE');

$page->addJavascriptVariable('language', strtolower($LANGUAGE_CODE[$user->getLid()]));

$page->startHTML();

?>
<div class="hint hintmargin">
    <div class="hint_title">
    <translate id="CRITIQUE_TITLE">
    Critique this artwork and help the author improve his/her craft
    </translate>
    </div> <!-- hint_title -->
<translate id="CRITIQUE_TITLE_BODY">
As soon as this artwork receives at least 10 comments or comments from 5 different members, it will be archived and a new one will be shown on this page.
</translate>
</div> <!-- hint -->
<?php

if (isset($_REQUEST['eid'])) $eid = $_REQUEST['eid'];

try {
    $entry = Entry::get($eid);
} catch (EntryException $e) {
    $page->endHTML();
    $page->render();
    exit(0);
}

$picture = Picture::get($entry->getPid());
$picture_src = $picture->getRealThumbnail($PICTURE_SIZE['HUGE']);

?>

<div id="entry_container">
<img id="entry" src="<?=$picture_src?>"/>
</div>

<?php

echo UI::RenderCommentThread($user, $entry, false, null, false);

$page->endHTML();
$page->render();
?>
