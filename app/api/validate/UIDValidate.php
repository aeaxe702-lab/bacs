<?php

namespace app\api\validate;

class UIDValidate extends BaseValidate
{
    protected $rule = [
        'uid' => 'require|isInt',
    ];

    protected $message = [
        'uid.require' => 'UID必传',
        'uid.isInt' => 'UID必须为正整数',
    ];
}