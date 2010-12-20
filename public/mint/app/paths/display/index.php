<?php
/******************************************************************************
 Mint
  
 Copyright 2004-2005 Shaun Inman. This code cannot be redistributed without
 permission from http://www.shauninman.com/
 
 More info at: http://www.haveamint.com/
 
 ******************************************************************************
 Display Path
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file 

if (isset($_GET['tab']))
{
	echo $Mint->displayTab().' '; // Trailing space is necessary for Safari to register an empty XMLHTTPRequest response
	exit();
}
else
{
	include_once('app/paths/display/display.php');
}
?>