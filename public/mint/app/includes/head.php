<?php
if (!isset($Mint->tmp['pageTitle']))
{
	$Mint->tmp['pageTitle'] = $Mint->cfg['siteDisplay'];
}
if (!isset($Mint->tmp['headTags']))
{
	$Mint->tmp['headTags'] = '';
}
?>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title>Mint: <?php echo $Mint->tmp['pageTitle']; ?></title>
<link rel="shortcut icon" type="image/ico" href="app/images/favicon.ico" />
<link rel="stylesheet" type="text/css" href="app/styles/mint.css" />
<script type="text/javascript" src="app/scripts/si-object-mint.js"></script>
<!--[if IE]>
<link type="text/css" href="app/styles/hack/iepc.css" rel="stylesheet" />
<script type="text/javascript" src="app/scripts/hack/iepc.js"></script>
<![endif]-->
<?php echo $Mint->tmp['headTags']; ?>