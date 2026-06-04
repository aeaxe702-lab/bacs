<?php

namespace app\api\controller;

use app\model\NodeStat as NodeStatModel;
use app\model\NodeStatDaily;
use app\model\NodeStatLog;
use think\facade\Db;

class NodeStat extends Base
{
    /**
     * Report VPN node traffic stats.
     *
     * POST /node/stat
     * ip, port, upload_total, download_total, upload_daily, download_daily,
     * upload_30s, download_30s, connections, report_time
     */
    public function report()
    {
        $data = request()->post();
        if (empty($data)) {
            $json = json_decode(request()->getContent(), true);
            $data = is_array($json) ? $json : [];
        }

        $secret = (string)env('NODE_STAT_SECRET', '');
        if ($secret !== '' && (($data['token'] ?? '') !== $secret)) {
            return self::returnData([], 'invalid token', 4001);
        }

        $ip = trim((string)($data['ip'] ?? request()->ip()));
        $port = (int)($data['port'] ?? 0);
        if ($ip === '' || $port <= 0) {
            return self::returnData([], 'ip and port are required', 4002);
        }

        $intervalSeconds = max(1, (int)($data['interval_seconds'] ?? 30));
        $upload30s = $this->uint($data['upload_30s'] ?? $data['upload_interval'] ?? 0);
        $download30s = $this->uint($data['download_30s'] ?? $data['download_interval'] ?? 0);
        $uploadSpeed = $this->uint($data['upload_speed'] ?? ($upload30s > 0 ? (int)round($upload30s / $intervalSeconds) : 0));
        $downloadSpeed = $this->uint($data['download_speed'] ?? ($download30s > 0 ? (int)round($download30s / $intervalSeconds) : 0));

        if ($upload30s === 0 && $uploadSpeed > 0) {
            $upload30s = $uploadSpeed * $intervalSeconds;
        }
        if ($download30s === 0 && $downloadSpeed > 0) {
            $download30s = $downloadSpeed * $intervalSeconds;
        }

        $reportTime = (int)($data['report_time'] ?? time());
        if ($reportTime <= 0) {
            $reportTime = time();
        }

        $nodeKey = $ip . ':' . $port;
        $statDate = date('Y-m-d', $reportTime);
        $now = time();

        $payload = [
            'node_key' => $nodeKey,
            'ip' => $ip,
            'port' => $port,
            'upload_total' => $this->uint($data['upload_total'] ?? 0),
            'download_total' => $this->uint($data['download_total'] ?? 0),
            'upload_daily' => $this->uint($data['upload_daily'] ?? 0),
            'download_daily' => $this->uint($data['download_daily'] ?? 0),
            'upload_30s' => $upload30s,
            'download_30s' => $download30s,
            'upload_speed' => $uploadSpeed,
            'download_speed' => $downloadSpeed,
            'connections' => max(0, (int)($data['connections'] ?? 0)),
            'report_time' => $reportTime,
            'update_time' => $now,
        ];

        Db::transaction(function () use ($payload, $nodeKey, $ip, $port, $statDate, $now) {
            $exists = NodeStatModel::where('node_key', $nodeKey)->find();
            if ($exists) {
                $exists->save($payload);
            } else {
                NodeStatModel::create($payload + ['create_time' => $now]);
            }

            $daily = NodeStatDaily::where('node_key', $nodeKey)->where('stat_date', $statDate)->find();
            $dailyPayload = [
                'node_key' => $nodeKey,
                'ip' => $ip,
                'port' => $port,
                'stat_date' => $statDate,
                'upload_total' => $payload['upload_daily'],
                'download_total' => $payload['download_daily'],
                'latest_upload_total' => $payload['upload_total'],
                'latest_download_total' => $payload['download_total'],
                'last_report_time' => $payload['report_time'],
                'update_time' => $now,
            ];

            if ($daily) {
                $daily->save($dailyPayload + ['report_count' => ((int)$daily->report_count) + 1]);
            } else {
                NodeStatDaily::create($dailyPayload + ['report_count' => 1, 'create_time' => $now]);
            }

            NodeStatLog::create([
                'node_key' => $nodeKey,
                'ip' => $ip,
                'port' => $port,
                'report_time' => $payload['report_time'],
                'upload_total' => $payload['upload_total'],
                'download_total' => $payload['download_total'],
                'upload_30s' => $payload['upload_30s'],
                'download_30s' => $payload['download_30s'],
                'upload_speed' => $payload['upload_speed'],
                'download_speed' => $payload['download_speed'],
                'connections' => $payload['connections'],
                'create_time' => $now,
            ]);
        });

        return self::returnData(['node_key' => $nodeKey]);
    }

    private function uint($value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }

        return max(0, (int)$value);
    }
}
