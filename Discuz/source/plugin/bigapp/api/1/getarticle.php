<?php

/***********************************************************
 * @file:   getarticle.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-09-29 12:01:34
 * @modify: 2015-09-29 12:01:34
 * @brief:  getarticle.php
 ***********************************************************/
if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}

require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();
require_once(dirname(__FILE__)."/../../libs/env.inc.php");
BigappEnv::import_model("portal/article.php");


$ret = array (
	"member" => $_G["member"],
	"data" => array (
        "list" => array(),
        "count" => 0,
        "incache" => 0,
    )
);

try {
    // parse & check request
	$aids = isset($_GET["aids"]) ? $_GET["aids"] : "";
    $cids = isset($_GET["cids"]) ? $_GET["cids"] : "";
    $page = isset($_GET["page"]) ? $_GET["page"] : 1;
    $clearcache = isset($_GET["clearcache"]) ? $_GET["clearcache"] : 0;
    $groupid = $_G["member"]["groupid"];
    
	$arr = explode(",", $aids);
	$aidarr = array();
	foreach ($arr as $aid) {
		if (is_numeric($aid)) $aidarr[] = $aid;
	}

    // 读取频道下的文章列表
	if ($cids != "") {
		$arr = explode(",", $cids);
		$cidarr = array();
		foreach ($arr as $cid) {
			if (is_numeric($cid)) $cidarr[] = $cid;
		}

        if ($clearcache) {
            Bigapp_Portal_Article::clearCache($cidarr, $aidarr);
            $ret["data"] = "clear cache";
			bigapp_core::result(bigapp_core::variable($ret));
        }

		$ret["data"] = Bigapp_Portal_Article::getArticalInChannelExceptAids($cidarr, $aidarr, $page);
    }

    // 读取指定的文章列表
    else if (!empty($aidarr)) {
        $ret["data"] = Bigapp_Portal_Article::getByArticleIds($aidarr);

    }
    
	bigapp_core::result(bigapp_core::variable($ret));
} catch (Exception $e) {
	//echo $e->getMessage(); die(0);
	bigapp_core::result(bigapp_core::variable($ret));
}

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
