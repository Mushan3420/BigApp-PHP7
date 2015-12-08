<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once dirname(__FILE__) . '/libs/env.inc.php';
require_once dirname(__FILE__) . '/libs/menu.inc.php';

// save push setting
if(isset($_REQUEST["pushenable"])) {
    $pushenable = $_REQUEST["pushenable"]==1 ? 1 : 0;
	$setting = array('push_enabled' => $pushenable);
	$settings = array('bigapp_push_config' => $setting);
	C::t('common_setting')->update_batch($settings);
	cpmsg('plugins_edit_succeed', 'action=plugins&operation=config&do=' . $pluginid . '&identifier=bigapp&pmod=pushaccount', 'succeed');
}


updatecache('setting');
if(isset($_G['setting']['bigapp_push_config'])){
        $_G['setting']['bigapp_push_config'] = unserialize($_G['setting']['bigapp_push_config']);
}
if(!isset($_G['setting']['bigapp_push_config']['push_enabled'])){
    $_G['setting']['bigapp_push_config']['push_enabled'] = 1;
}
if($jpushInfo['jpush_is_free'] != 0){
	$jpushInfo['jpush_is_free'] = 1;
}
if($_G['setting']['bigapp_push_config']['push_enabled'] != 0){
	$_G['setting']['bigapp_push_config']['push_enabled'] = 1;
}
$alias = sprintf('%020lu%020lu', $appInfo['app_id'], $_G['uid']);
$notifyTitle = '您收到一条测试通知';
$notifyContent = '恭喜您，您顺利收到来自于站点的推送测试信息，这意味着您的站点推送配置是正常的。';
$msgType = 0; //0: 测试消息；1: 有消息通知；2: 有回帖通知；3: 有好友请求通知
$mask = 3; //1: 消息，2: 通知, 3: 消息 + 通知
runlog('bigapp', "ready to push test message, params are [ alias: $alias, title: $notifyTitle, content: $notifyContent, msg type: $msgType, mask: $mask ].");
$params = http_build_query(array('alias' => $alias, 'title' => $notifyTitle, 'content' => $notifyContent, 'message_type' => $msgType, 'mask' => $mask, 'istest' => 1));
$url = 'plugin.php?id=bigapp:pushtest&' . $params;


//////////////////////////////////////////////////////
// add by mawentao

$pushEnabled = array('pushenable',
	array(
		array(0, lang('plugin/bigapp', 'no')),
		array(1, lang('plugin/bigapp', 'yes')),
	)
);
showformheader('plugins&operation=config&identifier=bigapp&pmod=pushaccount', 'enctype', 'newtask');
echo "<table class='tb tb2'><tbody><tr><th class='partition' colspan='3'>".lang('plugin/bigapp', 'push_config')."</th></tr>";
showsetting(lang('plugin/bigapp', 'push_enabled'), $pushEnabled, $_G['setting']['bigapp_push_config']['push_enabled'], 'mradio', 0, 0, lang('plugin/bigapp', 'push_enabled_comment'));
showsubmit('pushset', lang('plugin/bigapp', 'submit'), '', '', '', false);
echo "</table>";
showformfooter();

//////////////////////////////////////////////////////

$tableHeader = '<br/><table><tbody><tr><th class="partition">' . lang('plugin/bigapp', 'push_debug') . '</th></tr>';
$btn = '<tr><td><div class="fixsel"><br/><input type="submit" class="btn" id="pushtest_sumbit" name="pushtest_sumbit" value="' . lang('plugin/bigapp', 'send_test_message') . '" onclick="GetDatas(\'' . $url . '\')"></div></td><td><br /><label id="send_label"></label></td></tr>';
$tableFooter = '</tbody></table>';
$jqueryInclude = '<script type="text/javascript" src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/jquery.js' . '"></script>' . '<script type="text/javascript">var jq = jQuery.noConflict();</script>';
echo $tableHeader . $btn . $tableFooter .  $jqueryInclude;
echo '<script type="text/javascript" charset="utf-8" src="' . rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/js/sendmessage.js' . '"></script>';

/////////////////////////////////////////////////////////////////////////////////////

$params = array(
    "groupid" => isset($_G['groupid']) ? intval($_G['groupid']) : 7,
    "appid" => $appid,
    "api" => BigappEnv::getSiteUrl()."/plugin.php?id=bigapp:pushmsg&ajax=1",
);
$tplVars = array(
    "plugin_path"=>BigappEnv::getPluginPath(),
);
Utils::loadTemplate(dirname(__FILE__).'/view/pushmsg.tpl', $params, $tplVars);

/////////////////////////////////////////////////////////////////////////////////////

runlog('bigapp', 'show push_account page succ');
?>
