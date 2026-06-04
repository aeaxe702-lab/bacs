<?php

namespace app\api\controller;

use app\BaseController;
use app\service\Aes;

class Base extends BaseController
{
    /**
     * 组织返回数据
     */
    protected static function returnData($data = [],$msg = 'success',$error_code = 2000){
        $return = [
            'message'        => $msg,
            'error_code' => $error_code,
            'data'       => $data,
            'time'     => time(),
        ];
        return json($return) -> header([
            'contentType' => 'application/json',
        ]);
    }
}