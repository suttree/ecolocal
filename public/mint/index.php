<?php
/******************************************************************************
 Mint
  
 Copyright 2004-2005 Shaun Inman. This code cannot be redistributed without
 permission from http://www.shauninman.com/
 
 More info at: http://www.haveamint.com/
 
 ******************************************************************************
 Launcher
 ******************************************************************************/
if (isset($_GET['errors'])) { error_reporting(E_ALL); } else { error_reporting(0); }

define('MINT',true);
include('app/path.php');
?>