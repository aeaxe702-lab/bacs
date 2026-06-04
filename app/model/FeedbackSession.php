<?php

namespace app\model;

use think\Model;

class FeedbackSession extends Model
{
    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * 关联消息
     */
    public function messages()
    {
        return $this->hasMany(FeedbackMessage::class, 'session_id', 'id');
    }
}
