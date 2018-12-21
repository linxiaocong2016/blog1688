<?php
//配置文件
return [
    'template'               => [
            'layout_on'     =>  true,
            'layout_name'   =>  'layout/layout',
        ],
    'oss' => [
        'accessId' => 'LTAIND66s1Od0JAZ',
        'accessSecret' => '7TsnKr9BVK7yysNpoj75DnW7prJdZZ',
        'endpoint' => 'oss-cn-hongkong.aliyuncs.com',
        'bucket' => 'blog1688',
        'domain' => 'http://image.blog1688.com',
    ],
    'show_error_msg' => true,//显示错误信息
    'http_exception_template'    =>  [
    // 定义404错误的重定向页面地址
    404 =>  APP_PATH.'404.html',
    // 还可以定义其它的HTTP status
    401 =>  APP_PATH.'401.html',
]

];