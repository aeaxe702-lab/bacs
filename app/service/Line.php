<?php

namespace app\service;

use app\model\AppConfig;
use app\model\AppLine;
use app\model\Line as LineModel;
use app\model\LineStat;
use app\lib\exception\LineException;

class Line
{
    /**
     * 保存线路
     */
    public static function saveLine($data){
        $line = new LineModel();
        $saveData = [
            'name' => $data['name'],
            'subtitle' => $data['subtitle'] ?? '',
            'img_url'  => $data['img_url'] ?? '',
            'ip'       => $data['ip'],
            'port'     => $data['port'],
            'uuid'     => $data['uuid'],
            'sni'      => $data['sni'],
            'public_key' => $data['public_key'],
            'short_id' => $data['short_id'],
            'flow'     => $data['flow'],
            'type'     => $data['type'],
        ];
        
        if(!$line->save($saveData)){
            throw new LineException([
                'msg' => '线路添加失败',
                'error_code' => 5001
            ]);
        }
        
        return [];
    }
    
    /**
     * 根据ID获取线路
     */
    public static function getLineByID($id){
        $line = LineModel::find($id);
        if(!$line){
            throw new LineException([
                'msg' => '线路不存在',
                'error_code' => 5002
            ]);
        }
        return $line->toArray();
    }
    
    /**
     * 更新线路
     */
    public static function updateLine($data, $where){
        $line = LineModel::find($where['id']);
        if(!$line){
            throw new LineException([
                'msg' => '线路不存在',
                'error_code' => 5002
            ]);
        }
        $updateData = [
            'name' => $data['name'],
            'subtitle' => $data['subtitle'] ?? '',
            'img_url'  => $data['img_url'] ?? '',
            'ip'       => $data['ip'],
            'port'     => $data['port'],
            'uuid'     => $data['uuid'],
            'sni'      => $data['sni'],
            'public_key' => $data['public_key'],
            'short_id' => $data['short_id'],
            'flow'     => $data['flow'],
            'type'     => $data['type'],
        ];
        
        if(!$line->save($updateData)){
            throw new LineException([
                'msg' => '线路更新失败',
                'error_code' => 5003
            ]);
        }
        
        return [];
    }
    
    /**
     * 获取所有线路信息
     * @param int $type 线路类型 null:全部 0:普通 1:高级
     * @return array
     */
    public static function getAllLines($type = null, $appName = null)
    {
        $query = LineModel::where([]);
        
        if ($type !== null) {
            $query->where('type', $type);
        }

        $appLineNames = [];
        if ($appName !== null && $appName !== '') {
            $app = AppConfig::where('app_name', $appName)->find();
            if (!$app) {
                return [];
            }

            $bindings = AppLine::where('app_id', $app->id)
                ->order('sort', 'asc')
                ->order('id', 'asc')
                ->select()
                ->toArray();
            $lineIds = array_column($bindings, 'line_id');
            if (empty($lineIds)) {
                return [];
            }
            foreach ($bindings as $binding) {
                $lineId = (int)$binding['line_id'];
                $appLineNames[$lineId] = trim((string)($binding['name'] ?? ''));
            }

            $query->whereIn('id', $lineIds)
                ->orderRaw('FIELD(id,' . implode(',', $lineIds) . ')');
        } else {
            $query->order('sort', 'asc');
        }
        
        $lines = $query->hidden(['create_time','update_time','delete_time'])->select()->toArray();
        foreach ($lines as &$line) {
            $lineId = (int)($line['id'] ?? 0);
            if (isset($appLineNames[$lineId]) && $appLineNames[$lineId] !== '') {
                $line['name'] = $appLineNames[$lineId];
            }
        }
        unset($line);

        return $lines;
    }
}
