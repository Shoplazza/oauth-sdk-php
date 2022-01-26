[toc]

# Project introduction

oauth2 package contains a client implementation for OAuth 2.0 spec.


## USE

## GetAccessToken 
```injectablephp
//Test GetAccessToken to see if it can get data
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

//Testing Exchange access
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
//test RefreshToken 
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
//The test generates the URL using the AuthCodeUrl method
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
    //set clientID
    "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    //set $ClientSecret
    "Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
    //Specify optional request permissions.
    array("read_product", "write_product"),
    //set callback address
    "https://2fec-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
    array(
        "AuthURL" => "/admin/oauth/authorize",
        "TokenURL" => "/admin/oauth/token",),
    "/oauth_sdk/app_uri" ,
    "/oauth_sdk/redirect_uri/"
);

//Call the OauthRequest method
$middleware->OauthRequest();
//Call the OauthCallback method
$middleware->OauthCallback();

```

## In Laravel

[Laravel website](https://laravel.com/) 

Example-app is a Laravel project and middleware is set globally to block requests for urls' /auth/shoplazza 'and' /auth/shoplazza/callback 'by default:
- `/auth/shoplazza?shop=xx.myshoplaza.com` : Asked this URL will be redirected to the https://xx.myshoplaza.com/admin/oauth/authorize to initiate the authorization process
- `/auth/shoplazza/callback` : Intercepts authorization callback requests and automatically replaces tokens with codes in callback requests



### The directory structure
```shell
.
├── Readme.md
├── example-app
│   ├── README.md
│   ├── app
│   │   └── Http
│   │        └── Middleware
│   │               └── OauthDemo.php   //Middleware required for authentication
│   ├── artisan
│   ├── bootstrap
│   ├── composer.json
│   ├── composer.lock
│   ├── config
│   │   └── oauth.php       //Oauth configuration file required
│   ├── database
│   ├── docker-compose.yml  //Docker-compose configuration file
│   ├── lib                 //library files
│   ├── package.json    
│   ├── phpunit.xml
│   ├── public
│   ├── resources
│   ├── routes              //The routing file
│   │   └── web.php         //Test the file where the program resides
│   ├── server.php
│   ├── storage
│   ├── tests
│   ├── vendor
│   └── webpack.mix.js
└── lib
    ├── CurlRequest.php           //Curl Sends a request to encapsulate a request package
    ├── CurlResponse.php          //Curl Responds to the request by encapsulating the returned package
    ├── Exception                 //
    ├── HttpRequestJson.php       // HTTP request package
    ├── Oauth2.php                // Oauth2 encapsulation
    └── Oauth2Middleware.php      // Functional middleware for Oauth2
                


```
### [The configuration file](./example-app/config/oauth.php)
```injectablephp
<?php
return[
    //clientID
    'clientID' => "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    //clientSecret
    'clientSecret'=>"Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
    //callback address
    'redirectURL'=>"https://e820-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
    //Permission to apply for
    'Scopes' => [
        "read_product", "write_product","read_shop","write_shop"
    ],
    //Authorize the endpoint
    'Endpoint'=>[
        "AuthURL"=>"/admin/oauth/authorize",
        "TokenURL"=>"/admin/oauth/token",
    ],
    // Shop domain name, if not set, the default domain name is myshoplaza.com
    "doMain" => "preview.shoplazza.com",
    // The path intercepted by the request method
    "requestPath"=>"oauth_sdk/app_uri",
    // The path intercepted by the callback method
    "callbackPath"=>"oauth_sdk/redirect_uri",
    // Refactoring method or not (optional)
    "funcRewrite" => true,

];



```
### [The middleware](./example-app/app/Http/Middleware/OauthDemo.php)
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
    /** Determine the method to call
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
                $tmp =$middleware->OauthCallback();

                if  (config('oauth.funcRewrite')) {
                    $middleware->accessTokenHandlerFunc($tmp['shop'],$tmp['token']);
                    return $next($request);
                }else{
                    return $next($request);
                }
            case  $middleware->requestPath :
                return redirect()->away($middleware->OauthRequest());
        }
        return $next($request);
    }
}
```

### [Middleware registration](./example-app/app/Http/Kernel.php)


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
        // Register global middleware
        \App\Http\Middleware\OauthDemo::class,
    ];

 ...
}
```

### [The test program](./example-app/routes/web.php)

```injectablephp
Route::get('/openapi_test', function () {

    $tokenAndStop= $_COOKIE["tokenAndShop"];
    parse_str($tokenAndStop, $tokenAndStop_arr);

    $http = new Client;

    $headers = [
        'Accept'=>'application/json',

        'Access-Token'=>$tokenAndStop_arr['access_token'],];
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
        #Port mapping for web services
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
        #Port mapping of the database
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
        #Port mapping of the redis
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


### Start

#### Precondition:
Docker and Docker-compose will need to be installed
```shell
cd example-app   Enter the Demo project

./vendor/bin/sail up   Start the environment and run the project
```
The first time you run Sail's up command, the Sail application container will be compiled on your machine. This process will take a while. Don't worry, it will be soon.
