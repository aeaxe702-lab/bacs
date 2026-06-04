<?php

namespace app\back\controller;

use app\back\validate\IDMustPosValidate;
use app\model\Text as TextModel;
use app\service\Text as TextService;
use app\back\validate\TextAddValidate;
use app\back\validate\TextEditValidate;

class Text extends Base
{
    public function index()
    {
        $perPage = input('per_page', 20);
        $appContext = $this->getAppContext();
        $appId = $appContext['appId'];
        $appName = $appContext['appName'];
        
        $query = TextModel::order('create_time', 'desc');
        if ($appName !== '') {
            $query->where('app_name', $appName);
        }
        
        $texts = $query->paginate([
            'list_rows' => $perPage,
            'query' => request()->param()
        ]);
        
        return view('', [
            'texts' => $texts,
            'perPage' => $perPage,
            'appId' => $appId,
            'appName' => $appName,
        ]);
    }
    
    /**
     * 添加公告
     */
    public function add()
    {
        $data = (new TextAddValidate())->goCheck();
        TextService::saveText($data);
        return self::returnData([], '添加公告成功');
    }
    
    /**
     * 编辑公告
     */
    public function edit()
    {
        $data = (new TextEditValidate())->goCheck();
        TextService::updateText($data);
        return self::returnData([], '更新公告成功');
    }
    
    /**
     * 删除公告
     */
    public function destroy()
    {
        $data = (new IDMustPosValidate()) -> goCheck();
        $text = TextModel::find($data['id']);
        $text->delete();
        return self::returnData([], '删除公告成功');
    }
    
    /**
     * 获取公告详情
     */
    public function get()
    {
        $data = (new IDMustPosValidate()) -> goCheck();
        $text = TextService::getTextByID($data['id']);
        return self::returnData(['text' => $text]);
    }
}
