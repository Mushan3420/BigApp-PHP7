<?php

/***********************************************************
 * @file:   removethread.php
 * @author: andrew(zhucb@youzu.com)
 * @create: 2015-11-10 10:19:42
 * @modify: 2015-11-10 15:19:42
 * @brief:  删除thread[帖子主题]
 ***********************************************************/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

// 执行删除帖子主题
$_GET['mod'] = 'topicadmin';
$_GET['action'] = 'moderate';
$_GET['optgroup'] = 3;

$_GET['modsubmit'] = 'yes';
$_GET['infloat'] = 'yes';
$_GET['inajax'] = 1;

$_POST['operations'] = array('delete');
$_POST['modsubmit'] = 'true';
$_POST["handlekey"] = 'mods';

$_POST["formhash"] = isset($_REQUEST['formhash']) ? $_REQUEST['formhash']:"";
$_POST['reason'] = isset($_POST['reason']) ? $_POST['reason'] : '';

$_POST['moderate'] = isset($_POST['tid']) ? $_POST['tid'] : '';
$_POST['fid'] = isset($_POST['fid']) ? $_POST['fid'] : '';

/********* 
// 测试参数
$_POST['fid'] = 39;
$_POST['moderate'] = 10;
$_POST['reason'] = 'autodel';
$_POST['formhash'] = 'bb53831e';
$_SERVER['REQUEST_METHOD'] = 'POST';
*********/

include_once 'forum.php';


class BigAppAPI {

	public function common()
	{
	}

	public function output()
	{
		global $_G;
		$messageval = $_G['messageparam'][0];
		$variable = array (
				"status" => 0,
				"messageval" => $messageval,
				"show_message" => preg_replace ("'<script[^>]*?>.*?</script>'si", "", $GLOBALS['show_message']),
				);
		$errmsgvals = array('space_does_not_exist','submit_invalid');
		if (in_array($messageval,$errmsgvals)) {
			$variable["status"] = 1;
		}
		bigapp_core::result(bigapp_core::variable($variable));
	}
}

?>
