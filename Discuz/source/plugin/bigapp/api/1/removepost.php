<?php

/***********************************************************
 * @file:   removepost.php
 * @author: andrew(zhucb@youzu.com)
 * @create: 2015-11-10 10:19:42
 * @modify: 2015-11-10 15:19:42
 * @brief:  删除post[帖子详情页]
 ***********************************************************/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'topicadmin';
$_GET['action'] = 'delpost'; // 删除帖子回复

$_GET['modsubmit'] = 'yes';
$_GET['modclick'] = 'yes';
$_GET['infloat'] = 'yes';
$_GET['inajax'] = 1;

$_POST['modsubmit'] = 'true';
$_POST["handlekey"] = 'mods'; // 常量[默认赋予]
$_POST["formhash"] = isset($_POST['formhash']) ? $_POST['formhash']:'';

$_GET['topiclist'] = isset($_POST['pid']) ? $_POST['pid']:''; // 回帖编号
$_POST['fid'] = isset($_POST['fid']) ? $_POST['fid']:''; // 帖子板块
$_POST['tid'] = isset($_POST['tid']) ? $_POST['tid']:''; // 帖子编号

$_POST['page'] = isset($_POST['page']) ? $_POST['page']:''; // 所属页面
$_POST['reason'] = isset($_POST['reason']) ? $_POST['reason']:'';

include_once 'forum.php';

class BigAppAPI {

	public function common() {
	
	}

	public function output() {
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
