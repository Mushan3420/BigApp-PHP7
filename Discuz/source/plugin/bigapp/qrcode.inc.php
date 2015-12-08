<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}


if (isset($_GET['xml'])) {
	$xml = <<<EOF1
<?xml version="1.0" encoding="utf-8"?>
<root><![CDATA[<iframe src='plugin.php?id=bigapp:qrcode' style='border:none;width:130px;height:145px;'></iframe>]]></root>
EOF1;
    echo $xml;
    die(0);
}

define("FILE_PATH", dirname(__FILE__));
require_once( FILE_PATH."/libs/utils.inc.php" );

$svalue  = C::t('common_setting')->fetch("bigapp_mobile_setting",false);
$params  = json_decode($svalue, true);
$tplVars = array("plugin_path" => rtrim($_G['siteurl'], '/').'/source/plugin/bigapp');
Utils::loadTemplate(FILE_PATH.'/view/qrcode.tpl', $params, $tplVars);

runlog('bigapp', 'show qrcode page succ');
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
