<?php

namespace app\service;

use app\lib\exception\UserException;
use think\facade\Db;

class User
{
    public static function checkUserExists($userIDOrUUID, $throw = true, $appName = '')
    {
        if (strlen((string)$userIDOrUUID) > 12) {
            if ($appName !== '') {
                $query = Db::name('user')
                    ->where('app_name', $appName)
                    ->where('uuid_hash', self::uuidHash((string)$userIDOrUUID, (string)$appName));
            } else {
                $query = Db::name('user')->where('uuid', (string)$userIDOrUUID);
            }
        } else {
            $query = Db::name('user')->where('id', (int)$userIDOrUUID);
        }

        $user = $query
            ->field('id,uuid,login_time,last_time,last_login_ip,expire,app_name,create_time,update_time')
            ->find();

        if (!$user && $throw) {
            throw new UserException([
                'msg' => '用户不存在',
                'error_code' => 4001
            ]);
        }

        return $user;
    }

    public static function uuidHash(string $uuid, string $appName): string
    {
        return md5($appName . "\0" . $uuid);
    }
}
