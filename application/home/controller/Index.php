<?php
namespace app\home\controller;

use think\Db;
use think\Controller;
use think\Request;
use think\Config;
use think\Session;

class Index extends Controller
{
    public function index()
    {
        if(Request::instance()->isPost()){
            $page = Request::instance()->post('page',1);
            $pageSize = Request::instance()->post('pageSize',6);
            $type = Request::instance()->post('type');
            
            $where = ['cat_id' => 1];
            $order = ['a.id' => 'desc'];
            if($type == 'recommend'){
                $where['a.is_recommend'] = 1;
            }
            if($type == 'ranking'){
                $order = ['clicks' => 'desc'];
            }
            
            $count = Db::table('article')->alias('a')->where($where)->count();
            $pageCount = ceil($count / $pageSize);
            $offset = (($page - 1) * $pageSize);
            $list = Db::table('article')->alias('a')
            ->join('article_cate ac', 'ac.id = a.cat_id', 'LEFT')
            ->field('a.id,a.title,a.content,a.desc,a.image,a.like_number,a.addtime,a.true_clicks + a.false_clicks clicks,ac.name')
            ->where($where)->limit($offset,$pageSize)
            ->order($order)->select();
            if($list){
                foreach ($list as $key => $val){
                    $list[$key]['addtime'] = date('Y-m-d',$val['addtime']);
                    $list[$key]['desc'] = strip_tags($val['content']);
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
        $ip = Request::instance()->ip();
        
        $articleInfo = Db::table('article')->field('id,title,content,keyword,author,desc,image,addtime,true_clicks + false_clicks clicks')->where(['id'=>$id])->find();

        //获取上一篇文章
        $prevArticleInfo = Db::table('article')->field('id,title')->where(['id'=>['<',$articleInfo['id']]])->order(['id' => 'desc'])->find();
        $articleInfo['prevArticleInfo'] = $prevArticleInfo;
        //获取下一篇文章
        $nextArticleInfo = Db::table('article')->field('id,title')->where(['id'=>['>',$articleInfo['id']]])->order(['id' => 'asc'])->find();
        $articleInfo['nextArticleInfo'] = $nextArticleInfo;
        //获取相关文章
        $relatedArticleList = Db::table('article')->field('id,title')->where(['keyword' => ['like', "%{$articleInfo['keyword']}%"],'id' => ['<>', $articleInfo['id']]])->order(['id' => 'desc'])->limit(6)->select();
        $articleInfo['relatedArticleList'] = $relatedArticleList;
        
        $articleInfo['addtime'] = date('Y-m-d', $articleInfo['addtime']);
        $articleInfo['keyword'] = $articleInfo['keyword'] ? explode(',', $articleInfo['keyword']) : '';
        
//        print_r(Session::get("ip$id"));exit;
        //更新点击数
        if(Session::get("ip$id") != $ip){
            Session::set("ip$id",$ip);
            Db::table('article')->where('id', $id)->setInc('true_clicks', 1);
        }

        return view('detail',['articleInfo' => $articleInfo]);
    }
}
