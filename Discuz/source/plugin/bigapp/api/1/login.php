<?php
/**
* @file login.php
* @Brief for uc login
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'logging';
$_GET['action'] = !empty($_GET['action']) ? $_GET['action'] : 'login';
include_once 'member.php';
require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';
class BigAppAPI {
	function common()
	{
		if($_GET['action'] == 'logout'){
			BigAppAPI::logout();
		}else if($_GET['action'] == 'login'){
			BigAppAPI::login();
		}
		echo BIGAPPJSON::encode(array('error_code' => 1, 'error_msg' => lang('plugin/bigapp', 'invalid_param'), 'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'invalid_param'))));
		die(0);
	}
	function logout()
	{
		global $_G;
		$ctlObj = new logging_ctl();
		$ctlObj->setting = $_G['setting'];
		clearcookies();
		$_G['groupid'] = $_G['member']['groupid'] = 7;
		$_G['uid'] = $_G['member']['uid'] = 0;
		$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
		$_G['setting']['styleid'] = $ctlObj->setting['styleid'];
		echo BIGAPPJSON::encode(array('error_code' => 0, 'error_msg' => lang('plugin/bigapp', 'logout_succ'), 
				'Message' => array('messageval' => 'logout_succeed', 'messagestr' => lang('plugin/bigapp', 'logout_succ')), 'Variables' => array('auth' => null)));
		die(0);	
	}
	function login() 
	{
		global $_G;
		
		$userName = null;
		$password = null;
		if(isset($_REQUEST['username'])){
			$userName = $_REQUEST['username'];
		}
		if(isset($_REQUEST['password'])){
			$password = $_REQUEST['password'];
		}	
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
			$userName = iconv('UTF-8', CHARSET . '//ignore', $userName);
			$answer = iconv('UTF-8', CHARSET . '//ignore', $answer);
		}else{
			$userName = mb_convert_encoding($userName, CHARSET, 'UTF-8');
			$answer = mb_convert_encoding($answer, CHARSET, 'UTF-8');
		}
		$_G['uid'] = $_G['member']['uid'] = 0;
		$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
		if(empty($userName) || empty($password) || $password != addslashes($password)){
			echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'invalid_param'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'invalid_param'))));
			die(0);
		}	
		require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';
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
			
			/*
			$sql = 'SELECT * FROM ' . DB::table('common_member') . " WHERE username = '${userName}'";
			$query = DB::query($sql);
			$userInfo = array();
			while($tmp = DB::fetch($query)) {
				$userInfo = $tmp;
				break;
			}
			
			if(empty($userInfo)){
				echo BIGAPPJSON::encode(array('error_code' => 4, 'error_msg' => lang('plugin/bigapp', 'user_not_exists'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'user_not_exists'))));
				die(0);
			}else */
			//if(!empty($answer)){
				echo BIGAPPJSON::encode(array('error_code' => 9, 'error_msg' => lang('plugin/bigapp', 'user_seq_question'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'user_seq_question'))));
				die(0);
			//}
			/*
			$result['ucresult']['uid'] = $userInfo['uid'];
			$result['member'] = $userInfo;
			$result['status'] = 1;
			*/
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
			echo BIGAPPJSON::encode(array('error_code' => 0, 'error_msg' => lang('plugin/bigapp', 'login_succ'), 
					'data' => $result['member'], 'Message' => array('messageval' => 'login_succeed', 
					'messagestr' => lang('plugin/bigapp', 'login_succ')), 'Variables' => array('auth' => 'in order to be comapatible')));
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
	}
}
?>
