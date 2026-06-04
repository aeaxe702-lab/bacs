<?php

namespace app\lib\exception;

class LineException extends BaseException
{
    public $code = 400;
    public $msg = '线路相关异常';
    public $error_code = 5000;
}
