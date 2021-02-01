<?php
namespace app\index\controller;
use think\View;
use think\Db;
use think\Controller;
use think\Config;
use think\Cookie;
use think\Common;

define('RSA_PUBLIC', '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCK/yg9ccAyKyJwC/3kiYYsFlHX/ZPLUoqYvly+UkkBUr5PzIYV02qUmEg4wweO+/eDsFlqPgaR33FQ5+fCNkTfrCX5mzw9A+qSZ0Rznub5e1Kmf8gjyPqVDnLmwKEa8jrEqF3xo5cab26AmRpwCiy1aB+S0o/CqmJDJxUykYJ7BQIDAQAB
-----END PUBLIC KEY-----');
 
 
define('RSA_PRIVATE','-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAIr/KD1xwDIrInAL/eSJhiwWUdf9k8tSipi+XL5SSQFSvk/MhhXTapSYSDjDB47794OwWWo+BpHfcVDn58I2RN+sJfmbPD0D6pJnRHOe5vl7UqZ/yCPI+pUOcubAoRryOsSoXfGjlxpvboCZGnAKLLVoH5LSj8KqYkMnFTKRgnsFAgMBAAECgYEAgNicJbEfV6ISj0keds5g2Ndr0MuYSD7giUzVTfua/yYDkpdlqC/NuaccM7nedNXvEFzV1h1fG7PEKBqBBNAnsM33gHjNLn5Xi1/DnPt1ccK0XF0A7BU2DQlqkFKavnBimOAOG9ksxewy3ZJsmtxks3UYu+UJWpNHqB6wAa56M2ECQQC+U6TDf37gndljqSwT/vh7oMC77cdtgzgrzo3x0pnOKxllVTYZpMUdoYZ535JF28Z8lPtIymPLQ0dbxyhYhOJ5AkEAuvVRWR1VQEuoZKDCNacRAcJcbGERHf0g6BaKhM5BcmbvG4MFYNMVGXc1NETx3PG0FyIzUtmRzonL4E52x5cZ7QJBAITL2bdqWv2gRZEK9a1SBtBDvpahdreLigLO0S18c0Jtwf95MBE+bSaakDiy7N1/VgOQ86+7P1wQqlZ4JEd3GIkCQAoUIX+JWkguC/Toya90wzDyFmNtVCvmsnhwhqUkLVkKfYdhJ9ARcQi/aWnY8aT0jr3UhSnJOtgEi64a7MJTvf0CQFgktaTvBWvLPjJbp1eNfgmPgoSe3xgviLthOlsIv0Y47+KX4VaS2fcqNP4eIUSJ3rllD9m3pE5Oh7jl3ZO+12I=
-----END PRIVATE KEY-----');

class Index extends Controller
{

	//检查是否登录/接口状态
	public function _initialize(){
		//登陆
        if (!Cookie::get('userInfo')) {
        	$this->assign('userInfo','');
        }else{
        	$this->assign('userInfo',Cookie::get('userInfo'));
        }

    }  

    public function index()
    {

    	$recruit_list = Db::table('recruit')->alias('r')->join('recruit_plan p','r.id = p.recruit_id')->where('r.state','1')->where('p.type','1')->limit(3)->order('r.create_time',desc)->field('r.*,p.start_time,p.end_time')->select();
        $this->assign('recruit_list', $recruit_list);
        return view('Index/index');
    }

    public function detail()
    {
    	$id = input('id');
    	$info = Db::table('recruit')->where('id',$id)->find();
    	$plan = Db::table('recruit_plan')->where('recruit_id',$id)->where('type','1')->find();
    	$job = Db::table('recruit_job')->where('recruit_id',$id)->where('state','1')->select();
        $this->assign('info', $info);
        $this->assign('plan', $plan);
        $this->assign('job', $job);
    	return view('Index/detail');
    }

    public function recruitJobList()
    {
    	$id = input('id');
    	$job = Db::table('recruit_job')->where('recruit_id',$id)->where('state','1')->select();
        $this->assign('job', $job);
        return $this->fetch();
    }

    //报名页面
    public function enroll()
    {
    	//登陆
        if (!Cookie::get('userInfo')) {
			header("Location: http://".$_SERVER['SERVER_NAME']."/kgrlenroll/public/index.php/index/login/index.html"); die;
        }else{
        	$this->assign('userInfo',Cookie::get('userInfo'));
        	
        }
    	if ($_POST) {
    		//如果已有账号，则修改信息
    		//如果没有账号，则生成账号
    		$userInfo = Cookie::get('userInfo');
    		$user = Db::table('user')->where('id',$userInfo['id'])->where('state','1')->find();
    		if ($user) {
    			Db::table('user')->where('id',$user['id'])->update($_POST);
    			Cookie::delete('userInfo');
    			Cookie::set('userInfo',$user,3600);
    			return array('code'=>'1','user_id' => $user['id']);
    		}else{
	    		$user_id = Db::table('user')->insertGetId($_POST);
	    		return array('code'=>'1','user_id' => $user_id);
    		}
    	}

    	$job_id = input('job_id');
    	$job_info = Db::table('recruit_job')->where('id',$job_id)->find();
    	$recruit_info = Db::table('recruit')->where('id',$job_info['recruit_id'])->find();
    	$plan = Db::table('recruit_plan')->where('recruit_id',$job_info['recruit_id'])->where('type','1')->find();
        $this->assign('job_info', $job_info);
        $this->assign('recruit_info', $recruit_info);
        $this->assign('plan', $plan);
    	return $this->fetch();
    }

    public function enroll_check_state()
    {
    	//检查是否已经报名该单位
    	$enroll_info = Db::table('enroll')->where('user_id',Cookie::get('userInfo')['id'])->where('job_id',input('job_id'))->find();
    	if ($enroll_info) {        		
    		return array('code'=>'2','msg'=>'请勿重复报名');
    	}else{
    		return array('code' => '1');
    	}
    }

    //报名流程 步骤2 上传文件
    public function enroll_file()
    {
    	//登陆
        if (!Cookie::get('userInfo')) {
			header("Location: http://".$_SERVER['SERVER_NAME']."/kgrlenroll/public/index.php/index/login/index.html"); die;
        }else{
        	$this->assign('userInfo',Cookie::get('userInfo'));
        }
    	$job_id = input('job_id');
    	$user_id = Cookie::get('userInfo')['id'];
    	$job_info = Db::table('recruit_job')->where('id',$job_id)->find();
    	$recruit_info = Db::table('recruit')->where('id',$job_info['recruit_id'])->find();
    	$plan = Db::table('recruit_plan')->where('recruit_id',$job_info['recruit_id'])->where('type','1')->find();

    	//进入界面时获取该用户已上传的文件
    	$file_list = Db::table('enroll_file')->where('user_id',$user_id)->where('job_id',$job_id)->where('state','1')->select();
        $this->assign('file_list', $file_list);

        $this->assign('job_info', $job_info);
        $this->assign('recruit_info', $recruit_info);
        $this->assign('plan', $plan);
        $this->assign('user_id', $user_id);
    	return $this->fetch();
    }

  


	public function uploadFilePid(){
		//登陆
        if (!Cookie::get('userInfo')) {
			header("Location: http://".$_SERVER['SERVER_NAME']."/kgrlenroll/public/index.php/index/login/index.html"); die;
        }else{
        	$this->assign('userInfo',Cookie::get('userInfo'));
        }
		$user_id = Cookie::get('userInfo')['id'];
		$job_id = input('job_id');
		//上传文件目录获取
		// $month = date('Ym',time());
		define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
		$imgpath=$_GET['imgpath'];  //获取传来的图片分类，用于在服务器上分类存放
		
		// $dir = BASE_PATH."upload/".$month."/";
		$user = Db::table('user')->where('id',$user_id)->find();
		$dir=$_SERVER['DOCUMENT_ROOT'].$imgpath."/kgrlenroll/public/upload/".$user['pid']."/pid/";
		//获取后缀名
		 // $temp = explode(".", $_FILES["file"]["name"]);
		// $extension = end($temp);
		// $_FILES["file"]["name"] = $user['pid'].'pid'.$_FILES["file"]["name"].'.'.$extension;
		//初始化返回数组
		$arr = array(
		'code' => 0,
		'msg'=> '',
		'data' =>array(
		     'src' => $dir . $_FILES["file"]["name"]
		     ),
		);
		$file_info = $_FILES['file'];
		 $file_error = $file_info['error'];
		if(!is_dir($dir))//判断目录是否存在
		{
		    mkdir ($dir,0777,true);//如果目录不存在则创建目录
		};
		$file = $dir.$_FILES["file"]["name"];
		if(!file_exists($file))
		{
		if($file_error == 0){
		        if(move_uploaded_file($_FILES["file"]["tmp_name"],$dir. $_FILES["file"]["name"])){
		           $arr['msg'] ="上传成功";
		           //上传成功后，把文件url存入数据库
		           $data['url'] = $imgpath."/kgrlenroll/public/upload/".$user['pid']."/pid/". $_FILES["file"]["name"];
		           $data['type'] = 'pid';
		           $data['user_id'] = $user_id;
		           $data['job_id'] = $job_id;
		           $data['file_name_id'] = $this->trimall(explode(".", $_FILES["file"]["name"])[0]);
		           $data['servers_file'] = $file;
		           Db::table('enroll_file')->insert($data);
		        }else{
		           $arr['msg'] = "上传失败";
		        }
		    }else{
		        switch($file_error){
		            case 1:
		           $arr['msg'] ='上传文件超过了PHP配置文件中upload_max_filesize选项的值';
		                break;
		            case 2:
		              $arr['msg'] ='超过了表单max_file_size限制的大小';
		                break;
		            case 3:
		               $arr['msg'] ='文件部分被上传';
		                break;
		            case 4:
		              $arr['msg'] ='没有选择上传文件';
		                break;
		            case 6:
		                $arr['msg'] ='没有找到临时文件';
		                break;
		            case 7:
		            case 8:
		               $arr['msg'] = '系统错误';
		                break;
		        }
		    }
		}
		else
		{
			$arr['code'] ="1"; 
		    $arr['msg'] = "当前目录中，文件".$file."已存在";
		} 
		echo json_encode($arr);
	}


	public function uploadFileEducation(){
		//登陆
        if (!Cookie::get('userInfo')) {
			header("Location: http://".$_SERVER['SERVER_NAME']."/kgrlenroll/public/index.php/index/login/index.html"); die;
        }else{
        	$this->assign('userInfo',Cookie::get('userInfo'));
        }
		$user_id =  Cookie::get('userInfo')['id'];
		$job_id = input('job_id');
		//上传文件目录获取
		// $month = date('Ym',time());
		define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
		$imgpath=$_GET['imgpath'];  //获取传来的图片分类，用于在服务器上分类存放
		
		// $dir = BASE_PATH."upload/".$month."/";
		$user = Db::table('user')->where('id',$user_id)->find();
		$dir=$_SERVER['DOCUMENT_ROOT'].$imgpath."/kgrlenroll/public/upload/".$user['pid']."/education/";
		//获取后缀名
		// $temp = explode(".", $_FILES["file"]["name"]);
		// $extension = end($temp);
		// $_FILES["file"]["name"] = $user['pid'].'pid'.$_FILES["file"]["name"].'.'.$extension;
		//初始化返回数组
		$arr = array(
		'code' => 0,
		'msg'=> '',
		'data' =>array(
		     'src' => $dir . $_FILES["file"]["name"]
		     ),
		);
		$file_info = $_FILES['file'];
		 $file_error = $file_info['error'];
		if(!is_dir($dir))//判断目录是否存在
		{
		    mkdir ($dir,0777,true);//如果目录不存在则创建目录
		};
		$file = $dir.$_FILES["file"]["name"];
		if(!file_exists($file))
		{
		if($file_error == 0){
		        if(move_uploaded_file($_FILES["file"]["tmp_name"],$dir. $_FILES["file"]["name"])){
		           $arr['msg'] ="上传成功";
		           //上传成功后，把文件url存入数据库
		           $data['url'] = $imgpath."/kgrlenroll/public/upload/".$user['pid']."/education/". $_FILES["file"]["name"];
		           $data['type'] = 'education';
		           $data['user_id'] = $user_id;
		           $data['job_id'] = $job_id;
		           $data['file_name_id'] = $this->trimall(explode(".", $_FILES["file"]["name"])[0]);
		           $data['servers_file'] = $file;
		           Db::table('enroll_file')->insert($data);
		        }else{
		           $arr['msg'] = "上传失败";
		        }
		    }else{
		        switch($file_error){
		            case 1:
		           $arr['msg'] ='上传文件超过了PHP配置文件中upload_max_filesize选项的值';
		                break;
		            case 2:
		              $arr['msg'] ='超过了表单max_file_size限制的大小';
		                break;
		            case 3:
		               $arr['msg'] ='文件部分被上传';
		                break;
		            case 4:
		              $arr['msg'] ='没有选择上传文件';
		                break;
		            case 6:
		                $arr['msg'] ='没有找到临时文件';
		                break;
		            case 7:
		            case 8:
		               $arr['msg'] = '系统错误';
		                break;
		        }
		    }
		}
		else
		{
			$arr['code'] ="1"; 
		    $arr['msg'] = "当前目录中，文件".$file."已存在";
		} 
		echo json_encode($arr);
	}

	public function uploadFileCunzhao(){
		//登陆
        if (!Cookie::get('userInfo')) {
			header("Location: http://".$_SERVER['SERVER_NAME']."/kgrlenroll/public/index.php/index/login/index.html"); die;
        }else{
        	$this->assign('userInfo',Cookie::get('userInfo'));
        }
		$user_id = Cookie::get('userInfo')['id'];
		$job_id = input('job_id');
		//上传文件目录获取
		// $month = date('Ym',time());
		define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
		$imgpath=$_GET['imgpath'];  //获取传来的图片分类，用于在服务器上分类存放
		
		// $dir = BASE_PATH."upload/".$month."/";
		$user = Db::table('user')->where('id',$user_id)->find();
		$dir=$_SERVER['DOCUMENT_ROOT'].$imgpath."/kgrlenroll/public/upload/".$user['pid']."/cunzhao/";
		//获取后缀名
		// $temp = explode(".", $_FILES["file"]["name"]);
		// $extension = end($temp);
		// $_FILES["file"]["name"] = $user['pid'].'pid'.$_FILES["file"]["name"].'.'.$extension;
		//初始化返回数组
		$arr = array(
		'code' => 0,
		'msg'=> '',
		'data' =>array(
		     'src' => $dir . $_FILES["file"]["name"]
		     ),
		);
		$file_info = $_FILES['file'];
		 $file_error = $file_info['error'];
		if(!is_dir($dir))//判断目录是否存在
		{
		    mkdir ($dir,0777,true);//如果目录不存在则创建目录
		};
		$file = $dir.$_FILES["file"]["name"];
		if(!file_exists($file))
		{
		if($file_error == 0){
		        if(move_uploaded_file($_FILES["file"]["tmp_name"],$dir. $_FILES["file"]["name"])){
		           $arr['msg'] ="上传成功";
		           //上传成功后，把文件url存入数据库
		           $data['url'] = $imgpath."/kgrlenroll/public/upload/".$user['pid']."/cunzhao/". $_FILES["file"]["name"];
		           $data['type'] = 'cunzhao';
		           $data['user_id'] = $user_id;
		           $data['job_id'] = $job_id;
		           $data['file_name_id'] = $this->trimall(explode(".", $_FILES["file"]["name"])[0]);
		           $data['servers_file'] = $file;
		           Db::table('enroll_file')->insert($data);
		        }else{
		           $arr['msg'] = "上传失败";
		        }
		    }else{
		        switch($file_error){
		            case 1:
		           $arr['msg'] ='上传文件超过了PHP配置文件中upload_max_filesize选项的值';
		                break;
		            case 2:
		              $arr['msg'] ='超过了表单max_file_size限制的大小';
		                break;
		            case 3:
		               $arr['msg'] ='文件部分被上传';
		                break;
		            case 4:
		              $arr['msg'] ='没有选择上传文件';
		                break;
		            case 6:
		                $arr['msg'] ='没有找到临时文件';
		                break;
		            case 7:
		            case 8:
		               $arr['msg'] = '系统错误';
		                break;
		        }
		    }
		}
		else
		{
			$arr['code'] ="1"; 
		    $arr['msg'] = "当前目录中，文件".$file."已存在";
		} 
		echo json_encode($arr);
	}

	public function uploadFileOther(){
		//登陆
        if (!Cookie::get('userInfo')) {
			header("Location: http://".$_SERVER['SERVER_NAME']."/kgrlenroll/public/index.php/index/login/index.html"); die;
        }else{
        	$this->assign('userInfo',Cookie::get('userInfo'));
        }
		$user_id = Cookie::get('userInfo')['id'];
		$job_id = input('job_id');
		//上传文件目录获取
		// $month = date('Ym',time());
		define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
		$imgpath=$_GET['imgpath'];  //获取传来的图片分类，用于在服务器上分类存放
		
		// $dir = BASE_PATH."upload/".$month."/";
		$user = Db::table('user')->where('id',$user_id)->find();
		$dir=$_SERVER['DOCUMENT_ROOT'].$imgpath."/kgrlenroll/public/upload/".$user['pid']."/other/";
		//获取后缀名
		// $temp = explode(".", $_FILES["file"]["name"]);
		// $extension = end($temp);
		// $_FILES["file"]["name"] = $user['pid'].'pid'.$_FILES["file"]["name"].'.'.$extension;
		//初始化返回数组
		$arr = array(
		'code' => 0,
		'msg'=> '',
		'data' =>array(
		     'src' => $dir . $_FILES["file"]["name"]
		     ),
		);
		$file_info = $_FILES['file'];
		 $file_error = $file_info['error'];
		if(!is_dir($dir))//判断目录是否存在
		{
		    mkdir ($dir,0777,true);//如果目录不存在则创建目录
		};
		$file = $dir.$_FILES["file"]["name"];
		if(!file_exists($file))
		{
		if($file_error == 0){
		        if(move_uploaded_file($_FILES["file"]["tmp_name"],$dir. $_FILES["file"]["name"])){
		           $arr['msg'] ="上传成功";
		           //上传成功后，把文件url存入数据库
		           $data['url'] = $imgpath."/kgrlenroll/public/upload/".$user['pid']."/other/". $_FILES["file"]["name"];
		           $data['type'] = 'other';
		           $data['user_id'] = $user_id;
		           $data['job_id'] = $job_id;
		           $data['file_name_id'] = $this->trimall(explode(".", $_FILES["file"]["name"])[0]);
		           $data['servers_file'] = $file;
		           Db::table('enroll_file')->insert($data);
		        }else{
		           $arr['msg'] = "上传失败";
		        }
		    }else{
		        switch($file_error){
		            case 1:
		           $arr['msg'] ='上传文件超过了PHP配置文件中upload_max_filesize选项的值';
		                break;
		            case 2:
		              $arr['msg'] ='超过了表单max_file_size限制的大小';
		                break;
		            case 3:
		               $arr['msg'] ='文件部分被上传';
		                break;
		            case 4:
		              $arr['msg'] ='没有选择上传文件';
		                break;
		            case 6:
		                $arr['msg'] ='没有找到临时文件';
		                break;
		            case 7:
		            case 8:
		               $arr['msg'] = '系统错误';
		                break;
		        }
		    }
		}
		else
		{
			$arr['code'] ="1"; 
		    $arr['msg'] = "当前目录中，文件".$file."已存在";
		} 
		echo json_encode($arr);
	}

	public function enroll_del(){

		$servers_file = Db::table('enroll_file')->where('file_name_id',input('file_name_id'))->where('user_id',Cookie::get('userInfo')['id'])->where('job_id',input('job_id'))->where('type',input('type'))->where('state',1)->value('servers_file');
		Db::table('enroll_file')->where('file_name_id',input('file_name_id'))->where('user_id',Cookie::get('userInfo')['id'])->where('job_id',input('job_id'))->where('type',input('type'))->where('state',1)->delete();
		//删除该文件
		unlink($servers_file);
		return array('code'=>'1');
	}


	// 报名流程 核对信息
	public function enroll_check(){
		//登陆
        if (!Cookie::get('userInfo')) {
			header("Location: http://".$_SERVER['SERVER_NAME']."/kgrlenroll/public/index.php/index/login/index.html"); die;
        }else{
        	$this->assign('userInfo',Cookie::get('userInfo'));
        }
		$job_id = input('job_id');
    	$user_id = Cookie::get('userInfo')['id'];
    	$job_info = Db::table('recruit_job')->where('id',$job_id)->find();
    	$recruit_info = Db::table('recruit')->where('id',$job_info['recruit_id'])->find();
    	$plan = Db::table('recruit_plan')->where('recruit_id',$job_info['recruit_id'])->where('type','1')->find();

    	//进入界面时获取该用户已上传的文件
    	$file_list = Db::table('enroll_file')->where('user_id',$user_id)->where('job_id',$job_id)->where('state','1')->select();
    	$user_info = Db::table('user')->where('id',$user_id)->find();
        $this->assign('file_list', $file_list);

        $this->assign('job_info', $job_info);
        $this->assign('recruit_info', $recruit_info);
        $this->assign('plan', $plan);
        $this->assign('user_info', $user_info);
        $this->assign('user_id', $user_id);
    	return $this->fetch();
	}

	//报名流程 提交报名信息
	public function enroll_end(){
		//登陆
        if (!Cookie::get('userInfo')) {
			header("Location: http://".$_SERVER['SERVER_NAME']."/kgrlenroll/public/index.php/index/login/index.html"); die;
        }else{
        	$this->assign('userInfo',Cookie::get('userInfo'));
        }
		$data['user_id'] = Cookie::get('userInfo')['id'];
		$data['job_id'] = input('job_id');
		$enroll_info = Db::table('enroll')->where('job_id',input('job_id'))->where('user_id',$data['user_id'])->find();
		if ($enroll_info) {
			return array('code'=>'1');
		}else{
			Db::table('enroll')->insert($data);
			return array('code'=>'1');
		}
	}

	//报名状态页面
	public function enroll_state(){
		//登陆
        if (!Cookie::get('userInfo')) {
			header("Location: http://".$_SERVER['SERVER_NAME']."/kgrlenroll/public/index.php/index/login/index.html"); die;
        }else{
        	$this->assign('userInfo',Cookie::get('userInfo'));
        }
        //获取时间安排
        $job_info = Db::table('recruit_job')->where('id',input('job_id'))->find();
    	// $recruit_info = Db::table('recruit')->where('id',$job_info['recruit_id'])->find();
    	$recruit_plan = Db::table('recruit_plan')->where('id',$job_info['recruit_id'])->where('type','2')->find();
        $this->assign('job_info', $job_info);
        $this->assign('recruit_plan', $recruit_plan);
    	return $this->fetch();
	}

	//去掉字符串中的空格
	public function trimall($str)//删除空格
	{
	    $qian=array(" ","　","\t","\n","\r");$hou=array("","","","","");
	    return str_replace($qian,$hou,$str);   
	}



	public function test()
	{

		
		$str = array(
			'aaa100'=>'AAB022',
		);
		$str = json_encode($str);
		$cdata = $this->PublicEncrypt($str);
		$data['clientid'] = '115';
		// $data['aaa100'] = 'AAB019';
		$data['param'] = $cdata;
		$url = "https://ggfw.rlsbj.cq.gov.cn/cqjy/n/dmzthirdinterface/webservice"."/getDicts";
        $res = http_request($url, $data,'');
        $res = json_decode($res,1);

		// echo "<hr>";
		// $str = '封装测试';
		//调用java RSA解密接口
		// $res['output'] = 'FPsylE342HTiIO2TUucSZrrA8SkvZch9eqAd991GRpPNGUEKSLvugnqot+RrpeMan5nwOblPiatmSvjvl582uBowdXk6gy1MQEzEs62JD3c8GGuXeP1WfAKaB8eBDJj9em+KF09L6doznSRC1A7ge3vfYujIenDES18+MY8Xupc=';
		$ddata = $this->PrivateDecrypt($res['output']);
		var_dump($ddata);
 
	}





	public function tongbu_unit(){
    	$maxcount = 1;      //每次上传最大条数
		$company_count = 0; //企业上传条数
		$job_count_sum = 0;
		//查询已同步的企业数据
		$cqhrssInfo = Db::table('hr_cqhrss_sync')->where('type','company')->order('synctime desc')->limit('0,1')->select();
		// $maxid = $cqhrssInfo[0]['maxid'];
		$maxid = '79202';
		//查询审核通过的企业
		$member_sql = "SELECT m.email member_email, m.username member_username, p.* FROM ".
	    "(SELECT * FROM hr_members WHERE utype = 1 AND uid > ".$maxid.") m ".
	    "LEFT JOIN hr_company_profile p ON m.uid = p.uid WHERE p.audit = 1 AND p.id > 0 ORDER BY m.uid ASC LIMIT 0, ".$maxcount ;
		$companys = Db::query($member_sql);
		if ($companys) {
			foreach ($companys as $key => $company) {

				$maxid = $company['uid'];
				$unit_id = $this->addUnitNew($company);
				var_dump($unit_id);die;
				$company_count++;
				if ($unit_id) {
					$job_count = $this->addPostJobNew($company,$unit_id);
					$job_count_sum += $job_count;
				}
			}
		}
		$setarr = array();
		$setarr['type'] = 'company';
		$setarr['synctime'] = time();
		$setarr['maxid'] = $maxid;
		$setarr['company_count'] = $company_count;
		$setarr['job_count'] = $job_count_sum;
		$aa = Db::table('hr_cqhrss_sync')->insert($setarr);
		if ($aa) {
			return 'OK';
		}
    }


    public function addUnitNew($company){
    	$maxid = $company['uid'];
		$addUnit['unitname'] = $company['companyname'];
		//todo  需更改
		// $addUnit['organcode'] = '91500112666437516G';
		// $addUnit['businessid'] = '0';
		//单位类型字典
		$comtype = config('comtype');
		$addUnit['comtype'] = $comtype[$company['nature_cn']];
		//经济类别字典
		$addUnit['commericaltype'] = '0';
		//企业规模字典
		$comscale = config('comscale');
		$addUnit['comscale'] = $comscale[$company['scale_cn']];
		$addUnit['comaddress'] = $company['district_cn'];
		//所属行业字典
		$combusiness = config('combusiness');
		$addUnit['combusiness'] = $combusiness[$company['trade_cn']];
		$addUnit['psncontace'] = $company['contact'];
		$addUnit['zipcode'] = '0';
		$addUnit['addrcontact'] = $company['address'];
		$addUnit['phonecontact'] = $company['telephone'];
		$addUnit['fax'] = '0';
		$addUnit['comwebsite'] = $company['website'];
		$addUnit['registmoney'] = $company['registered'];
		$addUnit['jyfw'] = '0';
		$addUnit['coordinatex'] = $company['map_x'];
		$addUnit['coordinatey'] = $company['map_y'];
		$addUnit['geoid'] = '0';
		$addUnit['gljgid'] = '0';
		$addUnit['legle'] = '0';
		$addUnit['comdescription'] = '0';
		$addUnit['email'] = $company['email'];
		$addUnit = json_encode($addUnit);
		$cdata = $this->PublicEncrypt($addUnit);
		$data['clientid'] = '115';
		$data['param'] = $cdata;
		var_dump($cdata);die;
		// $url = "https://ggfw.rlsbj.cq.gov.cn/cqjy/n/dmzthirdinterface/webservice"."/addUnitNew";
		//测试url
		$url = "http://158u51320j.iok.la/dmzthirdinterface/webservice"."/addUnitNew";
		$res = http_request($url, $data,'');
		$res = json_decode($res,1);
		if ($res['code'] != '1') {
			return $res['message'].'companys'.$key;
			die;
		}
		//私钥解密
		$res = $this->PrivateDecrypt($res['output']);
		$unit_id = json_decode($res,1);
		return $unit_id;
    }

    public function addPostJobNew($company,$unit_id){
		$job_count = 0;     //职位上传条数
    	$job_sql = "SELECT * FROM hr_jobs WHERE uid = ".$company['uid']." UNION ALL SELECT * FROM hr_jobs_tmp WHERE uid = ".$company['uid'];
		$jobs = Db::query($job_sql);
		if ($jobs) {
			$index = 0;
			foreach ($jobs as $key1 => $job) {
				$job_data['clientid'] = '115';
				$job_data_param['unitid'] = $unit_id['unitid'];
				$job_data_param['jobname'] = $job['jobs_name'];
				$job_data_param['jobdescription'] = '0';
				if ($job['wage_cn'] == '1万以上/月') {
					$job_data_param['wageupper'] = '30000';
				}else{
					$wage_cn = explode('~',$job['wage_cn']);
					$job_data_param['wagelower'] = $wage_cn[0];
					$job_data_param['wageupper'] = substr($wage_cn[1], 0, -7);
				}
				//岗位类别
				$vocationaltitle = Db::table('hr_vocationaltitle_api')->where('name',$job['category_cn'])->find();
				$vocationaltitle_code = $vocationaltitle['code'];
				$vocationaltitle_val = $vocationaltitle['name'];
				if (!$vocationaltitle_code) {
					$job_data_param['vocationaltitle'] = '150299_'.'其他';
				}else{
					$job_data_param['vocationaltitle'] = $vocationaltitle_code.'_'.$vocationaltitle_val;
				}
				//工作年限
				$jobtype = config('workperiod');
				$job_data_param['workperiod'] = $jobtype[$job['experience_cn']];
				$job_data_param['probation'] = $job['syqx'];
				$job_data_param['professional'] = '0';
				//文化程度
				$education = config('education');
				$job_data_param['education'] = $education[$job['education_cn']];
				if ($job['zyzg'] == '无要求') {
					$job['zyzg'] = '0';
				}
				$job_data_param['vocationalskilln'] = $job['zyzg'];
				$job_data_param['vocationalskill'] = $job['zyzg'];
				//工作地点 区域代码
				$district_cn = explode('/',$job['district_cn']);
				if ($district_cn['1']) {
					$distric = $district_cn['1'];
				}else{
					$distric = $district_cn['0'];
				}
				$worklocation_code = Db::table('hr_worklocation_api')->where('name',$distric)->value('code');
				if (!$worklocation_code) {
					$worklocation_code = '500000000000';
				}
				$job_data_param['worklocation'] = $worklocation_code;
				//工作性质
				$jobtype = config('jobtype');
				$job_data_param['jobtype'] = $jobtype[$job['nature_cn']];
				$job_data_param['accommodation'] = '0';
				$job_data_param['worktime'] = '8';
				$job_data_param['environment'] = $job['gzhj'];
				$job_data_param['expertisetitle'] = $job['zyzg'];
				$job_data_param['psnrequiedcnt'] = $job['amount'];
				$job_data_param['enddate'] = '2021-12-31 23:59:59';
				$job_data_param_json = json_encode($job_data_param);
				$job_data_param_cdata = $this->PublicEncrypt($job_data_param_json);
				$job_data['param'] = $job_data_param_cdata;
				
				$job_data_url = "https://ggfw.rlsbj.cq.gov.cn/cqjy/n/dmzthirdinterface/webservice"."/addPostJobNew";
				$job_data_res = http_request($job_data_url, $job_data,'');
				$job_data_addret = json_decode($job_data_res,1);
				if($job_data_addret['code'] == '1') {
					$job_count++;
				}
			}
		}
		return $job_count;
    }

	//会员同步
	public function tongbu_member(){
		$maxid = 1;         //会员最大id
		$member_count = 0;  //会员上传条数
		$resume_count = 0;  //简历上传条数
		$maxcount = 100;      //最大上传条数  根据需要修改每一次上传数量
		$sync_sql = "SELECT * FROM hr_cqhrss_sync WHERE type = 'member' ORDER BY synctime DESC LIMIT 0, 1";
		$last_sync = Db::query($sync_sql);
		if ($last_sync) {
		    $max = $last_sync['maxid'];
		    if ($max > $maxid) {
		        $maxid = $max;
		    }
		}
		//查询普通会员
		$member_sql = "SELECT * FROM hr_members WHERE utype = 2 AND uid > ".$maxid." ORDER BY uid ASC LIMIT 0, ".$maxcount;
		$members = Db::query($member_sql);
		if ($members) {
		    foreach ($members as $member) {
		        try {
		            $maxid = $member['uid'];
		            //根据会员id查询简历，并获取其中的会员真实信息
		            $resume_sql = "SELECT * FROM hr_resume WHERE uid = ".$member['uid'];
		            $resumes = Db::query($resume_sql);
		            //调用接口添加会员信息
		            $addmember['clientid'] = '115';
		            $addmember_param['sfz'] = ' ';
		            $addmember_param['realname'] = $resumes['fullname'];
		            $addmember_param['email'] = $resumes['email'];
		            $addmember_param['sex'] = $resumes['sex'];
		            $addmember_param['telephone'] = $resumes['telephone'];
		            $addmember_param = json_encode($addmember_param);
					$cdata = $this->PublicEncrypt($addmember_param);
		            $addmember['param'] = $cdata;

					$url = "https://ggfw.rlsbj.cq.gov.cn/cqjy/n/dmzthirdinterface/webservice"."/addUser";
			        $res = http_request($url, $addmember,'');
			        $addret = json_decode($res,1);
			        //返回的id

		            if ($addret['code'] == '1') {
		                $member_count++;
		                //查询当前会员信息 根据ID添加简历
		                $curUser = getUserInfo($member['email']);
		                if ($curUser) {

		                    $resume_sql = "SELECT * FROM hr_resume WHERE uid = ".$member['uid'];
		                    $resumes = $db->getall($resume_sql);
		                    if ($resumes) {
		                        foreach ($resumes as $resume) {
		                            //添加简历 只有添加一个简历
		                            $resume_ret = addResume($resume, $curUser['psnid']);
		                            if($resume_ret) {
		                                $resume_count++;
		                               
		                            }
		                        }
		                    } else {
		                        //添加默认简历
		                        $resume_ret = addDefaultResume($curUser['psnid'], $member['email']);
		                        if($resume_ret) {
		                            $resume_count++;
		                           
		                        }
		                    }
		                }
		            }
		        } catch (Exception $e) {

		        }
		    }
		}
	}

	//新增招聘会场(有会场才有招聘会)
	public function add_jobfair()
	{
		# code...
	}

	//招聘会同步
	public function tongbu_jobfair(){
		$maxid = 0;
		$sync_sql = "SELECT * FROM hr_cqhrss_sync WHERE type = 'jobfair' ORDER BY synctime DESC LIMIT 0, 1";
		$last_sync = Db::query($sync_sql);
		if ($last_sync) {
		    $maxid = $last_sync['maxid'];
		}
		//招聘会数据
		$jobfairs_sql = 'SELECT * FROM hr_recruit_jobfair WHERE id > '.$maxid;
		$jobfairs = Db::query($jobfairs_sql);
		$jobfair_count = 0;

		if ($jobfairs) {
		    foreach ($jobfairs as $jobfair) {
		        	
		            //添加摊位
		            addJobFairAddr();
		            $addrid = getJobFairAddrList();
		            if ($addrid) {
		                $result = addJobFair($encode_password, $jobfair, $addrid);
		                if ($result) {
		                    $maxid = $jobfair['id'];
		                    $jobfair_count++;
		                }
		            }
		        
		    }
		}
	}


	//公钥加密
	public function PublicEncrypt($data){
        //openssl_public_encrypt($data,$encrypted,$this->pu_key);//公钥加密
        $crypto = '';
        $key = openssl_pkey_get_public(RSA_PUBLIC);
		if (!$key) {
		    return('公钥不可用');
		}
        foreach (str_split($data, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $key);
            $crypto .= $encryptData;
        } 
        return base64_encode($crypto);die;
        $encrypted = $this->urlsafe_b64encode($crypto);
        return $encrypted;
    }


    //私钥解密
    public function PrivateDecrypt($encrypted){
        $crypto = '';
        foreach (str_split($this->urlsafe_b64decode($encrypted), 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData,RSA_PRIVATE);
            $crypto .= $decryptData;
        }
        //$encrypted = $this->urlsafe_b64decode($encrypted);
        //openssl_private_decrypt($encrypted,$decrypted,$this->pi_key);
        return $crypto;
    }

	//解密码时把转换后的符号替换特殊符号
    function urlsafe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }


    public function cqybhr_test(){
    	if ($_POST) {
    		//验证统一社会信用代码
    		$check_group_res = $this->check_group($_POST['organcode']);
    		if ($check_group_res == false) {
    			return array('code'=>'2','msg'=>'统一社会信用代码有误,请核对后重新输入');
    		}
    		$update_res = Db::table('hr_company_profile')->where('uid',$_POST['uid'])->update($_POST);
    		if ($update_res == '0' || !$update_res) {
    			return array('code'=>'2','msg'=>'提交失败,请稍后再试,如遇多次提交错误,请联系023-67587133');
    		}
    		return array('code'=>'1');
    	}
    	$this->assign('uid',input('uid'));
        return $this->fetch();
    }



    public function check_group($str)
    {
        $one = '159Y';//第一位可以出现的字符
        $two = '12391';//第二位可以出现的字符
        $str = strtoupper($str);
        if (!strstr($one, $str['1']) && !strstr($two, $str['2']) && !empty($array[substr($str, 2, 6)])) {
            return false;  //有误
        }
        $wi = array(1, 3, 9, 27, 19, 26, 16, 17, 20, 29, 25, 13, 8, 24, 10, 30, 28);//加权因子数值
        $str_organization = substr($str, 0, 17);
        $num = 0;
        for ($i = 0; $i < 17; $i++) {
            $num += $this->transformation($str_organization[$i]) * $wi[$i];
        }
        switch ($num % 31) {
            case '0':
                $result = 0;
                break;
            default:
                $result = 31 - $num % 31;
                break;
        }
        if (substr($str, -1, 1) == $this->transformation($result, true)) {
            return true;
        } else {
            return false;  //有误
        }
    }

    public function transformation($num, $status = false)
    {
        $list = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17, 'J' => 18, 'K' => 19, 'L' => 20, 'M' => 21, 'N' => 22, 'P' => 23, 'Q' => 24, 'R' => 25, 'T' => 26, 'U' => 27, 'W' => 28, 'X' => 29, 'Y' => 30);//值转换
        if ($status == true) {
            $list = array_flip($list);
        }
        return $list[$num];
    }




	
 

    
}
