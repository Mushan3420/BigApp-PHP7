<?php
/**
* @file getsmile.inc.php
* @Brief for smile optmization
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
if(!isset($_GET['smile'])){
	return;
}
$smile = dirname(__FILE__) . '/smiles/' . $_GET['smile'];
if(!file_exists($smile)){
	return;
}
header('Content-Type: image/png');
echo @file_get_contents($smile);
?>
