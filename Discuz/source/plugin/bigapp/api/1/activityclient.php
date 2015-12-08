<?php
/**
* @file activityclient.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-10-20
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'misc';
$_GET['action'] = 'activityapplies';
$_GET['inajax'] = 1;

$_config['cookie']['cookiepre'];
$reg = "/^".$_config['cookie']['cookiepre']."(.*)mobile$/i";
foreach($_COOKIE as $key => $val) {
    if (preg_match($reg, $key) && 'no' === $val) {
        setcookie($key, '', 0);
    }   
}
include_once 'forum.php';

class BigAppAPI {

	public function common() 
    {
	}

	public function output() 
    {
		$variable = array();
		bigapp_core::result(bigapp_core::variable($variable));
	}

}
?>
