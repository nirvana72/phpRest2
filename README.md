# 介绍 Introduction

PhpRest2 是一款纯restful的轻量框架, 基于php8及以上版本

~~~
<?php
namespace App\Controller;

use PhpRest2\Controller\Attribute\{Controller, Action};

#[Controller('/index')]
class IndexController
{
    #[Action('GET:/index')]
    public function index($p1) 
    {
        return "p1 = {$p1}";
    }
}

~~~

# 环境 Requirements
 - PHP >= 8.0

# 安装 Installation
~~~
composer require nirvana72/phpRest2
~~~

### nginx 配置
~~~
server {
    ...

    location / {
        try_files $uri /index.php$is_args$args;
    }

    ...
}
~~~

### apache 配置
>开启 mod_rewrite 模块，入口目录(/public) 下添加 .htaccess 文件：
~~~
Options +FollowSymLinks
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
~~~

# 文档 Document
[参数绑定](https://github.com/nirvana72/phpRest2-example/blob/main/App/Controller/ParamsController.php)

[参数绑定实体类](https://github.com/nirvana72/phpRest2-example/blob/main/App/Controller/EntityController.php)

[中间件hook](https://github.com/nirvana72/phpRest2-example/blob/main/App/Controller/HookController.php)

[数据库操作](https://github.com/nirvana72/phpRest2-example/blob/main/App/Controller/DbController.php)

[ORM](https://github.com/nirvana72/phpRest2-example/blob/main/App/Controller/OrmController.php)

[文件上传](https://github.com/nirvana72/phpRest2-example/blob/main/App/Controller/FileUploadController.php)

[事件驱动](https://github.com/nirvana72/phpRest2-example/blob/main/App/Controller/EventController.php)

[路由信息](https://github.com/nirvana72/phpRest2-example/blob/main/App/Controller/IndexController.php)

# 其它 Other
需要 apcu 扩展
