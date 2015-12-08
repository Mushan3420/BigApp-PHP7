<?php

/***********************************************************
 * @file:   report.php
 * @author: tangyangyu(tangyy@youzu.com)
 * @create: 2015-08-4 09:30:14
 * @brief:  提交举报内容
 ***********************************************************/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['rtype'] = $_REQUEST['rtype']; //post, thread
$_GET['rid'] = $_REQUEST['rid'];
$_GET['tid'] = $_REQUEST['tid'];
$_GET['fid'] = $_REQUEST['fid'];
$_GET['uid'] = $_REQUEST['uid'];

$_GET['mod'] = 'report';
$_GET['inajax'] = 1;

$_POST['report_select'] = $_REQUEST['report_select'];
$_POST['message'] = $_REQUEST['message'];
$_POST["referer"] = "forum.php";
$_POST["reportsubmit"] = "true";
$_POST["rtype"] = "thread";
$_POST["rid"] = $_REQUEST['rid'];
$_POST["fid"] = $_REQUEST['fid'];
$_POST["url"] = "";
$_POST['inajax'] = $_REQUEST['inajax'];
$_POST["handlekey"] = "miscreport".$_REQUEST['rid'];
$_POST["formhash"] = isset($_REQUEST['formhash']) ? $_REQUEST['formhash'] : "";

//var_dump($_POST) or die();

include_once 'misc.php';

class BigAppAPI {

	public function common() 
	{

	}
	
	public function output() 
	{
		global $_G;
		$messageval = $_G['messageparam'][0];
		
		$variable = array (
			"status" => 1,
			"message_val" => $messageval,
		);
		
		bigapp_core::result(bigapp_core::variable($variable));
	}
    
}

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
