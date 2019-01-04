<?php
namespace app\home\controller;

use think\Db;
use think\Controller;
use think\Request;
use think\Config;

class About extends Controller
{
    public function index()
    {
        return view('index',['check' => 4]);
    }
}
