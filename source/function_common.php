<?php
//版本1.1.0 最后修改日期 2014.2.27 by heisai

if(!defined('IN_SYS')) {
	exit('Access Denied');
}

//SQL ADDSLASHES
function saddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = saddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

//取消HTML代码
function shtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = shtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
			str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

//字符串解密加密
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

	$ckey_length = 4;	// 随机密钥长度 取值 0-32;
				// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
				// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
				// 当此值为 0 时，则不产生随机密钥

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

//清空cookie
function clearcookie() {
	global $_SGLOBAL;

	obclean();
	ssetcookie('auth', '', -86400 * 365);
	$_SGLOBAL['supe_uid'] = 0;
	$_SGLOBAL['supe_username'] = '';
	$_SGLOBAL['member'] = array();
}

//cookie设置
function ssetcookie($var, $value, $life=0) {
	global $_SGLOBAL, $_SC, $_SERVER;
	setcookie($_SC['cookiepre'].$var, $value, $life?($_SGLOBAL['timestamp']+$life):0, $_SC['cookiepath'], $_SC['cookiedomain'], $_SERVER['SERVER_PORT']==443?1:0);
}

//数据库连接
function dbconnect() {
	global $_SGLOBAL, $_SC;

	include_once(S_ROOT.'./source/class_mysql.php');

	if(empty($_SGLOBAL['db'])) {
		$_SGLOBAL['db'] = new dbstuff;
		$_SGLOBAL['db']->charset = $_SC['dbcharset'];
		$_SGLOBAL['db']->connect($_SC['dbhost'], $_SC['dbuser'], $_SC['dbpw'], $_SC['dbname'], $_SC['pconnect']);
	}
}

//获取在线IP
function getonlineip($format=0) {
	global $_SGLOBAL;

	if(empty($_SGLOBAL['onlineip'])) {
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$onlineip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$onlineip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
		$_SGLOBAL['onlineip'] = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	}
	if($format) {
		$ips = explode('.', $_SGLOBAL['onlineip']);
		for($i=0;$i<3;$i++) {
			$ips[$i] = intval($ips[$i]);
		}
		return sprintf('%03d%03d%03d', $ips[0], $ips[1], $ips[2]);
	} else {
		return $_SGLOBAL['onlineip'];
	}
}

//获取到表名
function tname($name) {
	global $_SC;
	return $_SC['tablepre'].$name;
}


//判断当前用户登录状态
function checkauth() {
	global $_SGLOBAL, $_SC, $_SCONFIG, $_SCOOKIE, $_SN;

	if($_COOKIE['auth']) {
		@list($password, $uid) = explode(" ", authcode($_COOKIE['auth'], 'DECODE'));
		$_SGLOBAL['supe_uid'] = intval($uid);		
		if($password && $_SGLOBAL['supe_uid']) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("open_session")." WHERE uid=".$_SGLOBAL['supe_uid']);
			if($session = $_SGLOBAL['db']->fetch_array($query)) {
				if($session['password'] == $password) {
					$_SGLOBAL['supe_username'] = addslashes($session['username']);
					insertsession($session);//更新session
				} else {
					$_SGLOBAL['supe_uid'] = 0;
				}
			} else {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("open_member")." WHERE uid=".$_SGLOBAL['supe_uid']);
				if($member = $_SGLOBAL['db']->fetch_array($query)) {
					if($member['password'] == $password) {
						$_SGLOBAL['supe_username'] = addslashes($member['username']);
						$session = array('uid' => $_SGLOBAL['supe_uid'], 'username' => $_SGLOBAL['supe_username'], 'password' => $password);
						insertsession($session);//登录
					} else {
						$_SGLOBAL['supe_uid'] = 0;
					}
				} else {
					$_SGLOBAL['supe_uid'] = 0;
				}
			}
		}
	}
	if(empty($_SGLOBAL['supe_uid'])) {
		clearcookie();
	}
	
	return $_SGLOBAL['supe_uid']; 
}


//对话框
function showmessage($msg) {
header("Content-type: text/html; charset=utf-8");
echo "<script language='javascript' type='text/javascript'>";
echo "alert('".$msg."');";
echo "</script>"; 

}

//页面跳转
function gourl($url){
echo "<script language='javascript' type='text/javascript'>";
echo "window.location.href='".$url."'";
echo "</script>";
}

//判断提交是否正确
function submitcheck($var) {
	if(!empty($_POST[$var]) && $_SERVER['REQUEST_METHOD'] == 'POST') {
		if((empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])) && $_POST['formhash'] == formhash()) {
			return true;
		} else {
			showmessage('submit_invalid');
		}
	} else {
		return false;
	}
}

//添加数据
function inserttable($tablename, $insertsqlarr, $returnid=0, $replace = false, $silent=0) {
	global $_SGLOBAL;

	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ($insertsqlarr as $insert_key => $insert_value) {
		$insertkeysql .= $comma.'`'.$insert_key.'`';
		$insertvaluesql .= $comma.'\''.$insert_value.'\'';
		$comma = ', ';
	}
	$method = $replace?'REPLACE':'INSERT';
	$_SGLOBAL['db']->query($method.' INTO '.$tablename.' ('.$insertkeysql.') VALUES ('.$insertvaluesql.')', $silent?'SILENT':'');
	if($returnid && !$replace) {
		return $_SGLOBAL['db']->insert_id();
	}
}

//更新数据
function updatetable($tablename, $setsqlarr, $wheresqlarr, $silent=0) {
	global $_SGLOBAL;

	$setsql = $comma = '';
	foreach ($setsqlarr as $set_key => $set_value) {
		if(is_array($set_value)) {
			$setsql .= $comma.'`'.$set_key.'`'.'='.$set_value[0];
		} else {
			$setsql .= $comma.'`'.$set_key.'`'.'=\''.$set_value.'\'';
		}
		$comma = ', ';
	}
	$where = $comma = '';
	if(empty($wheresqlarr)) {
		$where = '1';
	} elseif(is_array($wheresqlarr)) {
		foreach ($wheresqlarr as $key => $value) {
			$where .= $comma.'`'.$key.'`'.'=\''.$value.'\'';
			$comma = ' AND ';
		}
	} else {
		$where = $wheresqlarr;
	}
	$_SGLOBAL['db']->query('UPDATE '.$tablename.' SET '.$setsql.' WHERE '.$where, $silent?'SILENT':'');
}



//写运行日志
function runlog($file, $log, $halt=0) {
	global $_SGLOBAL, $_SERVER;

	$nowurl = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$log = sgmdate('Y-m-d H:i:s', $_SGLOBAL['timestamp'])."\t$type\t".getonlineip()."\t$_SGLOBAL[supe_uid]\t{$nowurl}\t".str_replace(array("\r", "\n"), array(' ', ' '), trim($log))."\n";
	$yearmonth = sgmdate('Ym', $_SGLOBAL['timestamp']);
	$logdir = './data/log/';
	if(!is_dir($logdir)) mkdir($logdir, 0777);
	$logfile = $logdir.$yearmonth.'_'.$file.'.php';
	if(@filesize($logfile) > 2048000) {
		$dir = opendir($logdir);
		$length = strlen($file);
		$maxid = $id = 0;
		while($entry = readdir($dir)) {
			if(strexists($entry, $yearmonth.'_'.$file)) {
				$id = intval(substr($entry, $length + 8, -4));
				$id > $maxid && $maxid = $id;
			}
		}
		closedir($dir);
		$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
		@rename($logfile, $logfilebak);
	}
	if($fp = @fopen($logfile, 'a')) {
		@flock($fp, 2);
		fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>', "\r", "\n"), '', $log)."\n");
		fclose($fp);
	}
	if($halt) exit();
}

//获取字符串
function getstr($string, $length, $in_slashes=0, $out_slashes=0, $censor=0, $bbcode=0, $html=0) {
	global $_SC, $_SGLOBAL;

	$string = trim($string);

	if($in_slashes) {
		//传入的字符有slashes
		$string = sstripslashes($string);
	}
	if($html < 0) {
		//去掉html标签
		$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
		$string = shtmlspecialchars($string);
	} elseif ($html == 0) {
		//转换html标签
		$string = shtmlspecialchars($string);
	}
	if($censor) {
		//词语屏蔽
		@include_once(S_ROOT.'./data/data_censor.php');
		if($_SGLOBAL['censor']['banned'] && preg_match($_SGLOBAL['censor']['banned'], $string)) {
			showmessage('information_contains_the_shielding_text');
		} else {
			$string = empty($_SGLOBAL['censor']['filter']) ? $string :
				@preg_replace($_SGLOBAL['censor']['filter']['find'], $_SGLOBAL['censor']['filter']['replace'], $string);
		}
	}
	if($length && strlen($string) > $length) {
		//截断字符
		$wordscut = '';
		if(strtolower($_SC['charset']) == 'utf-8') {
			//utf8编码
			$n = 0;
			$tn = 0;
			$noc = 0;
			while ($n < strlen($string)) {
				$t = ord($string[$n]);
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1;
					$n++;
					$noc++;
				} elseif(194 <= $t && $t <= 223) {
					$tn = 2;
					$n += 2;
					$noc += 2;
				} elseif(224 <= $t && $t < 239) {
					$tn = 3;
					$n += 3;
					$noc += 2;
				} elseif(240 <= $t && $t <= 247) {
					$tn = 4;
					$n += 4;
					$noc += 2;
				} elseif(248 <= $t && $t <= 251) {
					$tn = 5;
					$n += 5;
					$noc += 2;
				} elseif($t == 252 || $t == 253) {
					$tn = 6;
					$n += 6;
					$noc += 2;
				} else {
					$n++;
				}
				if ($noc >= $length) {
					break;
				}
			}
			if ($noc > $length) {
				$n -= $tn;
			}
			$wordscut = substr($string, 0, $n);
		} else {
			for($i = 0; $i < $length - 1; $i++) {
				if(ord($string[$i]) > 127) {
					$wordscut .= $string[$i].$string[$i + 1];
					$i++;
				} else {
					$wordscut .= $string[$i];
				}
			}
		}
		$string = $wordscut;
	}
	if($bbcode) {
		include_once(S_ROOT.'./source/function_bbcode.php');
		$string = bbcode($string, $bbcode);
	}
	if($out_slashes) {
		$string = saddslashes($string);
	}
	return trim($string);
}

//时间格式化
function sgmdate($dateformat, $timestamp='', $format=0) {
	global $_SCONFIG, $_SGLOBAL;
	if(empty($timestamp)) {
		$timestamp = $_SGLOBAL['timestamp'];
	}
	$timeoffset = 8;//strlen($_SGLOBAL['member']['timeoffset'])>0?intval($_SGLOBAL['member']['timeoffset']):intval($_SCONFIG['timeoffset']);
	$result = '';
	if($format) {
		$time = $_SGLOBAL['timestamp'] - $timestamp;
		if($time > 24*3600) {
			$result = gmdate($dateformat, $timestamp + $timeoffset * 3600);
		} elseif ($time > 3600) {
			$result = intval($time/3600).lang('hour').lang('before');
		} elseif ($time > 60) {
			$result = intval($time/60).lang('minute').lang('before');
		} elseif ($time > 0) {
			$result = $time.lang('second').lang('before');
		} else {
			$result = lang('now');
		}
	} else {
		$result = gmdate($dateformat, $timestamp + $timeoffset * 3600);
	}
	return $result;
}

//字符串时间化
function sstrtotime($string) {
	global $_SGLOBAL, $_SCONFIG;
	$time = '';
	if($string) {
		$time = strtotime($string);
		if(gmdate('H:i', $_SGLOBAL['timestamp'] + $_SCONFIG['timeoffset'] * 3600) != date('H:i', $_SGLOBAL['timestamp'])) {
			$time = $time - $_SCONFIG['timeoffset'] * 3600;
		}
	}
	return $time;
}


//ob
function obclean() {
	global $_SC;

	ob_end_clean();
	if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) {
		ob_start('ob_gzhandler');
	} else {
		ob_start();
	}
}


//获取数目
function getcount($tablename, $wherearr=array(), $get='COUNT(*)') {
	global $_SGLOBAL;
	if(empty($wherearr)) {
		$wheresql = '1';
	} else {
		$wheresql = $mod = '';
		foreach ($wherearr as $key => $value) {
			$wheresql .= $mod."`$key`='$value'";
			$mod = ' AND ';
		}
	}
	return $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT $get FROM ".$tablename." WHERE $wheresql LIMIT 1"), 0);
}


//rewrite链接
function rewrite_url($pre, $para) {
	$para = str_replace(array('&','='), array('-', '-'), $para);
	return '<a href="'.$pre.$para.'.html"';
}


//处理搜索关键字
function stripsearchkey($string) {
	$string = trim($string);
	$string = str_replace('*', '%', addcslashes($string, '%_'));
	$string = str_replace('_', '\_', $string);
	return $string;
}



//是否屏蔽二级域名
function isholddomain($domain) {
	global $_SCONFIG;

	$domain = strtolower($domain);

	if(preg_match("/^[^a-z]/i", $domain)) return true;
	$holdmainarr = empty($_SCONFIG['holddomain'])?array('www'):explode('|', $_SCONFIG['holddomain']);
	$ishold = false;
	foreach ($holdmainarr as $value) {
		if(strpos($value, '*') === false) {
			if(strtolower($value) == $domain) {
				$ishold = true;
				break;
			}
		} else {
			$value = str_replace('*', '', $value);
			if(@preg_match("/$value/i", $domain)) {
				$ishold = true;
				break;
			}
		}
	}
	return $ishold;
}

//连接字符
function simplode($ids) {
	return "'".implode("','", $ids)."'";
}

//显示进程处理时间
function debuginfo() {
	global $_SGLOBAL, $_SC, $_SCONFIG;

	if(empty($_SCONFIG['debuginfo'])) {
		$info = '';
	} else {
		$mtime = explode(' ', microtime());
		$totaltime = number_format(($mtime[1] + $mtime[0] - $_SGLOBAL['supe_starttime']), 4);
		$info = 'Processed in '.$totaltime.' second(s), '.$_SGLOBAL['db']->querynum.' queries'.
				($_SC['gzipcompress'] ? ', Gzip enabled' : NULL);
	}

	return $info;
}

//格式化大小函数
function formatsize($size) {
	$prec=3;
	$size = round(abs($size));
	$units = array(0=>" B ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" TB");
	if ($size==0) return str_repeat(" ", $prec)."0$units[0]";
	$unit = min(4, floor(log($size)/log(2)/10));
	$size = $size * pow(2, -10*$unit);
	$digi = $prec - 1 - floor(log($size)/log(10));
	$size = round($size * pow(10, $digi)) * pow(10, -$digi);
	return $size.$units[$unit];
}

//获取文件内容
function sreadfile($filename) {
	$content = '';
	if(function_exists('file_get_contents')) {
		@$content = file_get_contents($filename);
	} else {
		if(@$fp = fopen($filename, 'r')) {
			@$content = fread($fp, filesize($filename));
			@fclose($fp);
		}
	}
	return $content;
}

//写入文件
function swritefile($filename, $writetext, $openmod='w') {
	if(@$fp = fopen($filename, $openmod)) {
		flock($fp, 2);
		fwrite($fp, $writetext);
		fclose($fp);
		return true;
	} else {
		runlog('error', "File: $filename write error.");
		return false;
	}
}

//产生随机字符
function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
	$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[mt_rand(0, $max)];
	}
	return $hash;
}

//判断字符串是否存在
function strexists($haystack, $needle) {
	return !(strpos($haystack, $needle) === FALSE);
}


//检查站点是否关闭
function checkclose() {
	global $_SGLOBAL, $_SCONFIG;

	//站点关闭
	if($_SCONFIG['close'] && !ckfounder($_SGLOBAL['supe_uid']) && !checkperm('closeignore')) {
		if(empty($_SCONFIG['closereason'])) {
			showmessage('site_temporarily_closed');
		} else {
			showmessage($_SCONFIG['closereason']);
		}
	}
	//IP访问检查
	if((!ipaccess($_SCONFIG['ipaccess']) || ipbanned($_SCONFIG['ipbanned'])) && !ckfounder($_SGLOBAL['supe_uid']) && !checkperm('closeignore')) {
		showmessage('ip_is_not_allowed_to_visit');
	}
}

//站点链接
function getsiteurl() {
	global $_SCONFIG;

	if(empty($_SCONFIG['siteallurl'])) {
		$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
		return shtmlspecialchars('http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/')+1));
	} else {
		return $_SCONFIG['siteallurl'];
	}
}

//获取文件名后缀
function fileext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1)));
}

//去掉slassh
function sstripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = sstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}


//编码转换
function siconv($str, $out_charset, $in_charset='') {
	global $_SC;

	$in_charset = empty($in_charset)?strtoupper($_SC['charset']):strtoupper($in_charset);
	$out_charset = strtoupper($out_charset);
	if($in_charset != $out_charset) {
		if (function_exists('iconv') && (@$outstr = iconv("$in_charset//IGNORE", "$out_charset//IGNORE", $str))) {
			return $outstr;
		} elseif (function_exists('mb_convert_encoding') && (@$outstr = mb_convert_encoding($str, $out_charset, $in_charset))) {
			return $outstr;
		}
	}
	return $str;//转换失败
}


//用户操作时间间隔检查
function interval_check($type) {
	global $_SGLOBAL, $space;

	$intervalname = $type.'interval';
	$lastname = 'last'.$type;

	$waittime = 0;
	if($interval = checkperm($intervalname)) {
		$lasttime = isset($space[$lastname])?$space[$lastname]:getcount('space', array('uid'=>$_SGLOBAL['supe_uid']), $lastname);
		$waittime = $interval - ($_SGLOBAL['timestamp'] - $lasttime);
	}
	return $waittime;
}



//重新组建
function renum($array) {
	$newnums = $nums = array();
	foreach ($array as $id => $num) {
		$newnums[$num][] = $id;
		$nums[$num] = $num;
	}
	return array($nums, $newnums);
}


//ip访问允许
function ipaccess($ipaccess) {
	return empty($ipaccess)?true:preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($ipaccess, '/')).")/", getonlineip());
}

//ip访问禁止
function ipbanned($ipbanned) {
	return empty($ipbanned)?false:preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($ipbanned, '/')).")/", getonlineip());
}

//检查start
function ckstart($start, $perpage) {
	global $_SCONFIG;

	$maxstart = $perpage*intval($_SCONFIG['maxpage']);
	if($start < 0 || ($maxstart > 0 && $start >= $maxstart)) {
		showmessage('length_is_not_within_the_scope_of');
	}
}


//语言替换
function lang_replace($text, $vars) {
	if($vars) {
		foreach ($vars as $k => $v) {
			$rk = $k + 1;
			$text = str_replace('\\'.$rk, $v, $text);
		}
	}
	return $text;
}



//截取链接
function sub_url($url, $length) {
	if(strlen($url) > $length) {
		$url = str_replace(array('%3A', '%2F'), array(':', '/'), rawurlencode($url));
		$url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
	}
	return $url;
}


//取数组中的随机个
function sarray_rand($arr, $num=1) {
	$r_values = array();
	if($arr && count($arr) > $num) {
		if($num > 1) {
			$r_keys = array_rand($arr, $num);
			foreach ($r_keys as $key) {
				$r_values[$key] = $arr[$key];
			}
		} else {
			$r_key = array_rand($arr, 1);
			$r_values[$r_key] = $arr[$r_key];
		}
	} else {
		$r_values = $arr;
	}
	return $r_values;
}



//产生form防伪码
function formhash() {
	global $_SGLOBAL, $_SCONFIG;

	if(empty($_SGLOBAL['formhash'])) {
		$hashadd = defined('IN_ADMINCP') ? 'AdminCP' : '';
		$_SGLOBAL['formhash'] = substr(md5(substr($_SGLOBAL['timestamp'], 0, -7).'|'.$_SGLOBAL['supe_uid'].'|'.md5($_SCONFIG['sitekey']).'|'.$hashadd), 8, 8);
	}
	return $_SGLOBAL['formhash'];
}

//检查邮箱是否有效
function isemail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}


//获取目录
function sreaddir($dir, $extarr=array()) {
	$dirs = array();
	if($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if(!empty($extarr) && is_array($extarr)) {
				if(in_array(strtolower(fileext($file)), $extarr)) {
					$dirs[] = $file;
				}
			} else if($file != '.' && $file != '..') {
				$dirs[] = $file;
			}
		}
		closedir($dh);
	}
	return $dirs;
}

//添加session
function insertsession($setarr) {
	global $_SGLOBAL, $_SCONFIG;

	$_SCONFIG['onlinehold'] = intval($_SCONFIG['onlinehold']);
	if($_SCONFIG['onlinehold'] < 1800) $_SCONFIG['onlinehold'] = 1800;
	//$_SGLOBAL['db']->query("DELETE FROM ".tname("session")." WHERE uid='$setarr[uid]' OR lastactivity<'".($_SGLOBAL['timestamp']-$_SCONFIG['onlinehold'])."'");
    $_SGLOBAL['db']->query("DELETE FROM ".tname("open_session")." WHERE uid='$setarr[uid]'");	
	

	//添加在线
	$ip = getonlineip();
	$setarr['lastactivity'] = $_SGLOBAL['timestamp'];
	$setarr['ip'] = $ip;

	inserttable(tname("open_session"), $setarr, 0, true, 0);

	$_SGLOBAL['supe_uid'] = $setarr['uid'];
}

//生成站点key
function mksitekey() {
	global $_SERVER, $_SC, $_SGLOBAL;
	//16位
	$sitekey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$_SC['dbhost'].$_SC['dbuser'].$_SC['dbpw'].$_SC['dbname'].substr($_SGLOBAL['timestamp'], 0, 6)), 8, 6).random(10);
	return $sitekey;
}

/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access   public
 * @param    mix      $item_list      列表数组或字符串
 * @param    string   $field_name     字段名称
 *
 * @return   void
 */
function db_create_in($item_list, $field_name = '')
{
    if (empty($item_list))
    {
        return $field_name . " IN ('') ";
    }
    else
    {
        if (!is_array($item_list))
        {
            $item_list = explode(',', $item_list);
        }
        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list AS $item)
        {
            if ($item !== '')
            {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp))
        {
            return $field_name . " IN ('') ";
        }
        else
        {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}


/**
           * 根据指定的键对数组排序
           *
           * 用法：
           * @code php
           * $rows = array(
           *           array('id' => 1, 'value' => '1-1', 'parent' => 1),
           *           array('id' => 2, 'value' => '2-1', 'parent' => 1),
           *           array('id' => 3, 'value' => '3-1', 'parent' => 1),
           *           array('id' => 4, 'value' => '4-1', 'parent' => 2),
           *           array('id' => 5, 'value' => '5-1', 'parent' => 2),
           *           array('id' => 6, 'value' => '6-1', 'parent' => 3),
           * );
           *
           * $rows = Helper_Array::sortByCol($rows, 'id', SORT_DESC);
           * dump($rows);
           * // 输出结果为：
           * // array(
           * //         array('id' => 6, 'value' => '6-1', 'parent' => 3),
           * //         array('id' => 5, 'value' => '5-1', 'parent' => 2),
           * //         array('id' => 4, 'value' => '4-1', 'parent' => 2),
           * //         array('id' => 3, 'value' => '3-1', 'parent' => 1),
           * //         array('id' => 2, 'value' => '2-1', 'parent' => 1),
           * //         array('id' => 1, 'value' => '1-1', 'parent' => 1),
           * // )
           * @endcode
           *
           * @param array $array 要排序的数组
           * @param string $keyname 排序的键
           * @param int $dir 排序方向
           *
           * @return array 排序后的数组
           */
          function sortByCol($array, $keyname, $dir = SORT_ASC)
          {
              return sortByMultiCols($array, array($keyname => $dir));
          }
          /**
           * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
           *
           * 用法：
           * @code php
           * $rows = Helper_Array::sortByMultiCols($rows, array(
           *           'parent' => SORT_ASC, 
           *           'name' => SORT_DESC,
           * ));
           * @endcode
           *
           * @param array $rowset 要排序的数组
           * @param array $args 排序的键
           *
           * @return array 排序后的数组
           */
          function sortByMultiCols($rowset, $args)
          {
              $sortArray = array();
              $sortRule = '';
              foreach ($args as $sortField => $sortDir) 
              {
                  foreach ($rowset as $offset => $row) 
                  {
                      $sortArray[$sortField][$offset] = $row[$sortField];
                  }
                  $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
              }
              if (empty($sortArray) || empty($sortRule)) { return $rowset; }
              eval('array_multisort(' . $sortRule . '$rowset);');
              return $rowset;
          }
		  
		  
		  //文本存入数据库
		  function content_to_db($content)
		  {			  
			return str_replace(array("\r\n","\n",chr(32),chr(13),chr(10)),array("<br>","<br>","&nbsp;","<br>","<br>"),getstr($content));
		  }


		  //数据库文本取入textarea
		  function db_to_content($content)
		  {
			  return str_replace(array("<br>","<br>","&nbsp;","<br>","<br>"),array("\r\n","\n",chr(32),chr(13),chr(10)),$content);
		  }

function convertip($ip) {

	$return = '';

	if(preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {

		$iparray = explode('.', $ip);

		if($iparray[0] == 10 || $iparray[0] == 127 || ($iparray[0] == 192 && $iparray[1] == 168) || ($iparray[0] == 172 && ($iparray[1] >= 16 && $iparray[1] <= 31))) {
			$return = '- LAN';
		} elseif($iparray[0] > 255 || $iparray[1] > 255 || $iparray[2] > 255 || $iparray[3] > 255) {
			$return = '- Invalid IP Address';
		} else {
			$tinyipfile = S_ROOT.'./data/ipdata/tinyipdata.dat';
			$fullipfile = S_ROOT.'./data/ipdata/wry.dat';
			if(@file_exists($tinyipfile)) {
				$return = convertip_tiny($ip, $tinyipfile);
			} elseif(@file_exists($fullipfile)) {
				$return = convertip_full($ip, $fullipfile);
			}
		}
	}

	return $return;

}

function convertip_tiny($ip, $ipdatafile) {

	static $fp = NULL, $offset = array(), $index = NULL;

	$ipdot = explode('.', $ip);
	$ip    = pack('N', ip2long($ip));

	$ipdot[0] = (int)$ipdot[0];
	$ipdot[1] = (int)$ipdot[1];

	if($fp === NULL && $fp = @fopen($ipdatafile, 'rb')) {
		$offset = @unpack('Nlen', @fread($fp, 4));
		$index  = @fread($fp, $offset['len'] - 4);
	} elseif($fp == FALSE) {
		return  '- Invalid IP data file';
	}

	$length = $offset['len'] - 1028;
	$start  = @unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);

	for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {

		if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
			$index_offset = @unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
			$index_length = @unpack('Clen', $index{$start + 7});
			break;
		}
	}

	@fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
	if($index_length['len']) {
		return @fread($fp, $index_length['len']);
	} else {
		return '- Unknown';
	}

}

function convertip_full($ip, $ipdatafile) {

	if(!$fd = @fopen($ipdatafile, 'rb')) {
		return '- Invalid IP data file';
	}

	$ip = explode('.', $ip);
	$ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

	if(!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4)) ) return;
	@$ipbegin = implode('', unpack('L', $DataBegin));
	if($ipbegin < 0) $ipbegin += pow(2, 32);
	@$ipend = implode('', unpack('L', $DataEnd));
	if($ipend < 0) $ipend += pow(2, 32);
	$ipAllNum = ($ipend - $ipbegin) / 7 + 1;

	$BeginNum = $ip2num = $ip1num = 0;
	$ipAddr1 = $ipAddr2 = '';
	$EndNum = $ipAllNum;

	while($ip1num > $ipNum || $ip2num < $ipNum) {
		$Middle= intval(($EndNum + $BeginNum) / 2);

		fseek($fd, $ipbegin + 7 * $Middle);
		$ipData1 = fread($fd, 4);
		if(strlen($ipData1) < 4) {
			fclose($fd);
			return '- System Error';
		}
		$ip1num = implode('', unpack('L', $ipData1));
		if($ip1num < 0) $ip1num += pow(2, 32);

		if($ip1num > $ipNum) {
			$EndNum = $Middle;
			continue;
		}

		$DataSeek = fread($fd, 3);
		if(strlen($DataSeek) < 3) {
			fclose($fd);
			return '- System Error';
		}
		$DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
		fseek($fd, $DataSeek);
		$ipData2 = fread($fd, 4);
		if(strlen($ipData2) < 4) {
			fclose($fd);
			return '- System Error';
		}
		$ip2num = implode('', unpack('L', $ipData2));
		if($ip2num < 0) $ip2num += pow(2, 32);

		if($ip2num < $ipNum) {
			if($Middle == $BeginNum) {
				fclose($fd);
				return '- Unknown';
			}
			$BeginNum = $Middle;
		}
	}

	$ipFlag = fread($fd, 1);
	if($ipFlag == chr(1)) {
		$ipSeek = fread($fd, 3);
		if(strlen($ipSeek) < 3) {
			fclose($fd);
			return '- System Error';
		}
		$ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
		fseek($fd, $ipSeek);
		$ipFlag = fread($fd, 1);
	}

	if($ipFlag == chr(2)) {
		$AddrSeek = fread($fd, 3);
		if(strlen($AddrSeek) < 3) {
			fclose($fd);
			return '- System Error';
		}
		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if(strlen($AddrSeek2) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}

		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr2 .= $char;

		$AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
		fseek($fd, $AddrSeek);

		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr1 .= $char;
	} else {
		fseek($fd, -1, SEEK_CUR);
		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr1 .= $char;

		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if(strlen($AddrSeek2) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}
		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr2 .= $char;
	}
	fclose($fd);

	if(preg_match('/http/i', $ipAddr2)) {
		$ipAddr2 = '';
	}
	$ipaddr = "$ipAddr1 $ipAddr2";
	$ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
	$ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
	$ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
	if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
		$ipaddr = '- Unknown';
	}

	return $ipaddr;

}


/**
 * XSS 清除处理
 */
function xssClean($data, $htmlentities = 0)
{
	$htmlentities && $data = htmlentities($data, ENT_QUOTES, 'utf-8');
	// Fix &entity\n;
	$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
	$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
	$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
	$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

	// Remove any attribute starting with "on" or xmlns
	$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

	// Remove javascript: and vbscript: protocols
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"\\\\]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"\\\\]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"\\\\]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

	// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"\\\\]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"\\\\]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"\\\\]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

	// Remove namespaced elements (we do not need them)
	$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

	do
	{
		// Remove really unwanted tags
		$old_data = $data;
		$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
	}
	while ($old_data !== $data);

	// we are done...
	$data = filter_remote_img_type($data, FALSE);
	return $data;
}

/**
 * 过滤内容中有问题网络图片
 * @author phpseyo<phpseyo@qq.com>
 * @param string $text 过滤文本
 * @param boolean $bbcode 是否为BBCODE类型
 * @return string
 */
function filter_remote_img_type($text, $bbcode = TRUE)
{
	$pattern = $bbcode ? "/\[img[^\]]*\]\s*(.*?)+\s*\[\/img\]/is" : "/<img[^>]+src=[\'|\"]([^\'|\"]+)[\'|\"][^>]*[\/]?>/is";
	preg_match_all($pattern, $text, $matches);
	foreach ($matches[1] as $k => $src) {
		$data = get_headers($src);
		$header_str = implode('', $data);
		if (FALSE === strpos($header_str, 'Content-Type: image') || FALSE !== strpos($header_str, 'HTTP/1.1 401') || FALSE !== strpos($header_str, 'HTTP/1.1 404')) {
			$text = str_replace($matches[0][$k], '', $text);
		}
	}
	return $text;
}
?>
