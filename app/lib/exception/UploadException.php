<?php

namespace app\lib\exception;

class UploadException extends BaseException
{
    public $code = 200;
    public $msg = '文件上传失败';
    public $errorCode = 8000;
}
