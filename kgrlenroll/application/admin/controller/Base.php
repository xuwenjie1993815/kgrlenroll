<?php
namespace app\admin\controller;
use think\View;
use think\Controller;
use think\Cookie;
class Base extends Controller
{
	//检查是否登录/接口状态
	public function _initialize(){
		
		//登陆
        if (!Cookie::get('adminInfo')) {
			header("Location: http://".$_SERVER['SERVER_NAME']."/kgrlenroll/public/index.php/admin/login/index.html"); die;
        }else{
        	$this->assign('adminInfo',Cookie::get('adminInfo'));
        }

    }  
}