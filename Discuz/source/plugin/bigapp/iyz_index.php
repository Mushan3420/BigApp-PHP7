<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.php 33969 2013-09-10 08:32:14Z nemohou $
 */
require_once dirname(__FILE__).'/addslashes.php';

$_GET['charset'] = 'UTF-8';
$oldRL = error_reporting();
if(defined(E_DEPRECATED)){
	error_reporting($oldRL & ~E_DEPRECATED & ~E_STRICT);
}else{
	error_reporting($oldRL & ~E_STRICT);
}
foreach ($_SERVER as &$v){
	$v = str_replace('source/plugin/bigapp/', 'api/mobile/', $v);
}
unset($v);
if(!empty($_SERVER['QUERY_STRING'])) {
	$plugin = !empty($_GET['oem']) ? 'mobileoem' : 'mobile';
	$file = 'mobile.php';
	if(isset($_GET['iyzmobile']) && $_GET['iyzmobile']){
		$plugin = 'bigapp';
		$file = 'bigapp.php';
	}
	$dir = '../' . $plugin . '/';
	if(!is_dir($dir)){
		echo "such directory does not exists [ $dir ].";
		die(0);
	}
	if(!defined('DISABLEDEFENSE')){
		define('DISABLEDEFENSE', 1);
	}
	chdir($dir);
	if((isset($_GET['check']) && $_GET['check'] == 'check' || $_SERVER['QUERY_STRING'] == 'check') && is_file('check.php')) {
		$file = 'check.php';
	}
	if(is_file($file)){
		require_once $file;
		die(0);
	}
	echo "such file does not exists [ ${dir}${$file} ].";
}

?>
