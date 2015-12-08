<?php

/***********************************************************
 * @file:   removefriend.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-07-15 15:19:42
 * @modify: 2015-07-15 15:19:42
 * @brief:  删除好友
 ***********************************************************/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'friend';
$_GET['op'] = 'ignore';
$_GET['uid'] = $_REQUEST['uid'];
$_GET['confirm'] = 1;
$_GET['inajax'] = 1;

$_POST['friendsubmit'] = true;
$_POST['friendsubmit_btn'] = true;
$_POST["referer"] = "forum.php";
$_POST["handlekey"] = "a_ignore_".$_GET['uid'];
$_POST["formhash"] = isset($_REQUEST['formhash']) ? $_REQUEST['formhash'] : "";
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
