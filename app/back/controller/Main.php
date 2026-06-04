<?php

namespace app\back\controller;

use app\model\NodeStat;
use app\model\Stat;
use app\model\User;
use think\facade\Db;

class Main extends Base
{
    public function index()
    {
        $endTime = strtotime('yesterday');
        $startTime = strtotime('-29 days', $endTime);

        $stats = Stat::where('record_time', '>=', $startTime)
            ->where('record_time', '<=', $endTime)
            ->field('record_time, SUM(reg_count) AS reg_count')
            ->group('record_time')
            ->order('record_time', 'asc')
            ->limit(30)
            ->select();

        $dates = [];
        $regCounts = [];
        foreach ($stats as $stat) {
            $dates[] = date('m-d', (int)$stat['record_time']);
            $regCounts[] = max(0, (int)$stat['reg_count']);
        }

        $todayStart = strtotime('today');
        $todayRegCount = User::where('create_time', '>=', $todayStart)
            ->where('create_time', '<=', time())
            ->count();

        return view('', [
            'dates' => json_encode($dates),
            'regCounts' => json_encode($regCounts),
            'todayData' => [
                'reg_count' => max(0, (int)$todayRegCount),
            ],
        ]);
    }

    public function nodeStatsData()
    {
        [$nodes, $nodeCharts] = $this->nodeStats();

        return self::returnData([
            'nodes' => $nodes,
            'nodeCharts' => $nodeCharts,
        ]);
    }

    private function nodeStats(): array
    {
        $nodes = NodeStat::field('node_key,ip,port,upload_total,download_total,upload_daily,download_daily,upload_30s,download_30s,connections,report_time')
            ->order('report_time', 'desc')
            ->select()
            ->toArray();
        $nodeKeys = array_column($nodes, 'node_key');
        $dailyRows = [];

        if (!empty($nodeKeys)) {
            $dailyRows = Db::name('node_stat_daily')
                ->whereIn('node_key', $nodeKeys)
                ->where('stat_date', '>=', date('Y-m-d', strtotime('-6 days')))
                ->field('node_key, stat_date, upload_total, download_total')
                ->order('stat_date', 'asc')
                ->select()
                ->toArray();
        }

        $dailyMap = [];
        foreach ($dailyRows as $row) {
            $dailyMap[$row['node_key']][$row['stat_date']] = $row;
        }

        $charts = [];
        foreach ($nodes as $index => &$node) {
            $node['chart_index'] = $index;
            $node['total_upload_text'] = $this->formatBytes((int)$node['upload_total']);
            $node['total_download_text'] = $this->formatBytes((int)$node['download_total']);
            $node['today_upload_text'] = $this->formatBytes((int)$node['upload_daily']);
            $node['today_download_text'] = $this->formatBytes((int)$node['download_daily']);
            $node['last_30s_upload_text'] = $this->formatBytes((int)$node['upload_30s']);
            $node['last_30s_download_text'] = $this->formatBytes((int)$node['download_30s']);
            $node['report_time_text'] = $node['report_time'] ? date('Y-m-d H:i:s', (int)$node['report_time']) : '-';

            $dates = [];
            $dailyUpload = [];
            $dailyDownload = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $row = $dailyMap[$node['node_key']][$date] ?? null;
                $dates[] = date('m-d', strtotime($date));
                $dailyUpload[] = (int)($row['upload_total'] ?? 0);
                $dailyDownload[] = (int)($row['download_total'] ?? 0);
            }

            $charts[] = [
                'index' => $index,
                'node_key' => $node['node_key'],
                'label' => $node['ip'] . ':' . $node['port'],
                'dates' => $dates,
                'daily_upload' => $dailyUpload,
                'daily_download' => $dailyDownload,
                'last_30s_upload' => (int)$node['upload_30s'],
                'last_30s_download' => (int)$node['download_30s'],
            ];
        }
        unset($node);

        return [$nodes, $charts];
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
