<?php

class BigAppConf
{
	public static $debug = false;	//是否打开debug，发布时应该予以关闭
	public static $akskFile = './data/aksk';
	public static $optFix = array('png', 'jpg', 'jpeg');
	public static $detailSize = '400_400';
	public static $enablePicOpt = true;	
	public static $thumbSize = '200_200';
	public static $upfileUrl = 'plugin.php?id=bigapp:uploadpic'; //本服务器上传文件的地址
	public static $myAppUrl = 'plugin.php?id=bigapp:myapp'; //本服务器上传文件的地址
	public static $myHomeUrl = 'plugin.php?id=bigapp:homeapi';//自定义主页
	public static $internalFileSvr = 'http://mobfile.youzu.com/upload'; //内部文件服务器地址
	public static $showfileUrl = 'http://mobfile.youzu.com/show';
	public static $ucRegUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=uc&controller=ucregister&method=regist';
	//public static $ucRegUrl = 'http://uc.youzu.com/ucregister/regist';
        public static $appInfoUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=app&method=get_basic';
	//public static $appInfoUrl = 'http://app.youzu.com/app/get_basic';
	public static $accountSetUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=settings&method=set';
	public static $accountGetUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=settings&method=get';
	//public static $taskCreateUrl = 'http://app.youzu.com/app/create';
	public static $taskCreateUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=app&method=create';
	//获取keystore信息
	public static $shareUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=app&method=get_share';

        public static $savePushAcctUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=jpush&method=savejpushapp';
        public static $getPushAcctUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=jpush&method=getjpushapp';
        public static $pushUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=jpush&method=addmsg';	

        public static $checkUpdateUrl = 'http://app.youzu.com/app/need_upadte';
    //public static $taskInfoUrl = 'http://app.youzu.com/app/get_latest';
	public static $taskInfoUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=app&method=get_latest';
	//public static $taskScheduleUrl = 'http://app.youzu.com/app/get_schedule';
	public static $taskScheduleUrl = 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=app&method=get_schedule2';
	public static $statApis = array(
		#'today_trend' => 'http://192.168.180.23:8080/product/ui/http/index.php?module=api&controller=today&method=getTrend',
		#'today_total' => 'http://192.168.180.23:8080/product/ui/http/index.php?module=api&controller=today&method=getTotal',
		#'days_total' => 'http://192.168.180.23:8080/product/ui/http/index.php?module=api&controller=days&method=gettotal',
		#'days_trend' => 'http://192.168.180.23:8080/product/ui/http/index.php?module=api&controller=days&method=gettrend',
		#'yesterday_total' => 'http://192.168.180.23:8080/product/ui/http/index.php?module=api&controller=days&method=getyesterdaytotal',
		'today_trend' => 'http://app.youzu.com/today/getTrend',
		'today_total' => 'http://app.youzu.com/today/gettotal',
		'days_total' => 'http://app.youzu.com/days/gettotal',
		'days_trend' => 'http://app.youzu.com/days/gettrend',
		'yesterday_total' => 'http://app.youzu.com/days/getyesterdaytotal',
	);

    ////////////////////////////////////////////////////////////////////////////
    //
    public static $releaseApis = array(
        'new_versions' => 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=appversion&method=new_versions',
        'create_version' => 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=appversion&method=create_version',
        'release_versions' => 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=appversion&method=release_versions',
        'latest_version' => 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=appversion&method=latest_version',
        'release' => 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=appversion&method=release',
        'latest_package' => 'http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=appversion&method=get_latest_package',
        'mid_page' => "http://192.168.180.93:8080/product/ui/http/index.php?module=api&controller=appversion&method=get_midpage",
    );

    // 站长中心api
    public static $mcapis = array (
        // 认证校验接口
        "checkaksk" => "http://192.168.180.93:8080/product/ui/http/index.php?module=mc&controller=mcapi&method=checkaksk",
        // 前往站长中心&获取key接口
        "checkin"   => "http://192.168.180.93:8080/product/ui/http/index.php?module=mc&controller=mcapi&method=checkin",
        // 点此查阅接口
        "myapp"     => "http://192.168.180.93:8080/product/ui-app/mc/fe/dist/index.html#/app_list",
        // 打包与配置接口
        "app"       => "http://192.168.180.93:8080/product/ui-app/mc/fe/dist/index.html#/app_main/",
        // 自动认证接口
        "autoverify"=> "http://192.168.180.93:8080/product/ui/http/index.php?module=mc&controller=mcapi&method=checkApi",
    );
    ////////////////////////////////////////////////////////////////////////////

	public static $qrCodePrefix = 'http://chart.apis.google.com/chart?chs=150x150&cht=qr&chld=L|0&chl=';
	public static $iconImgSize = array('width' => 108, 'height' => 108);
	public static $startupImgSize = array('width' => 108, 'height' => 192);
	public static $imgRequire = array(
			'icon_image_s' => array('allow_postfix' => array('image/png'), 'need_compress' => 0, 'width' => 1024, 'height' => 1024, 'size' => 1048576),
			'startup_image_s' => array('allow_postfix' => array('image/png'), 'width' => 1242, 'height' => 2208, 'size' => 1048576),
			'banner_image_s' => array('width' => 750, 'height' => 342, 'size' => 1048576),
			'func_image_s' => array('width' => 96, 'height' => 96, 'size' => 1048576),
			'func_forum_image_s' => array('width' => 88, 'height' => 88, 'size' => 1048576),
	////////////////////////////////////////////////////////////////////////////
    // add by mawentao
            'mobile_app_image_s' => array('width'=>249, 'height'=>433, 'size'=>1048576),
    ////////////////////////////////////////////////////////////////////////////
			);
	//自定义首页
    //96*96 88*88 750*342	
	public static $bannerImgSize = array('width' => 750, 'height' => 342);
	public static $funcLinkImgSize = array('width' => 96, 'height' => 96);
	public static $funcForumImgSize = array('width' => 88, 'height' => 88);
	
	public static $defaultConfig = array(
			'app_name' => 'discuz-app',
			'package_name' => 'com.youzu.clan',
			'channel_name' => 'bigapp',
			'bbs_name' => 'discuz',
			'os' => 3,
			'icon_image' => 'http://mobfile.youzu.com/show?size=1024_1024&pic=Uploads_image%2F2%2Fe%2Fa%2Fd%2Feadf1eda9529216516c1af5a5dd3fa5f.png',
			'startup_image' => 'http://mobfile.youzu.com/show?size=1242_2208&pic=Uploads_image%2F4%2Fb%2Fe%2F3%2Fbe306eea2728632f2cb7c1b883b5de6e.png',
			'nav_color' => '#198CE4',
			'version_name' => '1.0.0',
			'push_enabled' => 0,
			'jpush_app_key' => '',
			'jpush_master_secret' => '',
			'jpush_is_free' => 1,
			);
    public static $defaultShareConfig = array(
			'key_alias' => 'youyu.keystore',
			'store_password' => '1422@youzu',
			'key_password' => 'xiaoshun@youzu',
			'app_id_wechat' => 'wxb05003635a752c53',
			'sec_key_wechat' => '1bf2e7110a83d2b1d2ce71c11f18ca36',
			'app_id_qq' => '1104768684',
			'sec_key_qq' => 'QBWUohHz82fpd55h',
			'app_id_sina' => '3552514107',
			'sec_key_sina' => 'b234a863f8cfb50e4ec7c47ca0babba9',
			'redirect_url_sina' => 'https://api.weibo.com/oauth2/default.html',
			);

	//自定义按钮默认配置
    public static $defaultButtonSkeleton = array(
		array('id' => '1', 'icon_type' => '3_24', 'name' => '首页'),
		array('id' => '2', 'icon_type' => '3_25', 'name' => '论坛'),
		array('id' => '3', 'icon_type' => '3_26', 'name' => '发帖'),
		array('id' => '4', 'icon_type' => '3_27', 'name' => '站内信'),
		array('id' => '5', 'icon_type' => '3_28', 'name' => '我的'),
    );
	 
	//自定义首页默认配置
	public static $defaultHome = array(
								"banner"=>array(
												array('id'=>"1","title"=>"bigapp1","pic"=>"http://mobfile.youzu.com/Uploads_image/25/b/c/1/bc1ac812cc7927213d65149df160cca7.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"1","status"=>"1","desc"=>""),
												array('id'=>"2","title"=>"bigapp2","pic"=>"http://mobfile.youzu.com/Uploads_image/27/5/4/f/54f71d9ccc1d403ec90ffe9913bb967c.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"2","status"=>"1","desc"=>""),
												array('id'=>"3","title"=>"bigapp3","pic"=>"http://mobfile.youzu.com/Uploads_image/33/6/0/4/6041a6f1ecb9c375083ce6ce606d6bfa.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"3","status"=>"1","desc"=>""),
												array('id'=>"4","title"=>"bigapp4","pic"=>"http://mobfile.youzu.com/Uploads_image/22/d/a/b/dabd4a13566464ac912d962b5201329c.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"4","status"=>"1","desc"=>""),
												array('id'=>"5","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/28/5/5/9/5594b24cadf974af82ddb25892e9ac3c.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"5","status"=>"1","desc"=>""),
												array('id'=>"6","title"=>"bigapp6","pic"=>"http://mobfile.youzu.com/Uploads_image/32/d/7/b/d7be3c520b736af8fd6817ac6a89ebc4.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"6","status"=>"1","desc"=>""),
										),
								"func"=>array(
												array('id'=>"1","title"=>"bigapp1","pic"=>"http://mobfile.youzu.com/Uploads_image/1/a/2/d/a2d48037df9c1dbeacdb3232ca2197b3.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"1","desc"=>"bigapp","status"=>"1"),
												array('id'=>"2","title"=>"bigapp2","pic"=>"http://mobfile.youzu.com/Uploads_image/1/d/2/c/d2c5d626b2e2dbb705b277f92903a767.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"2","desc"=>"bigapp","status"=>"1"),
												array('id'=>"3","title"=>"bigapp3","pic"=>"http://mobfile.youzu.com/Uploads_image/1/c/5/d/c5d514e2ff2a7d4c58ceed840e0253f0.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"5","desc"=>"bigapp","status"=>"1"),
												array('id'=>"4","title"=>"bigapp4","pic"=>"http://mobfile.youzu.com/Uploads_image/1/d/6/4/d6443b9c58e64aa3d640d269b23d444f.png","url"=>"http://192.168.180.23:8080/discuz/forum.php?mod=viewthread&tid=1&extra=page%3D1","type"=>"2","pid"=>"0","order"=>"4","desc"=>"bigapp","status"=>"1"),
												array('id'=>"5","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/2/6/1/2610b00cd55a65d3580037c910f37e06.png","url"=>"http://192.168.180.23:8080/discuz/forum.php?mod=forumdisplay&fid=1","type"=>"3","pid"=>"3","order"=>"3","desc"=>"bigapp","status"=>"1"),
												array('id'=>"6","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/3/2/7/327bef8b10441329007fb9dcc956be9f.png","url"=>"http://192.168.180.23:8080/discuz/forum.php?mod=forumdisplay&fid=1","type"=>"3","pid"=>"3","order"=>"3","desc"=>"bigapp","status"=>"1"),
												array('id'=>"7","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/b/1/8/b184780602b05fd6df357984890b11f2.png","url"=>"http://192.168.180.23:8080/discuz/forum.php?mod=forumdisplay&fid=1","type"=>"3","pid"=>"3","order"=>"3","desc"=>"bigapp","status"=>"1"),
												array('id'=>"8","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/c/a/1/ca107b8c4d6edd90271c652148aeb1e7.png","url"=>"http://192.168.180.23:8080/discuz/forum.php?mod=forumdisplay&fid=1","type"=>"3","pid"=>"1","order"=>"3","desc"=>"bigapp","status"=>"1"),
												array('id'=>"9","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/7/6/0/76059c5c50fc81dcc32b9c6fbd722577.png","url"=>"http://192.168.180.23:8080/discuz/forum.php?mod=forumdisplay&fid=1","type"=>"3","pid"=>"1","order"=>"3","desc"=>"bigapp","status"=>"1"),
										),
								"func1"=>array(
												array('id'=>"1","title"=>"bigapp1","pic"=>"http://mobfile.youzu.com/Uploads_image/1/a/2/d/a2d48037df9c1dbeacdb3232ca2197b3.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"1","status"=>"1","desc"=>""),
												array('id'=>"2","title"=>"bigapp2","pic"=>"http://mobfile.youzu.com/Uploads_image/1/d/2/c/d2c5d626b2e2dbb705b277f92903a767.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"2","status"=>"1","desc"=>""),
												array('id'=>"3","title"=>"bigapp3","pic"=>"http://mobfile.youzu.com/Uploads_image/1/c/5/d/c5d514e2ff2a7d4c58ceed840e0253f0.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"5","status"=>"1","desc"=>""),
										),
								"func2"=>array(
												array('id'=>"4","title"=>"bigapp4","pic"=>"http://mobfile.youzu.com/Uploads_image/1/d/6/4/d6443b9c58e64aa3d640d269b23d444f.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"4","desc"=>"bigapp","status"=>"1"),
												array('id'=>"5","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/2/6/1/2610b00cd55a65d3580037c910f37e06.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"3","order"=>"3","desc"=>"bigapp","status"=>"1"),
												array('id'=>"6","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/3/2/7/327bef8b10441329007fb9dcc956be9f.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"3","order"=>"3","desc"=>"bigapp","status"=>"1"),
												array('id'=>"7","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/b/1/8/b184780602b05fd6df357984890b11f2.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"3","order"=>"3","desc"=>"bigapp","status"=>"1"),
												array('id'=>"8","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/c/a/1/ca107b8c4d6edd90271c652148aeb1e7.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"1","order"=>"3","desc"=>"bigapp","status"=>"1"),
												array('id'=>"9","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/7/6/0/76059c5c50fc81dcc32b9c6fbd722577.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"1","order"=>"3","desc"=>"bigapp","status"=>"1"),
										),
								"switch0"=>array("switch"=>1),
								"switch1"=>array("switch"=>1),
								"switch2"=>array("switch"=>1),
	);

	//自定义首页默认配置
	public static $newDefaultHome = array(
	    array(
			"type" => "1", //banner
			"sort" => "1",
			"enable" => "1",
			"setting" => array(
				array('id'=>"1","title"=>"bigapp1","pic"=>"http://mobfile.youzu.com/Uploads_image/25/b/c/1/bc1ac812cc7927213d65149df160cca7.png","url"=>"http://192.168.180.93:8080/discuz/forum.php?mod=forumdisplay&fid=2","type"=>"3","pid"=>"0","order"=>"1","flag"=>"1","desc"=>""),
				array('id'=>"2","title"=>"bigapp2","pic"=>"http://mobfile.youzu.com/Uploads_image/27/5/4/f/54f71d9ccc1d403ec90ffe9913bb967c.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"2","flag"=>"1","desc"=>""),
				array('id'=>"3","title"=>"bigapp3","pic"=>"http://mobfile.youzu.com/Uploads_image/33/6/0/4/6041a6f1ecb9c375083ce6ce606d6bfa.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"3","flag"=>"1","desc"=>""),
				array('id'=>"4","title"=>"bigapp4","pic"=>"http://mobfile.youzu.com/Uploads_image/22/d/a/b/dabd4a13566464ac912d962b5201329c.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"4","flag"=>"1","desc"=>""),
				array('id'=>"5","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/28/5/5/9/5594b24cadf974af82ddb25892e9ac3c.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"5","flag"=>"1","desc"=>""),
				array('id'=>"6","title"=>"bigapp6","pic"=>"http://mobfile.youzu.com/Uploads_image/32/d/7/b/d7be3c520b736af8fd6817ac6a89ebc4.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"6","flag"=>"1","desc"=>""),
			)
		),
		
		array(
			"type" => "2", //func
			"sort" => "2",
			"enable" => "1",
			"setting" => array(
				array('id'=>"1","title"=>"bigapp1","pic"=>"http://mobfile.youzu.com/Uploads_image/1/a/2/d/a2d48037df9c1dbeacdb3232ca2197b3.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"1","flag"=>"1","desc"=>""),
				array('id'=>"2","title"=>"bigapp2","pic"=>"http://mobfile.youzu.com/Uploads_image/1/d/2/c/d2c5d626b2e2dbb705b277f92903a767.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"2","flag"=>"1","desc"=>""),
				array('id'=>"3","title"=>"bigapp3","pic"=>"http://mobfile.youzu.com/Uploads_image/1/c/5/d/c5d514e2ff2a7d4c58ceed840e0253f0.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"5","flag"=>"1","desc"=>""),
			)
		),
		
		array(
			"type" => "3", //hot
			"sort" => "3",
			"enable" => "1",
			"setting" => array(
				array('id'=>"4","title"=>"bigapp4","pic"=>"http://mobfile.youzu.com/Uploads_image/1/d/6/4/d6443b9c58e64aa3d640d269b23d444f.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"0","order"=>"4","flag"=>"1","desc"=>"bigapp"),
				array('id'=>"5","title"=>"bigapp5","pic"=>"http://mobfile.youzu.com/Uploads_image/2/2/6/1/2610b00cd55a65d3580037c910f37e06.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"3","order"=>"3","flag"=>"1","desc"=>"bigapp"),
				array('id'=>"6","title"=>"bigapp6","pic"=>"http://mobfile.youzu.com/Uploads_image/2/3/2/7/327bef8b10441329007fb9dcc956be9f.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"3","order"=>"3","flag"=>"1","desc"=>"bigapp"),
				array('id'=>"7","title"=>"bigapp7","pic"=>"http://mobfile.youzu.com/Uploads_image/2/b/1/8/b184780602b05fd6df357984890b11f2.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"3","order"=>"3","flag"=>"1","desc"=>"bigapp"),
				array('id'=>"8","title"=>"bigapp8","pic"=>"http://mobfile.youzu.com/Uploads_image/2/c/a/1/ca107b8c4d6edd90271c652148aeb1e7.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"1","order"=>"3","flag"=>"1","desc"=>"bigapp"),
				array('id'=>"9","title"=>"bigapp9","pic"=>"http://mobfile.youzu.com/Uploads_image/2/7/6/0/76059c5c50fc81dcc32b9c6fbd722577.png","url"=>"http://bigapp.mob.com/","type"=>"1","pid"=>"1","order"=>"3","flag"=>"1","desc"=>"bigapp"),
			)
		),
		
		array(
			"type" => "4", //recomm
			"sort" => "4",
			"enable" => "1",
			"setting" => array(
				'type' => '1',//多图模式
				'data_src_cfg' => array(
					'type' => '2',
					'content_setting' => array(
							array('order' => '1', 'id' => '1,2,3', 'type'=> '1'),
							array('order' => '2', 'id' => '2,3', 'type'=> '2'),
					),
					'recomm_setting' => array(
							array('flag' => '1', 'order' => '0', 'module' => '最新帖子', 'title' => '最新', 'display_id' => "", 'forbidden_id' => ""),
							array('flag' => '1', 'order' => '1', 'module' => '热门帖子', 'title' => '热门', 'display_id' => "", 'forbidden_id' => ""),
							array('flag' => '1', 'order' => '2', 'module' => '精华帖子', 'title' => '精华', 'display_id' => "", 'forbidden_id' => ""),
					),
				),
		    )
		),
	);
	
	public static $thirdLogin = array(		
	'ShareTypeSinaWeibo' => 1,         /**< 新浪微博 */
	'ShareTypeTencentWeibo' => 2,      /**< 腾讯微博 */
	'ShareTypeDouBan' => 5,            /**< 豆瓣社区 */
	'ShareTypeQQSpace' => 6,           /**< QQ空间 */
	'ShareTypeRenren' => 7,            /**< 人人网 */
	'ShareTypeKaixin' => 8,            /**< 开心网 */
	'ShareTypePengyou' => 9,           /**< 朋友网 */
	'ShareTypeFacebook' => 10,         /**< Facebook */
	'ShareTypeTwitter' => 11,          /**< Twitter */
	'ShareTypeEvernote' => 12,         /**< 印象笔记 */
	'ShareTypeFoursquare' => 13,       /**< Foursquare */
	'ShareTypeGooglePlus' => 14,       /**< Google＋ */
	'ShareTypeInstagram' => 15,        /**< Instagram */
	'ShareTypeLinkedIn' => 16,         /**< LinkedIn */
	'ShareTypeTumblr' => 17,           /**< Tumbir */
    'ShareTypeMail' => 18,             /**< 邮件分享 */
	'ShareTypeSMS' => 19,              /**< 短信分享 */
	'ShareTypeAirPrint' => 20,         /**< 打印 */
	'ShareTypeCopy' => 21,             /**< 拷贝 */
    'ShareTypeWeixiSession' => 22,     /**< 微信好友 */
	'ShareTypeWeixiTimeline' => 23,    /**< 微信朋友圈 */
    'ShareTypeQQ' => 24,  /**< QQ */
    'ShareTypeInstapaper' => 25,       /**< Instapaper */
    'ShareTypePocket' => 26,           /**< Pocket */
    'ShareTypeYouDaoNote' => 27,       /**< 有道云笔记 */
    'ShareTypeSohuKan' => 28,          /**< 搜狐随身看 */
    'ShareTypePinterest' => 30,        /**< Pinterest */
    'ShareTypeFlickr' => 34,           /**< Flickr */
    'ShareTypeDropbox' => 35,          /**< Dropbox */
    'ShareTypeVKontakte' => 36,        /**< VKontakte */
    'ShareTypeWeixiFav' => 37,         /**< 微信收藏 */
    'ShareTypeYiXinSession' => 38,     /**< 易信好友 */
    'ShareTypeYiXinTimeline' => 39,    /**< 易信朋友圈 */
    'ShareTypeYiXinFav' => 40,         /**< 易信收藏 */
    'ShareTypeMingDao' => 41,          /**< 明道 */
    'ShareTypeLine' => 42,             /**< Line */
    'ShareTypeWhatsApp' => 43,         /**< Whats App */
    'ShareTypeKaKaoTalk' => 44,        /**< KaKao Talk */
    'ShareTypeKaKaoStory' => 45,       /**< KaKao Story */
    'ShareTypeOther' => -1,            /**< > */
    'ShareTypeAny' => 99               /**< 任意平台 */
	);
}
?>
