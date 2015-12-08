<?php
/**
* @file getaksk.inc.php
* @Brief get ak & sk for every page in admin center
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!function_exists('curl_init')){
	showmessage(lang('plugin/bigapp', 'curl_needed'), '', array(), array('alert_error')); 
}
if(!function_exists('iconv') && !function_exists('mb_convert_encoding')){
	showmessage(lang('plugin/bigapp', 'iconv_mb_needed'), '', array(), array('alert_error')); 
}
//if(!class_exists('ZipArchive', false)){
//	showmessage(lang('plugin/bigapp', 'zip_class_missing'), '', array(), array('alert_error'));
//}
$ak = $sk = $appInfo = null;
//try to read local ak sk
$tmp = Utils::readLocalAkSk2();
if(!isset($tmp['app_key']) || !isset($tmp['app_secret'])){
	runlog('bigapp', 'get local ak sk failed, try to get remote ak sk');
	list($ak, $sk) = readRemoteAkSk();
	Utils::saveLocalAkSk2($ak, $sk);
	runlog('bigapp', "get remote ak sk succ, save them [ ak: $ak, sk: $sk ]");
	$obj = new BkSvr($ak, $sk, 30);
    $appInfo = $obj->getInfo(BigAppConf::$appInfoUrl, array('method' => 'get_basic'));
    if(!is_array($appInfo)){
		runlog('bigapp', "use remote ak sk to get app info failed, give up [ ak: $ak, sk: $sk ]");
		showmessage(lang('plugin/bigapp', 'get_ak_sk_fail'), '', array(), array('alert' => 'error'));
	}
}else{
	$ak = $tmp['app_key'];
	$sk = $tmp['app_secret'];
	$obj = new BkSvr($ak, $sk, 30);
	$appInfo = $obj->getInfo(BigAppConf::$appInfoUrl, array('method' => 'get_basic'));
	if(!is_array($appInfo)){
		runlog('bigapp', 'use local ak sk to get app info failed, try to get remote ak sk');
		list($ak, $sk) = readRemoteAkSk();
		runlog('bigapp', "get remote ak sk succ, save them [ ak: $ak, sk: $sk ]");
		Utils::saveLocalAkSk2($ak, $sk);
		$obj = new BkSvr($ak, $sk, 30);
		$appInfo = $obj->getInfo(BigAppConf::$appInfoUrl, array('method' => 'get_basic'));
		if(!is_array($appInfo)){
			runlog('bigapp', "use remote ak sk to get app info failed, give up [ ak: $ak, sk: $sk ]");
			showmessage(lang('plugin/bigapp', 'get_ak_sk_fail'), '', array(), array('alert' => 'error'));
		}
	}
}
//$appInfo['remind'] = 2;
//$appInfo['remind_message'] = '插件功能迁移到站长中心，接口即将被废弃';
runlog('bigapp', "get ak sk and app info succ [ ak: $ak, sk: $sk, appid: " . $appInfo['app_id'] . "]");

function readRemoteAkSk()
{
	global $_G;
	$siteUrl = null;
    $adminEmail = '';
    $bbsName = '';
    if(isset($_G['siteurl'])){
        $siteUrl = $_G['siteurl'] . 'api/mobile/iyz_index.php';
		$siteUrl = str_replace('api/mobile/api/mobile/', 'api/mobile/', $siteUrl);
    }
    if(isset($_G['setting']['adminemail'])){
        $adminEmail = $_G['setting']['adminemail'];
    }
    if(isset($_G['setting']['bbname'])){
        $bbsName = $_G['setting']['bbname'];
    }
    if(empty($siteUrl)){
        showmessage(lang('plugin/bigapp', 'no_site_url'), '', array(), array('alert' => 'error'));
    }
    $obj = new BkSvr(1, 2, 30); //fake ak/sk
    $aksk = $obj->getInfo(BigAppConf::$ucRegUrl, array('method' => 'regist', 'site_url' => $siteUrl, 'bbs_name' => $bbsName, 'admin_email' => $adminEmail));
    if(isset($aksk['app_key']) && isset($aksk['app_secret'])){
        $ak = $aksk['app_key'];
        $sk = $aksk['app_secret'];
    }
    if(empty($ak) || empty($sk)){
        showmessage(lang('plugin/bigapp', 'get_ak_sk_fail'));
    }	
	return array($ak, $sk);
}

?>
