<?php
/**
 * Created by PhpStorm.
 * User: 浮生若梦
 * Date: 2020/4/23
 * Time: 9:40
 */

namespace app\lib\exception;


class ParameterException extends BaseException
{
    //HTTP 状态码 404,200 ...
    public $code = 200;
    //错误具体信息
    public $msg = '参数不合法';
    //自定义的错误码
    public $errorCode = 1000;
}