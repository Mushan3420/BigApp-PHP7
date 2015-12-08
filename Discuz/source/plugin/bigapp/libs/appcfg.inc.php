<?php
require_once dirname(__FILE__).'/../bigappjson.class.php';

function getAppConfigure()
{
    $svalue = C::t('common_setting')->fetch("bigapp_appcfg_setting",false);
    $params = BIGAPPJSON::decode($svalue, true);

    if(empty($params)) {
        // 默认第三方登录都不开启
        $params["qq_login"]=0;
        $params["wechat_login"]=0;
        $params["weibo_login"]=0;
    }

    global $_G;
    if(!$_G['setting']['connect']['allow']) {
        $params["qqconnect"] = 0;
        $params["qq_login"] = 0;
    } else {
        $params["qqconnect"] = 1;
    }

    return $params;
}

function saveAppConfigure(array &$params)
{

    $pstr = BIGAPPJSON::encode($params);
	//$svalue = str_replace("\\u", "#u", $pstr);
	$sql = "INSERT INTO ".DB::table('common_setting')." values ('bigapp_appcfg_setting','$pstr') ".
		   "ON DUPLICATE KEY UPDATE svalue=values(svalue)";
	DB::query($sql);
	require_once libfile('function/core');
	require_once libfile('function/cache');
    updatecache('setting');
}


// 保存登录注册配置
function saveLoginConfigure(array &$params)
{
    $pstr = BIGAPPJSON::encode($params);
	//$svalue = str_replace("\\u", "#u", $pstr);
	$sql = "INSERT INTO ".DB::table('common_setting')." values ('bigapp_longin_register_setting','$pstr') ".
		   "ON DUPLICATE KEY UPDATE svalue=values(svalue)";
	DB::query($sql);
	require_once libfile('function/core');
	require_once libfile('function/cache');
    updatecache('setting');
}

// 获取登录注册配置
function getLoginConfigure()
{
    $svalue = C::t('common_setting')->fetch("bigapp_longin_register_setting",false);
    $params = BIGAPPJSON::decode($svalue, true);

    if(empty($params)) {
        $params["login_mod"]  = 0;
        $params["login_url"]  = "";
        $params["reg_mod"]    = 0;
        $params["reg_url"]    = "";
        $params["reg_switch"] = 1;
        $params["allow_avatar_change"] = 1;
    } else {
        if($params['login_mod'] == 0){
			$params['login_url'] = "";
		}
		if($params['reg_mod'] == 0){
			$params['reg_url'] = "";
		}
    }
    return $params;
}

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
