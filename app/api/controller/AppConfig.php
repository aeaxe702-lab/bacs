<?php

namespace app\api\controller;

use app\api\validate\AppNameValidate;
use app\service\AppConfig as AppConfigService;

class AppConfig extends Base
{
    public function config()
    {
        $data = (new AppNameValidate()) -> goCheck();
        return self::returnData(AppConfigService::getProjectConfigByAppName($data['app_name']));
    }
}

