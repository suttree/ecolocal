<?php
/******************************************************************************
 Pepper
 
 Developer		: John Nunemaker
 Plug-in Name	: Addicted To Feedburner
 
 http://addictedtonew.com/

 ******************************************************************************/
 
$installPepper = "JN_FeedburnerStats";
	
class JN_FeedburnerStats extends Pepper
{
	var $version	= 302;
	var $info		= array
	(
		'pepperName'	=> 'Feedburner Stats',
		'pepperUrl'		=> 'http://addictedtonew.com/archives/87/mint-feedburner-stats-pepper/',
		'pepperDesc'	=> 'Check your Feedburner stats from Mint.',
		'developerName'	=> 'John Nunemaker',
		'developerUrl'	=> 'http://addictedtonew.com/'
	);
	var $panes 		= array
	(
		'Feedburner Stats' => array
		(
			'Circulation/Hits',
			'Item Data'
		)
	);
	var $prefs 		= array
	(
		'feedburner_uri' => '',
		'num_days' => 7
	);
	var $manifest 	= array ( );

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
				'explanation'	=> '<p>This Pepper is only compatible with Mint 1.2 and higher.</p>'
		);
		}
	}
	
	/**************************************************************************
	 onDisplay()
	 **************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		$html = '';
		
		switch($pane) 
		{
			/* Feedburner Stats ***********************************************************/
			case 'Feedburner Stats': 
				switch($tab) 
				{
					/* Circulation/Hits ************************************************/
					case 'Circulation/Hits':
						$html .= $this->getHTML_CirculationAndHits();
					break;
					/* Item Data ************************************************/
					case 'Item Data':
						$html .= $this->getHTML_ItemData();
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
		/* Global *************************************************************/
		$preferences['Feedburner Stats'] = "
<table>
	<tr>
		<th scope='row'>Feedburner URI</th>
		<td><span><input type='text' id='feedburner_uri' name='feedburner_uri' value='{$this->prefs['feedburner_uri']}' /></span></td>
	</tr>
	<tr>
		<td colspan='2'>Seperate multiple feeds with a comma.<br />(ie: johnnunemaker,orderedlist)</td>
	</tr>
	<tr>
		<th scope='row'>Number of Days</th>
		<td><span><input type='text' id='num_days' name='num_days' value='{$this->prefs['num_days']}' /></span></td>
	</tr>
	<tr>
		<td colspan='2'>The number of days of Feedburner stats you would like to show.</td>
	</tr>
</table>";	
		
		return $preferences;
	}
	
	/**************************************************************************
	 onSavePreferences()
	 **************************************************************************/
	function onSavePreferences() 
	{
		$this->prefs['feedburner_uri'] 	= $this->escapeSQL($_POST['feedburner_uri']);
		$this->prefs['num_days'] 		= $this->escapeSQL($_POST['num_days']);
	}
	
	/**
	* Shows the feed data for the $pref['uri']
	*
	* @return string $html The html to display in the tab
	* @access public
	*/
	function getHTML_CirculationAndHits() {
		require_once 'class.feedburner.php';
		
		$html = '';
		$this->prefs['num_days'] = ($this->prefs['num_days'] == '') ? 7 : $this->prefs['num_days'];
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>'Day','class'=>''),
			array('value'=>'Circulation','class'=>''),
			array('value'=>'Hits','class'=>''),
			);
		
		
		// get all the feedburner uri's
		$uris = explode(',', $this->prefs['feedburner_uri']);
		
		if (count($uris) > 0){
			foreach($uris as $uri) {
				$uri 		= trim($uri);
				$fb 		=& new feedburner($uri);
				$date_str 	= date('Y-m-d', strtotime('-' . $this->prefs['num_days'] . ' days', time())) . ',' . date('Y-m-d', time());
				$info 		= $fb->getFeedData(array('dates'=>$date_str));
				$tableData['tbody'][] = array('<strong style="font-size:small;">' . $uri . '</strong>', 'colspan'=>3);
				
				// check for error with feed
				if ($fb->isError()) {
					if ($this->prefs['feedburner_uri'] == '') {
						$tableData['tbody'][] = array('Error: You have not entered a feedburner uri in the preferences.');
					} else {				
						// if feedburner awareness not enabled
						if ($fb->getErrorCode() == 2) {
							$tableData['tbody'][] = array('Your Feedburner Awareness API is not enabled. To enable it, login to your Feedburner account, click on publicize, then on awareness api and finally click activate.', 'colspan'=>3);
						} else {
							$tableData['tbody'][] = array($fb->getErrorMsg(), 'colspan'=>3);
						}
					}
				} else {
					$entries 	= $info['entries'];
					$count 		= count($entries);
					$tot_circ 	= 0;
					$tot_hits 	= 0;
					if ($count > 0) {
						for ($i=$count; $i>0; $i--) {
							$tableData['tbody'][] = array(
															date('l M j', strtotime($entries[$i]['date'])),
															$entries[$i]['circulation'],
															$entries[$i]['hits']
														);
							$tot_circ += $entries[$i]['circulation'];
							$tot_hits += $entries[$i]['hits'];
						}
						$avg_circ = $tot_circ / $this->prefs['num_days'];
						$avg_hits = $tot_hits / $this->prefs['num_days'];
						$tableData['tbody'][] = array(
														'<strong>Averages for Past ' . $this->prefs['num_days'] . ' days</strong>',
														'<strong>' . number_format($avg_circ,0) . '</strong>',
														'<strong>' . number_format($avg_hits) . '</strong>'
													);
					}
				}			
				$tableData['tbody'][] = array('&nbsp;', 'colspan'=>3);
			}
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**
	* Shows the clickthroughs and hits for individual items
	*
	* @return string $html The html to display in the tab
	* @access public
	*/
	function getHTML_ItemData() {
		require_once 'class.feedburner.php';
		
		$html 		= '';
		$tableData 	= array();
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>'Item','class'=>''),
			array('value'=>'Hits','class'=>''),
			array('value'=>'Clicks','class'=>''),
		);
		
		// get all the feedburner uri's
		$uris = explode(',', $this->prefs['feedburner_uri']);
		
		if (count($uris) > 0){
			foreach($uris as $uri) {
				$uri = trim($uri);
				$fb =& new feedburner($uri);
				$result = $fb->getItemData();
				$tableData['tbody'][] = array('<strong style="font-size:small;">' . $uri . '</strong>', 'colspan'=>3);
				if ($fb->isError()) {
					if ($this->prefs['feedburner_uri'] == '') {
						$tableData['tbody'][] = array('Error: You have not entered your feedburner uri in the preferences.');
					} else {				
						// if feedburner awareness not enabled
						if ($fb->getErrorCode() == 2) {
							$tableData['tbody'][] = array('To enable your feedburner awareness api login to your feedburner account, click on publicize and then on awareness api. You can then click activate.', 'colspan'=>3);
						} else if ($fb->getErrorCode() == 4) {
							$tableData['tbody'][] = array('Item data only works if you have a <a href="http://www.feedburner.com/fb/a/pro-totalstats">Feedburner TotalStats Pro</a> Account.', 'colspan'=>3);
						} else {
							$tableData['tbody'][] = array($fb->getErrorMsg(), 'colspan'=>3);
						}
					}
				} else {
					$items = $result['entries'][1]['items'];
					if (count($items) > 0) {
						foreach($items as $item) {
							$tableData['tbody'][] = array(
														"<a href=\"{$item['url']}\">{$item['title']}</a>",
														number_format($item['itemviews']),
														number_format($item['clickthroughs'])
													);
						}
					}
				}
				$tableData['tbody'][] = array('&nbsp;', 'colspan'=>3);
			}
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
}
?>