<?php
return array(
	//数据库配置信息
	'DB_TYPE'   => 'mysql', // 数据库类型
	'DB_HOST'   => 'localhost', // 服务器地址
	'DB_NAME'   => 'yiyaotong', // 数据库名
	'DB_USER'   => 'root', // 用户名
	'DB_PWD'    => 'root', // 密码
	'DB_PORT'   => 3306, // 端口
	'DB_PREFIX' => 'tp_', // 数据库表前缀 

	'APP_GROUP_LIST' => 'Api,User,Wap,System,Daili', //项目分组设定
	'DEFAULT_GROUP'  => 'User', //默认分组	

	//'OUTPUT_ENCODE'         =>  true, 			//页面压缩输出

	/*定义模版标签*/
	'TMPL_L_DELIM'   		=>'<{',			//模板引擎普通标签开始标记
	'TMPL_R_DELIM'			=>'}>',				//模板引擎普通标签结束标记

	//'TMPL_FILE_DEPR'=>'_',			//分组模板
	'DEFAULT_THEME'=>'default',
	
	'LOAD_EXT_CONFIG' => 'info,upfile,route', // 加载扩展配置文件
	'SHOW_PAGE_TRACE'=> false,                //显示页面调试按钮
	'APP_AUTOLOAD_PATH'     =>'@.ORG'
	


);
?>