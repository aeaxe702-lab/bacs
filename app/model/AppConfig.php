<?php

namespace app\model;

class AppConfig extends Base
{
    protected $name = 'app_config';
    protected $append = ['status_text', 'status_class'];

    public const PROJECT_CONFIG_FIELDS = [
        'banner_position_id',
        'interstitial_position_id',
        'ad_interval_minutes',
        'ad_continuous_count',
        'popup_enabled',
        'popup_title',
        'popup_content',
        'popup_button_title',
    ];

    public const STATUS_DEV = 0;
    public const STATUS_RELEASED = 1;
    public const STATUS_SUBMITTED = 2;
    public const STATUS_REVIEWING = 3;
    public const STATUS_APPROVED = 4;
    public const STATUS_BANNED = 5;

    public static function statuses(): array
    {
        return [
            self::STATUS_DEV => '开发中',
            self::STATUS_SUBMITTED => '已提审',
            self::STATUS_REVIEWING => '审核中',
            self::STATUS_APPROVED => '已过审(未发布)',
            self::STATUS_RELEASED => '已发布',
            self::STATUS_BANNED => '已封号',
        ];
    }

    public static function statusOptions(): array
    {
        $options = [];
        foreach (self::statuses() as $value => $text) {
            $options[] = [
                'value' => $value,
                'text' => $text,
            ];
        }

        return $options;
    }

    public function getStatusTextAttr($value, $data): string
    {
        $status = (int)($data['status'] ?? self::STATUS_DEV);
        return self::statuses()[$status] ?? '未知';
    }

    public function getStatusClassAttr($value, $data): string
    {
        $status = (int)($data['status'] ?? self::STATUS_DEV);
        $classes = [
            self::STATUS_DEV => 'warning',
            self::STATUS_SUBMITTED => 'primary',
            self::STATUS_REVIEWING => 'info',
            self::STATUS_APPROVED => 'secondary',
            self::STATUS_RELEASED => 'success',
            self::STATUS_BANNED => 'danger',
        ];

        return $classes[$status] ?? 'secondary';
    }

    public function projectConfig(): array
    {
        return [
            'banner_position_id' => (string)$this->getAttr('banner_position_id'),
            'interstitial_position_id' => (string)$this->getAttr('interstitial_position_id'),
            'ad_interval_minutes' => (int)$this->getAttr('ad_interval_minutes'),
            'ad_continuous_count' => (int)$this->getAttr('ad_continuous_count'),
            'popup_enabled' => (int)$this->getAttr('popup_enabled'),
            'popup_title' => (string)$this->getAttr('popup_title'),
            'popup_content' => (string)$this->getAttr('popup_content'),
            'popup_button_title' => (string)$this->getAttr('popup_button_title'),
        ];
    }
}
