<?php
/**
 * Created by PhpStorm.
 * User: 浮生若梦
 * Date: 2020/4/22
 * Time: 17:54
 */

namespace app\lib\exception;


use Exception;

class BaseException extends Exception
{
    //HTTP 状态码 404,200 ...
    public $code;
    //错误具体信息
    public $msg;
    //自定义的错误码
    public $errorCode;

    public function __construct($params = []){
        if(!is_array($params)){
            return ;
        }
        if(array_key_exists('code',$params)){
            $this -> code = $params['code'];
        }
        if(array_key_exists('msg',$params)){
            $this -> msg = $params['msg'];
        }
        if(array_key_exists('error_code',$params)){
            $this -> errorCode = $params['error_code'];
        }
        parent::__construct($this -> msg ?? '未知错误', $this -> errorCode ?? 9999);
    }
}