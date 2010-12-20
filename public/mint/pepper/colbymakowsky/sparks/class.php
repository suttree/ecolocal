<?php
/******************************************************************************
 Pepper
 
 Developer		: Colby Makowsky and Marc A. Garrett
 Plug-in Name	: Sparks!
 
 http://www.rachelandcolby.com/sparks

 ******************************************************************************/
 
$installPepper = "CM_Sparks";
	
class CM_Sparks extends Pepper
{
	var $version	= 91; // Displays as 0.91
	var $info		= array
	(
		'pepperName'	=> 'Sparks!',
		'pepperUrl'		=> 'http://www.rachelandcolby.com/sparks',
		'pepperDesc'	=> 'Generate sparklines for tracked pages and referrers. Sparks! is built upon the <a href=\'http://www.sparkline.org\'>Sparklines PHP Graphing Library</a>, which is &copy; 2004 James Byers. Sparklines requires PHP 4.0.6 or newer and GD 2.0 built as a PHP module.',
		'developerName'	=> 'Colby Makowsky</a> and <a href=\'http://since1968.com\'>Marc A. Garrett',
		'developerUrl'	=> 'http://www.rachelandcolby.com'
	);
	var $panes = array
	(
		'Sparks!' => array
		(
			'Bar Graph', 'Line Graph'
		)
	);
	var $prefs = array
	(
		'showStats' => 1,
		'barColor' => '#7B9F53',
		'lineColor' => '#333333'
	);
	var $manifest = array( );

	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		if ($this->Mint->version >= 120)
		{
			return array
			(
				'isCompatible'	=> true
			);
		}
		else
		{
			return array
			(
				'isCompatible'	=> false,
				'explanation'	=> '<p>Sparks! is only compatible with Mint 1.2 and higher.</p>'
		);
		}
	}
	
	/**************************************************************************
	 onJavaScript()
	 **************************************************************************/
	function onJavaScript() 
	{ }
	
	/**************************************************************************
	 onRecord()
	 **************************************************************************
	function onRecord() 
	{ 
		return array( );
	}*/
	
	/**************************************************************************
	 onDisplay()
	 **************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		$html = '';

		switch($pane) {
			/* Sparks! ************************************************************/
			case 'Sparks!':
				switch($tab) {
					/* Bar Graph **************************************************/
					case 'Bar Graph':	
						$html .= $this->getHTML_Sparks("bar");
						break;
					/* Line Graph *************************************************/
					case 'Line Graph':
						$html .= $this->getHTML_Sparks("line");
						break;
					}
				break;
			}

		return $html;
	}
	
	/**************************************************************************
	 onDisplayPreferences()
	 **************************************************************************/
	function onDisplayPreferences() 
	{
	
		$show_stats = ( $this->prefs['showStats'] ? ' checked="checked"' : '' );
	
		/* Global *************************************************************/
		$preferences['Global']	= <<<HERE
<table>
	<tr>
		<th scope="row">Bar Graph Color</th>
		<td><span><input type="text" id="barColor" name="barColor" value="{$this->prefs['barColor']}" /></span></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>A hex color value, eg. <code>#7B9F53</code>.</td>
	</tr>
	<tr>
		<th scope="row">Line Graph Color</th>
		<td><span><input type="text" id="lineColor" name="lineColor" value="{$this->prefs['lineColor']}" /></span></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>A hex color value, eg. <code>#333333</code>.</td>
	</tr>
	<tr>
		<th scope="row">Show High and Low?</th>
		<td><input type="checkbox" id="showStats" name="showStats" value="1" {$show_stats} " /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Show the <strong>high</strong> and <strong>low</strong> figures next to each Sparkline.</td>
	</tr>
</table>

HERE;
		
		return $preferences;
	}
	
	/**************************************************************************
	 onSavePreferences()
	 **************************************************************************/
	function onSavePreferences() 
	{
		$this->prefs['showStats'] = ( isset($_POST['showStats']) ? $_POST['showStats'] : 0 );
		$this->prefs['barColor'] = $this->escapeSQL($_POST['barColor']);
		$this->prefs['lineColor'] = $this->escapeSQL($_POST['lineColor']);
	}
	
	
	/**************************************************************************
	getHTML_Sparks()
	**************************************************************************/

	function getHTML_Sparks($graphType = "bar") {
		$html = '';
		
        $defaultPepper = $this->Mint->getPepperByClassName('SI_Default');
        $visits = $defaultPepper->data['visits'];


		// Past Day
        $offset = $this->Mint->cfg['offset'];

		$dHigh = null;
		$dLow = null;
		$totalHours = 0;
    
        $thisHour = $this->Mint->getOffsetTime('hour');
        $thisHourFormatted = $this->Mint->offsetDate('H', $thisHour);

        for ($i = 0; $i < 24; $i++) {
            $j = $thisHour - ($i * 60 * 60);
            if (isset($visits[1][$j])) { 
            	$h = $visits[1][$j]; 
            	$totalHours += $h['total'];
            }
            else { $h = array('total'=>'0','unique'=>'0'); }
            
            $hourString .= ( isset($h['total'] ) ? $h['total'] : '0' );
            if ($i+1 <= 24) {
            	$hourString .= ",";
            }
            
            if ($dHigh == null || $h['total'] > $dHigh) {
            	$dHigh = $h['total'];
            }
            
            if ($dLow == null || $h['total'] < $dLow || !isset($h['total'])) {
            	if (isset($h['total'])) {
            		$dLow = $h['total'];
            	}
            	else {
            		$dLow = 0;
            	}
            }
       
		}
	
        // Past 7 days
        $wHigh = null;
        $wLow = null;
        $totalDays = 0;
        
		$day = $this->Mint->getOffsetTime('today');
        $todayDay = $this->Mint->offsetDate('D', $day);
 
 		for ($i = 0; $i < 7; $i++) {
            $j = $day - ($i * 60 * 60 * 24);
            if (isset($visits[2][$j])) { 
            	$d = $visits[2][$j]; 
            	$totalDays += $d['total'];
            }
            else { $d = array('total'=>'0','unique'=>'0'); }
            
			$dayString .= ( isset($d['total']) ? $d['total'] : '0' );

			if ($i+1 <= 7) {
				$dayString .= ",";
			}

			if ($wHigh == null || $d['total'] > $wHigh) {
				$wHigh = $d['total'];
			}

			if ($wLow == null || $d['total'] < $wLow || !isset($d['total'])) {
				if (isset($d['total'])) {
					$wLow = $d['total'];
				}
				else {
					$wLow = 0;
				}				
			}
		}
		
		// Past Month
		$week = $this->Mint->getOffsetTime('week');
		
		$mHigh = null;
		$mLow = null;
		$totalWeeks = 0;
		
		for ($i = 0; $i < 5; $i++) {
			$j = $week - ($i * 60 * 60 * 24 * 7);
			if (isset($visits[3][$j])) {
				$w = $visits[3][$j];
				$totalWeeks += $w['total'];
			}	
			else {
				$w = array('total'=>'0','unique'=>'0');
			}

			$weekString .= ( isset($w['total']) ? $w['total'] : '0' );

			if ($i+1 < 5) {
				$weekString .= ",";
			}

			if ($mHigh == null || $w['total'] > $mHigh) {
				$mHigh = $w['total'];
			}

			if ($mLow == null || $w['total'] < $mLow || !isset($w['total'])) {
				if (isset($w['total'])) {
					$mLow = $w['total'];
				}
				else {
					$mLow = 0;
				}
			}
		}
		
       // Past 12 months
        $month = $this->Mint->getOffsetTime('month');

		$yLow = null;
		$yHigh = null;
		$totalMonths = 0;

        for ($i = 0; $i < 12; $i++) {
            if ($i == 0) { $j = $month; }
            else {
                $days = $this->Mint->offsetDate('t', $this->Mint->offsetMakeGMT(0, 0, 0, $this->Mint->offsetDate('n', $month)-1, 1, $this->Mint->offsetDate('Y', $month))); // days in the month
                $j = $month - ($days * 24 * 3600);
                }
            $month = $j;
            if (isset($visits[4][$j])) { 
            	$m = $visits[4][$j]; 
            	$totalMonths += $m['total'];
            }
            else { $m = array('total'=>'0','unique'=>'0'); }

			$monthString .= ( isset($m['total'] ) ? $m['total'] : '0' );

			if ($i+1 < 12) {
				$monthString .= ",";
			}

			if ($yHigh == null || $m['total'] > $yHigh) {
				$yHigh = $m['total'];
			}

			if ($yLow == null || $m['total'] < $yLow || !isset($m['total'])) {
				if (isset($y['total'])) {
					$yLow = $m['total'];
				}
				else {
					$yLow = 0;
				}
            }

		}		

		$tableData['table'] = array('id'=>'','class'=>'');

		$barColor = urlencode($this->prefs['barColor']);
		$lineColor = urlencode($this->prefs['lineColor']);
		$graphColor = ( ($graphType == "line") ? $lineColor : $barColor );

		if ($this->Mint->cfg['preferences']['secondary']) {
			$tH = '<br /><span>Total visits in the past 24 hours: ' . number_format($totalHours) . '</span>';
			$tD = '<br /><span>Total visits in the past 7 days: ' . number_format($totalDays) . '</span>';
			$tW = '<br /><span>Total visits in the past 5 weeks: ' . number_format($totalWeeks) . '</span>';
			$tM = '<br /><span>Total visits in the past 12 months: ' . number_format($totalMonths) . '</span>';
		}
		else {	
			$tH = '';
			$td = '';
			$tW = '';
			$tM = '';
		}

		if ($this->prefs['showStats']) {
			$tableData['thead'] = array(
				// display name, CSS class(es) for each column
				array('value'=>'Period','class'=>''),
				array('value'=>'Sparkline','class'=>''),
				array('value'=>'High','class'=>''),
				array('value'=>'Low','class'=>''),
				);
			
			$tableData['tbody'][] = array(
				"Past Twenty-Four Hours" . $tH,
				"<img src=\"" .$this->Mint->cfg['installDir']. "/pepper/colbymakowsky/sparks/sparks.php?q=$hourString&bw=2&type=$graphType&color=$graphColor&odd=1\">",
				$dHigh,
				$dLow,
				);

			$tableData['tbody'][] = array(
				"Past Seven Days" . $tD,
				"<img src=\"" .$this->Mint->cfg['installDir']. "/pepper/colbymakowsky/sparks/sparks.php?q=$dayString&bw=4&type=$graphType&color=$graphColor&odd=0\">",
				$wHigh,
				$wLow,
				);
		
			$tableData['tbody'][] = array(
				"Past Five Weeks" . $tW,
				"<img src=\"" .$this->Mint->cfg['installDir']. "/pepper/colbymakowsky/sparks/sparks.php?q=$weekString&bw=4&type=$graphType&color=$graphColor&odd=1\">",
				$mHigh,
				$mLow,
				);

			$tableData['tbody'][] = array(
				"Past Twelve Months" . $tM,
				"<img src=\"" .$this->Mint->cfg['installDir']. "/pepper/colbymakowsky/sparks/sparks.php?q=$monthString&bw=4&type=$graphType&color=$graphColor&odd=0\">",
				$yHigh,
				$yLow,
				);
			}
		else {
			$tableData['thead'] = array(
				// display name, CSS class(es) for each column
				array('value'=>'Period','class'=>''),
				array('value'=>'Sparkline','class'=>''),
				);

			$tableData['tbody'][] = array(
				"Past Twenty-Four Hours" . $tH,
				"<img src=\"" .$this->Mint->cfg['installDir']. "/pepper/colbymakowsky/sparks/sparks.php?q=$hourString&bw=2&type=$graphType&color=$graphColor&odd=1\">",
				);

			$tableData['tbody'][] = array(
				"Past Seven Days" . $tD ,
				"<img src=\"" .$this->Mint->cfg['installDir']. "/pepper/colbymakowsky/sparks/sparks.php?q=$dayString&bw=4&type=$graphType&color=$graphColor&odd=0\">",
				);

			$tableData['tbody'][] = array(
				"Past Five Weeks" . $tW,
				"<img src=\"" .$this->Mint->cfg['installDir']. "/pepper/colbymakowsky/sparks/sparks.php?q=$weekString&bw=4&type=$graphType&color=$graphColor&odd=1\">",
				);

			$tableData['tbody'][] = array(
				"Past Twelve Months" . $tM,
				"<img src=\"" .$this->Mint->cfg['installDir']. "/pepper/colbymakowsky/sparks/sparks.php?q=$monthString&bw=4&type=$graphType&color=$graphColor&odd=0\"'>",
				);
			}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}

}
?>