<?php
namespace app\admin\controller;
use think\View;
use think\Db;
use think\Controller;
use think\Config;
use think\Cookie;
use think\Common;

class Index extends Base
{
    public function index()
    {
        return view('Index/index');
    }

    public function welcome()
    {
        return view('Index/welcome');
    }

    //招聘管理
    public function recruit()
    {
        // 查询状态为1的用户数据 并且每页显示10条数据
        $recruit_list = Db::table('recruit')->where('state',1)->paginate(10);
        // 获取分页显示
        $page = $recruit_list->render();
        // 把分页数据赋值给模板变量list
        $this->assign('recruit_list', $recruit_list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    //查看招聘简章
    public function recruit_content()
    {
        $recruit_content = Db::table('recruit')->where('id',input('id'))->value('content');
        $this->assign('recruit_content', $recruit_content);
        return $this->fetch();
    }

    //新增招聘
    public function recruit_add()
    {
        if ($_POST) {
        if (isset($_POST['data']['top']) == true) {
                $_POST['data']['top'] = '1';
            }
            $_POST['data']['create_time'] = date('Y-m-d H:i:s',time());
            Db::table('recruit')->insert($_POST['data']);
        }
        return view('Index/recruit_add');
    }

    //报名时间安排
    public function recruit_plan()
    {
        $id = input('id');
        $recruit_plan_list = Db::table('recruit_plan')->where('recruit_id',$id)->select();
        $this->assign('recruit_plan_list', $recruit_plan_list);
        $this->assign('id', $id);
        return $this->fetch();
    }

    //新增报名时间安排
    public function recruit_plan_add()
    {
        if ($_POST) {
            //判断是否多次添加todo
            
            $time = explode(' - ', $_POST['time']);
            $data['start_time'] = $time['0'];
            $data['end_time'] = $time['1'];
            $data['type'] = $_POST['type'];
            $data['recruit_id'] = $_POST['id'];
            Db::table('recruit_plan')->insert($data);
            return array('code' => 1);
        }
        $this->assign('id', input('id'));
        return $this->fetch();
    }

    //招聘岗位信息
    public function recruit_job()
    {
        $id = input('id');
        $recruit_job_list = Db::table('recruit_job')->where('recruit_id',$id)->select();
        $this->assign('recruit_job_list', $recruit_job_list);
        $this->assign('id', $id);
        return $this->fetch();
    }

    //新增岗位信息
    public function recruit_job_add()
    {
        if ($_POST) {
            if (isset($_POST['data']['quanrizhi']) == true) {
                $_POST['data']['quanrizhi'] = '1';
            }else{
                $_POST['data']['quanrizhi'] = '0';
            }
            if (isset($_POST['data']['dangyuan']) == true) {
                $_POST['data']['dangyuan'] = '1';
            }
            if (isset($_POST['data']['junren']) == true) {
                $_POST['data']['junren'] = '1';
            }
            $_POST['data']['create_time'] = date('Y-m-d H:i:s',time());
            $_POST['data']['recruit_id'] = $_POST['recruit_id'];
            Db::table('recruit_job')->insert($_POST['data']);
            return array('code' => 1);
        }
        $this->assign('id', input('id'));
        return $this->fetch();
    }

    //准考证
    public function admission_ticket()
    {
        return view('Index/admission_ticket');
    }

    //修改密码
    public function change_password()
    {
        if ($_POST) {
            $pwd_hash = $this->getRand(10);
            $data['pwd_hash'] = $pwd_hash;
            $data['password'] = md5(md5(input('newpassword')).$pwd_hash.Config::get('QS_pwdhash'));
            $res = Db::table('admin')->where('id',Cookie::get('adminInfo')['id'])->update($data);
            if ($res) {
                Cookie::delete('adminInfo');
                return array('code' => 1);
            }else{
                return array('code' => 2,'msg' => '操作失败,请重试');
            }
        }
        return view('Index/change_password');
    }

    //生成随机字符
    public function getRand($length = 8){
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = "";
        for ( $i = 0; $i < $length; $i++ )
        {
            $str .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $str ;
    }

   	
}
