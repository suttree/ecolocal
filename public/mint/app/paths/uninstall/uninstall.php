<?php
/******************************************************************************
 Mint
  
 Copyright 2004-2005 Shaun Inman. This code cannot be redistributed without
 permission from http://www.shauninman.com/
 
 More info at: http://www.haveamint.com/
 
 ******************************************************************************
 Activation
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<?php include_once('app/includes/head.php'); ?>
</head>
<body class="mini install">
<div id="container">
	<div id="header"><h1>MINT</h1></div>
	
	<div class="pane">
		<h1>Uninstall Mint?</h1>
		
		<div id="pane-preferences-content" class="content">
			<form action="" method="post" onsubmit="return confirmUninstall();">
				
				<h2 class="first-child">Confirmation Required</h2>
				<table>
					<tr>
						<td colspan="3">
							<p><strong>Are you sure you want to uninstall Mint? Any data recorded by Mint and optional/third-party Pepper will be removed. This action cannot be undone.</strong></p>
							<p>Once uninstalled, all Mint-related files should be removed from your server. If you chose to attach the Mint JavaScript include using the advanced method, dont' forget to remove the related code from your <code>.htaccess</code> files.</p>
						</td>
					</tr>
					<tr>
						<td><input type="image" src="app/images/btn-uninstall.png" alt="Uninstall" /></td>
						<td><input type="checkbox" id="confirm-uninstall" name="confirm" value="true" /> Confirm uninstall</td>
						<td class="btn-row"><a href="<?php echo $Mint->cfg['installDir']; ?>/?preferences"><img src="app/images/btn-cancel.png" width="62" height="22" alt="Cancel" /></a></td>
					</tr>
				</table>
			</form>
		</div>
			
		<div class="footer">
			<div>
			</div>
		</div>
	</div>
	<?php include_once('app/includes/foot.php'); ?>
</div>
<script type="text/javascript" language="JavaScript">
// <![CDATA[
function confirmUninstall() {
	var confirm = document.getElementById('confirm-uninstall').checked;
	if (!confirm) {
		alert('Please check "Confirm uninstall" to proceed.');
		return false;
		}
	return true;
	}
// ]]>
</script>
</body>
</html>