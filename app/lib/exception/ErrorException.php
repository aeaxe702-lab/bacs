<?php

namespace app\lib\exception;


class ErrorException extends BaseException
{
    //HTTP 状态码 404,200 ...
    public $code = 200;
    //错误具体信息
    public $msg = '服务器异常';
    //自定义的错误码
    public $errorCode = 9999;
}