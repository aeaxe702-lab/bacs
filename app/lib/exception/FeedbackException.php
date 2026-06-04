<?php

namespace app\lib\exception;

class FeedbackException extends BaseException
{
    // HTTP 状态码
    public $code = 200;
    // 错误具体信息
    public $msg = '反馈操作失败';
    // 自定义的错误码
    public $errorCode = 6000;
}
