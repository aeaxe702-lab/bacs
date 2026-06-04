<?php

namespace app\api\controller;

use app\api\validate\AppNameValidate;
use app\service\Line as LineService;

class Line extends Base
{
    public function index()
    {
        $data = (new AppNameValidate()) -> goCheck();
        $lines = LineService::getAllLines(null, $data['app_name']);
        return self::returnData($lines);
    }
}
