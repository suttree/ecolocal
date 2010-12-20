<?php
/******************************************************************************
 Link Spice
 
 Developer		: Craig Mod
 Plug-in Name	: Link Spice
 Version		: 2.0
 
 August/Sept 2006, May 2007
 
 http://craigmod.com/interactive/link_spice
 
 ******************************************************************************/
 
 $installPepper = "CM_LinkSpice";
	
class CM_LinkSpice extends Pepper
{
	var $version	= 200; 
	var $info		= array
	(
		'pepperName'	=> 'Link Spice',
		'pepperUrl'		=> 'http://www.craigmod.com/interactive/link_spice/',
		'pepperDesc'	=> 'Track campaigns and keyword results from google and yahoo advertising.',
		'developerName'	=> 'Craig Mod',
		'developerUrl'	=> 'http://craigmod.com/'
	);
	var $panes = array
	(
		'Link Spice' => array
		(
			'Campaigns',
			'Keywords',
			'Pages'
		)
	);
	var $prefs = array
	(
		'linkSpicePeriod' => 365
	);
	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		if ($this->Mint->version >= 200)
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
				'explanation'	=> '<p>This Pepper is only compatible with Mint 2.0 and higher.</p>'
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
			/* Link Spice ***************************************************/
			case 'Link Spice': 
				switch($tab)
				{
					/* Campaigns ************************************************/
					case 'Campaigns':
						$html .= $this->getHTML_Campaigns();
						break;

					/* Keywords ************************************************/
					case 'Keywords':
						$html .= $this->getHTML_Keywords();
						break;	
						
					/* Pages ************************************************/
					case 'Pages':
						$html .= $this->getHTML_Pages();
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
		$defaultGroups = get_class_vars('CM_LinkSpice');
		
		/* Global *************************************************************/
		$preferences['linkSpice']	= <<<HERE
<table>
	<tr>
		<th>Track ads for the following number of days: </th>
	</tr>
	<tr>
		<td><span><input type="text" id="linkSpicePeriod" name="linkSpicePeriod" value="{$this->prefs['linkSpicePeriod']}"></span></td>
	</tr>
	<tr>
		<td>Default is 365 days.</td>
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
		$this->prefs['linkSpicePeriod']	= $this->escapeSQL($_POST['linkSpicePeriod']);
	}

	/**************************************************************************
	 onCustom() 
	 **************************************************************************/
	function onCustom() 
	{
		if (isset($_POST['action']) && $_POST['action']=='getRelatedCampaigns' && isset($_POST['pageRef'])) {
			$pageRef = $this->Mint->escapeSQL($_POST['pageRef']);
			echo $this->getHTML_relatedCampaignsAndKeys($pageRef);
			}
		// For pulling pages related to some campaign or keyword(s)
		else if (isset($_POST['action']) && $_POST['action']=='getPages' && isset($_POST['type'])) {
			$token = $this->Mint->escapeSQL($_POST['token']);
			$type = $this->Mint->escapeSQL($_POST['type']);
			echo $this->getHTML_relatedPages($token, $type);
			}
	}

	/**************************************************************************
	 getHTML_Campaigns()
	 **************************************************************************/
	function getHTML_Campaigns()
	{
		$html = '';

		$tableData['hasFolders'] = true;
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array
		(
			array('value'=>'Campaign Name', 'class'=>'focus'),
			array('value'=>'Clicks', 'class'=>'sort'),
			array('value'=>'%', 'class'=>'sort')			
		);
		
		// Time offset calcs brazenly gleened from Brett DeWoody's Trends pepper		
		$timeStart1 = time() - ($this->prefs['linkSpicePeriod'] * 24 * 60 * 60);
		$timeStop1 = time();
		
		$query = "SELECT * 
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `resource` LIKE '%utm_campaign=%'
					AND `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
					ORDER BY `dt` DESC";

		$totalCount = 0;
		$gotCampaign = false;
		
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result))
			{
				$gotCampaign = true;
				// Pull out the ad campaign reference
				preg_match('/utm_campaign=([^&$]*)/i', $r['resource'], $cMatch);
				// Replace any %2b spaces
				$cMatch = preg_replace('/%2b/i', ' ', $cMatch);
				
				$campaigns[$cMatch[1]]++;
				$totalCount++;
			}
		}
	
		if ($gotCampaign) {
			arsort($campaigns);
			foreach ($campaigns as $campaign => $count) 
			{
				$tableData['tbody'][] = array 
				(
					$campaign,
					$count,
					$this->Mint->formatPercents((($count/$totalCount)*100)),
					'folderargs'=>array(
						'action'=>'getPages',
						'token'=>$campaign,
						'type'=>'campaign'
						)
				);
			}
		}
		else {
			$tableData['hasFolders'] = false;
			$tableData['tbody'][] = array
			(
				"No campaigns found in last ".$this->prefs['linkSpicePeriod']." days.",
				"",
				""
			);
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_Keywords()
	 **************************************************************************/
	function getHTML_Keywords()
	{ 
		$html = '';
		
		$tableData['hasFolders'] = true;
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array
		(
			array('value'=>'Keyword Phrase', 'class'=>'focus'),
			array('value'=>'Clicks', 'class'=>'sort'),
			array('value'=>'%', 'class'=>'sort')
		);

		// Time offset calcs brazenly gleened from Brett DeWoody's Trends pepper		
		$timeStart1 = time() - ($this->prefs['linkSpicePeriod'] * 24 * 60 * 60);
		$timeStop1 = time();
		
		$query = "SELECT * 
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `resource` LIKE '%utm_term=%'
					AND `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
					ORDER BY `dt` DESC";
		
		$gotKeyword = false;
		$totalCount = 0;
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result))
			{
				$gotKeyword = true;
				// Pull out the ad terms / keywords
				preg_match('/utm_term=([^&$]*)/i', $r['resource'], $kMatch);
				// Replace any %2b spaces
				$kMatch = preg_replace('/%2b/i', ' ', $kMatch);
				
				$keywords[$kMatch[1]]++;
				$totalCount++;
			}
		}
	
		if ($gotKeyword) {
			arsort($keywords);
			foreach ($keywords as $keyword => $count) 
			{
				$tableData['tbody'][] = array 
				(
					$keyword,
					$count,
					$this->Mint->formatPercents((($count/$totalCount)*100)),
					'folderargs'=>array(
						'action'=>'getPages',
						'token'=>$keyword,
						'type'=>'keyword'
						)
				);
			}
		}
		else {
			$tableData['hasFolders'] = false;
			$tableData['tbody'][] = array
			(
				"No keywords found in last ".$this->prefs['linkSpicePeriod']." days.",
				"",
				""
			);
		}
		
		$html = $this->Mint->generateTable($tableData);
		
		return $html;
	
	}
	
	/**************************************************************************
	 getHTML_Pages()
	 **************************************************************************/
	function getHTML_Pages()
	{ 
		$html = '';

		$tableData['hasFolders'] = true;
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array
		(
			array('value'=>'Page Title', 'class'=>'focus'),
			array('value'=>'Clicks', 'class'=>'sort'),
			array('value'=>'%', 'class'=>'sort')
		);
				
		// Time offset calcs brazenly gleened from Brett DeWoody's Trends pepper
		$timeStart1 = time() - ($this->prefs['linkSpicePeriod'] * 24 * 60 * 60);
		$timeStop1 = time();
		
		$query = "SELECT * 
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE (`resource` LIKE '%utm_term=%' OR `resource` LIKE '%utm_campaign=%')
					AND `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
					ORDER BY `dt` DESC";

		$gotPages = false;
		$totalCount = 0;
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result))
			{
				$gotPages = true;
				// Pull out the URL minus the campaign data
				preg_match('/(http:\/\/([^?]*))/i', $r['resource'], $uMatch);
				
				//$r['resource_title'] = $this->Mint->abbr($r['resource_title']);
				$pages[$r['resource_title']]['count']++;
				$pages[$r['resource_title']]['link']=$r['resource'];
				$pages[$r['resource_title']]['directLink'] = $uMatch[1];
				$pages[$r['resource_title']]['genURL'] = $uMatch[2];
				$pages[$r['resource_title']]['checksum']=$r['resource_checksum'];
				
				$totalCount++;
				
				// Debugging
				/*$tableData['tbody'][] = array 
				(
					//$this->Mint->observe($r['resource_title'])
					$r['resource_title']
				);*/
			}
		}
	
		if ($gotPages) {
			arsort($pages);
			foreach ($pages as $page => $details) 
			{
				$tableData['tbody'][] = array 
				(
					$page."<br /><span>URL: <a href=\"".$details['directLink']."\" target=\"_blank\">".$details['genURL']."</a></span>",
					$details['count'],
					$this->Mint->formatPercents((($details['count']/$totalCount)*100)),
					'folderargs'=>array(
						'action'=>'getRelatedCampaigns',
						'pageRef'=>urlencode($page)
						)
				);
			}
		}
		else {
			$tableData['hasFolders'] = false;
			$tableData['tbody'][] = array
			(
				"No pages found in last ".$this->prefs['linkSpicePeriod']." days.",
				"",
				""
			);
		}
		$html = $this->Mint->generateTable($tableData);
		
		return $html;
	
	}
	
	
	/**************************************************************************
	 getHTML_relatedCampaignsAndKeys($pageRef)
	 **************************************************************************/
	function getHTML_relatedCampaignsAndKeys($pageRef) 
	{
	
		$html = '';

		// Time offset calcs brazenly gleened from Brett DeWoody's Trends pepper		
		$timeStart1 = time() - ($this->prefs['linkSpicePeriod'] * 24 * 60 * 60);
		$timeStop1 = time();
		
		/* ------------------------------------------------
			Search for relevant Campaigns
		---------------------------------------------------*/
		urldecode($pageRef);
		$query = "SELECT * 
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `resource_title` = '$pageRef'
					AND `resource` LIKE '%utm_campaign=%'
					AND `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
					ORDER BY `dt` DESC";

		$foundCampaigns = false;
		$totalCount = 0;
		
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result))
			{
				$foundCampaigns = true;
				// Pull out the ad campaign reference
				preg_match('/utm_campaign=([^&$]*)/i', $r['resource'], $cMatch);

				// Replace any %2b spaces
				$cMatch = preg_replace('/%2b/i', ' ', $cMatch);
				
				$campaigns[$cMatch[1]]++;
				
				$totalCount++;
			}
		}
		
		$tableData['classes'] = array
		(
			'focus',
			'sort',
			'sort'
		);

		
		// If this page has any campaigns associated with it, build the proper HTML table
		if($foundCampaigns) {
			arsort($campaigns);
			foreach ($campaigns as $campaign => $count) 
			{
				$tableData['tbody'][] = array 
				(
					$campaign,
					$count,
					$this->Mint->formatPercents((($count/$totalCount)*100))
				);
			}
			$html .= "\t\t<tr>\r
					\t\t\t<td style=\"background-color: #6e6e6e; color: #fff; padding-left: 22px; padding-bottom: 2px; font-size: .9em; border-top: 3px solid #595959;\">Campaigns</td>\r
					\t\t\t<td style=\"background-color: #6e6e6e; color: #fff; padding-bottom: 2px; font-size: .9em; border-top: 3px solid #595959;\">Clicks</td>\r
					\t\t\t<td style=\"background-color: #6e6e6e; color: #fff; padding-bottom: 2px; font-size: .9em; border-top: 3px solid #595959;\">%</td>\r
				\t\t</tr>\r";
			$html .= $this->Mint->generateTableRows($tableData);
		}		

		/* ------------------------------------------------
			Search for relevant Keywords
		---------------------------------------------------*/
		unset($tableData);
		$query = "SELECT * 
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `resource_title` = '$pageRef'
					AND `resource` LIKE '%utm_term=%'
					AND `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
					ORDER BY `dt` DESC";
					//LIMIT {$this->Mint->cfg['preferences']['rows']}";					

		$foundKeywords = false;
		$totalCount = 0;
		if ($result = $this->query($query)) 
		{
			
			while ($r = mysql_fetch_array($result))
			{
				$foundKeywords = true;
	
				// Pull out the ad campaign reference
				preg_match('/utm_term=([^&$]*)/i', $r['resource'], $cMatch);

				// Replace any %2b spaces
				$cMatch = preg_replace('/%2b/i', ' ', $cMatch);
				
				$keywords[$cMatch[1]]++;
				
				$totalCount++;
			}
		}
		
		$tableData['classes'] = array
		(
			'focus',
			'sort',
			'sort'
		);
		
		// If this page has any keywords associated with it, build the appropriate table
		if($foundKeywords) {
			arsort($keywords);
			foreach ($keywords as $keyword => $count) 
			{
				$tableData['tbody'][] = array 
				(
					$keyword,
					$count,
					$this->Mint->formatPercents((($count/$totalCount)*100))
				);
			}
			
			$html .= "\t\t<tr>\r
						\t\t\t<td style=\"background-color: #6e6e6e; color: #fff; padding-left: 22px; padding-bottom: 2px; font-size: .9em; border-top: 3px solid #595959;\">Keywords</td>\r
						\t\t\t<td style=\"background-color: #6e6e6e; color: #fff; padding-bottom: 2px; font-size: .9em; border-top: 3px solid #595959;\">Clicks</td>\r
						\t\t\t<td style=\"background-color: #6e6e6e; color: #fff; padding-bottom: 2px; font-size: .9em; border-top: 3px solid #595959;\">%</td>\r
					\t\t</tr>\r";
			
			$html .= $this->Mint->generateTableRows($tableData);
		}
		

		return $html;
	}
	
	/**************************************************************************
	 getHTML_relatedPages($campaignName, $type)
	 **************************************************************************/
	function getHTML_relatedPages($token, $type) 
	{
		$html = '';
	
		// Insert %2b encoding back into token name spaces
		$token = preg_replace('/\s/i', '%2b', $token);

		// Time offset calcs brazenly gleened from Brett DeWoody's Trends pepper
		$timeStart1 = time() - ($this->prefs['linkSpicePeriod'] * 24 * 60 * 60);
		$timeStop1 = time();
		
		// Are we searching for campaigns or keywords?		
		switch($type) {
			case "campaign":
				$searchString = "%utm_campaign=".$token;
				break;
			case "keyword":
				$searchString = "%utm_term=".$token;
				break;
			}
		
		$query = "SELECT * 
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE (`resource` LIKE '$searchString&%' OR `resource` LIKE '$searchString')
					AND `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
					ORDER BY `dt` DESC";


		$totalCount = 0;
		
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result))
			{
				// Pull out the url w/ and w/o http://
				preg_match('/(http:\/\/([^?]*))/i', $r['resource'], $uMatch);
		
				$pages[$uMatch[2]]['count']++;
				$pages[$uMatch[2]]['URL'] = $uMatch[1];
				$pages[$uMatch[2]]['title'] = $r['resource_title'];
				$pages[$uMatch[2]]['resource'] = $r['resource'];
				$totalCount++;
			}
		}
		
		$tableData['classes'] = array
			(
				'focus',
				'sort',
				'sort'
			);
		
		arsort($pages);
		foreach ($pages as $page => $details) 
		{
			$tableData['tbody'][] = array 
			(
				"<a href=\"".$details['URL']."\" target=\"_blank\">".$details['title']."</a><br /><span>URL: ".$details['URL']."</span>",
				$details['count'],
				$this->Mint->formatPercents((($details['count']/$totalCount)*100))
			);
		}
		
		
		$html .= "\t\t<tr>\r
					\t\t\t<td style=\"background-color: #6e6e6e; color: #fff; padding-left: 10px; padding-bottom: 2px; font-size: .9em; border-top: 3px solid #595959;\">Page Title + URL</td>\r
					\t\t\t<td style=\"background-color: #6e6e6e; color: #fff; padding-bottom: 2px; font-size: .9em; border-top: 3px solid #595959;\">Clicks</td>\r
					\t\t\t<td style=\"background-color: #6e6e6e; color: #fff; padding-bottom: 2px; font-size: .9em; border-top: 3px solid #595959;\">%</td>\r
				\t\t</tr>\r";
		
		$html .= $this->Mint->generateTableRows($tableData);
		//$html = "siojdsd";
		return $html;
	
	}
}