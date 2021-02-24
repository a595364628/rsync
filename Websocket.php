<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/24/21
 * Time: 10:49 AM
 */

namespace app\console;
use app\index\controller\Index;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Websocket extends Command {
    protected $server;
    protected function configure()
    {
        $this->setName('websocket:start')->setDescription('Start Web Socket Server!');
    }

    protected function execute(Input $input, Output $output)
    {
        $serv = new \swoole_server('0.0.0.0',9501);

        $serv->set(array('task_worker_num' => 4));

        $serv->on('connect', function ($serv, $fd){
            echo $fd."客户端已经连接进来了.\n";
        });
        $serv->on('receive', function($serv, $fd, $from_id, $data) {
            $task_id = $serv->task($data);
            echo "开始投递异步任务 id=$task_id\n";
        });

        $serv->on('task', function ($serv, $task_id, $from_id, $data) {
            echo "接收异步任务[id=$task_id]".PHP_EOL;
            $data = json_decode($data);
            post($data[0],$data[1]);
            $data = json_encode($data);
            $serv->finish("$data -> OK");
        });

        $serv->on('finish', function ($serv, $task_id, $data) {
            echo "异步任务[id=$task_id]完成".PHP_EOL;
        });
        $serv->start();
    }
}
