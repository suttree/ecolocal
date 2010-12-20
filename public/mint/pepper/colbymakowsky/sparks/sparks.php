<?php
/*
 * Sparkline PHP Graphing Library
 * Copyright 2004 James Byers <jbyers@users.sf.net>
 * http://sparkline.org
 *
 * Sparkline is distributed under a BSD License.  See LICENSE for details.
 *
 */

//////////////////////////////////////////////////////////////////////////////
// build sparkline using standard flow:
//   construct, set, render, output
//

$query_var = $_GET["q"];
$values = explode(",", $query_var);

$graphType = strtoupper($_GET['type']);
if ($graphType == '') {
	$graphType = 'BAR';
	}

$is_odd = $_GET['odd'];

$graphColor = $_GET['color'];

$bar_width = $_GET['bw'];
if ($bar_width == '') {
	$bar_width = 1;
	}


$isDebug = ($_GET["debug"] == 1);

if ($graphType == 'LINE')
{
	require_once( dirname(__FILE__) . '/lib/Sparkline_Line.php');

	$sparkline = new Sparkline_Line();

	if ($isDebug) {
		$sparkline->SetDebugLevel(DEBUG_ERROR | DEBUG_WARNING | DEBUG_STATS | DEBUG_CALLS | DEBUG_SET, 'log.txt');
		}
	else {
		$sparkline->SetDebugLevel(DEBUG_NONE);
		}

	if ($is_odd) {
		$sparkline->SetColorHtml('mintOdd','#F0F7E2');
		$sparkline->SetColorBackground('mintOdd');
		}
	else {
		$sparkline->SetColorBackground('white');
		}

	$sparkline->SetYMin(0);
	$sparkline->SetPadding(0);
	$sparkline->SetColorHtml("sparkLine",$graphColor);

	$j = 0;
	for($i = sizeof($values); $i >= 0; $i--) {
		$sparkline->SetData($j++, $values[$i]);
	}

	$sparkline->Render(75,25);

	$sparkline->Output();
}
else {
	require_once( dirname(__FILE__) . '/lib/Sparkline_Bar.php');

	$sparkline = new Sparkline_Bar();

	if ($isDebug) {
		$sparkline->SetDebugLevel(DEBUG_ERROR | DEBUG_WARNING | DEBUG_STATS | DEBUG_CALLS | DEBUG_SET, 'log.txt');
		}
	else {
		$sparkline->SetDebugLevel(DEBUG_NONE);
		}

	if ($is_odd) {
		$sparkline->SetColorHtml('mintOdd','#F0F7E2');
		$sparkline->SetColorBackground('mintOdd');
		}
	else {
		$sparkline->SetColorBackground('white');
		}

	$sparkline->SetBarWidth($bar_width);
	$sparkline->SetBarSpacing(1);
	$sparkline->SetColorHtml("shaungreen",$graphColor);
	$sparkline->SetBarColorDefault('shaungreen');

	$j = 0;
	for($i = sizeof($values); $i >= 0; $i--) {
	  $sparkline->SetData($j++, $values[$i]);
	}

	$sparkline->Render(25); // height only for Sparkline_Bar

	$sparkline->Output();
}
?>