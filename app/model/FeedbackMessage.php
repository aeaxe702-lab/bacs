<?php

namespace app\model;

use think\Model;

class FeedbackMessage extends Model
{
    /**
     * 关联会话
     */
    public function session()
    {
        return $this->belongsTo(FeedbackSession::class, 'session_id', 'id');
    }
    
    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
