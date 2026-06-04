<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 统计模型
 */
class Stat extends Model
{
    // 设置表名
    protected $name = 'stat';
    
    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'record_time'   => 'int',
        'app_name'      => 'string',
        'reg_count'     => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
        'delete_time'   => 'int',
    ];
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 软删除
    use \think\model\concern\SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
}
