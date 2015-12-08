<?php
/**
* @file getaksk.php
* @Brief get plugin configs
* @author youzu
* @version 1.0.0
* @date 2015-07-21
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();
require_once(dirname(__FILE__)."/../../libs/env.inc.php");

$verbose = isset($_GET["verbose"])&&($_GET["verbose"]==1) ? 1 : 0;
$ret = array(
    'error_code' => 0,
    'error_msg' => 'SUCC',
    'aksk' => BigappEnv::getAkSkMd5(),
);

if ($verbose==1) {
    $s = BigappEnv::getAkSk();
    $ret["ak"] = $s["ak"];
    $ret["sk"] = $s["sk"];
}

echo BIGAPPJSON::encode($ret);
?>
