<?php

namespace Yashon\Laravel\Core\Console;

use Illuminate\Console\Command;

class CreateLogic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:logic {logic_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create logic file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //获取参数
        $arg = $this->arguments();

        //类名
        $class_name = class_basename($arg['logic_name']) . 'Logic';

        // 方法名
        $list_name = 'get' . class_basename($arg['logic_name']) . 'List';
        $get_one_name = 'getOne' . class_basename($arg['logic_name']);
        $add_name = 'add' . class_basename($arg['logic_name']);
        $update_name = 'update' . class_basename($arg['logic_name']);
        $delete_name = 'delete' . class_basename($arg['logic_name']);

        $model_name = basename($arg['logic_name']);

        $var_model_name = snake_case($model_name);
        $model_class = str_replace('/','\\',$model_name);

        //文件路径
        $file_path = app_path() . '/Logic/' . $arg['logic_name'] . 'Logic.php';

        //文件目录路径
        $dir_path = dirname($file_path);

        //分析命名空间
        if ($class_name == $arg['logic_name'] . 'Logic') {
            $name_space = 'App\Logic';
        } else {
            $name_space = 'App\Logic\\' . $arg['logic_name'];
            $name_space = str_replace('/', '\\', substr($name_space, 0, strrpos($name_space, '/')));
        }

        $template = file_get_contents(dirname(__FILE__) . '/stubs/logic.stub');

        $source = str_replace('{{class_name}}', $class_name, $template);
        $source = str_replace('{{name_space}}', $name_space, $source);
        $source = str_replace('{{list_name}}', $list_name, $source);
        $source = str_replace('{{get_one_name}}', $get_one_name, $source);
        $source = str_replace('{{add_name}}', $add_name, $source);
        $source = str_replace('{{update_name}}', $update_name, $source);
        $source = str_replace('{{delete_name}}', $delete_name, $source);
        $source = str_replace('{{logic_name}}', $arg['logic_name'], $source);
        $source = str_replace('{{model_name}}', $var_model_name, $source);
        $source = str_replace('{{model_class}}', $model_class, $source);

        //加载helper
        load_helper('File');

        //写入文件
        if (!dir_exists($dir_path)) {
            $this->error('目录' . $dir_path . ' 没有写入权限');
            exit;
        }

        //判断文件是否存在
        if (file_exists($file_path)) {
            $this->error('文件' . $file_path . ' 已存在');
            exit;
        }

        if (file_put_contents($file_path, $source)) {
            $this->info($class_name . '添加Logic成功');
        } else {
            $this->error($class_name . '添加Logic失败');
        }
    }
}
