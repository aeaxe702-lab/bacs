<?php

namespace app\api\controller;

use app\api\validate\LoginValidate;
use app\service\User as UserService;
use think\facade\Db;

class User extends Base
{
    private const USER_FIELDS = 'id,uuid,login_time,last_time,last_login_ip,expire,app_name,create_time,update_time';
    private const LOGIN_TOUCH_INTERVAL = 60;

    public function login()
    {
        $data = (new LoginValidate())->goCheck();
        $uuid = (string)$data['uuid'];
        $appName = (string)$data['app_name'];
        $uuidHash = UserService::uuidHash($uuid, $appName);
        $currentTime = time();
        $currentIp = $data['ip'];

        $user = $this->findUser($appName, $uuidHash);
        if ($user) {
            return self::returnData(
                $this->touchLogin($user, $currentTime, $currentIp),
                'login success'
            );
        }

        $user = $this->createUser($uuid, $uuidHash, $appName, $currentTime, $currentIp);
        return self::returnData($user, 'register success');
    }

    private function findUser(string $appName, string $uuidHash): ?array
    {
        $user = Db::name('user')
            ->field(self::USER_FIELDS)
            ->where('app_name', $appName)
            ->where('uuid_hash', $uuidHash)
            ->find();

        return $user ?: null;
    }

    private function touchLogin(array $user, int $currentTime, string $currentIp): array
    {
        $lastLoginTime = (int)$user['login_time'];
        if ($currentTime - $lastLoginTime < self::LOGIN_TOUCH_INTERVAL) {
            return $user;
        }

        Db::name('user')
            ->where('id', (int)$user['id'])
            ->update([
                'last_time' => $lastLoginTime,
                'login_time' => $currentTime,
                'last_login_ip' => $currentIp,
                'update_time' => $currentTime,
            ]);

        $user['last_time'] = $lastLoginTime;
        $user['login_time'] = $currentTime;
        $user['last_login_ip'] = $currentIp;
        $user['update_time'] = $currentTime;

        return $user;
    }

    private function createUser(
        string $uuid,
        string $uuidHash,
        string $appName,
        int $currentTime,
        string $currentIp
    ): array {
        $expire = $currentTime;

        $insertData = [
            'uuid' => $uuid,
            'uuid_hash' => $uuidHash,
            'login_time' => $currentTime,
            'last_time' => $currentTime,
            'last_login_ip' => $currentIp,
            'expire' => $expire,
            'app_name' => $appName,
            'create_time' => $currentTime,
            'update_time' => $currentTime,
        ];

        try {
            $userId = Db::name('user')->insertGetId($insertData);
        } catch (\Throwable $e) {
            if (!$this->isDuplicateUserError($e)) {
                throw $e;
            }

            $user = $this->findUser($appName, $uuidHash);
            if ($user) {
                return $this->touchLogin($user, $currentTime, $currentIp);
            }

            throw $e;
        }

        $insertData['id'] = (int)$userId;
        unset($insertData['uuid_hash']);

        return $insertData;
    }

    private function isDuplicateUserError(\Throwable $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, 'Duplicate entry') || str_contains($message, '1062');
    }
}
