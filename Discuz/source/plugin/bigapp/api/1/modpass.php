<?php
/**
* @file newuser.php
* @Brief for mod password for user in uc
* @author youzu
* @version 1.0.0
* @date 2015-11-06
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'profile';
$_GET['inajax'] = 1;
$_GET['questionidnew'] = '';
require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';

define('APPTYPEID', 1);
define('CURSCRIPT', 'home');

require_once './source/class/class_core.php';
require_once './source/function/function_home.php';
$discuz = C::app();
$cachelist = array('magic','userapp','usergroups', 'diytemplatenamehome');
$discuz->cachelist = $cachelist;
$discuz->init();
$mod = getgpc('mod');
if(!in_array($mod,array('spacecp'))){
    die(0);
}
define('CURMODULE', $mod);
require_once libfile('function/spacecp');
require_once libfile('function/magic');
runhooks();

class BigAppAPI {
	function common() {
		global $_G;
	    $space = getuserbyuid($_G['uid']);
	    if(empty($space)) {
			echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'cant find user info'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'cant find user info'))));
            die(0);
        }
	    $membersql = $memberfieldsql = $authstradd1 = $authstradd2 = $newpasswdadd = '';
	    $setarr = array();
	    $emailnew = dhtmlspecialchars($_GET['emailnew']);
	    $ignorepassword = 0;
		if(empty($_GET['newpassword'])) {
			echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'empty_params'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'empty_params'))));
            die(0);
		}

        if(!isset($_GET['questionidnew']) || $_GET['questionidnew'] === '') {
            $_GET['questionidnew'] = $_GET['answernew'] = '';
        } else {
            $secquesnew = $_GET['questionidnew'] > 0 ? random(8) : '';
        }

        //密码强度，取消
        $strongpw = false;
        if($strongpw && !empty($_GET['newpassword']) && $_G['setting']['strongpw']) {
            $strongpw_str = array();
            if(in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $_GET['newpassword'])) {
                $strongpw_str[] = lang('member/template', 'strongpw_1');
            }
            if(in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $_GET['newpassword'])) {
                $strongpw_str[] = lang('member/template', 'strongpw_2');
            }
            if(in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $_GET['newpassword'])) {
                $strongpw_str[] = lang('member/template', 'strongpw_3');
            }
            if(in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['newpassword'])) {
                $strongpw_str[] = lang('member/template', 'strongpw_4');
            }
            if($strongpw_str) {
                showmessage(lang('member/template', 'password_weak').implode(',', $strongpw_str));
            }
        }
        if(!empty($_GET['newpassword']) && $_GET['newpassword'] != addslashes($_GET['newpassword'])) {
			echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'password illegal'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'password illegal'))));
        }
        if(!empty($_GET['newpassword']) && $_GET['newpassword'] != $_GET['newpassword2']) {
			echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'password notmatch'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'password notmatch'))));
            die(0);
        }

	    loaducenter();
        //检测email,可以省略
        if(false && $emailnew != $_G['member']['email']) {
            include_once libfile('function/member');
            checkemail($emailnew);
        }
        $ucresult = uc_user_edit(addslashes($_G['username']), $_GET['oldpassword'], $_GET['newpassword'], '', $ignorepassword, $_GET['questionidnew'], $_GET['answernew']);
        if($ucresult == -1) {
			echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'password wrong'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'password wrong'))));
            die(0);
        } elseif($ucresult == -4) {
			echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'email illegal'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'email illegal'))));
            die(0);
        } elseif($ucresult == -5) {
			echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'email domain illegal'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'email domain illegal'))));
            die(0);
        } elseif($ucresult == -6) {
			echo BIGAPPJSON::encode(array('error_code' => 2, 'error_msg' => lang('plugin/bigapp', 'email duplicate'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => 'for comaptible', 'messagestr' => lang('plugin/bigapp', 'email duplicate'))));
            die(0);
        }

        if(!empty($_GET['newpassword']) || $secquesnew) {
            $setarr['password'] = md5(random(10));
        }

        $authstr = false;
        if(false && $emailnew != $_G['member']['email']) {
            $authstr = true;
            emailcheck_send($space['uid'], $emailnew);
            dsetcookie('newemail', "$space[uid]\t$emailnew\t$_G[timestamp]", 31536000);
        }
        if($setarr) {
            if($_G['member']['freeze'] == 1) {
                $setarr['freeze'] = 0;
            }
            C::t('common_member')->update($_G['uid'], $setarr);
        }
        if($_G['member']['freeze'] == 2) {
            C::t('common_member_validate')->update($_G['uid'], array('message' => dhtmlspecialchars($_G['gp_freezereson'])));
        }
		echo BIGAPPJSON::encode(array('error_code' => 0, 'error_msg' => lang('plugin/bigapp', 'succ'), 
					'Variables' => array('auth' => null), 'Message' => array('messageval' => '', 'messagestr' => lang('plugin/bigapp', 'succ'))));

        die(0);
    }
}
?>
