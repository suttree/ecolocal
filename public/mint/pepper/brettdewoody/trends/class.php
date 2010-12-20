<?php
/******************************************************************************
 Pepper
 
 Developer		: Brett DeWoody and Ronald Heft
 Plug-in Name	: Trends
 
 http://cavemonkey50.com/code/trends

 ******************************************************************************/

if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file

$installPepper = "BD_Trends";

class BD_Trends extends Pepper {

	var $version = 202;

	var $info = array
	(
		'pepperName'	=> 'Trends', 
		'pepperUrl'		=> 'http://cavemonkey50.com/code/trends/',
		'pepperDesc'	=> 'Tracks trends across a specified period.',
		'developerName'	=> '<a href="http://www.brettdewoody.com">Brett DeWoody</a> and <a href="http://cavemonkey50.com/">Ronald Heft</a>',
		'developerUrl'	=> ''
	);

	var $panes = array
	(
		'Trends Internal' => array
		(
			'Popular',
			'Best',
			'Worst',
			'New',
			'Old',
			'Watched'
		),
		'Trends External' => array
		(
			'Quick View',
			'Referrers',
			'Searches'
		)
	);

	var $prefs = array
	(
		'compare_days' => 1,
		'compare_to' => 1,
		'compare_timeframe' => 1
	);

 	//Don't need to add anything to the table
 	var $manifest = array();

	function isCompatible() {
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
	Updater
	**************************************************************************/
	
	function update(){

		if($this->Mint->cfg['pepperShaker'][$this->pepperId]['version'] < 200) {

		foreach($this->Mint->cfg['panes'] as $paneId => $paneData) {
			if($paneData['pepperId'] == $this->pepperId) {
				$this->Mint->cfg['panes'][$paneId]['name'] = 'Trends Internal';
				$this->Mint->cfg['panes'][$paneId]['tabs'][0] = 'Popular';
				$this->Mint->cfg['panes'][$paneId]['tabs'][1] = 'Best';
				$this->Mint->cfg['panes'][$paneId]['tabs'][2] = 'Worst';
				$this->Mint->cfg['panes'][$paneId]['tabs'][3] = 'New';
				$this->Mint->cfg['panes'][$paneId]['tabs'][4] = 'Old';
				$this->Mint->cfg['panes'][$paneId]['tabs'][5] = 'Watched';
			}
		}
		
		$paneId = $this->Mint->array_last_index($this->Mint->cfg['panes'])+1;
		$this->Mint->cfg['panes'][$paneId] = array(
			'pepperId' => $this->pepperId,
			'name' => 'Trends External',
			'tabs' => $this->panes['Trends External']
			);
		$this->Mint->cfg['pepperShaker'][$this->pepperId]['panes'][1] = $paneId;
		$newPane = $this->Mint->array_last_index($this->Mint->cfg['preferences']['paneOrder']['enabled'])+1;
		$this->Mint->cfg['preferences']['paneOrder']['enabled'][$newPane] = $paneId;
      
		$this->Mint->cfg['pepperShaker'][$this->pepperId]['version'] = 201;

		}

		if($this->Mint->cfg['pepperShaker'][$this->pepperId]['version'] < 201) {

		foreach($this->Mint->cfg['panes'] as $paneId => $paneData) {
			if($paneData['pepperId'] == $this->pepperId) {
				if ($this->Mint->cfg['panes'][$paneId]['name'] == "Trends - Internal")
					$this->Mint->cfg['panes'][$paneId]['name'] = 'Trends Internal';
				if ($this->Mint->cfg['panes'][$paneId]['name'] == "Trends - External")
					$this->Mint->cfg['panes'][$paneId]['name'] = 'Trends External';
			}
		}
      
		$this->Mint->cfg['pepperShaker'][$this->pepperId]['version'] = 201;

		}

	return true;
	}


	/**************************************************************************
	Panes and Prefs
	**************************************************************************/

	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		$html = '';
		switch($pane) {
		/* The Internal Window *****************************************************/
			case 'Trends Internal': 
				switch($tab) {
				/* The Panes ***************************************************/
					case 'Popular':
						$html .= $this->getHTML_MostPopular();
						break;
					case 'Best':
						$html .= $this->getHTML_Best();
						break;
					case 'Worst':
						$html .= $this->getHTML_Worst();
						break;
					case 'New':
						$html .= $this->getHTML_New();
						break;
					case 'Old':
						$html .= $this->getHTML_Old();
						break;
					case 'Watched':
						$html .= $this->getHTML_Watched();
						break;
					}
				break;
		/* The External Window *****************************************************/
			case 'Trends External': 
				switch($tab) {
				/* The Panes ***************************************************/
					case 'Quick View':
						$html .= $this->getHTML_QuickView();
						break;
					case 'Referrers':
						$html .= $this->getHTML_Referrers();
						break;
					case 'Searches':
						$html .= $this->getHTML_Searches();
						break;
					}
				break;
			}
		return $html;
	}

	function onDisplayPreferences()
	{
		if ($this->prefs['page_grouping']) { $checked = "checked='checked'"; }
		/* Global *************************************************************/
		$preferences['Time Span To Compare'] = "
			<table>
				<tr>
					<td><label>Compare The Last <input type='text' id='compare_days' size='3' name='compare_days' value='{$this->prefs['compare_days']}' /> Days</label></td>
				</tr>
			</table>";
		$preferences['Time Span To Compare Against'] = "
			<table class='snug'>
				<tr>
					<td><label>Against The Previous <input type='text' id='compare_to' size='3' name='compare_to' value='{$this->prefs['compare_to']}' /> Days Average</label></td>
				</tr>
			</table>";

		return $preferences;
	}

	/**************************************************************************
	onSavePreferences()
	**************************************************************************/
	function onSavePreferences()
	{
		$this->prefs['compare_days'] = $this->escapeSQL($_POST['compare_days']);
		if (($_POST['compare_to']) >= ($this->prefs['compare_days']))
			$this->prefs['compare_to'] = $this->escapeSQL($_POST['compare_to']);
		else
			$this->prefs['compare_to'] = $this->prefs['compare_days'];
		$this->prefs['compare_timeframe'] = ($this->prefs['compare_to'] / $this->prefs['compare_days']);
	}


	/**************************************************************************
	onCustom()
	**************************************************************************/
	function onCustom() 
	{
		if 
		(
			isset($_POST['action']) && 
			$_POST['action']=='getpagehistory' && 
			isset($_POST['page'])
		)
		{
			$page = $this->escapeSQL($_POST['page']);
			$column = $this->escapeSQL($_POST['column']);
			$diff = $this->escapeSQL($_POST['diff']);
			echo $this->get_PageHistory($page,$diff,$column);
		}
	}


	/***********************************************************************************
	Work Horse - Most Popular
	***********************************************************************************/

	function getPerformanceData($view = 'active')
	{
		$prefs = $this->prefs;
		$performance_data = array();

		$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;

        $sql = "SELECT `resource`, `resource_checksum`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
				GROUP BY `resource_checksum`
				ORDER BY `total` DESC, `dt` DESC
				LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";

		$query = $this->query($sql);

		/* Now find it in the previous time segment ***********************************/
		while ($daterange1 = mysql_fetch_array($query))
		{

			$sql_temp = "SELECT `resource_checksum`, COUNT(`resource_checksum`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` > {$timeStart2} AND `dt` <= {$timeStop2} AND `resource_checksum` = {$daterange1['resource_checksum']}
					GROUP BY `resource_checksum`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";

			$query_temp = $this->query($sql_temp);

			if (mysql_num_rows($query_temp) == 0) {
				/* Page is New! *******************************************************/
				$sql_new = "SELECT `resource_checksum`, COUNT(`resource_checksum`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` <= {$timeStart2} AND `resource_checksum` = {$daterange1['resource_checksum']}
					GROUP BY `resource_checksum`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";
				$query_new = $this->query($sql_new);
				if (mysql_num_rows($query_new) == 0)
					$diff = "NEW";
				else
					$diff = "HITS";

				$performance_data[] = array($diff, 
					$daterange1["total"], 
					false,
					$daterange1["resource_title"],
					$daterange1["resource"],
					$daterange1["resource_checksum"]
					);
			} else {
				/* Time to analyze ****************************************************/
				$daterange2 = mysql_fetch_array($query_temp);

				$hits_1 = $daterange1["total"];
				$hits_2 = ($daterange2["total_temp"]/$this->prefs['compare_timeframe']);
				$diff = (($hits_1/$hits_2)*100)-100;

				$performance_data[] = array($diff, 
					$daterange1["total"],
					$daterange2["total_temp"],
					$daterange1["resource_title"],
					$daterange1["resource"],
					$daterange1["resource_checksum"]
					);
				}
		}

		return $performance_data;
	}


	/***********************************************************************************
	Work Horse - Best
	***********************************************************************************/

	function getBestData($view = 'active')
	{
		$prefs = $this->prefs;
		$best_data = array();

		$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;

        $sql = "SELECT `resource`, `resource_checksum`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
			GROUP BY `resource_checksum`
			ORDER BY `total` DESC, `dt` DESC";

		$query = $this->query($sql);

		/* Now find it in the previous time segment ***********************************/
		$i = 0;
		while (($daterange1 = mysql_fetch_array($query)) && ($i < $this->Mint->cfg['preferences']['rows'])) {

			$sql_temp = "SELECT `resource_checksum`, COUNT(`resource_checksum`) as `total_temp`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `dt` > {$timeStart2} AND `dt` <= {$timeStop2} AND `resource_checksum` = {$daterange1['resource_checksum']}
				GROUP BY `resource_checksum`
				LIMIT 1";

			$query_temp = $this->query($sql_temp);

			if (mysql_num_rows($query_temp) == 0) {
				/* Nothing */
			} else {

			/* Time to analyze ****************************************************/
			$daterange2 = mysql_fetch_array($query_temp);

			$hits_1 = $daterange1["total"];
			$hits_2 = ($daterange2["total_temp"]/$this->prefs['compare_timeframe']);
			$diff = (($hits_1/$hits_2)*100)-100;
			
				if ($diff > 0){

					$i++;
					$best_data[] = array($diff, 
						$daterange1["total"],
						$daterange2["total_temp"],
						$daterange1["resource_title"],
						$daterange1["resource"],
						$daterange1["resource_checksum"]
						);
				}
			}
		}

		return $best_data;
    }


	/***********************************************************************************
	Work Horse - Worst
	***********************************************************************************/

	function getWorstData($view = 'active')
	{

		$prefs = $this->prefs;
		$worst_data = array();

		$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;

        $sql = "SELECT `resource`, `resource_checksum`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
			GROUP BY `resource_checksum`
			ORDER BY `total` DESC, `dt` DESC";

		$query = $this->query($sql);

		/* Now find it in the previous time segment ***********************************/
		while (($daterange1 = mysql_fetch_array($query)) && ($i < $this->Mint->cfg['preferences']['rows'])) {

			$sql_temp = "SELECT `resource_checksum`, COUNT(`resource_checksum`) as `total_temp`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `dt` > {$timeStart2} AND `dt` <= {$timeStop2} AND `resource_checksum` = {$daterange1['resource_checksum']}
				GROUP BY `resource_checksum`
				LIMIT 1";

			$query_temp = $this->query($sql_temp);

			if (mysql_num_rows($query_temp) == 0) {
				/* Nothing */
			} else {
				/* Time to analyze ****************************************************/
				$daterange2 = mysql_fetch_array($query_temp);

				$hits_1 = $daterange1["total"];
				$hits_2 = ($daterange2["total_temp"]/$this->prefs['compare_timeframe']);
				$diff = (($hits_1/$hits_2)*100)-100;

				if ($diff < 0) {

					$i++;
					$worst_data[] = array($diff, 
						$daterange1["total"],
						$daterange2["total_temp"],
						$daterange1["resource_title"],
						$daterange1["resource"],
						$daterange1["resource_checksum"]
						);
				}
			}
		}

		return $worst_data;
    }


	/***********************************************************************************
	Work Horse - New
	***********************************************************************************/

	function get_New($view = 'active')
	{

		$prefs = $this->prefs;
		$new_data = array();

		$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;

        $sql = "SELECT `resource`, `resource_checksum`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
			GROUP BY `resource_checksum`
			ORDER BY `total` DESC, `dt` DESC";

		$query = $this->query($sql);

		/* Now find it in the previous time segment ***********************************/
		$i = 0;
		while (($daterange1 = mysql_fetch_array($query)) && ($i < ($this->Mint->cfg['preferences']['rows']))) {

			$sql_temp = "SELECT `resource_checksum`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `resource_checksum` = {$daterange1['resource_checksum']} AND `dt` <= {$timeStop2}
				GROUP BY `resource_checksum`
				LIMIT 1";

			$query_temp = $this->query($sql_temp);
		   
			if (mysql_num_rows($query_temp) == 0) {

				$i++;
				$new_data[] = array(
					$daterange1["total"],
					$daterange1["resource_title"],
					$daterange1["resource"],
					$daterange1["dt"]
				);
			}
		}
		
		return $new_data;

    }


	/***********************************************************************************
	Work Horse - Old
	***********************************************************************************/

	function get_Old($view = 'active')
	{

		$prefs = $this->prefs;
		$old_data = array();

		$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;

        $sql = "SELECT `resource`, `resource_checksum`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `dt` <= {$timeStop2}
			GROUP BY `resource_checksum`
			ORDER BY `total` DESC, `dt` DESC";

		$query = $this->query($sql);

		/* Now find it in the previous time segment ***********************************/
		$i = 0;
		while (($daterange1 = mysql_fetch_array($query)) && ($i < ($this->Mint->cfg['preferences']['rows']))) {

			$sql_temp = "SELECT `resource_checksum`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `resource_checksum` = {$daterange1['resource_checksum']} AND`dt` > {$timeStart1} AND `dt` <= {$timeStop1}
				GROUP BY `resource_checksum`
				LIMIT 1";

			$query_temp = $this->query($sql_temp);

			$sql_date = "SELECT `resource_checksum`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `resource_checksum` = {$daterange1['resource_checksum']}
				ORDER BY `dt` DESC
				LIMIT 1";

			$query_date = $this->query($sql_date);

			if (mysql_num_rows($query_temp) == 0) {

				$date = mysql_fetch_array($query_date);

					$i++;
					$old_data[] = array(
						$daterange1["total"],
						$daterange1["resource_title"],
						$daterange1["resource"],
						$date["dt"]
						);
		   }
		}
		
		return $old_data;

	}

/***********************************************************************************
	Work Horse - Watched
	***********************************************************************************/

	function getWatchedData($view = 'active')
	{
		$prefs = $this->prefs;
		$watched_data = array();

		$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;

		$watched = $this->Mint->data['0']['watched'];

		if (!empty($watched)) 
		{
			$where = "WHERE (`dt` > {$timeStart1} AND `dt` <= {$timeStop1}) AND (`resource_checksum` = '".implode("' OR `resource_checksum` = '", $watched)."') ";
		}
		else
		{ 
			$where = "WHERE id=-2";
		}

        $sql = "SELECT `resource`, `resource_checksum`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				$where
				GROUP BY `resource_checksum`
				ORDER BY `total` DESC, `dt` DESC
				LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";

		$query = $this->query($sql);

		/* Now find it in the previous time segment ***********************************/
		while ($daterange1 = mysql_fetch_array($query))
		{

			$sql_temp = "SELECT `resource_checksum`, COUNT(`resource_checksum`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` > {$timeStart2} AND `dt` <= {$timeStop2} AND `resource_checksum` = {$daterange1['resource_checksum']}
					GROUP BY `resource_checksum`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";

			$query_temp = $this->query($sql_temp);

			if (mysql_num_rows($query_temp) == 0) {
				/* Page is New! *******************************************************/
				$sql_new = "SELECT `resource_checksum`, COUNT(`resource_checksum`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` <= {$timeStart2} AND `resource_checksum` = {$daterange1['resource_checksum']}
					GROUP BY `resource_checksum`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";
				$query_new = $this->query($sql_new);
				if (mysql_num_rows($query_new) == 0)
					$diff = "NEW";
				else
					$diff = "HITS";

				$watched_data[] = array($diff, 
					$daterange1["total"], 
					false,
					$daterange1["resource_title"],
					$daterange1["resource"]
					);
			} else {
				/* Time to analyze ****************************************************/
				$daterange2 = mysql_fetch_array($query_temp);

				$hits_1 = $daterange1["total"];
				$hits_2 = ($daterange2["total_temp"]/$this->prefs['compare_timeframe']);
				$diff = (($hits_1/$hits_2)*100)-100;

				$watched_data[] = array($diff, 
					$daterange1["total"],
					$daterange2["total_temp"],
					$daterange1["resource_title"],
					$daterange1["resource"],
					$daterange1["resource_checksum"]
					);
			}

		}

		return $watched_data;
	}



/***********************************************************************************
	Work Horse - Page History
	***********************************************************************************/

	function get_PageHistory($page,$diff,$column)
	{

		$html = '';
		$prefs = $this->prefs;

		$compare_days = $this->prefs['compare_days'];
		if ($compare_days < 1) {
			$compare_days = 1;
		}
		if ($compare_days > 1) {
			$compare_days = round($compare_days);
		}

		$hits = array();
		$dates = array();

		$time_minutes = date(i)/60;
		$time_now = date('H')+$time_minutes; 
		$time_daystart = $time_now * 60 * 60;
		$time_daystart = time() - $time_daystart;

		if ($diff != "NEW") {
		for($i = 0; $i <= 9; $i++) { 

			if ($i == 0) {
				$timeStart1 = $time_daystart - (($compare_days-1) * 24 * 60 * 60);
				$timeStop1 = time();
			} else {
				$timeStart1 = $time_daystart - ($compare_days * 24 * 60 * 60) - ($compare_days * 24 * 60 * 60 * ($i-1)) - (($compare_days-1) * 24 * 60 * 60);
				$timeStop1 = $time_daystart - ($compare_days * 24 * 60 * 60 * ($i-1)) - (($compare_days-1) * 24 * 60 * 60);
			}
			
			$page = mysql_real_escape_string($page);

			$sql = "SELECT COUNT(`{$column}`) as `total`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `dt` > ({$timeStart1}) AND `dt` <= ({$timeStop1}) AND `{$column}` = '{$page}'
				GROUP BY `{$column}`";

			$query = $this->query($sql);

			$history = mysql_fetch_array($query);

			$page_history = $history["total"];

			if ($page_history == 0) {
				$hits[] = "0";
				} else {
				$hits[] = $page_history;
			}
			
			if ($compare_days == 1) {
				if ($i ==0) {
					$dates[] = '<strong>'.date("n/d",$timeStart1).'</strong> - Today';
				} else {
					$dates[] = '<strong>'.date("n/d",$timeStart1).'</strong> - '.date(" l",$timeStart1);
				}
			} else {
				if ($i ==0) {
					$dates[] = '<strong>'.date("n/d",$timeStart1).'</strong> - <strong>Now</strong>';
				} else {
					$dates[] = '<strong>'.date("n/d",$timeStart1).' - '.date("n/d",$timeStop1-61).'</strong>'; 
				}
			}
		}

		$max_hits = max($hits);

		for($i = 0; $i <= 9; $i++) {

			$bars = floor(44/$max_hits*$hits[$i]);

			$tableData['tbody'][] = array
				(
					$dates[$i],
					$hits[$i],
					"<img align='left' src='pepper/brettdewoody/trends/bar.gif' width='".$bars."' height='15px' />"
				);

		}
		} else {
			if ($column == "resource_checksum")
				$type = "Page";
			elseif ($column == "referer_checksum")
				$type = "Referrer";
			elseif ($column == "search_terms")
				$type = "Search";
			$tableData['tbody'][] = array
				(
					$type." is New; No History",
					"",
					""
				);
		}

		$html = $this->Mint->generateTableRows($tableData);
		return $html;

	}


	/***********************************************************************************
	Work Horse - Referrers
	***********************************************************************************/

	function getRefererData($view = 'active')
	{
		$prefs = $this->prefs;
		$referer_data = array();

		$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;

		$ignore_referrers = $this->Mint->cfg['preferences']['pepper']['0']['ignoreReferringDomains'];

		// Ignore certain domains
		$ignoredDomains	= preg_split('/[\s,]+/', $ignore_referrers);
		$ignoreQuery = '';
		if (!empty($ignoredDomains))
		{
			foreach ($ignoredDomains as $domain)
			{
				if (empty($domain))
				{
					continue;
				}
				$ignoreQuery .= ' AND `domain_checksum` != '.crc32($domain);
			}
		}

        $sql = "SELECT `resource`, `resource_title`, `referer`, `referer_checksum`, `referer_is_local`, `search_terms`, COUNT(`referer_checksum`) as `total`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1} AND `referer_is_local` = 0 AND `search_terms` = '' $ignoreQuery
				GROUP BY `referer_checksum`
				ORDER BY `total` DESC, `dt` DESC
				LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";

		$query = $this->query($sql);

		/* Now find it in the previous time segment ***********************************/
		while ($daterange1 = mysql_fetch_array($query))
		{

			$sql_temp = "SELECT `referer_checksum`, COUNT(`referer_checksum`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` > {$timeStart2} AND `dt` <= {$timeStop2} AND `referer_checksum` = {$daterange1['referer_checksum']}
					GROUP BY `referer_checksum`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";

			$query_temp = $this->query($sql_temp);

			if (mysql_num_rows($query_temp) == 0) {
				/* Referrer is New! *******************************************************/
				$sql_new = "SELECT `referer_checksum`, COUNT(`referer_checksum`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` <= {$timeStart2} AND `referer_checksum` = {$daterange1['referer_checksum']}
					GROUP BY `referer_checksum`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";
				$query_new = $this->query($sql_new);
				if (mysql_num_rows($query_new) == 0)
					$diff = "NEW";
				else
					$diff = "HITS";

				$referer_data[] = array($diff, 
					$daterange1["total"], 
					false,
					$daterange1["referer"],
					$daterange1["referer_checksum"],
					$daterange1["resource_title"],
					$daterange1["resource"]
					);
			} else {
				/* Time to analyze ****************************************************/
				$daterange2 = mysql_fetch_array($query_temp);

				$hits_1 = $daterange1["total"];
				$hits_2 = ($daterange2["total_temp"]/$this->prefs['compare_timeframe']);
				$diff = (($hits_1/$hits_2)*100)-100;

				$referer_data[] = array($diff, 
					$daterange1["total"],
					$daterange2["total_temp"],
					$daterange1["referer"],
					$daterange1["referer_checksum"],
					$daterange1["resource_title"],
					$daterange1["resource"]
					);
				}
		}

		return $referer_data;
	}


	/***********************************************************************************
	Work Horse - Searches
	***********************************************************************************/

	function getSearchData($view = 'active')
	{
		$prefs = $this->prefs;
		$search_data = array();

		$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;

        $sql = "SELECT `resource`, `resource_title`, `referer`, `search_terms`, COUNT(`search_terms`) as `total`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1} AND `search_terms` != ''
				GROUP BY `search_terms`
				ORDER BY `total` DESC, `dt` DESC
				LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";

		$query = $this->query($sql); 

		/* Now find it in the previous time segment ***********************************/
		while ($daterange1 = mysql_fetch_array($query))
		{

			$term = $daterange1["search_terms"];
			$term = mysql_real_escape_string($term); 
			$sql_temp = "SELECT `referer`, `search_terms`, COUNT(`search_terms`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` > {$timeStart2} AND `dt` <= {$timeStop2} AND `search_terms` = '{$term}'
					GROUP BY `search_terms`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";

			$query_temp = $this->query($sql_temp);

			if (mysql_num_rows($query_temp) == 0) {
				/* Search is New! *******************************************************/
				$sql_new = "SELECT `referer`, `search_terms`, COUNT(`search_terms`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` <= {$timeStart2} AND `search_terms` = '{$term}'
					GROUP BY `search_terms`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";
				$query_new = $this->query($sql_new);
				if (mysql_num_rows($query_new) == 0)
					$diff = "NEW";
				else
					$diff = "HITS";

				$search_data[] = array($diff, 
					$daterange1["total"], 
					false,
					$daterange1["search_terms"],
					$daterange1["referer"],
					$daterange1["resource_title"],
					$daterange1["resource"]
					);
			} else {
				/* Time to analyze ****************************************************/
				$daterange2 = mysql_fetch_array($query_temp);

				$hits_1 = $daterange1["total"];
				$hits_2 = ($daterange2["total_temp"]/$this->prefs['compare_timeframe']);
				$diff = (($hits_1/$hits_2)*100)-100;

				$search_data[] = array($diff, 
					$daterange1["total"],
					$daterange2["total_temp"],
					$daterange1["search_terms"],
					$daterange1["referer"],
					$daterange1["resource_title"],
					$daterange1["resource"]
					);
				}
		}

		return $search_data;
	}



/***********************************************************************************
	Work Horse - Quick View
	***********************************************************************************/

	function get_QuickView($view = 'active')
	{

		$prefs = $this->prefs;
		$quick_data = array();

		$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		$timeStop1 = time();

		/* Time Frame Direct */
        $time_direct_sql = "SELECT COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1} AND `referer_is_local` = -1
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$time_direct_query = $this->query($time_direct_sql);
		$time_direct_fetch = mysql_fetch_array($time_direct_query);
		$time_direct = $time_direct_fetch["total"];

		/* Time Frame Referrers */
        $time_referrers_sql = "SELECT `search_terms`, COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1} AND `referer_is_local` = 0 AND `search_terms` = ''
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$time_referrers_query = $this->query($time_referrers_sql);
		$time_referrers_fetch = mysql_fetch_array($time_referrers_query);
		$time_referrers = $time_referrers_fetch["total"];

		/* Time Frame Searches */
        $time_searches_sql = "SELECT `search_terms`, COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1} AND `referer_is_local` = 0 AND `search_terms` != ''
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$time_searches_query = $this->query($time_searches_sql);
		$time_searches_fetch = mysql_fetch_array($time_searches_query);
		$time_searches = $time_searches_fetch["total"];

		/* It's Calculating Time */
		$time_total = $time_direct+$time_referrers+$time_searches;
		$time_direct_total = ($time_direct/$time_total)*100;
		$time_referrers_total = ($time_referrers/$time_total)*100;
		$time_searches_total = ($time_searches/$time_total)*100;

		/* Direct */
        $direct_sql = "SELECT COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `referer_is_local` = -1
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$direct_query = $this->query($direct_sql);
		$direct_fetch = mysql_fetch_array($direct_query);
		$direct = $direct_fetch["total"];

		/* Referrers */
        $referrers_sql = "SELECT `search_terms`, COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `referer_is_local` = 0 AND `search_terms` = ''
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$referrers_query = $this->query($referrers_sql);
		$referrers_fetch = mysql_fetch_array($referrers_query);
		$referrers = $referrers_fetch["total"];

		/* Searches */
        $searches_sql = "SELECT `search_terms`, COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `referer_is_local` = 0 AND `search_terms` != ''
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$searches_query = $this->query($searches_sql);
		$searches_fetch = mysql_fetch_array($searches_query);
		$searches = $searches_fetch["total"];

		/* It's Calculating Time */
		$total = $direct+$referrers+$searches;
		$direct_total = ($direct/$total)*100;
		$referrers_total = ($referrers/$total)*100;
		$searches_total = ($searches/$total)*100;

		$quick_data[] = array(
			$time_direct,
			$time_direct_total,
			$time_referrers,
			$time_referrers_total,
			$time_searches,
			$time_searches_total,
			$direct,
			$direct_total,
			$referrers,
			$referrers_total,
			$searches,
			$searches_total
			);
		
		return $quick_data;

    }


/*****************************************************************************************************
******************************************************************************************************
******************************************************************************************************
*****************************************************************************************************/	

	/**************************************************************************
	Most Popular 
	**************************************************************************/
	function getHTML_MostPopular()
	{
		$html = '';

		$prefs = $this->prefs;
		$performance_data = $this->getPerformanceData('active'); //grab the data from the db and get an array

		$day1 = $this->prefs['compare_days'];
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";
		
		
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>"Comparing $day1 to Previous $day2",'class'=>'stacked-rows'),
			array('value'=>'Hits','class'=>'stacked-rows'),
			array('value'=>'% +/-','class'=>'stacked-rows')
			);

		/* Display Results ****************************************************************/

			foreach($performance_data as $performance) { 

					$diff = $performance[0];
					$hits_new = $performance[1];
					$hits_old = $performance[2];
				   	$title = $performance[3];
					$address = $performance[4];
					$page = $performance[5];

					$res_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$address);
					$res_html = "<a href=\"$address\">$res_title</a>";

					$hits = $hits_new;

				
				if ($hits_old == false){
					
					$tableData['hasFolders'] = true;
					$img = " ";
					if ($diff == "NEW")
						$diff_text = "<div style='text-align:left;'>New!</div>";
					else
						$diff_text = "<div style='text-align:left;'>No Hits</div>";

					$tableData['tbody'][] = array(
						$res_html,
						"$hits_new",
						"$img $diff_text",
						'folderargs' => array
							(
								'action'=>'getpagehistory',
								'page'=>$page,
								'diff'=>$diff,
								'column'  =>"resource_checksum"
							)
						);
				
				} elseif ($diff < 0) {
					$tableData['hasFolders'] = true;
				   	$img = "<img src='pepper/brettdewoody/trends/down.gif'>";
					$diff = abs(round($diff,0))."%";

					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'	=>'getpagehistory',
								'page'	=>$page,
								'column'  =>"resource_checksum"
							)
						);

				} elseif ($diff > 0) {
					$tableData['hasFolders'] = true;
				   	$img = "<img src='pepper/brettdewoody/trends/up.gif'>";
					$diff = round($diff,0)."%";

					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'=>'getpagehistory',
								'page'=>$page,
								'column'  =>"resource_checksum"
							)
						);

				} else {
					$tableData['hasFolders'] = true;
				   	$img = "<img src='pepper/brettdewoody/trends/up.gif'>";
					$diff = round($diff,0)."%";

					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'=>'getpagehistory',
								'page'=>$page,
								'column'  =>"resource_checksum"
							)
						);
				}

				}

		$html = $this->Mint->generateTable($tableData);
		return $html;
	}


	/**************************************************************************
	Best
	**************************************************************************/
	function getHTML_Best()
	{
		$html = '';
		$prefs = $this->prefs;
		$best_data = $this->getBestData('active'); //grab the data from the db and get an array

		$day1 = $this->prefs['compare_days'];
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";

		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>"Comparing $day1 to Previous $day2",'class'=>'stacked-rows'),
			array('value'=>'Hits','class'=>'stacked-rows'),
			array('value'=>'% +/-','class'=>'stacked-rows')
			);

		/* Display Results ****************************************************************/

				if (count($best_data) == 0) {
					$tableData['tbody'][] = array(
						"There are no pages in the past ".strtolower($day1)." performing better than the average for the previous ".strtolower($day2).".","",""
						);

				} else {

				foreach($best_data as $best) {

				   		$diff = $best[0];
						$hits_new = $best[1];
						$hits_old = $best[2];
				   		$title = $best[3];
						$address = $best[4];
						$page = $best[5];

						$res_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$address);
						$res_html = "<a href=\"$address\">$res_title</a>";

						$hits = $hits_new;
					
					$tableData['hasFolders'] = true;
					$img = "<img src='pepper/brettdewoody/trends/up.gif'>";
					$diff = round($diff,0)."%";

					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'=>'getpagehistory',
								'page'=>$page,
								'column'=>"resource_checksum"
							)
						);

				} 

				}

		$html = $this->Mint->generateTable($tableData);
		return $html;
	}	


	/**************************************************************************
	Worst
	**************************************************************************/
	function getHTML_Worst()
	{
		$html = '';
		$prefs = $this->prefs;
		$worst_data = $this->getWorstData('active'); //grab the data from the db and get an array

		$day1 = $this->prefs['compare_days'];
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";

		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>"Comparing $day1 to Previous $day2",'class'=>'stacked-rows'),
			array('value'=>'Hits','class'=>'stacked-rows'),
			array('value'=>'% +/-','class'=>'stacked-rows')
			);

		/* Display Results ****************************************************************/

				if (count($worst_data) == 0){
					$tableData['tbody'][] = array(
						"Congratulations!<br><br>All your pages with hits in the past ".strtolower($day1)." are performing above the ".strtolower($day2)." average.<br /><br />Please check the Best pane to see how well they're performing.","",""
						);

				} else {

				foreach($worst_data as $worst) {

				   		$diff = $worst[0];
						$hits_new = $worst[1];
						$hits_old = $worst[2];
						$title = $worst[3];
						$address = $worst[4];
						$page = $worst[5];

						$res_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$address);
						$res_html = "<a href=\"$address\">$res_title</a>";

						$hits = $hits_new;

					$tableData['hasFolders'] = true;
				   	$img = "<img src='pepper/brettdewoody/trends/down.gif'>";
					$diff = abs(round($diff,0))."%";

					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'=>'getpagehistory',
								'page'=>$page,
								'column'=>"resource_checksum"
							)
						);

				}

				}

		$html = $this->Mint->generateTable($tableData);
		return $html;
	}	


	/**************************************************************************
	New
	**************************************************************************/
	function getHTML_New()
	{

		$html = '';
		$prefs = $this->prefs;
		$new_data = $this->get_New('active'); 
		
		$day1 = $this->prefs['compare_days'];
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";


		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>"New Pages in the Past $day1",'class'=>'stacked-rows'),
			array('value'=>'Hits (Previous)','class'=>'')
			);

		/* Display Results ****************************************************************/
		
		if (count($new_data) == 0){
			$tableData['tbody'][] = array(
			"There were no new pages viewed in the past ".strtolower($day1).".",""
			);
		} else {
		foreach($new_data as $new) {

				$hits = $new[0];
				$title = $new[1];
				$address = $new[2];
				$date = date('m/d/y - g:i a', $new[3]);
				$address_display = $this->Mint->abbr($address);

				$res_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$address);
				$res_html = "<a href=\"$address\">$res_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>First hit on $date</span>":'');

			
			$tableData['tbody'][] = array(
						$res_html,
						"<div style=\"float: right;\">".$hits." (0)</div>"
						);
		}
		}
	
		$html = $this->Mint->generateTable($tableData);
		return $html;

	}


    /**************************************************************************
	Old
	**************************************************************************/
	function getHTML_Old()
	{

		$html = '';
		$prefs = $this->prefs;
		$old_data = $this->get_Old('active'); 
		
		$day1 = $this->prefs['compare_days'];
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";


		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>"Old Pages in the Past $day1",'class'=>'stacked-rows'),
			array('value'=>'Hits (Previous)','class'=>'')
			);

		/* Display Results ****************************************************************/
		
		if (count($old_data) == 0) {
			$tableData['tbody'][] = array(
			"There were no unviewed pages in the past ".strtolower($day1)."!",""
			);
		} else {
		foreach($old_data as $old) {

				$hits = $old[0];
				$title = $old[1];
				$address = $old[2];
				$date = date('m/d/y - g:i a', $old[3]);
				$address_display = $this->Mint->abbr($address);

				$res_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$address);
				$res_html = "<a href=\"$address\">$res_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>Last hit on $date</span>":'');

			$tableData['tbody'][] = array
			(
				$res_html,
				"<div style=\"float: right;\">0 (".$hits.")</div>"
			);
		}
		}
	
		$html = $this->Mint->generateTable($tableData);
		return $html;

	}


	/**************************************************************************
	Watched
	**************************************************************************/
	function getHTML_Watched()
	{
	$html = '';

		$prefs = $this->prefs;
		$watched_data = $this->getWatchedData('active'); //grab the data from the db and get an array

		$day1 = $this->prefs['compare_days'];
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";


		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>"Comparing $day1 to Previous $day2",'class'=>'stacked-rows'),
			array('value'=>'Hits','class'=>'stacked-rows'),
			array('value'=>'% +/-','class'=>'stacked-rows')
			);

		/* Display Results ****************************************************************/

		if (count($watched_data) == 0){
			$tableData['tbody'][] = array(
			"You have no Watched Pages or your Watched Pages have not received hits in the last $day1.","",""
			);
		} else {
			foreach($watched_data as $watched) {

					$diff = $watched[0];
					$hits_new = $watched[1];
					$hits_old = $watched[2];
				   	$title = $watched[3];
					$address = $watched[4];
					$page = $watched[5];

					$res_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$address);
					$res_html = "<a href=\"$address\">$res_title</a>";

					$hits = $hits_new;

				
				if ($hits_old == false){
					
					$tableData['hasFolders'] = true;
					$img = " ";
					if ($diff == "NEW")
						$diff_text = "<div style='text-align:left;'>New!</div>";
					else
						$diff_text = "<div style='text-align:left;'>No Hits</div>";

					$tableData['tbody'][] = array(
						$res_html,
						"$hits_new",
						"$img $diff_text",
						'folderargs' => array
							(
								'action'=>'getpagehistory',
								'page'=>$page,
								'diff'=>$diff,
								'column'=>"resource_checksum"
							)
						);
				
				} elseif ($diff < 0) {
					$tableData['hasFolders'] = true;
				   	$img = "<img src='pepper/brettdewoody/trends/down.gif'>";
					$diff = abs(round($diff,0))."%";

					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'=>'getpagehistory',
								'page'=>$page,
								'column'=>"resource_checksum"
							)
						);

				} elseif ($diff > 0) {
					$tableData['hasFolders'] = true;
				   	$img = "<img src='pepper/brettdewoody/trends/up.gif'>";
					$diff = round($diff,0)."%";

					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'=>'getpagehistory',
								'page'=>$page,
								'column'=>"resource_checksum"
							)
						);

				} else {
					$tableData['hasFolders'] = true;
				   	$img = "<img src='pepper/brettdewoody/trends/up.gif'>";
					$diff = round($diff,0)."%";

					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'=>'getpagehistory',
								'page'=>$page,
								'column'=>"resource_checksum"
							)
						);
				}

			}

		}

		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	
	/**************************************************************************
	Quick View
	**************************************************************************/
	function getHTML_QuickView()
	{
	$html = '';

		$prefs = $this->prefs;
		$quick_data = $this->get_QuickView('active'); //grab the data from the db and get an array

		$day1 = $this->prefs['compare_days'];
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";


		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>"Site Visits",'class'=>'stacked-rows'),
			array('value'=>'Hits','class'=>'stacked-rows'),
			array('value'=>'Total','class'=>'stacked-rows')
			);

		/* Display Results ****************************************************************/

		foreach ($quick_data as $data) {
			$tableData['tbody'][] = array(
				"Direct (Past $day1)",
				$data[0],
				abs(round($data[1],0))."%"
				);
			$tableData['tbody'][] = array(
				"Referrers (Past $day1)",
				$data[2],
				abs(round($data[3],0))."%"
				);
			$tableData['tbody'][] = array(
				"Searches (Past $day1)",
				$data[4],
				abs(round($data[5],0))."%"
				);
			/* Separate Past and All Time */
			$tableData['tbody'][] = array(
				"<br />","",""
			);
			$tableData['tbody'][] = array(
				"Direct (All Time)",
				$data[6],
				abs(round($data[7],0))."%"
				);
			$tableData['tbody'][] = array(
				"Referrers (All Time)",
				$data[8],
				abs(round($data[9],0))."%"
				);
			$tableData['tbody'][] = array(
				"Searches (All Time)",
				$data[10],
				abs(round($data[11],0))."%"
				);
		}

		$html = $this->Mint->generateTable($tableData);
		return $html;
	}



	/**************************************************************************
	Referrers
	**************************************************************************/
	function getHTML_Referrers()
	{
		$html = '';

		$prefs = $this->prefs;
		$referer_data = $this->getRefererData('active'); //grab the data from the db and get an array

		$day1 = $this->prefs['compare_days'];
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";
		
		
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>"Comparing $day1 to Previous $day2",'class'=>'stacked-rows'),
			array('value'=>'Hits','class'=>''),
			array('value'=>'% +/-','class'=>'')
			);

		/* Display Results ****************************************************************/

			foreach($referer_data as $referer) { 

					$diff = $referer[0];
					$hits_new = $referer[1];
					$hits_old = $referer[2];
					$address = $referer[3];
					$checksum = $referer[4];
					$title = $referer[5];
					$title_address = $referer[6];

					$res_title = $this->Mint->abbr($address);
					$res2_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$title_address);
					$res_html = "<a href=\"$address\">$res_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>To <a href=\"$title_address\">$res2_title</a></span>":'');

					$hits = $hits_new;

				
				if ($hits_old == false){
					
					$img = " ";
					if ($diff == "NEW")
						$diff_text = "<div style='text-align:left;'>New!</div>";
					else
						$diff_text = "<div style='text-align:left;'>No Hits</div>";
					$tableData['hasFolders'] = true;
					
					$tableData['tbody'][] = array(
						$res_html,
						"$hits_new",
						"$img $diff_text",
						'folderargs' => array
							(
								'action'	=>'getpagehistory',
								'page'	=>$checksum,
								'diff'=>$diff,
								'column'  =>'referer_checksum'
							)
						);
				
				} elseif ($diff < 0) {
				   	$img = "<img src='pepper/brettdewoody/trends/down.gif'>";
					$diff = abs(round($diff,0))."%";
					$tableData['hasFolders'] = true;
					
					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'	=>'getpagehistory',
								'page'	=>$checksum,
								'column'  =>"referer_checksum"
							)
						);

				} elseif ($diff > 0) {
				   	$img = "<img src='pepper/brettdewoody/trends/up.gif'>";
					$diff = round($diff,0)."%";
					$tableData['hasFolders'] = true;
					
					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'	=>'getpagehistory',
								'page'	=>$checksum,
								'column'  =>"referer_checksum"
							)
						);

				} else {
				   	$img = "<img src='pepper/brettdewoody/trends/up.gif'>";
					$diff = round($diff,0)."%";
					$tableData['hasFolders'] = true;
					
					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'	=>'getpagehistory',
								'page'	=>$checksum,
								'column'  =>"referer_checksum"
							)
						);
				}

				}

		$html = $this->Mint->generateTable($tableData);
		return $html;
	}


	/**************************************************************************
	Searches
	**************************************************************************/
	function getHTML_Searches()
	{
		$html = '';

		$prefs = $this->prefs;
		$search_data = $this->getSearchData('active'); //grab the data from the db and get an array

		$day1 = $this->prefs['compare_days'];
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";
		
		
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>"Comparing $day1 to Previous $day2",'class'=>''),
			array('value'=>'Hits','class'=>'stacked-rows'),
			array('value'=>'% +/-','class'=>'')
			);

		/* Display Results ****************************************************************/

			foreach($search_data as $search) { 

					$diff = $search[0];
					$hits_new = $search[1];
					$hits_old = $search[2];
					$term = $search[3];
					$address = $search[4];
					$title = $search[5];
					$title_address = $search[6];

					$res_title = $this->Mint->abbr(stripslashes($term));
					$res2_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$title_address);
					$res_html = "<a href=\"$address\">$res_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>Found <a href=\"$title_address\">$res2_title</a></span>":'');

					$hits = $hits_new;

				
				if ($hits_old == false){
					
					$img = " ";
					if ($diff == "NEW")
						$diff_text = "<div style='text-align:left;'>New!</div>";
					else
						$diff_text = "<div style='text-align:left;'>No Hits</div>";
					$tableData['hasFolders'] = true;
					
					$tableData['tbody'][] = array(
						$res_html,
						"$hits_new",
						"$img $diff_text",
						'folderargs' => array
							(
								'action'	=>'getpagehistory',
								'page'	=>$term,
								'diff'=>$diff,
								'column'  =>"search_terms"
							)
						);
				
				} elseif ($diff < 0) {
				   	$img = "<img src='pepper/brettdewoody/trends/down.gif'>";
					$diff = abs(round($diff,0))."%";
					$tableData['hasFolders'] = true;
					
					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'	=>'getpagehistory',
								'page'	=>$term,
								'column'  =>"search_terms"
							)
						);

				} elseif ($diff > 0) {
				   	$img = "<img src='pepper/brettdewoody/trends/up.gif'>";
					$diff = round($diff,0)."%";
					$tableData['hasFolders'] = true;
					
					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'	=>'getpagehistory',
								'page'	=>$term,
								'column'  =>"search_terms"
							)
						);

				} else {
				   	$img = "<img src='pepper/brettdewoody/trends/up.gif'>";
					$diff = round($diff,0)."%";
					$tableData['hasFolders'] = true;
					
					$tableData['tbody'][] = array(
						$res_html,
						"$hits",
						"<div style='text-align:left;'>$img $diff</div>",
						'folderargs' => array
							(
								'action'	=>'getpagehistory',
								'page'	=>$term,
								'column'  =>"search_terms"
							)
						);
				}

				}

		$html = $this->Mint->generateTable($tableData);
		return $html;
	}


}

?>