<?php

/***********************************************************
 * @file:   contentthread.php
 * @author: tangyy(tangyy@youzu.com)
 * @create: 2015-09-29 15:05:12
 * @modify: 2015-10-07 21:01:10
 ***********************************************************/
if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}

require_once './source/class/class_core.php';
require_once './source/function/function_forum.php';
$discuz = C::app();
$discuz->init();
require_once libfile('function/cache');
require_once(dirname(__FILE__)."/../../libs/env.inc.php");

BigappEnv::import_model("portal/article.php");
BigappEnv::import_model("forum/thread.php");
$_G['siteurl'] = str_replace('api/mobile/', '', $_G['siteurl']);
$_G['siteurl'] = str_replace('source/plugin/bigapp/', '', $_G['siteurl']);


$style = isset($_GET['style']) ? $_GET['style'] : '2';
$ispic_mode = ('1' == $style ? true : false); 
$page = isset($_GET['page']) ? $_GET['page'] : '1';
$setting = isset($_GET['setting']) ? $_GET['setting'] : array();
$setting = json_decode(base64_decode($setting), true);

$ret = array (
	"data" => array (
    ),
	"count" => '0',
	"page" => $page,
	"need_more" => '0',
	"pic_mode" => $style,
);

if(is_array($setting)) {
	$all_aids = $cids = $all_tids = $fids = "";
	
	//配置根据order排序
	$newArr=array();
	for($i=0; $i < count($setting); $i++){
		$newArr[] = $setting[$i]['order'];
	}
	array_multisort($newArr, $setting);
	
	//将板块id,频道id聚集,不需考虑排序因素
	foreach($setting as $contentset) {
		if('1' == $contentset['type']) {
			$all_aids = $all_aids . "," . $contentset['id'];
		}
		if('2' == $contentset['type']) {
			$all_tids = $all_tids . "," . $contentset['id'];
		}
		if('3' == $contentset['type']) {
			$cids = $cids . "," . $contentset['id'];
		}
		if('4' == $contentset['type']) {
			$fids = $fids . "," . $contentset['id'];
		}
	}
	//all_aids, all_tids为了后面过滤用（从频道或者论坛中去重）
	if("" !== $all_aids) {
		$all_aids = substr($all_aids, 1);
		$all_aids = explode(',', $all_aids);
	} else {
		$all_aids = array();
	}
	
	if("" !== $all_tids) {
		$all_tids = substr($all_tids, 1);
		$all_tids = explode(',', $all_tids);
	} else {
		$all_tids = array();
	}
	
	if("" !== $fids) {
		$fids = substr($fids, 1);
		$fids = explode(',', $fids);
	} else {
		$fids = array();
	}
	
	if("" !== $cids) {
		$cids = substr($cids, 1);
		$cids = explode(',', $cids);
	} else {
		$cids = array();
	}
	//板块和频道只能设置一个
	if(!empty($cids) && !empty($fids)) {
		//$ret['code'] = 1;
		//$ret['msg'] = 'Fail';
		$ret['data'] = array();
		bigapp_core::result(bigapp_core::variable($ret));
	}
} else {
	//$ret['code'] = 1;
	//$ret['msg'] = 'Fail';
	$ret['data'] = array();
	bigapp_core::result(bigapp_core::variable($ret));
}

//require_once 'testtids.php';

//第一页数据组成 帖子+文章+(频道or论坛第一页)
if('1' === $page) {
	foreach($setting as $contentset) {
		if('1' == $contentset['type']) {
			$aids = $contentset['id'];
			if("" !== $aids) {
				$aids = explode(',', $aids);
			} else {
				$aids = array();
			}
			//取文章数据
			$aidarr = array();
			if (is_array($aids) && !empty($aids)) {
				foreach ($aids as $aid) {
					if (is_numeric($aid)) $aidarr[] = $aid;
				}
			}
			
			if (!empty($aidarr)) {
				$articles = Bigapp_Portal_Article::getByArticleIds($aidarr);
				$ret['data'] = array_merge($ret['data'], $articles['list']);
			}
			
		}
		if('2' == $contentset['type']) {
			$tids = $contentset['id'];
			if("" !== $tids) {
				$tids = explode(',', $tids);
			} else {
				$tids = array();
			}
			//取帖子数据
			$threadInfos = getSingleTidList($tids, $ispic_mode);
			
			//取forum name
			$sql = "SELECT name FROM " .DB::table('forum_forum'). " where fid=";
			foreach($threadInfos as $key => &$thread) {
				$name = "";
				if(isset($thread['fid'])) {
					$query = DB::query($sql . $thread['fid']);
					$tmp = DB::fetch($query);
					
					if(isset($tmp['name'])) {
						$name = $tmp['name'];
					}
				}
				
				if(isset($thread['typename'])) {
					unset($thread['typename']);
				}
				
				$thread['forum_name'] = $name;
			}
			
			$ret['data'] = array_merge($ret['data'], $threadInfos);
		}
	}
}

//取频道文章或者论坛帖子
try {
	// 读取频道下的文章列表
	if (is_array($cids) && !empty($cids)) {
		
      //比对频道设置是否发生变化，清楚上一次的cache
	   $content_setting_cids = C::t('common_setting')->fetch('content_setting_cids', true);
	   
	   if(false === $content_setting_cids[0]) {
			$oldcids = array();
	   } else {
		   $oldcids = $content_setting_cids;
	   }
	   
	   if($oldcids !== $cids && !empty($oldcids)) {
		   $oldaids = array();
		   Bigapp_Portal_Article::clearCache($oldcids, $oldaids);
	   } 
	   
	   $settings = array('content_setting_cids' => $cids);
		C::t('common_setting')->update_batch($settings);
	   
	   //获取频道文章数据
		$articles = Bigapp_Portal_Article::getArticalInChannelExceptAids($cids, $all_aids, $page);
		
		if(!$ispic_mode) {
			//文章无图模式情况下，过滤掉图片
			for($i = 0; $i < count($articles['list']); $i++) {
				$articles['list'][$i]['attachment_urls'] = array();
			}
		}
		
		$ret['data'] = array_merge($ret['data'], $articles['list']);
		$ret['count'] = $articles['total'];
		
		if(intval($articles['total_page']) > intval($page)) {
			$ret['need_more'] = '1';
		}
	}

	//读取板块下的帖子列表	
	if (is_array($fids) && !empty($fids)) {
		
		//比对版块设置是否发生变化，清楚上一次的cache
	   $content_setting_fids = C::t('common_setting')->fetch('content_setting_fids', true);
	   
	   if(false === $content_setting_fids[0]) {
			$oldfids = array();
	   } else {
		   $oldfids = $content_setting_fids;
	   }
	   
	   if($oldfids !== $fids && !empty($oldfids)) {
		   clearFidsList($oldfids);
	   } 
	   
	   $settings = array('content_setting_fids' => $fids);
		C::t('common_setting')->update_batch($settings);
		
		$threadInfos = getThreadsFromForums($fids, $ispic_mode, $page, $all_tids);
		
		//取forum name
		if(isset($threadInfos['thread_list'])) {
			$sql = "SELECT name FROM " . DB::table('forum_forum'). " where fid=";
			foreach($threadInfos['thread_list'] as $key => &$thread) {
				$name = "";
				if(isset($thread['fid'])) {
					$query = DB::query($sql . $thread['fid']);
					$tmp = DB::fetch($query);
					
					if(isset($tmp['name'])) {
						$name = $tmp['name'];
					}
				}
				
				if(isset($thread['typename'])) {
					unset($thread['typename']);
				}
				
				$thread['forum_name'] = $name;
			}
		}

		$ret['data'] = array_merge($ret['data'], $threadInfos['thread_list']);
	   $ret['count'] = $threadInfos['total'];
	   
	   
	   if(intval($threadInfos['total_page']) > intval($page)) {
			$ret['need_more'] = '1';
		}
	}
    
	bigapp_core::result(bigapp_core::variable($ret));
} catch (Exception $e) {
	//echo $e->getMessage(); die(0);
	bigapp_core::result(bigapp_core::variable($ret));
}

?>
