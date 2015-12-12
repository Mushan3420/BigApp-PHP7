<?php
/**
 * ajaxapi入口
 **/
define("IN_MOBILE", 1);
define("IN_MOBILE_API", 1);
define("PLUGIN_PATH", dirname(__FILE__));
define("LIB_PATH", PLUGIN_PATH."/libs");
chdir("../../../");

require_once LIB_PATH."/env.php";

$modules = array (
    "login","profile","seccode","regist","resetpass","smscode","bind",
    "pcsmscode",
);

if(!in_array($_GET['module'], $modules)) {
    module_not_exists();
}
$module  = $_GET['module'];
$version = !empty($_GET['version']) ? intval($_GET['version']) : 1;
while ($version>=1) {
    $apifile = PLUGIN_PATH."/api/$version/$module.php";
    if(file_exists($apifile)) {
        require_once $apifile;
        exit(0);
    }
    --$version;    
}
module_not_exists();


function module_not_exists()
{
	header("Content-type: application/json");
    echo json_encode(array('error' => 'module_not_exists'));
    exit;
}


function real_escape_string($str)
{/*{{{*/
    $len = strlen($str);
    if ($len==0) return $str;
    $res = "";
    for ($i=0; $i<$len; ++$i) {
        $c = $str[$i];
        if ($c=="\r") $c = "\\r";
        if ($c=="\n") $c = "\\n";
        if ($c=="\x00") $c = "\\0";
        if ($c=="\x1a") $c = "\\Z";
        if ($c=="'" || $c=='"' || $c=='\\') $res.="\\";
        $res.= $c; 
    }
    return $res;
}/*}}}*/

function get_request_param($key, $dv=null, $field='request')
{/*{{{*/
	if ($field=='GET') {
		return isset($_GET[$key]) ? real_escape_string($_GET[$key]) : $dv;
	}
	else if ($field=='POST') {
		return isset($_POST[$key]) ? real_escape_string($_POST[$key]) : $dv;
	}
	else {
		return isset($_REQUEST[$key]) ? real_escape_string($_REQUEST[$key]) : $dv;
	}
}/*}}}*/

function get_id_arr($ids)
{/*{{{*/
    $arr = explode(",",$ids);
    $idarr = array();
    foreach ($arr as $str) {
        $id = intval($str);
        if ($id>0) $idarr[] = $id;
    }
    return array_unique($idarr);
}/*}}}*/

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
