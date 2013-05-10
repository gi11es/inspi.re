<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page that gives hints and tutorials on how to use the website
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('HELP', 'INFORMATION', $user);
$page->addJavascript('BUG_REPORT');
$page->addStyle('BUG_REPORT');

$page->startHTML();

$page->addJavascriptVariable('request_new_bug_report', $REQUEST['NEW_BUG_REPORT']);

?>
<div class="hint hintmargin">
<div class="hint_title">
<translate id="BUG_REPORT_TITLE">
Report a bug
</translate>
</div> <!-- hint_title -->
<translate id="BUG_REPORT_HINT">
Are you experiencing any unexpected behaviour with the website? Do you see any error messages? You can contact us here so that we can fix it.
</translate>
</div> <!-- hint -->

<p id="bug_report_header">
<translate id="BUG_REPORT_CHUNK1">
This is <b>not</b> a general enquiry page. Seek help on the discussion boards if you have general questions about the website. This contact page is for bug reports only. If you contact us for anything else using this page, you will not get an answer. Read the following guidelines before sending us anything.
</translate>
</p>
<p>
<h2>
<translate id="BUG_REPORT_TITLE1">
What makes a bad bug report?
</translate>
</h2>
<translate id="BUG_REPORT_CHUNK2">
<b>Example: "The website is broken"</b><br/>
If you do not describe your issue in as much detail as possible, it will be impossible for us to know what your problem is. Word your bug report in such a way that another user would be able to follow your instructions and reproduce the bug. Think about the steps you took that led you to the bug.<br/>
</translate>
<br/>
<translate id="BUG_REPORT_CHUNK3">
<b>Example: "There is a fatal error"</b><br/>
The errors are being displayed on the website for a reason. They contain crucial information that lets us understand what happened. Any error displayed on the website always come along with some details. You might not understand what they mean, but it's important for you to copy and paste them into your report.
</translate>
<h2>
<translate id="BUG_REPORT_TITLE2">
What makes a good bug report?
</translate>
</h2>
<translate id="BUG_REPORT_CHUNK4">
<b>Example: "30 minutes ago I was voting on the Spoons competition and the right arrow wouldn't lead me to the next entry, I got stuck and had to leave the page"</b><br/>
This short report contains details about what happened exactly and what the time and location contexts were. Fill us in on the whole story, not just which button wouldn't do what you expected.<br/>
</translate>
<br/>
<translate id="BUG_REPORT_CHUNK5">
Be detailed, give us as much information as you can, even if you think it might be unrelated. Specify when and where it happened, along with the steps you took. Separate facts from speculation. Please be nice to us and stay calm when you're writing, cursing and adding many exclamation points to your report won't get your issue fixed any faster.
</translate>
</p>

<div id="report_success" class="hint" style="display: none">
<div class="hint_title">
<translate id="BUG_REPORT_SUCCESS">
Your bug report was submitted successfully
</translate>
</div> <!-- hint_title -->
</div> <!-- hint -->

<?php
echo '<form id="new_post" method="post" action="'.$REQUEST['NEW_BUG_REPORT'].'">';
echo '<div class="listing_item">';
echo '<div class="listing_thumbnail">';
echo '<profile_picture uid="'.$user->getUid().'" size="small"/>';
echo '</div> <!-- listing_thumbnail -->';
echo '<div class="listing_header listing_header_thumbnail_margin">';

echo '<translate id="BUG_REPORT_HEADER">';
echo '<user_name uid="'.$user->getUid().'"/> wrote the following bug report';
echo '</translate>';

echo '</div> <!-- listing_header -->';
echo '<textarea minimum="40" maximum="10000" id="text" name="text" minimumrows="8" autoexpand="true" class="new_post_text">';
echo '</textarea> <!-- new_post_text -->';
echo '<div id="send_post" class="post_action">';
echo '<a href="javascript:submitPost()"><translate id="BUG_REPORT_SUBMIT">Submit this bug report</translate></a>';
echo '</div> <!-- post_action -->';
echo '</div> <!-- post -->';
echo '<div id="length_errors">';
echo '<span class="length_error" id="post_too_short"><translate id="BUG_REPORT_TOO_SHORT">Bug report is too short</translate></span>';
echo '<span class="length_error" id="post_too_long" style="display:none"><translate id="BUG_REPORT_TOO_LONG">Bug report is too long</translate></span>';
echo '</div>';
echo '</form>';

$page->endHTML();
$page->render();
?>
