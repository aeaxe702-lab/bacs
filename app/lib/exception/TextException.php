<?php

namespace app\lib\exception;

class TextException extends BaseException
{
    // HTTP 状态码
    public $code = 200;
    // 错误具体信息
    public $msg = '文本操作失败';
    // 自定义的错误码
    public $errorCode = 7000;
}
