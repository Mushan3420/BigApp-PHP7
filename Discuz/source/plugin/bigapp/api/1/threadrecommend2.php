<?php
/**
* @file threadrecommend2.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'misc';
$_GET['action'] = 'recommend';
include_once 'forum.php';

class BigAppAPI {

	function common() {
	}

	function output() {
		$variable = array();
		bigapp_core::result(bigapp_core::variable($variable));
	}

}

?>
