<?php

/***********************************************************
 * @file:   checkin.php
 * @author: tangyangyu(tangyy@youzu.com)
 * @create: 2015-08-10 20:32:14
 * @brief:  提供签到数据
 ***********************************************************/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';
include_once 'forum.php';

class BigAppAPI {

	public function common() 
	{

	}
	
	private function getCookieUserId() {
		global $_G;
			
		$uid = empty($_G['uid']) ? 0 : intval($_G['uid']);

		if($uid <= 0 && isset($_G['username'])) {
			$member = C::t('common_member')->fetch_by_username($_GET['username']);
			$uid = $member['uid'];
		}
		
		return strval($uid);
	}

	private function getInfo($uid, $date) {
		$query = DB::query('SELECT * FROM ' . DB::table('bigapp_checkin') . ' WHERE date = ' . $date . ' and uid = ' . $uid );
		$value = DB::fetch($query);
		
		return $value;
	}
	
	private function updateCredits($uid, $credit, $title) {
		global $_G;
		
		//adjust the title
		$index = substr($title, -1);
							
		$i = 1;
		foreach($_G['setting']['extcredits'] as $key  => $credits_value) {
			if($i++ < $index) continue;
			
			$title = 'extcredits'.$key;
			break;
		}		
		
		$query = DB::query('SELECT * FROM ' . DB::table('common_member_count') . ' WHERE uid = ' . $uid );
		$value = DB::fetch($query);
		
		if(!isset($title)) {
			return false;
		}
		
		if(!isset($value[$title])) {
			return false;
		}
		
		$data = array($title => ($credit + $value[$title]));
		$ret = DB::update('common_member_count', $data, array('uid' => $uid));
		
		return $ret;
	}
	
	
	public function output() 
	{
		global $_G;
		$tmp = $_G['setting']['bigapp_settings_checkin'];
		if(isset($_G['setting']['bigapp_settings_checkin']) && is_string($_G['setting']['bigapp_settings_checkin'])){
			$tmp = unserialize($_G['setting']['bigapp_settings_checkin']);
		}
		$succRet['data'] = $tmp;

		$uid = $_REQUEST['uid'];
		$date = date('Ymd');
		$last_date = date("Ymd",strtotime("-1 day"));
		
		$type = $_REQUEST['check'];
		
		if($uid == "") {
			$variable = array (
				"status" => strval(1),
				"checkin_enabled" => strval($succRet['data']['enabled']),
			);

			echo BIGAPPJSON::encode(array('Message' => array('messageval' => '1', 'messagestr' => 'success'), 'Variables' => $variable));
			die(0);
			//bigapp_core::result(bigapp_core::variable($variable));
		} 
		
		//获取当前用户的cookie userid
		$cookie_uid = BigAppAPI::getCookieUserId();
		if($cookie_uid !== $uid) {
			$variable = array (
				"status" => strval(0), 
				"message" => 'checkin failed',
			);
			
			echo BIGAPPJSON::encode(array('Message' => array('messageval' => '0', 'messagestr' => 'unauthorized user'), 'Variables' => $variable));
			die(0);
		}

		//合法的用户id
       $uid = $cookie_uid;
		
		try {
			$value = BigAppAPI::getInfo($uid, $date);
			$has_checked = empty($value) ? '0' : '1';
			
			if($type == '1') {
				if($succRet['data']['enabled'] == '1' && $has_checked == '1') {
					$checked = "1";
				} else {
					$checked = "0";
				}
				
				$variable = array (
					"status" => strval(1),
					"checkin_enabled" => strval($succRet['data']['enabled']),
					"checked" => strval($checked),
				);
				
				echo BIGAPPJSON::encode(array('Message' => array('messageval' => '1', 'messagestr' => 'success'), 'Variables' => $variable));
				die(0);
			} else {
				if($succRet['data']['enabled'] == '1' && $has_checked == '0') {
					$value = BigAppAPI::getInfo($uid, $last_date);
					$has_checked_yesterday = empty($value) ? '0' : '1';
					
					if($has_checked_yesterday == '1') {
						//昨天签到过，修正用户签到信息
						$value = BigAppAPI::getInfo($uid, $last_date);
						$days = $value['days'] + 1;
						
						$data = array("date" => $date, "days" => $days);
						$ret = DB::update('bigapp_checkin', $data, array('uid' => $uid, "date" => $last_date));
						
					} else {
						//昨天未签到，重置用户签到信息
						DB::delete('bigapp_checkin', DB::field("uid", $uid));
						
						$data = array("uid"=> $uid, "date" => $date, "days" => '1');
						DB::insert('bigapp_checkin', $data, $return_insert_id);
					}
					
					
					$value = BigAppAPI::getInfo($uid, $date);
					$credit = $succRet['data']['credit_plus'];
					if($value['days'] > 0 && $value['days'] % $succRet['data']['bonus_day'] == 0) {
						//加上连续签到奖励
						$credit = $credit + $succRet['data']['bonus_plus'];
					}
					
					$ret = BigAppAPI::updateCredits($uid, $credit, $succRet['data']['credit']);
					
					if($ret) {
						if(!isset($succRet['data']['credit'])) {
							$title['title'] = "";
						} else {
							$index = substr($succRet['data']['credit'], -1);
							
							$i = 1;
							foreach($_G['setting']['extcredits'] as $key  => $credits_value) {
								if($i++ < $index) continue;
								
								$title = $_G['setting']['extcredits'][$key];
								break;
							}
						}
						
						$variable = array (
							"status" => strval(1), 
							"days" => $value['days'],
							"credit" => $credit,
							"bonus_days" => $succRet['data']['bonus_day'],
							"bonus_plus" => $succRet['data']['bonus_plus'],
							"title" => $title['title'],
							"message" => 'checked in success',
						);
						
						echo BIGAPPJSON::encode(array('Message' => array('messageval' => '1', 'messagestr' => 'checked in success'), 'Variables' => $variable));
						die(0);
					} else {
						$variable = array (
							"status" => strval(1), 
							"message" => 'credit update failed',
						);
						
						echo BIGAPPJSON::encode(array('Message' => array('messageval' => '1', 'messagestr' => 'credit update failed'), 'Variables' => $variable));
						die(0);
					}
					
				} else {
					
					if($succRet['data']['enabled'] == '0') {
						$variable = array (
							"status" => strval(1),
							"message" => 'checkin not support', 
						);
						
						echo BIGAPPJSON::encode(array('Message' => array('messageval' => '1', 'messagestr' => 'checkin not support'), 'Variables' => $variable));
						die(0);
					} else {				
						$variable = array (
							"status" => strval(1),
							"message" => 'has checked in', 
						);
						
						echo BIGAPPJSON::encode(array('Message' => array('messageval' => '1', 'messagestr' => 'has checked in'), 'Variables' => $variable));
						die(0);
					}
				}
				
			}
			
			echo BIGAPPJSON::encode(array('Message' => array('messageval' => '1', 'messagestr' => 'success'), 'Variables' => $variable));
			die(0);
		} catch (Exception $e) {
			$variable = array (
				"status" => strval(1),
				"message" => 'checkin not support', 
			);
			
			echo BIGAPPJSON::encode(array('Message' => array('messageval' => '0', 'messagestr' => $e->getMessage()), 'Variables' => $variable));
			die(0);
		}
		//bigapp_core::result(bigapp_core::variable($variable));
	}
    
}

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
