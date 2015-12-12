<?php
if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}

require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

$result = array (
    "uid" => $_G["uid"],
	"username" => iconv(CHARSET,"UTF-8//ignore",$_G["username"]),
	"groupid" => $_G["groupid"],
    "avatar" => avatar($_G["uid"], 'big', true),
    "phone" => "",
);

$phone = C::t("#login_mobile#mobile_login_connection")->getPhone($result["username"]);
if ($phone !== false) $result["phone"] = $phone;

DzEnv::result($result);


// vim600: sw=4 ts=4 fdm=marker syn=php
?>
