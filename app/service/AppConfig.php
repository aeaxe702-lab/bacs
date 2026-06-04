<?php

namespace app\service;

use app\lib\exception\AppConfigException;
use app\model\AppConfig as AppConfigModel;

class AppConfig
{
    /**
     * 根据 APP 名称获取项目配置
     */
    public static function getProjectConfigByAppName($appName)
    {
        $app = AppConfigModel::where('app_name', $appName)->find();
        if (!$app) {
            throw new AppConfigException([
                'msg' => 'APP不存在',
                'error_code' => 8001
            ]);
        }

        return $app->projectConfig();
    }
}

