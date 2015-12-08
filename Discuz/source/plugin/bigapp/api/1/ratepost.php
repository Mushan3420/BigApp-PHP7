<?php
if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}

$_GET['mod'] = "misc";
$_GET['action'] = "rate";
$_GET['ratesubmit'] = "yes";
$_GET['infloat'] = 'yes';
$_GET['inajax'] = 1;
include_once 'forum.php';

class BigAppAPI 
{
    public function common()
    {
    }

    public function output()
    {
        global $_G;

        $status = 0;
		if ($messageval == 'thread_rate_succeed') {
            $status = 1;
        }
        $variable = array("status"=>$status);
        bigapp_core::result(bigapp_core::variable($variable));
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
