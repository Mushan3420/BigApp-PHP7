<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once dirname(__FILE__).'/libs/env.php';

class plugin_login_mobile
{
    public function common()
    {
    }

    public function global_usernav_extra1()
    { 
        if (!DzEnv::isEnablePc()) return "";
        global $_G;
        if (!$_G['uid']) return "";
        $username = $_G["username"];
        $phone = C::t("#login_mobile#mobile_login_connection")->getPhone($username);
        if ($phone===false) {
            $siteurl = rtrim($_G['siteurl'], '/');
            $url = $siteurl."/home.php?mod=spacecp&ac=plugin&id=login_mobile:home_binding";
            $res = "<span class='pipe'>|</span><a href='$url'><img src='$siteurl/source/plugin/login_mobile/template/static/sjbd.png' align='absmiddle' style='border-radius:2px;'/></a>&nbsp;";
            return $res;
        }
        return "";
    }
}

class plugin_login_mobile_member extends plugin_login_mobile
{
    function logging()
    {
        if (!DzEnv::isEnablePc()) return;
        if ($_GET["action"]=="login" && $_REQUEST["username"]!="") {
            $username = $_REQUEST["username"];
            require_once dirname(__FILE__)."/libs/validate.php";
            if (DzValidate::is_phone($username)) {
				$res=C::t("#login_mobile#mobile_login_connection")->getUserName($username);
				if ($res!==false) {
					$username = $res;
					$_POST["username"] = $_GET["username"] = $_REQUEST["username"] = $username;
				}
            }
        }
        else if ($_GET["action"]=="login" && $_GET['viewlostpw']==1 && $_GET['infloat']=="yes") {
			global $_G;
            $vars = array();
            $tplVars = array(
                "plugin_path" => DzEnv::getPluginPath(),
                "ajax_api" => DzEnv::getSiteUrl()."/source/plugin/login_mobile/index.php?version=4",
            );
            echo MobileLogin_Utils::getTemplate(dirname(__FILE__)."/template/findpass.tpl",$vars,$tplVars);
            die(0);
        }
    }

    function register_input()
    {
        if (!DzEnv::isEnablePc()) return;
        global $_G;
        $vars = array();
        $tplVars = array(
            "plugin_path" => DzEnv::getPluginPath(),
            "ajax_api" => DzEnv::getSiteUrl()."/source/plugin/login_mobile/index.php?version=4",
        );
        return MobileLogin_Utils::getTemplate(dirname(__FILE__)."/template/regist.tpl",$vars,$tplVars);
    }

    function register()
    {
        if (!DzEnv::isEnablePc()) return;
        if (isset($_GET["inajax"]) && $_GET["inajax"]==1) {
			global $_G;
            $phone = $_POST["phone_aKnMp"];
            $pcode = $_POST["smscode"];
            if (!C::t("#login_mobile#mobile_login_seccode")->check($phone,$pcode)) {
                showmessage(lang('plugin/login_mobile','error_smscode'));
            }
            define('NOROBOT', TRUE);
            require_once dirname(__FILE__)."/libs/my_register_ctl.php";
            $ctl_obj = new my_register_ctl();
			$ctl_obj->setting = $_G['setting'];
			$ctl_obj->template = 'member/register';
			$ctl_obj->on_register();
        }
    }
}

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
