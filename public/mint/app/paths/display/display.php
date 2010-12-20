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
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php include_once('app/includes/head.php'); ?>
<script type="text/javascript" language="javascript">
// <![CDATA[
window.onload	= function() { SI.Mint.sizePanes(); SI.Mint.onloadScrolls(); };
window.onresize	= function() { SI.Mint.sizePanes(); };
// ]]>
</script>
</head>
<body class="display">
<div id="container">
<?php echo $Mint->display(); ?>
<?php include_once('app/includes/foot.php'); ?>
</div>
</body>
</html>