<?php
/**
* @file comment.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-10-20
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'misc';
$_GET['action'] = 'comment';

include_once 'forum.php';

class BigAppAPI {

	public function common() 
    {
		global $_G;
	}

	public function output() 
    {
		global $_G;
		$status = "1";
		if(!$_G['setting']['commentnumber']) {
			$status = "0";
		}
		$thread = C::t('forum_thread')->fetch($_GET['tid']);
		if($thread['closed'] && !$_G['forum']['ismoderator']) {
			$status = "0";
		}
		$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['pid']);
		
		if($_G['group']['allowcommentitem'] && !empty($_G['uid']) && $post['authorid'] != $_G['uid']) {
			
			$thread = C::t('forum_thread')->fetch($post['tid']);
			$itemi = $thread['special'];
			
			if($thread['special'] > 0) {
				if($thread['special'] == 2){
					$thread['special'] = $post['first'] || C::t('forum_trade')->check_goods($post['pid']) ? 2 : 0;
				} elseif($thread['special'] == 127) {
					$thread['special'] = $_GET['special'];
				} else {
					$thread['special'] = $post['first'] ? $thread['special'] : 0;
				}
			}
			//$_G['setting']['commentitem'] = $_G['setting']['commentitem'][$thread['special']];
			if($thread['special'] == 0) {
				loadcache('forums');
				if($_G['cache']['forums'][$post['fid']]['commentitem']) {
					$_G['setting']['commentitem'] = $_G['cache']['forums'][$post['fid']]['commentitem'];
				}
			}
			if($_G['setting']['commentitem'] && !C::t('forum_postcomment')->count_by_pid($_GET['pid'], $_G['uid'], 1)) {
				$commentitem = explode("\n", $_G['setting']['commentitem']);
			}
			
		}
		$variable = array();
		$variable['status'] = $status;
		if($status){
			if(is_array($commentitem)){
				foreach($commentitem as $item){
					$item = trim($item);
					$variable['comment_fields'][] = array("fieldid"=>"commentitem[$item]","title"=>$item);
				}
			}
			$variable['comment_fields'][] = array("fieldid"=>"message","title"=>"发表观点");
		}
		bigapp_core::result(bigapp_core::variable($variable));
	}

}
?>
