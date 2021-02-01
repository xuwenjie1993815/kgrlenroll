<?php
namespace app\index\controller;
use think\View;
use think\Db;
use think\Controller;
use think\Config;
use think\Cookie;
class Login extends controller
{

    public function index()
    {
    	return view('Index/login');
    }

    public function login()
    {
    	$username = input('username');
    	$password = input('password');
    	$user = Db::table('user')->where('pid',$username)->where('state','1')->find();
    	if (!$user) {
    		return array('code' => 2,'msg' => '用户名或密码错误' );die;
    	}
    	$pass = md5(md5($password).$user['pwd_hash'].Config::get('QS_pwdhash'));
    	if ($pass == $user['password']) {
    		Cookie::set('userInfo',$user,3600);
    		return array('code' => 1,'msg' => '登陆成功,请稍等...' );die;
    	}else{
    		return array('code' => 2,'msg' => '用户名或密码错误' );die;
    	}
    }



    //注销登陆
    public function loginout()
    {
        Cookie::delete('userInfo');
        return array('code' => 1);
    }
}