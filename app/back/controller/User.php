<?php

namespace app\back\controller;

use app\back\validate\UserUpdateMemberValidate;
use app\model\User as UserModel;

class User extends Base
{
    private const LIST_FIELDS = 'id,uuid,login_time,last_time,last_login_ip,expire,app_name,create_time';

    public function index()
    {
        $searchId = (int)input('search_id', 0);
        $appContext = $this->getAppContext();
        $appId = $appContext['appId'];
        $appName = $appContext['appName'];
        $days = min(30, max(1, (int)input('days', 7)));
        $perPage = (int)input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 30, 50, 100], true) ? $perPage : 20;

        if ($searchId > 0) {
            $query = UserModel::field(self::LIST_FIELDS)->where('id', $searchId);
            if ($appName !== '') {
                $query->where('app_name', $appName);
            }

            return view('', [
                'users' => $query->paginate(1),
                'days' => $days,
                'perPage' => 20,
                'searchId' => $searchId,
                'appId' => $appId,
                'appName' => $appName,
                'appIdJson' => json_encode($appId),
                'appNameJson' => json_encode($appName, JSON_UNESCAPED_UNICODE),
            ]);
        }

        $startTime = strtotime("-{$days} days");
        $query = UserModel::field(self::LIST_FIELDS)->where('create_time', '>=', $startTime);
        if ($appName !== '') {
            $query->where('app_name', $appName);
        }

        $users = $query->order('create_time', 'desc')
            ->order('id', 'desc')
            ->paginate([
                'list_rows' => $perPage,
                'query' => request()->param(),
            ], true);

        return view('', [
            'users' => $users,
            'days' => $days,
            'perPage' => $perPage,
            'searchId' => '',
            'appId' => $appId,
            'appName' => $appName,
            'appIdJson' => json_encode($appId),
            'appNameJson' => json_encode($appName, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function get()
    {
        $userId = (int)input('user_id', 0);
        $user = $userId > 0 ? UserModel::find($userId) : null;
        if (!$user) {
            return self::returnData([], '用户不存在', 4000);
        }

        return self::returnData(['user' => $user->toArray()]);
    }

    public function updateMemberExpire()
    {
        $data = (new UserUpdateMemberValidate())->goCheck();
        $user = UserModel::find($data['user_id']);
        if (!$user) {
            return self::returnData([], '用户不存在', 4000);
        }

        $user->expire = (int)$data['expire_time'];
        $user->save();

        return self::returnData([
            'user_id' => $user->id,
            'field' => 'expire',
            'new_expire' => $user->expire,
        ]);
    }
}
