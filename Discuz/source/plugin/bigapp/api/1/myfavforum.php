<?php
/**
* @file myfavforum.php
* @Brief 
* @author youzu
* @version 1
* @date 2015-04-03
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

class BigAppAPI
{
	public function common()
	{
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
	}

	public function output()
	{
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		if(isset($GLOBALS['list']) && is_array($GLOBALS['list'])){
			foreach ($GLOBALS['list'] as &$v){
				$v['icon'] = ApiUtils::getImgPath($v['icon']);
				loadcache(array('bigapp_favforum_'.$v['id'], 'plugin'));
        		$expire = 5;
        		if(!$_G['cache']['bigapp_favforum_'.$v['id']] || TIMESTAMP - $_G['cache']['bigapp_favforum_'.$v['id']]['expiration'] > $expire) {
					$sql = 'SELECT description, icon FROM ' . DB::table('forum_forumfield') . ' WHERE fid = ' . $v['id'];
					$query = DB::query($sql);
					$description = '';
					$icon = ApiUtils::getImgPath($v['icon']);
					while($fInfo = DB::fetch($query)) {
						$description = $fInfo['description'];
						$icon = ApiUtils::getDzRoot() . $_G['setting']['attachurl'] . 'common/' . $fInfo['icon'];
						break;
					}
					$sql = 'SELECT threads, posts, todayposts FROM ' . DB::table('forum_forum') . ' WHERE fid = ' . $v['id'];
					$query = DB::query($sql);
					$nums = array('threads' => 0, 'posts' => 0, 'todayposts' => 0, 'yesterdayposts' => 0);
					while($tmp = DB::fetch($query)) {
						$nums = $tmp;
						break;		
					}
					$fInfo = array_merge($fInfo, $nums);
					savecache('bigapp_favforum_'.$v['id'], array('variable' => $fInfo, 'expiration' => TIMESTAMP));
				}else{
					$fInfo = $_G['cache']['bigapp_favforum_' . $v['id']]['variable'];
					$description = $fInfo['description'];
					$icon = ApiUtils::getDzRoot() . $_G['setting']['attachurl'] . 'common/' . $fInfo['icon'];
				}
				$v['icon'] = $icon;
				$v['description'] = preg_replace('/<.*?>/', '', $description);
				$v['name'] = $v['title'];
				$v['threads'] = $fInfo['threads'];
				$v['posts'] = $fInfo['posts'];
				$v['todayposts'] = $fInfo['todayposts'];
				$v['yesterdayposts'] = isset($fInfo['yesterdayposts'])?$fInfo['yesterdayposts']:0;
				unset($v['title']);
			}
		}
		if(!isset($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] <= 0){
			$_GET['page'] = 1;
		}
		$start = $GLOBALS['perpage'] * ($_GET['page'] - 1);
		$end = count($GLOBALS['list']) + $start;
		loadcache('forum');
		if($end >= $GLOBALS['count']){
			$GLOBALS['need_more'] = 0;
		}else{
			$GLOBALS['need_more'] = 1;
		}
		$list = array_values($GLOBALS['list']);
		$newList = array();
		if( is_array($list) && !empty($list) ){
			foreach($list as $fav){
				if(!empty($fav['threads'])){
					$newList[] = $fav;
				}
			}
		}else{
			$newList = $list;
		}
		$variable = array(
			'list' => $newList,
			'perpage' => $GLOBALS['perpage'],
			'need_more' => $GLOBALS['need_more'],
			'count' => $GLOBALS['count'],
		);
		bigapp_core::result(bigapp_core::variable($variable));
	}

}

?>
