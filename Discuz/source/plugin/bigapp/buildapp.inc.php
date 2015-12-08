<?php
/**
* @file buildapp.inc.php
* @Brief build app page for admin center
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once dirname(__FILE__) . '/libs/menu.inc.php';
require_once dirname(__FILE__) . '/conf/conf.inc.php';
require_once dirname(__FILE__) . '/libs/utils.inc.php';
require_once dirname(__FILE__) . '/libs/bksvr.inc.php';
require_once dirname(__FILE__) . '/libs/getaksk.inc.php';
require_once dirname(__FILE__) . '/bigappjson.class.php';
echo '<script type="text/javascript" src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/jquery.js' . '"></script>';
echo '<script type="text/javascript" src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/jquery-ui-colorpicker.js' . '"></script>';
echo '<script type="text/javascript" src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/jquery.colorpicker.js' . '"></script>';
echo '<script type="text/javascript" src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/ajaxfileupload.js' . '"></script>';
echo '<script type="text/javascript" charset="utf-8" src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/uploadfile.js' . '"></script>';
echo '<script type="text/javascript" charset="utf-8" src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/uz/buildapp.js' . '"></script>';
echo '<script type="text/javascript" charset="utf-8">var v={"new_versions":"'.rtrim($_G['siteurl'], '/').'/plugin.php?id=bigapp:releaseapi&method=new_versions"};</script>';
echo '<link id="jquiCSS" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/ui-lightness/jquery-ui.css" type="text/css" media="all">';
echo '<link rel="stylesheet" href="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/css/evol.colorpicker.css"/>';
echo '<script src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/evol.colorpicker.js"></script>';

//add for plugin upgrading
if(isset($appInfo['remind']) && (1 == $appInfo['remind'] || 2 == $appInfo['remind'])){ 
	if(function_exists('iconv')){
		$msg = iconv('UTF-8', CHARSET . '//ignore', $appInfo['remind_message']);	
	}else{
		$msg = mb_convert_encoding($appInfo['remind_message'], CHARSET, 'UTF-8');
	}
	showtips($msg, '', true, lang('plugin/bigapp', 'plugin_interface_upgrade'));
	if(2 == $appInfo['remind']){
		die(0);
	}
}

if(!empty($sk) && !empty($ak)){
	$obj = new BkSvr($ak, $sk, 30);
	$appInfo = $obj->getInfo(BigAppConf::$appInfoUrl, array('method' => 'get_basic'));
    $shareSettingInfo = $obj->getInfo(BigAppConf::$shareUrl, array('app_id' => $appInfo['app_id']), false);

	if(!is_array($appInfo)){
		runlog('bigapp', "get app info failed [ ak: $ak, sk: $sk ]");
		Utils::jumpToLogin('invalid_aksk', 'buildapp');
	}
	if(submitcheck('newtask')){
		runlog('bigapp', 'try to create a new task');
		$param = prepCreateTask();
		$param['app_id'] = $appInfo['app_id'];
		$param['task_id'] = $_REQUEST['task_id'];
		
		if(isset($_REQUEST['choose_release'][0]) && $_REQUEST['choose_release'][0] == 0) {
			$param['choose_release'] = '1';
		} else {
			$param['choose_release'] = '0';
		}
		
		////share keystore配置参数
		$param['key_alias'] = trim($_REQUEST['key_alias']);
		if(empty($param['key_alias'])) {
			$param['key_alias'] = BigAppConf::$defaultShareConfig['key_alias'];
		}
		$param['store_password'] = trim($_REQUEST['store_password']);
		if(empty($param['store_password'])) {
			$param['store_password'] = BigAppConf::$defaultShareConfig['store_password'];
		}
		
		$param['key_password'] = trim($_REQUEST['key_password']);
		if(empty($param['key_password'])) {
			$param['key_password'] = BigAppConf::$defaultShareConfig['key_password'];
		}
		$tmpFile = $_FILES['key_store_file']['tmp_name'];
		runlog('bigapp', 'upload keystore file, error number is ' . $_FILES['key_store_file']['error']);
		
		//$flag = empty($shareSettingInfo['data']) && (1 == $_REQUEST['os'] || 3 == $_REQUEST['os']);
		if(!isset($shareSettingInfo['data']['key_store_content']) && $tmpFile === "" || empty($shareSettingInfo['data']['key_store_content'])) {
			$param['flag'] = "false";
			//showmessage("key store file should be uploaded, otherwise couldn't package app.");
		}
		//本次未提交key store文件，使用原来的key store文件信息
		if($tmpFile === "") {
			$param['key_store_content'] = $shareSettingInfo['data']['key_store_content'];
			$param['key_store_name'] = $shareSettingInfo['data']['key_store_name'];
		} else {
			//读取本次的提交的key store文件信息
			$content = read($tmpFile);
			$param['key_store_content'] = base64_encode($content);
			$param['key_store_name'] = $_FILES['key_store_file']['name'];
		}
		
		//showmessage($param['key_store_content']);
			
		////share plat配置参数
		$param['app_id_wechat'] = trim($_REQUEST['app_id_wechat']);
		$param['sec_key_wechat'] = trim($_REQUEST['sec_key_wechat']);
		$param['app_id_qq'] = trim($_REQUEST['app_id_qq']);
		$param['sec_key_qq'] = trim($_REQUEST['sec_key_qq']);
		$param['app_id_sina'] = trim($_REQUEST['app_id_sina']);
		$param['sec_key_sina'] = trim($_REQUEST['sec_key_sina']);
        $param['redirect_url_sina'] = trim($_REQUEST['redirect_url_sina']);

		if(empty($param['app_id_wechat']) || empty($param['sec_key_wechat'])) {
			$param['flag_wechat'] = '0';
			$param['app_id_wechat'] = BigAppConf::$defaultShareConfig['app_id_wechat'];
			$param['sec_key_wechat'] = BigAppConf::$defaultShareConfig['sec_key_wechat'];
		} else {
			if($param['app_id_wechat'] === BigAppConf::$defaultShareConfig['app_id_wechat'] &&
			   $param['sec_key_wechat'] === BigAppConf::$defaultShareConfig['sec_key_wechat']) {
				   $param['flag_wechat'] = '0';
			} else {
				$param['flag_wechat'] = '1';
			}
		}
		
		if(empty($param['app_id_qq']) || empty($param['sec_key_qq'])) {
			$param['flag_qq'] = '0';
			$param['app_id_qq'] = BigAppConf::$defaultShareConfig['app_id_qq'];
			$param['sec_key_qq'] = BigAppConf::$defaultShareConfig['sec_key_qq'];
		} else {
			if($param['app_id_qq'] === BigAppConf::$defaultShareConfig['app_id_qq'] &&
			   $param['sec_key_qq'] === BigAppConf::$defaultShareConfig['sec_key_qq']) {
				   $param['flag_qq'] = '0';
			} else {
				$param['flag_qq'] = '1';
			}
		}
		
		if(empty($param['app_id_sina']) || empty($param['sec_key_sina'])) {
			$param['flag_sina'] = '0';
			$param['app_id_sina'] = BigAppConf::$defaultShareConfig['app_id_sina'];
			$param['sec_key_sina'] = BigAppConf::$defaultShareConfig['sec_key_sina'];
			$param['redirect_url_sina'] = BigAppConf::$defaultShareConfig['redirect_url_sina'];
		} else {
			if($param['app_id_sina'] === BigAppConf::$defaultShareConfig['app_id_sina'] &&
			   $param['sec_key_sina'] === BigAppConf::$defaultShareConfig['sec_key_sina'] &&
			   $param['redirect_url_sina'] === BigAppConf::$defaultShareConfig['redirect_url_sina']) {
				   $param['flag_sina'] = '0';
			} else {
				$param['flag_sina'] = '1';
			}
		}

		if(isset($_GET['force']) && $_GET['force']){
			$param['force'] = 1;
		}
		$data = $obj->getInfoByPost(BigAppConf::$taskCreateUrl, $param, false);
		if(200814 == $data['error_code']){
			runlog('bigapp', "create task failed, too many tasks today");
			
			showmessage(lang('plugin/bigapp', 'task_limit_exceed'), $_G['siteurl'] . '/admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=bigapp&pmod=buildapp');	
		}
		if(200801 == $data['error_code']){
			runlog('bigapp', "create task failed, package name conflict");
			showmessage(lang('plugin/bigapp', 'pkgname_conflict'), $_G['siteurl'] . '/admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=bigapp&pmod=buildapp');	
		}
		if(200825 == $data['error_code']){
			runlog('bigapp', "create task failed, plugin need be updated");
			showmessage(lang('plugin/bigapp', 'plugin_update'), $_G['siteurl'] . '/admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=bigapp&pmod=buildapp');	
		}
		if(0 != $data['error_code']){
			runlog('bigapp', "create task failed, error code: " . $data['error_code']);
			showmessage(lang('plugin/bigapp', 'create_task_fail'), $_G['siteurl'] . '/admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=bigapp&pmod=buildapp');
		}
		$data = $data['data'];
		if(isset($data['task_id'])){
			$setting = array('push_enabled' => $param['push_enabled']);
			$settings = array('bigapp_push_config' => $setting);
			C::t('common_setting')->update_batch($settings);
			runlog('bigapp', "create task succ, task id: " . $data['task_id']);
			showmessage(lang('plugin/bigapp', 'create_task_succ'), $_G['siteurl'] . 
					'/admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=bigapp&pmod=myapp');
		}else{
			runlog('bigapp', "create task failed");
			showmessage(lang('plugin/bigapp', 'create_task_fail'));
		}
		exit;
	}
	//3. 展示任务的设置信息
	//3.1 获取最近一次任务
	runlog('bigapp', "show latest task info");
	$confInfo = $obj->getInfo(BigAppConf::$taskInfoUrl, array('app_id' => $appInfo['app_id'], 'method' => 'get_latest'));
	//获取失败，则使用默认设置
	if(!is_array($confInfo)){
		runlog('bigapp', "get latest task info failed, use default");
		$confInfo = array('task_id' => 0, 'task_info' => BigAppConf::$defaultConfig);
	}
	//对没返回的字段设置默认字段
	foreach (array_keys(BigAppConf::$defaultConfig) as $key){
		if(!isset($confInfo['task_info'][$key]) || empty($confInfo['task_info'][$key])){
			runlog('bigapp', "key $key does not exists in data, use default: " . BigAppConf::$defaultConfig[$key]);
			$confInfo['task_info'][$key] = BigAppConf::$defaultConfig[$key];
		}
	}
	if(BigAppConf::$defaultConfig['package_name'] === $confInfo['task_info']['package_name']){
		$confInfo['task_info']['package_name'] = Utils::getDefPkg();
		runlog('bigapp', 'package name is default, generate a new one [ ' . $confInfo['task_info']['package_name'] . ' ]');
	}

	//如果没有配置BBS名字，则默认获取PC的BBS名字
	if(0 == $confInfo['task_id']){
		$confInfo['task_info']['bbs_name'] = getDefaultBBName();
		$confInfo['task_info']['app_name'] = $confInfo['task_info']['bbs_name'];
	}
	if(function_exists('mb_strlen') && function_exists('mb_substr')){
		if(@mb_strlen($confInfo['task_info']['bbs_name'], 'UTF-8') > 10){
			$confInfo['task_info']['bbs_name'] = @mb_substr($confInfo['task_info']['bbs_name'], 0, 10, 'UTF-8');
			$confInfo['task_info']['app_name'] = @mb_substr($confInfo['task_info']['bbs_name'], 0, 10, 'UTF-8');
		}
	}
	showAppInfo($confInfo['create_time']);
	showTaskInfo($appInfo, $confInfo, $shareSettingInfo);
}else{
	runlog('bigapp', 'get ak sk failed, give up');
	showmessage(lang('plugin/bigapp', 'get_ak_sk_fail'));
}

function getDefaultBBName()
{
	global $_G;
	$ret = '';
	if(isset($_G['setting']['bbname']) && !empty($_G['setting']['bbname'])){
		if(function_exists('iconv')){
			$ret = @iconv(CHARSET, 'UTF-8//ignore', $_G['setting']['bbname']);
		}else{
			$ret = @mb_convert_encoding($_G['setting']['bbname'], 'UTF-8', CHARSET);
		}
		$ret = preg_replace('/[!-,.\/:-@\[-`{-~]/', '', $ret);
		if(function_exists('mb_strlen') && function_exists('mb_substr')){
			if(@mb_strlen($ret, 'UTF-8') > 10){
				$ret = @mb_substr($ret, 0, 10, 'UTF-8');
			}
		}
		runlog('bigapp', 'task id is 0, get bbs name [ ' . $ret . ' ]');
	}
	return $ret;
}

function read($file) {
	$os = substr(PHP_OS, 0, 3);
	$os = strtoupper($os);
	
	if('WIN' !== $os && (!is_file($file) || !is_readable($file))){
		return "";
	} else {
		$buf = file_get_contents($file);
		runlog('bigapp', 'read keystore file succ, file length [ ' . strlen($buf) . ' ]');
	}
	
	return $buf;
}
function checkPkgName($pkgName)
{
	if(!empty($pkgName)){
		$arr = explode('.', $pkgName);
		foreach ($arr as $v)
		{
			if(!preg_match('/^[a-zA-Z]+$/', $v)){
				runlog('bigapp', "invalid package name [ package name: $pkgName ]");
				showmessage(lang('plugin/bigapp', 'invalid_pkgname'), '', array(), array('alert' => 'error'));				
			}
		}
	}
}

function showAppInfo($taskTime)
{
	$timeInfo = '<font color="red"><strong>' . lang('plugin/bigapp', 'package_remind') . '</strong></font>';
	if(0 != $taskTime){
		$timeInfo = date('Y-m-d H:i:s', $taskTime);
	}
	global $_G;
	$str = '<li>' . lang('plugin/bigapp', 'discuz_version') . ': ' . $_G['setting']['version'] . '</li>';
	$str .= '<li>' . lang('plugin/bigapp', 'plugin_version') . ': ' . $_G['setting']['plugins']['version']['bigapp'] . '</li>';
	$str .= '<li>' . lang('plugin/bigapp', 'last_create_time') . ': ' . $timeInfo . '</li>';
	showtips($str, '', true, lang('plugin/bigapp', 'tip_title1'));
}
function prepCreateTask()
{
	global $_G;
	$param = array();
	$oldParam = array();
	if(isset($_GET['old_params'])){
		$oldParam = BIGAPPJSON::decode($_GET['old_params'], true);
	}
	//$arrNeedParam = array('nav_color', 'icon_image', 'startup_image', 'os', 'plugin_version', 'bbs_name', 'app_name', 'package_name', 'channel_name');
    //////////////////////////////////////////////////////
	$arrNeedParam = array('nav_color', 'icon_image', 'startup_image', 'os', 'plugin_version', 'bbs_name', 'app_name', 'package_name', 'channel_name','version_name', 'push_enabled',
		'key_alias', 'store_password', 'key_password', 'app_id_wechat', 'sec_key_wechat', 'app_id_qq', 'sec_key_qq', 'app_id_sina','sec_key_sina', 'jpush_app_key', 'jpush_master_secret', 'jpush_is_free');
    /////////////////////////////////////////////////////
	$param['method'] = 'create';
	$param['plugin_version'] = '0.0.0';
	if(isset($_G['setting']['plugins']['version']['bigapp'])){
		$param['plugin_version'] = $_G['setting']['plugins']['version']['bigapp'];
	}
	//$param['plugin_version'] = '2.0.0';//测试用
	if(isset($_REQUEST['os']) && is_array($_REQUEST['os']) && !empty($_REQUEST['os'])){
		foreach ($_REQUEST['os'] as $v){
			$os += (1 << $v);
		}
		$_REQUEST['os'] = $os;
	}
	foreach ($arrNeedParam as $key){
		if(isset($_REQUEST[$key])){
			if('icon_image' === $key || 'startup_image' === $key){
				$_REQUEST[$key] = htmlspecialchars_decode($_REQUEST[$key]);
			}
			$param[$key] = $_REQUEST[$key];
		}
	}
	checkParams($param);
	return $param;
}

function checkParams($param)
{
	if(isset($param['package_name'])){
		checkPkgName($param['package_name']);
	}
	if(isset($param['os']) && !in_array($param['os'], array(1, 2, 3))){
		runlog('bigapp', "invalid os type [ os type: " . $param['os'] . " ]");
		showmessage(lang('plugin/bigapp', 'invalid_os'), '', array(), array('alert' => 'error'));
	}
	if(isset($param['app_name']) && preg_match('/[!-,.\/:-@\[-`{-~]/', $param['app_name'])){
		runlog('bigapp', "invalid app name [ app name: " . $param['app_name'] . " ]");
		showmessage(lang('plugin/bigapp', 'invalid_app_name'), '', array(), array('alert' => 'error'));
	}
	if(isset($param['channel_name']) && preg_match('/[!-,.\/:-@\[-`{-~]/', $param['channel_name'])){
		runlog('bigapp', "invalid channel name [ channel name: " . $param['channel_name'] . " ]");
		showmessage(lang('plugin/bigapp', 'invalid_channel_name'), '', array(), array('alert' => 'error'));
	}
	if(isset($param['bbs_name']) && preg_match('/[!-,.\/:-@\[-`{-~]/', $param['bbs_name'])){
		runlog('bigapp', "invalid bbs name [ bbs name: " . $param['bbs_name'] . " ]");
		showmessage(lang('plugin/bigapp', 'invalid_bbs_name'), '', array(), array('alert' => 'error'));
	}
	if(isset($param['nav_color']) && !preg_match('/^#[0-9A-Za-z]{6,6}$/', $param['nav_color'])){
		runlog('bigapp', "invalid nav color [ nav color: " . $param['nav_color'] . " ]");
		showmessage(lang('plugin/bigapp', 'invalid_nav_color'), '', array(), array('alert' => 'error'));
	}
	if(isset($param['app_name'])){
		if(function_exists('mb_strlen') && (@mb_strlen($param['app_name'], CHARSET) > 10 || @mb_strlen($param['app_name'], CHARSET) < 1)){	
			runlog('bigapp', "invalid app name length [ app_name: " . $param['app_name'] . ", len: " . @mb_strlen($param['app_name'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'invalid_app_name_len'), '', array(), array('alert' => 'error'));
		}
	}
	if(isset($param['bbs_name'])){
		if(function_exists('mb_strlen') && (@mb_strlen($param['bbs_name'], CHARSET) > 10 || @mb_strlen($param['bbs_name'], CHARSET) < 1)){	
			runlog('bigapp', "invalid bbs name length [ bbs_name: " . $param['bbs_name'] . ", len: " . @mb_strlen($param['bbs_name'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'invalid_bbs_name_len'), '', array(), array('alert' => 'error'));
		}
	}
    ////////////////////////////////////////////////////
    // add by mawentao
    if (isset($param['version_name'])) {
        if (!preg_match("/^\d+\.\d+(\.\d+?(\.\d+)?)?$/i", $param['version_name'])) {
            showmessage(lang('plugin/bigapp', 'invalid_version_name'), '', array(), array('alert' => 'error'));
        }
    }
    ////////////////////////////////////////////////////
	if (isset($param['key_alias'])) {
		if(function_exists('mb_strlen') && (@mb_strlen($param['key_alias'], CHARSET) > 40)) {
			runlog('bigapp', "invalid key_alias length [ key_alias: " . $param['key_alias'] . ", len: " . @mb_strlen($param['key_alias'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'exceed_maxium_len'), '', array(), array('alert' => 'error'));
		}
	}
	
	if (isset($param['store_password'])) {
		if(function_exists('mb_strlen') && (@mb_strlen($param['store_password'], CHARSET) > 40)) {
			runlog('bigapp', "invalid store_password length [ store_password: " . $param['store_password'] . ", len: " . @mb_strlen($param['store_password'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'exceed_maxium_len'), '', array(), array('alert' => 'error'));
		}
	}
	
	if (isset($param['key_password'])) {
		if(function_exists('mb_strlen') && (@mb_strlen($param['key_password'], CHARSET) > 40)) {
			runlog('bigapp', "invalid key_password length [ key_password: " . $param['key_password'] . ", len: " . @mb_strlen($param['key_password'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'exceed_maxium_len'), '', array(), array('alert' => 'error'));
		}
	}
	
	if (isset($param['app_id_wechat'])) {
		if(function_exists('mb_strlen') && (@mb_strlen($param['app_id_wechat'], CHARSET) > 40)) {
			runlog('bigapp', "invalid app_id_wechat length [ app_id_wechat: " . $param['app_id_wechat'] . ", len: " . @mb_strlen($param['app_id_wechat'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'exceed_maxium_len'), '', array(), array('alert' => 'error'));
		}
	}
	
	if (isset($param['sec_key_wechat'])) {
		if(function_exists('mb_strlen') && (@mb_strlen($param['sec_key_wechat'], CHARSET) > 40)) {
			runlog('bigapp', "invalid sec_key_wechat length [ sec_key_wechat: " . $param['sec_key_wechat'] . ", len: " . @mb_strlen($param['sec_key_wechat'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'exceed_maxium_len'), '', array(), array('alert' => 'error'));
		}
	}
	
	if (isset($param['app_id_qq'])) {
		if(function_exists('mb_strlen') && (@mb_strlen($param['app_id_qq'], CHARSET) > 40)) {
			runlog('bigapp', "invalid app_id_qq length [ app_id_qq: " . $param['app_id_qq'] . ", len: " . @mb_strlen($param['app_id_qq'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'exceed_maxium_len'), '', array(), array('alert' => 'error'));
		}
	}
	
	if (isset($param['sec_key_qq'])) {
		if(function_exists('mb_strlen') && (@mb_strlen($param['sec_key_qq'], CHARSET) > 40)) {
			runlog('bigapp', "invalid sec_key_qq length [ sec_key_qq: " . $param['sec_key_qq'] . ", len: " . @mb_strlen($param['sec_key_qq'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'exceed_maxium_len'), '', array(), array('alert' => 'error'));
		}
	}
	
	if (isset($param['app_id_sina'])) {
		if(function_exists('mb_strlen') && (@mb_strlen($param['app_id_sina'], CHARSET) > 40)) {
			runlog('bigapp', "invalid app_id_sina length [ app_id_sina: " . $param['app_id_sina'] . ", len: " . @mb_strlen($param['app_id_sina'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'exceed_maxium_len'), '', array(), array('alert' => 'error'));
		}
	}
	
	if (isset($param['sec_key_sina'])) {
		if(function_exists('mb_strlen') && (@mb_strlen($param['sec_key_sina'], CHARSET) > 40)) {
			runlog('bigapp', "invalid sec_key_sina length [ sec_key_sina: " . $param['sec_key_sina'] . ", len: " . @mb_strlen($param['sec_key_sina'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'exceed_maxium_len'), '', array(), array('alert' => 'error'));
		}
	}
	
	if (isset($param['redirect_url_sina'])) {
		if(function_exists('mb_strlen') && (@mb_strlen($param['redirect_url_sina'], CHARSET) > 40)) {
			runlog('bigapp', "invalid redirect_url_sina length [ redirect_url_sina: " . $param['redirect_url_sina'] . ", len: " . @mb_strlen($param['redirect_url_sina'], CHARSET) . " ]");	
			showmessage(lang('plugin/bigapp', 'exceed_maxium_len'), '', array(), array('alert' => 'error'));
		}
	}
	if(isset($param['push_enabled']) && $param['push_enabled']){
		if(!isset($param['jpush_app_key']) || empty($param['jpush_app_key']) || !isset($param['jpush_master_secret']) || empty($param['jpush_master_secret'])){
			runlog('bigapp', 'push enabled, but jpush app_key or jpush master secret is empty');
			showmessage(lang('plugin/bigapp', 'push_param_error'), '', array(), array('alert' => 'error'));
		}
	}
}

	function showTaskInfo($appInfo, $confInfo, $shareSettingInfo)
	{
		global $_G;
		$force = '&force=1';
		if(isset($_GET['force']) && $_GET['force']){
			$force = '&force=' . $_GET['force'];
		}
		if(function_exists('iconv')){
			$confInfo['task_info']['app_name'] = iconv('UTF-8', CHARSET . '//ignore', $confInfo['task_info']['app_name']);
			$confInfo['task_info']['channel_name'] = iconv('UTF-8', CHARSET . '//ignore', $confInfo['task_info']['channel_name']);
			$confInfo['task_info']['bbs_name'] = iconv('UTF-8', CHARSET . '//ignore', $confInfo['task_info']['bbs_name']);
		}else{
			$confInfo['task_info']['app_name'] = mb_convert_encoding($confInfo['task_info']['app_name'], CHARSET, 'UTF-8');
			$confInfo['task_info']['channel_name'] = mb_convert_encoding($confInfo['task_info']['channel_name'], CHARSET, 'UTF-8');
			$confInfo['task_info']['bbs_name'] = mb_convert_encoding($confInfo['task_info']['bbs_name'], CHARSET, 'UTF-8');
		}
		showformheader('plugins&operation=config&do='.$pluginid.'&identifier=bigapp&pmod=buildapp&createtask=1&task_id=' . $confInfo['task_id'] . $force, 'enctype', 'newtask');
		showtableheader(lang('plugin/bigapp', 'conf_tip'));
		echo '<tr><td style="line-height:30px;color:red;font-weight:bold">' . lang('plugin/bigapp', 'conf_comment') . '</td></tr>';
		showtablefooter();
		showtableheader('');
		showsetting(lang('plugin/bigapp', 'app_name'), 'app_name', $confInfo['task_info']['app_name'], 
				'text', 0, 0, lang('plugin/bigapp', 'app_name_comment'), '', '', true);
		showsetting(lang('plugin/bigapp', 'package_name'), 'package_name', $confInfo['task_info']['package_name'], 
				'text', 0, 0, lang('plugin/bigapp', 'package_name_comment'), '', '', true);
		showsetting(lang('plugin/bigapp', 'channel_name'), 'channel_name', $confInfo['task_info']['channel_name'], 
				'text', 0, 0, lang('plugin/bigapp', 'channel_name_comment'), '', '', true);
		showsetting(lang('plugin/bigapp', 'bbs_name'), 'bbs_name', $confInfo['task_info']['bbs_name'], 
				'text', 0, 0, lang('plugin/bigapp', 'bbs_name_comment'), '', '', true);
		echo '<tr><td style="line-height:30px;font-weight:bold">' . lang('plugin/bigapp', 'nav_color') . ': </td></tr>';
		echo '<tr class="noborder" onmouseover="setfaq(this, \'faqbda4\')"><td class="vtop rowform"><input type="text" name="nav_color" style="width:250px" ' . 
				'id="color" value="' . $confInfo['task_info']['nav_color'] . '" /></td><td class="vtop tips2" s="1">' . lang('plugin/bigapp', 'nav_color_comment') . '</td></tr>';
		//echo '<tr class="noborder" onmouseover="setfaq(this, \'faqbda4\')"><td class="vtop rowform"> <input type="text" name="nav_color" value="' . 
		//		$confInfo['task_info']['nav_color'] . '" style= "color:' . $confInfo['task_info']['nav_color'] . 
		//		';width:250px" id="nav_color" /></td><td class="vtop tips2" s="1">' . lang('plugin/bigapp', 'nav_color_comment') . '</td></tr>';
		$iconUrl = rtrim($_G['siteurl'], '/') . '/' . BigAppConf::$upfileUrl . '&key=' . urlencode('icon_image_s');
		$startupUrl = rtrim($_G['siteurl'], '/') . '/' . BigAppConf::$upfileUrl . '&key=' . urlencode('startup_image_s');
		$tpl = @file_get_contents(dirname(__FILE__) . '/view/input-file.tpl');
		if(is_string($tpl) && strtolower(CHARSET) != 'utf-8' && strtolower(CHARSET) != 'utf8'){
			if(function_exists('iconv')){
				$tpl = @iconv('UTF-8', 'GBK//ignore', $tpl);
			}else if(function_exists('mb_convert_encoding')){
				$tpl = @mb_convert_encoding($tpl, 'GBK', 'UTF-8');
			}
		}
		$tpl = str_replace('<% btn_name %>', lang('plugin/bigapp', 'btn_name'), $tpl);
		$tpl = str_replace('<% upload_url_icon %>', $iconUrl, $tpl);
		$tpl = str_replace('<% error_str %>', lang('plugin/bigapp', 'upload_file_failed'), $tpl);
		$tpl = str_replace('<% upid_icon %>', 'icon_image', $tpl);
		$tpl = str_replace('<% file_tip_icon %>', lang('plugin/bigapp', 'icon_image_comment'), $tpl);
		$tpl = str_replace('<% upload_url_startup %>', $startupUrl, $tpl);
		$tpl = str_replace('<% upid_startup %>', 'startup_image', $tpl);
		$tpl = str_replace('<% file_tip_startup %>', lang('plugin/bigapp', 'startup_image_comment'), $tpl);
		$tpl = str_replace('<% icon_image_title %>', lang('plugin/bigapp', 'icon_image'), $tpl);
		$tpl = str_replace('<% startup_image_title %>', lang('plugin/bigapp', 'startup_image'), $tpl);
		$tpl = str_replace('<% app_charset %>', CHARSET, $tpl);
		echo $tpl;

		$var = array('os', array(
					array(0, 'android'),
					array(1, 'ios'),
					)
				);
		$tmp = $confInfo['task_info']['os'];
		$i = 0;
		$val = array();
		while($tmp != 0){
			if(1 == ($tmp & 1)){
				$val[] = $i;
			}
			$i++;
			$tmp = ($tmp >> 1);
		}
		
		$var1 = array('choose_release', array(
					array(0, lang('plugin/bigapp', 'choose_release_title')),
					)
				);
        //////////////////////////////////////
		showsetting(lang('plugin/bigapp', 'version_name'), 'version_name', $confInfo['task_info']['version_name'], 
				'text', 0, 0, lang('plugin/bigapp', 'version_name_comment'), '', '', true);
/*
		echo '<tr><td style="line-height:30px;font-weight:bold">APP版本号: </td></tr>';
        echo '<tr class="noborder" onmouseover="setfaq(this, \'faq7088\')">'.
             '<td class="vtop rowform">'.
             '  <select name="app_version" id="app_version" class="txt" style="cursor:pointer;"></select></td>'.
             '<td class="vtop tips2" s="1">输入两位或三位版本号（如1.0.2, 2.1）</td></tr>';
*/
        //////////////////////////////////////
             
	    echo '<tr><td style="line-height:30px;color:red;font-weight:bold">'.lang('plugin/bigapp', 'key_store_setting').'</td></tr>';
		
		//key store别名
       showsetting(lang('plugin/bigapp', 'key_alias'), 'key_alias', $shareSettingInfo['data']['key_alias'], 
				'text', 0, 0, lang('plugin/bigapp', 'key_alias_comment'), '', '', true);
       
		//秘钥库密码
		showsetting(lang('plugin/bigapp', 'store_password').'(store password)', 'store_password', $shareSettingInfo['data']['store_password'], 
				'text', 0, 0, lang('plugin/bigapp', 'store_password_comment'), '', '', true);
		//key store密码
		showsetting(lang('plugin/bigapp', 'key_password').'(key password)', 'key_password', $shareSettingInfo['data']['key_password'], 
				'text', 0, 0, lang('plugin/bigapp', 'key_password_comment'), '', '', true);
				
		//key store文件上传
		showsetting(lang('plugin/bigapp', 'key_store_file'), 'key_store_file', $shareSettingInfo['data']['key_store_name'], 
				'file', 0, 0, lang('plugin/bigapp', 'key_store_file_comment'), '', '', true);
		
		$val1[0] = 1;		
		showsetting(lang('plugin/bigapp', ''), $var1, $val1, 'mcheckbox', 0, 0, lang('plugin/bigapp', 'choose_release_msg'), '', '', true);
		
	
		$shareInfo = (Array)json_decode($shareSettingInfo['data']['share_plat'], true);
		
		echo '<tr><td style="line-height:30px;color:red;font-weight:bold">'.lang('plugin/bigapp', 'share_plat_setting').'</td></tr>';
		echo '<tr><td style="line-height:30px;font-weight:bold">'.lang('plugin/bigapp', 'wechat').'</td></tr>';
       showsetting('App ID', 'app_id_wechat', $shareInfo[0]['app_id'], 
				'text', 0, 0, lang('plugin/bigapp', ''), '', '', true);
       showsetting('Secret Key',  'sec_key_wechat', $shareInfo[0]['sec_key'], 
				'text', 0, 0, lang('plugin/bigapp', ''), '', '', true);
				
       echo '<tr><td style="line-height:30px;font-weight:bold">QQ</td></tr>';
       showsetting('App ID', 'app_id_qq', $shareInfo[1]['app_id'], 
				'text', 0, 0, lang('plugin/bigapp', ''), '', '', true);
       showsetting('Secret Key',  'sec_key_qq', $shareInfo[1]['sec_key'], 
				'text', 0, 0, lang('plugin/bigapp', ''), '', '', true);

       echo '<tr><td style="line-height:30px;font-weight:bold">'.lang('plugin/bigapp', 'sina').'</td></tr>';
       
       showsetting('App ID', 'app_id_sina', $shareInfo[2]['app_id'], 
				'text', 0, 0, lang('plugin/bigapp', ''), '', '', true);
        showsetting('Secret Key',  'sec_key_sina', $shareInfo[2]['sec_key'], 
				'text', 0, 0, lang('plugin/bigapp', ''), '', '', true);	
		showsetting('Redirect Url',  'redirect_url_sina', $shareInfo[2]['redirect_url_sina'], 
				'text', 0, 0, lang('plugin/bigapp', ''), '', '', true);

		showsetting(lang('plugin/bigapp', 'app_type'), $var, $val, 'mcheckbox', 0, 0, lang('plugin/bigapp', 'app_type_comment'), '', '', true);
		//for jpush start
		echo '<tr><th colspan="15" class="partition">' . lang('plugin/bigapp', 'push_config') . '</th></tr>';
		echo '<td colspan="2" class="td27" s="1">' . lang('plugin/bigapp', 'push_tip') . '</td>';
		$jpushInfo = $confInfo['task_info'];
		if(false === $jpushInfo){
    		runlog('bigapp', 'get jpush info failed, use empty info');
		}
		if(!isset($jpushInfo['jpush_app_key'])){
			$jpushInfo['jpush_app_key'] = '';
		}
		if(!isset($jpushInfo['jpush_master_secret'])){
			$jpushInfo['jpush_master_secret'] = '';
		}
		if(!isset($jpushInfo['jpush_is_free'])){
    		$jpushInfo['jpush_is_free'] = 0;
		}
		if($jpushInfo['jpush_is_free'] != 0){
			$jpushInfo['jpush_is_free'] = 1;
		}
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
		$pushEnabled = array('push_enabled',
                array(
                    array(0, lang('plugin/bigapp', 'no')),
                    array(1, lang('plugin/bigapp', 'yes')),
                )
              );
		showsetting(lang('plugin/bigapp', 'push_enabled'), $pushEnabled, $_G['setting']['bigapp_push_config']['push_enabled'], 'mradio', 0, 0, lang('plugin/bigapp', 'push_enabled_comment'));
		showsetting(lang('plugin/bigapp', 'jpush_app_key'), 'jpush_app_key', $jpushInfo['jpush_app_key'], 'text', 0, 0, lang('plugin/bigapp', 'app_key_comment'), 'id="app_key"', '', true);
		showsetting(lang('plugin/bigapp', 'jpush_master_secret'), 'jpush_master_secret', $jpushInfo['jpush_master_secret'], 'text', 0, 0, lang('plugin/bigapp', 'master_secret_comment'), 'id=master_secret', '', true);
		$isFreeAccount = array('jpush_is_free',
                array(
                    array(0, lang('plugin/bigapp', 'no')),
                    array(1, lang('plugin/bigapp', 'yes')),
                )
              );
		showsetting(lang('plugin/bigapp', 'is_free_account'), $isFreeAccount, $jpushInfo['jpush_is_free'], 'mradio', 0, 0, lang('plugin/bigapp', 'is_free_account_comment'));		
		//for jpush end
		showsubmit('newtask', lang('plugin/bigapp', 'start_to_go'), '', '', '', false);
		showtablefooter();
		showformfooter();
	}
	runlog('bigapp', 'show build app page succ');
	$str = <<<EOF1
		<script type="text/javascript">
		var jq = jQuery.noConflict()
		jq(function(){
				jq("#nav_color").colorpicker({
fillcolor:true
});
				});
		jq(function() {
        jq('#color').colorpicker({
            history:false,
            strings: "%sPalette,Historique,Pas encore d'historique."
        });
    });
</script>
EOF1;
	$output = sprintf($str, lang('plugin/bigapp', 'color_picker_tips'));
	echo $output;
?>
