<?php 
/**
* @file indexthread.php
* @Brief 
* @author tangyy
* @version 2
* @date 2015-09-28
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
	
	function common() {
	}
	
	function output() {
		
		global $_G;
		$perpage = 20;
		$page = isset($_G['page']) ? $_G['page'] : 1;
		$start = $perpage * ($page - 1);
		
		$view = $_GET['view'];
		$data[$view] = BigAppAPI::get_guide_list($view, $start, $perpage);

		$threadsInfo = isset($data[$view]['threadlist']) ? $data[$view]['threadlist'] : array();

		//数据映射
		foreach($threadsInfo as $tid=>&$thread) {
			if(!isset($_G['cache']['stamps'][$thread['icon']]['url'])){
				$thread['icon'] = -1;			
			}
			$thread['forum_name'] = $_G['cache']['forums'][$thread['fid']]['name'];
		}

		unset($thread);

		$threadsInfo = bigapp_core::getvalues($threadsInfo, 
			array('/^\d+$/'), array('tid', 'fid', 'posttableid', 'attachment', 'avatar', 'subject', 'author', 'views', 'replies', 'forum_name', 'authorid', 'icon', 'digest', 'dateline'));

		if(is_null($threadsInfo) || !is_array($threadsInfo)){
			$threadsInfo = array();
		}

		foreach($threadsInfo as $tid=>$thread) {
			$threadsInfo[$tid]['avatar'] = avatar($thread['authorid'], 'big', true);
			$threadsInfo[$tid]['dateline'] = str_replace('&nbsp;', '', $threadsInfo[$tid]['dateline']);
			$threadsInfo[$tid]['dateline'] = preg_replace('/<.*?\>/', '', $threadsInfo[$tid]['dateline']);
		}
		
		//filter &quot
	   foreach($threadsInfo as &$thread) {
		   $thread['subject'] = str_replace('&quot;', '"', $thread['subject']);
	   }

		BigAppAPI::_getDetails($threadsInfo);

		$thread_count = isset($data[$view]['threadcount']) ? $data[$view]['threadcount'] : 0;
		$need_more = ($perpage * $page < $thread_count) ? '1' : '0';

		$pic_mode = isset($_GET['style']) ? $_GET['style'] : '2';
		
		foreach($threadsInfo as $tid=> &$thread) {
			if('2' == $pic_mode) { //无图模式
				$thread['attachment_urls'] = array();
				$thread['message_abstract'] = "";
			}
		}
		
		$variable = array (
				"data" => array_values($threadsInfo),
				"count" => $thread_count,
				"page" => $page,
				"need_more" => $need_more,
				"pic_mode" => $pic_mode,		
		);

		bigapp_core::result(bigapp_core::variable($variable));	
	}

	//从导读中获取帖子数据
	protected function get_guide_list($view, $start = 0, $num = 20, $again = 0) {
		global $_G;
		
		$setting_guide = unserialize($_G['setting']['guide']);
		if(!in_array($view, array('hot', 'digest', 'new', 'newthread'))) {
			return array();
		}
		
		loadcache('forums');
		loadcache('forum_guide');
		$cachetimelimit = 900; //cache 15分钟
		$cache = $_G['cache']['forum_guide'][$view];
		
		$tids = array();
		
		$white_list = isset($_GET['displayid']) ? $_GET['displayid'] : "";
		$black_list = isset($_GET['forbiddenid']) ? $_GET['forbiddenid'] : "";
		
		$white_arr = explode(',', $white_list);
	   $black_arr = explode(',', $black_list);
		
		foreach($_G['cache']['forums'] as $fid => $forum) {
			if($forum['type'] != 'group' && $forum['status'] > 0 /*&& !$forum['viewperm']*/ && !$forum['havepassword']) {
				if((in_array($fid, $white_arr) || empty($white_arr[0])) && !in_array($fid, $black_arr)) {
					$fids[] = $fid;
				}
			}
		}
		
		if(empty($fids)) {
			return array();
		}
		
		$maxnum = 50000;

		$maxtid = C::t('forum_thread')->fetch_max_tid();
		$limittid = max(0,($maxtid - $maxnum));
		if($again) {
			$limittid = max(0,($limittid - $maxnum));
		}
		
		if(false/*isset($cache['cachetime']) && $cache['cachetime'] + $cachetimelimit > time()*/) {
				$updatecache = false; 
				$query = $cache['data'];
		} else {
			$dateline = 0;

			$updatecache = true;   //need update
			$query = C::t('forum_thread')->fetch_all_for_guide($view, $limittid, $tids, $_G['setting']['heatthread']['guidelimit'], $dateline);
		}
		
		$n = 0;
		foreach($query as $thread) {
			if(empty($tids) && ($thread['isgroup'] || !in_array($thread['fid'], $fids))) {
				continue;
			}
			if($thread['displayorder'] < 0) {
				continue;
			}
			$thread = BigAppAPI::guide_procthread($thread);
			$threadids[] = $thread['tid'];
			if($tids || ($n >= $start && $n < ($start + $num))) {
				$list[$thread[tid]] = $thread;
				$fids[$thread[fid]] = $thread['fid'];
			}
			$n ++;
		}
		
		if($limittid > $maxnum && !$again && count($list) < 50) {
			return BigAppAPI::get_guide_list($view, $start, $num, 1);
		}
		
		$forumnames = array();
		if($fids) {
			$forumnames = C::t('forum_forum')->fetch_all_name_by_fid($fids);
		}
		$threadlist = array();
		if($tids) {
			$threadids = array();
			foreach($tids as $key => $tid) {
				if($list[$tid]) {
					$threadlist[$key] = $list[$tid];
					$threadids[] = $tid;
				}
			}
		} else {
			$threadlist = $list;
		}
		
		unset($list);
		
		$threadcount = count($threadids);
		/*if($updatecache) {
			//cache 处理之前帖子数据
			$data = array('cachetime' => TIMESTAMP, 'data' => $query);
			$_G['cache']['forum_guide'][$view] = $data;
			savecache('forum_guide', $_G['cache']['forum_guide']);
		}*/
		
		return array('forumnames' => $forumnames, 'threadcount' => $threadcount, 'threadlist' => $threadlist);
	}

	//获取帖子信息
	protected function guide_procthread($thread) {
		global $_G;
		$todaytime = strtotime(dgmdate(TIMESTAMP, 'Ymd'));
		$thread['lastposterenc'] = rawurlencode($thread['lastposter']);
		$thread['multipage'] = '';
		$topicposts = $thread['special'] ? $thread['replies'] : $thread['replies'] + 1;
		if($topicposts > $_G['ppp']) {
			$pagelinks = '';
			$thread['pages'] = ceil($topicposts / $_G['ppp']);
			for($i = 2; $i <= 6 && $i <= $thread['pages']; $i++) {
				$pagelinks .= "<a href=\"forum.php?mod=viewthread&tid=$thread[tid]&amp;extra=$extra&amp;page=$i\">$i</a>";
			}
			if($thread['pages'] > 6) {
				$pagelinks .= "..<a href=\"forum.php?mod=viewthread&tid=$thread[tid]&amp;extra=$extra&amp;page=$thread[pages]\">$thread[pages]</a>";
			}
			$thread['multipage'] = '&nbsp;...'.$pagelinks;
		}

		if($thread['highlight']) {
			$string = sprintf('%02d', $thread['highlight']);
			$stylestr = sprintf('%03b', $string[0]);

			$thread['highlight'] = ' style="';
			$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
			$thread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
			$thread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
			$thread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
			$thread['highlight'] .= '"';
		} else {
			$thread['highlight'] = '';
		}

		$thread['recommendicon'] = '';
		if(!empty($_G['setting']['recommendthread']['status']) && $thread['recommends']) {
			foreach($_G['setting']['recommendthread']['iconlevels'] as $k => $i) {
				if($thread['recommends'] > $i) {
					$thread['recommendicon'] = $k+1;
					break;
				}
			}
		}

		$thread['moved'] = $thread['heatlevel'] = $thread['new'] = 0;
		$thread['icontid'] = $thread['forumstick'] || !$thread['moved'] && $thread['isgroup'] != 1 ? $thread['tid'] : $thread['closed'];
		$thread['folder'] = 'common';
		$thread['weeknew'] = TIMESTAMP - 604800 <= $thread['dbdateline'];
		if($thread['replies'] > $thread['views']) {
			$thread['views'] = $thread['replies'];
		}
		if($_G['setting']['heatthread']['iconlevels']) {
			foreach($_G['setting']['heatthread']['iconlevels'] as $k => $i) {
				if($thread['heats'] > $i) {
					$thread['heatlevel'] = $k + 1;
					break;
				}
			}
		}
		$thread['istoday'] = $thread['dateline'] > $todaytime ? 1 : 0;
		$thread['dbdateline'] = $thread['dateline'];
		$thread['dateline'] = dgmdate($thread['dateline'], 'u', '9999', getglobal('setting/dateformat'));
		$thread['dblastpost'] = $thread['lastpost'];
		$thread['lastpost'] = dgmdate($thread['lastpost'], 'u');

		if(in_array($thread['displayorder'], array(1, 2, 3, 4))) {
			$thread['id'] = 'stickthread_'.$thread['tid'];
		} else {
			$thread['id'] = 'normalthread_'.$thread['tid'];
		}
		$thread['rushreply'] = getstatus($thread['status'], 3);
		return $thread;
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
			$message = preg_replace_callback('/\[img.*?\](.*?)\[\/img\]|\[attach\]([0-9]+)\[\/attach\]|\[hide\](.*?)\[\/hide\]|\[i.*\](.*)' . 
					'\[\/i\]|\[.*?\]|\\n|\\r/', 'BigAppAPI::imgCallback', $message);
			
			//fix bug, pre_replace_callback not used on some sites		
			$message = preg_replace('/\[img.*?\](.*?)\[\/img\]|\[attach\]([0-9]+)\[\/attach\]|\[hide\](.*?)\[\/hide\]|\[i.*\](.*)' . 
					'\[\/i\]|\[.*?\]|\\n|\\r/', '', $message);
					
			if(function_exists('mb_substr')){
				$message = mb_substr($message, 0, 1000, 'UTF-8');
			}else{
				$message = substr($message, 0, 2000);
			}
			
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

	protected function imgCallback($matches)
	{	
		$collect = &BigAppAPI::$collect;
		if(isset($matches[1]) && !empty($matches[1])){
			$collect[] = $matches[1];
		}else if(isset($matches[2]) && !empty($matches[2])){
			$collect[] = $matches[2];
		}else if(isset($matches[3]) && !empty($matches[3])){
			return '[隐藏内容，请登录后查看]';	
		}
		return '';
	}

	//获取帖子消息等详细信息
	protected function _getDetails(&$list)
	{
		global $_G;

		$tids = array();
		$tbTids = array();
		foreach ($list as $l){
			$tids[] = $l['tid'];
			if(0 == $l['posttableid']){
				$table = DB::table('forum_post');
			}else{
				$table = DB::table('forum_post') . '_' . $l['posttableid'];
			}
			$tbTids[$table][] = $l['tid'];
		}
		
		if(empty($tids)){
			return ;
		}
		############################################
		$sqls = array();
		foreach ($tbTids as $table => &$tids){
			$tids = array_unique($tids);
			$sql = 'SELECT pid, tid, message FROM ' . $table . ' WHERE tid IN (' . implode(', ', $tids) . ') AND first = 1';
			$sqls[] = $sql;
		}
		unset($tids);
		if(empty($sqls)){
			return ;
		}
		$threadInfo = array();
		$pids = array();
		foreach ($sqls as $sql){
			runlog('bigapp', 'get message from forum_thread, sql: ' . $sql);
			$query = DB::query($sql);
			while($tmp = DB::fetch($query)){
				$threadInfo[$tmp['tid']] = array('pid' => $tmp['pid'], 'tid' => $tmp['tid'], 'message' => $tmp['message']);
				$pids[$tmp['pid']] = $tmp['pid'];
			}
		}
		############################################
		
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
