<?php
/**
* @file comment.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-10-20
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'post';
$_GET['action'] = 'reply';
$_GET['comment'] = 'yes';
$_GET['infloat'] = 'yes';
$_GET['inajax'] = 1;
$_POST['commentsubmit'] = $_GET['commentsubmit'] = 'yes';
$_POST['handlekey'] = 'comment';
//$_POST['formhash'] = 'd99611c2';
include_once 'forum.php';

class BigAppAPI {

	public function common() 
    {
		
	}

	public function output() 
    {
		$variable = array();
		global $_G;
		bigapp_core::result(bigapp_core::variable($variable));
	}

}
?>
