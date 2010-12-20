<?php
/******************************************************************************
 Mint
  
 Copyright 2004-2005 Shaun Inman. This code cannot be redistributed without
 permission from http://www.shauninman.com/
 
 More info at: http://www.haveamint.com/
 
 ******************************************************************************
 Login
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php include_once('app/includes/head.php'); ?>
<script src="?js" type="text/javascript" language="javascript"></script>
<script type="text/javascript" language="javascript">
// <![CDATA[
var MintTop = true; // Used to force logout from SI.Request loaded panes when appropriate
window.onload	= function() { SI.onload(); };
// ]]>
</script>
</head>
<body class="mini login">
<div id="container">
	<div id="header"><h1>MINT</h1></div>
	<?php if (!empty($Mint->errors['list'])) {?>
	<div class="notice"><?php echo $Mint->getFormattedErrors(); ?></div>
	<?php } ?>
	<div class="pane">
		<h1>Login Required</h1>
		<div class="tabs"><a href="#pane-login-content" class="active">Login</a> &nbsp; <a href="#pane-password-content">Forgotten Password?</a></div>
		
		<div id="pane-login-content" class="content">
			<form method="post" action="">
			<input type="hidden" name="MintPath" value="Auth" />
			<input type="hidden" name="action" value="Login" />
				<table>
					<tr>
						<th scope="row">Email</th>
						<td><span><input type="text" id="email" name="email" value="<?php echo (isset($_POST['email']))?$_POST['email']:'';?>" onblur="document.getElementById('emailReminder').value=this.value;" /></span></td>
					</tr>
					<tr>
						<th scope="row">Password</th>
						<td>
							<table>
								<tr>
									<td><span><input type="password" id="password" name="password" /></span></td>
									<td><input type="image" src="app/images/btn-login.png" alt="Login" class="btn" /></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</form>
		</div>
		
		<div id="pane-password-content" class="content">
			<form method="post" action="">
			<input type="hidden" name="MintPath" value="Auth" />
			<input type="hidden" name="action" value="Send Password" />
				<table>
					<tr>
						<td colspan="2">Enter the email address used to administer Mint below and your password will be emailed to you shortly.</td>
					</tr>
					<tr>
						<td><span><input type="text" id="emailReminder" name="emailReminder" value="<?php echo (isset($_POST['email']))?$_POST['email']:'';?>" onblur="document.getElementById('email').value=this.value;" /></span></td>
						<td width="1"><input type="image" src="app/images/btn-send.png" alt="Send" class="btn" /></td>
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
	
	<!-- Used to force logout from SI.Request loaded panes when appropriate -->
	<img onload="if(!window.MintTop){window.location.href=''};this.parentNode.removeChild(this);" src="app/images/loaded.gif" />
</div>
</body>
</html>
