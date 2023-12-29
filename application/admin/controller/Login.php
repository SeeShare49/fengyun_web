<?php

namespace app\admin\controller;

use think\captcha\Captcha;

use think\Controller;

use app\admin\model;
use think\facade\Log;

class Login extends Controller
{
    //登录界面
    public function index()
    {
        if (request()->isPost()) {
            $data = $_POST;
            $captcha = new Captcha();
            if (!$captcha->check($data['code'])) {
                $this->error('验证码错误!');
            }

            //$memberModel = db('users');
            $data['password'] = yw_ucenter_md5($data['password'], config('UC_AUTH_KEY'));
            $where[] =
                [
                    ['username', '=', $data['username']],
                    ['password', '=', $data['password']],
                ];
            $member = model\Users::where($where)->find();

            Log::write($member);
            if (!$member) {
                action_log('member_login_error', 'users', 0, 0, "登录失败，尝试登录。用户名：" . $data['username']);
                $this->error('用户名或密码错误！');
            }

            if ((int)$member['id'] != 1) {

                if ((int)$member['status'] < 0) {
                    $this->error('该用户已删除或禁用，请联系管理员！');
                }

                if ((int)$member['group_id'] == 0) {
                    $this->error('该用户没有被分配到任何用户权限组，无法登录！');
                }

                //判断用户组
                $authGroup = model\AuthGroup::find($member['group_id']);
                if (!$authGroup) {
                    $this->error('用户权限组不存在或已被删除，无法登录！');
                }
                if ((int)$authGroup['status'] != 1) {
                    $this->error('当前用户所在用户权限组已被删除或禁用，无法登录！');
                }
            }

            //登录，保存登录信息
            session('uid', $member['id']);
            //更新用户信息
            $updateData['id'] = $member['id'];
            $updateData['last_login_time'] = time();
            $updateData['last_login_ip'] = request()->ip();
            model\Users::update($updateData);
            action_log("member_login_success", "users", $member['id'], $member['id']);
            //跳转到首页
            $this->success('登录成功，正在跳转...', 'index/index', null, 10);

        } else {
            //判断是否存在登录信息，如果存在，直接跳转到后台首页。
            $uid = session('uid');
            if ($uid) {
                return redirect(url('index/index'));
            }
            return $this->fetch('login');
        }
    }


    /**
     * 后台退出
     */
    public function logout()
    {
        //session('ADMIN_MEMBER_RULES',null);
        session('uid', null);
        session('ADMIN_MENU_LIST', null);
        $this->success('退出成功，正在跳转...', 'admin/login/index');
    }


    /**
     * 验证码
     */
    public function img_captcha()
    {
        $config = [
            // 验证码字体大小
            'fontSize' => 20,
            // 验证码位数
            'length' => 4,
            'imageW' => 148,
            'imageH' => 38,
            'useCurve' => false,
            'useNoise' => false
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }
}
