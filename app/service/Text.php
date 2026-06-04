<?php

namespace app\service;

use app\lib\exception\TextException;
use app\model\Text as TextModel;

class Text
{
    /**
     * 保存公告
     */
    public static function saveText($data)
    {
        $currentTime = time();

        $text = TextModel::create([
            'title' => $data['title'] ?? '',
            'btn_title' => $data['btn_title'] ?? '',
            'target' => $data['target'] ?? '',
            'content' => $data['content'],
            'app_name' => $data['app_name'] ?? '',
            'create_time' => $currentTime,
            'update_time' => $currentTime,
        ]);

        if (!$text) {
            throw new TextException([
                'msg' => '公告添加失败',
                'error_code' => 7001
            ]);
        }

        return [];
    }

    /**
     * 根据ID获取公告
     */
    public static function getTextByID($id)
    {
        $text = TextModel::find($id);
        if (!$text) {
            throw new TextException([
                'msg' => '公告不存在',
                'error_code' => 7002
            ]);
        }

        return $text->toArray();
    }

    /**
     * 更新公告
     */
    public static function updateText($data)
    {
        $text = TextModel::find($data['id']);
        if (!$text) {
            throw new TextException([
                'msg' => '公告不存在',
                'error_code' => 7002
            ]);
        }

        $text->title = $data['title'] ?? '';
        $text->btn_title = $data['btn_title'] ?? '';
        $text->target = $data['target'] ?? '';
        $text->content = $data['content'];
        $text->app_name = $data['app_name'] ?? '';
        $text->update_time = time();

        if (!$text->save()) {
            throw new TextException([
                'msg' => '公告更新失败',
                'error_code' => 7003
            ]);
        }

        return [];
    }

    /**
     * 根据 APP 名称获取公告
     */
    public static function getTextByAppName($appName)
    {
        $text = TextModel::where('app_name', $appName)
            ->order('create_time', 'desc')
            ->select();

        if (!$text) {
            return [];
        }

        return $text->toArray();
    }
}
