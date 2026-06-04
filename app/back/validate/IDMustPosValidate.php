<?php

namespace app\back\validate;

class IDMustPosValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isInt',
    ];

    protected $message = [
        'id.require' => 'ID必传',
        'id.isInt' => 'ID必须为正整数',
    ];
}
