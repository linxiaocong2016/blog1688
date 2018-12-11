<?php
namespace app\admin\controller;

use think\Db;
use think\Controller;
use think\Request;

class Article extends Controller
{
    public $status = [0 => '未发布', 1 => '已发布']; 


    public function index()
    {
        if(Request::instance()->isPost()){
            $page = Request::instance()->post('page',1);
            $pageSize = Request::instance()->post('pageSize',20);

            $count = Db::table('article')->count();
            $pageCount = ceil($count / $pageSize);
            $offset = (($page - 1) * $pageSize);
            $list = Db::table('article')->select();
            
            if($list){
                foreach ($list as $key => $val){
                    $list[$key]['status'] = $this->status[$val['status']];
                    $list[$key]['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
                }
            }
            
            $data['list'] = $list;
            $data['count'] = $count;
            $data['pageCount'] = $pageCount;
            $data['pageSize'] = $pageSize;
            $data['page'] = $page;
            $this->success('请求成功',null,$data);
        }

        return view('index');
    }
    
    public function add()
    {
        if(Request::instance()->isPost()){
            $title = Request::instance()->post('articletitle');
            $content = Request::instance()->post('content');
            $column = Request::instance()->post('articlecolumn');
            $type = Request::instance()->post('articletype');
            $keyword = Request::instance()->post('keywords');
            $sort = Request::instance()->post('articlesort');
            $sources = Request::instance()->post('sources');
            $author = Request::instance()->post('author');
            $desc = Request::instance()->post('abstract');
            
            $data = [
                'title' => $title, 
                'content' => $content,
                'column' => $column,
                'type' => $type,
                'keyword' => $keyword,
                'sort' => $sort,
                'source' => $sources,
                'author' => $author,
                'desc' => $desc,
                'addtime' => time()
            ];
            $result = Db::table('article')->insert($data);
            if($result){
                $this->success('新增成功');
            } else {
                $this->error('新增失败');
            }
        }
        return view('add');
    }
    
    public function edit()
    {

    }
}
