<?php
/******************************************************************************
 Mint
  
 Copyright 2004-2005 Shaun Inman. This code cannot be redistributed without
 permission from http://www.shauninman.com/
 
 More info at: http://www.haveamint.com/
 
 ******************************************************************************
 Application Path
 ******************************************************************************/
if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file
header('P3P: CP="NOI NID ADMa OUR IND COM NAV STA LOC"'); // See http://www.p3pwriter.com/LRN_111.asp for definitions
 
include('app/lib/mint.php');
include('app/lib/pepper.php');
include('config/db.php');

$Mint->cfg['debug'] = false;

// Pepper is loaded separately so that the $Mint object exists in the global space
$Mint->loadPepper();

/******************************************************************************
 Record Path
 
 ******************************************************************************/
if (isset($_GET['js']) || isset($_GET['record']))
{
	include('app/paths/record/index.php');
	exit();
}

/******************************************************************************
 Utility Path (public)
 
 ******************************************************************************/
if (isset($_GET['info']))
{
	include('app/paths/util/info.php');
	exit();
}
if (isset($_GET['ignore']))
{
	$Mint->bakeCookie('MintIgnore', 'true', (time() + (3600 * 24 * 365 * 25)));
}
if (isset($_GET['gateway']))
{
	include('app/paths/util/gateway.php');
	exit();
}

/******************************************************************************
 Update Path

 ******************************************************************************/
if
(
	$Mint->cfg['version'] &&
	$Mint->cfg['version'] < $Mint->version
)
{
	$Mint->update();
}
foreach ($Mint->pepper as $pepperId => $pepper)
{
	if ($Mint->cfg['pepperShaker'][$pepperId]['version'] < $pepper->version)
	{
		$pepper->update();
	}
}

/******************************************************************************
 Installation Path

 ******************************************************************************/
if 
(
	!$Mint->errors['fatal'] && 
	(
		!$Mint->cfg['installed'] || 
		(
			isset($_POST['MintPath']) && 
			$_POST['MintPath'] == 'Install'
		)
	)
)
{
	include('app/paths/install/index.php');
}

/******************************************************************************
 Ping Path

 Used when transfering a license
 ******************************************************************************/
if (isset($_SERVER["HTTP_X_MINT_PING"])) 
{
	echo 'INSTALLED';
	exit();
}

/******************************************************************************
 Widget Path

 Manages own authentication
 ******************************************************************************/
if (isset($_POST['widget']))
{
	echo $Mint->widget();
	exit();
}

/******************************************************************************
 Authorization Path

 Login/logout 
 ******************************************************************************/
if (!$Mint->errors['fatal'])
{
	include('app/paths/auth/index.php');
}

/******************************************************************************
 Utility Path (private)
 
 Add additions to auth/index.php
 ******************************************************************************/
if (isset($_GET['visits']))
{
	include('app/paths/util/visits.php');
	exit();
}
if (isset($_GET['moved']))
{
	$Mint->updateAfterMove();
}
if (isset($_GET['import']))
{
	include('app/paths/util/import.php');
	exit();
}

/******************************************************************************
 Uninstall Path

 ******************************************************************************/
if (isset($_GET['uninstall']))
{
	include('app/paths/uninstall/index.php');
}

/******************************************************************************
 Custom Path

 RSS and other custom function calls. Must immediately follow 
 Authorization or we'll have a bit of a security hole.
 ******************************************************************************/
if 
(
	isset($_GET['custom']) || 
	(
		isset($_POST['MintPath']) && 
		$_POST['MintPath'] == 'Custom'
	) || 
	isset($_GET['RSS'])
)
{
	include('app/paths/custom/index.php');
	exit();
}

/******************************************************************************
 Preference Path

 ******************************************************************************/
if 
(
	isset($_GET['preferences']) ||
	(
		isset($_POST['MintPath']) && 
		$_POST['MintPath'] == 'Preferences'
	) ||
	isset($_GET['instructions'])
)
{
	include('app/paths/preferences/index.php');
}

/******************************************************************************
 Feedback Path

 Positive feedback notices
 ******************************************************************************/
if ($Mint->feedback['feedback'] && empty($Mint->errors['list']))
{
	include('app/paths/feedback/index.php');
	exit();
}

/******************************************************************************
 Fatal Error Path

 Database and other
 ******************************************************************************/
if ($Mint->errors['fatal'])
{
	include('app/paths/errors/index.php');
	exit();
}
$Mint->bakeCookie('MintConfigurePepper', '', time() - 365 * 24 * 60 * 60);

/******************************************************************************
 Display (default) Path
 
 ******************************************************************************/
include('app/paths/display/index.php');
?>