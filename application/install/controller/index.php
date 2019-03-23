<?php
 class indexController extends Controller { public function init() { if (is_file(DATA_PATH . '/install.lock')) { $this->error('您已安装过本程序，继续安装需要删除data目录下的install.lock文件', U('index.index.index'),0); } $this->steplist = array( 'index' => '欢迎使用', 'base' => '初始化设置', 'novel' => '系统设置', 'success' => '安装成功', ); $this->step=$_GET['a']; session_start(); } public function indexAction() { if (!is_writable(PT_ROOT) || (is_dir(!is_writeable((CACHE_PATH))) && !is_writeable((CACHE_PATH))) || !is_writeable(APP_PATH.'/common/config.php')){ $this->show('程序没有写入权限，安装终止'); exit; } if (!F(CACHE_PATH.'/test.txt','test') || F(CACHE_PATH.'/test.txt')!=='test' || !F(CACHE_PATH.'/test.txt',null)){ $this->error('没有写入权限，安装终止',0,0); } $this->userlist=array(); $this->display('index'); } public function baseAction() { if (IS_POST){ $zym_8=$zym_9=array(); if(phpversion() < '5.3.0'){ $zym_9[]='您的服务器PHP版本过低，无法正常使用本系统！'; } if(!class_exists('memcache')){ $zym_9[]='请您安装memcached服务及php的memcache扩展，无法正常使用本系统！'; } if(!in_array('pdo_mysql',array_change_key_case(get_loaded_extensions()))){ $zym_9[]='请您安装php的Pdo_mysql扩展，无法正常使用本系统！'; } $zym_10=curl_version(); if($zym_10['version']<'7.19'){ $zym_8[]='您的服务器curl版本过低，无法使用后台监控采集'; } if (!function_exists( "gd_info" )){ $zym_8[]='您的服务器不支持GD库，无法使用图片相关功能'; } if (ini_get("safe_mode")){ $zym_8[]='您的服务器开启了safe_mode，部分功能可能会受限制'; } if (!function_exists("curl_init")){ if (!ini_get('allow_url_fopen') || (!function_exists('file_get_contents') && !function_exists('fsocketopen') && !function_exists('fpsocketopen'))){ $zym_9[]='您的服务器不支持采集功能，无法正常使用本系统！'; }else{ $zym_8[]='您的服务器不支持Curl，我们建议您打开Curl获取更好的性能'; } } if (!F(PT_ROOT.'/test.txt','test') || F(PT_ROOT.'/test.txt')!=='test' || !F(PT_ROOT.'/test.txt',null)){ $zym_9[]='目录：'.PT_ROOT.'不可写，请检查权限！'; } if (!F(DATA_PATH.'/test.txt','test') || F(DATA_PATH.'/test.txt')!=='test' || !F(DATA_PATH.'/test.txt',null)){ $zym_9[]='数据目录：'.DATA_PATH.'不可写，请检查权限！'; } if (!F(CACHE_PATH.'/test.txt','test') || F(CACHE_PATH.'/test.txt')!=='test' || !F(CACHE_PATH.'/test.txt',null)){ $zym_9[]='缓存目录：'.CACHE_PATH.'不可写，请检查权限！'; } if (!F(PT_ROOT . '/public/test.txt','test') || F(PT_ROOT . '/public/test.txt')!=='test' || !F(PT_ROOT . '/public/test.txt',null)){ $zym_9[]='广告JS存放目录：'.PT_ROOT . '/public/不可写，请检查权限！'; } if (!is_writeable(APP_PATH.'/common/config.php')){ $zym_9[]='配置文件：'.APP_PATH.'/common/config.php'.'不可写，请检查权限！'; } $this->error=$zym_9; $this->warning=$zym_8; } $this->display('base'); } public function settingAction() { if (IS_POST){ $zym_11['sitename']=I('post.sitename','str'); $zym_11['siteurl']=I('post.siteurl','url'); $zym_11['adminuser']=I('post.adminuser','username'); $zym_11['adminpwd']=I('post.adminpwd','str'); $zym_11['sitename']=$zym_11['sitename']?$zym_11['sitename']:PRONAME; $zym_11['siteurl']=$zym_11['siteurl']?$zym_11['siteurl']:PT_URL; $zym_11['adminuser']=$zym_11['adminuser']?$zym_11['adminuser']:'admin'; $zym_11['adminpwd']=$zym_11['adminpwd']?$zym_11['adminpwd']:'admin'; $_SESSION['install']=$zym_11; } $this->display('setting'); } public function successAction() { if (IS_POST){ C($_POST); if (!@mysql_connect($_POST['mysql_master_host'].':'.$_POST['mysql_master_port'],$_POST['mysql_master_user'],$_POST['mysql_master_pwd'])){ $this->error('数据库帐号密码错误'); } if (!isset($_SERVER['HTTP_BAE_LOGID']) && !mysql_fetch_assoc(mysql_query("SELECT `SCHEMA_NAME` FROM `INFORMATION_SCHEMA`.`SCHEMATA` WHERE `SCHEMA_NAME`='{$_POST['mysql_master_name']}' LIMIT 0, 1;"))){ if (!mysql_query("CREATE DATABASE IF NOT EXISTS `{$_POST['mysql_master_name']}` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;")){ $this->error('数据库不存在，尝试创建失败！'); } } $zym_7=F(APP_PATH.'/install/data/mysql.sql'); if (!$zym_7){ $this->error('读取数据库安装信息失败，请检查程序完整性',0,0); } $zym_7=strtr($zym_7,array( '{adminuser}'=>$_SESSION['install']['adminuser'], '{salt}'=>substr(md5(NOW_TIME),0,6), '{adminpwd}'=>md5(md5($_SESSION['install']['adminpwd']).substr(md5(NOW_TIME),0,6)), '{installsitename}'=>$_SESSION['install']['sitename'], '{installsiteurl}'=>$_SESSION['install']['siteurl'], '{mysql_driver}'=>C('mysql_driver'), '{mysql_prefix}'=>C('mysql_prefix'), '{mysql_master_host}'=>C('mysql_master_host'), '{mysql_master_port}'=>C('mysql_master_port'), '{mysql_master_name}'=>C('mysql_master_name'), '{mysql_master_user}'=>C('mysql_master_user'), '{mysql_master_pwd}'=>C('mysql_master_pwd'), )); $zym_5=new model(); $zym_6=explode(";\n",$zym_7); foreach($zym_6 as $zym_7){ $zym_5->execute(trim($zym_7).';'); } M('config')->createConfigFile(); F(CACHE_PATH,null); F(DATA_PATH,null); F(DATA_PATH.'/install.lock','PTcms Install Lock File!'); $this->admin=$_SESSION['install']; $_SESSION['install']=null; } $this->display('success'); } }
?>