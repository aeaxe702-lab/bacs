<?php

namespace app\lib\exception;

class TokenException extends BaseException
{
    //HTTP 状态码 404,200 ...
    public $code = 200;
    //错误具体信息
    public $msg = 'Token不合法';
    //自定义的错误码
    public $errorCode = 70000;
}