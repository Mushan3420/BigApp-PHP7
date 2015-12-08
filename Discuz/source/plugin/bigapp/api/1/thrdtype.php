<?php
/**
 * @file thrdtype.php
 * @Brief get thread types
 * @author youzu
 * @version 1.0.0
 * @date 2015-08-06
 */
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
$_GET['mod'] = 'forumdisplay';
require './source/class/class_core.php';
require './source/function/function_forum.php';
$modarray = array('ajax','announcement','attachment','forumdisplay',
		'group','image','index','medal','misc','modcp','notice','post','redirect',
		'relatekw','relatethread','rss','topicadmin','trade','viewthread','tag','collection','guide'
		);
$modcachelist = array(
		'index'     => array('announcements', 'onlinelist', 'forumlinks',
			'heats', 'historyposts', 'onlinerecord', 'userstats', 'diytemplatenameforum'),
		'forumdisplay'  => array('smilies', 'announcements_forum', 'globalstick', 'forums',
			'onlinelist', 'forumstick', 'threadtable_info', 'threadtableids', 'stamps', 'diytemplatenameforum'),
		'viewthread'    => array('smilies', 'smileytypes', 'forums', 'usergroups',
			'stamps', 'bbcodes', 'smilies', 'custominfo', 'groupicon', 'stamps',
			'threadtableids', 'threadtable_info', 'posttable_info', 'diytemplatenameforum'),
		'redirect'  => array('threadtableids', 'threadtable_info', 'posttable_info'),
		'post'      => array('bbcodes_display', 'bbcodes', 'smileycodes', 'smilies', 'smileytypes',
			'domainwhitelist', 'albumcategory'),
		'space'     => array('fields_required', 'fields_optional', 'custominfo'),
		'group'     => array('grouptype', 'diytemplatenamegroup'),
		);

$mod = !in_array(C::app()->var['mod'], $modarray) ? 'index' : C::app()->var['mod'];
define('CURMODULE', $mod);
$cachelist = array();
if(isset($modcachelist[CURMODULE])) {
	$cachelist = $modcachelist[CURMODULE];

	$cachelist[] = 'plugin';
	$cachelist[] = 'pluginlanguage_system';
}
if(C::app()->var['mod'] == 'group') {
	$_G['basescript'] = 'group';
}
C::app()->cachelist = $cachelist;
C::app()->init();
loadforum();

$threadTypes = array();
if(!empty($_G['forum']['threadtypes']) || !empty($_GET['debug'])) {
	$threadTypes = $_G['forum']['threadtypes'];
	unset($threadTypes['types']);
	foreach ($_G['forum']['threadtypes']['types'] as $typeId => $typeValue){
		$typeValue = preg_replace('/<.*?>/', '', $typeValue);
		$threadTypes['types'][] = array('typeid' => $typeId, 'typename' => $typeValue);
	}
	unset($threadTypes['icons']);
	foreach ($_G['forum']['threadtypes']['icons'] as $typeId => $icon){
		$threadTypes['icons'][] = array('typeid' => $typeId, 'typeicon' => $icon);
	}
}
if(isset($threadTypes['moderators'])){
	unset($threadTypes['moderators']);
}
$ret = array(
		'error_code' => 0,
		'error_msg' => 'SUCC',
		'threadtypes' => $threadTypes,
		);
echo BIGAPPJSON::encode($ret);
?>
