<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Returns a CSV file containing the statistics of a given user
*/

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../utilities/inml.php');

$user = User::getSessionUser();
$levels = UserLevelList::getByUid($user->getUid());

if (in_array($USER_LEVEL['PREMIUM'], $levels)) {
	$community_name = '';
	if (isset($_REQUEST['xid'])) try {
		$community = Community::get($_REQUEST['xid']);
		$community_name = '-'.urlencode($community->getName());
	} catch (CommunityException $e) {}

	header('Content-Type: application/force-download');  
	header('Content-Transfer-Encoding: application/octet-stream'); 
	header('Content-disposition: filename='.urlencode($user->getUniqueName()).$community_name.'-stats-'.date('d-m-Y').'.csv');
	
	$votelist = EntryVoteList::getByAuthorUidAndStatus($user->getUid(), $ENTRY_VOTE_STATUS['CAST']);
	
	$result = '<translate id="CSV_COMMUNITY_COLUMN_TITLE">Community</translate>'.',';
	$result .= '<translate id="CSV_COMPETITION_COLUMN_TITLE">Competition</translate>'.',';
	$result .= '"<translate id="CSV_END_DATE_COLUMN_TITLE">End date (server\'s timezone, DD/MM/YYYY)</translate>"'.',';
	$result .= '<translate id="CSV_TOTAL_COLUMN_TITLE">Amount of entries</translate>'.',';
	$result .= '<translate id="CSV_RANK_COLUMN_TITLE">Rank of your entry</translate>'.',';
	$result .= '<translate id="CSV_VOTES_COLUMN_TITLE">Votes</translate>';
	
	$translated_html = I18N::translateHTML($user, $result);
	$result = INML::processHTML($user, $translated_html);
	
	echo $result;
	echo "\r\n";
	
	$entry = Entry::getArray(array_keys($votelist));
	
	foreach ($votelist as $eid => $votes) if (isset($entry[$eid])) {
		$competition = Competition::get($entry[$eid]->getCid());
		
		if (!isset($_REQUEST['xid']) || $_REQUEST['xid'] == $competition->getXid()) try {	
			$theme = Theme::get($competition->getTid());
			$community = Community::get($competition->getXid());
			
			echo '"'.mb_ereg_replace('"','""',$community->getName()).'","'.mb_ereg_replace('"','""',$theme->getTitle()).'",';
			
			echo date('d/m/Y', $competition->getEndTime()).',';
			
			if ($competition->getStatus() != $COMPETITION_STATUS['OPEN']) {
				$count = $competition->getEntriesCount();
				if ($count > 0) echo $count;
			}
			
			echo ',';
			
			if ($competition->getStatus() == $COMPETITION_STATUS['CLOSED']) {
				if ($user->getStatus() == $USER_STATUS['BANNED']) echo $entry[$eid]->getBannedRank();
				else echo $entry[$eid]->getRank();
			}
			
			foreach ($votes as $value) {
				echo ','.$value;
			}
			echo "\r\n";
		} catch (CommunityException $e) {}
	}
}

?>