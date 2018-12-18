<?php
namespace app\admin\controller;

use think\Db;
use think\Controller;
use think\Request;
use think\Session;

class Login extends Controller
{
    public function index()
    {
        if(Request::instance()->isPost()){
            $username = Request::instance()->post('username');
            $password = Request::instance()->post('password');
            $result = Db::table('admin')->where(['username' => $username,'password' => md5($password)])->find();
            if($result){
                Session::set('admin',$result);
                $this->success('请求成功');
            } else {
                $this->error('用户名或密码不正确');
            }
        }
        
        if(Session::get('admin')){
            $this->redirect('/admin/index/index');
        }
        
        return view('index');
    }
    
    public function welcome()
    {
        return view('welcome');
    }
}
