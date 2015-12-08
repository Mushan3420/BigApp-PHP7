<?php
/**
 * 图片处理驱动类，可配置图片处理库
 * 目前支持GD库和imagick
 * @author zhengtao
 * @param 2015 05 07
 */
$allow_pic_array = array(
	"20_20" =>array(20,20),
	"30_30" =>array(30,30),
	"40_40" =>array(40,40),
	"60_60" =>array(60,60),
	"80_80" =>array(80,80),
	"88_88" =>array(88,88),
	"96_96" =>array(96,96),
	"100_100" =>array(100,100),
	"200_200" =>array(200,200),
	"300_300" =>array(300,300),
	"400_400" =>array(400,400),
	"500_500" =>array(500,500),
	"600_600" =>array(600,600),
	"700_700" =>array(700,700),
	"750_342" =>array(750,342),
	"800_800" =>array(800,800),
	"900_900" =>array(900,900),
	"1000_1000" =>array(1000,1000),
	"1024_1024" =>array(1024,1024),
	"1242_2208" =>array(1242,2208),
);
$hashlevel = 3;//保存的图片的层级
$thisdir = dirname(__FILE__);
$default_pic = $thisdir."/Image/default.png";//默认图片的地址
$pic_url = isset($_GET['url'])?$_GET['url']:"";
$raw_pic_url = $pic_url;
$pic_size = isset($_GET['size'])?$_GET['size']:"";
$log_file = $thisdir.'/'.date("Y-m-d").".log";

$dzUp = new discuz_upload();
$dir2 = $dzUp->get_target_dir('common', strlen($pic_url));
$url_pathinfo = pathinfo($pic_url);
if(empty($url_pathinfo['extension'])){
	$url_pathinfo['extension'] = 'png';
}

//判断是否是非法后缀，非法后缀则直接跳转
if(!isValidExt($pic_url)){
	runlog('bigapp', 'invalid postfix, reject to optmize, just redirect [ url: ' . $pic_url . ' ]');
	redirect($pic_url);
}

$dir = getglobal('setting/attachdir') . './common/' . $dir2 . 'bigapp/'; //图片存储的目录

if($pic_url!=""&&array_key_exists($pic_size,$allow_pic_array))
{
	$size = $pic_size;
	$url_md5 = md5($pic_url);
	for($i=0;$i<$hashlevel;$i++) {
		$dir   .=  $url_md5{$i}.'/';
	}
	if($dir!=""&&!file_exists($dir))
	{
		mkdir($dir,0755, true);
	}
	$url_pathinfo = pathinfo($pic_url);
	if(!isset($url_pathinfo['extension']) || empty($url_pathinfo['extension'])){
		$url_pathinfo['extension'] = "png";
	}
	$new_pic_file = $dir.$url_pathinfo['filename']."_".$pic_size;
	$old_pic_file = $dir.$url_pathinfo['filename'];
	if($url_pathinfo['extension']!="")
	{
		$new_pic_file = $new_pic_file.".".$url_pathinfo['extension'];
		$old_pic_file = $old_pic_file.".".$url_pathinfo['extension'];		
	}
	if(file_exists($new_pic_file))//生成的图片存在
	{
		return_pic($new_pic_file);
	}
	else
	{
		if(strpos($pic_url, $_G['siteurl']) !== false){
			$pic_url = @substr($pic_url, strlen($_G['siteurl']));
			$tmp = @getcwd() . '/' . $pic_url;
			if(is_file($tmp) && is_readable($tmp)){
				$pic_url = $tmp;
			}
		}
		$pic_content = @file_get_contents($pic_url);
		if($pic_content!="")
		{
			@file_put_contents($old_pic_file,$pic_content,FILE_APPEND );//保存网上的图片
		}
		if(file_exists($old_pic_file))
		{
			require_once($thisdir."/Image/Image.class.php");
			$gd_info  = 0;
			if(function_exists("gd_info"))
			{
				$gd_info = 1;
			}
			elseif(class_exists("Imagick", false))
			{
				$gd_info = 2;				
			}
			if($gd_info === 0){
				redir($raw_pic_url);
			}
			$Image = new Image($gd_info);
			if(@$Image->get_handle())
			{
				@$Image->open($old_pic_file);				
				@$Image->thumb($allow_pic_array[$size][0],$allow_pic_array[$size][1])->save($new_pic_file);//缩放图片
				if(file_exists($new_pic_file))
				{
					return_pic($new_pic_file);
				}
				else
				{
					redir($raw_pic_url);
				}
			}
			else
			{				
				redir($raw_pic_url);
			}
		}
		else
		{
			redir($raw_pic_url);
		}
	}
}
else
{
	redir($raw_pic_url);
}

function isValidExt($url)
{
	$url_pathinfo = pathinfo($url);
	if(empty($url_pathinfo['extension'])){
    	$url_pathinfo['extension'] = 'png';
	}
	$url_pathinfo['extension'] = strtolower($url_pathinfo['extension']);
	if(!in_array($url_pathinfo['extension'], array('jpg', 'jpeg', 'png'))){
		return false;
	}
	return true;
}

function redirect($url)
{
	header('HTTP/1.1 301 Moved Permanently');//发出301头部
	header('Location:'.$url);
	die(0);
}

function redir($url)
{
	$content = @file_get_contents($url);
	if(false === $content || 0 === strlen($content)){
		runlog('bigapp', 'cannot get file content from url [ ' . $url .' ]');
		redirect($url);
	}
	$length = strlen($content);
	runlog('bigapp', "read remote content succ, echo and return now [ url: $url, length: $length ]");
	if(function_exists('ob_end_clean')){
		ob_end_clean();
		global $_G;
		if(isset($_G['gzipcompress']) && $_G['gzipcompress']){
			if(function_exists('ob_gzhandler')){
				ob_start('ob_gzhandler');
			}else{
				ob_start();
			}
		}
	} 
	$expire=3600;  
	header("Pragma: public");
    header("Cache-control: max-age=".$expire);
    header("Expires: " . gmdate("D, d M Y H:i:s",time()+$expire) . "GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . "GMT");
    header("Content-type: image/png");
    header("Content-Transfer-Encoding: binary" );
    echo $content;
	die(0);		
}
//显示图片返回给客户端
function return_pic(&$real_file_path)
{	
	if(function_exists('ob_end_clean')){
		ob_end_clean();
		global $_G;
		if(isset($_G['gzipcompress']) && $_G['gzipcompress']){
			if(function_exists('ob_gzhandler')){
				ob_start('ob_gzhandler');
			}else{
				ob_start();
			}
		}
	}
	$expire = 3600;
	header("Pragma: public");
	header("Cache-control: max-age=".$expire);
	header("Expires: " . gmdate("D, d M Y H:i:s",time()+$expire) . "GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . "GMT");
	header("Content-type: image/png");
	header("Content-Transfer-Encoding: binary" );
	readfile($real_file_path);
	die(0);
}
