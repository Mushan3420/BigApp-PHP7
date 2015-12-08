<?php
/**
* @file myapp.inc.php
* @Brief my app page for admin center
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
echo '<script type="text/javascript" src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/jquery.js' . '"></script>';
echo '<script type="text/javascript" src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/qr.js' . '"></script>';
echo '<script type="text/javascript">var jq = jQuery.noConflict();</script>';  //avoid jquery conflict

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
	if(is_array($appInfo)){
		//get latest task info, which should not be error
		$confInfo = $obj->getInfo(BigAppConf::$taskInfoUrl, array('app_id' => $appInfo['app_id'], 'method' => 'get_latest'));
		if(is_array($confInfo)){
			if($confInfo['task_id'] == 0){
				runlog('bigapp', 'task id is 0, remind and exit');
				showmessage(lang('plugin/bigapp', 'package_remind'), $_G['siteurl'] . '/admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=bigapp&pmod=buildapp');
			}
			//task exists now, show it
			runlog('bigapp', 'show task schedule now');
			$exit = showRTitle($appInfo['app_id']);
			if(0 === $exit){
				showTaskResult($appInfo, $confInfo);
			}
		}else{
			showmessage(lang('plugin/bigapp', 'unknown_error'), $_G['siteurl'] . 
					'/admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=bigapp&pmod=buildapp', array(), array('alert' => 'error'));
		}
	}else{
		Utils::jumpToLogin('invalid_aksk', 'buildapp');
	}
}

function getDefaultBBName()
{
    global $_G;
    $ret = '';
    if(isset($_G['setting']['bbname']) && !empty($_G['setting']['bbname'])){
		$ret = $_G['setting']['bbname'];
		$ret = preg_replace('/[!-,.\/:-@\[-`{-~]/', '', $ret);
        if(function_exists('mb_strlen') && function_exists('mb_substr')){
            if(@mb_strlen($ret, CHARSET) > 10){
                $ret = @mb_substr($ret, 0, 10, CHARSET);
            }
        }
        runlog('bigapp', 'task id is 0, get bbs name [ ' . $ret . ' ]');
    }
    return $ret;
}

function showRTitle($appId)
{
	global $ak, $sk, $_G, $pluginid;
	$str = '';
	$str .= '<li>' . sprintf(lang('plugin/bigapp', 'myapp_tip1'), $_G['setting']['plugins']['version']['bigapp'] ) . '</li>';
	$str .= '<li>' . lang('plugin/bigapp', 'myapp_tip2') . $_G['setting']['version'] . '</li>';
	$str .= '<li>' . lang('plugin/bigapp', 'myapp_tip3') . '</li>';
	//$str .= '<li>' . lang('plugin/bigapp', 'myapp_tip4') . '</li>';
	$str .= '<li style="color:red;font-weight:bold">' . lang('plugin/bigapp', 'jump2conf_tip1') . 
			'<a href="' . rtrim($_G['siteurl'], '/') . '/admin.php?action=plugins&operation=config&do=' . 
			$pluginid . '&identifier=bigapp&pmod=buildapp' . '">' . lang('plugin/bigapp', 'menu_app_gen') . 
			'</a>' . lang('plugin/bigapp', 'jump2conf_tip2') . '</li>';
    showtips($str, '', true);
	
	$str = '';
	$str .= '<li>APP_KEY: ' . $ak  . '</li>';
	$str .= '<li>APP_SECRET: ' . $sk  . '</li>';
	$title = lang('plugin/bigapp', 'basic_setting');
	$be = checkBoost();
	$exit = 0;
	if(0 !== $be){
		if(1 == $be){
			$str = '<li>' . lang('plugin/bigapp', 'boost_file_miss1') . '</li>';
		}else{
			$str = '<li>' . lang('plugin/bigapp', 'boost_file_miss2') . '</li>';
		}
		$exit = 1;
		$title = lang('plugin/bigapp', 'api_invalid');
	}
	showtips($str, '', true, $title);
	showtablefooter();
	return $exit;
}

function checkBoost()
{
	global $_G;
	if(isset($_SERVER['SCRIPT_FILENAME'])){
		$dir = dirname($_SERVER['SCRIPT_FILENAME']);
		$file = $dir . '/api/mobile/iyz_index.php';
		if(function_exists('is_file')){
			if(!is_file($file)){
				runlog('bigapp', 'use is_file to check boost file failed');
				return 1; //absolutely missed
			}
			runlog('bigapp', 'use is_file to check boost file succ');
			return 0; //file already exists
		}
	}
	if(isset($_G['siteurl'])){
		$url = $_G['siteurl'] . 'api/mobile/iyz_index.php?iyzmobile=1&check=check&json=1';
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $data = curl_exec($ch);
        $errorInfo = curl_error($ch);
        if(!empty($errorInfo)){
            runlog('bigapp', 'use curl to check boost file failed, url: ' . $url . ', error: ' . $errorInfo);
			return 0; //no method to check boost file
        }
        curl_close($ch);
		$arrData = json_decode($data, true);
		if(is_array($arrData)){
			runlog ('bigapp', 'use curl to check api interface succ, url: ' . $url);
			return 0;
		}
		runlog('bigapp', 'use curl to check boost file failed, invalid returned data [ data: ' . $data . ' ]');
		return 2; //possiblely missed
	}
	return 0; //no method to check boost file
}

function showTaskResult($appInfo, $taskInfo)
{
	global $ak, $pluginid, $_G;
	$appIcon = $taskInfo['task_info']['icon_image'];
	$appName = $taskInfo['task_info']['app_name'];
	$url = Utils::addUrlQueryString(BigAppConf::$taskScheduleUrl, array('task_id' => $taskInfo['task_id'], 'app_key' => $ak));
	$tpl = file_get_contents(dirname(__FILE__) . '/view/appLoad.tpl');
	if(is_string($tpl) && strtolower(CHARSET) != 'utf-8' && strtolower(CHARSET) != 'utf8'){
		if(function_exists('iconv')){
			$tpl = @iconv('UTF-8', 'GBK//ignore', $tpl);
		}else if(function_exists('mb_convert_encoding')){
			$tpl = @mb_convert_encoding($tpl, 'GBK', 'UTF-8');
		}
	}
	$tpl = str_replace('<% app_charset %>', CHARSET, $tpl);
	$tpl = str_replace('<% app_icon %>', $appIcon, $tpl);
	if(function_exists('iconv')){
        $appName = iconv('UTF-8', CHARSET . '//ignore', $appName);
    }else{
        $appName = mb_convert_encoding($appName, CHARSET, 'UTF-8');
    }
	$tpl = str_replace('<% app_name %>', $appName, $tpl);
	$tpl = str_replace('<% schedule_url %>', $url, $tpl);
	$tpl = str_replace('<% error_url %>', rtrim($_G['siteurl'], '/') . '/admin.php?action=plugins&operation=config&do='.
			$pluginid.'&identifier=bigapp&pmod=buildapp&force=1', $tpl);
	echo $tpl; 
	runlog('bigapp', 'show my app page succ');
}
?>
