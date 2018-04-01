<?php

namespace App\Exceptions;

use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class ApiException
 * @package App\Exceptions
 */
class ApiException extends \Exception
{
    /**
     * ApiException constructor.
     * @param string $message 错误信息
     * @param string $error_id 错误id
     * @param string $code http状态码
     */
    function __construct($message = '', $error_id = 'ERROR', $code = 400)
    {
        parent::__construct($message, $code);
        empty($error_id) || $this->error_id = $error_id;
    }

    /**
     * 获取错误id
     * @return string
     */
    public function getErrorId()
    {
        return empty($this->error_id) ? 'ERROR' : $this->error_id;
    }

    /**
     * Report the exception.
     *
     * @param  \Illuminate\Http\Request
     * @return void
     */
    public function render($request)
    {
        if ($request->header('X-ISAPI') == 1) {
            return $this->result($request);
        } else {
            /*$e = \Symfony\Component\Debug\Exception\FlattenException::create($this, $this->getCode());
            $handler = new SymfonyExceptionHandler(config('app.debug'));

            return SymfonyResponse::create($handler->getHtml($e), $e->getStatusCode(), $e->getHeaders());*/
            return view('Yashon-package::exception', [
                'error_msg' => $this->getMessage(),
                'debug' => config('app.debug') == 'true' ?  [
                    'type' => get_class($this),
                    'line' => $this->getLine(),
                    'file' => $this->getFile(),
                    'trace' => explode("\n", $this->getTraceAsString())
                ] : ''
            ]);


        }
    }

    /**
     * 返回结果
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function result($request)
    {

        $http_code = $this->getCode();
        $data = self::formatApiData($this);

        return response()->json($data, $http_code);
    }

    public static function formatApiData(\Exception $e)
    {
        $error_code = $e->getErrorId();
        $error_msg = $e->getMessage();
        $http_code = $e->getCode();

        $data = [
            'status' => false,
            'error_msg' => $error_msg,
            'error_code' => $error_code,
            'http_code' => $http_code,
            'data' => [],
            'list' => [],
        ];

        config('app.debug') == 'true' ? $data['debug'] = [
            'type' => get_class($e),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => explode("\n", $e->getTraceAsString())
        ] : true;

        return $data;
    }

    public static function formatData(\Exception $e)
    {
        $error_code = '';
        $error_msg = $e->getMessage();
        $http_code = $e->getStatusCode();

        $data = [
            'status' => false,
            'error_msg' => $error_msg,
            'error_code' => $error_code,
            'http_code' => $http_code,
            'data' => [],
            'list' => [],
        ];

        config('app.debug') == 'true' ? $data['debug'] = [
            'type' => get_class($e),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => explode("\n", $e->getTraceAsString())
        ] : $data['error_msg'] = 'Server Error';

        return $data;
    }


}