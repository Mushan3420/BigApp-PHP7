<?php
require_once dirname(dirname(__FILE__)) . '/conf/conf.inc.php';
require_once dirname(dirname(__FILE__)) . '/bigappjson.class.php';
require_once dirname(__FILE__) . '/utils.inc.php';
require_once dirname(__FILE__) . '/bksvr.inc.php';

class PushMsg{
	protected static function getAppInfo($ak, $sk)
	{
		$obj = new BkSvr($ak, $sk, 30);
		$appInfo = $obj->getInfo(BigAppConf::$appInfoUrl, array('method' => 'get_basic'));
		if(!is_array($appInfo)){
        	runlog('bigapp', "use remote ak sk to get app info failed, give up [ ak: $ak, sk: $sk ]");
			return false;
		}
		return $appInfo;
	}

	public static function sendMessage($uid, $title, $content, $msgType, $extra = null, $mask = 3, $jpushAk = null, $jpushSk = null)
	{
		global $_G;
		$tmp = Utils::readLocalAkSk2();
		if(!isset($tmp['app_key']) || !isset($tmp['app_secret'])){
			return false;
		}
		$ak = $tmp['app_key'];
		$sk = $tmp['app_secret'];
		$appInfo = self::getAppInfo($ak, $sk);
		if(!isset($appInfo['app_id'])){
			return false;
		}
		$appId = $appInfo['app_id'];
		$alias = sprintf('%020lu%020lu', $appId, $uid);
		$params = array(
				'alias' => $alias,
				'mask' => $mask,
				'message_type' => $msgType,
				'title' => $title, 
				'content' => $content,
				);
		if(is_array($extra)){
			$params['extra'] = BIGAPPJSON::encode($extra);
		}
		if(!is_null($jpushAk) && !is_null($jpushSk)){
			$params['jpush_app_key'] = $jpushAk;
			$params['jpush_master_secret'] = $jpushSk;
		}
		$url = BigAppConf::$pushUrl;
		$obj = new BkSvr($ak, $sk, 30);
		$ret = $obj->getInfo($url, $params, false, false);
		if(!is_array($ret)){
			runlog('bigapp', 'send message failed, invalid return [ ret: ' . $ret . ' ]');
			return false;
		}
		if(0 != $ret['error_code']){
			runlog('bigapp', 'send message failed, error code is not 0 [ ret: ' . BIGAPPJSON::encode($ret) . ' ]');
			return $ret;
		}
		return true;
	}
}
?>
