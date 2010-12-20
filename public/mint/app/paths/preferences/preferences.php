<?php
/******************************************************************************
 Mint
  
 Copyright 2004-2005 Shaun Inman. This code cannot be redistributed without
 permission from http://www.shauninman.com/
 
 More info at: http://www.haveamint.com/
 
 ******************************************************************************
 Preferences Path
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php include_once('app/includes/head.php'); ?>
<script type="text/javascript" language="javascript">
// <![CDATA[
window.onload	= function() { SI.onload(); };
// ]]>
</script>
</head>
<body class="mini preferences">
<div id="container">
	<div id="header">
		<h1>MINT</h1>
		<div class="panes"><a href="<?php echo $Mint->cfg['installDir'];?>/">View Mint</a> &nbsp; <a href="<?php echo $Mint->cfg['installDir'];?>/?instructions">Instructions</a> &nbsp; <a href="<?php echo $Mint->cfg['installDir'];?>/?uninstall">Uninstall</a> &nbsp; <a href="<?php echo $Mint->cfg['installDir'];?>/?logout">Logout</a></div>
	</div>
	<?php if (!empty($Mint->errors['list'])) {?>
	<div class="notice"><?php echo $Mint->getFormattedErrors(); ?></div>
	<?php } ?>
	
	<div class="pane">
		<h1>Preferences</h1>
		
		<div id="pane-preferences-content" class="content">
			<form method="post" action="<?php echo $Mint->cfg['installDir'];?>/">
				<input type="hidden" name="MintPath" value="Preferences" />
				<input type="hidden" name="action" value="Save" />
				
				<div class="general-column">
					<h2 class="first-child">Configuration</h2>
					<table>
						<tr>
							<th scope="row">Site Name</th>
							<td><span><input type="text" id="site_display" name="siteDisplay" value="<?php echo $Mint->cfg['siteDisplay'];?>" /></span></td>
						</tr>
						<tr>
							<th scope="row">Site Domain(s)</th>
							<td><span><input type="text" id="siteDomains" name="siteDomains" value="<?php echo $Mint->cfg['siteDomains']; ?>" /></span></td>
						</tr>
						<tr>
							<th scope="row">Mint Location</th>
							<td><span><input type="text" id="installFull" name="installFull" value="<?php echo $Mint->cfg['installFull']; ?>" /></span></td>
						</tr>
						<tr>
							<td></td>
							<td>Eg. <code>http://www.site.com/mint</code></td>
						</tr>
						<tr>
							<th scope="row">Local Time</th>
							<td><span><select name="offset" id="offset"><?php echo $Mint->generateOffsetOptions(); ?></select></span></td>
						</tr>
						<tr>
							<th scope="row">Maximum of</th>
							<td>
								<table class="snug">
									<tr>
										<td><span class="inline" style="margin-left: 0;"><input type="text" id="rows" name="rows" maxlength="4" value="<?php echo $Mint->cfg['preferences']['rows'];?>" class="cinch" /></span></td>
										<td>rows per pane</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td></td>
							<td><label><input type="checkbox" id="secondary" name="secondary" value="1"<?php echo ($Mint->cfg['preferences']['secondary'])?' checked="checked"':''?> /> Show secondary info</label></td>
						</tr>
						<tr>
							<td></td>
							<td><label><input type="checkbox" id="fix_height" name="fix_height" value="1"<?php echo ($Mint->cfg['preferences']['fixHeight'])?' checked="checked"':''?> /> Fix pane height and use scrollbars</label></td>
						</tr>
					</table>
					
					<h2>Login</h2>
					<table>
						<tr>
							<th scope="row">Email</th>
							<td><span><input type="text" id="email" name="email" value="<?php echo $Mint->cfg['email'];?>" autocomplete="off" /></span></td>
						</tr>
						<tr>
							<th scope="row">Password</th>
							<td><span><input type="password" id="password" name="password" value="<?php echo $Mint->cfg['password'];?>" autocomplete="off" /></span></td>
						</tr>
						<tr>
							<td></td>
							<td><label><input type="checkbox" onclick="SI.Cookie.set('MintIgnore',this.checked);" value="true" class="ignore"<?php if (isset($_COOKIE['MintIgnore']) && $_COOKIE['MintIgnore']=='true') { echo ' checked="checked"';} ?> /> Ignore my visits (uses cookies)</label></td>
						</tr>
						<tr>
							<td></td>
							<td><label><input type="checkbox" id="mode" name="mode" value="client" class="ignore"<?php if ($Mint->cfg['mode'] == 'client') { echo ' checked="checked"';} ?> /> Enable open, Client mode</label></td>
						</tr>
					</table>
					
					<h2>RSS</h2>
					<table class="snug">
						<tr>
							<td>Display </td>
							<td><span class="inline"><input type="text" id="rss_rows" name="rss_rows" maxlength="4" value="<?php echo $Mint->cfg['preferences']['rssRows'];?>" class="cinch" /></span></td>
							<td>items per feed</td>
						</tr>
					</table>
					
					<h2>Database (Current size: <?php echo number_format(($Mint->getDatabaseSize()/1024/1024),2); ?> MB)</h2>
					<table class="snug">
						<tr>
							<td>Remove visit data more than </td>
							<td><span class="inline"><input type="text" id="expiry" name="expiry" maxlength="4" value="<?php echo $Mint->cfg['preferences']['expiry'];?>" class="cinch" /></span></td>
							<td>weeks old</td>
						</tr>
					</table>
					<table class="snug">
						<tr>
							<td>Attempt to keep database size under </td>
							<td><span class="inline"><input type="text" id="maxSize" name="maxSize" maxlength="4" value="<?php echo $Mint->cfg['preferences']['maxSize'];?>" class="cinch" /></span></td>
							<td>MB</td>
						</tr>
					</table>
					
					<h2>Pane Order</h2>
					<table>
						<tr><td><?php echo $Mint->generatePaneOrderList(); ?></td></tr>
					</table>
				
				</div>
				<div class="pepper-column">
					<h2 class="first-child">Pepper</h2>
					
					<?php echo $Mint->preferences(); ?>
					
				</div>
				<div class="footer">
					<div><input type="image" src="app/images/btn-done.png" alt="Done" /></div>
				</div>
				<input type="image" src="app/images/btn-done.png" alt="Done" id="btn-done-top" />
			</form>
		</div>
			
		<div class="footer">
			<div>
			</div>
		</div>
	</div>
	<?php include_once('app/includes/foot.php'); ?>
</div>
</body>