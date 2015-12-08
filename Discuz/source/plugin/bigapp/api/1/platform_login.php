<?php

/***********************************************************
 * @file:   platform_login.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-08-04 16:10:28
 * @modify: 2015-08-04 16:10:28
 * @brief:  第三方登录（后）接口
 ***********************************************************/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
define('APPTYPEID', 0);
define('CURSCRIPT', 'member');
define("CUR_PATH", dirname(__FILE__));
require './source/class/class_core.php';
require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';
$discuz = C::app();
$discuz->init();

$mod = $_GET["mod"];
$support_mods = array("check","login","register");
if (!in_array($mod, $support_mods)) {
    bigapp_core::result(array('error' => 'mod_is_illegal'));
}
$_GET["platform"] = strtolower($_GET["platform"]);
$platform = $_GET["platform"];
$support_platform = array("qq","wechat");
if (!in_array($platform, $support_platform)) {
    bigapp_core::result(array('error' => 'platform_is_illegal'));
}
if (!isset($_GET["openid"]) || $_GET["openid"]=="") {
    bigapp_core::result(array('error' => 'openid_is_empty'));
}
if (!isset($_GET["token"]) || $_GET["token"]=="") {
    bigapp_core::result(array('error' => 'token_is_empty'));
}
$mod(); 


// 验证openid是否已绑定会员
function check()
{
    $plat = $_GET["platform"];
    if ($plat == "qq") {
        include_once(CUR_PATH."/../qqconnect/check.php");
    }
    if ($plat == "wechat") {
        include_once(CUR_PATH."/../wechatconnect/check.php");
    }
	$variable["message"] = "unkown_plat";
	bigapp_core::result(bigapp_core::variable($variable));
}

// 登录绑定
function login()
{/*{{{*/
	require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';
    $username = isset($_REQUEST["username"]) ? $_REQUEST["username"] : "";
    $password = isset($_REQUEST["password"]) ? $_REQUEST["password"] : "";

	global $_G;
	$_GET['username'] = $username;
	$_GET['password'] = $password;
    ////////////////////////////////////////////
	//$_GET['questionid'] = $_GET['answer'] = '';
	if(isset($_REQUEST['questionid'])){
		$questionid = intval($_REQUEST['questionid']);
	}else{
		$questionid = 0;
	}
	if(isset($_REQUEST['answer'])){
		$answer = $_REQUEST['answer'];
	}else{
		$answer = '';
	}
	if(function_exists('iconv')){
		$userName = iconv('UTF-8', CHARSET . '//ignore', $username);
		$answer = iconv('UTF-8', CHARSET . '//ignore', $answer);
	}else{
		$userName = mb_convert_encoding($username, CHARSET, 'UTF-8');
		$answer = mb_convert_encoding($answer, CHARSET, 'UTF-8');
	}
    ////////////////////////////////////////////
	$_GET['loginfield'] = 'username';

	require_once libfile('function/member');
	require_once libfile('class/member');
	require_once libfile('function/misc');
	require_once libfile('function/mail');

	loaducenter();
	if(!($_G['member_loginperm'] = logincheck($userName))) {
		echo BIGAPPJSON::encode(array('error_code' => 3, 'error_msg' => lang('plugin/bigapp', 'too_many_errors'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'too_many_errors'))));
		die(0);
	}
	$result = userlogin($userName, $password, $questionid, $answer, 'username', $_G['clientip']);
	if ($result['ucresult']['uid'] == '-3') {
		echo BIGAPPJSON::encode(array('error_code' => 9, 'error_msg' => lang('plugin/bigapp', 'user_seq_question'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'user_seq_question'))));
		die(0);
	}
	$uid = $_G['uid'] = $result['ucresult']['uid'];
	$userName = $result['ucresult']['username'];
	$userAvatar = avatar($_G['uid'], 'big', true);
	$userAvatar = str_replace("\r", '', $userAvatar);
	$userAvatar = str_replace("\n", '', $userAvatar);
	$ctlObj = new logging_ctl();
	$ctlObj->setting = $_G['setting'];
	if($result['status'] == -1) {
		if(!$ctlObj->setting['fastactivation']) {
			echo BIGAPPJSON::encode(array('error_code' => 5, 'error_msg' => lang('plugin/bigapp', 'activate_first'), 
						'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'login_failed'))));
			die(0);
		}
		$init_arr = explode(',', $ctlObj->setting['initcredits']);
		$groupid = $ctlObj->setting['regverify'] ? 8 : $ctlObj->setting['newusergroupid'];
		C::t('common_member')->insert($uid, $result['ucresult']['username'], md5(random(10)), $result['ucresult']['email'], $_G['clientip'], $groupid, $init_arr);
		$result['member'] = getuserbyuid($uid);
		$result['status'] = 1;
	}
	if($result['status'] > 0) {
		if($ctlObj->extrafile && file_exists($ctlObj->extrafile)) {
			require_once $ctlObj->extrafile;
		}
		setloginstatus($result['member'], $_GET['cookietime'] ? 2592000 : 0);
		checkfollowfeed();
		C::t('common_member_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' =>TIMESTAMP, 'lastactivity' => TIMESTAMP));
		if(isset($result['member']['password'])){
			unset($result['member']['password']);
		}
		if(isset($result['member']['credits'])){
			unset($result['member']['credits']);
		}

		/////////////////////////////////////////////////
		// 登录成功，进行绑定
        $plat = $_GET["platform"];
		if ($plat == "qq") {
			include_once(CUR_PATH."/../qqconnect/bind.php");
		} else if ($plat=='wechat') {
			include_once(CUR_PATH."/../wechatconnect/bind.php");
        }
        /////////////////////////////////////////////////

		echo BIGAPPJSON::encode(array('error_code' => 0, 'error_msg' => lang('plugin/bigapp', 'bind_succ'), 
					'data' => $result['member'], 'Message' => array('messageval' => 'login_succeed', 
						'messagestr' => lang('plugin/bigapp', 'bind_succ')), 'Variables' => array('auth' => 'in order to be comapatible')));
		die(0);
	}
	if($_G['member_loginperm'] > 1) {
		echo BIGAPPJSON::encode(array('error_code' => 6, 'error_msg' => lang('plugin/bigapp', 'login_failed'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'login_failed'))));
	}elseif($_G['member_loginperm'] == -1) {
		echo BIGAPPJSON::encode(array('error_code' => 7, 'error_msg' => lang('plugin/bigapp', 'error_password'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'error_password'))));
	}else{
		echo BIGAPPJSON::encode(array('error_code' => 8, 'error_msg' => lang('plugin/bigapp', 'too_many_errors'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'too_many_errors'))));
	}
	die(0);
}/*}}}*/

// 注册绑定
function register()
{/*{{{*/
	require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';
	require_once libfile('function/misc');
	require_once libfile('function/member');
	require_once libfile('class/member');

    $userName = isset($_REQUEST["username"]) ? $_REQUEST["username"] : "";
    $password = isset($_REQUEST["password"]) ? $_REQUEST["password"] : "";
    $email    = isset($_REQUEST["email"]) ? strtolower($_REQUEST["email"]) : "";
	global $_G;
	if(function_exists('iconv')){
		$userName = iconv('UTF-8', CHARSET . '//ignore', $userName);
	}else{
		$userName = mb_convert_encoding($userName, CHARSET, 'UTF-8');
	}
	if(empty($userName) || empty($password) || empty($email)){
		echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'empty_params'), 
				'Variables' => array('auth' => null),
                 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'empty_params'))));
		die(0);
	}
	$userNamelen = dstrlen($userName);
	if($userNamelen < 3) {
		echo BIGAPPJSON::encode(array('error_code' => 3, 'error_msg' => lang('plugin/bigapp', 'username_short'), 
			'Variables' => array('auth' => null),
            'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'username_short'))));
		die(0);
	}
	if($userNamelen > 15) {
		echo BIGAPPJSON::encode(array('error_code' => 4, 'error_msg' => lang('plugin/bigapp', 'username_long'), 
			'Variables' => array('auth' => null),
            'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'username_long'))));	
		die(0);
	}


	$ctlObj = new register_ctl();
	$ctlObj->setting = $_G['setting'];
	if(isset($ctlObj->setting['pwlength']) && $ctlObj->setting['pwlength']) {
		if(strlen($password) < $ctlObj->setting['pwlength']) {
			echo BIGAPPJSON::encode(array('error_code' => 5, 'error_msg' => lang('plugin/bigapp', 'password_length') . 
				' [ >= ' . $ctlObj->setting['pwlength'] . ' ]', 'Variables' => array('auth' => null), 
				'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'password_not_equal') . 
				' [ >= ' . $ctlObj->setting['pwlength'] . ' ]')));
			die(0);
		}
	}
	if(isset($ctlObj->setting['strongpw']) && $ctlObj->setting['strongpw']) {
		$strongpw_str = array();
		if(in_array(1, $ctlObj->setting['strongpw']) && !preg_match("/\d+/", $password)) {
			$strongpw_str[] = lang('plugin/bigapp', 'password_number');
		}
		if(in_array(2, $ctlObj->setting['strongpw']) && !preg_match("/[a-z]+/", $password)) {
			$strongpw_str[] = lang('plugin/bigapp', 'password_lowercase_char');
		}
		if(in_array(3, $ctlObj->setting['strongpw']) && !preg_match("/[A-Z]+/", $password)) {
			$strongpw_str[] = lang('plugin/bigapp', 'password_uppercase_char');
		}
		if(in_array(4, $ctlObj->setting['strongpw']) && !preg_match("/[^a-zA-Z0-9]+/", $password)) {
			$strongpw_str[] = lang('plugin/bigapp', 'password_charset');
		}
		if($strongpw_str) {
			echo BIGAPPJSON::encode(array('error_code' => 6, 'error_msg' => lang('plugin/bigapp', 'password_invalid') . 
				' [ ' . implode(', ', $strongpw_str) . ' ]', 'Variables' => array('auth' => null), 
				'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'password_invalid') . 
				' [ ' . implode(', ', $strongpw_str) . ' ]')));
			die(0);
		}
	}
/*
	if(!isset($_G['setting']['mobile']['mobileregister']) || !$_G['setting']['mobile']['mobileregister']){
		echo BIGAPPJSON::encode(array('error_code' => 7, 'error_msg' => lang('plugin/bigapp', 'forbid_mobreg'), 
				'Variables' => array('auth' => null),
                'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'forbid_mobreg'))));
		die(0);
	}
*/
	loaducenter();
	if(!$ctlObj->setting['regclosed'] && (!$ctlObj->setting['regstatus'] || !$ctlObj->setting['ucactivation'])) {
		if(!$ctlObj->setting['regstatus']) {
			echo BIGAPPJSON::encode(array('error_code' => 8, 'error_msg' => lang('plugin/bigapp', 'forbid_registration'), 
						'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 
						'messagestr' => lang('plugin/bigapp', 'forbid_registration'))));
			die(0);
		}
	}
	if($ctlObj->setting['regverify']) {
		if($ctlObj->setting['areaverifywhite']) {
			$location = $whitearea = '';
			$location = trim(convertip($_G['clientip'], "./"));
			if($location) {
				$whitearea = preg_quote(trim($ctlObj->setting['areaverifywhite']), '/');
				$whitearea = str_replace(array("\\*"), array('.*'), $whitearea);
				$whitearea = '.*'.$whitearea.'.*';
				$whitearea = '/^('.str_replace(array("\r\n", ' '), array('.*|.*', ''), $whitearea).')$/i';
				if(@preg_match($whitearea, $location)) {
					$ctlObj->setting['regverify'] = 0;
				}
			}
		}
		
		if($_G['cache']['ipctrl']['ipverifywhite']) {
			foreach(explode("\n", $_G['cache']['ipctrl']['ipverifywhite']) as $ctrlip) {
				if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
					$ctlObj->setting['regverify'] = 0;
					break;
				}
			}
		}
	}
	if($ctlObj->setting['regverify']) {
		$groupinfo['groupid'] = 8;
	} else {
		$groupinfo['groupid'] = $ctlObj->setting['newusergroupid'];
	}
	if(!$password || $password != addslashes($password)) {
		echo BIGAPPJSON::encode(array('error_code' => 9, 'error_msg' => lang('plugin/bigapp', 'password_invalid_char'), 
				'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 
				'messagestr' => lang('plugin/bigapp', 'password_invalid_char'))));
		die(0);
	}
	$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), 
				preg_quote(($ctlObj->setting['censoruser'] = trim($ctlObj->setting['censoruser'])), '/')).')$/i';
		if($ctlObj->setting['censoruser'] && @preg_match($censorexp, $userName)) {
			echo BIGAPPJSON::encode(array('error_code' => 10, 'error_msg' => lang('plugin/bigapp', 'forbid_username'), 
				'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'forbid_username'))));
			die(0);
		}
		if($_G['cache']['ipctrl']['ipregctrl']) {
			foreach(explode("\n", $_G['cache']['ipctrl']['ipregctrl']) as $ctrlip) {
				if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
					$ctrlip = $ctrlip.'%';
					$ctlObj->setting['regctrl'] = $ctlObj->setting['ipregctrltime'];
					break;
				} else {
					$ctrlip = $_G['clientip'];
				}
			}
		} else {
			$ctrlip = $_G['clientip'];
		}
		if($ctlObj->setting['regctrl']) {
			if(C::t('common_regip')->count_by_ip_dateline($ctrlip, $_G['timestamp']-$ctlObj->setting['regctrl']*3600)) {
				echo BIGAPPJSON::encode(array('error_code' => 11, 'error_msg' => lang('plugin/bigapp', 'forbid_ip'), 
					'Variables' => array('auth' => null),
                    'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'forbid_ip'))));
				die(0);
			}
		}
		
		$setregip = null;
		if($ctlObj->setting['regfloodctrl']) {
			$regip = C::t('common_regip')->fetch_by_ip_dateline($_G['clientip'], $_G['timestamp']-86400);
			if($regip) {
				if($regip['count'] >= $ctlObj->setting['regfloodctrl']) {
					echo BIGAPPJSON::encode(array('error_code' => 12, 'error_msg' => lang('plugin/bigapp', 'forbid_ip_today'), 
							'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 
							'messagestr' => lang('plugin/bigapp', 'forbid_ip_today'))));
					die(0);
				} else {
					$setregip = 1;
				}
			} else {
				$setregip = 2;
			}
		}
		$uid = uc_user_register($userName, $password, $email, '', '', $_G['clientip']);
		if($uid <= 0) {
			if($uid == -1) {
				echo BIGAPPJSON::encode(array('error_code' => 13, 'error_msg' => lang('plugin/bigapp', 'username_invalid_char'), 
						'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 
						'messagestr' => lang('plugin/bigapp', 'username_invalid_char'))));
			} elseif($uid == -2) {
				echo BIGAPPJSON::encode(array('error_code' => 13, 'error_msg' => lang('plugin/bigapp', 'username_invalid_char'), 
						'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 
						'messagestr' => lang('plugin/bigapp', 'username_invalid_char'))));
			} elseif($uid == -3) {
				echo BIGAPPJSON::encode(array('error_code' => 13, 'error_msg' => lang('plugin/bigapp', 'username_used'), 
						'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 
						'messagestr' => lang('plugin/bigapp', 'username_used'))));
			} elseif($uid == -4) {
				echo BIGAPPJSON::encode(array('error_code' => 13, 'error_msg' => lang('plugin/bigapp', 'invalid_email'), 
						'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 
						'messagestr' => lang('plugin/bigapp', 'invalid_email'))));
			} elseif($uid == -5) {
				echo BIGAPPJSON::encode(array('error_code' => 13, 'error_msg' => lang('plugin/bigapp', 'invalid_email'), 
						'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 
						'messagestr' => lang('plugin/bigapp', 'invalid_email'))));
			} elseif($uid == -6) {
				echo BIGAPPJSON::encode(array('error_code' => 13, 'error_msg' => lang('plugin/bigapp', 'email_used'), 
						'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 
						'messagestr' => lang('plugin/bigapp', 'email_used'))));
			}
			die(0);
		}
		$_G['username'] = $userName;
		$password = md5(random(10));
		if($setregip !== null) {
			if($setregip == 1) {
				C::t('common_regip')->update_count_by_ip($_G['clientip']);
			} else {
				C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => 1, 'dateline' => $_G['timestamp']));
			}
		}
		$profile = $verifyarr = array ();
		$emailstatus = 0;
		$init_arr = array('credits' => explode(',', $ctlObj->setting['initcredits']), 'profile'=>$profile, 'emailstatus' => $emailstatus);
		C::t('common_member')->insert($uid, $userName, $password, $email, $_G['clientip'], $groupinfo['groupid'], $init_arr);
		if($ctlObj->setting['regctrl'] || $ctlObj->setting['regfloodctrl']) {
			C::t('common_regip')->delete_by_dateline($_G['timestamp']-($ctlObj->setting['regctrl'] > 72 ? $ctlObj->setting['regctrl'] : 72)*3600);
			if($ctlObj->setting['regctrl']) {
				C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => -1, 'dateline' => $_G['timestamp']));
			}
		}
		if($ctlObj->setting['regverify'] == 1) {
			$idstring = random(6);
			$authstr = $ctlObj->setting['regverify'] == 1 ? "$_G[timestamp]\t2\t$idstring" : '';
			C::t('common_member_field_forum')->update($uid, array('authstr' => $authstr));
			$verifyurl = "{$_G[siteurl]}member.php?mod=activate&amp;uid=$uid&amp;id=$idstring";
			$email_verify_message = lang('email', 'email_verify_message', array(
						'username' => $username,
						'bbname' => $ctlObj->setting['bbname'],
						'siteurl' => $_G['siteurl'],
						'url' => $verifyurl
						));
			if(!sendmail("$username <$email>", lang('email', 'email_verify_subject'), $email_verify_message)) {
				runlog('sendmail', "$email sendmail failed.");
			}
		}
		require_once libfile('cache/userstats', 'function');
		build_cache_userstats();
		$_GET['regmessage'] = 'from bigapp client';
		$regmessage = dhtmlspecialchars($_GET['regmessage']);
		if($ctlObj->setting['regverify'] == 2) {
			C::t('common_member_validate')->insert(array(
						'uid' => $uid,
						'submitdate' => $_G['timestamp'],
						'moddate' => 0,
						'admin' => '',
						'submittimes' => 1,
						'status' => 0,
						'message' => $regmessage,
						'remark' => '',
						), false, true);
			manage_addnotify('verifyuser');
		}
		setloginstatus(array(
			'uid' => $uid,
			'username' => $_G['username'],
			'password' => $password,
			'groupid' => $groupinfo['groupid'],
		), 0);
		include_once libfile('function/stat');
		updatestat('register');
		checkfollowfeed();
		C::t('common_member_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' =>TIMESTAMP, 'lastactivity' => TIMESTAMP));

        ////////////////////////////////////////////////
        // 注册成功，绑定第三方openid
        $plat = $_GET["platform"];
		if ($plat == "qq") {
			include_once(CUR_PATH."/../qqconnect/bind.php");
		}
        else if ($plat == "wechat") {
            include_once(CUR_PATH."/../wechatconnect/bind.php");
        }
        ////////////////////////////////////////////////
		echo BIGAPPJSON::encode(array('error_code' => 0, 'error_msg' => lang('plugin/bigapp', 'regist_succ'), 
				'Message' => array('messageval' => 'register_succeed', 'messagestr' => lang('plugin/bigapp', 'regist_succ')), 
				'Variables' => array('auth' => 'in order to be comapatible')));  
		die(0);
}/*}}}*/



// vim600: sw=4 ts=4 fdm=marker syn=php
?>
