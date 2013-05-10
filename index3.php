<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Main page, shall contain a user's current and past entries when logged in
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
if ($user->getStatus() != $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());
	exit(0);
}

$page = new Page('HOME', 'HOME', $user);

$page->startHTML();
?>
<div id="presentation">
<h1>
<translate id="INDEX_ALT_TITLE">
A place to measure your art against fellow artists
</translate>
</h1>

<div id="index_images">
<img id="index1" src="<?=$GRAPHICS_PATH?>index1.jpg"/>
<img id="index2" src="<?=$GRAPHICS_PATH?>index2.jpg"/>
<img id="index3" src="<?=$GRAPHICS_PATH?>index3.jpg"/>
</div>

<p class="advice">
<translate id="INDEX_ALT_ADVICE_1">
Learn through competing and share knowledge. Find like-minded members and help each other grow as artists.
</translate> 
<translate id="INDEX_ALT_ADVICE_2">
You can explore inspi.re without restrictions right now, all the features are accessible*.
</translate>
</p>

<ul class="hint" id="steps">
<li>
<translate id="INDEX_ALT_STEP_1">
<span class="step">Step 1</span> <a href="<?=$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid()?>">Join a community</a>
</translate>
</li>
<li>
<translate id="INDEX_ALT_STEP_2">
<span class="step">Step 2</span> Explore the competitions, critique and vote on entries
</translate>
</li>
<li>
<translate id="INDEX_ALT_STEP_3">
<span class="step">Step 3</span> Enter your artwork into a competition
</translate>
</li>
</ul>

<translate id="INDEX_FOOTNOTE">
*Everything you do on the website will be made effective and saved once you register an account. You need to keep your browser open in the meantime.
</translate>
</div>

<?php

$page->endHTML();
$page->render();
?>