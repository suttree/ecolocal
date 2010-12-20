<?php
/******************************************************************************
 Pepper
 
 Developer		: Shaun Inman
 Plug-in Name	: User Agent 007
 
 http://www.shauninman.com/

 ******************************************************************************/
if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file
$installPepper = "SI_UserAgent";

class SI_UserAgent extends Pepper
{
	var $version	= 122; 
	var $info		= array
	(
		'pepperName'	=> 'User Agent 007',
		'pepperUrl'		=> 'http://www.haveamint.com/',
		'pepperDesc'	=> 'Mint. Peppermint. User Agent 007 goes undercover to uncover who\'s using which browser on which platform at what resolution with which plug-ins installed.',
		'developerName'	=> 'Shaun Inman',
		'developerUrl'	=> 'http://www.shauninman.com/'
	);
	var $panes		= array
	(
		'User Agent 007'	=> array
		(
			'Browsers',
			'Platform',
			'Resolution',
			'Flash'
		)
	);
	var $manifest	= array
	(
		'visit'	=> array
		(
			'browser_family' 	=> "VARCHAR(255) NOT NULL",
			'browser_version' 	=> "VARCHAR(15) NOT NULL",
			'platform' 			=> "VARCHAR(255) NOT NULL",
			'resolution' 		=> "VARCHAR(13) NOT NULL",
			'flash_version' 	=> "TINYINT(2) NOT NULL"
		)
	);
	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		return array
		(
			'isCompatible'	=> true,
		);
	}
	
	/**************************************************************************
	 onJavaScript()
	 **************************************************************************/
	function onJavaScript() 
	{
		$js = "pepper/shauninman/useragent007/script.js";
		if (file_exists($js))
		{
			include_once($js);
		}
	}
	
	/**************************************************************************
	 onRecord()
	 **************************************************************************/
	function onRecord() 
	{
 		if (empty($_GET)) { return array(); }
 		
 		$ua = $this->parseUserAgent($_SERVER['HTTP_USER_AGENT']);
 		$browser_family 	= $this->Mint->escapeSQL($ua['family']);
 		$browser_version	= $this->Mint->escapeSQL($ua['version']);
 		$platform			= $this->Mint->escapeSQL($ua['platform']);
 		
 		$resolution		= $this->Mint->escapeSQL($_GET['resolution']);
 		$flash_version	= $this->Mint->escapeSQL($_GET['flash_version']);
 		
 		return array
 		(
 			'resolution'		=> $resolution,
 			'flash_version'		=> $flash_version,
 			'browser_family'	=> $browser_family,
 			'browser_version'	=> $browser_version,
 			'platform'			=> $platform
 		);
	}
	
	/**************************************************************************
	 onDisplay()
	 **************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		$html = '';
		
		switch($pane) 
		{
			/* User Agent 007 *************************************************/
			case 'User Agent 007': 
				switch($tab) 
				{
					/* Browsers ***********************************************/
					case 'Browsers':
						$html .= $this->getHTML_Browsers();
					break;
					/* Platform ***********************************************/
					case 'Platform':
						$html .= $this->getHTML_Platform();
					break;
					/* Resolution *********************************************/
					case 'Resolution':
						$html .= $this->getHTML_Resolution();
					break;
					/* Flash **************************************************/
					case 'Flash':
						$html .= $this->getHTML_FlashVersion();
					break;
				}
			break;
		}
		return $html;
	}
	
	/**************************************************************************
	 onCustom()
	 
	 **************************************************************************/
	function onCustom() 
	{
		if 
		(
			isset($_POST['action']) 		&& 
			$_POST['action']=='getversion'	&& 
			isset($_POST['family']) 		&& 
			isset($_POST['total'])
		)
		{
			$family	= $this->escapeSQL($_POST['family']);
			$total	= $this->escapeSQL($_POST['total']);
			echo $this->getHTML_BrowserVersions($family,$total);
		}
	}
		
	
	
	/**************************************************************************
	 getHTML_Browsers()
	 
	 **************************************************************************/
	function getHTML_Browsers() {
		$html = '';
		
		$tableData['hasFolders'] = true;
		
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Browser Family','class'=>''),
			array('value'=>'% of Total','class'=>'')
		);
		
		$query = "SELECT `browser_family`, COUNT(`browser_family`) as `total`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE
					`browser_family`!='' 
					GROUP BY `browser_family` 
					ORDER BY `total` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		$fam	= array();
		$total	= 0;
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$fam[$r['browser_family']] = $r['total'];
				$total += $r['total'];
			}
		}
		foreach ($fam as $family=>$count) {
			$tableData['tbody'][] = array
			(
				$family,
				$this->Mint->formatPercents($count/$total*100),
			
				'folderargs' => array
				(
					'action'	=>'getversion',
					'family'	=>$family,
					'total'		=>$total
				)
			);
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_BrowserVersions()
	 **************************************************************************/
	function getHTML_BrowserVersions($family,$total)
	{
		$html = '';
		
		$query = "SELECT `browser_version`, COUNT(`browser_version`) as `total`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE
					`browser_family`='$family' 
					GROUP BY `browser_version` 
					ORDER BY `total` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		$v = array();
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$tableData['tbody'][] = array
				(
					$r['browser_version'],
					$this->Mint->formatPercents($r['total']/$total*100)
				);
			}
		}
		
		$html = $this->Mint->generateTableRows($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_Platform()
	 **************************************************************************/
	function getHTML_Platform()
	{
		$html = '';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Platform','class'=>''),
			array('value'=>'% of Total','class'=>'')
		);
		
		$query = "SELECT `platform`, COUNT(`platform`) as `total`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE
					`platform`!='' 
					GROUP BY `platform` 
					ORDER BY `total` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		$platforms	= array();
		$total		= 0;
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$platforms[$r['platform']] = $r['total'];
				$total += $r['total'];
			}
		}
		foreach ($platforms as $platform=>$count) {
			$tableData['tbody'][] = array
			(
				$platform,
				$this->Mint->formatPercents($count/$total*100)
			);
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_Resolution()
	 **************************************************************************/
	function getHTML_Resolution() 
	{
		$html = '';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Resolution','class'=>''),
			array('value'=>'% of Total','class'=>'')
		);
		
		$query = "SELECT `resolution`, COUNT(`resolution`) as `total`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE
					`resolution`!='' 
					GROUP BY `resolution` 
					ORDER BY `total` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		$res	= array();
		$total	= 0;
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$res[$r['resolution']] = $r['total'];
				$total += $r['total'];
			}
		}
		foreach ($res as $resolution=>$count) 
		{
			$tableData['tbody'][] = array
			(
				str_replace("x"," &times; ",$resolution),
				$this->Mint->formatPercents($count/$total*100)
			);
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_FlashVersion()
	 **************************************************************************/
	function getHTML_FlashVersion()
	{
		$html = '';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Version Installed','class'=>''),
			array('value'=>'% of Total','class'=>'')
		);
		
		$query = "SELECT `flash_version`, COUNT(`flash_version`) as `total`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					GROUP BY `flash_version` 
					ORDER BY `total` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		$version	= array();
		$total		= 0;
		
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$version[$r['flash_version']] = $r['total'];
				$total += $r['total'];
			}
		}
		foreach ($version as $flash_version => $count)
		{
			if ($flash_version == 0 || $flash_version == 16)
			{
				$flash_version = "None";
			}
			else
			{
				$flash_version = "Flash ".$flash_version;
			}
			$tableData['tbody'][] = array
			(
				$flash_version,
				$this->Mint->formatPercents($count/$total*100)
			);
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
		}
	
	/**************************************************************************
	 parseUserAgent()
	 **************************************************************************/
	function parseUserAgent($user_agent) 
	{
		$ua['platform']	= "Unknown";
		$ua['family']	= "Unknown";
		$ua['version']	= "Unknown";
		
		if (preg_match('/(?<!dar)win/i', $user_agent)) 
		{
			$ua['platform'] = 'Windows';
		}
		else if (preg_match('/mac(?!hine)/i', $user_agent))
		{
			$ua['platform'] = 'Macintosh';
		}
		else if (preg_match('/linux/i', $user_agent))
		{
			$ua['platform'] = 'Linux';
		}
		else
		{
			$ua['platform'] = 'Other';
		}
		
		// Should never detect robots that are unable to run JavaScript but they are in here anyway
		if (preg_match_all('/(safari|shiira|firefox|firebird|feedonfeeds|phoenix|galeon|liferea|netnewswire|pulpfiction|feeddemon|magpierss|shrook|newsfire|bloglines|feedmania|avantgo|newsgator|opera|omniweb|camino|chimera|epiphany|konqueror|icab|lynx|(?<!find)links)(?: \(|\/|[^\/]*\/| )v?([0-9.]*)/i', $user_agent, $b))
		{
			$ua['family']	= $b[1][0];
			$ua['version']	= $b[2][0];
		}
		else if (preg_match_all('/(applewebkit)\/([0-9.]*)/i', $user_agent, $b))
		{
			$ua['family']	= $b[1][0]." (Generic)";
			$ua['version']	= $b[2][0];
		}
		else if (preg_match('/Mozilla\/(4[0-9.]*)/', $user_agent, $v) && !preg_match('/(compatible|MSIE|bot|crawler)/i', $user_agent))
		{
			$ua['family']	= "Netscape";
			$ua['version']	= $v[1];
		}
		else if (strpos($user_agent, 'Mozilla/5.0') !== false && preg_match_all('/(netscape)(?:[0-9]\/)?\/?([0-9.]*)/i', $user_agent, $b))
		{
			$ua['family']	= $b[1][0];
			$ua['version']	= $b[2][0];
		}
		else if (strpos($user_agent, 'Mozilla/5.0') !== false && preg_match('/rv(?: |:)([0-9.]*)/i', $user_agent, $v))
		{
			$ua['family']	= "Mozilla";
			$ua['version']	= $v[1];
		}
		else if (preg_match('/MSIE ?([0-9.]*)/i', $user_agent, $v) && !preg_match('/(bot|(?<!mytotal)search|seeker)/i', $user_agent))
		{
			$ua['family']	= "Internet Explorer";
			$ua['version']	= $v[1];
		}
		else if (preg_match('/(?:bot|obo|spider|crawl|client|feed|slurp|seek|dex|google|track|findlinks|email|search|ask|validator|archive)/i', $user_agent))
		{
			$ua['family'] = "Crawler/Search Engine";
			if (preg_match('/(ask jeeves|google|yahoo|msn|altavista|lycos|css info|feedthing|popdexter|kinja|aol|findlinks|atomz|blogbot|wotbox|feedster|simpy|bobby|blogpulse|technorati|w3search|validator|slurp)/i',$user_agent,$v))
			{
				$ua['version'] = $v[1];
			}
		}
		return $ua;
	}
}