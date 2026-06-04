<?php

namespace app\back\validate;

class AppProjectConfigValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isInt',
        'banner_position_id' => 'max:128',
        'interstitial_position_id' => 'max:128',
        'ad_interval_minutes' => 'isInt',
        'ad_continuous_count' => 'isInt',
        'popup_enabled' => 'require|in:0,1',
        'popup_title' => 'max:255',
        'popup_content' => 'max:2000',
        'popup_button_title' => 'max:255',
    ];

    protected $message = [
        'id.require' => '应用ID必传',
        'id.isInt' => '应用ID必须为正整数',
        'banner_position_id.max' => '横幅位ID最多128个字符',
        'interstitial_position_id.max' => '插页ID最多128个字符',
        'ad_interval_minutes.isInt' => '广告添加时长必须为正整数',
        'ad_continuous_count.isInt' => '连续广告次数必须为正整数',
        'popup_enabled.require' => '是否弹框必传',
        'popup_enabled.in' => '是否弹框值必须为0或1',
        'popup_title.max' => '弹框标题最多255个字符',
        'popup_content.max' => '弹框内容最多2000个字符',
        'popup_button_title.max' => '按钮标题最多255个字符',
    ];
}
