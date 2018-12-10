<?php
namespace app\admin\controller;

use think\Db;
use think\Controller;

class Article extends Controller
{
    public function index()
    {
//        $result = Db::table('article')->where('id',2)->find();
//        print_r($result);exit;
        return view('index');

    }
    
    public function add()
    {
        return view('add');
    }
}
