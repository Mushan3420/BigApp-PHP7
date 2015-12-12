<?php
if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}

require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

if ($_GET["action"]=="logout") MobileLoginAPI::logout();
else MobileLoginAPI::login();

class MobileLoginAPI
{
    public static function login()
    {
        //1. 校验验证码
        require_once LIB_PATH."/seccode.php";
        if (!DzSecCode::check()) {
            DzEnv::error_result("error_seccode");
        }
        //2. 请求参数校验
		global $_G;
        $username = DzEnv::get_param("username",null,'POST');
        $password = DzEnv::get_param("password",null,'POST');
        $questionid = DzEnv::get_param("questionid", 0,'POST');
        $answer = DzEnv::get_param("answer", "",'POST');
        if (!$username || !$password) {
            DzEnv::error_result("invalid_param");
        }
		$username = iconv('UTF-8', CHARSET.'//ignore', urldecode($username));
		$answer = iconv('UTF-8', CHARSET.'//ignore', $answer);
        //3. 如果是手机号，找到对应的username
        if (DzValidate::is_phone($username)) {
            $res=C::t("#login_mobile#mobile_login_connection")->getUserName($username);
            if ($res!==false) {
                $username = $res;
            }
        }
        //4. 登录校验
        require_once LIB_PATH."/uc.php";
        $uid = DzUc::logincheck($username, $password, $questionid, $answer);
        if (!is_numeric($uid)) {
            DzEnv::error_result($uid);
        }
        //5. 登录
        DzUc::dologin($uid);
        $result = array (
            "retcode"=>0,
            "retmsg"=>lang("plugin/login_mobile","login_succeed"),
            "username" => $username,
            "uid" => $uid,
        );
        DzEnv::result($result);
    }

    public static function logout()
    {
        require_once LIB_PATH."/uc.php";
        DzUc::dologout();
		$_G['groupid'] = $_G['member']['groupid'] = 7;
		$_G['uid'] = $_G['member']['uid'] = 0;
		$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
        $result = array(
            "retcode" => 0,
            "retmsg" => "logout success",
        );
        DzEnv::result($result);
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
