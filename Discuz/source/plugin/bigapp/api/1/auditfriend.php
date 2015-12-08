<?php

/***********************************************************
 * @file:   addfriend.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-07-13 14:34:14
 * @modify: 2015-07-13 14:34:14
 * @brief:  审核好友申请
 ***********************************************************/


if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$audit = $_REQUEST["audit"];

// 接�好友申请
if ($audit == 0) {
	$_GET['mod'] = 'spacecp';
	$_GET['ac'] = 'friend';
	$_GET['op'] = 'add';
	$_GET['uid'] = $_REQUEST['uid'];
	$_GET['inajax'] = 1;

    $_POST["referer"] = "forum.php";
    $_POST["add2submit"] = "true";
    $_POST["from"] = "";
    $_POST["handlekey"] = "afr_".$_GET["uid"];
    $_POST["formhash"] = isset($_REQUEST["formhash"]) ? $_REQUEST["formhash"] : "";
    $_POST["gid"] = isset($_REQUEST["gid"]) ? $_REQUEST["gid"] : 1;
    $_POST["add2submit_btn"] = "true";
}
// 拒绝好友申请
else {
	$_GET['mod'] = 'spacecp';
	$_GET['ac'] = 'friend';
	$_GET['op'] = 'ignore';
	$_GET['uid'] = $_REQUEST['uid'];
	$_GET['confirm'] = 1;
	$_GET['inajax'] = 1;

    $_POST["referer"] = "forum.php";
    $_POST["friendsubmit"] = "true";
    $_POST["formhash"] = isset($_REQUEST["formhash"]) ? $_REQUEST["formhash"] : "";
    $_POST["from"] = "";
    $_POST["handlekey"] = "afi_".$_GET["uid"];
    $_POST["friendsubmit_btn"] = "true";
}

include_once 'home.php';


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

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
