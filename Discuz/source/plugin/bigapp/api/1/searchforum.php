<?php
/**
* @file searchforum.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

if(!isset($_GET['keyword']) || empty($_GET['keyword'])){
	bigapp_core::result(array('error' => 'param_error'));  	
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
		loadcache('bigapp_forumnav');
		$expire = 5;
		if(!isset($_G['cache']['bigapp_forumnav']) || empty($_G['cache']['bigapp_forumnav']) || TIMESTAMP - $_G['cache']['bigapp_forumnav']['expiration'] > $expire){
			//need update all forums from database
			$sql = "SELECT f.fid, f.type, f.name, f.fup, f.status, f.threads, f.posts, f.todayposts, " .
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
							'postperm', 'status', 'threadsorts', 'threadtypes', 'icon', 'description', 'threads', 
							'posts', 'todayposts', 'moderators'));
				}
			}
			//add result to syscache 
			savecache('bigapp_forumnav', array('variable' => $forums, 'expiration' => TIMESTAMP));
		}else{
			$forums = $_G['cache']['bigapp_forumnav']['variable'];
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
		foreach ($retData as &$value){
			BigAppAPI::_textDescription($value);
		}	
		unset($value);
		$variable['forums'] = array_values(BigAppAPI::_sortResult($retData));
		$variable['forum_list'] = array();
		BigAppAPI::_doSearch($variable, 'forums', $variable['forum_list']);
		unset($variable['forums']);
		bigapp_core::result(bigapp_core::variable($variable));
	}

	function _doSearch($variable, $key, &$ret)
	{
		if(function_exists('iconv')){
			$keyWord = iconv('UTF-8', BIGAPP_CHARSET . '//ignore', $_GET['keyword']);
		}else{
			$keyWord = mb_convert_encoding($_GET['keyword'], BIGAPP_CHARSET, 'UTF-8');
		}
		foreach ((array)$variable[$key] as $item)	{
			if($item['type'] !== 'group' && preg_match('/' . $keyWord . '/', $item['name']))	{
				$tmp = $item;
				if(isset($tmp['forums'])) unset($tmp['forums']);
				if(isset($tmp['subs'])) unset($tmp['subs']);
				$ret[] = $tmp;
			}
			isset($item['forums']) && BigAppAPI::_doSearch($item, 'forums', $ret);
			isset($item['subs']) && BigAppAPI::_doSearch($item, 'subs', $ret);
		}
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
		$search = array ("'<script[^>]*?>.*?</script>'si",
                "'<img.*src=\"(.*)\" .*>'iU",
                "'<[\/\!]*?[^<>]*?>'si",     
                "'&(quot|#34);'i",
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

	protected function _sortResult($forums)
	{
		$group = array();
		$forum = array();
		$sub = array();
		foreach ((array)$forums as $v){
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
			}
		}
		foreach ($forum as $v){
			if(isset($group[$v['fup']])){
				$group[$v['fup']]['forums'][] = $v;
			}
		}
		return $group;
	}
}

?>
