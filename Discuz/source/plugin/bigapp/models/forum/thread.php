<?php
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
require_once './source/class/class_core.php';
require_once './source/function/function_forum.php';
$discuz = C::app();
$mod = $discuz->var['mod'];
$discuz->init();
require_once libfile('function/cache');

$expireTime = 3600;
$_G['siteurl'] = str_replace('api/mobile/', '', $_G['siteurl']);
/* *****************************************************************************/
/**
 * @Brief getGroupFroumInfo 获取当前用户允许访问的板块ID及基本信息
 *
 * @Returns 
 */
/* *****************************************************************************/
function getGroupFroumInfo($gid = null)
{
	global $_G, $expireTime;
	$oldGid = $_G['groupid'];
	if(!is_null($gid) && is_numeric($gid) && $gid >= 0){
		$cacheKey = 'bigapp_group_fids_' . $gid;
		$_G['groupid'] = $gid;
	}else{
		$cacheKey = 'bigapp_group_fids_' . $_G['groupid'];
	}
	true === BIGAPP_DEV && runlog('bigapp', 'group key: ' . $cacheKey);
	loadcache($cacheKey);
	$forums = array();
	if(!isset($_G['cache'][$cacheKey]) || empty($_G['cache'][$cacheKey]) || TIMESTAMP - $_G['cache'][$cacheKey]['expiration'] > $expireTime){
		$sql = "SELECT f.fid, f.type, f.name, f.fup, f.status, f.threads, f.posts, f.todayposts, " . 
			"ff.password, ff.redirect, ff.viewperm, ff.postperm, ff.threadtypes, ff.threadsorts, ff.icon, ff.description, ff.moderators FROM " .
			DB::table('forum_forum') . " f LEFT JOIN " . DB::table('forum_forumfield') . " ff USING(fid) WHERE f.status='1' ORDER BY f.type, f.displayorder";
		$query = DB::query($sql);
		while($forum = DB::fetch($query)){
			//过滤掉会跳转或需要密码的板块
			if($forum['redirect'] || $forum['password']) {
				true === BIGAPP_DEV && runlog('bigapp', 'such forum will redirect or need password, ignore [ fid: ' . $forum['fid'] . ' ]');
				continue;
			}
			//板块存在用户组的权限设定，那么判定该用户组是否允许访问该板块
			if(!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
				$forums[$forum['fid']] = array('fid' => $forum['fid'], 'threadtypes' => unserialize($forum['threadtypes']));
				continue;
			}
			true === BIGAPP_DEV && runlog('bigapp', 'viewperm has been set [ perm: ' . $forum['viewperm'] . ', group id: ' . $_G['groupid']  . ', ignore fid: ' . $forum['fid'] . ' ]');
		}
		savecache($cacheKey, array('variable' => $forums, 'expiration' => TIMESTAMP));
		true === BIGAPP_DEV && runlog('bigapp', 'save forum ids to cache for group id [ group id: ' . $_G['groupid'] . ' ]');
	}else{
		true === BIGAPP_DEV && runlog('bigapp', 'get forum ids from cache data [ group id: ' . $_G['groupid'] . '  ]');
		$forums = $_G['cache'][$cacheKey]['variable'];
	}
	true === BIGAPP_DEV && runlog('bigapp', 'get group forum info finished [ fids: ' . json_encode(array_keys($forums)) . ' ]');
	$_G['groupid'] = $oldGid;
	return $forums;
}

/* *****************************************************************************/
/**
 * @Brief getArchiveIds 获得所有板块的存档ID号
 *
 * @Param $fids 要获取的板块ID
 *
 * @Returns 组: 存档ID表，为空时表示不存在存档
 */
/* *****************************************************************************/
function getArchiveIds($fids = array())
{
	if(empty($fids)){
		return array();
	}
	$sql = 'SELECT fid, threadtableid FROM ' . DB::table('forum_forum_threadtable');
	if(!empty($fids)){
		$sql .= ' WHERE fid IN (' . implode(', ', $fids) . ')';
	}
	true === BIGAPP_DEV && runlog('bigapp', $sql);
	$ids = array(0);
	$query = DB::query($sql);
	while($id = DB::fetch($query)){
		$ids[] = intval($id['threadtableid']);
	}
	$ids = array_unique($ids);
	return $ids;
}

/* *****************************************************************************/
/**
 * @Brief getSingleThreadsFromForums 从特定板块中获取指定的主题列表信息
 *
 * @Param $tids 要查询的主题ID号
 * @Param $fids 用户允许查看的板块有哪些
 *
 * @Returns 
*/
/* *****************************************************************************/
function getSingleThreadsFromForums($tids, $fids = array())
{
	if(empty($tids) || empty($fids)){
		return array();
	}
	$ids = getArchiveIds($fids); //获取指定板块有哪些存档表	
	$threadTables = array(DB::table('forum_thread'));
	foreach ($ids as $id){
		if(0 == $id){
			continue;
		}
		$threadTables[] = DB::table('forum_thread') . '_' . $id;
	}
	$sqlTemplate0 = 'SELECT tid, fid, posttableid, typeid, readperm, author, authorid, subject, dateline, lastpost, lastposter, views, replies, digest, attachment, heats, icon FROM ';
	$sqlTemplate1 = ' WHERE tid IN (' . implode(', ', $tids) . ') AND displayorder >= 0';
	$sqls = array();
	foreach ($threadTables as $threadTable){
		$sqls[] = $sqlTemplate0 . $threadTable . $sqlTemplate1;
	}
	$ret = array();
	foreach ($sqls as $sql){
		$query = DB::query($sql);
		while($tmp = DB::fetch($query)){
			$ret[] = $tmp;
		}
	}
	return $ret;
}

/* *****************************************************************************/
/**
* @Brief clearFidsList 清除指定用户组（全部）在指定版块ID下的所有列表信息
*
* @Param $fids 要删除哪些板块的信息
* @Param $gid 要删除哪些用户组的信息，如果是null，则表示所有用户组
*
* @Returns 
*/
/* *****************************************************************************/
function clearFidsList($fids, $gid = null)
{
	global $_G;
	if(is_null($gid)){
		$groupIds = getUserGroupList();
	}else{
		$groupIds = (array)$gid;
	}
	$cacheKeys = array();
	sort($fids);
	foreach ($groupIds as $groupId){
		$cacheKey = 'bigapp_fids_tinfos_' . $groupId . '_' . implode(', ', $fids);
		$md5Key = md5($cacheKey);
		true === BIGAPP_DEV && runlog('bigapp', "add cache key to delete list [ key: $cacheKey, md5 key: $md5Key ]");
		$cacheKeys[] = $md5Key;
		//强制清除所有子缓存数据，要求从DB中获取
		loadcache($md5Key, true);
		if(isset($_G['cache'][$md5Key]['variable'])){
			$totalPage = $_G['cache'][$md5Key]['variable']['page'];
			for ($idx = 0; $idx < $totalPage; $idx++){
				$cacheKey = 'bigapp_fids_tinfos_' . $groupId . '_' . implode(', ', $fids) . '_' . $idx;
				$md5Key = md5($cacheKey);
				true === BIGAPP_DEV && runlog('bigapp', "add cache key to delete list [ key: $cacheKey, md5 key: $md5Key ]");
				$cacheKeys[] = $md5Key;
			}
		}else{
			true === BIGAPP_DEV && runlog('bigapp', "such summary key has no subkeys [ key: $cacheKey, md5 key: $md5Key ]");
		}
	}
	if(!empty($cacheKeys)){
		C::t('common_syscache')->delete((array)$cacheKeys);
	}
}

/* *****************************************************************************/
/**
* @Brief getTidsFromForums 获取当前用户组在指定版块下的列表信息
*
* @Param $fids 要获取哪些版块，函数内部会对无法展示的板块做删除
*
* @Returns 
*/
/* *****************************************************************************/
function getThreadsFromForums($fids, $imgMode = false, $pageNo = 1, $excludeTids = array())
{
	if($pageNo <= 0){
		$pageNo = 1;
	}
	$pageSize = 20;
	$start = ($pageNo - 1) * $pageSize;
	sort($fids);
	$oldFids = $fids;
	//获得当前用户组允许访问的板块列表
	$fInfos = getGroupFroumInfo();
	$_fids = array_keys($fInfos);
	//和想要访问的板块做交集，得到真正允许访问的板块列表
	$fids = array_intersect($fids, $_fids);
	if(empty($fids)){
		return array(
			'page_no' => $pageNo,
			'page_size' => $pageSize,
			'total' => 0,
			'total_page' => 0,
			'thread_list' => array(),
		);
	}
	global $_G, $expireTime;
	sort($fids); //排序，减少缓存数量
	//当前用户组在指定板块下的摘要KEY
	$cacheKey = 'bigapp_fids_tinfos_' . $_G['groupid'] . '_' . implode(', ', $oldFids);
	$md5Key = md5($cacheKey);
	$detailCacheKey = '';
    true === BIGAPP_DEV && runlog('bigapp', 'bigapp fids thread info summary key: ' . $cacheKey . ', md5 key: ' . $md5Key . ', group id: ' . $_G['groupid']);
    loadcache($md5Key);
    $ret = array(
		'page_no' => $pageNo,
		'page_size' => $pageSize,
		'total' => 0,
		'total_page' => 0,
		'thread_list' => array(),
	);
    if(!isset($_G['cache'][$md5Key]) || empty($_G['cache'][$md5Key]) || TIMESTAMP - $_G['cache'][$md5Key]['expiration'] > $expireTime){	
		if(isset($_G['cache'][$md5Key])){
			clearFidsList($oldFids, $_G['groupid']);
		}
		//获取版块中所有的主题ID
		$tInfo = array();
		$sql = 'SELECT SQL_CALC_FOUND_ROWS tid, fid, posttableid, typeid, readperm, author, authorid, subject, dateline, lastpost, lastposter, views, replies, digest, attachment, heats, icon FROM ' . 
				DB::table('forum_thread') . ' WHERE fid IN (' . implode(', ', $fids) . ') AND displayorder >= 0 ORDER BY dateline DESC LIMIT ' . $start . ', ' . $pageSize;	
		true === BIGAPP_DEV && runlog('bigapp', "summary info does not exist or expired, get thread info from db [ start: $start, page size: $pageSize, page no: $pageNo, sql: $sql ]");
		$query = DB::query($sql);
		while($tmp = DB::fetch($query)){
			$tInfo[] = $tmp;
		}
		$sql = 'select FOUND_ROWS() AS total';
		true === BIGAPP_DEV && runlog('bigapp', 'get total [ sql: ' . $sql . ' ]');
		$query = DB::query($sql);
		$total = 0;
		while($tmp = DB::fetch($query)){
			$total = $tmp['total'];
			break;
		}
		$sumary = array('fids' => $oldFids, 'total' => $total, 'page_size' => $pageSize, 'page' => intval(($total - 1) / $pageSize) + 1);
		true === BIGAPP_DEV && runlog('bigapp', 'save summary info [ cache key: ' . $cacheKey . ', md5 key: ' . $md5Key . ', value: ' . json_encode($sumary) . ' ]');
		savecache($md5Key, array('variable' => $sumary, 'expiration' => TIMESTAMP)); //缓存摘要信息
		//接下来缓存当前用户组在指定块下、指定页面上的信息
		$subKeyTemp = 'bigapp_fids_tinfos_' . $_G['groupid'] . '_' . implode(', ', $oldFids);
		$subKey = $subKeyTemp . '_' . ($pageNo - 1);
		$md5SubKey = md5($subKey);
		$ret['total'] = $total;
		$ret['total_page'] = intval(($total - 1) / $pageSize) + 1;
		if(!empty($tInfo)){
			//当前页非空，缓存本页数据
			extendThreadsInfo($tInfo, $fInfos);
			getDetails($tInfo);
			savecache($md5SubKey, array('variable' => $tInfo, 'expiration' => TIMESTAMP)); //缓存子KEY信息
			true === BIGAPP_DEV && runlog('bigapp', "such page is not empty, save them to cache [ page: $pageNo, key: $subKey, md5 key: $md5SubKey ]");
			$ret['thread_list'] = $tInfo;
		}else{
			true === BIGAPP_DEV && runlog('bigapp', "such page is empty, ignore [ page: $pageNo, key: $subKey, md5 key: $md5SubKey ]");
			//do nothing
			$ret['thread_list'] = array();
		}
	}else{
		true === BIGAPP_DEV && runlog('bigapp', 'summary info already exist, get thread info from cache [ key: ' . $cacheKey . ', summary key: ' . $md5Key . ' ]');
		$sumVar = $_G['cache'][$md5Key]['variable'];
		$ret['total'] = $sumVar['total'];
		$ret['total_page'] = $sumVar['page'];
		if($pageNo <= $sumVar['page']){
			$subKey = 'bigapp_fids_tinfos_' . $_G['groupid'] . '_' . implode(', ', $oldFids) . '_' . ($pageNo - 1);
			$md5SubKey = md5($subKey);
			loadcache($md5SubKey);
			if(!isset($_G['cache'][$md5SubKey]) || empty($_G['cache'][$md5SubKey])){
				true === BIGAPP_DEV && runlog('bigapp', "such page does not in cache, try to create it [ page no: $pageNo, sub key: $subKey, md5 key: $md5SubKey ]");
				$sql = 'SELECT tid, fid, posttableid, typeid, readperm, author, authorid, subject, dateline, lastpost, lastposter, views, replies, digest, attachment, heats, icon FROM ' . 
					DB::table('forum_thread') . ' WHERE fid IN (' . implode(', ', $fids) . ') AND displayorder >= 0 ORDER BY dateline DESC LIMIT ' . $start . ', ' . $pageSize;	
				true === BIGAPP_DEV && runlog('bigapp', "get sub info from db [ page no: $pageNo, sub key: $subKey, md5 key: $md5SubKey, sql: $sql ]");
				$query = DB::query($sql); 
				while($tmp = DB::fetch($query)){
					$tInfo[] = $tmp;
				}
				if(!empty($tInfo)){
					extendThreadsInfo($tInfo, $fInfos);
					getDetails($tInfo);
					savecache($md5SubKey, array('variable' => $tInfo, 'expiration' => TIMESTAMP)); //缓存子KEY信息
					true === BIGAPP_DEV && runlog('bigapp', "create such page succ, save them to cache [ page no: $pageNo, sub key: $subKey, md5 key: $md5SubKey, num of result: " . count($tInfo) . " ]");
					$ret['thread_list'] = $tInfo;
				}else{
					true === BIGAPP_DEV && runlog('bigapp', "create such page failed, empty [ page no: $pageNo, sub key: $subKey, md5 key: $md5SubKey, page no: " . count($tInfo) . " ]");
					//do nothing
				}
			}else{
				true === BIGAPP_DEV && runlog('bigapp', 'such page already exists in cache, get thread info from cache [ page no: ' . $pageNo . ', sub key ' . $subKey .  ', md5 key: ' . $md5SubKey . ' ]');
				$ret['thread_list'] = $_G['cache'][$md5SubKey]['variable'];
			}
		}else{
			true === BIGAPP_DEV && runlog('bigapp', "invalid page, return empty array [ page no: $pageNo, total page: " . $sumVar['page'] . " ]");
			//do nothing
		}
	}
	$tmp = $ret['thread_list'];
	$ret['thread_list'] = array();
	if(!empty($excludeTids)){
		foreach ($tmp as $_t){
			if(in_array($_t['tid'], $excludeTids)){
				true === BIGAPP_DEV && runlog('bigapp', 'tid is in exclude array, ignore [ tid: ' . $_t['tid'] . ', ext ids: ' . json_encode($excludeTids) . ' ]');
				continue;
			}
			if(!$imgMode){
				$_t['message_abstract'] = '';
				$_t['attachment_urls'] = array();
			}
			$ret['thread_list'][] = $_t;
		}
	} else {
		foreach ($tmp as $_t){
			if(!$imgMode){
				$_t['message_abstract'] = '';
				$_t['attachment_urls'] = array();
			}
			$ret['thread_list'][] = $_t;
		}
	}
	true === BIGAPP_DEV && runlog('bigapp', 'get thread info finished [  count: ' . count($ret) . ', page: ' . $pageNo . ' ]');
	return $ret;
}

/* *****************************************************************************/
/**
* @Brief extendThreadsInfo 处理和扩展基本的主题信息
*
* @Param $infos 要处理的信息
* @Param $fInfos 版块信息
*
* @Returns 
*/
/* *****************************************************************************/
function extendThreadsInfo(&$infos, $fInfos)
{
	foreach ($infos as &$info){
		$info['subject'] = preg_replace('/<.*?\>/', '', $info['subject']);
		$info['dbdateline']  = $info['dateline'];
		$info['dateline'] = date('Y-m-d', $info['dateline']);
		$info['lastpost'] = date('Y-m-d H:i:s', $info['lastpost']);
		$info['avatar'] = avatar($info['authorid'], 'big', 'true');
		$info['typename'] = '';
		if(isset($fInfos[$info['fid']]['threadtypes']['types'][$info['typeid']])){
			$info['typename'] = $fInfos[$info['fid']]['threadtypes']['types'][$info['typeid']];
			$info['typename'] = preg_replace('/<.*?\>/', '', $info['typename']);
		}
		$info['message_abstract'] = '';
		$info['attachment_urls'] = array();
	}
	unset($infos);
}

function getDetails(&$list)
{
	global $_G;
	$tbTids = array();
	foreach ($list as $l){
		$tids[$l['tid']] = array('tid' => $l['tid'], 'posttableid' => $l['posttableid']);
		if(0 == $l['posttableid']){
			$table = DB::table('forum_post');
		}else{
			$table = DB::table('forum_post') . '_' . $l['posttableid'];
		}
		$tbTids[$table][] = $l['tid'];
	}
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
		true === BIGAPP_DEV && runlog('bigapp', 'get message from forum_thread, sql: ' . $sql);
		$query = DB::query($sql);
		while($tmp = DB::fetch($query)){
			$threadInfo[$tmp['tid']] = array('pid' => $tmp['pid'], 'tid' => $tmp['tid'], 'message' => $tmp['message']);
			$pids[$tmp['pid']] = $tmp['pid'];
		}
	}
	if(empty($pids)){
		return ;
	}
	$sql = 'SELECT aid, tid, tableid, pid FROM ' . DB::table('forum_attachment') . ' WHERE pid IN (' . implode(', ', $pids) . ')';
	true === BIGAPP_DEV && runlog('bigapp', 'get aid and tableid from db [ sql: ' . $sql . ' ]');
	$tbIdx = array();
	$query = DB::query($sql); 
	while($tmp = DB::fetch($query)){
		if($tmp['tableid'] < 10){
			$threadInfo[$tmp['tid']]['aid'][] = $tmp['aid'];
			$tbIdx[$tmp['tableid']][] = $tmp['aid'];
		}
	}
	foreach ($tbIdx as $tableId => $aids){
		$sql = 'SELECT aid, tid, attachment, description, remote, isimage FROM ' . DB::table('forum_attachment_' . $tableId) . ' WHERE aid IN (' . implode(', ', $aids) . ')';
		true === BIGAPP_DEV && runlog('bigapp', 'get attachment info from db, sql: ' . $sql);
		$query = DB::query($sql);
		while($tmp = DB::fetch($query)){
			$isImage = $tmp['isimage'];
			if($tmp['isimage'] && !$_G['setting']['attachimgpost']){
				$isImage = 0;
			}
			if($isImage){
				$threadInfo[$tmp['tid']]['attachments'][$tmp['aid']] = array('attachment' => $tmp['attachment'], 
					'description' => $tmp['description'], 'remote' => $tmp['remote'], 'isimage' => $isImage);
				continue;
			}
			true === BIGAPP_DEV && runlog('bigapp', 'attachment is not an image or attachimgpost is not true, ignore [ attachment info: ' . $tmp['attachment'] . ' ]');
		}
	}
	getPictures($threadInfo);
	foreach ($list as &$l){
		if(isset($threadInfo[$l['tid']]['message']) && is_string($threadInfo[$l['tid']]['message'])){
			$l['message_abstract'] = $threadInfo[$l['tid']]['message'];
		}
		if(isset($threadInfo[$l['tid']]['attachment_urls']) && is_array($threadInfo[$l['tid']]['attachment_urls'])){
			$l['attachment_urls'] = $threadInfo[$l['tid']]['attachment_urls'];
		}
		if(true === BigAppConf::$enablePicOpt){
			foreach ($l['attachment_urls'] as &$_url){
				if(ApiUtils::isOptFix($_url)){
					$_url = rtrim($_G['siteurl'], '/') . '/plugin.php?id=bigapp:optpic&size=' . urlencode(BigAppConf::$thumbSize) . '&url=' . urlencode($_url);
					$_url = str_replace('source/plugin/mobile/', '', $_url);
					$_url = str_replace('source/plugin/bigapp/', '', $_url);
				}
			}
			unset($_url);
		}
	}
	unset($l);
}

$collect = array();
function imgCallback($matches)
{   
	global $collect;
	if(isset($matches[1]) && !empty($matches[1])){
		$collect[] = $matches[1];
	}else if(isset($matches[2]) && !empty($matches[2])){
		$collect[] = $matches[2];
	}else if(isset($matches[3]) && !empty($matches[3])){
		return '[隐藏内容，请登录后查看]';
	}
	return '';
}

function getPictures(&$threadInfo)
{
	global $_G, $collect;
	foreach ($threadInfo as $tid => &$info){
		$collect = array();
		$message = str_replace("\r", '', $info['message']);
		$message = str_replace("\n", '', $message);
		$message = str_replace('\r', '', $message);
		$message = str_replace('\n', '', $message);
		if(function_exists('iconv')){
			$message = iconv(CHARSET, 'UTF-8//ignore', $message);
			foreach($info['attachments'] as &$att){
				$att['description'] = '__DONT_DICONV_TO_UTF8___' . iconv(CHARSET, 'UTF-8//ignore', $att['description']);
			}
			unset($att);
		}else{
			$message = mb_convert_encoding($message, 'UTF-8', CHARSET);
			foreach($info['attachments'] as &$att){
				$att['description'] = '__DONT_DICONV_TO_UTF8___' . mb_convert_encoding($att['description'], 'UTF-8', CHARSET);
			}
			unset($att);
		}
		$message = preg_replace_callback('/\[img.*?\](.*?)\[\/img\]|\[attach\]([0-9]+)\[\/attach\]|\[hide\](.*?)\[\/hide\]|\[i.*\](.*)' .
			'\[\/i\]|\[.*?\]|\\n|\\r/', 'imgCallback', $message);
		$oldMessage = $message;
		if(function_exists('mb_substr')){
			$message = mb_substr($message, 0, 30, 'UTF-8');
		}else{
			$message = substr($message, 0, 60);
		}
		if($oldMessage !== $message){
			$message .= '...';
		}
		loadcache(array('smilies', 'smileytypes'));
		foreach($_G['cache']['smilies']['replacearray'] as $id => $img) {
			$pattern = $_G['cache']['smilies']['searcharray'][$id];
			$message = preg_replace($pattern, '[表情]', $message);
		}
		$info['message'] = '__DONT_DICONV_TO_UTF8___' . $message;
		$infoAttrs = $info['attachments'];	
		foreach ($collect as $attach){
			true === BIGAPP_DEV && runlog('bigapp', 'process attach collect [ ' . $attach . ' ]');
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

/* *****************************************************************************/
/**
* @Brief getSingleTidList 获取某些主题的列表信息
*
* @Param $tids 主题ID列表
* @Param $imgMode 是否是多图模式
*
* @Returns 
*/
/* *****************************************************************************/
function getSingleTidList($tids, $imgMode = false)
{
	if(empty($tids)){
		return array();
	}

	global $_G, $expireTime;
	//某用户组获取某些tids的信息
	$cacheKey = 'bigapp_group_tids_' . $_G['groupid'] . '_' . implode(', ', $tids);
	$md5Key = md5($cacheKey);
    true === BIGAPP_DEV && runlog('bigapp', 'bigapp group tids key: ' . $cacheKey . ', md5 key: ' . $md5Key);
    loadcache($cacheKey);
    $ret = array();
	if(!isset($_G['cache'][$md5Key]) || empty($_G['cache'][$md5Key]) || TIMESTAMP - $_G['cache'][$md5Key]['expiration'] > $expireTime){
		//1. 获取本用户组允许浏览的板块
		$fInfos = getGroupFroumInfo();
		$fids = array_keys($fInfos);
		//2. 获取本用户组能获取到的主题，考虑板块因素
		$ret = getSingleThreadsFromForums($tids, $fids);
		//3. 扩展主题信息
		extendThreadsInfo($ret, $fInfos);
		//4. 获取附件图片的信息
		getDetails($ret);
		savecache($md5Key, array('variable' => $ret, 'expiration' => TIMESTAMP));
        true === BIGAPP_DEV && runlog('bigapp', 'save thread infos for group and tids [ cache key: ' . $cacheKey . ', md5 key: ' . $md5Key  . ',  group id: ' . $_G['groupid'] . ', tids: ' . implode(', ', $tids) . ', img mode: ' . intval($imgMode) . ' ]');
    }else{
        true === BIGAPP_DEV && runlog('bigapp', 'get thread info from cache [ cache key: ' . $cacheKey . ', md5 key: ' . $md5Key  . ', group id: ' . $_G['groupid'] . ', tids: ' . implode(', ', $tids) . ', img mode: ' . intval($imgMode) . ' ]');
        $ret = $_G['cache'][$md5Key]['variable'];
    }
    if(!$imgMode){
		foreach ($ret as &$l){
			$l['message_abstract'] = '';
			$l['attachment_urls'] = array();
		}
		unset($l);
	}
	return $ret;
}


/* *****************************************************************************/
/**
* @Brief getUserGroupList 获取所有的用户组ID号列表
*
* @Returns 
*/
/* *****************************************************************************/
function getUserGroupList()
{
	$sql = 'SELECT groupid FROM ' . DB::table('common_usergroup');
	$query = DB::query($sql);
	$gids = array();
	while($tmp = DB::fetch($query)){
		$gids[] = $tmp['groupid'];
	}
	true === BIGAPP_DEV && runlog('bigapp', 'get user group list succ [ gids: ' . implode(', ', $gids) . ' ]');
	return $gids;
}

/* *****************************************************************************/
/**
* @Brief clearSingleTidList 供外部调用本函数来清空之前的缓存数据
*
* @Param $tids 主题ID列表
* @Param $imgMode 是否是多图模式
*
* @Returns NA
*/
/* *****************************************************************************/
function clearSingleTidList($tids)
{
	if(empty($tids)){
		return ;
	}
	global $_G, $expireTime;
	$gids = getUserGroupList();
	if(empty($gids)){
		return ;
	}
	$cacheKeys = array();
	foreach ($gids as $gid){
		$cacheKey = 'bigapp_group_tids_' . $gid . '_' . implode(', ', $tids);
		$md5Key = md5($cacheKey);
		$cacheKeys[] = $md5Key;
		true === BIGAPP_DEV && runlog('bigapp', "add key to delete list [ key: $cacheKey, md5 key: $md5Key ]");
	}
	C::t('common_syscache')->delete((array)$cacheKeys);
}

//$ret = getSingleTidList(array(125, 685, 688), false);
//clearSingleTidList(array(125, 685, 688));
//--------------------------------------------------------------------------------------
//$threadInfos = getThreadsFromForums(array(2, 51, 57), true, $_GET['page'], array(685));
//clearFidsList(array(2, 51, 57));
//die(json_encode($threadInfos));
?>
