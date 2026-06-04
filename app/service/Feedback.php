<?php

namespace app\service;

use app\model\FeedbackSession;
use app\model\FeedbackMessage;
use app\model\User as UserModel;
use app\lib\exception\FeedbackException;
use app\service\User as UserService;

class Feedback
{
    /**
     * 获取会话列表
     * @param int $showHidden 是否显示隐藏的会话 0:不显示 1:显示
     * @return array
     */
    public static function getSessions($showHidden = 0)
    {
        $query = FeedbackSession::with(['user']);
        
        // 是否显示隐藏的会话
        if ($showHidden == 0) {
            $query->where('is_hidden', 0);
        }
        
        // 按最后消息时间倒序
        $sessions = $query->order('last_message_time', 'desc')
            ->select()
            ->toArray();
        
        return $sessions;
    }
    
    /**
     * 获取会话消息列表
     * @param int $sessionId 会话ID
     * @return array
     */
    public static function getMessages($sessionId)
    {
        // 验证会话是否存在
        $session = FeedbackSession::with(['user'])->find($sessionId);
        if (!$session) {
            throw new FeedbackException([
                'msg' => '会话不存在',
                'error_code' => 6001
            ]);
        }
        
        // 获取消息列表
        $messages = FeedbackMessage::where('session_id', $sessionId)
            ->order('create_time', 'asc')
            ->select()
            ->toArray();
        
        // 标记客服未读消息为已读
        FeedbackMessage::where('session_id', $sessionId)
            ->where('sender_type', 0) // 用户发送的
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
                'update_time' => time()
            ]);
        
        // 更新会话未读数
        $session->unread_count = 0;
        $session->update_time = time();
        $session->save();
        
        return [
            'session' => $session->toArray(),
            'messages' => $messages
        ];
    }
    
    /**
     * 发送消息（客服回复）
     * @param int $sessionId 会话ID
     * @param string $content 消息内容
     * @return array
     */
    public static function sendMessage($sessionId, $content)
    {
        // 验证会话是否存在
        $session = FeedbackSession::find($sessionId);
        if (!$session) {
            throw new FeedbackException([
                'msg' => '会话不存在',
                'error_code' => 6001
            ]);
        }
        
        $currentTime = time();
        
        // 创建消息
        $message = FeedbackMessage::create([
            'session_id' => $sessionId,
            'user_id' => $session->user_id,
            'sender_type' => 1, // 客服
            'content' => $content,
            'is_read' => 0, // 用户未读
            'create_time' => $currentTime,
            'update_time' => $currentTime,
        ]);
        
        // 更新会话最后消息，并标记为已回复
        $session->last_message = $content;
        $session->last_message_time = $currentTime;
        $session->is_replied = 1; // 标记为已回复
        $session->update_time = $currentTime;
        $session->save();
        
        return $message->toArray();
    }
    
    /**
     * 切换会话隐藏状态
     * @param int $sessionId 会话ID
     * @return bool
     */
    public static function toggleHidden($sessionId)
    {
        $session = FeedbackSession::find($sessionId);
        if (!$session) {
            throw new FeedbackException([
                'msg' => '会话不存在',
                'error_code' => 6001
            ]);
        }
        
        $session->is_hidden = $session->is_hidden == 1 ? 0 : 1;
        $session->update_time = time();
        $session->save();
        
        return true;
    }
    
    /**
     * 保存用户提问（用于API接口调用）
     * @param int $userId 用户ID
     * @param string $app 应用标识
     * @param string $content 消息内容
     * @return array
     */
    public static function saveUserQuestion($userId, $content)
    {
        // 验证用户是否存在
        UserService::checkUserExists($userId);
        
        $currentTime = time();
        
        // 查找或创建会话
        $session = FeedbackSession::where('user_id', $userId)
            ->find();
        
        if (!$session) {
            // 创建新会话
            $session = FeedbackSession::create([
                'user_id' => $userId,
                'last_message' => $content,
                'last_message_time' => $currentTime,
                'unread_count' => 1,
                'is_hidden' => 0,
                'is_replied' => 0, // 未回复
                'create_time' => $currentTime,
                'update_time' => $currentTime,
            ]);
        } else {
            // 更新会话，标记为未回复
            $session->last_message = $content;
            $session->last_message_time = $currentTime;
            $session->is_hidden=0;
            $session->unread_count = $session->unread_count + 1;
            $session->is_replied = 0; // 用户再次提问，标记为未回复
            $session->update_time = $currentTime;
            $session->save();
        }
        
        // 创建消息
        $message = FeedbackMessage::create([
            'session_id' => $session->id,
            'user_id' => $userId,
            'sender_type' => 0, // 用户
            'content' => $content,
            'is_read' => 0, // 客服未读
            'create_time' => $currentTime,
            'update_time' => $currentTime,
        ]);
        
        return [
            'session_id' => $session->id,
            'message' => $message->toArray()
        ];
    }
    
    /**
     * 获取用户的所有消息（用于API接口调用）
     * @param int $userId 用户ID
     * @param string $app 应用标识
     * @return array
     */
    public static function getUserMessages($userId)
    {
        // 验证用户是否存在
        UserService::checkUserExists($userId);
        
        // 查找会话
        $session = FeedbackSession::where('user_id', $userId)
            ->find();
        
        if (!$session) {
            // 没有会话，返回空数组
            return [];
        }
        
        // 获取该会话的所有消息
        $messages = FeedbackMessage::where('session_id', $session->id)
            ->order('create_time', 'asc')
            ->hidden(['update_time','delete_time','id'])
            ->select()
            ->toArray();
        
        return $messages;
    }
}
