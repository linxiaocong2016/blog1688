<?php
namespace app\home\controller;

use think\Db;
use think\Controller;
use think\Request;
use think\Config;

class Index extends Controller
{
    public function index()
    {
        if(Request::instance()->isPost()){
            $page = Request::instance()->post('page',1);
            $pageSize = Request::instance()->post('pageSize',6);
            $type = Request::instance()->post('type');
            
            $where = [];
            $order = ['id' => 'desc'];
            if($type == 'recommend'){
                $where['is_recommend'] = 1;
            }
            if($type == 'ranking'){
                $order = ['clicks' => 'desc'];
            }
            
            $count = Db::table('article')->where($where)->count();
            $pageCount = ceil($count / $pageSize);
            $offset = (($page - 1) * $pageSize);
            $list = Db::table('article')->field('id,title,content,desc,image,addtime,true_clicks + false_clicks clicks')->where($where)->limit($offset,$pageSize)->order($order)->select();
            if($list){
                foreach ($list as $key => $val){
                    $list[$key]['addtime'] = date('Y-m-d',$val['addtime']);
                    $list[$key]['desc'] = mb_substr(strip_tags($val['content']),0,40,'utf-8');
                    $list[$key]['new_desc'] = mb_substr(strip_tags($val['content']),0,160,'utf-8');
                    $list[$key]['ranking_desc'] = mb_substr(strip_tags($val['content']),0,45,'utf-8');
                }
            }
            
            $data['list'] = $list;
            $data['count'] = $count;
            $data['pageCount'] = $pageCount;
            $data['pageSize'] = $pageSize;
            $data['page'] = $page;
            $data['demoin'] = Config::get('oss.domain');
            $this->success('请求成功',null,$data);
        }

        return view('index');
    }
    
    public function detail()
    {
        $id = Request::instance()->get('id');
        
        $articleInfo = Db::table('article')->field('id,title,content,keyword,author,desc,image,addtime,true_clicks + false_clicks clicks')->where(['id'=>$id])->find();
        $articleInfo['addtime'] = date('Y-m-d', $articleInfo['addtime']);
        $articleInfo['keyword'] = $articleInfo['keyword'] ? explode(',', $articleInfo['keyword']) : '';
//        print_r($articleInfo);exit;
        
        return view('detail',['articleInfo' => $articleInfo]);
    }
}
