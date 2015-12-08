<?php
/**
* @file optpic.inc.php
* @Brief for picture optimization
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if(!isset($_GET['url']) || !isset($_GET['size'])){
	return;
}
require_once (dirname(__FILE__) . '/image/cut_pic.php');
?>
