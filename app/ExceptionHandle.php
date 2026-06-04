<?php
namespace app;

use app\lib\exception\BaseException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Log;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        if($e instanceof BaseException){
            //如果是自定义异常
            $msg        = $e -> msg;
            $errorCode  = $e -> errorCode;
        }else{
            if(env("APP_DEBUG",false)){
                return parent::render($request,$e);
            }else{
                $msg        = '不可描述的错误';
                $errorCode  = 9999;
                if(!$e instanceof HttpException){
                    Log::write($e -> getMessage(),'error');
                }
            }
        }
        $result = [
            'message'           => $msg,
            'error_code'    => $errorCode,
            'data'          => '',
            'time'        => time(),
        ];
        //将主体序列化为json格式
        header('Content-Type: application/json');
        return json($result);
    }
}
