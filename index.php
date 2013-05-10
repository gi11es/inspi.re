<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Main page, shall contain a user's current and past entries when logged in
*/

require_once(dirname(__FILE__).'/entities/communitylist.php');
require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/competitionlist.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/i18n.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

if (isset($_REQUEST['e'])) {
	header('Location: '.$PAGE['ENTRY'].'?lid='.$user->getLid().'&eid='.$_REQUEST['e']);
	exit(0);
}

if ($user->getStatus() != $USER_STATUS['UNREGISTERED']) {
	header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());
	exit(0);
}

$page = new Page('HOME', 'HOME', $user);
$page->setTitle('<translate id="INDEX_PAGE_TITLE">Welcome to inspi.re</translate>');
$page->addJavascriptVariable('comet_url', $COMET_URL);
$page->addJavascriptVariable('comet_channel', $COMET_CHANNEL['GENERAL_ACTIVITY']);
$page->addJavascriptVariable('lid', $user->getLid());
$page->addJavascriptVariable('prize_url', '/Prize/s4-l'.$user->getLid());

$page->startHTML();

function RenderTestimonial($id) {
	echo '<div>';
	switch ($id) {
		case 1:
			echo '<profile_picture uid="3798" size="medium" class="testimonial_picture clearboth"/>',
				 '<div class="testimonial">',
				 '<translate id="INDEX_TESTIMONIAL_1">',
				 '<user_name uid="3798" link="true"/>: <i>"I have been aroused, animated, and imbued with the spirit to do something since discovering inspi.re. That something is picking up my camera and taking photographs come wind, rain or shine. To compete with an eclectic gathering of wonderful, friendly, like minded folk from around the world. They all share their visions and the skills they have learnt to create them."</i>',
				 '</translate>',
				 '</div> <!-- testimonial -->';
			break;
		case 2:
			echo '<profile_picture uid="1129" size="medium" class="testimonial_picture clearboth"/>',
				 '<div class="testimonial">',
				 '<translate id="INDEX_TESTIMONIAL_2">',
				 '<user_name uid="1129" link="true"/>: <i>"Since I started taking photography seriously, a little less than 1 year ago, I found on what has now become inspi.re a great community of people willing to share experiences and knowledge. It has helped me on improving my skills far more that I expected."</i>',
				 '</translate>',
				 '</div> <!-- testimonial -->';
			break;
		case 3:
			echo '<profile_picture uid="405392" size="medium" class="testimonial_picture clearboth"/>',
				 '<div class="testimonial">',
				 '<translate id="INDEX_TESTIMONIAL_3">',
				 '<user_name uid="405392" link="true"/>: <i>"Inspi.re is to photography what Daft Punk is to electronic music, a UFO! The French touch strikes again, this time in the world of online photo competitions. I truly believe that this website is helping me improve my photography skills in a fun environment, for free."</i>',
				 '</translate>',
				 '</div> <!-- testimonial -->';
			break;
		case 4:
			echo '<profile_picture uid="547045" size="medium" class="testimonial_picture clearboth"/>',
				 '<div class="testimonial">',
				 '<translate id="INDEX_TESTIMONIAL_4">',
				 '<user_name uid="547045" link="true"/>: <i>"This is the first photography website I\'ve found that actually provides honest feedback and fair contests. So refreshing to get that input.........very helpful in improving my skills."</i>',
				 '</translate>',
				 '</div> <!-- testimonial -->';
			break;
		case 5:
			echo '<profile_picture uid="387891" size="medium" class="testimonial_picture clearboth"/>',
				 '<div class="testimonial">',
				 '<translate id="INDEX_TESTIMONIAL_5">',
				 '<user_name uid="387891" link="true"/>: <i>"Passionate about your art? Still a beginner? On inspi.re everyone can participate without being judged for who they are. It\'s more than a website where you can showcase your art, it\'s a way of sharing your passion and learning new techniques through the varied competition themes. Inspi.re is a lot more than a community of passionate people, it\'s a revolutionary sharing place you mustn\'t miss!"</i>',
				 '</translate>',
				 '</div> <!-- testimonial -->';
			break;
		case 6:
			echo '<profile_picture uid="12758" size="medium" class="testimonial_picture clearboth"/>',
				 '<div class="testimonial">',
				 '<translate id="INDEX_TESTIMONIAL_6">',
				 '<user_name uid="12758" link="true"/>: <i>"I love exploring other photographers work & styles and I\'m constantly amazed at the skill and imagination that is show cased here on Inspi.re. You soon learn it’s not about winning at Inspi.re, it’s about listening and learning and along the way you improve and help others too.  Friends become family and you become part of a community that shares a phenomenal passion called Photography!  Aspire to Inspi.re before you Expire!"</i>',
				 '</translate>',
				 '</div> <!-- testimonial -->';
			break;
		case 7:
			echo '<profile_picture uid="406488" size="medium" class="testimonial_picture clearboth"/>',
				 '<div class="testimonial">',
				 '<translate id="INDEX_TESTIMONIAL_7">',
				 '<user_name uid="406488" link="true"/>: <i>"Fellow members give you objective feedback and allow you to evaluate your own level. Viewing, voting, critiquing and commenting other members\' entries will make you leap forward by showing you how far creativity can be pushed. You\'ll start considering new angles, new ways of setting up your scenes, etc.  That\'s the real benefit of inspi.re, widening our vision of the world and learning the skills we need to transfer that new vision into what we create."</i>',
				 '</translate>',
				 '</div> <!-- testimonial -->';
			break;
	}
	echo '</div>';
}

?>
<div id="presentation">
<h1>
<translate id="INDEX_TITLE">
Photo competitions for everyone.
</translate>
</h1>

<p class="advice">
<translate id="INDEX_ADVICE_3">
Inspi.re provides a fair environment where photographers of all levels compete against each other. It's an entertaining learning experience that will get your creative juices flowing. If you think you're good, you can put your skills to the test. If you're a beginner, you can learn from the best.<br/><br/>Where to start? Look at <a href="<?=$PAGE['JOIN_COMMUNITIES'].'?lid='.$user->getLid()?>">the list of communities</a> and find one you'd like to join.
</translate>
</p>

<?php

echo '</div> <!-- presentation -->',

	 '<div id="winning_entries">',
	 '<div class="hint" id="winning_entries_hint">',
	 '<div class="hint_title">',
	 '<translate id="RECENT_WINNERS_TITLE">',
	 'Most recent winning entries',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '<translate id="RECENT_WINNERS_BODY">',
	 'These wonderful artworks just won the competitions they were entered in. Congratulations to their authors!',
	 '</translate>',
	 '</div> <!-- hint -->';

$competitionlist = CompetitionList::getByStatus($COMPETITION_STATUS['CLOSED']);
$endtimelist = array();
$competition = array();

$competitioncache = Competition::getArray(array_keys($competitionlist));

foreach ($competitioncache as $cid => $comp) {
	$competition[$cid] = $comp;
	$endtimelist[$cid] = $comp->getEndTime();
}

arsort($endtimelist);

$entrylist = array();
$entriescount = 0;

foreach ($endtimelist as $cid => $end_time) {
	$localentrylist = EntryList::getByCidAndStatus($cid, $ENTRY_STATUS['POSTED']);
	if (!in_array($cid, $FRONT_PAGE_BLACKLIST) && count($localentrylist) >= 15) {
		$firsts = array_values(EntryList::getByCidAndRank($cid, 1));
		$entrylist = array_merge($entrylist, $firsts);
		$entriescount += count($firsts);
	}
	
	if ($entriescount >= 5) break;
}

echo '<div id="winning_thumbnails">';
$i = 0;

foreach ($entrylist as $eid) {
	if ($i >= 5) break;
	try {
		$entry = Entry::get($eid);
		$pid = $entry->getPid();
		
		if ($pid !== null) try {
			$entry_user = User::get($entry->getUid());
			$theme = Theme::get($competition[$entry->getCid()]->getTid());
			$competitionname = $theme->getTitle();
			$title = '<translate id="INDEX_WINNING_ENTRY_TITLE"><string value="'.String::htmlentities($entry_user->getUniqueName()).'"/> won <string value="'.String::htmlentities('"'.$competitionname.'"').'"/> <duration value="'.(gmmktime() - $competition[$entry->getCid()]->getEndTime()).'"/> ago</translate>';
			$title = INML::processHTML($user, I18N::translateHTML($user, $title));
			echo '<picture title="',$title,'" href="',$PAGE['ENTRY'],'?lid=',$user->getLid(),'#eid=',$eid,'" class="winning_thumbnail" pid="',$pid,'" size="medium"/>';
			$i++;
		} catch (UserException $f) {}
	} catch (EntryException $e) {}
	
}

$opencompetitioncount = count(CompetitionList::getByStatus($COMPETITION_STATUS['OPEN']));

echo '</div> <!-- winning_thumbnails -->',

	 '</div> <!-- winning_entries -->',
	 
	 '<div id="prize" style="background-image: url(\'',$GRAPHICS_PATH,'prizebadge.png?1\')">',
	 '100 €<br/>',
	 '<span id="prize_description">',
	 '<translate id="INDEX_PRIZE_DESCRIPTION">',
	 'up for grabs<br/>every month',
	 '</translate>',
	 '</span>',
	 '</div> <!-- prize -->',
	 
	 '<div class="hint hintmargin clearboth">',
	 '<div class="hint_title">',
	 '<translate id="MEMBERS_REAL_TIME_UPDATES">',
	 'What\'s happening right now',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',
	 
	 '<div id="real_time_updates" class="clearboth"></div>',

	 '<div class="warning clearboth bigmargin">',
	 '<div class="warning_title">',
	 '<translate id="INDEX_ADVICE_4">',
	 'There are <integer value="',$opencompetitioncount,'"/> competitions you can enter right now. Are you up for the challenge?',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<div id="testimonial_container">';
	 
	 $firstid = rand(1, 2);
	 
	 do {
	 	$secondid = rand(1, 2);
	 } while ($secondid == $firstid);
	 
	 RenderTestimonial($firstid);
	 RenderTestimonial($secondid);
	 
	 echo '<div id="testimonials_send" class="clearboth">',
	 	 '<translate id="INDEX_TESTIMONIALS_BODY">',
	 'If you\'re already a member, you can send your testimonials to <a href="mailto:testimonials@inspi.re">testimonials@inspi.re</a>',
	 '</translate>',
	 	 '</div>';

echo '</div> <!-- testimonial_container -->';

?>

<div id="differences_container">

<p class="advice">
<translate id="INDEX_WHY_DIFFERENT_TITLE">
What makes us different from other online photo contests:
</translate>
</p>

<ul class="differences">
<translate id="INDEX_WHY_DIFFERENT">
<li><b>Competitions are our specialty.</b> We are not yet another online portfolio that runs contests occasionally. Competitions are our core focus and there are dozens of them opening every day.</li>
<li><b>The process is democratic.</b> The rankings are always determined by a democratic vote.</li>
<li><b>Authors are anonymous during the voting phase.</b> People judge the art, not the person who made it.</li>
<li><b>Feedback is on a tit-for-tat basis.</b> Our points system guarantees that members give as much feedback as they receive. This recipe creates a vibrant environment.</li> 
<li><b>You can find the challenge that suits you best.</b> Members are part of many separate communities that each focus on different crafts, techniques and levels.</li>
<li><b>We are tough on cheating.</b> We shut down fake accounts, detect and delete votes that come from abusive sympathy voting and "vote for me" email chains. We ban people who post art they stole from someone else. As a result, all the people you compete against are fair players.</li>
<li><b>You can earn money with your art.</b> Artworks you upload can be sold as canvas prints. We handle the manufacturing and shipping, everything is automated. Add a markup to the retail price of the prints and start monetizing your art.</li>
</translate>
</ul>

</div> <!-- differences_container -->

<?php

$page->endHTML();
$page->render();
?>
