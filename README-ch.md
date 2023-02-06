<!-- vscode-markdown-toc -->
* 1. [介绍](#)
* 2. [快速启动](#-1)
	* 2.1. [创建app](#app)
	* 2.2. [配置app](#app-1)
	* 2.3. [修改sdk配置](#sdk)
	* 2.4. [启动程序](#-1)
		* 2.4.1. [docker-compose](#docker-compose)
		* 2.4.2. [Laravel 脚本启动](#Laravel)
	* 2.5. [验证](#-1)
* 3. [目录结构](#-1)
* 4. [关于Demo](#Demo)
	* 4.1. [关于demo的中间件](#demo)
		* 4.1.1. [中间件的实现](#-1)
		* 4.1.2. [ 中间件的注册](#-1)
	* 4.2. [关于测试程序](#-1)
* 5. [关于功能函数的使用](#-1)
	* 5.1. [GetAccessToken](#GetAccessToken)
	* 5.2. [Exchange](#Exchange)
	* 5.3. [RefreshToken](#RefreshToken)
	* 5.4. [AuthCodeUrl](#AuthCodeUrl)
	* 5.5. [Oauth2Middleware](#Oauth2Middleware)

<!-- vscode-markdown-toc-config
	numbering=true
	autoSave=true
	/vscode-markdown-toc-config -->
<!-- /vscode-markdown-toc -->

<!-- [toc] -->
[English version](./README.md)
##  1. <a name=''></a>介绍

本项目是为了shoplazza的开发者可以不需要理解过多的Oauth2流程完成认证操作的一款php语言开发的sdk。

关于shoplazza认证流程请阅读文档 [标准的OAuth流程](https://helpcenter.shoplazza.com/hc/zh-cn/articles/4408686586137#h_01FM4XX2CX746V3277HB7SPGTN)


##  2. <a name='-1'></a>快速启动
###  2.1. <a name='app'></a>创建app
关于创建app 请阅读文档 [构建公用App](https://helpcenter.shoplazza.com/hc/zh-cn/articles/4409360434201)

###  2.2. <a name='app-1'></a>配置app
关于配置app 请阅读文档 [管理你的App](https://helpcenter.shoplazza.com/hc/zh-cn/articles/4409476265241)

###  2.3. <a name='sdk'></a>修改sdk配置

在《构建公用App》与《管理你的App》两个文章当中提到了几个参数分别是：    
1、Client ID用于验证身份。
2、Client Secret用于验证身份。
3、App URL	你的App主服务地址。
4、Redirect URL 你的App重定向地址，通常是由店铺重定向到你的App时的地址。

上面的四个参数需要配置到demo的配置文件当中
[配置文件](./example-app/config/oauth.php)

``` injectablephp
<?php
return[
    //ID用于验证身份。
    'clientID' => "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    //ID用于验证身份。
    'clientSecret'=>"Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
    //你的App重定向地址，通常是由店铺重定向到你的App时的地址。
    'redirectURL'=>"https://17d3-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
    'Scopes' => [
        "read_product", "write_product","read_shop","write_shop"
    ],
    'Endpoint'=>[
        "AuthURL"=>"/admin/oauth/authorize",
        "TokenURL"=>"/admin/oauth/token",
    ],
    "doMain" => "myshoplaza.com",
    //App URL 的请求路径
    "requestPath"=>"oauth_sdk/app_uri",
    //redirectURL 请求路径
    "callbackPath"=>"oauth_sdk/redirect_uri",
    //是否重构
    "funcRewrite" => true,

];
```


###  2.4. <a name='-1'></a>启动程序
在demo的程序当中有两种启动方式，实际上都是依靠了docker-compose，如果在本地没有对应镜像的情况下，就需要进行镜像的拉取。

docker-compose默认的镜像源是国际源，如果在拉取的时候网络连接缓慢或者异常，建议更换国内源进行拉取。更换方式请自行搜索。
> 由于Laravel/sail中DockerCompose所使用的Ubuntu支持版本为TLS，请在切还镜像源后保证Ubuntu支持版本对应。

####  2.4.1. <a name='docker-compose'></a>docker-compose
[docker-compose.yml](./example-app/docker-compose.yml)
如果熟悉docker，可以直接配置该文件
```
# For more information: https://laravel.com/docs/sail
version: '3'
services:
    laravel.test:
        build:
            # Dockerfile 中依托镜像为 Ubuntu:[version] TLS
            # 采用加速器拉取到的 Ubuntu 镜像可能并非 TLS 版本
            context: ./vendor/laravel/sail/runtimes/8.1
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP}'
        image: sail-8.1/app
        extra_hosts:
            - 'host.docker.internal:host-gateway'
        ports:
            # 应用端口 宿主机:容器内
            - '${APP_PORT:-80}:80'
        environment:
            WWWUSER: '${WWWUSER}'
            LARAVEL_SAIL: 1
            XDEBUG_MODE: '${SAIL_XDEBUG_MODE:-off}'
            XDEBUG_CONFIG: '${SAIL_XDEBUG_CONFIG:-client_host=host.docker.internal}'
        volumes:
            - '.:/var/www/html'
        networks:
            - sail
        depends_on:
            - mysql
            - redis
            - meilisearch
            - selenium
    mysql:
        image: 'mysql/mysql-server:8.0'
        ports:
        #   数据库端口  宿主机:容器内 
            - '${FORWARD_DB_PORT:-3307}:3306'
        environment:
            MYSQL_ROOT_PASSWORD: '${DB_PASSWORD}'
            MYSQL_ROOT_HOST: "%"
            MYSQL_DATABASE: '${DB_DATABASE}'
            MYSQL_USER: '${DB_USERNAME}'
            MYSQL_PASSWORD: '${DB_PASSWORD}'
            MYSQL_ALLOW_EMPTY_PASSWORD: 1
        volumes:
            - 'sailmysql:/var/lib/mysql'
        networks:
            - sail
        healthcheck:
            test: ["CMD", "mysqladmin", "ping", "-p${DB_PASSWORD}"]
            retries: 3
            timeout: 5s
    redis:
        image: 'redis:alpine'
        ports:
        #   redis端口  宿主机:容器内 
            - '${FORWARD_REDIS_PORT:-6379}:6379'
        volumes:
            - 'sailredis:/data'
        networks:
            - sail
        healthcheck:
            test: ["CMD", "redis-cli", "ping"]
            retries: 3
            timeout: 5s
    meilisearch:
        image: 'getmeili/meilisearch:latest'
        platform: linux/x86_64
        ports:
            - '${FORWARD_MEILISEARCH_PORT:-7700}:7700'
        volumes:
            - 'sailmeilisearch:/data.ms'
        networks:
            - sail
        healthcheck:
            test: ["CMD", "wget", "--no-verbose", "--spider",  "http://localhost:7700/health"]
            retries: 3
            timeout: 5s
    mailhog:
        image: 'mailhog/mailhog:latest'
        ports:
            - '${FORWARD_MAILHOG_PORT:-1025}:1025'
            - '${FORWARD_MAILHOG_DASHBOARD_PORT:-8025}:8025'
        networks:
            - sail
    selenium:
        image: 'selenium/standalone-chrome'
        volumes:
            - '/dev/shm:/dev/shm'
        networks:
            - sail
networks:
    sail:
        driver: bridge
volumes:
    sailmysql:
        driver: local
    sailredis:
        driver: local
    sailmeilisearch:
        driver: local

```
docker-compose启动命令
[docker-compose官方文档](https://docs.docker.com/compose/reference/)

```
#进入demo项目
cd example-app  

#前台启动
docker-compose up

#后台启动
docker-compose up -d


#检查启动状态
docker-compose ps

#查看日志
docker-compose logs -f


#关闭
docker-compose down


```

####  2.4.2. <a name='Laravel'></a>Laravel 脚本启动

[脚本文件](./example-app/vendor/bin/sail)

```
··· 
#31行起的配置
# Define environment variables...
#app的端口
export APP_PORT=${APP_PORT:-80}
#app的服务地址
export APP_SERVICE=${APP_SERVICE:-"laravel.test"}
#数据库的端口
export DB_PORT=${DB_PORT:-3306}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

export SAIL_SHARE_DASHBOARD=${SAIL_SHARE_DASHBOARD:-4040}
export SAIL_SHARE_SERVER_HOST=${SAIL_SHARE_SERVER_HOST:-"laravel-sail.site"}
export SAIL_SHARE_SERVER_PORT=${SAIL_SHARE_SERVER_PORT:-8080}
export SAIL_SHARE_SUBDOMAIN=${SAIL_SHARE_SUBDOMAIN:-""}
···

```

脚本启动命令
[sail介绍文档](https://learnku.com/docs/laravel/8.x/sail/9789#installing-sail-into-existing-applications)
```
#进入demo项目
cd example-app  

#配置环境并运行项目
./vendor/bin/sail up
#配置环境并运行项目 后台
./vendor/bin/sail up -d
```



###  2.5. <a name='-1'></a>验证
此时应该已经完成了demo的启动，那么接下来进行验证
1、确定程序demo程序正常运行
```
#浏览器当中输入(默认80端口)
127.0.0.1/hello
#页面会返回
hello worldpanda
```

2、确定安装流程顺畅
这个步骤请先阅读 [测试公共App](https://helpcenter.shoplazza.com/hc/zh-cn/articles/4409360434201#h_01FM7BPX2QBPB9ZWQZM80GTH4C)

按照流程：

前往 [合作伙伴中心](https://partners.shoplazza.com/)->App->App列表->管理App->测试App 入口，选择该店铺安装App，即可跳转至该店铺的授权安装页面.

正常授权结果后页面会返回
```
{
    "code":200,
    "message":"save access-token success"
}
```

3、验证token

```
#浏览器当中输入(默认80端口)
https域名/openapi_test
#页面会返回店铺默认信息
```




##  3. <a name='-1'></a>目录结构
```shell
.
├── Readme.md
├── example-app
│   ├── README.md
│   ├── app
│   │   └── Http
│   │        └── Middleware
│   │               └── OauthDemo.php   //认证中间件的demo
│   ├── artisan
│   ├── bootstrap
│   ├── composer.json
│   ├── composer.lock
│   ├── config
│   │   └── oauth.php       //Oauth中间件的配置文件
│   ├── database
│   ├── docker-compose.yml  //Docker-compose配置文件
│   ├── lib                 //库文件
│   ├── package.json    
│   ├── phpunit.xml
│   ├── public
│   ├── resources
│   ├── routes              //路由文件 
│   │   └── web.php         //测试程序所在的文件
│   ├── server.php
│   ├── storage
│   ├── tests
│   ├── vendor
│   └── webpack.mix.js
└── lib
    ├── CurlRequest.php           //Curl发送一个封装请求包的请求
    ├── CurlResponse.php          //Curl通过封装返回的包来响应请求
    ├── Exception                 
    ├── HttpRequestJson.php       // HTTP请求包
    ├── Oauth2.php                // Oauth2 封装
    └── Oauth2Middleware.php      // Oauth2的功能中间件
                
```




##  4. <a name='Demo'></a>关于Demo

Demo的主要目的是为了为开发者提供一些使用上的便捷，以及方便理解使用方法。

Demo是项目目录下example-app是一个[Laravel](https://laravel.com/) 框架实现的phpwebDemo。

Demo已经设置了全局的 middleware 会默认拦截 `/auth/shoplazza` 以及 `/auth/shoplazza/callback` 两个 URL 的请求:
- `/auth/shoplazza?shop=xx.myshoplaza.com` : 请求此 URL 时，会重定向到 https://xx.myshoplaza.com/admin/oauth/authorize 去发起授权流程
- `/auth/shoplazza/callback` : 拦截授权回调请求，自动将回调请求中的 code 替换 token

###  4.1. <a name='demo'></a>关于demo的中间件


####  4.1.1. <a name='-1'></a>中间件的实现

[中间件代码](./example-app/app/Http/Middleware/OauthDemo.php)

```injectablephp
<?php

namespace App\Http\Middleware;

//include '../../../lib/Oauth2Middleware.php';
include '../lib/Oauth2Middleware.php';
use Illuminate\Http\Request;
use Closure;
use PHPShoplazza\Oauth2Middleware as OM;



class OauthDemo
{
    /** 判断调用相关方法
     *
     *
     * @param Request $request
     * @param Closure $next
     */

    public  function handle(Request $request,Closure $next){

        $middleware = new OM(
            config('oauth.clientID'),
            config('oauth.clientSecret'),
            config('oauth.redirectURL'),
            config('oauth.Scopes'),
            config('oauth.doMain'),
            config('oauth.Endpoint'),
            config('oauth.requestPath'),
            config('oauth.callbackPath'),
        );
        switch ($request->path()){
            case  $middleware->callbackPath :
                var_dump($request->path());
                $tmp =$middleware->OauthCallback();
                var_dump("打印状态");

                var_dump(config('oauth.funcRewrite'));


                if  (config('oauth.funcRewrite')) {
                    var_dump("accessTokenHandlerFunc");

                    //进入下一步
                    $middleware->accessTokenHandlerFunc($tmp['shop'],$tmp['token']);
                    return $next($request);
                }else{
                    var_dump("走了");

                    return $next($request);
                }
            case  $middleware->requestPath :
                var_dump($request->path());
                return redirect()->away($middleware->OauthRequest());
        }
        return $next($request);
    }
}
```

####  4.1.2. <a name='-1'></a> 中间件的注册

[中间件注册配置](./example-app/app/Http/Kernel.php)


```injectablephp
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Oauth2Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        //注册全局中间件
        \App\Http\Middleware\OauthDemo::class,
    ];

 ...
}
```

###  4.2. <a name='-1'></a>关于测试程序

[测试程序](./example-app/routes/web.php)

```injectablephp
Route::get('/openapi_test', function () {
    //在完成安装的时候会产生tokenAndShop的 cookie
    $tokenAndStop= $_COOKIE["tokenAndShop"];
    parse_str($tokenAndStop, $tokenAndStop_arr);

    $http = new Client;

    //依靠cookie来进行 请求获取店铺信息 ，以达到测试的效果
    $headers = [
        'Accept'=>'application/json',

        'Access-Token'=>$tokenAndStop_arr['access_token'],];
    var_dump($tokenAndStop_arr['access_token'] );
    $req = $http->request("GET",'https://'.$tokenAndStop_arr['shop'].'/openapi/2020-07/shop',[
        'headers'=>[
            'Accept'=>'application/json',
            'Access-Token'=>$tokenAndStop_arr['access_token']
        ]
    ]);
});
```


##  5. <a name='-1'></a>关于功能函数的使用

###  5.1. <a name='GetAccessToken'></a>GetAccessToken
```injectablephp
//测试 GetAccessToken 可以获取到数据
require 'lib/Oauth2.php';

use PHPShoplazza\Oauth2;

$tokenArray = Oauth2::GetAccessToken(
    "https://panda.preview.shoplazza.com/admin/oauth/token",
    "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    "Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
    array(
        "grant_type" => "authorization_code",
        "code" => array("code"),
        'redirect_uri'=>'/oauth_sdk/redirect_uri/'
    )
);
var_dump($tokenObj);

```
###  5.2. <a name='Exchange'></a>Exchange
```injectablephp

//测试使用 Exchange 方法访问
require 'lib/Oauth2.php';
use PHPShoplazza\Oauth2;

$oauthObj=new Oauth2(
    "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    "Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
    "https://2fec-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
    array("read_product", "write_product"),
);


$res =$oauthObj->Exchange(
    "panda.preview.shoplazza.com",
    "code",
);

var_dump($res);
```

###  5.3. <a name='RefreshToken'></a>RefreshToken

```injectablephp
//测试使用 RefreshToken 方法访问
require 'lib/Oauth2.php';

use PHPShoplazza\Oauth2;

$oauthObj=new Oauth2(
    "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    "Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
    "https://2fec-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
    array("read_product", "write_product"),
);


$res =$oauthObj->RefreshToken(
    "panda.preview.shoplazza.com",
    "token",
);

var_dump($res);

```

###  5.4. <a name='AuthCodeUrl'></a>AuthCodeUrl
```injectablephp
//测试使用 AuthCodeUrl 方法生成url
require 'lib/Oauth2.php';

use PHPShoplazza\Oauth2;

$oauthObj=new Oauth2(
    "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    "Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
    "https://2fec-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
    array("read_product", "write_product"),
);


$res =$oauthObj->AuthCodeUrl(
    "panda.preview.shoplazza.com",
    array(
        "state"=>"aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa",
    ),
);

```

###  5.5. <a name='Oauth2Middleware'></a>Oauth2Middleware
```injectablephp
require 'lib/Oauth2Middleware.php';

use PHPShoplazza\Oauth2Middleware;

$middleware = new Oauth2Middleware(
    //设置  ClientID
    "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    //设置  ClientSecret
    "Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
    //指定回调地址的连接
    "https://2fec-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
    //指定可选的请求权限。
    array("read_product", "write_product"),
    array(
        "AuthURL" => "/admin/oauth/authorize",
        "TokenURL" => "/admin/oauth/token",),
    "/oauth_sdk/app_uri" ,
    "/oauth_sdk/redirect_uri/"
);

//调用OauthRequest方法
$middleware->OauthRequest();
//调用OauthCallback方法
$middleware->OauthCallback();

```
