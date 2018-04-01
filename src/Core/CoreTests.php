<?php

namespace Yashon\Laravel\Core;

use App\Exceptions\ApiException;


/**
 * 测试基类
 * Class Controller
 * @package Yashon\Core
 */
trait CoreTests
{

    protected $sessionToken;    //token

    protected $testUserId;      //调试testuser模式,用户id


    /**
     * 获取token
     * @return $this
     * @throws ApiException
     */
    public function getSessionToken()
    {
        if (empty($this->sessionToken)) {
            $response = $this->json('GET', '/api/init');

            if ($response->baseResponse->getStatusCode() != 200) {
                throw new ApiException('获取token失败!');
            }

            if (empty($token = $response->baseResponse->getOriginalContent()['token'])) {
                throw new ApiException('获取token失败!');
            }

            $this->sessionToken = $token;
        }

        return $this->sessionToken;
    }

    /**
     * 获取验证头部
     * @return array
     * @throws ApiException
     */
    public function getSessionHeader()
    {
        return [
            'Authorization' => $this->getSessionToken(),
            'X-ISAPI' => 1,
            'X-DEBUG' => 1
        ];
    }

    /**
     * 发送到接口
     * @param $method
     * @param $uri
     * @param int $usertest
     * @param array $data
     * @param array $headers
     * @return mixed
     */
    public function send($method, $uri, $usertest = 1, array $data = [], array $headers = [])
    {
        $url_data = array(
            'usertest' => $usertest
        );

        if (strtoupper($method) === 'GET') {
            $url_data=array_merge($url_data,$data);
            $data=[];
        }

        $uri=$uri.'?'.http_build_query($url_data);
        $headers=array_merge($headers,$this->getSessionHeader());

        return $this->json($method, $uri, $data, $headers);
    }

}



