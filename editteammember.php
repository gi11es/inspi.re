<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where a user can be designated as a team member, revoked or have his/her title edited
*/

require_once(dirname(__FILE__).'/entities/teammembership.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/constants.php');

$user = User::getSessionUser();

$page = new Page('MEMBERS', 'COMMUNITIES', $user);
$page->startHTML();

$uid = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);

if ($uid === null) {
	header('Location: /Members/s3-l'.$user->getLid());
	exit(0);
}

try {
	$membership = TeamMembership::get($uid);
	$title = $membership->getTitle();
} catch (TeamMembershipException $e) {
	$membership = null;
	$title = '';
}

if ($membership === null) {
?>
<div class="hint hintmargin">
<div class="hint_title">
<translate id="NEW_TEAM_MEMBER_HINT">Add this member to the team</translate>
</div> <!-- hint_title -->
<translate id="NEW_TEAM_MEMBER_HINT_BODY">Define their job title and add him/her to the official inspi.re team</translate>
</div> <!-- hint -->
<?php
} else {
?>
<div class="hint hintmargin">
<div class="hint_title">
<translate id="EDIT_TEAM_MEMBER_HINT">Modify this team membership</translate>
</div> <!-- hint_title -->
<translate id="EDIT_TEAM_MEMBER_HINT_BODY">Make changes to that team member's job title</translate>
</div> <!-- hint -->
<?php
}
?>
<profile_picture size="small" uid="<?=$uid?>" id="team_member_picture"/> <user_name id="team_member_name" uid="<?=$uid?>"/>

<form id="new_team_member" method="post" action="<?=$REQUEST['EDIT_TEAM_MEMBER'].'?uid='.$uid?>">
<label for="job_title_input"><translate id="NEW_TEAM_MEMBER_TITLE">Title:</translate></label><input id="job_title_input" type="text" lefttrimmed="true" maximum="255" name="title" value="<?=$title?>" />

<?php
if ($membership === null) {
?>
<input class="left_margin" id="new_team_member_submit" type="submit" value="<translate id="NEW_TEAM_MEMBER_SUBMIT">Add to the inspi.re team</translate>">
<?php
} else {
?>
<input class="left_margin" id="new_team_member_submit" type="submit" value="<translate id="EDIT_TEAM_MEMBER_SUBMIT">Update inspi.re team title</translate>">
<?php
}
?>

</form>

<?php

$page->endHTML();
$page->render();
?>