<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use AliOSS\AliOSS;
use think\Config;

class Upload extends Controller
{
    /**
     * 成功返回示例
     * {"code":0,"path":"/yii2test/happy/1803091801545914.jpg","url":"http://testcloud.7724.com/yii2test/happy/1803091801545914.jpg"}
     */
    public function image()
    {        
        $filepath = Request::instance()->post('filepath');
        $base64img = Request::instance()->post('base64img');
        $data = AliOSS::uploadBase64img($filepath, $base64img);
        $this->json($data);
    }

    /**
     * ueditor文件上传
     */
    public function actionUeditorUploadFile()
    {
        $object = ltrim(Request::instance()->post('object'), '/');
        $filePath = Request::instance()->post('filePath');

        if ($res = AliOSS::uploadFile($object, $filePath)) {
            $this->json(['code' => 0, 'path' => "/{$object}", 'url' => Config::get('oss.domain') . "/{$object}"]);
        } else {
            $this->json(['code' => 1, 'msg' => 'fail']);
        }
    }

    /**
     * ueditor二进制方式上传文件
     */
    public function actionUeditorPutObject()
    {
        $object = ltrim(Request::instance()->post('object'), '/');
        $content = Request::instance()->post('content');

        if ($res = AliOSS::putObject($object, $content)) {
            $this->json(['code' => 0, 'path' => "/{$object}", 'url' => Config::get('oss.domain') . "/{$object}"]);
        } else {
            $this->json(['code' => 1, 'msg' => 'fail']);
        }
    }
    
    public function json($arr, $output = true)
    {
        $content = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!$output) {
            return $content;
        }
        header('Content-Type: application/json; charset=utf-8');
        exit($content);
    }
}
