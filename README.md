# 项目介绍

oauth2 package contains a client implementation for OAuth 2.0 spec.


## 使用

## GetAccessToken 
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
## Exchange
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

## RefreshToken

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

## AuthCodeUrl
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

## Oauth2Middleware
```injectablephp
require 'lib/Oauth2Middleware.php';

use PHPShoplazza\Oauth2Middleware;

$middleware = new Oauth2Middleware(
    //设置clientID
    "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    //设置$ClientSecret
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

## In Laravel

目录下example-app 是一个Laravel 项目并且已经设置了全局的 middleware 会默认拦截 `/auth/shoplazza` 以及 `/auth/shoplazza/callback` 两个 URL 的请求:
- `/auth/shoplazza?shop=xx.myshoplaza.com` : 请求此 URL 时，会重定向到 https://xx.myshoplaza.com/admin/oauth/authorize 去发起授权流程
- `/auth/shoplazza/callback` : 拦截授权回调请求，自动将回调请求中的 code 替换 token


### 目录结构
```shell
.
├── Readme.md
├── example-app
│   ├── README.md
│   ├── app
│   │   └── Http
│   │        └── Middleware
│   │               └── OauthDemo.php   //认证所需要的中间件
│   ├── artisan
│   ├── bootstrap
│   ├── composer.json
│   ├── composer.lock
│   ├── config
│   │   └── oauth.php       //oauth 所需要的配置文件
│   ├── database
│   ├── docker-compose.yml  //docker-compose的配置文件
│   ├── lib                 //库文件
│   ├── package.json    
│   ├── phpunit.xml
│   ├── public
│   ├── resources
│   ├── routes              //路由文件
│   │   └── web.php         //测试程序所在文件
│   ├── server.php
│   ├── storage
│   ├── tests
│   ├── vendor
│   └── webpack.mix.js
└── lib
    ├── CurlRequest.php           //Curl 发送请求封装请求包
    ├── CurlResponse.php          //Curl 响应请求封装返回包
    ├── Exception                 // 意外情况返回
    ├── HttpRequestJson.php       // http 请求的包
    ├── Oauth2.php                // oauth2 的封装
    └── Oauth2Middleware.php      // oauth2 的功能中间件
                


```

### [配置文件](./example-app/config/oauth.php)
```injectablephp
<?php
return[
    //clientID
    'clientID' => "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    //clientSecret
    'clientSecret'=>"Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
    //指定回调地址的链接
    'redirectURL'=>"https://e820-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
    //需要申请的权限
    'Scopes' => [
        "read_product", "write_product","read_shop","write_shop"
    ],
    //授权端点
    'Endpoint'=>[
        "AuthURL"=>"/admin/oauth/authorize",
        "TokenURL"=>"/admin/oauth/token",
    ],
    // 店铺域名，不设置的话默认使用美服域名：myshoplaza.com
    "doMain" => "preview.shoplazza.com",
    //request方法拦截的路径 
    "requestPath"=>"oauth_sdk/app_uri",
    //callback方法拦截的路径 
    "callbackPath"=>"oauth_sdk/redirect_uri",
    //是否重构方法  (可选)
    "funcRewrite" => true,

];



```
### [中间件](./example-app/app/Http/Middleware/OauthDemo.php)
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

### [中间件的注册](./example-app/app/Http/Kernel.php)


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

### [测试程序](./example-app/routes/web.php)

```injectablephp
Route::get('/openapi_test', function () {

    $tokenAndStop= $_COOKIE["tokenAndShop"];
    parse_str($tokenAndStop, $tokenAndStop_arr);

    $http = new Client;

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

### [docker-compose](example-app/docker-compose.yml)

```yaml

# For more information: https://laravel.com/docs/sail
version: '3'
services:
    laravel.test:
        build:
            context: ./vendor/laravel/sail/runtimes/8.1
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP}'
        image: sail-8.1/app
        extra_hosts:
            - 'host.docker.internal:host-gateway'
#            web服务的端口映射
        ports:
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
#        数据库的端口映射
        ports:
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
#        缓存的端口映射
        ports:
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


### 启动

#### 前置条件:
需要安装docker以及docker-compose

```shell
cd example-app   进入demo项目

./vendor/bin/sail up   启动环境并且运行项目
```
在您首次运行 Sail 的 up 命令的时候，Sail 的应用容器将会在您的机器上进行编译。这个过程将会花费一段时间。不要担心，以后就会很快了。

