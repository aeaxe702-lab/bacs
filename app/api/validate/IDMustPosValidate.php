<?php

namespace app\api\validate;

class IDMustPosValidate extends BaseValidate
{
    protected $rule = [
        'pkg_id' => 'require|isInt',
    ];

    protected $message = [
        'pkg_id.require' => 'ID必传',
        'pkg_id.isInt' => 'ID必须为正整数',
    ];
}
