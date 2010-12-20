<?php
/******************************************************************************
 Pepper
 
 Developer		: Shaun Inman
 Plug-in Name	: Default Pepper
 
 http://www.shauninman.com/

 ******************************************************************************/
if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file
$installPepper = "SI_Default";

class SI_Default extends Pepper
{
	var $version	= 127;
	var $info		= array
	(
		'pepperName'	=> 'Default',
		'pepperUrl'		=> 'http://www.haveamint.com/',
		'pepperDesc'	=> 'The Default Pepper covers the basics. It is responsible for tracking the number of page views and unique visitors, where they are coming from and what they are looking at, as well as which search terms led them to your site.',
		'developerName'	=> 'Shaun Inman',
		'developerUrl'	=> 'http://www.shauninman.com/'
	);
	var $panes		= array
	(
		'Visits'	=> array
		(
			'Refresh'
		),
		'Referrers'	=> array
		(
			'Newest Unique',
			'Most Recent',
			'Repeat'
		),
		'Pages'	=> array
		(
			'Most Popular',
			'Most Recent',
			'Watched'
		),
		'Searches'	=> array
		(
			'Most Common',
			'Most Recent'
		)
	);
	var $prefs		= array
	(
		'condensedVisits'			=> 0,
		'trimPrefixIndex'			=> 1,
		'referrerTimespan'			=> 24,
		'ignoreReferringDomains'	=> 'images.google.com bloglines.com'
	);
	var $data		= array
	(
		'visits'	=> array
		(
			/****************************************************
			 0	: Every visit ever recorded			(1 index)
			 1	: Every visit recorded by hour		(24 indexes)
			 2	: Every visit recorded by day		(7 indexes)
			 3	: Every visit recorded by week		(8 indexes)
			 4	: Every visit recorded by month		(12 indexes)
			 ****************************************************/
			array // 0
			(
				array // 0
				(
					'total'		=> 0,
					'unique'	=> 0
				)
			)
		),
		'watched'	=> array()
	);
	var $manifest	= array
	(
		'visit'	=> array
		(
			'ip_long' 			=> "INT(10) NOT NULL",
			'referer' 			=> "VARCHAR(255) NOT NULL",
			'referer_checksum' 	=> "INT(10) NOT NULL",
			'domain_checksum' 	=> "INT(10) NOT NULL",
			'referer_is_local' 	=> "TINYINT(1) NOT NULL DEFAULT '-1'",
			'resource' 			=> "VARCHAR(255) NOT NULL",
			'resource_checksum' => "INT(10) NOT NULL",
			'resource_title' 	=> "VARCHAR(255) NOT NULL",
			'search_terms' 		=> "VARCHAR(255) NOT NULL"
		)
	);
	
	/**************************************************************************
	 onUpdate()
	 
	 Called when Mint has been updated. Useful for importing data from versions
	 of Mint prior to 1.2.
	 **************************************************************************/
	function onUpdate()
	{
		if 
		(
			$this->Mint->cfg['version'] < 121				&& 
			!empty($this->data['watched'])						&& 
			gettype($this->data['watched'][0]) == 'string'
		)
		{
			foreach ($this->data['watched'] as $i => $resource)
			{
				$this->data['watched'][$i] = crc32($resource);
			}
		}
		return true;
	}
	
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
		$js = "pepper/shauninman/default/script.js";
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
 		if (empty($_GET)) 
 		{ 
 			return array(); 
 		}
 		
		$visits = $this->data['visits'];
		// Build an array of the start of the the current hour, day, week, & month
		$timespans = array
		(
			0,
			$this->Mint->getOffsetTime('hour'),
			$this->Mint->getOffsetTime('today'),
			$this->Mint->getOffsetTime('week'),
			$this->Mint->getOffsetTime('month')
		);
		
		// Update totals
		foreach ($timespans as $window => $begins) 
		{
			if (isset($visits[$window][$begins]['total'])) 
			{ 
				$visits[$window][$begins]['total']++;
			}
			else 
			{
				$visits[$window][$begins]['total']=1;
			}
		}
		
		// The virgin visit
		if (!isset($_COOKIE['MintUnique'])) 
		{
			$this->Mint->bakeCookie("MintUnique", 1, (time()+(60 * 60 * 24 * 365 * 10)));

			if (isset($visits[0][0]['unique'])) 
			{ 
				$visits[0][0]['unique']++;
			}
			else 
			{
				$visits[0][0]['unique']=1;
			}
		}
		
		// Unique Hour
		if (!isset($_COOKIE['MintUniqueHour']) || (isset($_COOKIE['MintUniqueHour']) && $_COOKIE['MintUniqueHour'] != $timespans[1]))
		{
			$hour = $timespans[1];
			$this->Mint->bakeCookie("MintUniqueHour", $hour, ($hour + (60 * 60)));

			if (isset($visits[1][$hour]['unique'])) 
			{ 
				$visits[1][$hour]['unique']++;
			}
			else 
			{
				$visits[1][$hour]['unique']=1;
			}
		}
		
		// Unique Day
		if (!isset($_COOKIE['MintUniqueDay']) || (isset($_COOKIE['MintUniqueDay']) && $_COOKIE['MintUniqueDay'] != $timespans[2]))
		{
			$day = $timespans[2];
			$this->Mint->bakeCookie("MintUniqueDay", $day, ($day + (60 * 60 * 24)));

			if (isset($visits[2][$day]['unique'])) 
			{ 
				$visits[2][$day]['unique']++;
			}
			else 
			{
				$visits[2][$day]['unique']=1;
			}
		}
		
		// Unique Week
		if (!isset($_COOKIE['MintUniqueWeek']) || (isset($_COOKIE['MintUniqueWeek']) && $_COOKIE['MintUniqueWeek'] != $timespans[3]))
		{
			$week = $timespans[3];
			$this->Mint->bakeCookie("MintUniqueWeek", $week, ($week + (60 * 60 * 24 * 7)));

			if (isset($visits[3][$week]['unique'])) 
			{ 
				$visits[3][$week]['unique']++;
			}
			else 
			{
				$visits[3][$week]['unique']=1;
			}
		}
		
		// Unique Month
		if (!isset($_COOKIE['MintUniqueMonth']) || (isset($_COOKIE['MintUniqueMonth']) && $_COOKIE['MintUniqueMonth'] != $timespans[4]))
		{
			$month = $timespans[4];
			$this->Mint->bakeCookie("MintUniqueMonth", $month, ($month + (60 * 60 * 24 * gmdate('t', $month))));

			if (isset($visits[4][$month]['unique'])) 
			{ 
				$visits[4][$month]['unique']++;
			}
			else 
			{
				$visits[4][$month]['unique']=1;
			}
		}
		
		// Trim older visit data
		$visits[1] = $this->array_prune($visits[1],24);
		$visits[2] = $this->array_prune($visits[2],7);
		$visits[3] = $this->array_prune($visits[3],5);
		$visits[4] = $this->array_prune($visits[4],12);
		
		// Store the updated visits data
		$this->data['visits'] = $visits;
		
 		/**********************************************************************/
 		
 		$ip 				= ip2long($_SERVER['REMOTE_ADDR']);
 		$referer 			= $this->escapeSQL(preg_replace('/#.*$/', '', htmlentities($_GET['referer'])));
 		$referer_is_local	= -1; // default for no referrer
 		$search				= '';
 		$resource			= $this->escapeSQL(preg_replace('/#.*$/', '', htmlentities($_GET['resource'])));
 		$resource_title		= ($_GET['resource_title_encoded']) ? $_GET['resource_title'] : htmlentities($_GET['resource_title']);
 		$res_title			= $this->escapeSQL(trim(str_replace('\n', ' ', preg_replace('/%u([\d\w]{4})/', '&#x$1;', $resource_title))));
 		
 		if ($this->prefs['trimPrefixIndex']) 
 		{
 			$referer = $this->trimPrefixIndex($referer);
 			$resource = $this->trimPrefixIndex($resource);
 		}
 		$referer_checksum	= crc32($referer);
 		$domain_checksum	= crc32(preg_replace('/(^([^:]+):\/\/(www\.)?|(:\d+)?\/.*$)/', '', $referer));
 		$resource_checksum	= crc32($resource);
 		
 		if (!empty($referer)) 
 		{
 			$referer_is_local	= (preg_match("/^([^:]+):\/\/([a-z0-9]+[\._-])*(".str_replace('.', '\.', implode('|', $this->Mint->domains)).")/i", $referer))?1:0;

			include_once('pepper/shauninman/default/engines.php');
			$search_sites = '';
			$search_query = '';
			for ($i=0; $i<count($SI_SearchEngines); $i++) 
			{
				$engine = $SI_SearchEngines[$i];
				if ($i) 
				{
					$search_sites .= '|';
					$search_query .= (!empty($engine['query']))?'|':'';
				}
				$search_sites .= preg_quote($engine['domain']).'\.';
				$search_query .= $engine['query'];
			}
			
			// select the domain up to the dot, then everything up to the first slash, then everything up to the first ? 
			// or a & followed by the query var and = but everything after the slash is optional (as in a9) 
			// then we capture everything until the next &	
			// if (preg_match('/(?:'.$search_sites.')[^\/]*\/(?:(?:[^\?&]*(?:\?|&))+(?:'.$search_query.')=)?([^&]*)/i',$referer,$q))
			if (preg_match('/(?:'.$search_sites.')[^\/]*\/(?:(?:[^\?&]*(?:\?|&))+(?:'.$search_query.')=)([^&]*)/i', html_entity_decode($referer), $q)) // Removed a9-accommodating optional query string
			{
				if (!empty($q[1])) 
				{
					$search = $this->escapeSQL(stripslashes(rawurldecode(preg_replace('/%u([\d\w]{4})/', '&#x$1;', htmlentities($q[1])))));
				}
			}
 		}
 		
 		return array
 		(
 			'ip_long'			=> $ip,
 			'referer'			=> $referer,
			'referer_checksum'	=> $referer_checksum,
			'domain_checksum'	=> $domain_checksum,
			'referer_is_local'	=> $referer_is_local,
			'resource'			=> $resource,
			'resource_checksum'	=> $resource_checksum,
			'resource_title'	=> $res_title,
			'search_terms'		=> $search
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
		/* Referrers **********************************************************/
			case 'Referrers': 
				switch($tab) 
				{
				/* Newest Unique **********************************************/
					case 'Newest Unique':
						$html .= $this->getHTML_ReferrersUnique();
					break;
				/* Most Recent ************************************************/
					case 'Most Recent':
						$html .= $this->getHTML_ReferrersRecent();
					break;
				/* Repeat *****************************************************/
					case 'Repeat':
						$html .= $this->getHTML_ReferrersRepeat();
					break;
				}
			break;
			
		/* Pages **************************************************************/
			case 'Pages': 
				// Ug Lee Hak
				$html .= <<<HERE
<style type="text/css" title="text/css" media="screen">
/* <![CDATA[ */
th.watched { background-color: #7B9F53; }
th.watched,
td.watched { text-align: right; width: 1%; }
td.watched a.watch { color: #666; font-size: 1.1em; }
td.watched a.unwatch { color: #AB6666; }
/* ]]> */
</style>
<script type="text/javascript" language="javascript">
// <![CDATA[
function SI_manageWatched(e,resource,remove) {
	if (remove) {
		// Remove from display and reorder
		var tbody	= e.parentNode.parentNode.parentNode;
		var row		= e.parentNode.parentNode;
		tbody.removeChild(row);
		SI.CSS.relate(tbody);
		var action = 'unwatch';
		}
	else {
		var action = e.href.replace(/^[^#]*#(.*)$/,'$1');
		if (action=='watch') {
			e.href 		= '#unwatch';
			e.innerHTML = '&times;';
			e.title 	= 'Unwatch this page';
			e.className	= 'unwatch';
			}
		else {
			e.href 		= '#watch';
			e.innerHTML	= '+';
			e.title 	= 'Watch this page';
			e.className	= 'watch';
			};
		};
	
	// Send request
	var url = '{$this->Mint->cfg['installDir']}/?MintPath=Custom&action='+action+'&pane=pages&resource='+escape(resource);
	SI.Request.post(url); //, document.getElementById('donotremove'));
	}

// ]]>
</script>
HERE;
				switch($tab) 
				{
				/* Most Popular ***********************************************/
					case 'Most Popular':
						$html .= $this->getHTML_PagesPopular();
					break;
				/* Most Recent ************************************************/
					case 'Most Recent':
						$html .= $this->getHTML_PagesRecent();
					break;
				/* Watched ****************************************************/
					case 'Watched':
						$html .= $this->getHTML_PagesWatched();
					break;
				}
				break;
		/* Searches ***********************************************************/
			case 'Searches': 
				switch($tab)
				{
				/* Most Common ************************************************/
					case 'Most Common':
						$html .= $this->getHTML_SearchesCommon();
					break;
				/* Most Recent ************************************************/
					case 'Most Recent':
						$html .= $this->getHTML_SearchesRecent();
					break;
				}
			break;
		/* Visits *************************************************************/
			case 'Visits': 
				$html .= $this->getHTML_Visits();
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
		$checked = ($this->prefs['trimPrefixIndex'])?' checked="checked"':'';
		$preferences['Global']	= <<<HERE
<table>
	<tr>
		<td><label><input type="checkbox" name="trimPrefixIndex" value="1"$checked /> Trim <code>www</code> and <code>index.*</code> from urls (This will logically collapse two different urls that point to the same file. May break some urls.)</label></td>
	</tr>
</table>

HERE;
		
		/* Visits ************************************************************* /
		$checked = ($prefs['condensedVisits'])?' checked="checked"':'';
		$preferences['Visits']		= <<<HERE
<table>
	<tr>
		<td><label><input type="checkbox" name="condensedVisits" value="1"$checked /> Used condensed version of the Visits pane</label></td>
	</tr>
</table>

HERE;
		
		/* Referrers **********************************************************/
		$selected1 = ($this->prefs['referrerTimespan']==1)?' selected="selected"':'';
		$selected2 = ($this->prefs['referrerTimespan']==2)?' selected="selected"':'';
		$selected4 = ($this->prefs['referrerTimespan']==4)?' selected="selected"':'';
		$selected8 = ($this->prefs['referrerTimespan']==8)?' selected="selected"':'';
		$selected24 = ($this->prefs['referrerTimespan']==24)?' selected="selected"':'';
		$selected48 = ($this->prefs['referrerTimespan']==48)?' selected="selected"':'';
		$selected72	= ($this->prefs['referrerTimespan']==72)?' selected="selected"':'';
		$selectedAll= ($this->prefs['referrerTimespan']==0)?' selected="selected"':'';
		$ignoredDomains = preg_replace('/[\s,]+/', "\r\n", $this->prefs['ignoreReferringDomains']);
		
		$preferences['Referrers']	= <<<HERE
<table>
	<tr>
		<td><label>Don't show referrals from the following domains in the Newest Unique Referrers tab (Search engines recognized by the Searches pane are ignored automatically):</label></td>
	</tr>
	<tr>
		<td><span><textarea id="ignoreReferringDomains" name="ignoreReferringDomains" rows="6" cols="30">{$ignoredDomains}</textarea></span></td>
	</tr>
</table>
<table class="snug">
	<tr>
		<td>Limit Repeat Referrers to past </td>
		<td><span class="inline"><select name="referrerTimespan">
			<option value="1"{$selected1}>1 hour</option>
			<option value="2"{$selected2}>2 hours</option>
			<option value="4"{$selected4}>4 hours</option>
			<option value="8"{$selected8}>8 hours</option>
			<option value="24"{$selected24}>24 hours</option>
			<option value="48"{$selected48}>48 hours</option>
			<option value="72"{$selected72}>72 hours</option>
			<option value="0"{$selectedAll}>Show all</option>
		</select></span></td>
	</tr>
</table>

HERE;

		/* Pages **************************************************************/
		$preferences['Pages']		= <<<HERE
<table>
	<tr>
		<td>
			<p>Drag the favelets below onto your browser's bookmark bar. Next time you view a page on <a href="http://{$this->Mint->cfg['installDomain']}/">{$this->Mint->cfg['siteDisplay']}</a>, use the favelets to add or remove the current page from the Watched tab of the Pages pane.</p>
						
			<p>Watched Favelets: &nbsp; 
			<a href="javascript:watch();function%20watch(){var%20resource=escape(window.location);var%20path='{$this->Mint->cfg['installFull']}/';if(path.toLowerCase().indexOf('www.')!=-1&&!(resource.toLowerCase().indexOf('www.')!=-1)){path=path.replace('www.','');}else%20if(resource.toLowerCase().indexOf('www.')!=-1&&!(path.toLowerCase().indexOf('www.')!=-1)){path=path.replace('http://','http://www.');};var%20post='MintPath=Custom&pane=pages&action=watch&resource='+resource+'&'+(new%20Date).getTime();var%20request=new%20XMLHttpRequest();request.open('POST',path,true);request.setRequestHeader('Method','POST%20'+path+'%20HTTP/1.1');request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');request.send(post);};" onclick="alert('Drag the Mint Watch favelet to your browser\'s bookmarks bar.'); return false;">Watch</a> &nbsp; 
			<a href="javascript:watch();function%20watch(){var%20resource=escape(window.location);var%20path='{$this->Mint->cfg['installFull']}/';if(path.toLowerCase().indexOf('www.')!=-1&&!(resource.toLowerCase().indexOf('www.')!=-1)){path=path.replace('www.','');}else%20if(resource.toLowerCase().indexOf('www.')!=-1&&!(path.toLowerCase().indexOf('www.')!=-1)){path=path.replace('http://','http://www.');};var%20post='MintPath=Custom&pane=pages&action=unwatch&resource='+resource+'&'+(new%20Date).getTime();var%20request=new%20XMLHttpRequest();request.open('POST',path,true);request.setRequestHeader('Method','POST%20'+path+'%20HTTP/1.1');request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');request.send(post);};" onclick="alert('Drag the Mint Unwatch favelet to your browser\'s bookmarks bar.'); return false;">Unwatch</a></p>
	
		</td>
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
		// If the offset is changing then we need to update the visits array
		if (isset($_POST['offset']) && $_POST['offset'] != $this->Mint->cfg['offset'])
		{
			$offset_difference = ($_POST['offset'] - $this->Mint->cfg['offset']) * 60 * 60;
			
			/****************************************************
			 Visits
			 2	: Every visit recorded by day		(7 indexes)
			 3	: Every visit recorded by week		(8 indexes)
			 4	: Every visit recorded by month		(12 indexes)
			 ****************************************************/
			// Get stored visits data
			$visits = $this->data['visits'];
			
			// Update day indexes
			if (isset($visits[2]))
			{
				$days = $visits[2];
				foreach ($days as $date=>$hits)
				{
					$days[($date - $offset_difference)] = $hits;
					unset($days[$date]);
				}
				$visits[2] = $days;
			}
			
			// Update week indexes
			if (isset($visits[3]))
			{
				$weeks = $visits[3];
				foreach ($weeks as $date=>$hits)
				{
					$weeks[($date - $offset_difference)] = $hits;
					unset($weeks[$date]);
				}
				$visits[3] = $weeks;
			}
			
			// Update month indexes
			if (isset($visits[4]))
			{
				$months = $visits[4];
				foreach ($months as $date=>$hits)
				{
					$months[($date - $offset_difference)] = $hits;
					unset($months[$date]);
				}
				$visits[4] = $months;
			}
			
			// Store the updated visits data
			$this->data['visits'] = $visits;
			/***********************************************************************/
		}
		
		$this->prefs['condensedVisits']			= (isset($_POST['condensedVisits']))?$_POST['condensedVisits']:0;
		$this->prefs['trimPrefixIndex']			= (isset($_POST['trimPrefixIndex']))?$_POST['trimPrefixIndex']:0;
		$this->prefs['referrerTimespan']		= (isset($_POST['referrerTimespan']))?$_POST['referrerTimespan']:24;
		$this->prefs['ignoreReferringDomains']	= $this->escapeSQL(preg_replace('/[\s,]+/', ' ', $_POST['ignoreReferringDomains']));
	}
	
	/**************************************************************************
	 onCustom()
	 **************************************************************************/
	function onCustom() 
	{
		/* Watch/Unwatch Pages -----------------------------------------------*/
		if
		(
			isset($_POST['action']) && 
			($_POST['action']=='watch' || $_POST['action']=='unwatch') && 
			(isset($_POST['pane']) && $_POST['pane']=='pages') && 
			isset($_POST['resource'])
		) 
		{
			$resource = $_POST['resource'];
			// Ignore if the un/watched page isn't local
			$localDomains = str_replace('.', '\.', implode('|', $this->Mint->domains));
			if (preg_match("/^https?:\/\/([a-z0-9]+[\._-])*($localDomains)/i", $resource)) 
			{
				// Get existing Watched Pages
				$watched = $this->data['watched'];
			
				if ($this->prefs['trimPrefixIndex'])
				{
					$resource = $this->trimPrefixIndex($resource);
				}
				
				$resource = crc32(preg_replace('/#.*$/', '', htmlentities($resource)));
				
				if ($_POST['action'] == 'watch')
				{
					if (!in_array($resource,$watched))
					{
						$watched[] = $resource;
					}
				}
				else if ($_POST['action'] == 'unwatch')
				{
					$i = array_search($resource,$watched);
					if ($i!==false)
					{
						unset($watched[$i]);
						$watched = $this->Mint->array_reindex($watched);
					}
				}
				
				// Save updated Watched Pages
				$this->data['watched'] = $watched;
			}
		}
		
		/* WATCHED PAGE REFERRERS --------------------------------------------*/
		else if 
		(
			isset($_POST['action']) 		&& 
			$_POST['action']=='getreferrers'	&& 
			isset($_POST['checksum'])
		)
		{
			$checksum	= $this->escapeSQL($_POST['checksum']);
			echo $this->getHTML_PagesWatchedReferrers($checksum).' ';
		}
		
		/* RSS ---------------------------------------------------------------*/
		else if (isset($_GET['RSS'])) 
		{
			$html 	= '';
			
			// Ignore certain domains
			$ignoredDomains	= preg_split('/[\s,]+/', $this->prefs['ignoreReferringDomains']);
			$ignoreQuery 	= '';
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
			
			$query = "SELECT `referer`, `resource`, `resource_title`, `dt`
						FROM `{$this->Mint->db['tblPrefix']}visit` 
						WHERE `referer_is_local` = 0 AND `search_terms` = '' $ignoreQuery
						GROUP BY `referer_checksum` 
						ORDER BY `dt` DESC 
						LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
			
			if ($result = $this->query($query)) 
			{
				while ($r = mysql_fetch_array($result)) 
				{
					$date			= gmdate("D, d M Y H:i:s",$r['dt'])." GMT";
					$r['referer']	= str_replace('&', '&amp;', $r['referer']);
					$r['resource']	= str_replace('&', '&amp;', $r['resource']);
					$res_title		= (!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource'];
					
					preg_match('/^[^:]+:\/\/(?:www\.)?([^\/]+)/', $r['referer'], $d);
					$domain = $d[1];
					
					$html .= <<<HERE
		<item>
			<title>New Referrer from $domain</title>
			<description><![CDATA[
				<table>
					<tr>
						<th scope="row" align="right">From</th>
						<td><a href="{$r['referer']}">{$r['referer']}</a></td>
					</tr>
					<tr>
						<th scope="row" align="right">To</th>
						<td><a href="{$r['resource']}">$res_title</a></td>
					</tr>
				</table>			
			]]></description>
			<link>{$r['referer']}</link>
			<pubDate>$date</pubDate>
		</item>

HERE;
				}
			}
			return $html;
		}
	}
	
	/**************************************************************************
	 onWidget()
	 
	 **************************************************************************/
	function onWidget() 
	{
		$visits = $this->data['visits'];
		
		// Ever
		$everArr = array
		(
			@number_format($visits[0][0]['total']),
			@number_format($visits[0][0]['unique']),
			$this->Mint->formatDateRelative($this->Mint->cfg['installDate'], 'month', 1)
		);
		
		// Today
		$day = $this->Mint->getOffsetTime('today');
		if (isset($visits[2][$day]))
		{
			$d = $visits[2][$day];
		}
		else
		{
			$d = array('total'=>'0','unique'=>'0');
		}
		$todayArr = array
		(
			@number_format($d['total']),
			@number_format($d['unique'])
		);
		
		
		// This hour
		$hour = $this->Mint->getOffsetTime('hour');
		if (isset($visits[1][$hour]))
		{
			$h = $visits[1][$hour];
		}
		else
		{
			$h = array('total'=>'0','unique'=>'0');
		}
		$hourArr = array
		(
			@number_format($h['total']),
			@number_format($h['unique']),
			$this->Mint->formatDateRelative(0, 'hour')
		);
		
		$visitsHTML = <<<HERE
			<ul id="visits-list">
				<li id="visits-ever">
					<div class="total">{$everArr[0]}</div>
					<div class="unique"><span>{$everArr[1]}</span> Unique</div>
					<div class="visits">Visits since {$everArr[2]}</div>
				</li>
				<li id="visits-today">
					<div class="total">{$todayArr[0]}</div>
					<div class="unique"><span>{$todayArr[1]}</span> Unique</div>
					<div class="visits">Visits today</div>
				</li>
				<li id="visits-hour">
					<div class="total">{$hourArr[0]}</div>
					<div class="unique"><span>{$hourArr[1]}</span> Unique</div>
					<div class="visits">Visits since {$hourArr[2]}</div>
				</li>
			</ul>
HERE;

		return $visitsHTML;
	}
		
	/**************************************************************************
	 getHTML_Visits()
	 **************************************************************************/
	function getHTML_Visits() 
	{
		$visits	= $this->data['visits'];
		
		/* Since **************************************************************/
		$tableData['table'] = array('id'=>'','class'=>'inline-foot');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Since','class'=>''),
			array('value'=>'Total','class'=>''),
			array('value'=>'Unique','class'=>'')
		);
		
		$tableData['tbody'][] = array
		(
			$this->Mint->formatDateRelative($this->Mint->cfg['installDate'], 'month', 1),
			$visits[0][0]['total'],
			$visits[0][0]['unique']
		);
		$sinceHTML = $this->Mint->generateTable($tableData);
		unset($tableData);
		
		
		/* Past Day ***********************************************************/
		$tableData['table'] = array('id'=>'','class'=>'inline day');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Past Day','class'=>''),
			array('value'=>'Total','class'=>''),
			array('value'=>'Unique','class'=>'')
		);
		$hour = $this->Mint->getOffsetTime('hour');
		// Past 24 hours
		for ($i=0; $i<24; $i++) 
		{
			$j = $hour - ($i*60*60);
			if (isset($visits[1][$j]))
			{
				$h = $visits[1][$j];
			}
			else
			{
				$h = array('total'=>'-','unique'=>'-');
			}
			$tableData['tbody'][] = array
			(
				$this->Mint->formatDateRelative($j,"hour"),
				((isset($h['total']))?$h['total']:'-'),
				((isset($h['unique']))?$h['unique']:'-')
			);
		}
		$dayHTML = $this->Mint->generateTable($tableData);
		unset($tableData);
		
		// Everything below this point gets broken when the offset changes
		// Maybe index times should be adjusted when the preferences are saved?
		
		/* Past Week **********************************************************/
		$tableData['table'] = array('id'=>'','class'=>'inline-foot');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Past Week','class'=>''),
			array('value'=>'Total','class'=>''),
			array('value'=>'Unique','class'=>'')
		);
		$day = $this->Mint->getOffsetTime('today');
		// Past 7 days
		for ($i=0; $i<7; $i++) 
		{
			$j = $day - ($i*60*60*24);
			if (isset($visits[2][$j]))
			{
				$d = $visits[2][$j];
			}
			else
			{
				$d = array('total'=>'-','unique'=>'-');
			}
			$tableData['tbody'][] = array
			(
				$this->Mint->formatDateRelative($j,"day"),
				((isset($d['total']))?$d['total']:'-'),
				((isset($d['unique']))?$d['unique']:'-')
			);
		}
		$weekHTML = $this->Mint->generateTable($tableData);
		unset($tableData);
		
		
		/* Past Month *********************************************************/
		$tableData['table'] = array('id'=>'','class'=>'inline inline-foot');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Past Month','class'=>''),
			array('value'=>'Total','class'=>''),
			array('value'=>'Unique','class'=>'')
		);
		$week = $this->Mint->getOffsetTime('week');
		// Past 5 weeks
		for ($i=0; $i<5; $i++)
		{
			$j = $week - ($i*60*60*24*7);
			if (isset($visits[3][$j]))
			{
				$w = $visits[3][$j];
			}
			else
			{
				$w = array('total'=>'-','unique'=>'-');
			}
			$tableData['tbody'][] = array
			(
				$this->Mint->formatDateRelative($j,"week",$i),
				((isset($w['total']))?$w['total']:'-'),
				((isset($w['unique']))?$w['unique']:'-')
			);
		}
		$monthHTML = $this->Mint->generateTable($tableData);
		unset($tableData);
		
		
		/* Past Year **********************************************************/
		$tableData['table'] = array('id'=>'','class'=>'inline year');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Past Year','class'=>''),
			array('value'=>'Total','class'=>''),
			array('value'=>'Unique','class'=>'')
		);
		$month = $this->Mint->getOffsetTime('month');
		// Past 12 months
		for ($i=0; $i<12; $i++)
		{
			if ($i==0)
			{
				$j=$month;
			}
			else
			{
				$days 		= $this->Mint->offsetDate('t', $this->Mint->offsetMakeGMT(0, 0, 0, $this->Mint->offsetDate('n', $month)-1, 1, $this->Mint->offsetDate('Y', $month))); // days in the month
				$j 			= $month - ($days*24*3600);
			}
			
			$month = $j;
			if (isset($visits[4][$j]))
			{
				$m = $visits[4][$j];
			}
			else
			{
				$m = array('total'=>'-','unique'=>'-');
			}
			
			$tableData['tbody'][] = array
			(
				$this->Mint->formatDateRelative($j, 'month', $i),
				((isset($m['total']))?$m['total']:'-'),
				((isset($m['unique']))?$m['unique']:'-')
			);
		}
		$yearHTML = $this->Mint->generateTable($tableData);
		unset($tableData);
		
		/**/
		$html  = '<table cellspacing="0" class="visits">';
		$html .= "\r\t<tr>\r";
		$html .= "\t\t<td class=\"left\">\r";
		$html .= $sinceHTML.$dayHTML;
		$html .= "\t\t</td>";
		$html .= "\t\t<td class=\"right\">\r";
		$html .= $weekHTML.$monthHTML.$yearHTML;
		$html .= "\t\t</td>";
		$html .= "\r\t</tr>\r";
		$html .= "</table>\r";
		return $html;
	}

	/**************************************************************************
	 getHTML_ReferrersRecent()
	 **************************************************************************/
	function getHTML_ReferrersRecent() 
	{
		$html = '';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'From','class'=>'stacked-rows'),
			array('value'=>'When','class'=>'')
		);
		
		// Referrers Pane
		$query = "SELECT `referer`, `resource`, `resource_title`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `referer_is_local` = 0 
					ORDER BY `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				$dt = $this->Mint->formatDateTimeRelative($r['dt']);
				$ref_title = $this->Mint->abbr($r['referer']);
				$res_title = $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				$tableData['tbody'][] = array
				(
					"<a href=\"{$r['referer']}\" rel=\"nofollow\">$ref_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>To <a href=\"{$r['resource']}\">$res_title</a></span>":''),
					$dt
				);
			}
		}
			
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}


	/**************************************************************************
	 getHTML_ReferrersUnique()
	 **************************************************************************/
	function getHTML_ReferrersUnique() 
	{
		$html = '';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'From','class'=>'stacked-rows'),
			array('value'=>'When','class'=>'')
		);
		
		// Ignore certain domains
		$ignoredDomains	= preg_split('/[\s,]+/', $this->prefs['ignoreReferringDomains']);
		$ignoreQuery 	= '';
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
		
		$query = "SELECT `referer`, `resource`, `resource_title`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `referer_is_local` = 0 AND `search_terms` = '' $ignoreQuery
					GROUP BY `referer_checksum` 
					ORDER BY `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				$dt = $this->Mint->formatDateTimeRelative($r['dt']);
				$ref_title = $this->Mint->abbr($r['referer']);
				$res_title = $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				$tableData['tbody'][] = array
				(
					"<a href=\"{$r['referer']}\" rel=\"nofollow\">$ref_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>To <a href=\"{$r['resource']}\">$res_title</a></span>":''),
					$dt
				);
			}
		}
			
		$html  = $this->Mint->generateTable($tableData);
		$html .= $this->Mint->generateRSSLink($this->pepperId, 'Newest Unique Referrers');
		return $html;
	}

	/**************************************************************************
	 getHTML_ReferrersRepeat()
	 **************************************************************************/
	function getHTML_ReferrersRepeat() 
	{
		$html = '';
		
		$timepsan = ($this->prefs['referrerTimespan'])?" AND dt > ".(time()-($this->prefs['referrerTimespan']*60*60)):'';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'From','class'=>'stacked-rows'),
			array('value'=>'Hits','class'=>'')
		);
		
		$query = "SELECT `referer`, `resource`, `resource_title`, COUNT(`referer`) as `total`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `referer_is_local` = 0 $timepsan
					GROUP BY `referer_checksum` 
					ORDER BY `total` DESC, `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				$ref_title = $this->Mint->abbr($r['referer']);
				$res_title = $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				$tableData['tbody'][] = array
				(
					"<a href=\"{$r['referer']}\" rel=\"nofollow\">$ref_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>To <a href=\"{$r['resource']}\">$res_title</a></span>":''),
					$r['total']
				   );
			   }
		   }
			
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}


	/**************************************************************************
	 getHTML_PagesRecent()
	 **************************************************************************/
	function getHTML_PagesRecent() 
	{
		$html = '';
		
		$watched = $this->data['watched'];
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Page','class'=>'stacked-rows'),
			array('value'=>'&nbsp;','class'=>'watched'),
			array('value'=>'When','class'=>'')
		);
		
		$query = "SELECT `referer`, `resource`, `resource_checksum`, `resource_title`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					ORDER BY `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
					
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				$dt = $this->Mint->formatDateTimeRelative($r['dt']);
				$ref_title = $this->Mint->abbr($r['referer']);
				$res_title = $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				
				$res_html = "<a href=\"{$r['resource']}\">$res_title</a>";
				if (!empty($ref_title) && $this->Mint->cfg['preferences']['secondary'])
				{
					$res_html .= "<br /><span>From <a href=\"{$r['referer']}\" rel=\"nofollow\">$ref_title</a></span>";
				}
				
				if (is_array($watched) && in_array($r['resource_checksum'], $watched)) 
				{
					$action = 'unwatch';
					$title	= 'Unwatch this page';
					$icon	= '&times;';
				}
				else 
				{
					$action = 'watch';
					$title	= 'Watch this page';
					$icon	= '+';
				}
				
				$actionLink = (!$this->Mint->isLoggedIn())?'':"<a href=\"#$action\" class=\"$action\" title=\"$title\" onclick=\"SI_manageWatched(this,'{$r['resource']}',0); return false;\">$icon</a>";
				
				$tableData['tbody'][] = array
				(
					$res_html,
					$actionLink,
					$dt
				);
			}
		}
			
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}


	/**************************************************************************
	 getHTML_PagesPopular()
	 **************************************************************************/
	function getHTML_PagesPopular() 
	{
		$html = '';
		
		$watched = $this->data['watched'];
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Page','class'=>'stacked-rows'),
			array('value'=>'&nbsp;','class'=>'watched'),
			array('value'=>'Hits','class'=>'')
		);
		
		$query = "SELECT `referer`, `resource`, `resource_checksum`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					GROUP BY `resource_checksum` 
					ORDER BY `total` DESC, `dt` DESC
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				$ref_title = $this->Mint->abbr($r['referer']);
				$res_title = $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				
				$res_html = "<a href=\"{$r['resource']}\">$res_title</a>";
				if (!empty($ref_title) && $this->Mint->cfg['preferences']['secondary']) 
				{
					$res_html .= "<br /><span>From <a href=\"{$r['referer']}\" rel=\"nofollow\">$ref_title</a></span>";
				}
				
				if (is_array($watched) && in_array($r['resource_checksum'],$watched)) 
				{
					$action = 'unwatch';
					$title	= 'Unwatch this page';
					$icon	= '&times;';
				}
				else 
				{
					$action = 'watch';
					$title	= 'Watch this page';
					$icon	= '+';
				}
				
				$actionLink = (!$this->Mint->isLoggedIn())?'':"<a href=\"#$action\" class=\"$action\" title=\"$title\" onclick=\"SI_manageWatched(this,'{$r['resource']}',0); return false;\">$icon</a>";
				
				$tableData['tbody'][] = array
				(
					$res_html,
					$actionLink,
					$r['total']
				);
			}
		}
			
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}


	/**************************************************************************
	 getHTML_PagesWatched()
	 **************************************************************************/
	function getHTML_PagesWatched() 
	{
		$html = '';

		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Page','class'=>'stacked-rows'),
			array('value'=>'&nbsp;','class'=>'watched'),
			array('value'=>'Hits','class'=>'')
		);
		
		$where = '';
		
		$watched = $this->data['watched'];
		
		if (!empty($watched)) 
		{
			$where = "WHERE `resource_checksum` = '".implode("' OR `resource_checksum` = '", $watched)."' ";
		}
		else
		{ 
			$where = "WHERE id=-1";
			$tableData['tbody'][] = array
			(
				"You have no Watched Pages",
				"",
				0
			);
		}
		
		$query = "SELECT `referer`, `resource`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					$where 
					GROUP BY `resource_checksum` 
					ORDER BY `total` DESC, `dt` DESC
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				$ref_title = $this->Mint->abbr($r['referer']);
				$res_title = $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				
				$res_html = "<a href=\"{$r['resource']}\">$res_title</a>";
				if (!empty($ref_title) && $this->Mint->cfg['preferences']['secondary']) 
				{
					$res_html .= "<br /><span>From <a href=\"{$r['referer']}\" rel=\"nofollow\">$ref_title</a></span>";
				}
				
				$actionLink = (!$this->Mint->isLoggedIn())?'':"<a href=\"#unwatch\" class=\"unwatch\" title=\"Unwatch this page\" onclick=\"SI_manageWatched(this,'{$r['resource']}',1); return false;\">&times;</a>";
				
				$tableData['tbody'][] = array
				(
					$res_html,
					$actionLink,
					$r['total']
				);
			}
		}
			
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_PagesWatched()										ACCORDION-STYLE
	 ************************************************************************** /
	function getHTML_PagesWatched() 
	{
		$html = '';

		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Page','class'=>'stacked-rows'),
			// array('value'=>'&nbsp;','class'=>'watched'),
			array('value'=>'Hits','class'=>'')
		);
		
		$where = '';
		
		$watched = $this->data['watched'];
		
		if (!empty($watched)) 
		{
			$tableData['hasFolders']		= true;
			$tableData['table']['class']	= 'folder';
			
			$where = "WHERE `resource_checksum` = '".implode("' OR `resource_checksum` = '", $watched)."' ";
		}
		else
		{ 
			$where = "WHERE id = -1";
			$tableData['tbody'][] = array
			(
				"You have no Watched Pages",
				// "",
				0
			);
		}
		
		$query = "SELECT `resource`, `resource_title`, COUNT(`resource_checksum`) AS `total`, `resource_checksum`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					$where 
					GROUP BY `resource_checksum` 
					ORDER BY `total` DESC, `dt` DESC
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				$res_title = $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				
				if ($this->Mint->cfg['preferences']['secondary']) 
				{
					$res_title .= "<br /><span>Click for Repeat Referrers</span>";
				}
				
				$tableData['tbody'][] = array
				(
					$res_title,
					// "<a href=\"#unwatch\" class=\"unwatch\" title=\"Unwatch this page\" onclick=\"SI_manageWatched(this,'{$r['resource']}',1); return false;\">&times;</a>",
					$r['total'],
				
					'folderargs' => array
					(
						'action'	=> 'getreferrers',
						'checksum'	=> $r['resource_checksum']
					)
				);
			}
		}
			
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_PagesWatchedReferrers()
	 **************************************************************************/
	function getHTML_PagesWatchedReferrers($checksum)
	{
		$html = '';
		$tableData['tbody'] = array();
		$timepsan = ''; // ($this->prefs['referrerTimespan'])?" AND dt > ".(time()-($this->prefs['referrerTimespan']*60*60)):'';
		$query = "SELECT `referer`, COUNT(`referer`) as `total`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `resource_checksum` = '$checksum' AND `referer_checksum` != 0 AND `referer_is_local` = 0 $timepsan
					GROUP BY `referer_checksum` 
					ORDER BY `total` DESC, `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$tableData['tbody'][] = array
				(
					'From <a href="'.$r['referer'].'" rel="nofollow">'.$this->Mint->abbr($r['referer']).'</a>',
					$r['total']
				);
			}
		}
		
		$html = $this->Mint->generateTableRows($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_SearchesRecent()
	 **************************************************************************/
	function getHTML_SearchesRecent() 
	{
		$html = '';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Keywords','class'=>'stacked-rows'),
			array('value'=>'When','class'=>'')
		);
			
		$query = "SELECT `referer`, `search_terms`, `resource`, `resource_title`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE
					`search_terms`!=''
					ORDER BY `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				$dt = $this->Mint->formatDateTimeRelative($r['dt']);
				$search_terms	= $this->Mint->abbr(stripslashes($r['search_terms']));
				$res_title		= $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				$tableData['tbody'][] = array
				(
					"<a href=\"{$r['referer']}\" rel=\"nofollow\">$search_terms</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>Found <a href=\"{$r['resource']}\">$res_title</a></span>":''),
					$dt
				);
			}
		}
			
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}


	/**************************************************************************
	 getHTML_SearchesCommon()
	 **************************************************************************/
	function getHTML_SearchesCommon() 
	{
		$html = '';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Keywords','class'=>'stacked-rows'),
			array('value'=>'Hits','class'=>'')
		);
		
		$query = "SELECT `referer`, `search_terms`, `resource`, `resource_title`, COUNT(`referer`) as `total`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE
					`search_terms`!=''
					GROUP BY `search_terms` 
					ORDER BY `total` DESC, `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$search_terms	= $this->Mint->abbr(stripslashes($r['search_terms']));
				$res_title		= $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				$tableData['tbody'][] = array
				(
					"<a href=\"{$r['referer']}\" rel=\"nofollow\">$search_terms</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>Found <a href=\"{$r['resource']}\">$res_title</a></span>":''),
					$r['total']
				);
			}
		}
			
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	
	/**************************************************************************
	 array_prune()
	 
	 Removes earlier indexes until the array is the specified length. Acts like
	 array_shift() if no length is specified but preserves integer indexes.
	 **************************************************************************/
	function array_prune($array, $length = -1)
	{
		// exit ASAP if pruning is unnecessary
		if ($length != -1 && count($array) <= $length)
		{
			return $array;
		}
		
		// No length specified, default to array_shift behavior
		if ($length==-1) 
		{
			$length = count($array)-1;
		}
		
		// Order ascending
		ksort($array);
		reset($array);
		
		// Go get 'em tiger
		$n = count($array)-$length;
		foreach($array as $key=>$val) 
		{
			if ($n>0) 
			{ 
				unset($array[$key]); 
				$n--;
			}
			else 
			{
				break;
			}
		}
		return $array;
	}
	
	/**************************************************************************
	 trimPrefixIndex()
	 
	 Removes www. and index.* from a url in an attempt to normalize disparate urls
	 **************************************************************************/
	function trimPrefixIndex($url) 
	{
		return preg_replace("/^http:\/\/www\./i","http://",preg_replace("/\/index\.[^?]+/i","/",$url));
	}
}