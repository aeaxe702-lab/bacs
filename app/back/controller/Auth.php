<?php

namespace app\back\controller;

use app\model\Admin;
use think\facade\Cookie;

class Auth extends Base
{

    /**
     * 登录页面
     */
    public function login()
    {
        $token = Cookie::get('admin_token');
        if ($token && Admin::findByToken($token)) {
            $url = (string)url('index/index');
            return response("<script>window.top.location.href='{$url}';</script>", 200, ['Content-Type' => 'text/html']);
        }
        return view();
    }

    /**
     * 处理登录
     */
    public function doLogin()
    {
        $username = input('post.username', '');
        $password = input('post.password', '');

        if (empty($username) || empty($password)) {
            return self::returnData([], '用户名和密码不能为空', 4000);
        }

        $admin = Admin::findByUsername($username);
        if (!$admin || !$admin->verifyPassword($password)) {
            return self::returnData([], '用户名或密码错误', 4001);
        }

        $token = $admin->generateToken();
        // 种 cookie，有效期 7 天
        Cookie::set('admin_token', $token, 604800);

        return self::returnData(['redirect' => (string)url('index/index')]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $token = Cookie::get('admin_token');
        if ($token) {
            $admin = Admin::findByToken($token);
            if ($admin) {
                $admin->token = '';
                $admin->save();
            }
        }
        Cookie::delete('admin_token');
        return redirect((string)url('auth/login'));
    }
}
