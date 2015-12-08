<?php
/**
* @file forumnav.php
* @Brief 
* @author youzu
* @version 1
* @date 2015-04-03
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once dirname(__FILE__) . '/sub_forum.php';
class BigAppAPI {
	function process() {
		global $_G;
	
		//1. try to get user mask
		$authority = array();
		if($_G['uid'] && !empty($_G['member']['accessmasks'])){
			loadcache('plugin');
			$bucketNum = 100003;
			$expire = 5;
			$userBucket = $_G['uid'] % $bucketNum;
			loadcache('bigapp_authority_' . $userBucket);
			if(!isset($_G['cache']['bigapp_authority_' . $userBucket]) || TIMESTAMP - $_G['cache']['bigapp_authority_' . $userBucket]['expiration'] > $expire){
				$sql = 'SELECT uid, fid, allowview FROM ' . DB::table('forum_access') . ' WHERE uid % ' . $bucketNum . ' = ' . $userBucket;
				$query = DB::query($sql);
				$authorities = array();
				while($tmp = DB::fetch($query)){
					$authorities[$tmp['uid']][$tmp['fid']] = $tmp['allowview'];
				}
				savecache('bigapp_authority_' . $userBucket, array('variable' => $authorities, 'expiration' => TIMESTAMP));
			}else{
				$authorities = $_G['cache']['bigapp_authority_' . $userBucket]['variable'];
			}
			if(isset($authorities[$_G['uid']])){
				$authority = $authorities[$_G['uid']];
			}
		}

		//2. try to get all forums
		$forums = array();
        $cacheKey = 'bigapp_forumnav';
        if(isset($_G['adminid'])){
            $cacheKey = 'bigapp_forumnav_' . $_G['adminid'];
        }
		loadcache($cacheKey);
		$expire = 5;
		if(!isset($_G['cache'][$cacheKey]) || empty($_G['cache'][$cacheKey]) || TIMESTAMP - $_G['cache'][$cacheKey]['expiration'] > $expire){
			//need update all forums from database
			$sql = "SELECT f.fid, f.type, f.name, f.fup, f.status, f.threads, f.posts, f.todayposts, f.allowpostspecial, f.allowspecialonly, " .
					"ff.password, ff.redirect, ff.viewperm, ff.postperm, ff.threadtypes, ff.threadsorts, ff.icon, ff.description, ff.moderators FROM " . 
					DB::table('forum_forum') . " f LEFT JOIN " . DB::table('forum_forumfield') . " ff USING(fid) WHERE f.status='1' ORDER BY f.type, f.displayorder";
			$query = DB::query($sql);
			while($forum = DB::fetch($query)){
				if($forum['redirect'] || $forum['password']) {
					continue;
				}
				if(!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
					if($forum['threadsorts']) {
						$forum['threadsorts'] = bigapp_core::getvalues(unserialize($forum['threadsorts']), array('required', 'types'));
					}
					if($forum['threadtypes']) {
						$forum['threadtypes'] = unserialize($forum['threadtypes']);
						$unsetthreadtype = false;
						if($_G['adminid'] == 3 && strpos($forum['moderators'], $_G['username']) === false) {
							$unsetthreadtype = true;
						}
						if($_G['adminid'] == 0) {
							$unsetthreadtype = true;
						}
						if($unsetthreadtype) {
							foreach ($forum['threadtypes']['moderators'] AS $k => $v) {
								if(!empty($v)) {
									unset($forum['threadtypes']['types'][$k]);
								}
							}
						}
						$flag = 0;
						foreach($forum['threadtypes']['types'] as $k => $v) {
							if($k == 0) {
								$flag = 1;
								break;
							}
						}
						if($flag == 1) {
							krsort($forum['threadtypes']['types']);
						}
						$forum['threadtypes'] = bigapp_core::getvalues($forum['threadtypes'], array('required', 'types'));
					}
					loadforum($forum['fid'], null);
					if(!empty($_G['forum']['threadtypes']) || !empty($_GET['debug'])) {
						$forum['threadtypes_detail'] = $_G['forum']['threadtypes'];
						unset($forum['threadtypes_detail']['types']);
						foreach ($_G['forum']['threadtypes']['types'] as $typeId => $typeValue){
							$typeValue = preg_replace('/<.*?>/', '', $typeValue);
                            if(isset($_G['forum']['threadtypes']['moderators'][$typeId]) && !empty($_G['forum']['threadtypes']['moderators'][$typeId])){
                                if(isset($_G['adminid']) && $_G['adminid'] != $_G['forum']['threadtypes']['moderators'][$typeId]){
                                    continue;
                                }
                            }
							$forum['threadtypes_detail']['types'][] = array('typeid' => $typeId, 'typename' => $typeValue);
           				}
						unset($forum['threadtypes_detail']['icons']);
						foreach ($_G['forum']['threadtypes']['icons'] as $typeId => $icon){
							$forum['threadtypes_detail']['icons'][] = array('typeid' => $typeId, 'typeicon' => $icon);
						}
        			}	
					$moderators = explode("\t", $forum['moderators']);
					if(!is_array($moderators) || 0 === count($moderators)){
						$forum['moderators'] = array();
					}else{
						foreach ($moderators as &$_v){
							$_v = "'${_v}'";
						}
						unset($_v);
						$sql = 'SELECT username, uid FROM ' . DB::table('common_member') . ' WHERE username IN (' . implode(', ', $moderators) . ')';
						$subQuery = DB::query($sql);
						$forum['moderators'] = array();
						while($moderator = DB::fetch($subQuery)){
							$forum['moderators'][] = array('uid' => $moderator['uid'], 'username' => $moderator['username']);
						}
					}
					$forums[] = bigapp_core::getvalues($forum, array('fid', 'type', 'name', 'fup', 'viewperm', 
							'postperm', 'status', 'threadsorts', 'threadtypes', 'threadtypes_detail', 'icon', 'description', 'threads', 'allowpostspecial', 'allowspecialonly', 
							'posts', 'todayposts', 'moderators'));
				}
			}
			//add result to syscache 
			savecache($cacheKey, array('variable' => $forums, 'expiration' => TIMESTAMP));
		}else{
			$forums = $_G['cache'][$cacheKey]['variable'];
		}
		
		//3. judge which forum should be displayed
		$retData = array();
		if(!empty($authority)){
			foreach ($forums as $forum){
				if(isset($authority[$forum['fid']]) && -1 == $authority[$forum['fid']]){
					continue;
				}
				$retData[] = $forum;
			}
		}else{
			$retData = $forums;
		}
		
		//4. AppDegin 论坛视图设置过滤
		$res = C::t('common_setting')->fetch("bigapp_view_2", true);
		if(isset($res['displayid']) && isset($res['forbiddenid'])) { //获取论坛视图配置
			$displayArr = array();
			$forbiddenArr = array();
			
			if(!empty($res['displayid'])) {
				$displayArr = explode(',' , $res['displayid']);
			}
			
			if(!empty($res['forbiddenid'])) {
				$forbiddenArr = explode(',' , $res['forbiddenid']);
			}
			
			$retData = BigAppAPI::_filterResult($retData, $displayArr, $forbiddenArr);
		}
		
		$activityForum = array();
		foreach ($retData as $forum){
			if(isset($_G['group']['allowpostactivity']) && $_G['group']['allowpostactivity'] && ($forum['allowpostspecial'] & 8)){
				$activityForum[] = $forum;
			}
		}
		foreach ($retData as &$value){
			BigAppAPI::_textDescription($value);
		}	
		unset($value);
		$variable['forums'] = array_values(BigAppAPI::_sortResult($retData));
		$variable['activity_forums'] = $activityForum;
		if(isset($_G['setting']['bigapp_settings'])){
            $_G['setting']['bigapp_settings'] = unserialize($_G['setting']['bigapp_settings']);
        }	
		
		$res = C::t('common_setting')->fetch("bigapp_view_2", true);
		if(isset($res[0]) && empty($res[0])) { //没有获取论坛视图配置
			$variable['display_style'] = strval((isset($_G['setting']['bigapp_settings']['display_style']) ? $_G['setting']['bigapp_settings']['display_style'] : 0));
		} else {
			$variable['display_style'] = isset($res["type"]) ? strval(intval($res["type"]) - 1) : '0';
		}

		bigapp_core::result(bigapp_core::variable($variable));
	}

	function callback($matches){
        $smiles = array(
                'smile.gif' => ':)',
            'sad.gif' => ':(',
                    'biggrin.gif' => ':D',
                    'cry.gif' => ':\'(',
                    'huffy.gif' => ':@',
                    'shocked.gif' => ':o',
                    'shocked.png' => ':o',
                    'tongue.gif' => ':P',
                    'shy.gif' => ':$',
                    'titter.gif' => ';P',
                    'sweat.gif' => ':L',
                    'mad.gif' => ':Q',
                    'lol.gif' => ':lol',
                    'loveliness.gif' => ':loveliness:',
                    'funk.gif' => ':funk:',
                    'curse.gif' => ':curse:',
                    'dizzy.gif' => ':dizzy:',
                    'shutup.gif' => ':shutup:',
                    'sleepy.gif' => ':sleepy:',
                    'hug.gif' => ':hug:',
                    'victory.gif' => ':victory:',
                    'time.gif' => ':time:',
                    'kiss.gif' => ':kiss:',
                    'handshake.gif' => ':handshake',
                    'call.gif' => ':call:',
                    );
        if(!isset($matches[1])){
            return $matches[0];
        }
        $baseName = basename($matches[1]);
        if(isset($smiles[$baseName])){
            return $smiles[$baseName];
        }
        return $matches[1];
    }

	protected function _textDescription(&$value)
	{
		if(!isset($value['description'])){
			return ;
		}
		$search = array ("'<script[^>]*?>.*?</script>'si", // 去掉 javascript
                "'<img.*src=\"(.*)\" .*>'iU",
                "'<[\/\!]*?[^<>]*?>'si",      // 去掉 HTML 标记
                //"'([\r\n])[\s]+'",         // 去掉空白字符
                "'&(quot|#34);'i",         // 替换 HTML 实体
                "'&(amp|#38);'i",
                "'&(lt|#60);'i",
                "'&(gt|#62);'i",
                "'&(nbsp|#160);'i",
                "'&(iexcl|#161);'i",
                "'&(cent|#162);'i",
                "'&(pound|#163);'i",
                "'&(copy|#169);'i",
                "'&#(\d+);'e",
                "'\\\\r\\\\n'i",
                );

        $replace = array ("",
                "[img]\\1[/img]",
                "",
                //"\\1",
                "\"",
                "&",
                "<",
                ">",
                " ",
                chr(161),
                chr(162),
                chr(163),
                chr(169),
                "chr(\\1)",
                "\r\n",
                );
		$value['description'] = preg_replace ($search, $replace, $value['description']);
	}
	
	protected function _filterResult($forums, $displayArr, $forbiddenArr) {
		if(empty($displayArr) && empty($forbiddenAr)) return $forums;
			
	    $retData = array();
		$group = array();
		$forum = array();
		$sub = array();
		
		foreach((array)$forums as $v){
			if('group' === $v['type'])	{
				$group[$v['fid']] = $v;
				continue;
			}
			if('forum' === $v['type']){
				$forum[$v['fid']] = $v;
				continue;
			}
			if('sub' === $v['type']) {
				$sub[$v['fid']] = $v;
				continue;
			}
		}
		
		foreach((array)$forums as $v) {
			if(isset($v['fup'])) {
				if('forum' === $v['type']) {
					$group[$v['fup']]['fdn'][] = $v['fid'];
				}
				
				if('sub' === $v['type']) {
					$forum[$v['fup']]['fdn'][] = $v['fid'];
				}
			} 
		}
		
		$showForumIds = array();
		$excludeForumIds = array();
		
		//$displayArr = array('37');
		//$forbiddenArr = array('36');
		foreach ($forums as $v){
			$fid = $v['fid'];
			
			if(in_array($fid, $displayArr)) {
				$showForumIds[] = $fid;
				//collect father forum infos
				if('forum' === $v['type']) {
					if(!in_array($v['fup'], $showForumIds)) $showForumIds[] = $v['fup'];
				}

				if('sub' === $v['type']) {
					if(!in_array($v['fup'], $showForumIds))	$showForumIds[] = $v['fup'];
					if(!in_array($forum[$v['fup']]['fup'], $showForumIds))	$showForumIds[] = $forum[$v['fup']]['fup'];
				}
				
				//collect children forum infos
				##########################################################################
				if('group' === $v['type']) {	
					if(isset($group[$fid]['fdn']) && !empty($group[$fid]['fdn'])) {
						foreach($group[$fid]['fdn'] as $fdn) {
							if(!in_array($fdn, $showForumIds)) $showForumIds[] = $fdn;
							
							if(isset($forum[$fdn]['fdn']) && !empty($forum[$fdn]['fdn'])) {
								foreach($forum[$fdn]['fdn'] as $fdn1) {
									if(!in_array($fdn1, $showForumIds)) $showForumIds[] = $fdn1;
								}
							}
						}
					}
				}
				
				if('forum' === $v['type']) {
					if(isset($forum[$fid]['fdn']) && !empty($forum[$fid]['fdn'])) {
						foreach($forum[$fid]['fdn'] as $fdn) {
							if(!in_array($fdn, $$showForumIds)) $showForumIds[] = $fdn;
						}
					}
				}
				##################################################################################
			}

			if(in_array($fid, $forbiddenArr)) {
				$excludeForumIds[] = $fid;
				
				if('group' === $v['type']) {
					
					if(isset($group[$fid]['fdn']) && !empty($group[$fid]['fdn'])) {
						foreach($group[$fid]['fdn'] as $fdn) {
							if(!in_array($fdn, $excludeForumIds)) $excludeForumIds[] = $fdn;
							
							if(isset($forum[$fdn]['fdn']) && !empty($forum[$fdn]['fdn'])) {
								foreach($forum[$fdn]['fdn'] as $fdn1) {
									if(!in_array($fdn1, $excludeForumIds)) $excludeForumIds[] = $fdn1;
								}
							}
						}
					}
				}
				
				if('forum' === $v['type']) {
					if(isset($forum[$fid]['fdn']) && !empty($forum[$fid]['fdn'])) {
						foreach($forum[$fid]['fdn'] as $fdn) {
							if(!in_array($fdn, $excludeForumIds)) $excludeForumIds[] = $fdn;
						}
					}
				}
			}
		}

		//var_dump($showForumIds);
		//var_dump($excludeForumIds) or die();
		//show forum data		
		foreach($forums as $v) {
			if((in_array($v['fid'], $showForumIds) || empty($showForumIds)) && !in_array($v['fid'], $excludeForumIds)) {
				$retData[] = $v;
			}
		}   
	   return $retData;
   }
		

	protected function _sortResult($forums)
	{
		$group = array();
		$forum = array();
		$sub = array();
		foreach ((array)$forums as $v){
			$v['name'] = preg_replace('/<.*?\>/', '', $v['name']);
			$v['icon'] = ApiUtils::getAttachPath($v['icon']);
			if(empty($v['icon'])){
				$v['icon'] = '';
			}
			if('group' === $v['type'])	{
				$group[$v['fid']] = $v;
				continue;
			}
			if('forum' === $v['type']){
				$forum[$v['fid']] = $v;
				continue;
			}
			$sub[$v['fid']] = $v;
		}
		foreach ($sub as $v){
			if(isset($forum[$v['fup']])){
				$forum[$v['fup']]['subs'][] = $v;
				if(!isset($forum[$v['fup']]['posts'])){
					$forum[$v['fup']]['posts'] = 0;
				}
				if(!isset($forum[$v['fup']]['threads'])){
					$forum[$v['fup']]['threads'] = 0;
				}
				$forum[$v['fup']]['posts'] += $v['posts'];
				$forum[$v['fup']]['threads'] += $v['threads'];
			}
		}
		foreach ($forum as $v){
			if(isset($group[$v['fup']])){
				$group[$v['fup']]['forums'][] = $v;
			}
		}
		foreach ($group as $fid => $v){
			if(isset($v['forums']) && !empty($v['forums'])){
				continue;
			}
			unset($group[$fid]);
		}
		return $group;
	}
}

?>
