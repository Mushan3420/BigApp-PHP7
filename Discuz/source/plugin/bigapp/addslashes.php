<?php
/**
 * Created by PhpStorm.
 * User: hepeichun
 * Date: 2016/5/18
 * Time: 23:02
 */
 /*
foreach($_GET as $k=>$v){
    $_GET[$k] = addslashes($v);
}
foreach($_POST as $k=>$v){
    $_POST[$k] =addslashes($v);
}
 */
error_reporting(0);
if(substr_count($_GET['url'],'../')){
    header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden");
    exit();
}
if(!substr_count($_GET['url'],$_SERVER['SERVER_NAME'])){
    if(substr_count($_GET['url'],'http://') >= 2){
        header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden");
        exit();
    }
}