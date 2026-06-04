<?php

namespace app\lib\exception;

class AppConfigException extends BaseException
{
    // HTTP 状态码
    public $code = 200;
    // 错误具体信息
    public $msg = 'APP配置操作失败';
    // 自定义的错误码
    public $errorCode = 8000;
}

