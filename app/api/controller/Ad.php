<?php

namespace app\api\controller;

class Ad extends Base
{
    public function index (){
        $userInfo = request() -> userInfo['id'];
        return self::returnData();
    }
}