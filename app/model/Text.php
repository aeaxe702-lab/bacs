<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 文本模型
 */
class Text extends Model
{
    protected $name = 'text';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = 'delete_time';

}
