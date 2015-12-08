<?php
/**
* @file menu.inc.php
* @Brief menu for every page in admin center
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
require_once dirname(__FILE__) . "/env.inc.php";
require_once dirname(dirname(__FILE__)) . '/conf/conf.inc.php';
require_once libfile('function/core');
echo <<<EOF
<style>
.floattop { display: none; }
.floattopempty { display: none; }
.mymenu { height:35px; }
.mymenu .floattop { display: inline; }
.mymenu .floattopempty { display: inline; }
</style>
EOF;

$memuLang = array(
    "push"=>"推送设置",
    "jpush_account" => "推送测试",
    "home"=>"门户设置",
    "func1"=>"功能区设置",
    "func2"=>"热门区设置",
    "portal_category"=>"频道设置",
    "portal_home"=>"首页设置",
    "portal_home_title"=>"首页",
    "theme"=>"布局设置",
    "checkin" => '签到设置',
    "option_tab"=>"tab底部导航",
    "option_slide"=>"左侧抽屉导航",
    "yes"=>"是",
    "no"=>"否",
    "browser_tip" => '请使用<a href="http://www.google.cn/chrome/browser/desktop/index.html" target="_blank"><font style="color:red;font-weight:bold">chrome</font></a>或<a href="http://www.firefox.com.cn/" target="_blank"><font style="color:red;font-weight:bold">firefox</font></a>浏览器使用本插件',
    "broser_title" => '浏览器兼容提示',
    "threadsetting" => '推荐区设置',
    'menu_banner' => '焦点图设置',
    'menu_app_design' => '步骤4高级DIY',
);

foreach($memuLang as $k => &$v){
    $v = converMenuString($v);
}

if(isset($lang)){
    $lang = array_merge($lang,$memuLang);
}else{
    $lang = $memuLang;
}

echo '<div class="mymenu">';
$appinfo = BigappEnv::getAppInfoFromBigstation();
$appid = 0;
if (isset($appinfo["app_id"])) $appid = $appinfo["app_id"];
$pack_and_config_url = rtrim(BigAppConf::$mcapis["app"],"/")."/".$appid;

$menu = array(
            array(array('menu' => lang('plugin/bigapp', 'menu_app_config'), 'submenu' => array(
                        //array(lang('plugin/bigapp', 'menu_app_myapp'), 'plugins&operation=config&identifier=bigapp&pmod=myapp', $_GET['pmod'] == 'myapp'),
                        array(lang('plugin/bigapp', 'menu_app_certify'), 'plugins&operation=config&identifier=bigapp&pmod=certify', $_GET['pmod'] == 'certify'),
                        //array(lang('plugin/bigapp', 'menu_app_gen'), 'plugins&operation=config&identifier=bigapp&pmod=buildapp', $_GET['pmod'] == 'buildapp'),
                        //array(lang('plugin/bigapp', 'menu_app_release'), 'plugins&operation=config&identifier=bigapp&pmod=release', $_GET['pmod'] == 'release'),
                        array(lang('plugin/bigapp', 'menu_app_mobileset'), 'plugins&operation=config&identifier=bigapp&pmod=mobileset', $_GET['pmod'] == 'mobileset'),
                        //array(lang('plugin/bigapp', 'menu_app_pcset'), 'plugins&operation=config&identifier=bigapp&pmod=pcset', $_GET['pmod'] == 'pcset'),
                        //array(lang('plugin/bigapp', 'menu_app_pushmsg'), 'plugins&operation=config&identifier=bigapp&pmod=pushmsg', $_GET['pmod'] == 'pushmsg'),
            ))),
            array(lang('plugin/bigapp', 'menu_app_packconf'), $pack_and_config_url, $_GET['pmod'] == 'needhelp', true, true),
            array(array('menu' => lang('plugin/bigapp', 'menu_config'), 'submenu' => array(
							  array($lang['theme'], 'plugins&operation=config&identifier=bigapp&pmod=theme', $_GET['pmod'] == 'theme'),
                        //array(lang('plugin/bigapp', 'menu_variable'), 'plugins&operation=config&identifier=bigapp&pmod=variable', $_GET['pmod'] == 'variable'),
                        array(lang('plugin/bigapp', 'menu_account'), 'plugins&operation=config&identifier=bigapp&pmod=account', $_GET['pmod'] == 'account'),
                        array(lang('plugin/bigapp', 'menu_app_appcfg'), 'plugins&operation=config&identifier=bigapp&pmod=appcfg', $_GET['pmod'] == 'appcfg'),
                        array($lang['checkin'], 'plugins&operation=config&identifier=bigapp&pmod=checkin', $_GET['pmod'] == 'checkin'),
							  array($lang['push'], 'plugins&operation=config&identifier=bigapp&pmod=pushaccount', $_GET['pmod'] == 'pushaccount'),
                        ))),
				array($lang['menu_app_design'], 'plugin.php?id=bigapp:design', false, true, true),
            /*array(array('menu' => $lang['home'], 'submenu' => array(
                        //array($lang['portal_category'], 'plugins&operation=config&identifier=bigapp&pmod=myhome&tpl=portal', $_GET['pmod'] == 'myhome'),
                        array($lang['menu_banner'], 'plugins&operation=config&identifier=bigapp&pmod=myhome&tpl=banner', $_GET['pmod'] == 'myhome'),
                        array($lang['func1'], 'plugins&operation=config&identifier=bigapp&pmod=myhome&tpl=func&fun=1', $_GET['pmod'] == 'myhome'),
                        array($lang['func2'], 'plugins&operation=config&identifier=bigapp&pmod=myhome&tpl=func&fun=2', $_GET['pmod'] == 'myhome'),
                        array($lang['threadsetting'], 'plugins&operation=config&identifier=bigapp&pmod=myhome&tpl=threadsetting', $_GET['pmod'] == 'myhome'),
                        ))),*/
            /*
            array(array('menu' => $lang['push'], 'submenu' => array(
                        array($lang['jpush_account'], 'plugins&operation=config&identifier=bigapp&pmod=pushaccount', $_GET['pmod'] == 'pushaccount'),
                        ))),
            */
////////////////////////////
				array(array('menu' => lang('plugin/bigapp', 'menu_stat'), 'submenu' => array(
                        array(lang('plugin/bigapp', 'menu_stat_realtime'), 'plugins&operation=config&identifier=bigapp&pmod=realtime', $_GET['pmod'] == 'realtime'),
                        array(lang('plugin/bigapp', 'menu_stat_total'), 'plugins&operation=config&identifier=bigapp&pmod=total', $_GET['pmod'] == 'total'),
                        ))),
            array(lang('plugin/bigapp', 'menu_faq'), 'http://app.youzu.com/app/identifier?identifier=help', $_GET['pmod'] == 'needhelp', true, true),
            );
showsubmenu(lang('plugin/bigapp', 'menu_root'), $menu);
echo '</div>';
showtips($lang['browser_tip'], '', true, $lang['broser_title']);


/////////////////////////////////////////////

if(!function_exists('curl_init')){
    showmessage(lang('plugin/bigapp', 'curl_needed'), '', array(), array('alert_error')); 
}
if(!function_exists('iconv') && !function_exists('mb_convert_encoding')){
    showmessage(lang('plugin/bigapp', 'iconv_mb_needed'), '', array(), array('alert_error')); 
}
//if(!class_exists('ZipArchive', false)){
//    showmessage(lang('plugin/bigapp', 'zip_class_missing'), '', array(), array('alert_error'));
//}
$ak = $sk = $appInfo = null;
$aksk = BigappEnv::getAkSk();
if ($aksk!==false && isset($aksk['ak']) &&  isset($aksk["sk"])) {
    $ak = $aksk["ak"];
    $sk = $aksk["sk"];
    $appInfo = BigappEnv::getAppInfoFromBigstation();
    
    ///////////////////////////////////////////////////////////////////////
    // 原代码逻辑：从bksvrapi获取appinfo
    /*
    $obj = new BkSvr($ak, $sk, 30);
    $appInfo = $obj->getInfo(BigAppConf::$appInfoUrl, array('method' => 'get_basic'));
    if(!is_array($appInfo)){
        runlog('bigapp', "use remote ak sk to get app info failed, give up [ ak: $ak, sk: $sk ]");
        showmessage(lang('plugin/bigapp', 'get_ak_sk_fail'), '', array(), array('alert' => 'error'));
    }
    //runlog('bigapp', "get ak sk and app info succ [ ak: $ak, sk: $sk, appid: " . $appInfo['app_id'] . "]");
    */
    ///////////////////////////////////////////////////////////////////////

    //add for plugin upgrading
    if(isset($appInfo['remind']) && (1 == $appInfo['remind'] || 2 == $appInfo['remind'])){ 
        $msg = Utils::diconv('UTF-8', CHARSET, $appInfo['remind_message']);
        showtips($msg, '', true, lang('plugin/bigapp', 'plugin_interface_upgrade'));
        if(2 == $appInfo['remind']){
            die(0);
        }
    }
}
/////////////////////////////////////////////


function converMenuString($content){
    global $_G;
    $charset = strtoupper($_G['charset']);
    if(is_string($content) && strtolower($charset) != 'utf-8' && strtolower($charset) != 'utf8'){
        if(function_exists('iconv')){
                $content = @iconv('UTF-8', 'GBK//ignore', $content);
        }else if(function_exists('mb_convert_encoding')){
                $content = @mb_convert_encoding($content, 'GBK', 'UTF-8');
        }
    }
    return $content;
}

    
?>
