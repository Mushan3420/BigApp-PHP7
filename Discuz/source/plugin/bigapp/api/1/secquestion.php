<?php
/**
* @file login.php
* @Brief for uc login
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'logging';
$_GET['action'] = !empty($_GET['action']) ? $_GET['action'] : 'login';
include_once 'member.php';


require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';
class BigAppAPI {
	function common()
	{
		include_once 'source/language/lang_admincp_login.php';
        $data = array(
					array('questionid'=>"0","question"=>$lang['security_question_0']),
					array('questionid'=>"1","question"=>$lang['security_question_1']),
					array('questionid'=>"2","question"=>$lang['security_question_2']),
					array('questionid'=>"3","question"=>$lang['security_question_3']),
					array('questionid'=>"4","question"=>$lang['security_question_4']),
					array('questionid'=>"5","question"=>$lang['security_question_5']),
					array('questionid'=>"6","question"=>$lang['security_question_6']),
					array('questionid'=>"7","question"=>$lang['security_question_7']),
				);		
		echo BIGAPPJSON::encode(array('error_code' => 0,
									  'error_msg'  => 'succ',
									  'data'       => $data,
									  /*
									  'Message'    => array('messageval' => 'get security question succ', 
														    'messagestr' => null
													 ), 
									  'Variables'   => array('auth' => null)
									  */
									  )
								);
		die(0);	
	}
	
}
?>
