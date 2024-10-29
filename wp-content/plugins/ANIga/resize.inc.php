<?php

$vers = substr(phpversion(), 0, 1);

include_once('thumbnail.php'.$vers.'.php');

if(!empty($_POST) && isset($_POST['file'])){	

$p = $_POST['path'];
$f = $_POST['file'];
$rsize = $_POST['rsize'];
$crop = $_POST['crop'];
$csize = $_POST['csize'];
$mode = $_POST['mode'];
$qual = $_POST['qual'];
$refl = $_POST['refl'];
$border = $_POST['border'];

	$thumb = new Thumbnail($p.$f);
	
	if ($crop == 'yes') {
		$thumb->resize($csize,$csize);
		$thumb->cropFromCenter($rsize);
	}
	else $thumb->resize($rsize,$rsize);
	
	if ($refl == 'yes') {
		$thumb->createReflection(40,35,75,true,'#'.$border);
	}
	
	$thumb->save($p.$mode.'_'.$_POST['file'],$qual);
	
	if ($vers == 4) $thumb->destruct();

//if ($mode == 'norm') echo "resizing norm_ <b>OK</b>!";
//if ($mode == 'thumb') echo "resizing thumb_ <b>OK</b>!";

echo 'resize <span style="color:green">successful</span>';
}
?>