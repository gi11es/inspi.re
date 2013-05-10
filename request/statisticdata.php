<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Renders the chart data for site-wide stats
*/

require_once(dirname(__FILE__).'/../entities/statistic.php');
require_once(dirname(__FILE__).'/../entities/statisticlist.php');
require_once(dirname(__FILE__).'/../libraries/open-flash-chart.php');
require_once(dirname(__FILE__).'/../constants.php');

if (isset($_REQUEST['sid'])) {
	if ($_REQUEST['sid'] == $STATISTIC['COMMENTS_ENTRIES_RATIO']) {
		$entrylist = StatisticList::getBySid($STATISTIC['ENTRIES']);
		$entrydatelist = array_keys($entrylist);
		arsort($entrydatelist);
		
		$commentlist = StatisticList::getBySid($STATISTIC['COMMENTS_WORDCOUNT']);
		$datelist = array_keys($commentlist);
		asort($datelist);
		
		$statlist = array();
		foreach ($datelist as $timestamp) {
			$statlist[$timestamp] = $commentlist[$timestamp] / $entrylist[array_pop($entrydatelist)];
		}
	} else {
		$statlist = StatisticList::getBySid($_REQUEST['sid']);
		$datelist = array_keys($statlist);
		asort($datelist);
	}
	
	$data_1 = new line_hollow( 2, 4, '#FFA927' );
	
	$min = 9999999999;
	$max = 0;
	foreach ($datelist as $timestamp) {
		$data_1->add_data_tip($statlist[$timestamp], date('D j/n/Y', $timestamp));
		if ($statlist[$timestamp] < $min) $min = $statlist[$timestamp];
		elseif ($statlist[$timestamp] > $max) $max = $statlist[$timestamp];
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
		$g->set_y_right_max( $max );
		$g->set_y_right_min( $min );
		$g->y_right_axis_colour( '#929292' );
		
		$g->set_y_max($max);
		$g->set_y_min($min);
		//$g->y_label_steps( 10 );
		echo $g->render();
}
?>