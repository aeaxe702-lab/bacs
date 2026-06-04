<?php

namespace app\api\validate;

class AppNameValidate extends BaseValidate
{
    protected $rule = [
        'app_name' => 'require|max:32',
    ];
    protected $message = [
        'app_name.require' => 'APP名称必传',
        'app_name.max' => 'APP名称最多32个字符',
    ];
}
