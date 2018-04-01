<?php

namespace Yashon\Laravel\Core\Console;

use Illuminate\Console\Command;

class CreateSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:seeder {table_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Seeder file include data for database table';

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
        $class_name = studly_case($arg['table_name']) . 'Seeder';

        //文件名称
        $file_name = $class_name . '.php';

        //文件路径
        $file_path = database_path() . '/seeds/' . $file_name;

        //文件目录路径
        $dir_path = dirname($file_path);

        //引用模板
        $template = file_get_contents(dirname(__FILE__) . '/stubs/seeder/create_seeder.stub');

        //查询数据
        $list = \DB::table($arg['table_name'])->get();
        $data = '';
        if(!$list->isEmpty()){
            foreach($list as $val){
                $data .= '        DB::table(\''.$arg["table_name"].'\')->insert(['.PHP_EOL;
                    foreach($val as $k => $v){
                        $data .= '            \''.$k.'\' => \''.$v.'\','.PHP_EOL;
                    }
                $data .= '        ]);'.PHP_EOL;
            }
        }

        $source = str_replace('{{class_name}}', $class_name, $template);
        $source = str_replace('{{data}}', $data, $source);
        //加载helper
        load_helper('File');

        //写入文件
        if (!dir_exists($dir_path)) {
            $this->error('目录' . $dir_path . ' 没有写入权限');
            exit;
        }

        //判断文件是否存在
        if (file_exists($file_path)) {
            $this->error('文件' . $file_path . ' 已存在,请先手动删除文件!');
            exit;
        }

        if (file_put_contents($file_path, $source)) {
            $this->info($class_name . '添加数据填充成功');
        } else {
            $this->error($class_name . '添加数据填充失败');
        }
    }
}
