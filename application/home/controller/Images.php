<?php
namespace app\home\controller;

use think\Db;
use think\Controller;
use think\Request;
use think\Config;
use think\Session;
use think\image;

class Images extends Controller
{
    public function index()
    {
        if(Request::instance()->isPost()){
            $page = Request::instance()->post('page',1);
            $pageSize = Request::instance()->post('pageSize',6);
            $type = Request::instance()->post('type');
            
            $where = [];
            $order = ['i.id' => 'desc'];
            if($type == 'recommend'){
                $where['i.is_recommend'] = 1;
            }
            if($type == 'ranking'){
                $order = ['clicks' => 'desc'];
            }
            
            $count = Db::table('image')->alias('i')->where($where)->count();
            $pageCount = ceil($count / $pageSize);
            $offset = (($page - 1) * $pageSize);
            $list = Db::table('image')->alias('i')
            ->join('image_cate ic', 'ic.id = i.cat_id', 'LEFT')
            ->field('i.id,i.title,i.desc,i.image,i.addtime,ic.name')
            ->where($where)->limit($offset,$pageSize)
            ->order($order)->select();
            if($list){
                foreach ($list as $key => $val){
                    $list[$key]['addtime'] = date('Y-m-d',$val['addtime']);
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
        
        $imageInfo = Db::table('image')->where(['id'=>$id])->find();
        
        //获取上一篇图片
        $prevImageInfo = Db::table('image')->field('id,title')->where(['id'=>['<',$imageInfo['id']]])->order(['id' => 'desc'])->find();
        $imageInfo['prevImageInfo'] = $prevImageInfo;
        //获取下一篇图片
        $nextImageInfo = Db::table('image')->field('id,title')->where(['id'=>['>',$imageInfo['id']]])->order(['id' => 'asc'])->find();
        $imageInfo['nextImageInfo'] = $nextImageInfo;
        
        $imageInfo['addtime'] = date('Y-m-d', $imageInfo['addtime']);
        $imageInfo['keyword'] = $imageInfo['keyword'] ? explode(',', $imageInfo['keyword']) : '';
        $imageInfo['image_detail'] = json_decode($imageInfo['image_detail'],TRUE);
//echo '<pre />';print_r($imageInfo);exit;
        return view('detail',['imageInfo' => $imageInfo, 'demoin' => Config::get('oss.domain')]);
    }
}
