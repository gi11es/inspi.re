<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where users pick the competitions they wish to enter
*/

require_once(dirname(__FILE__).'/entities/community.php');
require_once(dirname(__FILE__).'/entities/communitymoderator.php');
require_once(dirname(__FILE__).'/entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/competitionhidelist.php');
require_once(dirname(__FILE__).'/entities/competitionlist.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/entrylist.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();

$page = new Page('COMPETE', 'COMPETITIONS', $user);

$page->setTitle('<translate id="COMPETE_PAGE_TITLE">Open competitions on inspi.re</translate>');
$page->addJavascriptVariable('request_hide_competition', $REQUEST['HIDE_COMPETITION']);

$page->startHTML();

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);
$hideads = ($ispremium && $user->getHideAds());

$xidfilter = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;
$globalfilter = isset($_REQUEST['filter'])?intval($_REQUEST['filter']):null;

$moderatedcommunitylist = CommunityModeratorList::getByUid($user->getUid());

function RenderCompetition($competition, $first, $highlight) {
	global $user;
	global $moderatedcommunitylist;
	global $PAGE;
	global $ENTRY_STATUS;
	global $USER_STATUS;
	global $hideads;
	global $globalfilter;
	global $COMPETE_FILTER;
	
	$theme = Theme::get($competition->getTid());
	
	try {
		$community = Community::get($competition->getXid());
	} catch (CommunityException $e) {
		return '';
	}
	
	$entries = EntryList::getByCidAndStatus($competition->getCid(), $ENTRY_STATUS['POSTED']);
	
	if ($user->getStatus() == $USER_STATUS['BANNED'])
		$entries += EntryList::getByCidAndStatus($competition->getCid(), $ENTRY_STATUS['BANNED']);
	
	if ($user->getStatus() == $USER_STATUS['UNREGISTERED']) {
		foreach (EntryList::getByUidAndCidAndStatus($user->getUid(), $competition->getCid(), $ENTRY_STATUS['ANONYMOUS']) as $eid) {
			$entries[$user->getUid()] = $eid;
		}
	}
	
	$entries_count = count($entries);
	
	echo '<div id="competition_',$competition->getCid(),'" class="',($first && !$hideads?'marginless_item':'listing_item'),($highlight /*|| $community->getXid() == 267*/?' highlight_item':''),'">',
		 '<picture href="',$PAGE['COMMUNITY'],'?lid=',$user->getLid(),'&xid=',$competition->getXid(),'" category="community" class="listing_thumbnail" size="small" ',($community->getPid() === null?'':'pid="'.$community->getPid().'"'),' />',
		 '<div class="listing_header">';
		 
	if ($community->getXid() == 267) {
		echo '<a href="',$PAGE['ENTER'],'?lid=',$user->getLid(),'&amp;cid=',$competition->getCid(),'"><translate id="PRIZE_COMMUNITY_THEME_TITLE'.$competition->getTid().'">'.$theme->getTitle().'</translate></a> ';
	} else {
		echo '<theme_title href="',$PAGE['ENTER'],'?lid=',$user->getLid(),'&amp;cid=',$competition->getCid(),'" tid="',$competition->getTid(),'"/> ';
	}

	$ismoderator = in_array($competition->getXid(), $moderatedcommunitylist);

	if ($user->getUid() == $community->getUid() || $ismoderator) {
		echo '(<a href="',$PAGE['GRID'],'?lid=',$user->getLid(),'&amp;cid=',$competition->getCid(),'">',
			 '<translate id="COMPETE_SEE_UPCOMING">',
			 'see upcoming entries',
			 '</translate>',
			 '</a>)';
	}
	
	if ($globalfilter != $COMPETE_FILTER['HIDDEN'])
	echo '<div class="hide_competition">',
		 '<a href="javascript:hideCompetition('.$competition->getCid().');">',
		 '<translate id="COMPETE_HIDE_COMPETITION">Hide this competition</translate>',
		 '</a></div>'; 
	else
	echo '<div class="hide_competition">',
		 '<a href="javascript:unhideCompetition('.$competition->getCid().');">',
		 '<translate id="COMPETE_UNHIDE_COMPETITION">Unhide this competition</translate>',
		 '</a></div>'; 

	echo '</div> <!-- listing_header -->',
		 '<div class="listing_subheader">',
		 '<translate id="COMPETE_LIST_SUBHEADER">',
		 'Suggested by <user_name uid="',$theme->getUid(),'"/> for <community_name link="true" xid="',$competition->getXid(),'"/>. ',
		 '<span class="time_left"><duration value="',($competition->getVoteTime() - gmmktime()),'"/> left to enter this competition</span>.',
		 '</translate> ';
		 
	if ($entries_count == 0) {
		echo '<translate id="COMPETE_LIST_SUBHEADER_2_NONE">',
			 'No entry has been submitted yet.',
			 '</translate>';
	} elseif ($entries_count == 1) {
		echo '<translate id="COMPETE_LIST_SUBHEADER_2_SINGULAR">',
			 '1 entry has been submitted.',
			 '</translate>';
	} else {
		echo '<translate id="COMPETE_LIST_SUBHEADER_2_PLURAL">',
			 '<integer value="',$entries_count,'"/> entries have been submitted.',
			 '</translate>';
	}
	
	echo '</div> <!-- listing_subheader -->',
		 '<div class="listing_content">';
		 
	if ($community->getXid() == 267) {
		 echo '<translate id="PRIZE_COMMUNITY_THEME_DESCRIPTION'.$competition->getTid().'">'.$theme->getDescription().'</translate>';
	} else {
		 echo String::fromaform($theme->getDescription());
	}
	
	echo '</div> <!-- listing_content -->';
	
	if (!empty($entries)) {
		echo '<div class="listing_content">';
		
		$uids = array_rand($entries, min(13, $entries_count));
		if (!is_array($uids)) $uids = array($uids);
		
		foreach ($uids as $uid) {
			try {
			$entry = Entry::get($entries[$uid]);
			if ($user->getUid() == $community->getUid() || $ismoderator)
				echo '<picture href="'.$PAGE['ENTRY'].'?lid='.$user->getLid().'#eid='.$entry->getEid().'" class="entry_preview" pid="'.$entry->getPid().'" size="small"/>';
			else echo '<picture class="entry_preview" pid="'.$entry->getPid().'" size="small"/>';
			} catch (EntryException $e) {}
		}
		echo '</div> <!-- listing_content -->';
	}
	
	echo '</div> <!-- listing_item -->';
}

?>

<div class="hint hintmargin">
<div class="hint_title">
<translate id="COMPETE_HINT_TITLE">
Competitions you can enter
</translate>
</div> <!-- hint_title -->
<translate id="COMPETE_HINT_BODY">
Below is the list of open competitions for the communities you're a member of
</translate>
</div> <!-- hint -->

<?php

$communitylist = $user->getCommunityList();

if (!empty($communitylist)) {
	echo '<div id="community_filter">',
		 '<div id="community_filter_title">',
		 '<translate id="COMPETE_FILTER">',
		 'Filter',
		 '</translate>',
		 '</div> <!-- community_filter_title -->',
		 '<div id="community_filters"',($xidfilter == null && $globalfilter == null?' style="display:none"':''),'>',

		 '<a ',($globalfilter === null?'class="filtered" ':''),'href="',$PAGE['COMPETE'],'?lid=',$user->getLid(),($xidfilter !== null?'&xid='.$xidfilter:''),'">',
		 '<translate id="COMPETE_FILTER_ALL">',
		 'Open competition(s)',
		 '</translate>',
		 '</a><br/>',

		 '<a ',($globalfilter === $COMPETE_FILTER['ENTERED']?'class="filtered" ':''),'href="',$PAGE['COMPETE'],'?lid=',$user->getLid(),'&filter=',$COMPETE_FILTER['ENTERED'],($xidfilter !== null?'&xid='.$xidfilter:''),'">',
		 '<translate id="COMPETE_FILTER_ENTERED">',
		 'Open competition(s) you\'ve already entered',
		 '</translate>',
		 '</a><br/>',
	
		 '<a ',($globalfilter === $COMPETE_FILTER['VIRGIN']?'class="filtered" ':''),'href="',$PAGE['COMPETE'],'?lid=',$user->getLid(),'&filter=',$COMPETE_FILTER['VIRGIN'],($xidfilter !== null?'&xid='.$xidfilter:''),'">',
		 '<translate id="COMPETE_FILTER_VIRGIN">',
		 'Open competition(s) you have yet to enter',
		 '</translate>',
		 '</a><br/>',
		 
		 '<a ',($globalfilter === $COMPETE_FILTER['HIDDEN']?'class="filtered" ':''),'href="',$PAGE['COMPETE'],'?lid=',$user->getLid(),'&filter=',$COMPETE_FILTER['HIDDEN'],($xidfilter !== null?'&xid='.$xidfilter:''),'">',
		 '<translate id="COMPETE_FILTER_HIDDEN">',
		 'Open competition(s) you have hidden',
		 '</translate>',
		 '</a><br/>',
	
		 '<br/>',
	
		 '<a ',($xidfilter === null?'class="filtered" ':''),'href="',$PAGE['COMPETE'],'?lid=',$user->getLid(),($globalfilter !== null?'&filter='.$globalfilter:''),'">',
		 '<translate id="COMPETE_FILTER_ALL_COMMUNITIES">',
		 'Open competitions for any of your communities',
		 '</translate>',
		 '</a><br/>';
	
	if ($user->getCommunityFilterIcons()) echo '<div id="icon_filters">';
	
	foreach ($communitylist as $xid) {
		if ($user->getCommunityFilterIcons()) {
			$community = Community::get($xid);
			echo '<picture title="'.String::htmlentities($community->getName()).'" href="'.$PAGE['COMPETE'].'?lid='.$user->getLid().'&xid='.$xid.($globalfilter !== null?'&filter='.$globalfilter:'').'" category="community" class="listing_thumbnail '.($xidfilter == $xid?'filter_selected':'filter_unselected').'" size="small" '.($community->getPid() === null?'':'pid="'.$community->getPid().'"').' />';
		} else {
			echo '<a ',($xidfilter == $xid?'class="filtered" ':''),'href="',$PAGE['COMPETE'],'?lid=',$user->getLid(),'&xid=',$xid,($globalfilter !== null?'&filter='.$globalfilter:''),'">',
				 '<translate id="COMPETE_FILTER_COMMUNITY">',
				 'Open competition(s) for <community_name xid="',$xid,'"/>',
				 '</translate>',
				 '</a><br/>';
		}
	}
	
	if ($user->getCommunityFilterIcons()) echo '</div> <!-- icon_filters -->';
	
	echo '</div> <!-- community_filters -->',
		 '</div> <!-- community_filter -->';
}

if (!empty($communitylist)) echo '<ad id="ad_compete" ad_id="COMPETE"/>';

echo '<div id="competition_list">';

$competitionlist = array();

if (empty($communitylist)) {
	echo '<div class="',($hideads?'listing_item':'marginless_item'),'">',
		 '<div class="listing_header">',
		 '<translate id="COMPETE_NO_COMMUNITIES">',
		 'You\'re not a member of any community yet. You must first <a href="',$PAGE['JOIN_COMMUNITIES'],'?lid=',$user->getLid(),'">join a community</a> before you can enter competitions.',
		 '</translate>',
		 '</div> <!-- listing_header -->',
		 '</div> <!-- listing_item -->';
} else foreach ($communitylist as $xid) {
	if ($xidfilter === null || $xidfilter == $xid)
		$competitionlist += CompetitionList::getByXidAndStatus($xid, $COMPETITION_STATUS['OPEN']);
}

// Remove hidden competitions

$competitionhidelist = CompetitionHideList::getByUid($user->getUid());

if ($globalfilter != $COMPETE_FILTER['HIDDEN'])
	foreach ($competitionhidelist as $cid) unset($competitionlist[$cid]);

if (!empty($communitylist) && empty($competitionlist)) {
	echo '<div class="',($hideads?'listing_item':'marginless_item'),'">',
		 '<div class="listing_header">';
	if ($xidfilter === null) {
		echo '<translate id="COMPETE_NO_OPEN_COMPETITIONS">',
			 'There are currently no open competitions in your communities (except ones you might have hidden). You can <a href="',$PAGE['JOIN_COMMUNITIES'],'?lid=',$user->getLid(),'">join more communities</a> if you like.',
			 '</translate>';
	} else {
		echo '<translate id="COMPETE_FILTERED_NO_OPEN_COMPETITIONS">',
			 'There are currently no open competitions in this community (except ones you might have hidden).',
			 '</translate>';
	}
	echo '</div> <!-- listing_header -->',
		 '</div> <!-- listing_item -->';
} elseif (!empty($communitylist)) {
	$competition = array();
	$voteTimeList = array();
	
	foreach ($competitionlist as $cid => $start_time) {
		$competition[$cid] = Competition::get($cid); 
		switch ($user->getStatus()) {
			case $USER_STATUS['UNREGISTERED']:
				$status = $ENTRY_STATUS['ANONYMOUS'];
				break;
			case $USER_STATUS['BANNED']:
				$status = $ENTRY_STATUS['BANNED'];
				break;
			default:
				$status = $ENTRY_STATUS['POSTED'];
		}
		
		if ($globalfilter == $COMPETE_FILTER['ENTERED']) {
			$entrylist = EntryList::getByUidAndCidAndStatus($user->getUid(), $cid, $status);
			if (!empty($entrylist)) $voteTimeList[$cid] = $competition[$cid]->getVoteTime();
		} elseif ($globalfilter == $COMPETE_FILTER['VIRGIN']) {
			$entrylist = EntryList::getByUidAndCidAndStatus($user->getUid(), $cid, $status);
			if (empty($entrylist)) $voteTimeList[$cid] = $competition[$cid]->getVoteTime();
		} elseif ($globalfilter == $COMPETE_FILTER['HIDDEN']) {
			if (in_array($cid, $competitionhidelist)) $voteTimeList[$cid] = $competition[$cid]->getVoteTime();
		} else $voteTimeList[$cid] = $competition[$cid]->getVoteTime();
		
		if ($competition[$cid]->getXid() == 267) {
			$voteTimeList[$cid] = time();
		}
	}
	
	asort($voteTimeList);
	
	$first = true;
	foreach ($voteTimeList as $cid => $vote_time) {
		if ($vote_time >= gmmktime())
			RenderCompetition($competition[$cid], $first, isset($_REQUEST['highlight']) && $cid == $_REQUEST['highlight']);
		if ($first) $first = false;
	}
}

?>

</div> <!-- competition_list -->

<?php

$page->endHTML();
$page->render();
?>
