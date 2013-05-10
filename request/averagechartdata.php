<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Adds a user to the list of donators
*/

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevel.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../libraries/open-flash-chart.php');

if (isset($_REQUEST['token'])) try {
	$uid = Token::get($_REQUEST['token']);
	$user = User::get($uid);
	
	$levels = UserLevelList::getByUid($uid);
	
	if (in_array($USER_LEVEL['PREMIUM'], $levels)) {
		$votelist = EntryVoteList::getByAuthorUidAndStatus($user->getUid(), $ENTRY_VOTE_STATUS['CAST']);

		$average = array();
		$percentile = array();
		$competitionendtime = array();
		
		$entry = Entry::getArray(array_keys($votelist));
		
		$competitionlist = array();
		foreach ($entry as $eid => $ent) $competitionlist []= $ent->getCid();
		$competition = Competition::getArray($competitionlist);
		
		$themelist = array();
		foreach ($competition as $cid => $comp) $themelist []= $comp->getTid();
		$theme = Theme::getArray($themelist);
		
		foreach ($votelist as $eid => $votes) {
			$cid = $entry[$eid]->getCid();
			
			if (!isset($_REQUEST['xid']) || $competition[$cid]->getXid() == $_REQUEST['xid']) {	
				$rank = $entry[$eid]->getRank();
			
				if ($rank !== null && $rank != 0) {
					$comp = $competition[$cid];
					$competitionendtime[$cid] = $comp->getEndTime();
				
					if (count($votes) > 0) $average[$cid] = round(array_sum($votes) / count($votes), 2);
					else $average[$cid] = 0;
				
					$totalparticipants = $comp->getEntriesCount();
					$samerank = count(EntryList::getByCidAndRank($cid, $rank)) - 1;
					
					if ($totalparticipants > 1)
						$percentile[$cid]= 100 * round(($totalparticipants - $rank - $samerank) / ($totalparticipants - 1), 4);
					else
						$percentile[$cid]= 100;
				}
			}
		}
		
		asort($competitionendtime);
		
		if (count($competitionendtime) > 100) $competitionendtime = array_slice($competitionendtime, count($competitionendtime) - 100, 100, true);
		
		$data_1 = new line_hollow( 2, 4, '#FFA927' );
		
		
		foreach ($competitionendtime as $cid =>$end_time) {
			$data_1->add_data_tip($average[$cid], $theme[$competition[$cid]->getTid()]->getTitle());
		}
		
		$g = new graph();
		$g->title(' ', '{font-size: 35px;}');
		$g->bg_colour = '#FFFFFF';
		$g->set_x_offset( false );
		
		$g->x_axis_colour( '#929292', '#FFFFFF' );
		$g->y_axis_colour( '#929292', '#929292' );
		
		$g->data_sets[] = $data_1;
		
		$g->set_tool_tip( '#tip#<br>#val#' );
		
		$g->attach_to_y_right_axis(1);
		$g->set_y_right_max( 5.0 );
		$g->y_right_axis_colour( '#929292' );
		
		$g->set_y_max(5.0);
		$g->set_y_min(0.0);
		$g->y_label_steps( 10 );
		echo $g->render();
	}
} catch (Exception $e) {}
?>