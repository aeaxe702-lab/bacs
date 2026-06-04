<?php

namespace app\back\controller;

use app\BaseController;
use app\model\AppConfig;

class Base extends BaseController
{
    /**
     * 组织返回数据
     */
    protected static function returnData($data = [],$msg = '成功',$error_code = 2000){
        $return = [
            'msg'        => $msg,
            'error_code' => $error_code,
            'data'       => $data,
            'second'     => time(),
        ];
        return json($return) -> header([
            'contentType' => 'application/json',
        ]);
    }

    protected function getAppContext(): array
    {
        $appId = (int)input('app_id', 0);
        $appName = trim((string)input('app_name', ''));

        if ($appId > 0) {
            $app = AppConfig::find($appId);
            if ($app) {
                $appName = $app->app_name;
            }
        }

        return [
            'appId' => $appId,
            'appName' => $appName,
        ];
    }

    protected static function appStatusOptions(): array
    {
        $statuses = [
            AppConfig::STATUS_DEV => '开发中',
            AppConfig::STATUS_SUBMITTED => '已提审',
            AppConfig::STATUS_REVIEWING => '审核中',
            AppConfig::STATUS_APPROVED => '已过审(未发布)',
            AppConfig::STATUS_RELEASED => '已发布',
            AppConfig::STATUS_BANNED => '已封号',
        ];
        $options = [];
        foreach ($statuses as $value => $text) {
            $options[] = [
                'value' => $value,
                'text' => $text,
            ];
        }

        return $options;
    }
}
