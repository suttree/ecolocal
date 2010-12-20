<div id="donotremove">
	<?php if (isset($Mint)) { echo $Mint->getFormattedVersion();  }?> &copy; 2004-<?php echo date("Y"); ?> <a href="http://www.shauninman.com/">Shaun Inman</a>. All rights reserved.
	Available at <a href="http://www.haveamint.com/">haveamint.com</a>. <?php if (isset($Mint) && $Mint->cfg['mode'] == 'client') { echo '<span>(Client Mode Enabled)</span>'; } ?>
</div>
<?php
if (isset($_GET['benchmark']))
{
	echo $Mint->getFormattedBenchmark();
}
if (isset($_GET['observe']))
{
	echo $Mint->observe($Mint); 
}
?>
<script type="text/javascript" language="javascript">
// <![CDATA[
SI.onbeforeload();
// ]]>
</script>
