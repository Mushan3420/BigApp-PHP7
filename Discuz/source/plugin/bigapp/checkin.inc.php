<?php
/**
* @file checkin.inc.php
* @Brief variable setting page for admin center
* @author youzu
* @version 1.0.0
* @date 2015-08-10
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
require_once dirname(__FILE__) . '/libs/menu.inc.php';
require_once dirname(__FILE__) . '/bigappjson.class.php';

if(submitcheck('checkin')){
	$enabled = 0;
	
	if(isset($_REQUEST['setting_enable']) && '0' == trim($_REQUEST['setting_enable'])) {
		$enabled = 1;
	}

    /////////////////////////////////////////////
    if (!isset($_REQUEST['setting_credit']) || $_REQUEST['setting_credit']=='0') {
        showmessage(lang('plugin/bigapp', 'checkin_credit_wrong'));
    }
    $credit = $_REQUEST['setting_credit'];
    /////////////////////////////////////////////

	
	if(isset($_REQUEST['setting_credit_plus']) && checkParam(trim($_REQUEST['setting_credit_plus']))) {
		$credit_plus = trim($_REQUEST['setting_credit_plus']);
	} else {
		showmessage(lang('plugin/bigapp', 'wrong_credit_msg'));
	}
	
	if(isset($_REQUEST['setting_bonus_day']) && checkParam(trim($_REQUEST['setting_bonus_day']))) {
		$bonus_day = trim($_REQUEST['setting_bonus_day']);
	} else {
		showmessage(lang('plugin/bigapp', 'wrong_credit_msg'));
	}
	
	if(isset($_REQUEST['setting_bonus_plus']) && checkParam(trim($_REQUEST['setting_bonus_plus']))) {
		$bonus_plus = trim($_REQUEST['setting_bonus_plus']);
	} else {
		showmessage(lang('plugin/bigapp', 'wrong_credit_msg'));
	}
	
	$setting = array('enabled' => $enabled, 'credit_plus' => $credit_plus, 'bonus_day' => $bonus_day, 'bonus_plus' => $bonus_plus);
    ////////////////////////////////////////
    // add by mawentao
    $setting["credit"] = $credit;
    ////////////////////////////////////////
	$settings = array('bigapp_settings_checkin' => $setting);
	C::t('common_setting')->update_batch($settings);
	
	//update cache
	$_G['setting']['bigapp_settings_checkin']['enabled'] = $enabled;
	$_G['setting']['bigapp_settings_checkin']['credit_plus'] = $credit_plus;
	$_G['setting']['bigapp_settings_checkin']['bonus_day'] = $bonus_day;
	$_G['setting']['bigapp_settings_checkin']['bonus_plus'] = $bonus_plus;
    $_G['setting']['bigapp_settings_checkin']['credit'] = $credit;
	
	updatecache('setting');
	
	runlog('bigapp', 'save checkin settings variable succ [ settings: ' . BIGAPPJSON::encode($settings) . ' ]');
	cpmsg('plugins_edit_succeed', 'action=plugins&operation=config&do=' . $pluginid . '&identifier=bigapp&pmod=checkin', 'succeed');
	die(0);
}

function checkParam($param) {
	
	if(!is_numeric($param))
		return false;
   if(is_numeric($param) && is_int($param + 0)) { //整数
		if(intval($param) <= 0 || intval($param) > 100) {
			return false;
	    }
   } else {
	   //小数，其他非整数数字
	   return false;
   }
   
	return true;
}

if(isset($_G['setting']['bigapp_settings_checkin'])){
	$_G['setting']['bigapp_settings_checkin'] = unserialize($_G['setting']['bigapp_settings_checkin']);
}
if(!isset($_G['setting']['bigapp_settings_checkin']['enabled'])){
    $_G['setting']['bigapp_settings_checkin']['enabled'] = 0;
}
if(!isset($_G['setting']['bigapp_settings_checkin']['credit_plus'])){
    $_G['setting']['bigapp_settings_checkin']['credit_plus'] = 10;
}
if(!isset($_G['setting']['bigapp_settings_checkin']['bonus_day'])){
    $_G['setting']['bigapp_settings_checkin']['bonus_day'] = 5;
}
if(!isset($_G['setting']['bigapp_settings_checkin']['bonus_plus'])){
    $_G['setting']['bigapp_settings_checkin']['bonus_plus'] = 10;
}
$checked = '';
//showmessage($_G['setting']['bigapp_settings_checkin']['enabled']);
if($_G['setting']['bigapp_settings_checkin']['enabled'] == '1'){
	$checked = 'checked';
}

showformheader('plugins&operation=config&do=' . $pluginid . '&identifier=bigapp&pmod=checkin', '', 'checkin');
showtableheader(lang('plugin/bigapp', 'checkin_config'));

echo '<tr class="noborder" onmouseover="setfaq(this, \'faqd41d\')">';
echo '<td class="vtop rowform">';
echo '<ul class="nofloat"><li class="checked"><input class="checkbox" type="checkbox" name="setting_enable" value="0"' . $checked . '>' . lang('plugin/bigapp', 'checkin_enabled') . '</li></ul></td><td class="vtop tips2" s="1">' . lang('plugin/bigapp', 'checkin_enabled_comment') . '</td></tr>';

//////////////////////////////////////////////////////////
// add by mawentao
//print_r($_G['setting']['extcredits']);
$credit = 0;
if (isset($_G['setting']['bigapp_settings_checkin']['credit'])) {
    $credit = $_G['setting']['bigapp_settings_checkin']['credit'];
}
echo '<tr class="noborder"><td>'.lang('plugin/bigapp', 'checkin_credit').'&nbsp;&nbsp;'.
     '<select id="extcredits-sel" name="setting_credit">';
$i = 1;
echo '<option value="0">'.lang('plugin/bigapp', 'checkin_credit_choose').'</option>';
foreach ($_G['setting']['extcredits'] as &$ec) {
    $selected = ($credit === "extcredits$i") ? "selected" : "";
    echo '<option value="extcredits'.$i.'" '.$selected.'>'.$ec["title"].'</option>';
    ++$i;
}
echo '</select></td><td class="vtop tips2" s="1">'.lang('plugin/bigapp', 'checkin_credit_comment').'</td></tr>';
//////////////////////////////////////////////////////////

echo '<tr class="noborder"><td><p>' . lang('plugin/bigapp', 'checkin_once') . '&nbsp;&nbsp;<input type="text" name="setting_credit_plus" value="' . $_G['setting']['bigapp_settings_checkin']['credit_plus'] . '" /></p></td></tr>';
echo '<tr class="noborder"><td width="400px"><p>' . lang('plugin/bigapp', 'checkin_many') . '&nbsp;&nbsp;<select name="setting_bonus_day"><option value="' . $_G['setting']['bigapp_settings_checkin']['bonus_day'] . '">' . $_G['setting']['bigapp_settings_checkin']['bonus_day'] . '</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option></select>&nbsp;&nbsp;'. lang('plugin/bigapp', 'checkin_extra') .'&nbsp;&nbsp;<input type="text" name="setting_bonus_plus" value="' . $_G['setting']['bigapp_settings_checkin']['bonus_plus'] . '"/></p></td></tr>';


showsubmit('checkin', lang('plugin/bigapp', 'submit'));
showtablefooter();
showformfooter();
runlog('bigapp', 'show variable setting page succ');
?>
