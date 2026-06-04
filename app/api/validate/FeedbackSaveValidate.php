<?php

namespace app\api\validate;

class FeedbackSaveValidate extends BaseValidate
{
    protected $rule = [
        'content' => 'require|max:500',
    ];
    protected $message = [
        'content.require' => '反馈内容不可为空',
        'content.max' => '反馈内容最大长度为500',
    ];
}