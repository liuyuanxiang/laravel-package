<?php
namespace Yashon\Laravel\Verify;

use App\Exceptions\ApiException;
use Illuminate\Support\Arr;

/**
 * 验证类- for laravel
 * Class Verify
 */
class Verify
{

    public $data = array();
    private $temData = array();

    /**
     * 获取验证后的数据
     * @return array
     * @throws ApiException
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 验证
     * @param array $rule
     * @param string $data
     * @return bool
     * @throws ApiException
     */
    public function check(array $rule, $data = 'get')
    {
        if ($data === 'GET') {  //get数据
            $data = Request()->query->all();;
        } else if ($data === 'POST') {  //post数据
            $data = Request()->request->all();;
        } else if (!is_array($data)) {
            throw new ApiException('验证数据类型错误');
        }

        //初始化验证数组
        $check_arr = array();

        //初始化数据
        $this->data = array();

        //获取需要验证的字段
        foreach ($rule as $field => $v) {
            //分解规则
            $vail_type = explode('|', $v);

            //该验证字段是否是数组
            $field_array = explode('.', $field);

            if (count($field_array) > 1) {        //数组
                //如果没有设定可以不传,则判断该字段数组是否有传值
                if (!in_array('no_required', $vail_type) && !isset($data[$field_array[0]])) {
                    throw new ApiException($field . '验证字段不存在！', $field . '_ERROR', 422);
                }

                if ($field_array[1] !== '*') {    //二维数组
                    //如果没有设定可以不传,则判断该字段数组中的字段是否有传值
                    if (!in_array('no_required', $vail_type) && !isset($data[$field_array[0]][$field_array[1]])) {
                        throw new ApiException($field . '验证字段不存在！', $field . '_ERROR', 422);
                    }

                    //没有传,则跳过验证
                    if (!isset($data[$field_array[0]][$field_array[1]])) {
                        return true;
                    }

                    $vail_data = $data[$field_array[0]][$field_array[1]];

                    $check_arr[] = array(
                        'field' => $field,
                        'vail_type' => $vail_type,
                        'vail_data' => $vail_data
                    );

                    //$this->data[$field_array[0]]=

                } else {   //多维数组

                    //可不传,且没有传
                    if (in_array('no_required', $vail_type) && !isset($data[$field_array[0]])) {
                        continue;
                    }

                    //必传,但没有传
                    if (!in_array('no_required', $vail_type) && !isset($data[$field_array[0]])) {
                        throw new ApiException($field . '验证字段不存在！', $field . '_ERROR', 422);
                    }

                    //不是数组
                    if (isset($data[$field_array[0]]) && (!is_array($data[$field_array[0]]) || count($data[$field_array[0]]) < 1)) {
                        throw new ApiException($field . '验证字段不存在！', $field . '_ERROR', 422);
                    }

                    if (count($data[$field_array[0]]) > 0) {

                        //循环数据
                        foreach ($data[$field_array[0]] as $mutikey => $vv) {
                            //如果没有设定可以不传,则判断该字段数组中的字段是否有传值
                            if (!in_array('no_required', $vail_type) && !isset($vv[$field_array[2]])) {
                                throw new ApiException($field . '验证字段不存在！', $field . '_ERROR', 422);
                            }

                            //没有传,则跳过验证
                            if (!isset($vv[$field_array[2]])) {
                                continue;
                            }

                            $vail_data = $vv[$field_array[2]];

                            $check_arr[] = array(
                                'field' => $field,
                                'vail_type' => $vail_type,
                                'vail_data' => $vail_data
                            );

                            $this->temData[$field_array[0]][$mutikey][$field_array[2]] = $vail_data;
                        }

                        // 验证是否需要去重，需要去重却存在重复的则抛出错误
                        if (in_array('unique', $vail_type)) {
                            $unique_num = count(array_unique(array_column($data[$field_array[0]], $field_array[2])));
                            $now_num = count($data[$field_array[0]]);
                            if ($unique_num != $now_num) {
                                throw new ApiException($field . '验证字段传值不唯一！', $field . '_ERROR', 422);
                            }
                        }
                    }

                }
            } else {      //不是数组
                //如果没有设定可以不传,则判断该字段是否有传值
                if (!in_array('no_required', $vail_type) && !isset($data[$field])) {
                    throw new ApiException($field . '验证字段不存在！', $field . '_ERROR', 422);
                }

                //没有传,则跳过验证
                if (!isset($data[$field])) {
                    continue;
                }

                $vail_data = $data[$field];

                $check_arr[] = array(
                    'field' => $field,
                    'vail_type' => $vail_type,
                    'vail_data' => $vail_data
                );
            }

            if (count($check_arr) > 0) {
                foreach ($check_arr as $key => $vv) {
                    $set_key = '';
                    $set_data = '';
                    $this->secondCheck($vv['field'], $vv['vail_type'], $vv['vail_data']);

                    //是否数据键值
                    $field_array = explode('.', $vv['field']);

                    if (count($field_array) > 1) {        //数组
                        if ($field_array[1] !== '*') {    //二维数组
                            $set_key = $vv['field'];
                            $set_data = $vv['vail_data'];
                        } else {
                            //尝试处理多维数组数据
                            if (isset($this->temData[$field_array[0]])) {
                                $set_key = $field_array[0];
                                $set_data = $this->temData[$field_array[0]];
                            }
                        }
                    } else {
                        $set_key = $vv['field'];
                        $set_data = $vv['vail_data'];
                    }

                    Arr::set($this->data, $set_key, $set_data);
                }
            }
        }

        return true;
    }

    /**
     * second check
     * @param $field
     * @param $vail_type
     * @param $vail_data
     * @return bool
     * @throws ApiException
     */
    public function secondCheck($field, $vail_type, $vail_data)
    {
        //判断是否可以空,仅限空字符串
        if (in_array('can_null', $vail_type) && $vail_data === '') {
            return true;
        } elseif (!in_array('can_null', $vail_type) && $vail_data === '') {
            throw new ApiException($field . '验证字段不能为空！', $field . '_ERROR', 422);
        }

        //按顺序验证规则
        foreach ($vail_type as $key => $item) {
            if ($item === 'no_required' || $item === 'can_null' || $item === 'unique') {
                continue;
            }

            //继续分解vail_type
            $vail_type_arr = explode(':', $item);
            $fun = $vail_type_arr[0];
            $vail_type_arr[0] = $vail_data;

            if ($fun === '') continue;

            $result = call_user_func_array(array($this, $fun), $vail_type_arr);
            //func_get_args();

            if (!$result) {
                throw new ApiException($field . '字段验证错误！', $field . '_ERROR', 422);
            }
        }

        return true;
    }


    /**
     * 验证正整数
     * @param string $value 值
     * @param int $min 最小值
     * @param int $max 最大值
     * @return bool
     */
    public function egnum($value, $min = null, $max = null)
    {
        if (!preg_match('/^\+?[1-9]\d*$/', $value)) {
            return false;
        }

        //判断最小值范围
        if (!($min === null || $min === 'null')) {
            if (intval($value) < $min) {
                return false;
            }
        }

        //判断最小值范围
        if (!($max === null || $min === 'null')) {
            if (intval($value) > $max) {
                return false;
            }
        }

        return true;
    }

    /**
     * 是否数字
     * @param string $value 值
     * @param int $min 最小值
     * @param int $max 最大值
     * @param bool $is_integer 是否为整数
     * @return bool
     */
    public function num($value, $min = null, $max = null, $is_integer = false)
    {
        if (!is_numeric($value)) {
            return false;
        }

        //判断最小值范围
        if (!($min === null || $min === 'null')) {
            if (intval($value) < $min) {
                return false;
            }
        }

        //判断最小值范围
        if (!($max === null || $max === 'null')) {
            if (intval($value) > $max) {
                return false;
            }
        }

        //判断整数
        if ($is_integer) {
            if (intval($value) != $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * 验证邮箱
     * @param string $value 值
     * @param int $minLen 最小长度
     * @param int $maxLen 最长长度
     * @return bool
     */
    public function email($value, $minLen = 6, $maxLen = 60)
    {
        $match = '/^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/i';

        return (strlen($value) >= $minLen && strlen($value) <= $maxLen && preg_match($match, $value)) ? true : false;
    }

    /**
     * 验证金额
     * @param string $value
     * @return boolean
     */
    public function money($value)
    {
        $match = '/^(([1-9]\d{0,9})|0)(\.\d{1,2})?$/';
        return preg_match($match, $value);
    }

    /**
     * 验证手机
     * @param string $value
     * @return boolean
     */
    public function mobile($value)
    {
        $match = '/^(0)?1[3|4|5|6|7|8|9]([0-9]){9}$/';
        return preg_match($match, $value);
    }

    /**
     * 验证IP
     * @param string $value
     * @return boolean
     */
    public function ip($value)
    {
        $match = '/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/';
        return preg_match($match, $value);
    }

    /**
     * 验证mask地址
     * @param string $value
     * @return boolean
     */
    public function mask($value)
    {
        $match = '/^(254|252|248|240|224|192|128|0)\.0\.0\.0$|^(255\.(254|252|248|240|224|192|128|0)\.0\.0)$|^(255\.255\.(254|252|248|240|224|192|128|0)\.0)$|^(255\.255\.255\.(254|252|248|240|224|192|128|0))$/';
        return preg_match($match, $value);
    }

    /**
     * 验证身份证号码
     * @param string $value
     * @return boolean
     */
    public function idcard($value)
    {
        if (strlen($value) > 18) {
            return false;
        }

        if (!preg_match('/^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/', $value)) {
            return false;
        }

        return true;
    }

    /**
     * 验证URL
     * @param string $value
     * @return boolean
     */
    public function url($value)
    {
        $result = parse_url($value);
        return $result ? true : false;
    }

    /**
     * 验证邮政编码
     * @param string $value
     * @return boolean
     */
    public function zcode($value)
    {
        $match = '/^([0-9]{5})(-[0-9]{4})?$/i';
        return preg_match($match, $value);
    }

    /**
     * 验证域名
     * @param string $value
     * @return boolean
     */
    public function domain($value)
    {
        $match = '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
        return preg_match($match, $value);
    }

    /**
     * 验证时间
     */
    public function date($str)
    {
        return strtotime($str) !== false;
    }

    /**
     * 验证时间戳
     */
    public function timestamp($time)
    {
        if (!preg_match('/^\d{10}$/', $time)) {
            return false;
        }
        return strtotime(date('Y-m-d H:i:s', $time)) !== false;
    }

    /**
     * 验证营业执照
     */
    public function buscode($value)
    {
        return preg_match('/^\d{14}$/', $value);
    }

    /**
     * 包含验证
     * @param string $value
     * @param array $match
     * @return number
     */
    public function in($value)
    {
        $data = func_get_args();
        unset($data[0]);

        return in_array($value, $data);
    }

    /**
     * 自定义验证
     * @param string $value
     * @param string $match 正则表达式
     * @return number
     */
    public function reg($value, $match)
    {
        return preg_match($match, $value);
    }

    /**
     * @param string $value 值
     * @param int $minLen 最小长度
     * @param int $maxLen 最长长度
     */
    public function string($value, $min = null, $max = null)
    {
        //判断最小值范围
        if (!($min === null || $min === 'null')) {
            if (mb_strlen($value) < $min) {
                return false;
            }
        }

        //判断最小值范围
        if (!($max === null || $min == 'null')) {
            if (mb_strlen($value) > $max) {
                return false;
            }
        }
        return true;
    }


}