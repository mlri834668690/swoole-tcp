<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2021/7/2
 * Time: 11:06
 * php swoole-tcp.php
 *
 */
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $hexstr = '7e 35 00 00 3c 00 00 00 00 00 03 31 31 31 1f e2 7f';
    $begin = hex2bin('7e');
    $end = hex2bin('7f');
    writeLog('log');
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('192.168.1.63', 9501, 0.5)) {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    //$str = $begin.'111'.$end;
    $str = hexstring($hexstr);
    $client->send("$str");
    while (true) {
        $data = $client->recv();
        if (strlen($data) > 0) {
            echo $data;
            $client->send(time() . PHP_EOL);
        } else {
            if ($data === '') {
                // 全等于空 直接关闭连接
                echo 'recv'."\n";
                //$client->close();
                //break;
            } else {
                if ($data === false) {
                    // 可以自行根据业务逻辑和错误码进行处理，例如：
                    // 如果超时时则不关闭连接，其他情况直接关闭连接
                    if ($client->errCode !== SOCKET_ETIMEDOUT) {
                        echo 222;
                        $client->close();
                        break;
                    }else{
                        echo $client->errCode;
                        $client->close();
                        break;
                    }
                } else {
                    echo 333;
                    $client->close();
                    break;
                }
            }
        }
        \Co::sleep(1);
    }
});

/**
 * 写入日志
 */
function writeLog($data)
{
    //$data = array_merge(['date' => date('Ymd H:i:s')], $_GET, $_POST, $_SERVER);
    $logs = '';
    /*foreach ($data as $key => $val) {
        $logs .= $key. ':'. $val. ' --- ';
    }*/
    if(!is_dir(__DIR__."/../runtime/log/". date('Ym'))){
        mkdir(__DIR__."/../runtime/log/". date('Ym') , 0777 ,true);
        chmod(__DIR__."/../runtime/log/". date('Ym') ,0777);
    }
    $logPath = __DIR__."/../runtime/log/". date('Ym'). '/'. date('d'). '_access.log';
    // 通过协程的方法写入日志
    go(function() use ($logPath, $data){
        file_put_contents($logPath, $data. PHP_EOL, FILE_APPEND);
    });
}

function hexstring($str){
    $str = explode(' ',$str);
    $result = '';
    foreach ($str as $k=>$v){
        $result .= @hex2bin($v);
    }
    return $result;
}