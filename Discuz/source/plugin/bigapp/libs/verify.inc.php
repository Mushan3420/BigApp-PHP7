<?php

/***********************************************************
 * @file:   verify.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-09-17 19:06:23
 * @modify: 2015-09-17 19:06:23
 * @brief:  验证站点是否已认证，未认证的直接跳站长认证设置页面
 *          （所有需要站点认证的页面，引用此文件）
 ***********************************************************/

require_once("env.inc.php");


function gotoSiteSet()
{
//    header("Location: admin.php?action=plugins&operation=config&do=23&identifier=bigapp&pmod=certify");
    showtips(lang('plugin/bigapp', 'certify_content'), '', true, lang('plugin/bigapp', 'certify_tips'));
}

$bigappInfo = BigappEnv::getAppInfoFromBigstation();
if (empty($bigappInfo) || !isset($bigappInfo["verified"]) || $bigappInfo["verified"]!=1) {
    gotoSiteSet();
}


// vim600: sw=4 ts=4 fdm=marker syn=php
?>
