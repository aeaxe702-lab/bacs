<?php

namespace app\back\middleware;

use app\model\Admin;
use think\facade\Cookie;

class BackAuth
{
    public function handle($request, \Closure $next)
    {
        $action = strtolower($request->controller() . '/' . $request->action());
        
        // 白名单，无需登录
        if (in_array($action, ['auth/login', 'auth/dologin', 'auth/install'])) {
            return $next($request);
        }
        // 兼容带命名空间的控制器名，如 "app\back\controller\auth/login"
        $shortAction = strtolower(class_basename($request->controller()) . '/' . $request->action());
        if (in_array($shortAction, ['auth/login', 'auth/dologin', 'auth/install'])) {
            return $next($request);
        }

        $token = Cookie::get('admin_token');
        $admin = $token ? Admin::findByToken($token) : null;

        if (!$admin) {
            if ($request->isAjax()) {
                return json(['msg' => '请先登录', 'error_code' => 4010, 'data' => []]);
            }
            $loginUrl = (string)url('auth/login');
            // iframe 内请求用 JS 跳出，直接访问用 302
            if ($request->server('HTTP_SEC_FETCH_DEST') === 'iframe') {
                return response(
                    "<script>window.top.location.href='{$loginUrl}';</script>",
                    200,
                    ['Content-Type' => 'text/html']
                );
            }
            return redirect($loginUrl);
        }

        // 把管理员信息挂到 request 上，方便后续使用
        $request->adminInfo = $admin;
        return $next($request);
    }
}
