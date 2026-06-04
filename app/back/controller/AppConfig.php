<?php

namespace app\back\controller;

use app\back\validate\AppProjectConfigValidate;
use app\model\AppConfig as AppConfigModel;
use app\model\AppLine;
use app\model\Line;
use app\model\Stat;
use app\model\User;
use think\facade\Db;

class AppConfig extends Base
{
    public function index()
    {
        $apps = AppConfigModel::field('id,app_name,status,create_time')
            ->order('id', 'desc')
            ->select();
        return view('', [
            'apps' => $apps,
            'appStatuses' => self::appStatusOptions(),
        ]);
    }

    public function add()
    {
        $name = trim((string)input('app_name', ''));
        $status = (int)input('status', 0);

        if ($name === '') {
            return self::returnData([], 'APP名称不能为空', 4001);
        }
        if (mb_strlen($name, 'UTF-8') > 32) {
            return self::returnData([], 'APP名称最多32个字符', 4002);
        }
        if (!array_key_exists($status, AppConfigModel::statuses())) {
            return self::returnData([], 'APP状态无效', 4003);
        }

        $exists = AppConfigModel::where('app_name', $name)->find();
        if ($exists) {
            return self::returnData([], 'APP名称已存在', 4004);
        }

        $app = AppConfigModel::create([
            'app_name' => $name,
            'status' => $status,
            'create_time' => time(),
            'update_time' => time(),
        ]);

        return self::returnData($app->toArray());
    }

    public function update()
    {
        $app = $this->getApp();
        $status = (int)input('status', 0);

        if (!array_key_exists($status, AppConfigModel::statuses())) {
            return self::returnData([], 'APP状态无效', 4003);
        }

        $app->status = $status;
        $app->update_time = time();
        $app->save();

        return self::returnData($app->toArray());
    }

    public function detail()
    {
        $app = $this->getApp();
        return view('', array_merge([
            'app' => $app,
        ], $this->buildAppStats($app->app_name)));
    }

    public function project()
    {
        $app = $this->getApp();
        return view('', [
            'app' => $app,
            'projectConfig' => $app->projectConfig(),
        ]);
    }

    public function saveConfig()
    {
        $data = (new AppProjectConfigValidate())->goCheck();
        $app = $this->getAppById((int)$data['id']);
        $popupEnabled = (int)$data['popup_enabled'];

        $popupTitle = trim((string)($data['popup_title'] ?? ''));
        $popupContent = trim((string)($data['popup_content'] ?? ''));
        $popupButtonTitle = trim((string)($data['popup_button_title'] ?? ''));

        if ($popupEnabled === 1 && ($popupTitle === '' || $popupContent === '' || $popupButtonTitle === '')) {
            return self::returnData([], '开启弹框时，弹框标题、弹框内容和按钮标题都必填', 4006);
        }

        $app->save([
            'banner_position_id' => trim((string)($data['banner_position_id'] ?? '')),
            'interstitial_position_id' => trim((string)($data['interstitial_position_id'] ?? '')),
            'ad_interval_minutes' => max(0, (int)($data['ad_interval_minutes'] ?? 0)),
            'ad_continuous_count' => max(0, (int)($data['ad_continuous_count'] ?? 0)),
            'popup_enabled' => $popupEnabled,
            'popup_title' => $popupEnabled ? $popupTitle : '',
            'popup_content' => $popupEnabled ? $popupContent : '',
            'popup_button_title' => $popupEnabled ? $popupButtonTitle : '',
            'update_time' => time(),
        ]);

        return self::returnData(['config' => $app->projectConfig()], '保存成功');
    }

    public function lines()
    {
        $app = $this->getApp();
        $bindings = AppLine::where('app_id', $app->id)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();
        $boundIds = array_column($bindings, 'line_id');
        $boundNames = [];
        foreach ($bindings as $binding) {
            $boundNames[(int)$binding['line_id']] = (string)($binding['name'] ?? '');
        }
        $boundSort = array_flip($boundIds);
        $lines = Line::order('sort', 'asc')->order('id', 'desc')->select()->toArray();
        foreach ($lines as &$line) {
            $lineId = (int)$line['id'];
            $line['app_line_name'] = $boundNames[$lineId] ?? $line['name'];
        }
        unset($line);

        usort($lines, function ($left, $right) use ($boundSort) {
            $leftBound = isset($boundSort[$left['id']]);
            $rightBound = isset($boundSort[$right['id']]);
            if ($leftBound && $rightBound) {
                return $boundSort[$left['id']] <=> $boundSort[$right['id']];
            }
            if ($leftBound !== $rightBound) {
                return $leftBound ? -1 : 1;
            }
            if ((int)$left['sort'] === (int)$right['sort']) {
                return (int)$right['id'] <=> (int)$left['id'];
            }
            return (int)$left['sort'] <=> (int)$right['sort'];
        });

        return view('', [
            'app' => $app,
            'lines' => $lines,
            'boundIds' => $boundIds,
        ]);
    }

    public function saveLines()
    {
        $app = $this->getApp();
        $lineIds = input('line_ids/a', []);
        $lineNames = input('line_names/a', []);
        $lineIds = array_values(array_unique(array_filter(array_map('intval', $lineIds), function ($id) {
            return $id > 0;
        })));
        $publicNames = empty($lineIds) ? [] : Line::whereIn('id', $lineIds)->column('name', 'id');
        $lineIds = array_values(array_filter($lineIds, function ($id) use ($publicNames) {
            return isset($publicNames[$id]);
        }));
        $lineNamesById = [];
        foreach ($lineIds as $lineId) {
            $name = trim((string)($lineNames[$lineId] ?? ''));
            if ($name === '') {
                $name = (string)$publicNames[$lineId];
            }
            if (mb_strlen($name, 'UTF-8') > 255) {
                return self::returnData([], '节点名称最多255个字符', 4005);
            }
            $lineNamesById[$lineId] = $name;
        }

        Db::transaction(function () use ($app, $lineIds, $lineNamesById) {
            AppLine::where('app_id', $app->id)->delete();
            $now = time();
            foreach ($lineIds as $sort => $lineId) {
                AppLine::create([
                    'app_id' => $app->id,
                    'line_id' => $lineId,
                    'name' => $lineNamesById[$lineId],
                    'sort' => $sort + 1,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
            }
        });

        return self::returnData(['line_ids' => $lineIds]);
    }

    private function getApp(): AppConfigModel
    {
        $id = (int)input('id', input('app_id', 0));
        return $this->getAppById($id);
    }

    private function getAppById(int $id): AppConfigModel
    {
        $app = $id > 0 ? AppConfigModel::find($id) : null;
        if (!$app) {
            abort(404, 'app not found');
        }
        return $app;
    }

    private function buildAppStats(string $appName): array
    {
        $endTime = strtotime('yesterday');
        $startTime = strtotime('-29 days', $endTime);

        $stats = Stat::where('record_time', '>=', $startTime)
            ->where('record_time', '<=', $endTime)
            ->where('app_name', $appName)
            ->field('record_time,reg_count')
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
        $todayEnd = time();

        $todayRegCount = User::where('create_time', '>=', $todayStart)
            ->where('create_time', '<=', $todayEnd)
            ->where('app_name', $appName)
            ->count();

        return [
            'dates' => json_encode($dates),
            'regCounts' => json_encode($regCounts),
            'todayData' => [
                'reg_count' => max(0, (int)$todayRegCount),
            ],
        ];
    }
}
