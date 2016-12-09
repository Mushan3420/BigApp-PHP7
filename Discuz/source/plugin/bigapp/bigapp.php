<?php
/**
* @file bigapp.php
* @Brief enterace for bigapp plugin
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

define('IN_MOBILE_API', 1);
define('IN_MOBILE', 1);
define ('IN_BIGAPP', 1);
chdir('../../../');


if(!isset($_config)){
	@require_once 'config/config_global.php';
}
define ('BIGAPP_CHARSET', $_config['output']['charset']);
require_once dirname(__FILE__) . '/bigapp.class.php';

$modules = array('forumnav', 'forumnav2', 'editpost', 'deletepm', 'deletepl', 'mythread2', 'delfav', 'login3body', 'newuser', 
		'captcha', 'checknewpm', 'myhome','myportal','secquestion', 'checkpost', 'forumupload', 'postsupport', 'search', 'searchuser', 
		'searchforum', 'threadrecommend2', 'newfriend', 'findfriend', 'addfriend', 'auditfriend', 'removefriend','plugcfg', 'report', 
		'platform_login', 'thrdtype', 'smilies', 'checkin', 'indexthread','favarticle','myfavarticle', 'indexcfg','contentthread', 'getaksk','modpass','viewinfo', 'activityclient', 'activityapplylist', 'viewratings','rate','ratepost','comment','commentmore','commentpost','commentnotice', 'removepost', 'removethread');

$defaultversions = array(
	'forumnav' => 4,
	'editpost' => 4,
	'forumnav2' => 4,
	'deletepm' => 4,
	'deletepl' => 4,
	'mythread2' => 4,
	'delfav' => 4,
	'login3body' => 4,
	'newuser' => 4,
	'captcha' => 4,
	'checknewpm' => 4,
	'checkpost' => 4,
	'forumupload' => 4,
	'postsupport' => 4,
	'search' => 4,
	'searchuser' => 4,
	'searchforum' => 4,
	'threadrecommend2' => 4, 
	'plugcfg' => 4, 
	'thrdtype' => 4, 
	'getaksk' => 4,
    'getarticle' => 4,
    'activityclient' => 4,
	'activityapplylist' => 4,
    'viewratings' => 4,
    'rate' => 4,
    'ratepost' => 4,
	'removepost' => 4,
	'removethread' => 4,
	'newfriend' => 4,
	'findfriend' => 4,
	'addfriend' => 4,
	'auditfriend' => 4,
    'removefriend' => 4,
    'report' => 4,
    'platform_login' => 4,
    'smilies' => 4,
    'checkin' => 4,
	'indexthread' => 4,
	'myhome' => 4,
	'testtids' => 4,
    'modpass' => 4,
);

if(!in_array($_GET['module'], $modules)) {
	bigapp_core::result(array('error' => 'module_not_exists'));
}

$_GET['iyzversion'] = !empty($_GET['iyzversion']) ? intval($_GET['iyzversion']) : 1;
$_GET['iyzversion'] = $_GET['iyzversion'] > BIGAPP_PLUGIN_VERSION ? BIGAPP_PLUGIN_VERSION : $_GET['iyzversion'];

if(empty($_GET['module']) || empty($_GET['iyzversion']) || !preg_match('/^[\w\.]+$/', $_GET['module']) || !preg_match('/^[\d\.]+$/', $_GET['iyzversion'])) {
	bigapp_core::result(array('error' => 'param_error'));
}

if($_GET['module'] == 'extends') {
	require_once 'source/plugin/mobile/mobile_extends.php';
	return;
}

if(!empty($_GET['_auth'])) {
	unset($_GET['formhash'], $_POST['formhash']);
}
$apifile = dirname(__FILE__) . '/api/'.$_GET['iyzversion'].'/'.$_GET['module'].'.php';

if(file_exists($apifile)) {
	require_once $apifile;
} else {
	if($_GET['iyzversion'] > 1) {
		for($i = $_GET['iyzversion']; $i >= 1; $i--) {
			$apifile = dirname(__FILE__) . '/api/'.$i.'/'.$_GET['module'].'.php';
			if(file_exists($apifile)) {
				$_GET['iyzversion'] = $i;
				require_once $apifile;
				break;
			} elseif($i==1 && !file_exists($apifile)) {
				bigapp_core::result(array('error' => 'module_not_exists'));
			}
		}
	} else {
		bigapp_core::result(array('error' => 'module_not_exists'));
	}
}

?>
