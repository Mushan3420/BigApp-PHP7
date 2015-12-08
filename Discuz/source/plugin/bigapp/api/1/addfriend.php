<?php

/***********************************************************
 * @file:   addfriend.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-07-13 14:34:14
 * @modify: 2015-07-13 14:34:14
 * @brief:  申请 添加 好友
 ***********************************************************/


if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'friend';
$_GET['op'] = 'add';
$_GET['uid'] = $_REQUEST['uid'];
$_GET['inajax'] = 1;

///////////////////////////////////////////
//fix bug 2287, 删除mobile cookie
$_config['cookie']['cookiepre'];
$reg = "/^".$_config['cookie']['cookiepre']."(.*)mobile$/i";
foreach($_COOKIE as $key => $val) {
    if (preg_match($reg, $key) && 'no' === $val) {
        setcookie($key, '', 0);
    }   
}
///////////////////////////////////////////

if (isset($_GET['check'])) {
	$_GET['infloat'] = 'yes';
} else {
    $_POST['note'] = $_REQUEST['note'];
	$_POST['gid'] = isset($_REQUEST['gid']) ? $_REQUEST['gid'] : 1;
    $_POST["referer"] = "forum.php";
    $_POST["addsubmit"] = "true";
    $_POST["addsubmit_btn"] = "true";
    $_POST["handlekey"] = "friend_".$_GET['uid'];
    $_POST["formhash"] = isset($_REQUEST['formhash']) ? $_REQUEST['formhash'] : "";
}
include_once 'home.php';


class BigAppAPI {

	public function common() 
    {
	}

	public function output() 
    {
        if (isset($_GET['check'])) {
            self::checkOutput();
        } else {
            self::addOutput();
        }
	}

    //////////////////////////////////////
	private function addOutput()
    {
 		global $_G;
		$messageval = $_G['messageparam'][0];
		$variable = array (
            "status" => 1,
            "messageval" => $messageval,
			"show_message" => preg_replace ("'<script[^>]*?>.*?</script>'si", "", $GLOBALS['show_message']),
		);
		if ($messageval == 'request_has_been_sent') {
            $variable["status"] = 0;
			if(isset($_G['setting']['bigapp_push_config'])  && is_string($_G['setting']['bigapp_push_config'])){
				$_G['setting']['bigapp_push_config'] = unserialize($_G['setting']['bigapp_push_config']);
			}
			if(!isset($_G['setting']['bigapp_push_config']['push_enabled'])){
				$_G['setting']['bigapp_push_config']['push_enabled'] = 1;
			} 	
			if($_G['setting']['bigapp_push_config']['push_enabled']){
				$sql = 'SELECT uid, username FROM ' . DB::table('common_member') . ' WHERE uid IN (' . $_G['uid'] . ', ' . $_GET['uid'] . ')';
				$query = DB::query($sql);
				$uid = null;
				$touid = null;
				$user = null;
				$touser = null;
				while($tmp = DB::fetch($query)){
					if($tmp['uid'] === $_G['uid']){
						$uid = $_G['uid'];
						$user = $tmp['username'];
						continue;
					}
					$touid = $tmp['uid'];
					$touser = $tmp['username'];
				}
				if(!is_null($uid) && !is_null($touid) && !is_null($user) && !is_null($touser) && $uid != $touid){
					if(function_exists('iconv')){
           	        	$user = iconv(CHARSET, 'UTF-8//ignore', $user);
						$touser = iconv(CHARSET, 'UTF-8//ignore', $touser);
                	}else{
                    	$user = mb_convert_encoding($user, 'UTF-8', CHARSET);
                    	$touser = mb_convert_encoding($touser, 'UTF-8', CHARSET);
                	}	
					$title = '您收到好友请求';
                	$content = "用户 ${user} 请求添加您为好友，详情点击查看";
                	$extra = array('user' => '__DONT_DICONV_TO_UTF8___' . $user, 'touser' => '__DONT_DICONV_TO_UTF8___' . $touser, 'uid' => $uid, 'touid' => $touid);
                	require_once (dirname(dirname(dirname(__FILE__))) . '/libs/pushmsg.inc.php');
                	$ret = PushMsg::sendMessage($touid, $title, $content, 3, $extra, 1);
                	$result = 'fail';
                	if(true === $ret){
                    	$result = 'succ';
                	}
                	runlog('bigapp', "[mobile]try to send friend request message [ uid: $uid, touid: $touid, user: $user, touser: $touser, result: $result ].");		
				}
			}
        }
        bigapp_core::result(bigapp_core::variable($variable));
    }

    private function checkOutput()
    {
        ///////////////////////////////////////
        // check操作类型(1:加好友,2:同意好友请求,3:拒绝好友请求)
        $optype = isset($_GET["optype"]) ? $_GET["optype"] : 1;
        ///////////////////////////////////////

		//echo json_encode($GLOBALS); exit(0);
 		global $_G;
        $groups = array();
        if (isset($GLOBALS["groups"])) {
            foreach ($GLOBALS["groups"] as $gid => $gname) {
				$gname = preg_replace('/<.*?\>/', '', $gname);
                $groups[] = array("gid"=>$gid, "group"=>$gname);
            }   
        }
        if (isset($GLOBALS['show_message'])) {
			$messageval = isset($_G['messageparam'][0]) ? $_G['messageparam'][0] : "";
            $msg = preg_replace ("'<script[^>]*?>.*?</script>'si", "", $GLOBALS['show_message']);
            $variable = array (
                "status" => 1,
				"groups" => $groups,
                "show_message" => strip_tags($msg),
                "messageval" => $messageval,
            );
            /////////////////////////////////////////////////
            // 当前用户组无加好友权限，但是可以拒绝别人的请求
            if ($optype==3) {
                if ($messageval=="no_privilege_addfriend") {
                    $variable["status"] = 0;
                }
            }
            /////////////////////////////////////////////////
        } else {
            /////////////////////////////////////////////////
            // 检查该用户是否已向自己发出好友申请请求
            require_once libfile('function/friend');
            if (friend_request_check($_GET['uid'])) {
				$variable = array (
					"status" => 2,
					"groups" => $groups,
					"show_message" => iconv('UTF-8', CHARSET.'//ignore', '该用户已向您发出好友申请，请到新的好友页面审核'),
				);
            }
            ////////////////////////////////////////////////
            else {
				$variable = array (
					"status" => 0,
					"groups" => $groups,
					"show_message" => "",
				);
			}
        }
        bigapp_core::result(bigapp_core::variable($variable));
    }
    
}

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
