<?php

namespace app\back\validate;

class FeedbackSessionValidate extends BaseValidate
{
    protected $rule = [
        'session_id' => 'require|isInt',
    ];

    protected $message = [
        'session_id.require' => '会话ID必传',
        'session_id.isInt' => '会话ID必须为正整数',
    ];
}
