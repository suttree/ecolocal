<?php
/******************************************************************************
 SERPs Pepper
 
 Developer		: Raven SEO
 Plug-in Name	: SERPs Pepper
 
 http://raven-seo-tools.com/mint/

 ******************************************************************************/
if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file
$installPepper = "RS_SERPs";

class RS_SERPs extends Pepper
{
	var $version	= 103;
	var $info		= array
	(
		'pepperName'	=> 'SERPs',
		'pepperUrl'		=> 'http://www.raven-seo-tools.com/mint/',
		'pepperDesc'	=> 'The SERPs Pepper adds passive SERP data to your search engine referrals',
		'developerName'	=> 'Raven SEO',
		'developerUrl'	=> 'http://www.raven-seo-tools.com/'
	);
	var $panes		= array
	(
		'SERPs'	=> array
		(
			'Most Common',
			'Most Recent',
			'Organic',
			'Paid'
		)
	);
	var $manifest	= array
	(
		'visit'	=> array
		(
			'serps_page'	=> "INT(4) NOT NULL default '-1'",
			'serps_paid' 	=> "INT(1) NOT NULL default '-1'",
			'serps_engine' 	=> "VARCHAR(255) NOT NULL"
		)
	);
	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		if ($this->Mint->version < 203)
		{
			$compatible = array
			(
				'isCompatible'	=> false,
				'explanation'	=> '<p>This Pepper requires Mint 2.03. Mint 2, a paid upgrade from Mint 1.x, is available at <a href="http://www.haveamint.com/">haveamint.com</a>.</p>'
			);
		}
		else
		{
			$compatible = array
			(
				'isCompatible'	=> true,
			);
		}
		return $compatible;
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
 		
 		$referer 			= $this->escapeSQL(preg_replace('/#.*$/', '', htmlentities($_GET['referer'])));
 		$serps_page			= -1;
 		$serps_paid			= -1;
		$serps_engine       = "";
		
 		if (!empty($referer)) 
 		{
 			$referer_is_local	= (preg_match("/^([^:]+):\/\/([a-z0-9]+[\._-])*(".str_replace('.', '\.', implode('|', $this->Mint->domains)).")/i", $referer))?1:0;
			if (!$referer_is_local)
			{
				include(MINT_ROOT.'pepper/ravenseo/serps/engines.php');
				
				$start = "";
				$offset = 0;
				$perpage = 0;
				$search_engine = "";
				$paid_var = "";
				
				foreach ($RS_SearchEngines as $engine) 
				{
					if (preg_match('!://[^/]*'.preg_quote($engine['domain']).'!i', html_entity_decode($referer), $q)) {
						$start = $engine['start'];
						$offset = $engine['offset'];
						$perpage = $engine['perpage'];
						$search_engine = $engine['name'];
						$paid_var = $engine['paid'];
						break;
					}
				}

				if ($start)
				{
					$serps_engine = $search_engine;
					if (preg_match('![\?\&]'.$paid_var.'=!i',html_entity_decode($_GET["resource"]), $q))
					{
						$serps_paid = 1;
					}
					if (preg_match('![\?\&]'.$start.'=(\d+)[^\d]*!i',html_entity_decode($referer), $q))
					{
						if (!empty($q[1]))
						{
							$x = intval($q[1]);
							if ($perpage > 0) {
								if ($x < 1) { $x = 1; }
								$x *= $perpage;
							}
							$x += $offset;
							$serp_value = intval($x / 10) + 1;
							$serps_page = $this->escapeSQL($serp_value);
						}
					}
					else
					{
						$serps_page = 1;
					}	
				}
			}
		}

 		return array
 		(
			'serps_page'	=> $serps_page,
			'serps_paid'	=> $serps_paid,
			'serps_engine'	=> $serps_engine
		);
	}
	
	/**************************************************************************
	 onDisplay()
	 **************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		
		if ($tab == "Most Recent")
			$html = $this->getHTML_SERPRecent();
		else
			$html = $this->getHTML_SERP($tab);

		return $html;
	}

	/**************************************************************************
	 getHTML_SERP()
	 **************************************************************************/
	function getHTML_SERP($tab="") 
	{
		$html = '';

		$filter = "";
		$timespan = "";


		$filters = array
		(
			'Show all'	=> 0,
			'Past hour'	=> 1,
			'2h'		=> 2,
			'4h'		=> 4,
			'8h'		=> 8,
			'24h'		=> 24,
			'48h'		=> 48,
			'72h'		=> 72
		);
		$html .= $this->generateFilterList($tab, $filters, array('Most Common', 'Organic', 'Paid'));
		$timespan = ($this->filter) ? " AND dt > ".(time() - ($this->filter * 60 * 60)) : '';
		if ($tab == "Organic")
			$filter = ' AND `serps_paid` <> 1';
		elseif ($tab == "Paid")
			$filter = ' AND `serps_paid` = 1';
		
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Page','class'=>''),
			array('value'=>'Engine','class'=>''),
			array('value'=>'Hits','class'=>'sort'),
			array('value'=>'Keywords','class'=>'focus')
		);
		
		$query = "SELECT `referer`, `serps_engine`, `search_terms`, `resource`, `resource_title`, COUNT(`referer`) as `total`, AVG(`serps_page`) as `avg_page`, `dt`, `img_search_found`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE
					`search_terms`!='' $filter $timespan
					and `serps_page`>0
					GROUP BY `serps_engine`, `search_terms` 
					ORDER BY `total` DESC, `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
					
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$search_terms	= $this->Mint->abbr(stripslashes($r['search_terms']), 30);
				$res_title		= $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource'], 30);
				$class			= ($r['img_search_found']) ? ' class="image-search"' : '';
				$tableData['tbody'][] = array
				(
					number_format($r['avg_page'],2),
					$r['serps_engine'],
					$r['total'],
					"<a href=\"{$r['referer']}\" rel=\"nofollow\"{$class}>$search_terms</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>Found <a href=\"{$r['resource']}\">$res_title</a></span>":'')
				);
			}
		}
			
		$html .= $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_SERP()
	 **************************************************************************/
	function getHTML_SERPRecent() 
	{
		$html = '';
		
		$filter = "";

		$filters = array
		(
			'Show all'	=> 0,
			'Organic'   => 1,
			'Paid' 		=> 2,
			'Image'		=> 3
		);
		$html .= $this->generateFilterList('Most Recent', $filters);
		switch ($this->filter)
		{
			case 1: $filter = ' AND `serps_paid` <> 1'; break;
			case 2: $filter = ' AND `serps_paid` = 1'; break;
			case 3: $filter = ' AND `img_search_found` = 1'; break;
			default: $filter = "";
		}

		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Page','class'=>''),
			array('value'=>'Engine','class'=>''),
			array('value'=>'Keywords','class'=>'focus'),
			array('value'=>'When','class'=>'sort')
		);
		
		$query = "SELECT `referer`, `serps_engine`, `search_terms`, `resource`, `resource_title`, `serps_page`, `dt`, `img_search_found`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE
					`search_terms`!='' $filter $timespan
					and `serps_page`>0
					ORDER BY `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
					
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$dt = $this->Mint->formatDateTimeRelative($r['dt']);
				$search_terms	= $this->Mint->abbr(stripslashes($r['search_terms']), 30);
				$res_title		= $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource'], 30);
				$class			= ($r['img_search_found']) ? ' class="image-search"' : '';
				$tableData['tbody'][] = array
				(
					number_format($r['serps_page'],2),
					$r['serps_engine'],
					"<a href=\"{$r['referer']}\" rel=\"nofollow\"{$class}>$search_terms</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>Found <a href=\"{$r['resource']}\">$res_title</a></span>":''),
					$dt
					
				);
			}
		}
			
		$html .= $this->Mint->generateTable($tableData);
		return $html;
	}
	
}