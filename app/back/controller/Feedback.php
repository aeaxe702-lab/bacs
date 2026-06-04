<?php

namespace app\back\controller;

use app\service\Feedback as FeedbackService;
use app\back\validate\FeedbackMessageValidate;
use app\back\validate\FeedbackSessionValidate;

class Feedback extends Base
{
    /**
     * 意见反馈页面
     */
    public function index()
    {
        return view();
    }
    
    /**
     * 获取会话列表
     */
    public function getSessions()
    {
        $showHidden = input('show_hidden', 0);
        
        $sessions = FeedbackService::getSessions($showHidden);
        
        return self::returnData(['sessions' => $sessions]);
    }
    
    /**
     * 获取会话消息列表
     */
    public function getMessages()
    {
        $data = (new FeedbackSessionValidate())->goCheck();
        
        $result = FeedbackService::getMessages($data['session_id']);
        
        return self::returnData($result);
    }
    
    /**
     * 发送消息（客服回复）
     */
    public function sendMessage()
    {
        $data = (new FeedbackMessageValidate())->goCheck();
        
        $message = FeedbackService::sendMessage($data['session_id'], $data['content']);
        
        return self::returnData(['message' => $message]);
    }
    
    /**
     * 切换会话隐藏状态
     */
    public function toggleHidden()
    {
        $data = (new FeedbackSessionValidate())->goCheck();
        
        FeedbackService::toggleHidden($data['session_id']);
        
        return self::returnData([], '操作成功');
    }
}