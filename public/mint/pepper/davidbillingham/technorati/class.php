<?php
/******************************************************************************
 Pepper
 
 Developer: David Billingham
 Plug-in Name: TechnoratiNE
 
 http://slimandhisdogfrankie.com/code#technorati
 
 Displays Technorati Cosmos Results in mint
 ******************************************************************************/

if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file 

$installPepper = "DB_Technorati";
	
class DB_Technorati extends Pepper
{
	var $version	= 001; 
	var $info		= array
	(
		'pepperName'	=> 'Technorati',
		'pepperUrl'		=> 'http://slimandhisdogfrankie.com/peppers#technorati',
		'pepperDesc'	=> 'Displays Technorati Information in mint',
		'developerName'	=> 'David Billingham',
		'developerUrl'	=> 'http://slimandhisdogfrankie.com/'
	);
	var $panes = array
	(
		'Technorati' => array
		(
			'General',
			'Inbound'
		)
	);
	var $prefs = array
	(
		'apiKey' => '',
		'blogUrl' => '',
		'blogrollFix' => false
	);
	
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
			case 'Technorati': 
			
				switch($tab)
				{
					case 'Inbound':
					  $html .= $this->getHTML_Technorati_Inbound();
						break;
					case 'General':
					  $html .= $this->getHTML_Technorati_General();
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
		$prefs = $this->prefs;
				

		/* Global *************************************************************/

		$preferences['API Key'] = "
		<table>
			<tr>
				<td><label>API Key</label></td>
				<td><span><input type='text 'id='apiKey' name='apiKey' rows='6' cols='30' value='{$prefs['apiKey']}' /></span></td>
			</tr>
			<tr>
				<td colspan='2'>Enter your Technorati API Key. Find it <a href='http://technorati.com/developers/apikey.html' target='_blank'>here</a>.</td>
			</tr>
		</table>
		";
		if ($prefs['blogUrl'] == '')
			$prefs['blogUrl'] = "http://".$this->Mint->domains[0];
		$preferences['Blog URL'] = "
		<table>
			<tr>
				<td><label>Blog URL</label></td>
				<td><span><input type='text 'id='blogUrl' name='blogUrl' rows='6' cols='30' value='{$prefs['blogUrl']}' /></span></td>
			</tr>
			<tr>
				<td colspan='2'>Enter the URL to access your blog's index page.</td>
			</tr>
		</table>
		";
		
		$checked = ($this->prefs['blogRollFix'])?' checked="checked"':'';
		$preferences['Display Options'] = "
		<table>
			<tr>
				<td><strong>High Traffic Site?</strong><br />If you manage a high traffic site and are on alot of blog rolls the results may be over run with links that arn't important to you.  To fix that click the following box: <input type=\"checkbox\" id=\"blogRollFix\" name=\"blogRollFix\" value=\"0\"$checked /></td>
			</tr>
		</table>
		";
		
		return $preferences;

	}
	
	/**************************************************************************
	 onSavePreferences()
	 **************************************************************************/
	function onSavePreferences() 
	{
		$this->prefs['apiKey'] = $this->escapeSQL($_POST['apiKey']);
		$this->prefs['blogUrl'] = $this->escapeSQL($_POST['blogUrl']);
		$this->prefs['blogRollFix'] = (isset($_POST['blogRollFix']))?1:0;
	}
	
	/**************************************************************************
	onCustom()
	**************************************************************************/
	function onCustom() 
	{
		if 
		(
			isset($_POST['action']) && 
			$_POST['action']=='getlinkdetails'
		)
		{
			$from = $this->escapeSQL($_POST['from']);
			echo $this->get_LinkDetails($from);
		}
	}		
	
	/**************************************************************************
	 getHTML_Comments()
	 **************************************************************************/
	function getHTML_Technorati_Inbound()		
	{		
		$html = '';						
		
		$apiKey = $this->prefs['apiKey'];
		$blogUrl = $this->prefs['blogUrl'];
		$blogRollFix = $this->prefs['blogRollFix'];
		$error = false;
		
		$links = array();
		$linksfrom_prev="|";
		$numLinks = 0;

		if ($blogUrl != '') 
		{
			include "ducksoup.php";
			$api = new duckSoup;	// create a new object
			$api->api_key = $apiKey;	// your API key
			$api->type = 'cosmos';	// what API?
			$api->params = array('url' => $blogUrl, 'limit' => 100, 'current' => 'yes');	// the parameters

			$content=$api->get_content();	// get the content
				
		
			if ($content)
			{
					foreach($content['item'] as $item)
					{
					
						$title = $item['weblog']['name'];
						$permalink = $item['weblog']['url'];
						$inboundblogs = $item['weblog']['inboundblogs'];
						$inboundlinks = $item['weblog']['inboundlinks'];
						$mostrecent = date('U', strtotime($item['linkcreated']));
						//$mostrecent = $this->Mint->formatDateTimeRelative($mostrecent );
						
						if(strpos($linksfrom_prev,"|".$permalink."|")===false
								&& (!$blogRollFix || $permalink!=$item['nearestpermalink'])){
							$linksfrom_prev .= $permalink."|";
							$links[] = array
							(
								'title' => $title,
								'permalink' => $permalink,
								'linksin' => intval($inboundlinks),
								'blogsin' => intval($inboundblogs),
								'mostrecent' => $mostrecent
							);
							$numLinks++;
						}
					
						
						
						if ($numLinks >= $this->Mint->cfg['preferences']['rows'])
							break;
					}
				}
			
			

			if ($numLinks > 0)
			{
				$tableData['table'] = array('id'=>'','class'=>'folder');
				//$tableData['table'] = array('id'=>'','class'=>'');
				$tableData['thead'] = array
				(
					// display name, CSS class(es) for each column
					//array('value'=>"Inbound Link",'class'=>'stacked-rows'),
					array('value'=>"Inbound Link",'class'=>'stacked-rows'),
					array('value'=>'When','class'=>'')			
				);
				$tableData['hasFolders'] = true;

				foreach ($links as $link) 
				{
					$title = $link['title'];
					$permalink = $link['permalink'];
					$published = $link['mostrecent'];
					$blogsin=$link['blogsin'];
					$linksin=$link['linksin'];
					$count++;
					
					if ($published != "Not Available")
						$published = $this->Mint->formatDateTimeRelative($published);
					
					$tableData['tbody'][] = array
					(
						'<a href="'.$permalink.'" title="'.$permalink.'">'.$this->Mint->abbr($title).'</a>'.
						     (($this->Mint->cfg['preferences']['secondary'])?'<br /><span>Links:'.$linksin.' Blogs:'.$blogsin.'</span>':''),
						$published,
						'folderargs' => array
						(
							'action' => 'getlinkdetails',
							'from' => $permalink
						)
					);
				}
			} else {
				$error = true;
				$errorText = "Generic Parsing Error";
			}
		} else {
			$error = true;
			$errorText = "Blog URL not configured.";
		}
		
		if ($error)
		{
			$tableData['table'] = array('id'=>'','class'=>'');
			$tableData['thead'] = array
			(
				// display name, CSS class(es) for each column
				array('value'=>"Error Getting Comments",'class'=>'')		
			);
			$tableData['tbody'][] = array
			(
				'<strong>Error</strong>: '.$errorText
			);
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	function getHTML_Technorati_General()		
	{		
		$html = '';						
		
		$apiKey = $this->prefs['apiKey'];
		$blogUrl = $this->prefs['blogUrl'];
		$error = false;
		
		$rows = array();
		$numLinks = 0;

		if ($blogUrl != '') 
		{
			include "ducksoup.php";
			$api = new duckSoup;	// create a new object
			$api->api_key = $apiKey;	// your API key
			$api->type = 'bloginfo';	// what API?
			$api->params = array('url' => $blogUrl, 'limit' => $this->Mint->cfg['preferences']['rows']);	// the parameters

			$content=$api->get_content();	// get the content
				

			if ($content)
			{
				$rows[] = array('name' => 'Inbound Blogs','value' => $content['result']['inboundblogs']);
				$rows[] = array('name' => 'Inbound Links','value' => $content['result']['inboundlinks']);
				if(isset($content['result']['weblog'])){
					$rows[] = array('name' => 'Rank','value' => $content['result']['weblog']['rank']);
					$published = date('U', strtotime($content['result']['weblog']['lastupdate']));
					$published = $this->Mint->formatDateTimeRelative($published );
					$rows[] = array('name' => 'Last Updated','value' => $published);
				}
			}
			
			

			if (sizeof($rows) > 0)
			{
				$tableData['table'] = array('id'=>'','class'=>'folder');
				$tableData['table'] = array('id'=>'','class'=>'');
				$tableData['thead'] = array
				(
					array('value'=> $blogUrl,'class'=>''),
					array('value'=>'Value','class'=>'')			
				);
				foreach ($rows as $row) 
				{
					$tableData['tbody'][] = array
					(
						$row['name'],
						$row['value']
					);
				}
			} else {
				$error = true;
				$errorText = "Generic Parsing Error";
			}
		} else {
			$error = true;
			$errorText = "Blog URL not configured.";
		}
		
		if ($error)
		{
			$tableData['table'] = array('id'=>'','class'=>'');
			$tableData['thead'] = array
			(
				// display name, CSS class(es) for each column
				array('value'=>"Error Getting Comments",'class'=>'')		
			);
			$tableData['tbody'][] = array
			(
				'<strong>Error</strong>: '.$errorText
			);
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 get_CommentText()
	 **************************************************************************/
	function get_LinkDetails($from)		
	{		
		$html = '';						
		
		$apiKey = $this->prefs['apiKey'];
		$blogUrl = $this->prefs['blogUrl'];
		$blogRollFix = $this->prefs['blogRollFix'];
		$error = false;
		
		$links = array();
		$linksfrom_prev="|";
		$numLinks = 0;

		if ($blogUrl != '') 
		{
			include "ducksoup.php";
			$api = new duckSoup;	// create a new object
			$api->api_key = $apiKey;	// your API key
			$api->type = 'cosmos';	// what API?
			$api->params = array('url' => $blogUrl, 'limit' => 100, 'current' => 'no');	// the parameters

			$content=$api->get_content();	// get the content
				
		
			if ($content)
			{
					foreach($content['item'] as $item)
					{
					
						$baseurl=$item['weblog']['url'];
						$created = date('U', strtotime($item['linkcreated']));
						//$mostrecent = $this->Mint->formatDateTimeRelative($mostrecent );
						
						if($baseurl==$from
								&& (!$blogRollFix || $permalink!=$item['nearestpermalink'])){
							$links[] = array
							(
								'permalink' => $item['nearestpermalink'],
								'linkto' => $item['linkurl'],
								'excerpt' => $item['excerpt'],
								'created' => $created
							);
							$numLinks++;
						}
					
						
					}
				}
			
			

			if ($numLinks > 0)
			{
				foreach ($links as $link) 
				{
					$permalink = $link['permalink'];
					$linkto = $link['linkto'];
					$excerpt=$link['excerpt'];
					$created=$link['created'];
					$count++;
					
					if ( ($created != -1) && ($created)) {
						$date = $this->Mint->offsetDate('M j, Y',$created);
						$time = $this->Mint->offsetDate('g:i a',$created);
					}
					
					$tableData['tbody'][] = array
					(
						'<a href="'.$permalink.'" title="'.$permalink.'">'.$this->Mint->abbr($permalink).'</a><br/>'
								.'<span>to <a href="'.$linkto.'" title="'.$linkto.'">'.$this->Mint->abbr($linkto).'</a></span><br/>'
								.$this->Mint->abbr(strip_tags($excerpt), 250),
						$date.'<br /><span>'.$time.'</span>'
					);
				}
			} else {
				$error = true;
				$errorText = "Generic Parsing Error";
			}
		} else {
			$error = true;
			$errorText = "Blog URL not configured.";
		}
		
		if ($error)
		{
			
			$tableData['tbody'][] = array
			(
				'<strong>Error</strong>: '.$errorText
			);
		}
		
		$html = $this->Mint->generateTableRows($tableData);
		return $html;
	}
	
	
}

?>