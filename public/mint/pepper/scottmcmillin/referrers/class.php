<?php
/******************************************************************************
 Pepper
 
 Developer		: Scott McMillin
 Plug-in Name	: Referrer Rollup
 
 http://www.scottmcmillin.com/

 ******************************************************************************/

$installPepper = "DSM_Referrers";

class DSM_Referrers extends Pepper
{
	var $version	= 121; 
	var $info		= array
	(
		'pepperName'	=> 'Referrer Rollup',
		'pepperUrl'		=> 'http://www.scottmcmillin.com/pepper/',
		'pepperDesc'	=> 'View your referrers all rolled-up.',
		'developerName'	=> 'Scott McMillin',
		'developerUrl'	=> 'http://www.scottmcmillin.com/'
	);
	var $panes		= array
	(
		'Referrer Rollup'	=> array
		(
			'Referrers'
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

	}
	
	/**************************************************************************
	 onRecord()
	 **************************************************************************/
	function onRecord() 
	{
        return array();
	}
	
	/**************************************************************************
	 onDisplayPreferences()
	 
	 **************************************************************************/
 	function onDisplayPreferences() {

 			$checked = (isset($this->prefs['hideMyDomain']) && $this->prefs['hideMyDomain'] == 1) ? ' checked="checked"':'';
 			$preferences['Referrals']	= "
         	<table>
         		<tr>
         			<td><label><input type=\"checkbox\" name=\"hideMyDomain\" value=\"1\" $checked /> Hide referrals from " . $this->Mint->cfg['siteDomains'] . "</label></td>
         		</tr>
         	</table>";

 			return $preferences;

 		}

 	/**************************************************************************
 	 onSavePreferences()

 	 **************************************************************************/
 	function onSavePreferences() {
 		$this->prefs['hideMyDomain']	= (isset($_POST['hideMyDomain'])) ? $_POST['hideMyDomain'] : 0;	
 		}	
	
	/**************************************************************************
	 onDisplay()
	 **************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		$html = '';
		
		switch($pane) {
		/* Referrer Rollup *****************************************************/
			case 'Referrer Rollup': 
				switch($tab) {
				/* Browsers ***************************************************/
					case 'Referrers':
						$html .= $this->getHTML_Referrers();
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
		if (isset($_POST['action']) && $_POST['action']=='getkids' && isset($_POST['dsmrefdomain'])) {
			$domain	= $this->Mint->escapeSQL($_POST['dsmrefdomain']);
			echo $this->getHTML_ReferrerChildren($domain);
			}
	}




	/**************************************************************************
	 getHTML_Referrers()

	 **************************************************************************/
	function getHTML_Referrers() {


		$html = '';

		$domain = "domain_checksum != '0'";

		if (isset($this->prefs['hideMyDomain']) && $this->prefs['hideMyDomain'] == '1') {

		    $domain .= " AND referer_is_local = '0' ";
		    
		}
		

		$tableData['hasFolders'] = true;

		$tableData['table'] = array('id'=>'','class'=>'folder');

		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>'Referrer','class'=>''),
			array('value'=>'Hits','class'=>'')
			);


		$query = "select domain_checksum, count(domain_checksum) as total,referer  
						from {$this->Mint->db['tblPrefix']}visit  
						where $domain
		 				group by domain_checksum 
						order by total desc
						LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";			



		$domains = array();



		if ($result = mysql_query($query)) {
			while ($r = mysql_fetch_array($result)) {
				$domains[$r['domain_checksum']] = $r['total'];
				$refererdomain[$r['domain_checksum']] = $this->createDomain($r['referer']);
				}
			}



		foreach ($domains as $domain => $total) {
            
            
            
			$tableData['tbody'][] = array(
				$refererdomain[$domain],
				$total,
				'folderargs'=>array(
					'action'=>'getkids',
					'dsmrefdomain'=>$domain
					)


				);
			}



		$html = $this->Mint->generateTable($tableData);


		return $html;

		}



		/**************************************************************************
		 getHTML_Browsers()

		 **************************************************************************/
		function getHTML_ReferrerChildren($domain) {


			$html = '';

		/*	$query = "select referer, count(referer) as total
			 			from {$this->Mint->db['tblPrefix']}visit
						where domain_checksum = '$domain'
						group by referer 
						order by total desc
						LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";	*/

           $query = "  SELECT 
                        CONCAT('/',SUBSTRING_INDEX(TRIM(LEADING 'http://' FROM referer),'/',-1)) AS resource,
                        COUNT(resource) AS total,
                        referer 
    			 			from {$this->Mint->db['tblPrefix']}visit
    						where domain_checksum = '$domain'
    						group by resource 
    						order by total desc
                        	LIMIT 0,{$this->Mint->cfg['preferences']['rows']}					";



				if ($result = mysql_query($query)) {
					while ($r = mysql_fetch_array($result)) {
						$tableData['tbody'][] = array(
							$this->createLink($r['referer'],$r['resource']),
							$r['total']
							);
						}
					}


			$html = $this->Mint->generateTableRows($tableData);


			return $html;

			}


			/**************************************************************************
			 createLink()

			 **************************************************************************/
			function createLink($referer,$resource) {

				return "<a href=\"$referer\" target=\"_blank\">". $this->Mint->abbr($resource) ."</a>";


			}
			
			function createDomain($referer) {
			    
			    $domain = explode("/",$referer);
			    return $domain[2];
			}



}