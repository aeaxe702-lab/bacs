<?php

namespace app\back\validate;

class UserUpdateMemberValidate extends BaseValidate
{
    protected $rule = [
        'user_id' => 'require|isInt',
        'expire_time' => 'require|isTimestamp',
    ];

    protected $message = [
        'user_id.require' => '用户ID必传',
        'user_id.isInt' => '用户ID必须为正整数',
        'expire_time.require' => '到期时间必传',
        'expire_time.isTimestamp' => '到期时间格式不正确',
    ];
}
