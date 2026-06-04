<?php

namespace app\back\controller;

use app\model\Admin;
use app\model\AppConfig;
use think\facade\Cookie;

class Index extends Base
{
    public function index()
    {
        $token = Cookie::get('admin_token');
        $admin = $token ? Admin::findByToken($token) : null;
        $username = $admin ? $admin->username : 'admin';
        $apps = AppConfig::field('id,app_name,status')
            ->order('id', 'desc')
            ->select();

        return view('', [
            'username' => $username,
            'apps' => $apps,
            'appStatuses' => self::appStatusOptions(),
        ]);
    }
}
