<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where a user can be designated as a team member, revoked or have his/her title edited
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$page = new Page('MEMBERS', 'COMMUNITIES', $user);
$page->startHTML();

$uid = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);

$levels = UserLevelList::getByUid($user->getUid());

if ($uid === null || !in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	header('Location: /Members/s3-l'.$user->getLid());
	exit(0);
}

?>
<div class="hint hintmargin">
<div class="hint_title">
<translate id="GIVE_POINTS_TITLE">Give this member points</translate>
</div> <!-- hint_title -->
</div> <!-- hint -->

<profile_picture size="small" uid="<?=$uid?>" id="team_member_picture"/> <user_name id="team_member_name" uid="<?=$uid?>"/>

<form id="new_team_member" method="post" action="<?=$REQUEST['GIVE_POINTS'].'?uid='.$uid?>">
<label for="points"><translate id="GIVE_POINTS_FIELD">extra points:</translate></label><input id="points" type="text" name="points" value="" />

<input class="left_margin" id="new_team_member_submit" type="submit" value="<translate id="GIVE_POINTS_SUBMIT">give points</translate>">

</form>

<?php

$page->endHTML();
$page->render();
?>