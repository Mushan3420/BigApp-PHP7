<?php
/**
* @file check.php
* @Brief for api check
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(false === strpos($_SERVER['REQUEST_URI'], 'iyz_index.php')){
	return ;
}
require '../../../source/class/class_core.php';
$discuz = C::app();
$discuz->cachelist = array();
$discuz->init();

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
@include 'bigapp.class.php';
require_once dirname(__FILE__) . '/libs/utils.inc.php';
define("PLUGIN_INNER_VERSION", "9143");
$mobilecheck = Utils::getCheckJson();
if(isset($mobilecheck['plugin_info']['bigapp']['version'])) {
    $mobilecheck['plugin_info']['bigapp']['version'] .= ".".PLUGIN_INNER_VERSION;
}
if(isset($_GET['json']) && $_GET['json']){
	echo BIGAPPJSON::encode($mobilecheck);
	die(0);
}
header("Content-type:text/html;charset=utf-8");
$output = <<<EOF
<html>
<style type="text/css">
table.gridtable {
	font-family: verdana,arial,sans-serif;
	font-size:13px;
	color:#333333;
	border-width: 1px;
	border-color: #666666;
	border-collapse: collapse;
}
table.gridtable th {
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #666666;
	background-color: #dedede;
}
table.gridtable td {
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #666666;
	background-color: #ffffff;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>BigApp API可用性检测</title>
<table class="gridtable" align="center">
<caption>BigApp API可用性检测</caption>
<tr>
	<th>项目</th><th>值</th><th>要求</th><th>检查结论</th>
</tr>
<tr>
	<td>站点名称</td><td>%s</td><td>NA</td><td>%s</td>
</tr>
<tr>
	<td>用户中心地址</td><td>%s</td><td>NA</td><td>%s</td>
</tr>
<tr>
	<td>BigApp内部版本</td><td>%s</td><td>NA</td><td>%s</td>
</tr>
<tr>
	<td>discuz版本</td><td>%s</td><td>≥X2.5</td><td>%s</td>
</tr>
<tr>
	<td>服务器编码</td><td>%s</td><td>utf-8或gbk</td><td>%s</td>
</tr>
<tr>
	<td><a href="http://addon.discuz.com/?@mobile.plugin" target="_blank">掌上论坛</a>插件</td><td>%s</td><td>已启用,版本≥1.4.7</td><td>%s</td>
</tr>
<tr>
	<td><a href="http://addon.discuz.com/?@bigapp.plugin" target="_blank">BigApp</a>插件</td><td>%s</td><td>已启用</td><td>%s</td>
</tr>
<tr>
	<td>手机版访问控制</td><td>%s</td><td>启用</td><td>%s</td>
</tr>
<tr>
	<td>论坛访问控制</td><td>%s</td><td>启用</td><td>%s</td>
</tr>
</table>
</html>
EOF;
$OK = '<font style="color:green;font-weight:bold">OK</font>';
$FAIL = '<font style="color:red;font-weight:bold">FAIL</font>';

$dzVer = isset($mobilecheck['discuzversion']) ? $mobilecheck['discuzversion'] : 'NA';
if(!preg_match('/^x([0-9\.]+)$/i', $dzVer, $matches)){
	$dzVer = 'NA';
	$num = 0;
}else{
	$num = $matches[1];
}
$dzStat = $FAIL;
if('NA' !== $dzVer && $num >= 2.5){
	$dzStat = $OK;
}

$charset = 'NA';
if(isset($mobilecheck['charset'])){
	$charset = $mobilecheck['charset'];
}
$charsetStat = $FAIL;
if('gbk' === strtolower($charset) || 'utf8' === strtolower($charset) || 'utf-8' === strtolower($charset)){
	$charsetStat = $OK;
}
$siteNameStat = $OK;
$siteName = 'NA';
if(isset($mobilecheck['sitename'])){
	$siteName = $mobilecheck['sitename'];
	if('NA' !== $charset){
		if(function_exists('iconv')){
			$siteName = iconv($charset, 'UTF-8//ignore', $siteName);
		}else{
			$siteName = mb_convert_encoding($siteName, 'UTF-8', $charset);
		}
	}
}
$ucUrlStat = $OK;
$ucUrl = 'NA';
if(isset($mobilecheck['ucenterurl'])){
	$ucUrl = $mobilecheck['ucenterurl'];
}

$mobileStat = $FAIL;
$mobile = 'NA';
if(isset($mobilecheck['plugin_info']['mobile'])){
	if($mobilecheck['plugin_info']['mobile']['enabled']){
		$mobileStat = $OK;
		$mobile = '已启用，版本: ';
	}else{
		$mobile = '未启用，版本: ';
	}
	$mobile .= $mobilecheck['plugin_info']['mobile']['version'];
	$mversion = explode('.', $mobilecheck['plugin_info']['mobile']['version']);
	$target = array(1, 4, 7);
	$i = 0;
	$cnt = max(3, count($mversion));
	for ($i = 0; $i < $cnt; $i++){
		$mv = (isset($mversion[$i]) ? $mversion[$i] : 0);
		$tv = (isset($target[$i]) ? $target[$i] : 0);
		if($mv > $tv){
			break;
		}
		if($mv < $tv){
			$mobileStat = $FAIL;
			break;
		}
	}
}

$bigAppStat = $FAIL;
$bigApp = 'NA';
if(isset($mobilecheck['plugin_info']['bigapp'])){
	if($mobilecheck['plugin_info']['bigapp']['enabled']){
		$bigAppStat = $OK;
		$bigApp = '已启用，版本: ';
	}else{
		$bigApp = '未启用，版本: ';
	}
	$bigApp .= $mobilecheck['plugin_info']['bigapp']['version'];
}

$mEnableStat = $FAIL;
$mEnable = '关闭';
if($mobilecheck['mobile_enabled']){
	$mEnableStat = $OK;
	$mEnable = '启用';
}

$bbEnableStat = $FAIL;
$bbEnable = '关闭';
if(!$mobilecheck['bbclosed']){
	$bbEnableStat = $OK;
	$bbEnable = '启用';
}

echo sprintf($output, $siteName, $siteNameStat, $ucUrl, $ucUrlStat, $mobilecheck['inner_edition'], $OK, $dzVer, $dzStat, $charset, $charsetStat, $mobile, $mobileStat, $bigApp, $bigAppStat, $mEnable, $mEnableStat, $bbEnable, $bbEnableStat);

//$mobilecheck = BIGAPPJSON::encode($mobilecheck);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//echo $mobilecheck;

?>
