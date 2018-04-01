<?php
namespace Yashon\Laravel\Core;

use App\Exceptions\ApiException;

class Debuger
{

    public $loggerObj; //日志对象

    public function __construct()
    {
        //新建一个log实例
        $logger = new \Monolog\Logger('Yashon-Debug');
        $file_name = 'debug-' . date('Y-m-d') . '.log';
        $log_path = storage_path('debug/' . $file_name);
        $stream = new \Monolog\Handler\StreamHandler($log_path, \Monolog\Logger::DEBUG);
        $dateFormat = "[Y-m-d H:i:s]";
        $output = "%datetime%-||-%message%" . PHP_EOL;
        $formatter = new \Monolog\Formatter\LineFormatter($output, $dateFormat);
        $stream->setFormatter($formatter);
        $logger->pushHandler($stream);

        $this->loggerObj = $logger;
        $this->run();
    }

    /**
     * 运行
     */
    public function run()
    {
        //监听数据库事件
        \DB::listen(function ($sql) {
            $args = func_get_args();
            $sql = vsprintf(str_replace(['%', '?'], ['%%', "'%s'"], $args[0]->sql), $args[0]->bindings) . ';';

            $log_data = [
                'sql' => $sql,
                'sql_origin' => $args[0]->sql,
                'bindings' => $args[0]->bindings,
                'time' => $args[0]->time
            ];

            $this->loggerObj->debug(json_encode($log_data));
        });
    }

    /**
     * 获取日志
     * @param int $limit
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function getLog($limit = 100)
    {
        $file_name = 'debug-' . date('Y-m-d') . '.log';
        $log_path = storage_path('debug/' . $file_name);

        $data = array();

        if (file_exists($log_path)) {

            if (!$fp = fopen($log_path, 'r')) {
                throw new ApiException('打开文件失败，请检查文件路径是否正确，路径和文件名不要包含中文');
            }

            while (!feof($fp)) {
                $buffer = fgets($fp, 16384);

                if($buffer){
                    $line = explode('-||-', $buffer);
                    $data[] = array(
                        'time' => trim(trim($line[0], '['), ']'),
                        'data' => json_decode(trim($line[1], '\n'), true)
                    );
                }
            }
            fclose($fp);
        }

        return view('Yashon-package::debug', ['list' => array_reverse($data)]);
    }

}



