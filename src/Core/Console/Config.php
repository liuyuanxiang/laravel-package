<?php

namespace Yashon\Laravel\Core\Console;

use Illuminate\Console\Command;

class Config extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:env';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a .env file for the developing environment';

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
        $env_obj = new \Dotenv\Dotenv(base_path(),'.dev');
        $env_obj->load();

        $url = env('DEV_CONFIG_URL');
        if(empty($url)){
            $this->error('请在.env文件中配置 DEV_CONFIG_URL 配置项!');
            exit;
        }
        load_helper('Network');

        $str=http_get($url);

        if(!$str){
            $this->error('下载配置错误!');
            exit;
        }

        if(file_exists(base_path('.env'))){
            if(!unlink(base_path('.env'))){
                $this->error('原配置文件删除失败!');
                exit;
            }
        }

        if(file_put_contents(base_path('.env'),$str)){
            $this->info('下载配置完毕');
        }
        else{
            $this->error('保存配置文件失败!');
            exit;
        }

    }
}
