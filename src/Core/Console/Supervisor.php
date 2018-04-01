<?php

namespace Yashon\Laravel\Core\Console;

use Illuminate\Console\Command;

class Supervisor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supervisor {action} {--supervisor_path=} {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Config the supervisor';

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
        $action = $this->argument('action');

        switch ($action) {
            case 'config':
                $this->setConfig();
                break;
            case 'start' :
                $this -> startService();
                break;
            case 'restart' :
                $this -> restartService();
                break;
            case 'stop' :
                $this -> stopService();
                break;
            case 'create' :
                $this -> createConfig();
                break;
        }

    }

    /**
     *  设置配置
     */
    public function setConfig()
    {
        //获取配置
        $obj = new \App\Supervisor\Config();
        $list = $obj->supervisor;

        if(!$list){
            $this -> error('没有配置文件操作!');
            exit;
        }

        $source_supervistor_path = app_path('Supervisor/');

        if(empty($this->option('supervisor_path'))){
            $supervistor_path = '/etc/supervisor/conf.d/'.config('app.name').'/';
        }
        else{
            $supervistor_path = $this->option('supervisor_path');
        }

        foreach ($list as $v) {
            $is_exist = 0;

            $supervistor_file = $supervistor_path . $v . '.conf';       //目标文件路径文件名
            $source_supervistor_file = $source_supervistor_path . $v . '.conf';     //源文件路径文件名

            //如果已经配置了,则删除
            if (file_exists($supervistor_file)) {
                $is_exist = 1;

                if (unlink($supervistor_file)) {
                    //$this->info('删除原配置' . $supervistor_file . ' -> 成功!');
                } else {
                    $this->error('删除原配置' . $supervistor_file . ' -> 失败!');
                    exit;
                }
            }

            //替换artisan路径
            $str = file_get_contents($source_supervistor_file);
            $str = str_replace('artisan', base_path('artisan'), $str);

            //替换进程数
            $num_procs = env('SUPERVISOR_'.strtoupper($v).'_NUMPROCS');
            if(empty($num_procs)){
                $num_procs = 1;
            }
            $get_config_procs = preg_match('/.*numprocs=\d/',$str,$match);
            if($get_config_procs > 0){
                $str = str_replace($match[0], 'numprocs='.$num_procs, $str);
            }

            //添加日志地址
            $str .= 'stdout_logfile='.storage_path('supervisor/').$v.'.log';

            $copy_text = $is_exist == 1 ? '覆盖' : '创建';

            load_helper('File');

            //判断目录是否生成
            dir_exists(dirname($supervistor_file));

            //复制配置
            if (file_put_contents($supervistor_file, $str)) {
                $this->info($copy_text . '配置' . $source_supervistor_file . ' -> ' . $supervistor_file . ' -> 成功!');
            } else {
                $this->error($copy_text . '配置' . $source_supervistor_file . ' -> ' . $supervistor_file . ' -> 失败!');
                exit;
            }
        }
    }

    /**
     *  开启服务
     */
    public function startService()
    {
        passthru('service supervisor start');
    }

    /**
     *  重启服务
     */
    public function restartService()
    {
        passthru('service supervisor restart');
    }

    /**
     *  停止
     */
    public function stopService()
    {
        passthru('service supervisor stop');
    }

    /**
     *  创建配置
     */
    public function createConfig()
    {
        $file_name = $this->option('name');
        if(empty($file_name)){
            $this->error('请输入要创建的文件名');
            exit;
        }

        //模板路径
        $temp_path = dirname(__FILE__) . '/stubs/supervisor.conf';

        //目标路径
        $to_path = app_path('Supervisor/'.$file_name.'.conf');

        if(copy($temp_path,$to_path)){
            $this ->info('创建配置文件成功 -> '.$to_path);
        }
        else{
            $this ->error('创建配置文件成功 -> '.$to_path);
        }

    }

}
