<?php

namespace app\api\validate;

class LoginValidate extends BaseValidate
{
    protected $rule = [
        'uuid' => 'require|alphaDash|max:128',
        'app_name' => 'require|max:32',
        'ip' => 'require|ip',
    ];

    protected $message = [
        'uuid.require' => 'UUID必传',
        'uuid.alphaDash' => 'UUID格式不正确',
        'uuid.max' => 'UUID最多128个字符',
        'app_name.require' => 'APP名称必传',
        'app_name.max' => 'APP名称最多32个字符',
        'ip.require' => 'IP地址必传',
        'ip.ip' => 'IP地址格式不正确',
    ];
}
