<?php

/* 
 	Copyright (C) 2008 INSPI.RE (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Terms and conditions for using the website
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('LEGAL', 'INFORMATION', $user);
$page->setTitle('<translate id="LEGAL_PAGE_TITLE">Terms and conditions on inspi.re</translate>');

$page->startHTML();

$lid = $user->getLid();
?>

<h1 class="hint legal" id="about">
<a href="/<translate id="URL_LEGAL" escape="urlify">Terms And Conditions</translate>/s8-l<?=$lid?>#about">
<translate id="LEGAL_ABOUT_TITLE">About the terms</translate>
</a>
</h1> <!-- about -->

<translate id="LEGAL_ABOUT_CHUNK_1">
Access to and use of 'inspi.re' internationally is provided by INSPI.RE on the following terms:
</translate>

<ul>
<li>
<translate id="LEGAL_ABOUT_CHUNK_2">
By using inspi.re you agree to be bound by these terms, which shall take effect immediately on your 
first use of inspi.re. If you do not agree to be bound by all of the following terms please do not 
access, use and/or contribute to inspi.re.
</translate>
</li>
<li>
<translate id="LEGAL_ABOUT_CHUNK_3">
INSPI.RE may change these terms from time to time and so you should check these terms regularly. 
Your continued use will be deemed acceptance of the updated or amended terms. 
If you do not agree to the changes, you should cease using this website. 
If there is any conflict between these terms and specific local terms appearing elsewhere on 
inspi.re (including community rules) then the former shall prevail.
</translate>
</li>
</ul>

<h1 class="hint legal" id="rules">
<a href="/<translate id="URL_LEGAL" escape="urlify">Terms And Conditions</translate>/s8-l<?=$lid?>#rules">
<translate id="LEGAL_RULES_TITLE">Rules</translate>
</a>
</h1>

<translate id="LEGAL_RULES_INTRODUCTION">
The following rules are site-wide and apply to anywhere on inspi.re. No community-specific rules 
can override the following.
</translate>
<ul>
<li>
<translate id="LEGAL_RULES_1">
Do not post any graphic violence, pornography or sexually explicit content
</translate>
</li>
<li>
<translate id="LEGAL_RULES_2">
Do not practice or promote hate speech
</translate>
</li>
<li>
<translate id="LEGAL_RULES_3">
Do not post an entry of which you are not the rightful author
</translate>
</li>
<li>
<translate id="LEGAL_RULES_4">
Do not ask other people (on inspi.re or outside of it) to vote for one of your entries
</translate>
</li>
<li>
<translate id="LEGAL_RULES_5">
Do not ask other people (on inspi.re or outside of it) to look at one of your entries before the 
corresponding competition is closed
</translate>
</li>
<li>
<translate id="LEGAL_RULES_6">
Do not create fake or secondary accounts, a given person is only entitled to one inspi.re account
</translate>
</li>
<li>
<translate id="LEGAL_RULES_7">
Do not attempt to override or mitigate any of the present rules using the rules of your community
</translate></li>
<li>
<translate id="LEGAL_RULES_8">
Do not spam
</translate></li>
</ul>
<translate id="LEGAL_RULES_CONCLUSION">
Failure to comply with some or all of the above rules can lead to having your content deleted and 
your inspi.re membership banned permanently.
</translate>
<br/>
<br/>
<h1 class="hint legal" id="use">
<a href="/<translate id="URL_LEGAL" escape="urlify">Terms And Conditions</translate>/s8-l<?=$lid?>#use">
<translate id="LEGAL_USE_TITLE">
Use of inspi.re
</translate>
</a>
</h1>
<translate id="LEGAL_USE_BODY">
You agree to use inspi.re only for lawful purposes, and in a way that does not infringe the rights 
of, restrict or inhibit anyone else's use and enjoyment of inspi.re. Prohibited behaviour includes 
harassing or causing distress or inconvenience to any person, transmitting obscene or offensive 
content or disrupting the normal flow of dialogue within inspi.re.
</translate>
<br/>
<br/>
<h1 class="hint legal" id="copyright">
<a href="/<translate id="URL_LEGAL" escape="urlify">Terms And Conditions</translate>/s8-l<?=$lid?>#copyright">
<translate id="LEGAL_COPYRIGHT_TITLE">Intellectual property</translate>
</a>
</h1>
<translate id="LEGAL_COPYRIGHT_BODY">
All copyright, trade marks, design rights, patents and other intellectual property rights 
(registered and unregistered) in and on inspi.re and all content (including all applications) 
located on the site shall remain vested in INSPI.RE or its licensors (which includes other users). 
You may not copy, reproduce, republish, disassemble, decompile, reverse engineer, download, post, 
broadcast, transmit, make available to the public, or otherwise use inspi.re content in any way 
except for your own personal, non-commercial use. You also agree not to adapt, alter or create a 
derivative work from any inspi.re content except for your own personal, non-commercial use. 
Any other use of inspi.re content requires the prior written permission of INSPI.RE.
</translate>
<br/>
<br/>
<h1 class="hint legal" id="contributions">
<a href="/<translate id="URL_LEGAL" escape="urlify">Terms And Conditions</translate>/s8-l<?=$lid?>#contributions">
<translate id="LEGAL_CONTRIBUTIONS_TITLE">Contributions to inspi.re</translate>
</a>
</h1>
<ul>
<li>
<translate id="LEGAL_CONTRIBUTIONS_BODY_1">
By sharing any contribution (including any text, photographs, graphics, video or audio) with 
inspi.re you agree to grant to INSPI.RE, free of charge, permission to display, publish or broadcast 
the material to be displayed on INSPI.RE services in any media worldwide (including on the inspi.re 
website accessed by international users). You can revoke this permission at any time by removing your 
contribution from the inspi.re website or by deleting your user account.
</translate>
</li>
<li>
<translate id="LEGAL_CONTRIBUTIONS_BODY_2">
Copyright in your contribution will remain with you and this permission is not exclusive, so you can
 continue to use the material in any way including allowing others to use it.
 </translate>
 </li>
<li>
<translate id="LEGAL_CONTRIBUTIONS_BODY_3">
In order that INSPI.RE can use your contribution, you confirm that your contribution is your own 
original work, is not defamatory and does not infringe any European or French laws, that you have 
the right to give INSPI.RE permission to use it for the purposes specified above, and that you have 
the consent of anyone who is identifiable in your contribution or the consent of their parent / 
guardian if they are under 18.
</translate>
</li>
<li>
<translate id="LEGAL_CONTRIBUTIONS_BODY_4">
Please do not endanger yourself or others, take any unnecessary risks or break any laws when 
creating content you may share with INSPI.RE.
</translate>
</li>
<li>
<translate id="LEGAL_CONTRIBUTIONS_BODY_5">
If you do not want to grant INSPI.RE the permission set out above on these terms, please do not 
submit or share your contribution to or with INSPI.RE
</translate>
</li>
</ul>
<h1 class="hint legal" id="disclaimers">
<a href="/<translate id="URL_LEGAL" escape="urlify">Terms And Conditions</translate>/s8-l<?=$lid?>#disclaimers">
<translate id="LEGAL_DISCLAIMERS_TITLE">Disclaimers and limitation of liability</translate>
</a>
</h1>
<ul>
<li>
<translate id="LEGAL_DISCLAIMERS_BODY_1">
The majority of content posted on inspi.re is created by members of the public. 
The views expressed are theirs and unless specifically stated are not those of INSPI.RE. 
INSPI.RE is not responsible for any content posted by members of the public on inspi.re or for the 
availability or content of any third party sites that are accessible through inspi.re. 
Any links to third party websites from inspi.re do not amount to any endorsement of that site by 
INSPI.RE and any use of that site by you is at your own risk.
</translate>
</li>
<li>
<translate id="LEGAL_DISCLAIMERS_BODY_2">
inspi.re content, including the information, names, images, pictures, logos and icons regarding or 
relating to inspi.re, its products and services (or to third party products and services), is 
provided "AS IS" and on an "AS AVAILABLE" basis. To the extent permitted by law, INSPI.RE excludes 
all representations and warranties (whether express or implied by law), including the implied 
warranties of satisfactory quality, fitness for a particular purpose, non-infringement, 
compatibility, security and accuracy. INSPI.RE does not guarantee the timeliness, completeness or 
performance of the website or any of the content. While we try to ensure that all content provided 
by INSPI.RE is correct at the time of publication no responsibility is accepted by or on behalf of 
INSPI.RE for any errors, omissions or inaccurate content on the website.
</translate>
</li>
<li>
<translate id="LEGAL_DISCLAIMERS_BODY_3">
Nothing in these terms limits or excludes INSPI.RE's liability for death or personal injury caused 
by its proven negligence. Subject to the previous sentence, INSPI.RE shall not be liable for any of 
the following losses or damage (whether such damage or losses were foreseen, foreseeable, known or 
otherwise): (a) loss of data; (b) loss of revenue or anticipated profits; (c) loss of business; (d) 
loss of opportunity; (e) loss of goodwill or injury to reputation; (f) losses suffered by third 
parties; or (g) any indirect, consequential, special or exemplary damages arising from the use of 
inspi.re regardless of the form of action.
</translate>
</li>
<li>
<translate id="LEGAL_DISCLAIMERS_BODY_4">
INSPI.RE does not warrant that functions available on inspi.re will be uninterrupted or error free, 
that defects will be corrected, or that inspi.re or the server that makes it available are free of 
viruses or bugs. You acknowledge that it is your responsibility to implement sufficient procedures 
and virus checks (including anti-virus and other security checks) to satisfy your particular 
requirements for the accuracy of data input and output.
</translate>
</li>
</ul>
<h1 class="hint legal" id="general">
<a href="/<translate id="URL_LEGAL" escape="urlify">Terms And Conditions</translate>/s8-l<?=$lid?>#general">
<translate id="LEGAL_GENERAL_TITLE">General</translate>
</a>
</h1>
<ul>
<li><translate id="LEGAL_GENERAL_BODY_1">
If any of these terms are determined to be illegal, 
invalid or otherwise unenforceable by reason of the laws of any state or country in which these 
terms are intended to be effective, then to the extent and within the jurisdiction in which that 
term is illegal, invalid or unenforceable, it shall be severed and deleted from these terms and 
the remaining terms shall survive and continue to be binding and enforceable.
</translate>
</li>
<li>
<translate id="LEGAL_GENERAL_BODY_2">
The failure or delay of inspi.re to exercise or enforce any right in these terms does not waive 
inspi.re's right to enforce that right.
</translate>
</li>
<li>
<translate id="LEGAL_GENERAL_BODY_3">
These terms shall be governed by and interpreted in accordance with the laws of France which shall 
have exclusive jurisdiction over any disputes.
</translate>
</li>
</ul>
<h1 class="hint legal" id="contact">
<a href="/<translate id="URL_LEGAL" escape="urlify">Terms And Conditions</translate>/s8-l<?=$lid?>#contact">
<translate id="LEGAL_CONTACT_TITLE">Contacting inspi.re about these terms and conditions</translate>
</a>
</h1>
INSPI.RE<br/>
12 Les Hortensias<br/>
13430 EYGUIÈRES<br/>
FRANCE<br/>
<br/>
+35226785812
<?php
$page->endHTML();
$page->render();
?>
