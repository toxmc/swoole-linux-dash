<?php

/**
 * Web uptime 监控
 * @author xmc
 * 2015-6-12
 */

$uptime_path = '/usr/bin/uptime'; //uptime命令绝对路径
$host = "0.0.0.0";	//代表监听全部地址 
$port = 8880;	//监听端口号

/**
 * MasterPid命令时格式化输出
 * ManagerPid命令时格式化输出
 * WorkerId命令时格式化输出
 * WorkerPid命令时格式化输出
 * @var int
 */
$_maxMasterPidLength = 12;
$_maxManagerPidLength = 12;
$_maxWorkerIdLength = 12;
$_maxWorkerPidLength = 12;

/**
 * 创建一个websocket服务器
 * 端口8888
 */
$table = new swoole_table(4);
$table->column('cmd', swoole_table::TYPE_STRING, 32);
$table->create();
$table->set('uptime', array('cmd' => $uptime_path));

$server = new swoole_websocket_server($host, $port);
$server->table = $table;	//将table保存在serv对象上

/**
 * websocket server配置
 */
$server->set (array(
		'worker_num' => 1,		//worker进程数量
		'daemonize' => false,	//守护进程设置成true
		'max_request' => 1000,	//最大请求次数，当请求大于它时，将会自动重启该worker
		'dispatch_mode' => 1
));

/**
 * websocket server start
 * 成功后回调
 */
$server->on('start', function ($serv) use($_maxMasterPidLength, $_maxManagerPidLength, $_maxWorkerIdLength, $_maxWorkerPidLength) {
	echo "\033[1A\n\033[K-----------------------\033[47;30m SWOOLE \033[0m-----------------------------\n\033[0m";
	echo 'swoole version:' . swoole_version() . "          PHP version:".PHP_VERSION."\n";
	echo "------------------------\033[47;30m WORKERS \033[0m---------------------------\n";
	echo "\033[47;30mMasterPid\033[0m", str_pad('', $_maxMasterPidLength + 2 - strlen('MasterPid')),
		 "\033[47;30mManagerPid\033[0m", str_pad('', $_maxManagerPidLength + 2 - strlen('ManagerPid')),
		 "\033[47;30mWorkerId\033[0m", str_pad('', $_maxWorkerIdLength + 2 - strlen('WorkerId')),
		 "\033[47;30mWorkerPid\033[0m", str_pad('', $_maxWorkerPidLength + 2 - strlen('WorkerPid')),"\n";
});


/**
 * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数。
 */
$server->on('open', function (swoole_websocket_server $server, $request) {
	$fd = $request->fd;
	echo "server: handshake success with fd{$fd}\n";
	$server->push($fd, "nowtime -----------Linetime---------- ---users-- -----load average----\n");
	$server->push($fd, "nowtime,days,time,users,one,five,fifteen\n");
	$server->tick(1000, function($id) use($server, $fd){
		$data = $server->table->get('uptime');
		$conn = $server->connection_info($fd);
		if (!empty($conn)) {
			$str = exec($data['cmd'],$string);
			$nowtime = substr($str, 0, strpos($str,'up'));
			$str = str_replace($nowtime, '', $str);
			$str = str_replace('up', '', $str);
			$str = str_replace('days', '', $str);
			$str = str_replace('users', '', $str);
			$str = str_replace('load average:', '', $str);
			$str = $nowtime.','.$str;
			if ($conn) {
				$server->push($fd, $str);
			}
		}else{
			$server->clearTimer($id);
		}
	});
	
});

/**
 * 当服务器收到来自客户端的数据帧时会回调此函数。
 */
$server->on('message', function (swoole_websocket_server $server, $frame) {
	echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
	$server->push($frame->fd, "this is server");
});

/**
 * 当客户端关闭的时候调用
 */
$server->on('close', function ($ser, $fd) {
	echo "client {$fd} closed\n";
});

/**
 * 当worker 启动的时候调用
 */
$server->on('workerStart',function ($serv, $worker_id) use($_maxMasterPidLength, $_maxManagerPidLength, $_maxWorkerIdLength, $_maxWorkerPidLength) {
	echo str_pad($serv->master_pid, $_maxMasterPidLength+2),
		 str_pad($serv->manager_pid, $_maxManagerPidLength+2),
		 str_pad($serv->worker_id, $_maxWorkerIdLength+2), 
		 str_pad($serv->worker_pid, $_maxWorkerIdLength), "\n";;
});

$server->start();