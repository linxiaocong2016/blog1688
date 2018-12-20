<?php
namespace app\admin\controller;

use think\Db;
use think\Controller;
use think\Request;
use think\Config;

class Article extends Controller
{
    public $status = [0 => '未发布', 1 => '已发布']; 


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

            $count = Db::table('article a')->where($where)->count();
            $pageCount = ceil($count / $pageSize);
            $offset = (($page - 1) * $pageSize);
            $list = Db::table('article')
                    ->alias('a')
                    ->join('article_cate ac', 'ac.id = a.cat_id', 'LEFT')
                    ->where($where)->limit($offset,$pageSize)
                    ->field('a.*,ac.name')
                    ->order('a.id', 'desc')->select();
//echo '<pre />';print_r($list);exit;
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

        return view('index',['status' => $this->status]);
    }
    
    public function add()
    {
        if(Request::instance()->isPost()){
            $title = Request::instance()->post('articletitle');
            $content = Request::instance()->post('content');
            $cat_id = Request::instance()->post('articlecatid');
            $keyword = Request::instance()->post('keywords');
            $sort = Request::instance()->post('articlesort');
            $sources = Request::instance()->post('sources');
            $author = Request::instance()->post('author');
            $desc = Request::instance()->post('abstract');
            $image = Request::instance()->post('image');
            
            $data = [
                'title' => $title, 
                'content' => $content,
                'cat_id' => $cat_id,
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
        
        $articleCate = Db::table('article_cate')->select();
        $articleCate = $this->getTree($articleCate);
        return view('add',['articleCate' => $articleCate]);
    }
    
    public function edit()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');
            $title = Request::instance()->post('articletitle');
            $content = Request::instance()->post('content');
            $cat_id = Request::instance()->post('articlecatid');
            $keyword = Request::instance()->post('keywords');
            $sort = Request::instance()->post('articlesort');
            $sources = Request::instance()->post('sources');
            $author = Request::instance()->post('author');
            $desc = Request::instance()->post('abstract');
            $image = Request::instance()->post('image');
            
            $data = [
                'title' => $title, 
                'content' => $content,
                'cat_id' => $cat_id,
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
        $articleCate = Db::table('article_cate')->select();
        $articleCate = $this->getTree($articleCate);
        return view('edit',['articleInfo' => $articleInfo, 'articleCate' => $articleCate, 'domain' => Config::get('oss.domain')]);
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
    
    function getTree($array, $pid =0, $level = 0){
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        static $list = [];
        foreach ($array as $key => $value){
            if ($value['pid'] == $pid){
                $value['level'] = $level;
                $list[] = $value;
                unset($array[$key]);
                $this->getTree($array, $value['id'], $level+1);
            }
        }
        return $list;
    }
}
