<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where a user can reframe the thumbnails for his/her picture
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/picture.php');
require_once(dirname(__FILE__).'/entities/picturefile.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/system.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

require_once(dirname(__FILE__).'/utilities/s3.php');

$user = User::getSessionUser();

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;
$cid = isset($_REQUEST['cid'])?$_REQUEST['cid']:null;

if ($xid === null && $cid === null) {
	$pid = $user->getPid();
	$redirection_target = $PAGE['SETTINGS'].'?lid='.$user->getLid();
} elseif ($xid !== null) {
	$community = Community::get($xid);
	$pid = $community->getPid();
	$redirection_target = $PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$xid;
} elseif ($cid !== null) {
	$status = $user->getStatus() == $USER_STATUS['UNREGISTERED'] ? $ENTRY_STATUS['ANONYMOUS'] : $ENTRY_STATUS['POSTED'];
	$entrylist = EntryList::getByUidAndCidAndStatus($user->getUid(), $cid, $status);
	
	if (!empty($entrylist)) {
		$eid = array_shift($entrylist);
		$entry = Entry::get($eid);
		$pid = $entry->getPid();
	} else {
		header('Location: '.$PAGE['ENTER'].'?lid='.$user->getLid().'&cid='.$cid);
		exit(0);
	}
	$redirection_target = $PAGE['ENTER'].'?lid='.$user->getLid().'&amp;cid='.$cid;
}

$page = new Page('EDIT_CROPPING', 'HOME', $user);
$page->addJavascriptVariable('request_update_cropping', $REQUEST['UPDATE_CROPPING'].'?pid='.$pid);
$page->startHTML();

$picture = Picture::get($pid);

$original = PictureFile::get($picture->getFid($PICTURE_SIZE['ORIGINAL']));
$huge = PictureFile::get($picture->getFid($PICTURE_SIZE['HUGE']));

$total_width = $huge->getWidth();
$total_height = $huge->getHeight();
$ratio = floatval($huge->getWidth()) / floatval($original->getWidth());

$top = intval(floatval($picture->getOffsetY()) * $ratio);
$left = intval(floatval($picture->getOffsetX()) * $ratio);
$width = intval(floatval($picture->getDimension()) * $ratio);
$height = intval(floatval($picture->getDimension()) * $ratio);

if ($width > $huge->getWidth()) $width = $huge->getWidth();
if ($height > $huge->getHeight()) $height = $huge->getHeight();

if ($top + $height > $huge->getHeight()) {
	$top = $huge->getHeight() - $height;
}

if ($left + $width > $huge->getWidth()) {
	$left = $huge->getWidth() - $width;
}

$page->addJavascriptVariable('total_width', $total_width);
$page->addJavascriptVariable('total_height', $total_height);

?>
<div class="hint hintmargin">
<div class="hint_title">
<translate id="EDIT_PROFILE_PICTURE_HINT_TITLE">
Edit the cropping of your picture
</translate>
</div> <!-- hint_title -->
<translate id="EDIT_PROFILE_PICTURE_HINT_BODY">
Simply move and resize the square to choose the area of the picture that you wish to keep
</translate>
</div> <!-- hint -->

<div id="picture_container">

<div id="picture_holder" style="width: <?=$total_width?>px; height: <?=$total_height?>px;">
<picture pid="<?=$pid?>" link="false" size="huge" id="picture_huge"/>
<div id="picture_shader" style="width: <?=$total_width?>px; height: <?=$total_height?>px;"></div>
<div id="picture_huge_cropped" style="top: <?=$top?>px; left: <?=$left?>px; width: <?=$width?>px; height: <?=$height?>px; background-position: -<?=$left?>px -<?=$top?>px;">
<div id="marquee_top" class="marquee_horizontal"></div>
<div id="marquee_bottom" class="marquee_horizontal" style="top: <?=($height - 2)?>px;"></div>
<div id="marquee_left" class="marquee_vertical"></div>
<div id="marquee_right" class="marquee_vertical" style="left: <?=($width - 1)?>px;"></div>
<div id="corner_top_left" class="corner"></div>
<div id="corner_top_right" class="corner" style="left: <?=($width - 5)?>px;"></div>
<div id="corner_bottom_left" class="corner" style="top: <?=($height - 4)?>px;"></div>
<div id="corner_bottom_right" class="corner" style="top: <?=($height - 4)?>px; left: <?=($width - 5)?>px;"></div>
</div> <!-- picture_huge_cropped -->
</div> <!-- picture_holder -->

<form id="cropping" method="post" action="<?=$redirection_target?>">
<div>
<input id="save_cropping" type="button" value="<translate id="EDIT_PROFILE_PICTURE_SAVE">Save new cropping settings</translate>" onclick="saveCrop()" style="font-size: 8pt;" />
</div>
</form> <!-- cropping -->

</div> <!-- picture_container -->
<?php
$page->endHTML();
$page->render();
?>
