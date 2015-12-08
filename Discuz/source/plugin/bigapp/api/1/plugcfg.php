<?php
/**
* @file plugcfg.php
* @Brief get plugin configs
* @author youzu
* @version 1.0.0
* @date 2015-07-21
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
define('APPTYPEID', 0);
define('CURSCRIPT', 'member');
require './source/class/class_core.php';
require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';
$discuz = C::app();
$discuz->init();

$config = array(
    'display_style' => 0,
);
if(isset($_G['setting']['bigapp_settings'])){
    $_G['setting']['bigapp_settings'] = unserialize($_G['setting']['bigapp_settings']);
}
$config['display_style'] = strval((isset($_G['setting']['bigapp_settings']['display_style']) ? $_G['setting']['bigapp_settings']['display_style'] : 0));


///////////////////////////////////////////////////////
// add by mawentao
// check qqconnet
require_once dirname(__FILE__)."/../../libs/appcfg.inc.php";
$appcfg=getAppConfigure();
//if(!$_G['setting']['connect']['allow']) {
if(!$appcfg["qq_login"]) {
    $config['qqlogin'] = "";
    $config['qqlogin_end'] = "";
} else {
    $siteurl    = substr($_G["siteurl"],0,-11);
    $qqloginurl = $siteurl."source/plugin/bigapp/api/qqconnect/connect.php?mod=login&op=init&referer=forum.php&statfrom=login_simple";
    $qqlogin_end_url = $siteurl."source/plugin/bigapp/api/qqconnect/connect.php?platform=qq";
    $config['qqlogin'] = $qqloginurl;
    $config['qqlogin_end'] = $qqlogin_end_url;
}
$config["wechat_login"]=$appcfg["wechat_login"];
$config["weibo_login"]=$appcfg["weibo_login"];
$config["weibo_login"]=0;

// add appinfo
require_once dirname(__FILE__) . '/../../conf/conf.inc.php';
require_once dirname(__FILE__) . '/../../libs/utils.inc.php';
require_once dirname(__FILE__) . '/../../libs/bksvr.inc.php';
require_once dirname(__FILE__) . '/../../libs/getaksk.inc.php';
$obj = new BkSvr($ak, $sk, 30);
$appInfo = $obj->getInfo(BigAppConf::$appInfoUrl, array('method' => 'get_basic'));
$config["appinfo"] = array(
    "app_id" => $appInfo["app_id"],
);
///////////////////////////////////////////////////////

$succRet['data'] = C::t('common_setting')->fetch("bigapp_settings_checkin", true);
$config['checkin_enabled'] = strval(isset($succRet['data']['enabled']) ? $succRet['data']['enabled'] : 0 );


########################################################

function getPushStat()
{
	global $_G;
	updatecache('setting');
	if(isset($_G['setting']['bigapp_push_config'])){
		$_G['setting']['bigapp_push_config'] = unserialize($_G['setting']['bigapp_push_config']);
	}
	if(!isset($_G['setting']['bigapp_push_config']['push_enabled'])){
		$_G['setting']['bigapp_push_config']['push_enabled'] = 0;
	}
	if($_G['setting']['bigapp_push_config']['push_enabled'] != 0){
		$_G['setting']['bigapp_push_config']['push_enabled'] = 1;
	}
	return $_G['setting']['bigapp_push_config']['push_enabled'];	
}

function addFileToZip($path, $zip, $filterArray){
	$handler=opendir($path); //打开当前文件夹由$path指定
	while(($filename=readdir($handler))!==false){
		if($filename != "." && $filename != ".." && $filename != "index.htm" && !in_array($filename, $filterArray)){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
			if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
				addFileToZip($path."/".$filename, $zip, $filterArray);
			}else{ //将文件加入zip对象
				if(file_exists($path."/".$filename) && is_readable($path."/".$filename))
					$zip->addFile($path."/".$filename);
			}
		}
	}
	@closedir($path);
}

function getSmileyInfo() {
	$info = array();
	foreach(C::t('forum_imagetype')->fetch_all_by_type('smiley') as $type) {
		$available = $type['available'];
		if($available == '0')	continue;
		$id = $type['typeid'];
		
		$info[$id]['name'] = $type['name'];
		$info[$id]['directory'] = $type['directory'];
		
		$smiley_cnt = 0;
		foreach(C::t('common_smiley')->fetch_all_by_typeid_type($id, 'smiley') as $smiley) {
			$info[$id]['smiley'][$smiley_cnt]['code'] = $smiley['code'];
			$info[$id]['smiley'][$smiley_cnt]['url'] = $smiley['url'];
			$info[$id]['smiley'][$smiley_cnt]['id'] = $smiley['id'];
			$smiley_cnt ++;
		}
	}

	$simily_infos = json_encode($info);
	
	return $simily_infos;
}

$smiley_info = array();
if(!class_exists('ZipArchive')){
	$smiley_info['message'] = 'no zip extension';
	$smiley_info['code'] = '-1';
} else {
	$ret = C::t('common_setting')->fetch('bigapp_similes_info', false);
			
	if(!empty($ret)) {
		$info = json_decode($ret, true);
	} else {
		$info= "";
	}

	if($info == "") {
		$time = time() - 7 * 24 * 60 * 60;
	} else {
		if(!isset($info['time'])) {
			$time = time() - 7 * 24 * 60 * 60;
		} else {
			$time = $info['time'];
		}
	}

	$siteurl = substr($_G["siteurl"], 0, -11);

	if((time() - $time) > 600) {
		//超过10分钟了，需要监控下zip表情包是否发生变化
		$smurl = 'static/image/';
		$smdir = DISCUZ_ROOT.$smurl;
		
		$zip_file = DISCUZ_ROOT . 'static/image/smiley.zip';
		
		$zip=new ZipArchive();
		if($zip->open($zip_file, ZipArchive::OVERWRITE | ZIPARCHIVE::CREATE )=== TRUE){
			$filterArray = array();
			$current_dir = getcwd();
			
			chdir($smdir);
			foreach(C::t('forum_imagetype')->fetch_all_by_type('smiley') as $type) {
				$available = $type['available'];
				if($available == '0')	{
					array_push($filterArray, $type['directory']);
				}			
			}
			//调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
			addFileToZip('smiley', $zip, $filterArray);
			//关闭处理的zip文件						
			$zip->close(); 
			
			chdir($current_dir);
		}
		
		if(!file_exists($zip_file)) {
			$smiley_info['message'] = 'zip file created failed';
			$smiley_info['code'] = '0';
		} else {
			$md5 = md5(md5(getSmileyInfo()) + md5(file_get_contents($zip_file)));
			
			//if(!isset($info['md5']) || isset($info['md5']) && $info['md5'] != $md5) {							
				$smilies_info = array('file' => 'smiley.zip', 'md5' => $md5, 'time' => time());
				$setting = array('bigapp_similes_info' => json_encode($smilies_info));
				C::t('common_setting')->update_batch($setting);
			//}
			
			$smiley_info['message'] = 'success';
			$smiley_info['code'] = '1';
			$smiley_info['md5'] = $md5;
			$smiley_info['zip_url'] = $siteurl . 'static/image/smiley.zip';
		}
	} else {
		if($info != "") {
			$smiley_info['message'] = 'success';
			$smiley_info['code'] = '1';
			$smiley_info['md5'] = $info['md5'];
			$smiley_info['zip_url'] = $siteurl . 'static/image/smiley.zip';
		}
	}
}

$config['smiley_info'] = $smiley_info;

##########################################################

$ret = C::t('common_setting')->fetch('bigapp_mobile_setting', false);
		
if(!empty($ret)) {
	$ret = str_replace("#u", "\\u", $ret);
	$info = json_decode($ret, true);
} else {
	$info= "";
}

if(isset($info['appdesc'])) {
	$config['appdesc'] = $info['appdesc'];
} else {
	$config['appdesc'] = "";
}

/*
if(function_exists('iconv')){
	$config['appdesc'] = iconv(CHARSET, 'UTF-8//ignore', $config['appdesc']);
}else{
	$config['appdesc'] = mb_convert_encoding($config['appdesc'], 'UTF-8', CHARSET);
}

$config['appdesc'] = '__DONT_DICONV_TO_UTF8___' . $config['appdesc'];
*/

if(function_exists('iconv')){
    $config['appdesc'] = iconv('UTF-8//ignore', CHARSET, $config['appdesc']);
}else{
    $config['appdesc'] = mb_convert_encoding($config['appdesc'], CHARSET, 'UTF-8');
}

###########################################################
$ret = C::t('common_setting')->fetch('search', true);

$enable_search = '0';
$search_info = array();
		
if(!empty($ret) && is_array($ret)) {
	
	foreach($ret as $key => $value) {
		if(isset($value['status']) && $value['status'] == '1') {
			if($key == 'forum' || $key == 'group')
				$enable_search = '1';
		}
		$value['key'] = $key;
		
		$search_info['setting'][] = $value;
	}
}

$search_info['enable'] = $enable_search;
//$search_info['setting'] = $ret; 

$ret = C::t('common_setting')->fetch('sphinxon', false);
$sphinxon_enable = '0';

if(isset($ret['sphinxon']) && $ret['sphinxon'] == '1') {
	$sphinxon_enable = '1';
} else {
	$sphinxon_enable = '0';
}

$search_info['enablesphinxon'] = $sphinxon_enable;

$config['searchsetting'] = $search_info;
##########################################################

$setting = C::t('common_setting')->fetch("bigapp_home_threadsetting", false);
		
$setting = json_decode($setting, true);

$thread_config = array();
if(is_array($setting)){
	$succRet['data'] = $setting;

	if($setting['enable_new'] == 'true') {
		$thread_config[] = array('title' => 'new', 'enable'=> '1', 'sort' => $setting['sort_new']);
	}
	if($setting['enable_hot'] == 'true') {
		$thread_config[] = array('title' =>'hot', 'enable'=> '1', 'sort' => $setting['sort_hot']);
	}
	if($setting['enable_fav'] == 'true') {
		$thread_config[] = array('title' => 'digest', 'enable'=> '1', 'sort' => $setting['sort_fav']);
	}
	
	$newArr=array();
	
	for($j=0; $j < count($thread_config); $j++){
		$newArr[]=$thread_config[$j]['sort'];
	}
	
	array_multisort($newArr , $thread_config);
} else {
	$thread_config[] = array('title' => 'new', 'enable'=> '1', 'sort' => '1');
	$thread_config[] = array('title' =>'hot', 'enable'=> '1', 'sort' => '2');
	$thread_config[] = array('title' => 'digest', 'enable'=> '1', 'sort' => '3');
}


$config['threadconfig'] = $thread_config;
$config['push_enabled'] = intval(getPushStat());
$ret = array(
    'error_code' => 0,
    'error_msg' => 'SUCC',
    'config' => $config,
);

echo BIGAPPJSON::encode($ret);

?>
