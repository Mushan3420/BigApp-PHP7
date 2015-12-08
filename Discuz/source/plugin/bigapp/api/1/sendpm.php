<?php
/**
* @file sendpm.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

class BigAppAPI {
	function common() {
		$_POST = $_GET;
		
		//$regex = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';
		$regex1 = '/([^>=\]"\'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|qqdl|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\x{4e00}-\x{9fa5}]+\.([\.a-zA-Z0-9]+|\x{4E2D}\x{56FD}|\x{7F51}\x{7EDC}|\x{516C}\x{53F8})((\?|\/|:)+[\w\.\/=\?%\-&;~`@\':+!#]*)*)/u';
		$regex2 = '/([^>=\]"\'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\x{4e00}-\x{9fa5}]+\.([\.a-zA-Z0-9]+|\x{4E2D}\x{56FD}|\x{7F51}\x{7EDC}|\x{516C}\x{53F8})((\?|\/|:)+[\w\.\/=\?%\-&;~`@\':+!#]*)*)/u';
		
		$search = array ($regex1, $regex2);

		$replace = array ("\\1[url]\\2[/url]", "\\1[url]\\2[/url]");

		$content = $_POST['message'];
		
		global $_G;
		$charset = strtoupper($_G['charset']);
		if(is_string($content) && strtolower($charset) != 'utf-8' && strtolower($charset) != 'utf8'){
			if(function_exists('iconv')){
				//gbk强制转utf-8,为了能够进行汉字正则匹配
				$content = @iconv('GBK', 'UTF-8//ignore', $content);
			}else if(function_exists('mb_convert_encoding')){
				$content = @mb_convert_encoding($content, 'UTF-8', 'GBK');
			}

			$content = preg_replace ($search, $replace, $content);

			if(function_exists('iconv')){
				$content = @iconv('UTF-8', 'GBK//ignore', $content);
			}else if(function_exists('mb_convert_encoding')){
				$content = @mb_convert_encoding($content, 'GBK', 'UTF-8');
			}

		} else {
			$content = preg_replace ($search, $replace, $content);
		}

		$_POST['message'] = $content;
		
		$search = array();
		$replace = array();
		
		loadcache(array('smilies', 'smileytypes'));
		$smiley_infos = array();
		foreach ($_G['cache']['smilies']['replacearray'] as $id => $img) {
			$search[] = $_G['cache']['smilies']['searcharray'][$id];
			$replace[] = '{'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$id]]['directory'] . '/' . $img .'}';
		}
		
		$_POST['show_message'] = preg_replace($search, $replace, $content);
	}

	function output() {
		global $_G;

		$variable = array(
			'pmid' => $GLOBALS['return'],
			'message' => $_POST['show_message'],
		);
		
		if(isset($_G['setting']['bigapp_push_config'])  && is_string($_G['setting']['bigapp_push_config'])){
			$_G['setting']['bigapp_push_config'] = unserialize($_G['setting']['bigapp_push_config']);
		}
		if(!isset($_G['setting']['bigapp_push_config']['push_enabled'])){
			$_G['setting']['bigapp_push_config']['push_enabled'] = 1;
		}		
		if($_G['setting']['bigapp_push_config']['push_enabled']){
			if($GLOBALS['return'] > 0){
				$sql = 'SELECT uid, username FROM ' . DB::table('common_member') . ' WHERE uid IN (' . $_G['uid'] . ', ' . $_REQUEST['touid'] . ')';
                $query = DB::query($sql);
                $uid = null;
                $touid = null;
                $user = null;
                $touser = null;
                while($tmp = DB::fetch($query)){
                    if($tmp['uid'] === $_G['uid']){
                        $uid = $_G['uid'];
                        $user = $tmp['username'];
                        continue;
                    }
                    $touid = $tmp['uid'];
                    $touser = $tmp['username'];
                }
                if(!is_null($uid) && !is_null($touid) && !is_null($user) && !is_null($touser) && $uid != $touid){
                    if(function_exists('iconv')){
                        $user = iconv(CHARSET, 'UTF-8//ignore', $user);
                        $touser = iconv(CHARSET, 'UTF-8//ignore', $touser);
                    }else{
                        $user = mb_convert_encoding($user, 'UTF-8', CHARSET);
                        $touser = mb_convert_encoding($touser, 'UTF-8', CHARSET);
                    }
                    $title = '您有新的消息，请注意查收';
                    $content = "用户 ${user} 向您发来新消息，详情点击查看";
                    $extra = array('user' => '__DONT_DICONV_TO_UTF8___' . $user, 'touser' => '__DONT_DICONV_TO_UTF8___' . $touser, 'uid' => $uid, 'touid' => $touid, 'pmid' => $GLOBALS['return'], 'message' => '__DONT_DICONV_TO_UTF8___' . $_REQUEST['message']);
                    require_once (dirname(dirname(dirname(__FILE__))) . '/libs/pushmsg.inc.php');
                    $ret = PushMsg::sendMessage($touid, $title, $content, 1, $extra, 1);  //仅仅推送消息
                    $result = 'fail';
                    if(true === $ret){
                        $result = 'succ';
                    }
                    runlog('bigapp', "[mobile]try to send message notice [ uid: $uid, touid: $touid, user: $user, touser: $touser, result: $result ].");
                }						
			}
		}
		bigapp_core::result(bigapp_core::variable($variable));
	}

}

?>
