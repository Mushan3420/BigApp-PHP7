<?php
/**
* @file newuser.php
* @Brief for create a new user in uc
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
$_GET['mod'] = 'register';
$_GET['handlekey'] = 'registerform';
$_GET['inajax'] = 1;
require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';

define('APPTYPEID', 0);
define('CURSCRIPT', 'member');
require 'source/class/class_core.php';
$discuz = C::app();
$modarray = array('activate', 'clearcookies', 'emailverify', 'getpasswd',
    'groupexpiry', 'logging', 'lostpasswd',
    'register', 'regverify', 'switchstatus');
$mod = !in_array($discuz->var['mod'], $modarray) && (!preg_match('/^\w+$/', $discuz->var['mod']) || !file_exists(DISCUZ_ROOT.'./source/module/member/member_'.$discuz->var['mod'].'.php')) ? 'register' : $discuz->var['mod'];
define('CURMODULE', $mod);
$discuz->init();
require libfile('function/member');
require libfile('class/member');
runhooks();

class BigAppAPI {
	function common()
	{
		$userName = null;
		$password = null;
		$email = null;
		global $_G;
		if(isset($_REQUEST['un']) && !empty($_REQUEST['un'])){
			$userName = $_REQUEST['un'];
		}
		if(function_exists('iconv')){
			$userName = iconv('UTF-8', CHARSET . '//ignore', $userName);
		}else{
			$userName = mb_convert_encoding($userName, CHARSET, 'UTF-8');
		}
		if(isset($_REQUEST['pd']) && !empty($_REQUEST['pd'])){
			$password = $_REQUEST['pd'];
			if(!isset($_REQUEST['pd2']) || $_REQUEST['pd2'] != $_REQUEST['pd']){
				echo BIGAPPJSON::encode(array('error_code' => 1, 'error_msg' => lang('plugin/bigapp', 'password_not_equal'), 
						'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 
						'messagestr' => lang('plugin/bigapp', 'password_not_equal'))));
				die(0);
			}
		}
		if(isset($_REQUEST['em']) && !empty($_REQUEST['em'])){
			$email = strtolower($_REQUEST['em']);
		}
		if(empty($userName) || empty($password) || empty($email)){
			echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'empty_params'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'empty_params'))));
			die(0);
		}
		$userNamelen = dstrlen($userName);
		if($userNamelen < 3) {
			echo BIGAPPJSON::encode(array('error_code' => 3, 'error_msg' => lang('plugin/bigapp', 'username_short'), 
				'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'username_short'))));
			die(0);
		}
		if($userNamelen > 15) {
			echo BIGAPPJSON::encode(array('error_code' => 4, 'error_msg' => lang('plugin/bigapp', 'username_long'), 
				'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'username_long'))));	
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
		if(!isset($_G['setting']['mobile']['mobileregister']) || !$_G['setting']['mobile']['mobileregister']){
			echo BIGAPPJSON::encode(array('error_code' => 7, 'error_msg' => lang('plugin/bigapp', 'forbid_mobreg'), 
				'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'forbid_mobreg'))));
			die(0);
		}
		require_once libfile('function/misc');
		require_once libfile('function/member');
		require_once libfile('class/member');
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
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'forbid_ip'))));
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
		echo BIGAPPJSON::encode(array('error_code' => 0, 'error_msg' => lang('plugin/bigapp', 'regist_succ'), 
				'Message' => array('messageval' => 'register_succeed', 'messagestr' => lang('plugin/bigapp', 'regist_succ')), 
				'Variables' => array('auth' => 'in order to be comapatible')));  
		die(0);
	}
}
?>
