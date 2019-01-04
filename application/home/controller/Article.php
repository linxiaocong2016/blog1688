<?php
namespace app\home\controller;

use think\Db;
use think\Controller;
use think\Request;
use think\Config;
use think\Session;

class Article extends Controller
{
    public $check = 6;


    public function index()
    {
        if(Request::instance()->isPost()){
            $page = Request::instance()->post('page',1);
            $pageSize = Request::instance()->post('pageSize',6);
            $type = Request::instance()->post('type');
            $cat_id = Request::instance()->post('cat_id');
            
            $where = ['ac.id' => 1];
            $whereOr = ['ac.pid' => 1];
            $order = ['a.id' => 'desc'];
            if($type == 'recommend'){
                $where['a.is_recommend'] = 1;
            }
            if($type == 'ranking'){
                $order = ['clicks' => 'desc'];
            }
            if($cat_id){
                $where['a.cat_id'] = $cat_id;
            }
            
            $count = Db::table('article')->alias('a')
            ->join('article_cate ac', 'ac.id = a.cat_id', 'LEFT')
            ->where($where)->whereOr($whereOr)->count();
            
            $pageCount = ceil($count / $pageSize);
            $offset = (($page - 1) * $pageSize);
            
            $list = Db::table('article')->alias('a')
            ->join('article_cate ac', 'ac.id = a.cat_id', 'LEFT')
            ->field('a.id,a.title,a.content,a.desc,a.image,a.like_number,a.addtime,a.true_clicks + a.false_clicks clicks,ac.name')
            ->where($where)->limit($offset,$pageSize)
            ->whereOr($whereOr)
            ->order($order)->select();
            
            if($list){
                foreach ($list as $key => $val){
                    $list[$key]['comment_total'] = Db::table('article_comment')->where(['article_id' => $val['id']])->count();
                    $list[$key]['addtime'] = date('Y-m-d',$val['addtime']);
                    $list[$key]['desc'] = mb_substr(strip_tags($val['content']),0,300,'utf-8');
                    unset($list[$key]['content']);
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
        
        $articleCate = Db::table('article_cate')->where(['pid' => 1, 'status' => 1])->select();
        return view('index',['check' => $this->check, 'articleCate' => $articleCate]);
    }
    
    public function detail()
    {
        $id = Request::instance()->get('id');
        $ip = Request::instance()->ip();
        
        $articleInfo = Db::table('article')->field('id,title,content,keyword,like_number,author,desc,image,addtime,true_clicks + false_clicks clicks')->where(['id'=>$id])->find();

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
        
        //获取评论内容
        $commentList = Db::table('article_comment')->where(["article_id" => $id])->limit(0,20)->order(['id' => 'desc'])->select();
        if($commentList){
            foreach ($commentList as $key => $val){
                $commentList[$key]['addtime'] = date('Y-m-d',$val['addtime']);
            }
        }

        return view('detail',['articleInfo' => $articleInfo,'commentList' => $commentList ,'check' => $this->check]);
    }
    
    public function comment()
    {
        if(Request::instance()->isPost()){
            $article_id = Request::instance()->post('article_id',0);
            $captcha = Request::instance()->post('captcha');
            $content = Request::instance()->post('content');
            $ip = Request::instance()->ip();
            $ip = ip2long($ip);
            
            if(!captcha_check($captcha))$this->error ('验证码不正确，请重新输入');

            //判断一天只能评论10条
            $date = date('Y-m-d',time());
            $time = strtotime($date.' 00:00:00');
            $commentLimit = Db::table('article_comment')->where(['article_id' => $article_id, "ip" => $ip, 'addtime' => ['>=',$time]])->count();
            if($commentLimit >= 5)$this->error ('同一篇文章一天只能评论5次哦');

            $data = [
                'article_id' => $article_id, 
                'content' => $content,
                'ip' => $ip,
                'addtime' => time()
            ];
            $result = Db::table('article_comment')->insert($data);
            if($result){
                //获取最后插入的ID
//                $lastId = Db::table('article_comment')->getLastInsID();
                $this->success('评论成功');
            } else {
                $this->error('评论失败');
            }
        }
    }
}
