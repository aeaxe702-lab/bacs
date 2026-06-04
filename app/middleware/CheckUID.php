<?php

namespace app\middleware;

use app\api\validate\UIDValidate;
use app\service\User;

class CheckUID
{
    /**
     * 检测参数中是否存在uid
     */
    public function handle ($request, \Closure $next){
        $data = (new UIDValidate()) -> goCheck();
        //最后校验是否为正规请求
        ###查询用户信息是否存在
        $userInfo = User::checkUserExists($data['uid']);
        ###将用户信息保存至请求体中，方便后续使用
        $request -> userInfo = $userInfo;

        return $next($request);
    }
}