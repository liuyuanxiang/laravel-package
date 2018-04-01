<?php

namespace Yashon\Laravel\Core\Console;

use App\Exceptions\ApiException;
use Illuminate\Console\Command;

class CreateMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:migration {--table=} {--seeder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create migration file for all database table';

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
        $table_name = $this->option('table');

        $date_name = date('Y_m_d') . '_' . rand(100000, 999999);

        if (!$table_name) {
            $tables = array_map('reset', \DB::select('SHOW TABLES'));
            foreach ($tables as $v) {
                $this->createData($v, $date_name);
            }
        } else {
            $this->createData($table_name, $date_name);
        }
    }

    /**
     * 创建data
     * @param $table_name
     * @param $date_name
     */
    public function createData($table_name, $date_name)
    {
        $columns = \DB::select('SHOW FULL COLUMNS FROM `' . $table_name . '`');
        $pattern = '/(\w*)\((\d*)/';
        $sql = '';

        foreach ($columns as $v) {
            $str = '            ';

            //判断主键和自增
            if ($v->Extra == 'auto_increment') {   //主键并且是自增
                $str = '$table->increments(\'' . $v->Field . '\')';
            } elseif ($v->Key == 'PRI') {   //单纯是主键
                $str = '$table->primary(\'' . $v->Field . '\')';
            } else {
                //分析数据类型
                $is_length = preg_match($pattern, $v->Type, $matches);
                if ($is_length) {
                    switch ($matches[1]) {
                        case 'int':
                            $str = '$table->integer(\'' . $v->Field . '\')->length(' . $matches[2] . ')';
                            break;
                        case 'tinyint':
                            $str = '$table->tinyInteger(\'' . $v->Field . '\')->length(' . $matches[2] . ')';
                            break;
                        case 'mediumint':
                            $str = '$table->mediumInteger(\'' . $v->Field . '\')->length(' . $matches[2] . ')';
                            break;
                        case 'smallint':
                            $str = '$table->smallInteger(\'' . $v->Field . '\')->length(' . $matches[2] . ')';
                            break;
                        case 'bigint':
                            $str = '$table->bigInteger(\'' . $v->Field . '\')->length(' . $matches[2] . ')';
                            break;
                        case 'varchar':
                            $str = '$table->string(\'' . $v->Field . '\', ' . $matches[2] . ')';
                            break;
                        case 'char':
                            $str = '$table->char(\'' . $v->Field . '\', ' . $matches[2] . ')';
                            break;
                    }
                } else {
                    switch ($v->Type) {

                        //text类型
                        case 'text':
                            $str = '$table->text(\'' . $v->Field . '\')';
                            break;
                        case 'tinytext':
                            $str = '$table->text(\'' . $v->Field . '\')';
                            break;
                        case 'mediumtext':
                            $str = '$table->mediumText(\'' . $v->Field . '\')';
                            break;
                        case 'longtext':
                            $str = '$table->longText(\'' . $v->Field . '\')';
                            break;

                        //blob类型
                        case 'blob':
                        case 'mediumblob':
                        case 'longblob':
                        case 'tinyblob':
                            $str = '$table->binary(\'' . $v->Field . '\')';
                            break;

                        //date
                        case 'date':
                            $str = '$table->date(\'' . $v->Field . '\')';
                            break;
                        case 'datetime':
                            $str = '$table->dateTime(\'' . $v->Field . '\')';
                            break;
                        case 'time':
                            $str = '$table->time(\'' . $v->Field . '\')';
                            break;
                        case 'timestamp':
                            $str = '$table->timestamp(\'' . $v->Field . '\')';
                            break;
                    }
                }
            }

            if (empty($str)) {
                $this->error('暂不支持' . $v->Type . '类型!');
                exit;
            }

            //是否unsigned
            if (strstr($v->Type, "unsigned")) {
                $str .= '->unsigned()';
            }

            //添加注释信息
            if (!empty($v->Comment)) {
                $str .= '->comment("' . $v->Comment . '")';
            }

            //指定列的默认值
            if (isset($v->Default)) {
                $str .= '->default("' . $v->Default . '")';
            }

            //是否允许该列的值为NULL
            if ($v->Null == 'YES') {
                $str .= '->nullable()';
            }

            $sql .= $str . ';' . PHP_EOL . '            ';

        }

        //表注释
        $comment_db = \DB::select('show table status where NAME=\''.$table_name.'\'');
        if (!empty($comment_db[0]) && !empty($comment_db[0]->Comment)) {
            $sql .= '$table->comment = \'' . $comment_db[0]->Comment . '\';' . PHP_EOL . '            ';
        }

        $sql .= '$table->engine = \'' . $comment_db[0]->Engine . '\';';


        //查询数据
        $data = '';
        if($this->option('seeder')){
            $list = \DB::table($table_name)->get();
            if(!$list->isEmpty()){
                foreach($list as $val){
                    $data .= '        DB::table(\''.$table_name.'\')->insert(['.PHP_EOL;
                    foreach($val as $k => $v){
                        $data .= '            \''.$k.'\' => \''.$v.'\','.PHP_EOL;
                    }
                    $data .= '        ]);'.PHP_EOL.PHP_EOL;
                }
            }
        }

        //引用模板
        $template = file_get_contents(dirname(__FILE__) . '/stubs/migration/create_migration.stub');

        $class_name = 'Create' . ucfirst(camel_case($table_name)) . 'Table';

        $source = str_replace('{{content}}', $sql, $template);
        $source = str_replace('{{class_name}}', $class_name, $source);
        $source = str_replace('{{table_name}}', $table_name, $source);
        $source = str_replace('{{seeder}}', $data, $source);

        //保存
        $dir_path = database_path('migrations/');

        $file_path = $dir_path . '/' . $date_name . '_create_' . $table_name . '_table.php';

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
            $this->info($class_name . '添加migration成功');
        } else {
            $this->error($class_name . '添加migration失败');
        }
    }
}
