<?php
/**
* @file uploadavatar.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

class BigAppAPI 
{

	public static $tmpavatar;
	public static $tmpavatarbig;
	public static $tmpavatarmiddle;
	public static $tmpavatarsmall;

	public static function common() 
	{
		global $_G;
		$avatarpath = $_G['setting']['attachdir'];
		$filetype = null;
		$imgtype = array(1 => '.gif', 2 => '.jpg', 3 => '.png');
		foreach ($imgtype as $_filetype){
			if(file_exists($avatarpath.'./temp/upload'.$_G['uid'].$_filetype)){
				$filetype = $_filetype;
				break;
			}
		}
		if(is_null($filetype)){
			self::error('api_uploadavatar_unavailable_pic');
		}
		$tmpavatar = $avatarpath.'./temp/upload'.$_G['uid'].$filetype;
		$tmpavatarbig = './temp/upload'.$_G['uid'].'big'.$filetype;
		$tmpavatarmiddle = './temp/upload'.$_G['uid'].'middle'.$filetype;
		$tmpavatarsmall = './temp/upload'.$_G['uid'].'small'.$filetype;
		self::$tmpavatar = $tmpavatar;
		self::$tmpavatarbig = $avatarpath.$tmpavatarbig;
		self::$tmpavatarmiddle = $avatarpath.$tmpavatarmiddle;
		self::$tmpavatarsmall = $avatarpath.$tmpavatarsmall;
	}

	public static function output() 
	{
		global $_G;
		if(!empty($_G['uid'])) {
			if(self::$tmpavatarbig && self::$tmpavatarmiddle && self::$tmpavatarsmall) {
				$avatar1 = self::byte2hex(file_get_contents(self::$tmpavatarbig));
				$avatar2 = self::byte2hex(file_get_contents(self::$tmpavatarmiddle));
				$avatar3 = self::byte2hex(file_get_contents(self::$tmpavatarsmall));

				$extra = '&avatar1='.$avatar1.'&avatar2='.$avatar2.'&avatar3='.$avatar3;
				$result = self::uc_api_post_ex('user', 'rectavatar', array('uid' => $_G['uid']), $extra);

				@unlink(self::$tmpavatar);
				@unlink(self::$tmpavatarbig);
				@unlink(self::$tmpavatarmiddle);
				@unlink(self::$tmpavatarsmall);

				if($result == '<?xml version="1.0" ?><root><face success="1"/></root>') {
					$variable = array(
						'uploadavatar' => 'api_uploadavatar_success',
					);
					$tableext = '';
					$member = C::t('common_member')->fetch($_G['uid'], false, 1);
					if(!$member) {
						self::error('api_uploadavatar_user_not_exists');
					}
					$tableext = isset($member['_inarchive']) ? '_archive' : '';
					C::t('common_member'.$tableext)->update($_G['uid'], array('avatarstatus'=>'1'));
					bigapp_core::result(bigapp_core::variable($variable));
				} else {
					self::error('api_uploadavatar_uc_error');
				}
			}
		} else {
			self::error('api_uploadavatar_unavailable_user');
		}
	}

	public static function byte2hex($string) 
	{
		$buffer = '';
		$value = unpack('H*', $string);
		$value = str_split($value[1], 2);
		$b = '';
		foreach($value as $k => $v) {
			$b .= strtoupper($v);
		}

		return $b;
	}

	public static function uc_api_post_ex($module, $action, $arg = array(), $extra = '') 
	{
		$s = $sep = '';
		foreach($arg as $k => $v) {
			$k = urlencode($k);
			if(is_array($v)) {
				$s2 = $sep2 = '';
				foreach($v as $k2 => $v2) {
					$k2 = urlencode($k2);
					$s2 .= "$sep2{$k}[$k2]=".urlencode(uc_stripslashes($v2));
					$sep2 = '&';
				}
				$s .= $sep.$s2;
			} else {
				$s .= "$sep$k=".urlencode(uc_stripslashes($v));
			}
			$sep = '&';
		}
		$postdata = uc_api_requestdata($module, $action, $s, $extra);
		return uc_fopen2(UC_API.'/index.php', 500000, $postdata, '', TRUE, UC_IP, 20);
	}

	function error($errstr) 
	{
		$variable = array(
			'uploadavatar' => $errstr,
		);
		bigapp_core::result(bigapp_core::variable($variable));
	}

}

?>
