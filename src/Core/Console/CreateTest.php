<?php

namespace Yashon\Laravel\Core\Console;

use Illuminate\Console\Command;

class CreateTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test {test_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test file';

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
        $class_name = class_basename($arg['test_name']) . 'Test';

        //文件路径
        $file_path = base_path() . '/tests/Feature/' . $arg['test_name'] . 'Test.php';

        //文件目录路径
        $dir_path = dirname($file_path);

        //分析命名空间
        $name_space = 'Tests\Feature\\' . $arg['test_name'];
        $name_space = str_replace('/', '\\', substr($name_space, 0, strrpos($name_space, '/')));

        $template = file_get_contents(dirname(__FILE__) . '/stubs/test.stub');

        $source = str_replace('{{class_name}}', $class_name, $template);
        $source = str_replace('{{name_space}}', $name_space, $source);
        $source = str_replace('{{table}}', snake_case(class_basename($arg['test_name'])), $source);

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
            $this->info($class_name . '添加测试类成功');
        } else {
            $this->error($class_name . '添加测试类失败');
        }
    }
}
