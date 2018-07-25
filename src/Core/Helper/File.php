<?php

/**
 * 判断目录是否存在
 */
if (!function_exists('dir_exists')) {
    /**
     * @param string $path 目录路径
     * @return bool
     */
    function dir_exists($path)
    {
        $f = true;
        if (file_exists($path) == false) {//创建目录
            if (mkdir($path, 0777, true) == false)
                $f = false;
            else if (chmod($path, 0777) == false)
                $f = false;
        }

        return $f;
    }
}

/**
 * 组装文件url路径
 */
if (!function_exists('file_url')) {
    function file_url($path, $is_cloud = false, $type = 'img')
    {
        if(empty($path)){
            return '';
        }

        $domain = !$is_cloud ? request()->getSchemeAndHttpHost() : Config::get('aliyun.oss.' . $type . '_domain');
        return $domain . $path;
    }
}

/**
 * 根据设置获取完整的url地址
 */
if (!function_exists('auto_url')) {
    function auto_url($path, $type = 'img')
    {
        if(empty($path)){
            return '';
        }

        //获取当前配置
        $config = config('domain');

        if ($config['file_domain_default'] === 'local') {
            if (!isset($config['local'][$type . '_domain'])) {
                throw new \App\Exceptions\ApiException('不存在域名');
            }
            $domain = $config['local'][$type . '_domain'];

            if (empty($domain)) {
                $domain = request()->getSchemeAndHttpHost();
            }

        } elseif ($config['file_domain_default'] === 'oss') {
            $config = config('aliyun.oss');

            if (!isset($config[$type . '_domain'])) {
                throw new \App\Exceptions\ApiException('不存在域名');
            }
            $domain = $config[$type . '_domain'];
        } else {
            throw new \App\Exceptions\ApiException('域名配置错误');
        }

        return $domain . $path;
    }
}

/**
 * 上传文件到云盘
 */
if (!function_exists('upload_to_cloud')) {
    function upload_to_cloud($file_path, $object)
    {
        $config = \Config::get('aliyun.oss');
        //上传到阿里云
        $access_id = $config['access_key_id'];
        $access_key = $config['access_key_secret'];
        $endpoint = $config['endpoint'];
        $bucket = $config['bucket'];

        $object = ltrim($object, '/');
        $oss_client = new \OSS\OssClient($access_id, $access_key, $endpoint);

        try {
            $oss_client->multiuploadFile($bucket, $object, $file_path);
        } catch (\OSS\Core\OssException $e) {
            throw new \App\Exceptions\ApiException('上传失败!', 'UPLOAD_ERROR');
        }

        //删除原文件
        unlink($file_path);

        return true;
    }
}

/**
 * 复制文件夹
 * @param string $src 源文件夹
 * @param string $dst 目标文件夹
 * @param bool $is_cover 是否覆盖
 */
if (!function_exists('copy_dir')) {
    function copy_dir($src, $dst, $is_cover = false)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    copy_dir($src . '/' . $file, $dst . '/' . $file);
                    continue;
                } else {
                    if (!$is_cover) {
                        if (file_exists($dst . '/' . $file)) {
                            return false;
                        }
                    }

                    $result = copy($src . '/' . $file, $dst . '/' . $file);
                    if (!$result) {
                        return false;
                    }
                }
            }
        }
        closedir($dir);
        return true;
    }
}
if (!function_exists('copy_stubs')) {
    function copy_stubs($src, $dst, $is_cover = false)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    copy_stubs($src . '/' . $file, $dst . '/' . $file);
                    continue;
                } else {
                    $dst_filename = str_replace('.stub', '.php', $file);
                    if (!$is_cover) {

                        if (file_exists($dst . '/' . $dst_filename)) {
                            return false;
                        }
                    }

                    $result = copy($src . '/' . $file, $dst . '/' . $dst_filename);
                    if (!$result) {
                        return false;
                    }
                }
            }
        }
        closedir($dir);
        return true;
    }
}

/**
 * 删除文件夹
 * @param string $dir 文件夹
 * @return bool
 */
if (!function_exists('del_dir')) {
    function del_dir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->deldir($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}


/**
     * 复制图片
     * @param $source_src 源图片地址
     * @param $target_src 目标图片地址
     * @param int $is_change 是否改变图片大小,0表示原图复制
     * @param int $width 改变图片的宽度
     * @param int $height 改变图片的高度
     */
if (!function_exists('copyImage')) {
    copyImage($source_src='', $target_src, $is_change = 0, $width = 300, $height = 300)
    {
        //源图的路径，可以是本地文件，也可以是远程图片
        Log::info('creatImage from source_src: ' . $source_src);

        //源图对象
        try {
            $src_image = imagecreatefromjpeg($source_src);
        } catch (\Exception $e) {
            Log::error('Failed when imagecreatefromjpeg($source_src), exception:' . $e->getMessage() . ' ,line:' . $e->getLine());
        }

        //不改变图片大小时，直接复制
        if (empty($is_change)) {
            imagejpeg($src_image, $target_src);
            return true;
        }

        //改变图片大小
        $src_width = imagesx($src_image);
        $src_height = imagesy($src_image);

        //生成等比例的缩略图
        $tmp_image_width = 0;
        $tmp_image_height = 0;
        if ($src_width / $src_height >= $width / $height) {
            $tmp_image_width = $width;
            $tmp_image_height = round($tmp_image_width * $src_height / $src_width);
        } else {
            $tmp_image_height = $height;
            $tmp_image_width = round($tmp_image_height * $src_width / $src_height);
        }

        $tmpImage = imagecreatetruecolor($tmp_image_width, $tmp_image_height);
        imagecopyresampled($tmpImage, $src_image, 0, 0, 0, 0, $tmp_image_width, $tmp_image_height, $src_width, $src_height);

        //添加白边
        $final_image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($final_image, 255, 255, 255);
        imagefill($final_image, 0, 0, $color);

        $x = round(($width - $tmp_image_width) / 2);
        $y = round(($height - $tmp_image_height) / 2);

        imagecopy($final_image, $tmpImage, $x, $y, 0, 0, $tmp_image_width, $tmp_image_height);

        imagejpeg($final_image, $target_src);

        return true;

    }
}
