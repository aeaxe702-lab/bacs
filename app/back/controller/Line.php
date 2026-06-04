<?php

namespace app\back\controller;

use app\model\Line as LineModel;
use app\model\NodeStat;
use app\back\validate\IDMustPosValidate;
use app\back\validate\LineAddValidate;
use app\back\validate\LineEditValidate;
use app\lib\exception\LineException;
use app\service\Line as LineServer;
use think\facade\Db;

class Line extends Base
{
    public function index(){
        $lineModel = new LineModel();
        $perPage = request()->param('per_page', 20);
        // 限制每页数量范围
        $perPage = max(10, min(100, intval($perPage)));
        $lineInfos = $lineModel->order('sort', 'asc')->order('id', 'desc')->paginate([
            'list_rows' => $perPage,
            'query' => ['per_page' => $perPage]
        ]);
        [$nodeStats, $nodeCharts] = $this->nodeStatsForLines($lineInfos);

        return view('index', [
            'lineInfos' => $lineInfos,
            'perPage' => $perPage,
            'nodeStats' => json_encode($nodeStats, JSON_UNESCAPED_UNICODE),
            'nodeCharts' => json_encode($nodeCharts, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function nodeStats()
    {
        $lineIds = input('line_ids/a', []);
        $lineIds = array_values(array_unique(array_filter(array_map('intval', $lineIds), function ($id) {
            return $id > 0;
        })));

        $query = LineModel::field('id,ip,port')->order('sort', 'asc')->order('id', 'desc');
        if (!empty($lineIds)) {
            $query->whereIn('id', $lineIds);
        }

        $lines = $query->select();
        [$nodeStats, $nodeCharts] = $this->nodeStatsForLines($lines);

        return self::returnData([
            'nodeStats' => $nodeStats,
            'nodeCharts' => $nodeCharts,
        ]);
    }

    public function add(){
        $data = (new LineAddValidate())->goCheck();
        return self::returnData(LineServer::saveLine($data));
    }

    public function get(){
        $data = (new IDMustPosValidate())->goCheck();
        return self::returnData(LineServer::getLineByID($data['id']));
    }

    public function edit(){
        $data = (new LineEditValidate())->goCheck();
        return self::returnData(LineServer::updateLine($data, ['id' => $data['id']]));
    }

    public function destroy(){
        try{
            $data = (new IDMustPosValidate())->goCheck();
            LineModel::destroy($data['id']);
            return self::returnData();
        }catch (LineException $e){
            throw new LineException([
                'msg' => $e->getMessage(),
                'error_code' => 5001
            ]);
        }
    }
    
    public function updateSort(){
        $ids = request()->post('ids');
        
        if(empty($ids) || !is_array($ids)){
            throw new LineException([
                'msg' => '参数错误',
                'error_code' => 5004
            ]);
        }
        
        // 批量更新排序
        foreach($ids as $sort => $id){
            LineModel::where('id', $id)->update(['sort' => $sort + 1]);
        }
        
        return self::returnData();
    }
    
    public function uploadImage(){
        $path = uploadFile('file','line');
        return self::returnData(['url' => $path]);
    }

    private function nodeStatsForLines($lines): array
    {
        if (is_object($lines) && method_exists($lines, 'items')) {
            $lines = $lines->items();
        }

        $lineMap = [];
        foreach ($lines as $line) {
            $ip = trim((string)($line['ip'] ?? ''));
            $port = (int)($line['port'] ?? 0);
            if ($ip === '' || $port <= 0) {
                continue;
            }

            $nodeKey = $ip . ':' . $port;
            $lineMap[$nodeKey] = [
                'node_key' => $nodeKey,
                'ip' => $ip,
                'port' => $port,
            ];
        }

        if (empty($lineMap)) {
            return [[], []];
        }

        $nodeKeys = array_keys($lineMap);
        $stats = NodeStat::whereIn('node_key', $nodeKeys)
            ->field('node_key,upload_total,download_total,upload_daily,download_daily,upload_30s,download_30s,connections')
            ->select()
            ->toArray();
        $statMap = [];
        foreach ($stats as $stat) {
            $statMap[$stat['node_key']] = $stat;
        }

        $dailyRows = Db::name('node_stat_daily')
            ->whereIn('node_key', $nodeKeys)
            ->where('stat_date', '>=', date('Y-m-d', strtotime('-6 days')))
            ->field('node_key, stat_date, upload_total, download_total')
            ->order('stat_date', 'asc')
            ->select()
            ->toArray();

        $dailyMap = [];
        foreach ($dailyRows as $row) {
            $dailyMap[$row['node_key']][$row['stat_date']] = $row;
        }

        $nodeStats = [];
        $nodeCharts = [];
        foreach ($lineMap as $nodeKey => $line) {
            $stat = $statMap[$nodeKey] ?? [];
            $node = array_merge([
                'node_key' => $nodeKey,
                'ip' => $line['ip'],
                'port' => $line['port'],
                'upload_total' => 0,
                'download_total' => 0,
                'upload_daily' => 0,
                'download_daily' => 0,
                'upload_30s' => 0,
                'download_30s' => 0,
                'connections' => 0,
            ], $stat);

            $nodeStats[$nodeKey] = [
                'node_key' => $nodeKey,
                'ip' => $node['ip'],
                'port' => $node['port'],
                'total_upload_text' => $this->formatBytes((int)$node['upload_total']),
                'total_download_text' => $this->formatBytes((int)$node['download_total']),
                'today_upload_text' => $this->formatBytes((int)$node['upload_daily']),
                'today_download_text' => $this->formatBytes((int)$node['download_daily']),
                'last_30s_upload_text' => $this->formatBytes((int)$node['upload_30s']),
                'last_30s_download_text' => $this->formatBytes((int)$node['download_30s']),
                'connections' => max(0, (int)$node['connections']),
            ];

            $dates = [];
            $dailyUpload = [];
            $dailyDownload = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $row = $dailyMap[$nodeKey][$date] ?? null;
                $dates[] = date('m-d', strtotime($date));
                $dailyUpload[] = (int)($row['upload_total'] ?? 0);
                $dailyDownload[] = (int)($row['download_total'] ?? 0);
            }

            $nodeCharts[$nodeKey] = [
                'node_key' => $nodeKey,
                'label' => $line['ip'],
                'dates' => $dates,
                'daily_upload' => $dailyUpload,
                'daily_download' => $dailyDownload,
            ];
        }

        return [$nodeStats, $nodeCharts];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $value = max(0, $bytes);
        $index = 0;

        while ($value >= 1024 && $index < count($units) - 1) {
            $value /= 1024;
            $index++;
        }

        return ($index === 0 ? (string)(int)$value : number_format($value, 2)) . ' ' . $units[$index];
    }
}
