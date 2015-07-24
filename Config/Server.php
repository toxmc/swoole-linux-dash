<?php

/**
 * 统计server与web server配置
 * @author xmc
 */
namespace Config;

class Server
{
	/**
	 * 获取web server配置
	 * @return multitype:number string boolean
	 */
	public static function getWebServerConfig()
	{
		$config = array(
			'worker_num' => 4, // worker进程数量
			'max_request' => 1000, // 最大请求次数，当请求大于它时，将会自动重启该worker
			'dispatch_mode' => 1,
			'log_file' => 'data/web.log',
			'daemonize' => false, // 守护进程设置成true
		);
		return $config;
	}
}

