<?php

namespace Yashon\Laravel\Core;

use App\Exceptions\ApiException;


/**
 * 控制器基类
 * Class Controller
 * @package Yashon\Core
 */
trait Controller
{

    public $verifyObj;   //验证类
    public $verifyData;    //验证成功后的数据

    public $helperObj = array();  //helper类

    /**
     * 应答数据api
     * @param array || Collection || Object $data
     * @param array $list
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($data = array(), $list = array(), $code = '200')
    {
        return response()->json([
            'status' => true,
            'error_msg' => 'ok',
            'error_code' => '',
            'data' => $data,
            'list' => $list
        ], $code, array(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 应答列表数据api
     * @param array $list
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseList($list = array())
    {
        return $this->response(array(), $list);
    }

    /**
     * 应答错误api
     * @param $error_msg $string 错误信息
     * @param string $error_id 错误id
     * @param int $status_code http状态码
     * @throws ApiException
     */
    public function responseError($error_msg, $error_id = 'ERROR', $status_code = 400)
    {
        response_error($error_msg, $error_id, $status_code);
    }

    /**
     * 验证
     * @param \Illuminate\Http\Request $request
     * @throws ApiException
     */
    public function verify(array $rule, $data = 'GET')
    {
        if (empty($verifyObj)) {
            $this->verifyObj = new \Yashon\Laravel\Verify\Verify();
        }

        $result = $this->verifyObj->check($rule, $data);
        $this->verifyData = $this->verifyObj->data;

        return $result;
    }

    /**
     * 验证ID
     * @param \Illuminate\Http\Request $request
     * @throws ApiException
     */
    public function verifyId($id)
    {
        if (empty($verifyObj)) {
            $this->verifyObj = new \Yashon\Laravel\Verify\Verify();
        }

        $result = $this->verifyObj->egnum($id);
        if (!$result) {
            throw new ApiException('id验证错误', 'id_ERROR', 422);
        }

        return true;
    }

    /**
     * 设置保存的数据
     * @param Object $model
     * @param array $data
     * @return object
     */
    public function setSaveData($model, $data)
    {
        foreach ($data as $key => $v) {
            $model->$key = $v;
        }

        return $model;
    }

    /**
     * 加载helper
     * @param $class_name
     * @return bool
     */
    public function loadHelper($class_name)
    {
        if (isset($this->helperObj[$class_name])) {
            return true;
        }

        load_helper($class_name);
        $this->helperObj[$class_name] = 1;

        return true;
    }

}



