<?php

namespace app\api\controller;
use app\api\validate\FeedbackSaveValidate;
use app\service\Feedback as FeedbackService;

class Feedback extends Base
{
    /**
     * 获取意见建议列表
     */
    public function index (){
        $messages = FeedbackService::getUserMessages(\request() -> userInfo['id']);
        return self::returnData($messages);
    }

    /**
     * 意见建议
     */
    public function save (){
        $data = (new FeedbackSaveValidate()) -> goCheck();
        $userID = \request() -> userInfo['id'];
        FeedbackService::saveUserQuestion($userID,$data['content']);
        return self::returnData();
    }
}
