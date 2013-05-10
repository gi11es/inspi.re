<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	This is the error page reached when a user doesn't have javascript enabled on their browser
*/

require_once(dirname(__FILE__).'/entities/i18n.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

ob_start();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="http://inspi.re/css/main.css" />
<script type="text/javascript">
<!--
window.location = "<?=$PAGE['HOME'].'?lid='.$user->getLid()?>"
//-->
</script>
</head>
<body bgcolor="#000">
<div id="background">
<div id="container">
<div id="header"><img class="unselectable" id="logo" src="http://inspi.re/graphics/logo-small.gif" />
</div> <!-- header-->
<div class="content_container">
<div id="content_with_ad">
<div class="warning hintmargin">
<div class="warning_title">
<translate id="NO_JAVASCRIPT_TITLE">You web browser lacks javascript support!</translate>
</div> <!-- warning_title -->
<translate id="NO_JAVASCRIPT_BODY">
This website involves a number of modern web technologies that require you to enable javascript for them to work.<br/>
<br/>
Currently your browser doesn't have javascript support. You need to reactivate it or use a web browser 
that supports javascript if the one you're currently using doesn't. 
<a target=_blank href="http://www.activatejavascript.org/">This website explains how to activate javascript in all major browsers.</a><br/>
<br/>
We apologize to the blind community for not making this website accessible to screen readers that 
don't support javascript.
</translate>
</div> <!-- warning -->
</div> <!-- content_with_ad -->
</div> <!-- content_container -->
<div id="footer">
</div> <!-- footer -->
</div> <!-- container -->
</div> <!-- background -->
</body>
</html>
<?php

$total_html = ob_get_contents();
ob_end_clean();

echo I18N::translateHTML($user, $total_html);

?>