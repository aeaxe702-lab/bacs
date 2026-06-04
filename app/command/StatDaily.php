<?php
declare(strict_types=1);

namespace app\command;

use app\model\AppConfig;
use app\model\Stat;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

class StatDaily extends Command
{
    protected function configure()
    {
        $this->setName('stat:daily')
            ->setDescription('Daily user registration stats');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('Start daily registration stats...');

        try {
            $yesterday = strtotime('yesterday');
            $startTime = strtotime(date('Y-m-d 00:00:00', $yesterday));
            $endTime = strtotime(date('Y-m-d 23:59:59', $yesterday));

            $dailyCounts = $this->getDailyCounts($startTime, $endTime);
            $appNames = $this->getAppNames(array_keys($dailyCounts));
            $this->cleanupLegacyEmptyAppStat($startTime, $appNames);
            $summary = 0;

            foreach ($appNames as $appName) {
                $statData = [
                    'record_time' => $startTime,
                    'app_name' => $appName,
                    'reg_count' => (int)($dailyCounts[$appName] ?? 0),
                ];
                $existStat = Stat::where('record_time', $startTime)
                    ->where('app_name', $appName)
                    ->find();

                if ($existStat) {
                    (new Stat())->update($statData, ['id' => $existStat['id']]);
                } else {
                    Stat::create($statData);
                }

                $summary += $statData['reg_count'];
            }

            $output->writeln('Registered users: ' . $summary);
            return 0;
        } catch (\Exception $e) {
            $output->error('Stats failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function getDailyCounts(int $startTime, int $endTime): array
    {
        $rows = Db::name('user')
            ->where('create_time', 'between', [$startTime, $endTime])
            ->field('app_name, COUNT(*) AS reg_count')
            ->group('app_name')
            ->select()
            ->toArray();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(string)$row['app_name']] = (int)$row['reg_count'];
        }

        return $counts;
    }

    private function getAppNames(array $activeAppNames): array
    {
        $configApps = AppConfig::column('app_name');
        $appNames = array_values(array_unique(array_merge($configApps, $activeAppNames)));
        sort($appNames);

        return $appNames;
    }

    private function cleanupLegacyEmptyAppStat(int $recordTime, array $appNames): void
    {
        if (in_array('', $appNames, true)) {
            return;
        }

        Stat::where('record_time', $recordTime)
            ->where('app_name', '')
            ->delete();
    }

}
