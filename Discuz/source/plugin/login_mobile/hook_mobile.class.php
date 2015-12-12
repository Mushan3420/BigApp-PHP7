<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once dirname(__FILE__).'/libs/env.php';

class mobileplugin_login_mobile
{
    function common()
    {
    }
/*
    function global_header_mobile()
    {
        return "hello";
    }
*/
}

class mobileplugin_login_mobile_member extends mobileplugin_login_mobile
{
    function logging()
    {
        if (!DzEnv::isEnableMobile()) return;
        global $_G;
        if(isset($_GET["action"]) && $_GET["action"]=="login" && isset($_GET["mobile"])) {
            $loginurl = DzEnv::getSiteUrl()."/source/plugin/login_mobile/fe/login.html";
            header("Location: $loginurl"); 
            die(0);
        }
    }

    function register()
    {
        if (!DzEnv::isEnableMobile()) return;
        global $_G;
        if(isset($_GET["mobile"])) {
            $url = DzEnv::getSiteUrl()."/source/plugin/login_mobile/fe/regist.html";
            header("Location: $url"); 
            die(0);
        }       
    }
}

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
