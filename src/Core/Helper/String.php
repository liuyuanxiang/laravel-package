<?php

if (!function_exists('first_charter_letter')) {
    /**
     * 取汉字的第一个字拼音的首字母
     * @param string $str 中文
     * @return bool
     */
    function first_charter_letter($str)
    {
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gbk', $str);
        $s2 = iconv('gbk', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;

        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';
        return '#';
    }
}

if (!function_exists('cut_html')) {
    /**
     * 去掉富文本标签
     * @param $content
     * @return string
     */
    function cut_html($content, $length = 100)
    {
        $content_01 = $content;//从数据库获取富文本content
        $content_02 = htmlspecialchars_decode($content_01);//把一些预定义的 HTML 实体转换为字符
        $content_03 = str_replace("&nbsp;", "", $content_02);//将空格替换成空
        $contents = strip_tags($content_03);//函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
        $con = mb_substr($contents, 0, $length, "utf-8");//返回字符串中的前100字符串长度的字符
        return $con;
    }
}

if (!function_exists('encode_hashids')) {
    /**
     * 加密数字id到hashid
     * @param $name
     * @param $id
     * @return bool|string
     */
    function encode_hashids($name, $id)
    {
        $config = config('hash.' . $name);

        if (empty($config)) {
            return false;
        }

        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

        //实例化Hashids
        switch ($config['level']) {
            case 1:
                $alphabet = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case 2:
                $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
                break;
            case 3:
                $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
                break;
        }

        $hashids = new \Hashids\Hashids($config['salt'], $config['length'], $alphabet);
        $str = $hashids->encode($id);
        unset($hashids);
        return $str;
    }
}

if (!function_exists('decode_hashids')) {
    /**
     * 解密数字id到hashid
     * @param $name
     * @param $hashid
     * @return bool
     */
    function decode_hashids($name, $hashid)
    {
        $config = config('hash.' . $name);

        if (empty($config)) {
            return false;
        }

        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

        //实例化Hashids
        switch ($config['level']) {
            case 1:
                $alphabet = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case 2:
                $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
                break;
            case 3:
                $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
                break;
        }

        $hashids = new \Hashids\Hashids($config['salt'], $config['length'], $alphabet);
        $ids = $hashids->decode($hashid);
        unset($hashids);
        if (!isset($ids[0])) {
            return false;
        }
        return $ids[0];
    }
}



