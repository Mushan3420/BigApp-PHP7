<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once dirname(__FILE__).'/libs/env.inc.php';

function is_url($str) {
	return preg_match("/^https?:\/\/(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z_!~*'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,4})?((\/?)|(\/[^\s]+)+\/?)$/i", $str);
}

if (isset($_REQUEST["inajax"])) {
    $params = array (
        "moburl_switch" => $_REQUEST["moburl_switch"],
        "moburl" => isset($_REQUEST["moburl"]) ? $_REQUEST["moburl"] : "",
    );

    if ($params["moburl_switch"]==2 && !is_url($params["moburl"])) {
		$ret = array (
			"error_code" => 1,
            "error_msg"  => lang('plugin/bigapp', 'error_mobile_url'),
		);
		echo BIGAPPJSON::encode($ret);
        die(0);
    }

	C::t('common_setting')->update_batch(array("bigapp_pcset"=>$params));
	$ret = array (
		"error_code" => 0,
	);
	echo BIGAPPJSON::encode($ret);
	die(0);
}


// save pcset setting
if (isset($_REQUEST["moburl_switch"])) {
    $params = array (
        "moburl_switch" => $_REQUEST["moburl_switch"],
        "moburl" => isset($_REQUEST["moburl_txt"]) ? $_REQUEST["moburl_txt"] : "",
    );
    $landurl = 'action=plugins&operation=config&do='.$pluginid.'&identifier=bigapp&pmod=pcset';
    if ($params["moburl_switch"]==2 && !is_url($params["moburl"])) {
		cpmsg_error(lang('plugin/bigapp', 'error_mobile_url'), $landurl); 
    } else {
		C::t('common_setting')->update_batch(array("bigapp_pcset"=>$params));
		cpmsg('plugins_edit_succeed', $landurl, 'succeed');
    }
}

require_once dirname(__FILE__).'/libs/menu.inc.php';

updatecache('setting');
if(isset($_G['setting']['bigapp_pcset'])){
	$_G['setting']['bigapp_pcset'] = unserialize($_G['setting']['bigapp_pcset']);
}
$params = array(
    "moburl_switch" => 0,
    "moburl" => "",
);
if (isset($_G['setting']['bigapp_pcset']['moburl_switch'])) {
    $params["moburl_switch"] = $_G['setting']['bigapp_pcset']['moburl_switch'];
}
if (isset($_G['setting']['bigapp_pcset']['moburl'])) {
    $params["moburl"] = $_G['setting']['bigapp_pcset']['moburl'];
}

$tplVars = array(
    "plugin_path"=>BigappEnv::getPluginPath(),
);
Utils::loadTemplate(dirname(__FILE__).'/view/pcset.tpl', $params, $tplVars);
runlog('bigapp', 'show pcset page succ');
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
