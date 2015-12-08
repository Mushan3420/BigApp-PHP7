<?php

/***********************************************************
 * @file:   bind.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-08-05 09:06:25
 * @modify: 2015-08-05 09:06:25
 * @brief:  绑定qq账号
 ***********************************************************/

global $_G;

$openid = $_GET["openid"];
$current_connect_member = C::t('#bigapp#bigapp_connection')->fetch_fields_by_uid_platid($_G['uid'], 1);
//print_r($current_connect_member); die(0);


// 当前登录账号已绑定过微信
if($current_connect_member['openid']) {
    // 该登录账号绑定的是另一个微信
	if(strtoupper($current_connect_member['openid']) != strtoupper($openid)) {
        echo BIGAPPJSON::encode(array(
            'error_code' => 5,
            'error_msg' => lang('plugin/bigapp', 'wechat_connect_register_bind_already'), 
		    'Variables' => array('auth' => null), 
            'Message' => array(
                'messageval'=>'for comaptible',
                'messagestr'=>lang('plugin/bigapp', 'wechat_connect_register_bind_already')
            )
        ));
        die(0);
	} 
    // 当登录账号绑定的是当前微信
    else {
        // do nothing
    }
}
// 当前登录账号未绑定过微信账号
else {
	$item = array (
		"uid"    => $_G['uid'],
		"openid" => $openid,
		"platid" => 1,
		"status" => 0,
		"param"  => "",
	);
	C::t('#bigapp#bigapp_connection')->save($item);
}

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
