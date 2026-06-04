<?php

namespace app\back\validate;

class TextAddValidate extends BaseValidate
{
    protected $rule = [
        'title' => 'max:255',
        'btn_title' => 'max:255',
        'target' => 'max:255',
        'content' => 'require|max:255',
        'app_name' => 'max:32',
    ];

    protected $message = [
        'title.max' => '标题不能超过255字符',
        'btn_title.max' => '按钮标题不能超过255字符',
        'target.max' => '跳转目标不能超过255字符',
        'content.require' => '内容必传',
        'content.max' => '内容不能超过255字符',
        'app_name.max' => 'APP名称最大32个字符',
    ];
}
