<?php

namespace app\back\validate;

class FeedbackMessageValidate extends BaseValidate
{
    protected $rule = [
        'session_id' => 'require|isInt',
        'content' => 'require|max:1000',
    ];

    protected $message = [
        'session_id.require' => '会话ID必传',
        'session_id.isInt' => '会话ID必须为正整数',
        'content.require' => '消息内容必传',
        'content.max' => '消息内容不能超过1000字符',
    ];
}
