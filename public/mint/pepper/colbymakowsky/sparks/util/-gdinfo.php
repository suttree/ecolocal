<?
function gdVersion($user_ver = 0)	{
	if (! extension_loaded('gd')) { return; }	
	
	static $gd_ver = 0;

	// Just accept the specified setting if it's 1.
	if ($user_ver == 1) { $gd_ver = 1; return 1; }
	// Use the static variable if function was called previously.
	if ($user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }
	// Use the gd_info() function if possible.
	if (function_exists('gd_info')) {
		$ver_info = gd_info();
		preg_match('/\d/', $ver_info['GD Version'], $match);
		$gd_ver = $match[0];
		return $match[0];
	}
	// If phpinfo() is disabled use a specified / fail-safe choice...
	if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
		if ($user_ver == 2) {
			$gd_ver = 2;
			return 2;
		} 
		else {
			$gd_ver = 1;
			return 1;
		}
	}
	
	// ...otherwise use phpinfo().
	ob_start();
	phpinfo(8);
	$info = ob_get_contents();
	ob_end_clean();
	$info = stristr($info, 'gd version');
	preg_match('/\d/', $info, $match);
	$gd_ver = $match[0];

	return $match[0];
} // End gdVersion()

// Retrieve information about the currently installed GD library
function describeGDdyn() {
	echo "\n<ul><li>GD support: ";
	
	if(function_exists("gd_info")){
		echo "<font color=\"#00ff00\">yes</font>";
		$info = gd_info();
		$keys = array_keys($info);
		for($i=0; $i<count($keys); $i++) {
			if(is_bool($info[$keys[$i]])) echo "</li>\n<li>" . $keys[$i] .": " . yesNo($info[$keys[$i]]);
			else echo "</li>\n<li>" . $keys[$i] .": " . $info[$keys[$i]];
		}
	}
	else { echo "<font color=\"#ff0000\">no</font>"; }
	echo "</li></ul>";
}

function yesNo($bool){
	if($bool) return "<font color=\"#00ff00\"> yes</font>";
	else return "<font color=\"#ff0000\"> no</font>";
}

echo ('<strong>var_dump(gd_info()):</strong><br>');
var_dump(gd_info());

echo ('<br><br><strong>gdVersion():</strong><br>');
if ($gdv = gdVersion()) {
	echo '$gdv: ' . $gdv . '<br>';
   if ($gdv >=2) {
       echo 'TrueColor functions may be used.';
   } else {
       echo 'GD version is 1.  Avoid the TrueColor functions.';
   }
} else {
   echo "The GD extension isn't loaded.";
}

echo('<br><br><strong>describeGDdyn():</strong><br>');
describeGDdyn();

?>