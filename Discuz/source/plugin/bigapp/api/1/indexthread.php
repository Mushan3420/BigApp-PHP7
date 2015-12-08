<?php

/**
* @file indexthread.php
* @Brief 
* @author tangyy
* @version 1
* @date 2015-08-18
*/
//帖子thread >> (热门主题, 新增主题, 精华主题)

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'guide';
$view = isset($_GET['view']) ? $_GET['view'] : 'hot';

$viewType = array('new', 'hot', 'digest', 'newthread');

$_GET['view'] = !in_array($view, $viewType) ? 'hot' : $view;

include_once 'forum.php';

class BigAppAPI {
	
	static protected $collect = array(); 

	//note 程序模块执行前需要运行的代码
	function common() {
		global $_G;
			
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		//open viewperm
		loadcache('forums');
		foreach ($_G['cache']['forums'] as $fid => &$info){
			if($info['type'] === 'group' || '' === $info['viewperm']){
				continue;
			}
			$viewPerm = explode("\t", $info['viewperm']);
			if(in_array($_G['groupid'], $viewPerm)){
				$info['viewperm'] = '';
			}
		}
		if(isset($_G['setting']['bigapp_settings'])){
            $tmp = unserialize($_G['setting']['bigapp_settings']);
        }
        if(!isset($tmp['enable_pic_opt'])){
            $tmp['enable_pic_opt'] = 1;   
        }
        BigAppConf::$enablePicOpt = (!!$tmp['enable_pic_opt']);
	}

	//note 程序模板输出前运行的代码
	function output() {
		global $_G;
	
		$type = $_GET['view'];
		
		foreach($GLOBALS['data'][$type]['threadlist'] as $tid=>&$thread) {
			if(!isset($_G['cache']['stamps'][$thread['icon']]['url'])){
				$thread['icon'] = -1;			
			}
			$thread['forum_name'] = $_G['cache']['forums'][$thread['fid']]['name'];
		}
		
		unset($thread);
		
		$GLOBALS['data'][$type]['threadlist'] = bigapp_core::getvalues($GLOBALS['data'][$type]['threadlist'], 
				array('/^\d+$/'), array('tid', 'fid', 'attachment', 'avatar', 'subject', 'author', 'views', 'replies', 'forum_name', 'authorid', 'dateline'));
		
		if(is_null($GLOBALS['data'][$type]['threadlist']) || !is_array($GLOBALS['data'][$type]['threadlist'])){
			$GLOBALS['data'][$type]['threadlist'] = array();
		}
		
		foreach($GLOBALS['data'][$type]['threadlist'] as $tid=>$thread) {
            $GLOBALS['data'][$type]['threadlist'][$tid]['avatar'] = avatar($thread['authorid'], 'big', true);
			$GLOBALS['data'][$type]['threadlist'][$tid]['dateline'] = str_replace('&nbsp;', '', $GLOBALS['data'][$type]['threadlist'][$tid]['dateline']);
			$GLOBALS['data'][$type]['threadlist'][$tid]['dateline'] = preg_replace('/<.*?\>/', '', $GLOBALS['data'][$type]['threadlist'][$tid]['dateline']);
       }
	   
	   //filter &quot
	   foreach($GLOBALS['data'][$type]['threadlist'] as &$thread) {
		   $thread['subject'] = str_replace('&quot;', '"', $thread['subject']);
	   }
		
		BigAppAPI::_getDetails($GLOBALS['data'][$type]['threadlist']);
		
		$variable = array(
			'data' => array_values($GLOBALS['data'][$type]['threadlist']),
			'perpage' => $GLOBALS['perpage'],
		);
		if(isset($_G['setting']['bigapp_settings']['threadlist_image_mode']) && !$_G['setting']['bigapp_settings']['threadlist_image_mode']){
			$variable['open_image_mode'] = 0;
		}
		
		//$tmp = array();
		//foreach ($variable['data'] as $_v){
			//if(isset($_v['fid']) && in_array($_v['fid'], array(4, 217))){ //韬客论坛
			//if(isset($_v['fid']) && !in_array($_v['fid'], array(28, 81, 18, 19, 33, 17))){ //中羽论坛
				//continue;
			//}
			//$tmp[] = $_v;
		//}
		//$variable['data'] = $tmp;

		bigapp_core::result(mobile_core::variable($variable));
	}
	
	protected function _getPictures(&$threadInfo)
	{
		global $_G;
		$collect = &BigAppAPI::$collect;
		foreach ($threadInfo as $tid => &$info){
			$collect = array();
			$message = str_replace("\r", '', $info['message']);
			$message = str_replace("\n", '', $message);
			$message = str_replace('\r', '', $message);
			$message = str_replace('\n', '', $message);
			if(function_exists('iconv')){
				$message = iconv(CHARSET, 'UTF-8//ignore', $message);
			}else{
				$message = mb_convert_encoding($message, 'UTF-8', CHARSET);
			}
			$message = preg_replace_callback('/\[img.*?\](.*?)\[\/img\]|\[attach\]([0-9]+)\[\/attach\]|\[i.*\](.*)' . 
					'\[\/i\]|\[.*?\]|\\n|\\r/', 'BigAppAPI::imgCallback', $message);
			if(function_exists('mb_substr')){
				$message = mb_substr($message, 0, 1000, 'UTF-8');
			}else{
				$message = substr($message, 0, 2000);
			}
			/*$find = array(':)', ':(', ':D', ':\'(', ':@', ':o', ':P', ':$', ';P', ':L', ':Q', ':lol', ':loveliness:', 
					':funk:', ':curse:', ':dizzy:', ':shutup:', ':sleepy:', ':hug:', ':victory:', ':time:', ':kiss:', ':handshake', ':call:');
			$replace = array("\xF0\x9F\x98\x8C", "\xF0\x9F\x98\x94",  "\xF0\x9F\x98\x83", "\xF0\x9F\x98\xAD", "\xF0\x9F\x98\xA0", 
					"\xF0\x9F\x98\xB2", "\xF0\x9F\x98\x9C", "\xF0\x9F\x98\x86", "\xF0\x9F\x98\x9D",  "\xF0\x9F\x98\x93",  "\xF0\x9F\x98\xAB", 
					"\xF0\x9F\x98\x81", "\xF0\x9F\x98\x8A", "\xF0\x9F\x98\xB1", "\xF0\x9F\x98\xA4", "\xF0\x9F\x98\x96", "\xF0\x9F\x98\xB7", 
					"\xF0\x9F\x98\xAA", "\xF0\x9F\x98\x9A", "\xE2\x9C\x8C", "\xE2\x8F\xB0", "\xF0\x9F\x92\x8B", "\xF0\x9F\x91\x8C", "\xF0\x9F\x93\x9E");*/
			//$message = str_replace($find, $replace, $message);
			
			loadcache(array('smilies', 'smileytypes'));
	
			foreach($_G['cache']['smilies']['replacearray'] as $id => $img) {
			    $pattern = $_G['cache']['smilies']['searcharray'][$id];
			    $message = preg_replace($pattern, '[表情]', $message);
			}
			
			if(function_exists('mb_strlen')){
				if(mb_strlen($message, 'UTF-8') > 30){
					$message = mb_substr($message, 0, 30, 'UTF-8') . '...';
				}
			}else{
				if(strlen($message) > 60){
					$message = substr($message, 0, 30) . '...';
				}
			}
			$info['message'] = '__DONT_DICONV_TO_UTF8___' . $message;
			
			$attachments = array();
			$infoAttrs = $info['attachments'];
			foreach ($collect as $attach){
				if(is_numeric($attach)){
					if(isset($infoAttrs[$attach])){
						$url = ($infoAttrs[$attach]['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
						$url .= $infoAttrs[$attach]['attachment'];
						$tmp = parse_url($url);
						if(!isset($tmp['scheme'])){
							$url = ApiUtils::getDzRoot() . $url;
						}
						$info['attachment_urls'][] = $url;
						unset($infoAttrs[$attach]);
					}
					continue;
				}
				$tmp = parse_url($attach);
				if(!isset($tmp['scheme'])){
					$url = ApiUtils::getDzRoot() . $attach;
				}else{
					$url = str_replace('source/plugin/mobile/', '', $attach);
					$url = str_replace('source/plugin/mobile/', '', $url);
				}
				$info['attachment_urls'][] = $url;
			}
			global $_G;
			//feed others
			foreach ($infoAttrs as $aid => $aInfo){
				$url = ($aInfo['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
				$url .= $aInfo['attachment'];
				$tmp = parse_url($url);
				if(!isset($tmp['scheme'])){
					$url = ApiUtils::getDzRoot() . $url;
				}
				$info['attachment_urls'][] = $url;
			}
		}
		unset($info);
	}

	protected static function imgCallback($matches)
	{
		$collect = &BigAppAPI::$collect;
		if(isset($matches[1]) && !empty($matches[1])){
			$collect[] = $matches[1];
		}else if(isset($matches[2]) && !empty($matches[2])){
			$collect[] = $matches[2];
		}
		return '';
	}/*}}}*/

	protected function _getDetails(&$list)
	{
		//check whether thread list image mode is open
		global $_G;
		if(isset($_G['setting']['bigapp_settings']) && is_string($_G['setting']['bigapp_settings'])){
    		$_G['setting']['bigapp_settings'] = unserialize($_G['setting']['bigapp_settings']);
		}
		
		if(isset($_G['setting']['bigapp_settings']['threadlist_image_mode']) && !$_G['setting']['bigapp_settings']['threadlist_image_mode']){
			return ;
		}
		$tids = array();
		foreach ($list as $l){
			$tids[] = $l['tid'];
		}
		if(empty($tids)){
			return ;
		}
		$sql = 'SELECT pid, tid, first FROM ' . DB::table('forum_post') . ' WHERE tid IN (' . implode(', ', $tids) . ')';
		$query = DB::query($sql);
		$threadInfo = array();
		$pids = array();
		while($tmp = DB::fetch($query)){
			if(!!$tmp['first']){
				if(isset($pids[$threadInfo[$tmp['tid']]['pid']])){
					unset($pids[$threadInfo[$tmp['tid']]['pid']]);
				}
				$threadInfo[$tmp['tid']] = array('pid' => $tmp['pid']);
				$pids[$tmp['pid']] = $tmp['pid'];
			}
		}
		if(empty($pids)){
			return ;
		}
		$pids = array_values($pids);
		$sql = 'SELECT pid, tid, message FROM ' . DB::table('forum_post') . ' WHERE pid IN (' . implode(', ', $pids) . ')';
		$query = DB::query($sql);
		while($tmp = DB::fetch($query)){
			$threadInfo[$tmp['tid']]['message'] = $tmp['message'];
		}
		$sql = 'SELECT aid, tid, tableid, pid FROM ' . DB::table('forum_attachment') . ' WHERE pid IN (' . implode(', ', $pids) . ')';
		$tbIdx = array();
		$query = DB::query($sql); 
		while($tmp = DB::fetch($query)){
			if($tmp['tableid'] < 10){
				$threadInfo[$tmp['tid']]['aid'][] = $tmp['aid'];
				$tbIdx[$tmp['tableid']][] = $tmp['aid'];
			}
		}
		foreach ($tbIdx as $tableId => $aids){
			$sql = 'SELECT aid, tid, attachment, description, remote, isimage FROM ' . DB::table('forum_attachment_' . 
					$tableId) . ' WHERE aid IN (' . implode(', ', $aids) . ')';
			$query = DB::query($sql);
			while($tmp = DB::fetch($query)){
				$isImage = $tmp['isimage'];
				if($tmp['isimage'] && !$_G['setting']['attachimgpost']){
					$isImage = 0;
				}
				if($isImage){
					$threadInfo[$tmp['tid']]['attachments'][$tmp['aid']] = array('attachment' => $tmp['attachment'], 
							'description' => $tmp['description'], 'remote' => $tmp['remote'], 'isimage' => $isImage);
				}
			}
		}
		
		BigAppAPI::_getPictures($threadInfo);
		foreach ($list as &$l){
			$l['attachment_urls'] = array();
			$l['message_abstract'] = '';
			if(isset($threadInfo[$l['tid']]['message'])){
				$l['message_abstract'] = $threadInfo[$l['tid']]['message'];
			}
			if(isset($threadInfo[$l['tid']]['attachment_urls'])){
				$l['attachment_urls'] = $threadInfo[$l['tid']]['attachment_urls'];
			}
			if(true === BigAppConf::$enablePicOpt){
				foreach ($l['attachment_urls'] as &$_url){
					if(ApiUtils::isOptFix($_url)){
						$_url = rtrim($_G['siteurl'], '/') . '/plugin.php?id=bigapp:optpic&mod=__x__&size=' . urlencode(BigAppConf::$thumbSize) . '&url=' . urlencode($_url);
						$_url = str_replace('source/plugin/mobile/', '', $_url);
						$_url = str_replace('source/plugin/bigapp/', '', $_url);
					}
				}
				unset($_url);
			}
		}
		unset($l);
	}

}

?>
