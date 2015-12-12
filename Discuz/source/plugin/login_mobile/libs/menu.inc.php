<?php

showtips(lang('plugin/login_mobile','browser_tips'),'',true,lang('plugin/login_mobile','browser_tips_title'));

$api = "var ajaxapi='".DzEnv::getSiteUrl()."/source/plugin/login_mobile/index.php';";
@file_put_contents(dirname(__FILE__)."/../fe/api.js", $api);

?>
