<?php
class DzUc
{
    // 登录校验，成功返回uid，失败返回error_sring
    public static function logincheck($username, $password, $questionid, $answer)
    {/*{{{*/
        global $_G;
        require_once libfile('function/misc');
        require_once libfile('function/mail');
		require_once libfile('function/member');
		require_once libfile('class/member');
        loaducenter();
        try {
            if(!($_G['member_loginperm'] = logincheck($username))) {
                throw new Exception("too_many_errors");
            }
            $result = userlogin($username, $password, $questionid, $answer, 'username', $_G['clientip']); 
            $uid = $result["ucresult"]["uid"];
            if ($uid<=0) {
                throw new Exception("error_password");
            }
            return $uid;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }/*}}}*/

    // 登录
    public static function dologin($uid)
    {/*{{{*/
        global $_G;
        if (!($member = getuserbyuid($uid, 1))) {
            return false;
        }
        if (isset($member['_inarchive'])) {
            C::t('common_member_archive')->move_to_master($member['uid']);
        }
        require_once libfile('function/member');
        $cookietime = 1296000;
        setloginstatus($member, $cookietime);
		//dsetcookie('connect_login', 1, $cookietime);
		//dsetcookie('connect_is_bind', '1', 31536000);
		//dsetcookie('connect_uin', $connect_member['conopenid'], 31536000);
        return true;
    }/*}}}*/

    // 注销
    public static function dologout()
    {/*{{{*/
		global $_G;
        require_once libfile('function/misc');
        require_once libfile('function/mail');
		require_once libfile('function/member');
		require_once libfile('class/member');

		$ctlObj = new logging_ctl();
		$ctlObj->setting = $_G['setting'];
		clearcookies();
    }/*}}}*/

    // 生成随机email
    public static function gen_rand_email($email)
    {/*{{{*/
        $arr = explode("@",$email);
        $p = $arr[0];
        $e = $arr[1];
		$charset = array(
			"a","b","c","d","e","f","g","h","i","j","k","l","m",
			"n","o","p","q","r","s","t","u","v","w","x","y","z",
			"0","1","2","3","4","5","6","7","8","9"
		);
        $len = count($charset);
		$res = "";
		shuffle($charset);
		for ($i=0; $i<2; ++$i) {
			$rn = mt_rand(0,$len-1);
			$char = $charset[$rn];
			$charset[$rn] = $charset[$len-1];
			--$len;
			if (!is_numeric($char)) {
				$seed = mt_rand(0,1);
				if ($seed == 0) $char = strtoupper($char);
			}
			$res.= $char;
		}
        return "$p$res@$e";
    }/*}}}*/

    // 注册
    public static function regist($username, $password, $email, $profile=array())
    {/*{{{*/
        global $_G;
        require_once libfile('function/misc');
        require_once libfile('function/mail');
		require_once libfile('function/member');
		require_once libfile('class/member');

		try {
            //1. check name,pass
            $userNamelen = dstrlen($username);
            if ($userNamelen<3 || $userNamelen > 15) {
                throw new Exception("username_len_invalid");
            }
            $passwdlen = dstrlen($password);
            if ($passwdlen<6 || $passwdlen > 20) {
                throw new Exception("password_len_invalid");
            }
			loaducenter();
            //1. gen uid
			$ctlObj = new register_ctl();
			$uid = uc_user_register($username, $password, $email, '', '', $_G['clientip']);
            if ($uid<=0) {
                switch ($uid) {
                    case -3: throw new Exception("used_username"); break;
                    case -4:
                    case -5: throw new Exception("invalid_email"); break;
                    case -6:
                        $email = self::gen_rand_email($email);
                        return self::regist($username, $password, $email, $profile);
                        break;
                    default: throw new Exception("regist_failed"); break;
                };
            }
            //2. insert db
			if($ctlObj->setting['regverify']) {
				$groupinfo['groupid'] = 8;
			} else {
				$groupinfo['groupid'] = $ctlObj->setting['newusergroupid'];
			}
			$verifyarr = array ();
			$emailstatus = 0;
			$init_arr = array('credits' => explode(',', $ctlObj->setting['initcredits']), 'profile'=>$profile, 'emailstatus' => $emailstatus);
			C::t('common_member')->insert($uid, $username, $password, $email, $_G['clientip'], $groupinfo['groupid'], $init_arr);
            //3. do login
            self::dologin($uid);
            return $uid;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }/*}}}*/

}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
