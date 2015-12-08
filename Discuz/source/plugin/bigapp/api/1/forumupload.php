<?php
/**
* @file forumupload.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

define('APPTYPEID', 100);
define('CURSCRIPT', 'misc');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init_cron = false;
$discuz->init_session = false;
$discuz->init();

$precheckStr = null;
if(!isset($_G['setting']['plugins']['available']) || !in_array('bigapp', $_G['setting']['plugins']['available'])){
	$precheckStr = '论坛移动端访问已关闭';
}else if(isset($_G['group']['allowpostimage']) && !$_G['group']['allowpostimage']){
	$precheckStr = '您所在用户组不允许上传图片';
}
if(!is_null($precheckStr)){
	$variable = array(
		'code' => 100, 
		'message' => '__DONT_DICONV_TO_UTF8___' . $precheckStr, 
		'ret' => array(
			'aId' => -1, 
			'relative_url' => '', 
			'abs_url' => '', 
			'image' => -1,
		),
	);
	bigapp_core::result(bigapp_core::variable($variable));
}
$_G['uid'] = intval($_POST['uid']);

if((empty($_G['uid']) && $_GET['operation'] != 'upload') || $_POST['hash'] != md5(substr(md5($_G['config']['security']['authkey']), 8).$_G['uid'])) {
	$variable = array(
                'code' => 100,
                'message' => '__DONT_DICONV_TO_UTF8___' . 'hash校验失败',
                'ret' => array(
                        'aId' => -1,
                        'relative_url' => '',
                        'abs_url' => '',
                        'image' => -1,
                ),
        );
	bigapp_core::result(bigapp_core::variable($variable));
} else {
	if($_G['uid']) {
		$_G['member'] = getuserbyuid($_G['uid']);
	}
	$_G['groupid'] = $_G['member']['groupid'];
	loadcache('usergroup_'.$_G['member']['groupid']);
	$_G['group'] = $_G['cache']['usergroup_'.$_G['member']['groupid']];
}

$_FILES['Filedata']['name'] = diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8');
$_FILES['Filedata']['type'] = $_GET['filetype'];

$forumattachextensions = '';
$fid = intval($_GET['fid']);
if($fid) {
	$forum = $fid != $_G['fid'] ? C::t('forum_forum')->fetch_info_by_fid($fid) : $_G['forum'];
	if($forum['status'] == 3 && $forum['level']) {
		$levelinfo = C::t('forum_grouplevel')->fetch($forum['level']);
		if($postpolicy = $levelinfo['postpolicy']) {
			$postpolicy = dunserialize($postpolicy);
			$forumattachextensions = $postpolicy['attachextensions'];
		}
	} else {
		$forumattachextensions = $forum['attachextensions'];
	}
	if($forumattachextensions) {
		$_G['group']['attachextensions'] = $forumattachextensions;
	}
}


class forum_upload_bigapp extends forum_upload {

	function uploadmsg($statusid) {
		global $_G;
		$errorMap = array(
			0 => '上传成功',
			10 => '非法提交',
			2 => '上传失败',
			6 => '附件个数超限制',
			1 => '非法附件后缀',
			2 => '上传失败',
			3 => '附件超最大限制',
			4 => '附件超最大限制',
			5 => '附件超最大限制',
			11 => '今日附件总大小超限制',
			8 => '保存图片失败',
			9 => '保存附件失败',
			7 => '文件格式不一致',
		);
		$msg = '附件提交失败';
		if(isset($errorMap[$statusid])){
			$msg = $errorMap[$statusid];	
		}
		if(function_exists('iconv')){
			$msg = iconv('UTF-8', CHARSET . '//ignore', $msg);
		}else{
			$msg = mb_convert_encoding($msg, CHARSET, 'UTF-8');
		}
		$variable = array(
						'code' => $statusid, 
						'message' => $msg, 
						'ret' => array(
									'aId' => $this->aid, 
									'relative_url' => $this->attach['attachment'], 
									'abs_url' => ApiUtils::getDzRoot() . $_G['setting']['attachurl'] . 'forum/' . $this->attach['attachment'], 
									'image' => $this->attach['isimage'] ? -1 : 2,
								),
					);
		bigapp_core::result(bigapp_core::variable($variable));
	}

}

$upload = new forum_upload_bigapp();

?>
