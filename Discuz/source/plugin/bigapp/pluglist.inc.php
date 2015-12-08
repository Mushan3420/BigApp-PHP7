<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once dirname(__FILE__) . '/bigappjson.class.php';

//echo json_encode($_G);
//die(0);

if(isset($_GET['log']) && $_GET['log']){
	header("Content-type:text/plain;charset=utf-8");
	$dateStr = date('Ym');
	if(isset($_GET['date'])){
		$dateStr = $_GET['date'];
	}
	$file = rtrim(DISCUZ_ROOT, '/') . '/data/log/' . $dateStr . '_bigapp.php';
	if(is_readable($file)){
		$tmp = @file($file);
		$cnt = count($tmp);
		$lines = array();
		for($i = 0; $i < $cnt; $i++){
			$line = trim($tmp[$i]);
			if(!empty($line)){
				$lines[] = $tmp[$i];
			}
		}
		$cnt = count($lines);
		$i = 0;
		$total = 1024;
		if(isset($_GET['count']) && $_GET['count']){
			$total = intval($_GET['count']);
		}
		if($cnt >= $total){
			$i = $cnt - $total;
		}
		for(;$i < $cnt; $i++){
			echo $lines[$i];
		}
	}else{
		echo 'such log file does not exists or not readable [ log file: ' . '${DISCUZ_ROOT}/data/log/' . $dateStr . '_bigapp.php' . ' ]';
	}
	die(0);
}
if(isset($_GET['getdau']) && $_GET['getdau']){
	$end = time();
	$start = $end - 86400;
	$sql = 'SELECT COUNT(DISTINCT uid) AS total FROM ' . DB::table('common_member_status') . ' WHERE lastactivity >= ' . $start . ' AND lastactivity < ' . $end;
	$dbRet = DB::query($sql);
	$count = array('dau' => 'NA');
	while($row = DB::fetch($dbRet)){
		$count = array('dau' => $row['total']);
		break;
	}
	if(!is_null($count)){
		echo BIGAPPJSON::encode($count);
	}else{
		echo BIGAPPJSON::encode($count);
	}
	die(0);
}
loadcache('forums');
$ret['discuz_version'] = $_G['setting']['version'];
$ret['plugins'] = $_G['setting']['plugins'];
$ret['forums'] = $_G['cache']['forums'];
foreach ($GLOBALS as $key => $value){
	if('GLOBALS' === $key){
		continue;
	}
	$ret['GLOBALS'][$key] = $value;
}
if(function_exists('phpversion')){
	$ret['php_version'] = phpversion();
}
if(function_exists('get_loaded_extensions')){
	$ret['loaded_module'] = get_loaded_extensions();
}
echo BIGAPPJSON::encode($ret);
?>
