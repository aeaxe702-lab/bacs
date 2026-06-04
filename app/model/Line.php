<?php

namespace app\model;


class Line extends Base
{
    protected $name = 'line';

    public function getImgUrlAttr ($val){
        // 如果值为空，直接返回空，避免拼接无效链接
        if (empty($val)) {
            return '';
        }

        // 获取当前域名
        $domain = request()->domain();

        // 判断 $val 是否已经以 http 或 https 开头
        if (str_starts_with(trim($val), 'http://') || str_starts_with(trim($val), 'https://')) {
            // 已经有域名协议，直接返回原值
            return trim($val);
        }

        // 没有域名，拼接域名 + 图片路径
        return $domain . trim($val);
    }

    public function setImgUrlAttr($val)
    {
        // 为空直接保存空
        if (empty($val)) {
            return '';
        }

        $val = trim($val);

        // 如果是完整URL，则去掉域名
        if (
            str_starts_with($val, 'http://') ||
            str_starts_with($val, 'https://')
        ) {
            $path = parse_url($val, PHP_URL_PATH);

            // 保留 query 参数（如果有）
            $query = parse_url($val, PHP_URL_QUERY);

            return $query ? $path . '?' . $query : $path;
        }

        // 不是完整URL，直接保存
        return $val;
    }

}
