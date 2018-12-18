<?php
namespace app\admin\controller;

use think\Db;
use think\Controller;
use think\Request;
use think\Config;

class Article extends Controller
{
    public $status = [0 => '未发布', 1 => '已发布']; 
    
    public $column = [0 => '所有栏目显示',1 => '新闻资讯',2 => '├行业动态'];
    
    public $type = [0 => '全部类型', 1 => '个人博客文章', 2 => '帮助说明', 3 => '新闻资讯'];


    public function index()
    {
        if(Request::instance()->isPost()){
            $page = Request::instance()->post('page',1);
            $pageSize = Request::instance()->post('pageSize',20);
            $status = Request::instance()->post('status');
            $start_time = Request::instance()->post('start_time');
            $end_time = Request::instance()->post('end_time');
            $title = Request::instance()->post('title');
            
            $where = [];
            if(isset($status) && $status != ''){
                $where['status'] = $status;
            }
            if($start_time){
                $start_time = strtotime($start_time . ' 00:00:00');
                $where['addtime'][] = ['>=',$start_time];
            }
            if($end_time){
                $end_time = strtotime($end_time . ' 00:00:00');
                $where['addtime'][] = ['<=',$end_time];
            }
            if($title){
                $where['title'] = $title;
            }

            $count = Db::table('article')->where($where)->count();
            $pageCount = ceil($count / $pageSize);
            $offset = (($page - 1) * $pageSize);
            $list = Db::table('article')->where($where)->limit($offset,$pageSize)->order('id', 'desc')->select();

            if($list){
                foreach ($list as $key => $val){
                    $list[$key]['status'] = $this->status[$val['status']];
                    $list[$key]['column'] = $this->column[$val['column']];
                    $list[$key]['type'] = $this->type[$val['type']];
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

        return view('index',['status' => $this->status]);
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
            $image = Request::instance()->post('image');
            
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
                'image' => $image,
                'addtime' => time()
            ];
            $result = Db::table('article')->insert($data);
            if($result){
                $this->success('新增成功');
            } else {
                $this->error('新增失败');
            }
        }
        return view('add',['column' => $this->column, 'type' => $this->type]);
    }
    
    public function edit()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');
            $title = Request::instance()->post('articletitle');
            $content = Request::instance()->post('content');
            $column = Request::instance()->post('articlecolumn');
            $type = Request::instance()->post('articletype');
            $keyword = Request::instance()->post('keywords');
            $sort = Request::instance()->post('articlesort');
            $sources = Request::instance()->post('sources');
            $author = Request::instance()->post('author');
            $desc = Request::instance()->post('abstract');
            $image = Request::instance()->post('image');
            
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
                'image' => $image,
            ];

            $result = Db::table('article')->where('id', $id)->update($data);
            if($result){
                $this->success('更新成功');
            } else {
                $this->error('更新失败');
            }
        }
        
        $id = Request::instance()->get('id');
        $articleInfo = Db::table('article')->where('id',$id)->find();
        return view('edit',['articleInfo' => $articleInfo, 'column' => $this->column, 'type' => $this->type, 'domain' => Config::get('oss.domain')]);
    }
    
    public function del()
    {
        if(Request::instance()->isPost()){
            $ids = Request::instance()->post('ids',0);
            $ids = ltrim($ids,',');
            
            if(empty($ids))$this->error ('参数不完整');
            
            $result = Db::table('article')->whereIn('id', $ids)->delete();
            if($result){
                $this->success('删除成功！');
            } else {
                $this->error('删除失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
