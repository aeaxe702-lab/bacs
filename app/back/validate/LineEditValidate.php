<?php

namespace app\back\validate;

class LineEditValidate extends BaseValidate
{
    protected $rule = [
        'id'          => 'require|isInt',
        'name' => 'require|max:255',
        'subtitle' => 'max:255',
        'img_url'  => 'max:255',
        'ip'       => 'require|max:255',
        'port'     => 'require|max:32',
        'uuid'     => 'require|max:255',
        'sni'      => 'require|max:255',
        'public_key' => 'require|max:255',
        'short_id' => 'require|max:255',
        'flow'     => 'require|max:255',
        'type'     => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'          => 'ID必传',
        'id.isInt'            => 'ID必须为正整数',
        'name.require' => '线路名称必传',
        'name.max'     => '线路名称最多255个字符',
        'img_url.max'  => '图标路径最多255个字符',
        'ip.require'   => 'IP地址必传',
        'ip.max'       => 'IP地址最多255个字符',
        'port.require' => '端口必传',
        'port.max'     => '端口最多32个字符',
        'uuid.require' => 'UUID必传',
        'uuid.max'     => 'UUID最多255个字符',
        'sni.require'  => 'SNI必传',
        'sni.max'      => 'SNI最多255个字符',
        'public_key.require' => '公钥必传',
        'public_key.max'     => '公钥最多255个字符',
        'short_id.require' => '短ID必传',
        'short_id.max'     => '短ID最多255个字符',
        'flow.require' => '流控必传',
        'flow.max'     => '流控最多255个字符',
        'type.require' => '线路类型必传',
        'type.in'      => '线路类型值必须为0或1',
    ];
}
