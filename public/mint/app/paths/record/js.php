<?php
/******************************************************************************
 Mint
  
 Copyright 2004-2005 Shaun Inman. This code cannot be redistributed without
 permission from http://www.shauninman.com/
 
 More info at: http://www.haveamint.com/
 
 ******************************************************************************
 Record
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file 
 
// Prevent caching. Considering removing...
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-type: text/javascript');

if (isset($_COOKIE['MintIgnore']) && $_COOKIE['MintIgnore']=='true')
{
	echo '// Mint is ignoring you as requested';
	exit();
}

?>var Mint = new Object();
Mint.save = function() 
{
	var now		= new Date();
	var debug	= <?php echo ($Mint->cfg['debug'])?'true':'false'; ?>; // this is set by php 
	if (window.location.hash == '#Mint:Debug') { debug = true; };
	var path	= '<?php echo $Mint->cfg['installFull']; ?>/?record' + ((debug)?'&debug&errors':'') + '&key=<?php echo $Mint->generateKey(); ?>';
	
	// Loop through the different plug-ins to assemble the query string
	for (var developer in this) 
	{
		for (var plugin in this[developer]) 
		{
			if (this[developer][plugin] && this[developer][plugin].onsave) 
			{
				path += this[developer][plugin].onsave();
			};
		};
	};
	// Slap the current time on there to prevent caching on subsequent page views in a few browsers
	path += '&'+now.getTime();
	
	// Redirect to the debug page
	if (debug) { window.location.href = path; return; };
	
	if (document.write) { document.write('<img src="'+path+'" alt="" style="position: absolute; left: -9999px;" onload="this.parentNode.removeChild(this);" />'); }
	else
	{
		// Record this visit; uses XMLHttpRequest to play nice with pages served as application/xhtml+xml
		// Causes a security issue when served from a sub or other domain
		var data = false;
		/*@cc_on @*/
		/*@if (@_jscript_version >= 5)
		try { data = new ActiveXObject("Msxml2.XMLHTTP"); } 
		catch (e) { try { data = new ActiveXObject("Microsoft.XMLHTTP"); } catch (E) { data = false; } }
		@end @*/
		if (!data && typeof XMLHttpRequest!='undefined') { data = new XMLHttpRequest(); }
		if (data) { data.open("GET", path, true); data.send(null); }
	};
};
<?php $Mint->javaScript(); ?>
Mint.save();