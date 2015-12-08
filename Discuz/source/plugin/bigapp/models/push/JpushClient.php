<?php

/***********************************************************
 * @file:   JpushClient.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-11-16 14:02:48
 * @modify: 2015-11-16 14:02:48
 * @brief:  消息推送客户端
 ***********************************************************/
class Bigapp_JpushClient
{
    private static $default_reqpack = array(
        // 消息推送到的用户别名,all表示所有用户
        "alias" => "all",
        // 消息类型 //0: 测试消息；1: 有消息通知；2: 有回帖通知；3:有好友请求通知
        "message_type" => 1,
        // 消息推送方式（1:消息，2:通知, 3:消息+通知）
        "mask" => 3,
        // 消息标题
        "title" => "",
        // 消息内容
        "content" => "",
        // 其他信息，随着message_type的不同而不同，可选
        "extra" => "",
        // 是否测试消息（0:否，1:是）
        "istest" => 1,
    );

    public static function sendMessage($params)
    {
        $paramRet = json_encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100801, 'error_msg' => 'invalid param', 'show_tips' => '参数错误'));
		$authRet = json_encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100802, 'error_msg' => 'auth failed', 'show_tips' => '必须是管理员帐号才可执行此操作'));
		$akskRet = json_encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100803, 'error_msg' => 'auth failed', 'show_tips' => '您尚未在应用设置中填写jpush的appkey或master_secret，无法发送消息'));
		$svrRet = json_encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100804, 'error_msg' => 'internal server error', 'show_tips' => '服务器内部错误'));
		$aliasRet = json_encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100805, 'error_msg' => 'invalid alias', 'show_tips' => '请在网络良好的环境下开启客户端'));
		$jpushkeyRet = json_encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100806, 'error_msg' => 'jpush key lost', 'show_tips' => '请申请您自己的JpushKey并在生成应用填写该key'));

        $reqparams = self::$default_reqpack;
        if(isset($params["alias"])){ $reqparams["alias"]=$params["alias"]; }
        if(isset($params["message_type"])){ $reqparams["message_type"]=$params["message_type"]; }
		if(isset($params["mask"])){ $reqparams["mask"]=$params["mask"]; }
		if(isset($params["title"])){ $reqparams["title"]=$params["title"]; }
		if(isset($params["content"])){ $reqparams["content"]=$params["content"]; }
		if(isset($params["extra"])){ $reqparams["extra"]=$params["extra"]; }
		if(isset($params["istest"])){ $reqparams["istest"]=$params["istest"]; }


        $aksk = BigappEnv::getAkSk();
        $ak = $aksk["ak"];
        $sk = $aksk["sk"];
        $obj = new BkSvr($ak, $sk, 30);
        $ret = $obj->getInfo(BigAppConf::$pushUrl,$reqparams,false,false);
		if(false === $ret || 0 != $ret['error_code']){
			if(100020 == $ret['error_code']){
				$aliasRet['show_tips'] .= '并以' . $_G['username'] . '帐号登录，然后重试';
				$aliasRet = BIGAPPJSON::encode($aliasRet);
				return $aliasRet;
			}
			if(100021 == $ret['error_code']){
				return $akskRet;
			}
			if(100022 == $ret['error_code']){
				return $jpushkeyRet;
			}
			return $svrRet;
		}

        C::t("#bigapp#bigapp_push_message")->save($reqparams);

        return json_encode($ret);
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
