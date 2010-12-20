<?php
/******************************************************************************
 Mint
  
 Copyright 2004-2005 Shaun Inman. This code cannot be redistributed without
 permission from http://www.shauninman.com/
 
 More info at: http://www.haveamint.com/
 
 ******************************************************************************
 Fatal Errors Path
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php include_once('app/includes/head.php'); ?>
</head>
<body class="mini">
<div id="container">
	<div id="header"><h1>MINT</h1></div>
	<div class="notice">
		<?php echo $Mint->getFormattedErrors(); ?>
		<a href="" onclick="history.back(); return false;"><img src="app/images/btn-back.png" alt="Back" width="62" height="22" /></a>
	</div>
	<?php include_once('app/includes/foot.php'); ?>
</div>
</body>
</html>