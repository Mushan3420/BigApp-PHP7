<?php

/***********************************************************
 * @file:   check.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-08-04 16:29:19
 * @modify: 2015-08-04 16:29:19
 * @brief:  qq登录后检查
 ***********************************************************/

global $_G;


$openid = $_GET["openid"];


$fields = array('uid','openid','platid','status','param');
$connect_member = C::t('#bigapp#bigapp_connection')->fetch_fields_by_openid_platid($openid, 1, $fields);

// 已绑定会员账号，直接登录
if (!empty($connect_member)) {
	connect_login($connect_member);
	C::t('common_member_status')->update($connect_member['uid'], array('lastip'=>$_G['clientip'],
                                         'lastvisit'=>TIMESTAMP, 'lastactivity' => TIMESTAMP));
	$variable["hasbind"] = 1;
	$variable["message"] = "wechat_login_success";
	bigapp_core::result(bigapp_core::variable($variable));
} 

// 告诉客户端需要登录绑定，或注册绑定
else {
	$variable["hasbind"] = 0;
//    $variable["openid"] = $openid;
//    $variable["token"] = $token;
	bigapp_core::result(bigapp_core::variable($variable));
}


// copy from qqconnect/connect.php
function connect_login($connect_member) {
    global $_G;

    if(!($member = getuserbyuid($connect_member['uid'], 1))) {
        return false;
    } else {
        if(isset($member['_inarchive'])) {
            C::t('common_member_archive')->move_to_master($member['uid']);
        }
    }

    require_once libfile('function/member');
    $cookietime = 1296000;
    setloginstatus($member, $cookietime);

    dsetcookie('connect_login', 1, $cookietime);
    dsetcookie('connect_is_bind', '1', 31536000);
    dsetcookie('connect_uin', $connect_member['openid'], 31536000);
    return true;
}
