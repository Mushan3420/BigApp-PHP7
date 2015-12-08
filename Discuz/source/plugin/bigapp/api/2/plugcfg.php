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
require_once dirname(dirname(dirname(__FILE__))) . '/libs/portalcfg.inc.php';
$discuz = C::app();
$discuz->init();


$config = array(
    'display_style' => '0',
	'iyzversion'=>BIGAPP_PLUGIN_VERSION,
);
if(isset($_G['setting']['bigapp_settings'])){
    $_G['setting']['bigapp_settings'] = unserialize($_G['setting']['bigapp_settings']);
}

##############App Design#############################
//论坛配置的数据
$res = C::t('common_setting')->fetch("bigapp_view_2", true);
if(isset($res[0]) && empty($res[0])) { //没有获取论坛视图配置
	$config['display_style'] = strval((isset($_G['setting']['bigapp_settings']['display_style']) ? $_G['setting']['bigapp_settings']['display_style'] : 0));
} else {
	$config['display_style'] = isset($res["type"]) ? strval(intval($res["type"]) - 1) : '0';
}
#####################################################
//回复样式
$config['reply_button_type'] = isset($_G['setting']['bigapp_settings']['reply_button_type']) ? $_G['setting']['bigapp_settings']['reply_button_type'] : '0';

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

$config["platform_login"] = array(
    "qqlogin" => $config['qqlogin'],
    "qqlogin_end" => $config['qqlogin_end'] ,
    "wechat_login" => $config["wechat_login"],
    "weibo_login" => $config["weibo_login"],
);

// add appinfo
require_once dirname(__FILE__) . '/../../conf/conf.inc.php';
require_once dirname(__FILE__) . '/../../libs/env.inc.php';
//$appInfo = $obj->getInfo(BigAppConf::$appInfoUrl, array('method' => 'get_basic'));
$appInfo = BigappEnv::getAppInfoFromBigstation();
$appid = isset($appInfo["app_id"]) ? $appInfo["app_id"] : 0;
$config["appinfo"] = array(
    "app_id" => $appid,
);

// get login register configure
$config["login_info"] = getLoginConfigure();
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

function addFileToZip2($path, &$zip, $filterArray, $siteurl) {
	$result = array();
	
	if(file_exists($zip)) {
		file_put_contents($zip, "");
	}
    
	$handler=opendir($path);
	
	$smiley_dirs = array();
	while(($filename=readdir($handler))!==false) {
		//文件夹文件名字为'.'和‘..’，不要对他们进行操作
		if($filename != "." && $filename != ".." && $filename != "index.htm" && !in_array($filename, $filterArray)) {
			$smiley_dirs[] = $filename;
		}
	}
	
	//对表情包目录排序
	sort($smiley_dirs);
	
	foreach($smiley_dirs as $filename) {
		
		//文件夹文件名字为'.'和‘..’，不要对他们进行操作
		if(is_dir($path . "/" . $filename)){
			$filelist = getFileList($path . "/" . $filename);
			sort($filelist);
			$image_type['pic_schema'] = array();
			//$pic_content = $filename.'.zip';
			
			if (count($filelist) > 0) {
				foreach($filelist as $file) {
					$tmp = array();
					$oldfile = $file;
					$file = $path . "/" . $filename. "/" . $file;
					if (is_file($file)) {
						$fd = fopen ($file, "r");
						$content = fread ($fd, filesize ($file));
						fclose ($fd);

						$tmp['pic_name'] = $oldfile;
						$tmp['pic_size'] = filesize($file);
						
						file_put_contents($zip, $content, FILE_APPEND | LOCK_EX);
						$image_type['pic_schema'][] = $tmp;
					}
				}
				
				$image_type['pic_directory'] = $filename;
				//$image_type['pic_content'] = $siteurl . 'static/image/' . $filename.'.zip';
				
				$result[] = $image_type;
			}
		}
	}
	@closedir($path);
	
	return $result;
}

function getFileList($path) {
	$filelist = array();
	
	if(is_dir($path)) {
		$handler = opendir($path);
		while(($filename = readdir($handler)) !== false) {
			if($filename != "." && $filename != ".." && $filename != "index.htm") {
				array_push($filelist, $filename);
			}
		}
	}
	
	return $filelist;
}
########################################################
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
//if(!class_exists('ZipArchive')){
//	$smiley_info['message'] = 'no zip extension';
//	$smiley_info['code'] = '-1';
//} else {
	$ret = C::t('common_setting')->fetch('bigapp_similes_info_v2', false);
			
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

	if((time() - $time) > 60) {
		//超过10分钟了，需要监控下zip表情包是否发生变化
		$smurl = 'static/image/';
		$smdir = DISCUZ_ROOT.$smurl;
		
		$zip_file = DISCUZ_ROOT . 'static/image/smiley_v2.zip';
		
		$filterArray = array();
		$current_dir = getcwd();
			
		chdir($smdir);
		foreach(C::t('forum_imagetype')->fetch_all_by_type('smiley') as $type) {
			$available = $type['available'];
			if($available == '0')	{
				array_push($filterArray, $type['directory']);
			}			
		}
		
		$result = addFileToZip2('smiley', $zip_file, $filterArray, $siteurl);
		$smiley_info['zip_info'] = $result;
	
		chdir($current_dir);
		
		
		$md5 = md5(md5(getSmileyInfo()) + md5(file_get_contents($zip_file)));
		
		//if(!isset($info['md5']) || isset($info['md5']) && $info['md5'] != $md5) {							
			$smilies_info = array('file' => 'smiley_v2.zip', 'md5' => $md5, 'time' => time(), 'schema' => $result);
			$setting = array('bigapp_similes_info_v2' => json_encode($smilies_info));
			C::t('common_setting')->update_batch($setting);
		//}
		
		$smiley_info['message'] = 'success';
		$smiley_info['code'] = '1';
		$smiley_info['md5'] = $md5;
		$smiley_info['zip_url'] = $siteurl . 'static/image/smiley_v2.zip';
	} else {
		if($info != "") {
			$smiley_info['message'] = 'success';
			$smiley_info['code'] = '1';
			$smiley_info['zip_info'] = $info['schema'];
			$smiley_info['md5'] = $info['md5'];
			$smiley_info['zip_url'] = $siteurl . 'static/image/smiley_v2.zip';
		}
	}
//}

if(is_null($smiley_info['zip_info'])){
	$smiley_info['zip_info'] = array();
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
if(!empty($setting)){
	$setting = json_decode($setting, true);
}	


$thread_config = array();
if(is_array($setting)){
	$succRet['data'] = $setting;

	if($setting['enable_new'] == 'true') {
		$title = "";
		if(!empty($setting['title_new'])){
			$title = Utils::converGbkString($setting['title_new']);
		}
		$thread_config[] = array('module' => 'new' ,'type'=>'6', 'enable'=> '1', 'sort' => $setting['sort_new'],'title' => $title);
	}
	if($setting['enable_hot'] == 'true') {
		$title = "";
		if(!empty($setting['title_hot'])){
			$title = Utils::converGbkString($setting['title_hot']);
		}
		$thread_config[] = array('module' =>'hot' ,'type'=>'7', 'enable'=> '1', 'sort' => $setting['sort_hot'],'title' => $title);
	}
	if($setting['enable_fav'] == 'true') {
		$title = "";
		if(!empty($setting['title_fav'])){
			$title = Utils::converGbkString($setting['title_fav']);
		}
		$thread_config[] = array('module' => 'digest' ,'type'=>'8', 'enable'=> '1', 'sort' => $setting['sort_fav'],'title' => $title);
	}

} else {
	$thread_config[] = array('title' => Utils::converGbkString('最新'), 'enable'=> '1', 'sort' => '1','module' => 'new','type'=>'6');
	$thread_config[] = array('title' => Utils::converGbkString('热门'), 'enable'=> '1', 'sort' => '2','module' => 'hot','type'=>'7');
	$thread_config[] = array('title' => Utils::converGbkString('精华'), 'enable'=> '1', 'sort' => '3','module' => 'digest','type'=>'8');
}


foreach($thread_config as &$thread){
	if(!isset($thread['id'])){
		$thread['id'] = '0';
	}
	if(!isset($thread['type'])){
		$thread['type'] = '0';
	}
	if(!isset($thread['title'])){
		$thread['title'] = $thread['title'];
	}
}
if(true){
	loadcache('portalcategory');
    $portalcategory = $_G['cache']['portalcategory'];
	if(!empty($portalcategory)){
		foreach($portalcategory as $cat){
			if($cat['closed'] != 1){
				if(isset($setting['portal']) && !empty($setting['portal'])){
					foreach($setting['portal'] as $category){
						if($category['id'] == $cat['catid'] && $category['enable'] == 1){
							$cname = isset($category['title'])?Utils::converGbkString($category['title']):$cat['catname'];
							$thread_config[] = array('title' => $cname,'module'=>$cat['catname'], 'enable'=> '1', 'sort' => $category['sort'],'id'=>$cat['catid'],'type'=>'4');
						}
					
					}
				}
			}
		}
	}
}

$newArr=array();
for($j=0; $j < count($thread_config); $j++){
	$newArr[]=$thread_config[$j]['sort'];
}
array_multisort($newArr , $thread_config);

$config['threadconfig'] = $thread_config;


$setting = C::t('common_setting')->fetch("bigapp_home_portalsetting", false);
if(!empty($setting)){
	$setting = json_decode($setting, true);
}

$config['portalconfig'] = getPortalConfigure($setting);
$config['push_enabled'] = intval(getPushStat());

$ret = array(
    'error_code' => 0,
    'error_msg' => 'SUCC',
    'config' => $config,
);

echo BIGAPPJSON::encode($ret);

?>
