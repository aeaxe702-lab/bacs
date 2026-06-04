<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class Admin extends Model
{
    protected $hidden = ['password'];

    public static function findByUsername(string $username): ?self
    {
        return self::where('username', $username)->find();
    }

    public static function findByToken(string $token): ?self
    {
        if (empty($token)) return null;
        return self::where('token', $token)->find();
    }

    public function verifyPassword(string $password): bool
    {
        return $this->password == $password;
    }

    public function generateToken(): string
    {
        $token = md5(uniqid((string)$this->id, true) . mt_rand());
        $this->token = $token;
        $this->save();
        return $token;
    }
}
