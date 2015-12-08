<?php

/***********************************************************
 * @file:   bind.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-08-05 09:06:25
 * @modify: 2015-08-05 09:06:25
 * @brief:  绑定qq账号
 ***********************************************************/

global $_G;

$conopenid = $_GET["openid"];
$token = $_GET["token"];
$current_connect_member = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);

//print_r($current_connect_member); die(0);

// 当前登录账号已绑定过QQ
if($current_connect_member['conopenid']) {
    // 该登录账号绑定的是另一个QQ
	if(strtoupper($current_connect_member['conopenid']) != $conopenid) {
        echo BIGAPPJSON::encode(array(
            'error_code' => 5,
            'error_msg' => lang('plugin/qqconnect', 'connect_register_bind_already'), 
		    'Variables' => array('auth' => null), 
            'Message' => array(
                'messageval'=>'for comaptible',
                'messagestr'=>lang('plugin/qqconnect', 'connect_register_bind_already')
            )
        ));
        die(0);
	} 
    // 当登录账号绑定的是当前QQ
    else {
        // do nothing
    }
}
// 当前登录账号未绑定过QQ
else {
	if(empty($current_connect_member)) {
		C::t('#qqconnect#common_member_connect')->insert(
			!$_G['setting']['connect']['oauth2'] ? array(
				'uid' => $_G['uid'],
				'conuin' => $token,
				'conuinsecret' => '',
				'conopenid' => $conopenid,
				'conispublishfeed' => 1,
				'conispublisht' => 1,
				'conisregister' => 0,
				'conisfeed' => 1,
				'conisqqshow' => 1,
			) : array(
				'uid' => $_G['uid'],
				'conuin' => '',
				'conuintoken' => $token,
				'conopenid' => $conopenid,
				'conispublishfeed' => 1,
				'conispublisht' => 1,
				'conisregister' => 0,
				'conisfeed' => 1,
				'conisqqshow' => 1,
			)
		);
	} else {
		C::t('#qqconnect#common_member_connect')->update($_G['uid'],
			!$_G['setting']['connect']['oauth2'] ? array(
				'conuin' => $token,
				'conuinsecret' => '',
				'conopenid' => $conopenid,
				'conispublishfeed' => 1,
				'conispublisht' => 1,
				'conisregister' => 0,
				'conisfeed' => 1,
				'conisqqshow' => 1,
			) : array(
				'conuintoken' => $token,
				'conopenid' => $conopenid,
				'conispublishfeed' => 1,
				'conispublisht' => 1,
				'conisregister' => 0,
				'conisfeed' => 1,
				'conisqqshow' => 1,
			)
		);
	}
	C::t('common_member')->update($_G['uid'], array('conisbind' => '1'));
	C::t('#qqconnect#common_connect_guest')->delete($conopenid);
}


// vim600: sw=4 ts=4 fdm=marker syn=php
?>
