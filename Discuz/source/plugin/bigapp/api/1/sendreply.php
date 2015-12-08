<?php
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

class BigAppAPI{
	function common() {
		$_POST["mobiletype"] = 7;
	}
	
	function output() {
		global $_G;
		$variable = array(
		    'tid' => $_G['tid'],
		    'pid' => $GLOBALS['pid'],
		);
		if(isset($_G['setting']['bigapp_push_config'])  && is_string($_G['setting']['bigapp_push_config'])){
                        $_G['setting']['bigapp_push_config'] = unserialize($_G['setting']['bigapp_push_config']);  
                }
                if(!isset($_G['setting']['bigapp_push_config']['push_enabled'])){
                        $_G['setting']['bigapp_push_config']['push_enabled'] = 1;
                }
		if($_G['setting']['bigapp_push_config']['push_enabled']){
			$reppid = null;
			if(isset($_REQUEST['reppid']) && intval($_REQUEST['reppid']) > 0){
				$reppid = intval($_REQUEST['reppid']);
			}
			if(is_null($reppid)){
				$sql = 'SELECT first, pid, fid, tid, author, authorid, subject FROM ' . DB::table('forum_post') . ' WHERE tid = ' . $_G['tid'] . ' AND first = 1 OR pid = ' . $GLOBALS['pid'];
			}else{
				$sql = 'SELECT first, pid, fid, tid, author, authorid, subject FROM ' . DB::table('forum_post') . ' where pid = ' . $GLOBALS['pid'] . ' OR pid = ' . $reppid;
			}
			runlog('bigapp', $sql);
			$dbRet = array();
			$query = DB::query($sql);
			while($tmp = DB::fetch($query)){
				$dbRet[] = $tmp;
			}
			if(count($dbRet) === 2){
				$subject = null;
				$author = null;
				$pid = null;
				$fid = null;
				$tid = null;
				$authorid = null;
				$ownerid = null;
				foreach($dbRet as $value){
					if(1 == $value['first'] || !is_null($reppid) && $reppid == $value['pid']){
						$subject = $value['subject'];
						$ownerid = $value['authorid'];
						continue;
					}
					$author = $value['author'];
					$pid = $value['pid'];
					$fid = $value['fid'];
					$tid = $value['tid'];
					$authorid = $value['authorid'];
				}
				if(!is_null($subject) && !is_null($author) && $ownerid != $authorid){
					if(function_exists('iconv')){
						$subject = iconv(CHARSET, 'UTF-8//ignore', $subject);
						$author = iconv(CHARSET, 'UTF-8//ignore', $author);
					}else{
						$subject = mb_convert_encoding($subject, 'UTF-8', CHARSET);
						$author = mb_convert_encoding($author, 'UTF-8', CHARSET);
					}
					$title = '您有新的回帖';
					if(is_null($reppid)){
						$content = "用户 ${author} 回复了您的主题 ${subject}，详情点击查看";
					}else{
						$content = "用户 ${author} 回复了您的回帖，详情点击查看";
					}
					$extra = array('subject' => '__DONT_DICONV_TO_UTF8___' . $subject, 'author' => '__DONT_DICONV_TO_UTF8___' . $author, 'pid' => $pid, 'fid' => $fid, 'tid' => $tid, 'authorid' => $authorid);
					require_once (dirname(dirname(dirname(__FILE__))) . '/libs/pushmsg.inc.php');
					$ret = PushMsg::sendMessage($ownerid, $title, $content, 2, $extra, 1);
					$result = 'fail';
					if(true === $ret){
						$result = 'succ';
					}
					runlog('bigapp', "[mobile]try to send reply message [ subject: $subject, author: $author, ownerid: $ownerid, result: $result ].");
				}
			}
		}
		bigapp_core::result(bigapp_core::variable($variable));
	}
}

?>
