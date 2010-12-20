<?php
/******************************************************************************
 Mint
  
 Copyright 2004-2005 Shaun Inman. This code cannot be redistributed without
 permission from http://www.shauninman.com/
 
 More info at: http://www.haveamint.com/
 
 ******************************************************************************
 Pepper Constructor
 ******************************************************************************/
if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file 

class Pepper
{
	var $Mint;
	var $pepperId;
	var $version	= 1; // eg. 1 = 0.01, 100 = 1.0
	var $info		= array
	(
		'pepperName'	=> '',
		'pepperUrl'		=> '',
		'pepperDesc'	=> '',
		'developerName'	=> '',
		'developerUrl'	=> ''
	);
	var $panes		= array();
	var $prefs		= array();
	var $data		= array();
	var $manifest	= array();
	
	/**************************************************************************
	 Pepper Constructor											DO NOT OVERLOAD
	 
	 Takes care of getting a reference to Mint and loading Pepper data and 
	 preferences. Do NOT overload this constructor in your Pepper. 
	 
	 If you need to perform additional actions onload, overload the 
	 onPepperLoad() method in your Pepper's class.
	 **************************************************************************/
	function Pepper($pepperId)
	{
		global $Mint;
		$this->Mint		=& $Mint;
		$this->pepperId	= $pepperId;
		$this->onPepperLoad();
	}
	 
	/**************************************************************************
	 onPepperLoad() 
	 
	 This optional handler is called immediately after Mint loads the Pepper. 
	 To be used in Pepper maintenance routines and as a replacement for 
	 individual Pepper constructors.
	 **************************************************************************/
	function onPepperLoad()
	{
		return true;
	}
	
	/**************************************************************************
	 onUpdate()
	 
	 Called when Mint has been updated. Useful for importing data from versions
	 of Mint prior to 1.2.
	 **************************************************************************/
	function onUpdate()
	{
		return true;
	}
	 
	/**************************************************************************
	 update()
	 
	 Mint will compare the version number it has on file for this Pepper against
	 the hardcoded version and call this method if the latter is greater. It is
	 up to the Pepper developer to detect the previous version and update 
	 accordingly.
	 Version numbers should be 100 for every full release, eg. 1.0 = v100
	 **************************************************************************/
	function update()
	{
		return true;
	}
	
	/**************************************************************************
	 isCompatible()
	 
	 It is each Pepper's responsibility to check compatibility with the server 
	 software and the version of Mint installed. Returns a two index array. The 
	 first is a boolean indicating whether this Pepper is compatible or not. The
	 second, optional index provides an upgrade or helpful message specific to 
	 the server software or current version of Mint as a formatted HTML string. 
	 Use HERE doc syntax for complex messages.
	 
	 Please provide a helpful explanation (reasons/upgrade info) if your Pepper 
	 is not compatible.
	 **************************************************************************/
	function isCompatible()
	{
		return array
		(
			'isCompatible'	=> true,
			'explanation'	=> '<p>This Pepper does not check for compatibility with this version of Mint. Please contact the Pepper developer if you have any questions or problems with installation or use of this Pepper.</p>'
		);
	}
		
	/**************************************************************************
	 onJavaScript()
	 Outputs JavaScript responsible for extracting the necessary values (if any)
	 necessary for this plug-in. Does not return a string. echo or print() any
	 return output. You may also push an external file out to the browser as in 
	 the Default Pepper.
	 
	 JavaScript should follow format of the new SI object to prevent collisions
	 with local code. Never overwrite local elements event handlers like 
	 body.onload or a.onclick.
	 **************************************************************************/
	function onJavaScript() 
	{
		return;
	}
	
	/**************************************************************************
	 onRecord()
	 
	 Operates on existing $_GET values, values generated as a result of optional 
	 JavaScript output or existing $_SERVER variables and returns an associative
	 array with a column name as the index and the value to be stored in that 
	 column as the value.
	 **************************************************************************/
	function onRecord() 
	{	
 		return array();
	}
	
	/**************************************************************************
	 onDisplay()
	 Produces what the user sees when they are browsing their Mint install.
	 
	 Returns an HTML string for the requested pane and tab. Pane and Tab are 
	 requested by name. The $column and $sort arguments don't do anything 
	 currently.
	 **************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '') 
	{
		return '';
	}
	
	/**************************************************************************
	 onDisplayPreferences()
	 
	 Should return an assoicative array (indexed by pane name) that contains the
	 HTML contents of that pane's preference. Preferences used by all panes in 
	 this Pepper should be indexed as 'Global' and appear first in the array.
	 **************************************************************************/
	function onDisplayPreferences() 
	{
		return array();
	}
	
	/**************************************************************************
	 onSavePreferences()
	 
	 Should validate and assign user input to the $this->prefs array. This array
	 along with the $this->data array are now automatically saved to the 
	 database by Mint.
	 **************************************************************************/
	function onSavePreferences() 
	{
		return true;
	}
	
	/**************************************************************************
	 onCustom()
	 
	 This optional handler is called when `custom` appears in the query string 
	 of a request or a form is posted with `MintPath` set to 'Custom'. Your 
	 Pepper is responsible for providing additional variables and logic to 
	 handle those variables. The function can return anything or nothing--
	 whatever is appropriate for its use.
	 
	 The $this->prefs array and the $this->data array are automatically saved to
	 the database by Mint after this handler is called.
	 **************************************************************************/
	function onCustom()
	{
		return;
	}
	
	/**************************************************************************
	 onWidget()
	 
	 Returns HTML specially formatted for the Junior Mint Dashboard Widget. 
	 Currently a closed system used only by Default Pepper.
	 **************************************************************************/
	function onWidget() 
	{
		return;
	}
	
	/**************************************************************************
	 onTidy()
	 
	 Any Pepper that adds a table to the users Mint database is responsible for
	 maintaining the size of the table. Any table starting with the Mint table 
	 prefix counts towards the total Mint database size. This method is called
	 after expired visit data is removed from the database but before Mint trims
	 it's own visit table to the size (optionally) specified by the user. This
	 method will be called once an hour.
	 
	 See Mint->_tidySave() for sample code.
	 **************************************************************************/
	function onTidy() 
	{
		return;
	}
	
	/**************************************************************************
	 getFormattedVersion()										DO NOT OVERLOAD
	 
	 Returns the version number formatted for display
	 **************************************************************************/
	function getFormattedVersion() 
	{
		$len = (substr($this->version.'',-1) == '0')?1:2;
		return '<abbr title="v'.str_pad($this->version,3,'0',STR_PAD_LEFT).'">'.number_format($this->version/100,$len).'</abbr>';
	}
	
	/**************************************************************************
	 query()													DO NOT OVERLOAD
	 
	 Handler for mysql_query, writes query to $Mint->output
	 **************************************************************************/
	function query($query) 
	{
		$this->Mint->logBenchmark('query("'.substr($query, 0, 24).'...") {');
		
		$this->Mint->queries[] = $query;
		if (!($result = mysql_query($query)))
		{
			$this->Mint->logError('MySQL Error (from '.$this->info['pepperName'].'): '.mysql_error().'. ('.mysql_errno().')<br />Query: '.$query);
			$result = false;
		}
		
		$this->Mint->logBenchmark('}');
		return $result;
	}
	
	/**************************************************************************
	 Shortcuts to Mint methods									DO NOT OVERLOAD
	 **************************************************************************/
	function escapeSQL($str) { return $this->Mint->escapeSQL($str); }
}