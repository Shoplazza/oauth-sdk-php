<!-- vscode-markdown-toc -->
* 1. [Introduce](#Introduce)
* 2. [Quick start](#Quickstart)
	* 2.1. [Create app](#Createapp)
	* 2.2. [Configure the app](#Configuretheapp)
	* 2.3. [Modifying THE SDK Configuration](#ModifyingTHESDKConfiguration)
	* 2.4. [Start the program](#Starttheprogram)
		* 2.4.1. [docker-compose](#docker-compose)
		* 2.4.2. [The Laravel script starts](#TheLaravelscriptstarts)
	* 2.5. [validation](#validation)
* 3. [The directory structure](#Thedirectorystructure)
* 4. [About Demo](#AboutDemo)
	* 4.1. [Middleware for Demo](#MiddlewareforDemo)
		* 4.1.1. [Implementation of Middleware](#ImplementationofMiddleware)
		* 4.1.2. [ Middleware registration](#Middlewareregistration)
	* 4.2. [About the Test Procedure](#AbouttheTestProcedure)
* 5. [About the use of functional functions](#Abouttheuseoffunctionalfunctions)
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
[中文版](./README-ch.md)

##  1. <a name='Introduce'></a>Introduce

This project is a PHP SDK for Shoplazza developers to complete authentication without having to understand too much Oauth2 process.
Read the documentation for the Shoplazza certification process [Standard OAuth process](https://helpcenter.shoplazza.com/hc/zh-cn/articles/4408686586137#h_01FM4XX2CX746V3277HB7SPGTN)


##  2. <a name='Quickstart'></a>Quick start
###  2.1. <a name='Createapp'></a>Create app
Read the documentation for creating the app [Building Public App](https://helpcenter.shoplazza.com/hc/en-us/articles/4409360434201-Building-Public-App)

###  2.2. <a name='Configuretheapp'></a>Configure the app
Read the documentation for configuring the app [Manage Your App](https://helpcenter.shoplazza.com/hc/en-us/articles/4409476265241-Manage-Your-App)

###  2.3. <a name='ModifyingTHESDKConfiguration'></a>Modifying THE SDK Configuration

The parameters mentioned in the "Building Public App" and "Managing Your App" articles are:    
1、Client ID： Authentication
2、Client Secret：Authentication
3、App URL：Your app main entrance URL
4、Redirect：URL Your app's redirect URL, generally used to receive OAuth responses

The preceding four parameters need to be set in the demo configuration file
[The configuration file](./example-app/config/oauth.php)

``` injectablephp
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


###  2.4. <a name='Starttheprogram'></a>Start the program
In the program of Demo, there are two startup modes, which are actually dependent on docker-compose. If there is no local mirror, it is necessary to pull the mirror.

Docker-compose's default mirror source is an international source. If the network connection is slow or abnormal, it is recommended to change the domestic source for docker-compose. Search for the replacement mode by yourself.
> As the Ubuntu supported version used for DockerFile in Laravel/sail is TLS, please ensure that the Ubuntu supported version corresponds after replacing the mirror source.

####  2.4.1. <a name='docker-compose'></a>docker-compose
[docker-compose.yml](./example-app/docker-compose.yml)

If you are familiar with Docker, you can configure this file directly
```
version: '3'
services:
    laravel.test:
        build:
            # The Dockerfile image is Ubuntu:[version] TLS
			# An image of Ubuntu pulled by the accelerator may not be the TLS version
            context: ./vendor/laravel/sail/runtimes/8.1
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP}'
        image: sail-8.1/app
        extra_hosts:
            - 'host.docker.internal:host-gateway'
        ports:
            # Application port  host:container
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
        #   Database port   host:container 
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
        #   redis port  host:container  
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
docker-compose Start the command
[Docker-compose official documentation](https://docs.docker.com/compose/reference/)

```
#Enter the Demo project
cd example-app  

#The front desk to start the
docker-compose up

#The background to start
docker-compose up -d


#Checking startup Status
docker-compose ps

#see the log
docker-compose logs -f


#close
docker-compose down


```

####  2.4.2. <a name='TheLaravelscriptstarts'></a>The Laravel script starts

[The script file](./example-app/vendor/bin/sail)

```
··· 
#Configuration from line 31
# Define environment variables...
#app port
export APP_PORT=${APP_PORT:-80}
#app host
export APP_SERVICE=${APP_SERVICE:-"laravel.test"}
#database port
export DB_PORT=${DB_PORT:-3306}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

export SAIL_SHARE_DASHBOARD=${SAIL_SHARE_DASHBOARD:-4040}
export SAIL_SHARE_SERVER_HOST=${SAIL_SHARE_SERVER_HOST:-"laravel-sail.site"}
export SAIL_SHARE_SERVER_PORT=${SAIL_SHARE_SERVER_PORT:-8080}
export SAIL_SHARE_SUBDOMAIN=${SAIL_SHARE_SUBDOMAIN:-""}
···

```

Script Start command

[sail doc](https://laravel.com/docs/sail)
```
#Enter the Demo project
cd example-app  

#Configure the environment and run the project
./vendor/bin/sail up
#The background configures the environment and runs the project
./vendor/bin/sail up -d
```



###  2.5. <a name='validation'></a>validation
At this point, you should have started the demo, so verify

1、Verify that the demo program is running properly
```
# Input in browser (default port 80)
127.0.0.1/hello
#Page response
hello worldpanda
```

2、Ensure that the installation process is smooth
Please read this step first [Testing public app](https://helpcenter.shoplazza.com/hc/en-us/articles/4409360434201-Building-Public-App#h_01FM7GXEAM5VPXTK6PJSA9MFWC)

Installation process:

If you have development store, go to  [Partner Center](https://partners.shoplazza.com/)->Apps->Apps List->Manage Apps->Test Apps  with the above Shoplazza account, select store to install the app and jump to the authorized installation page of the store for testing.

The page responds to the normal authorization result
```
{
    "code":200,
    "message":"save access-token success"
}
```

3、Authentication token

```
# Input in browser (default port 80)
https://host/openapi_test
#The page responds to the store default information
```




##  3. <a name='Thedirectorystructure'></a>The directory structure

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




##  4. <a name='AboutDemo'></a>About Demo

The main purpose of Demo is to provide some convenience for developers to use and understand how to use it.

Example-app is a [Laravel](https://laravel.com/) framework implementation of phpwebDemo.

The Demo has set up global middleware to block requests for `/auth/shoplazza` and `/auth/shoplazza/callback` by default:
- `/auth/shoplazza?shop=xx.myshoplaza.com` : Asked this URL will be redirected to the https://xx.myshoplaza.com/admin/oauth/authorize to initiate the authorization process
- `/auth/shoplazza/callback` : Intercepts authorization callback requests and automatically replaces tokens with codes in callback requests
###  4.1. <a name='MiddlewareforDemo'></a>Middleware for Demo


####  4.1.1. <a name='ImplementationofMiddleware'></a>Implementation of Middleware

[Middleware](./example-app/app/Http/Middleware/OauthDemo.php)

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

####  4.1.2. <a name='Middlewareregistration'></a> Middleware registration

[Middleware registration config](./example-app/app/Http/Kernel.php)


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

###  4.2. <a name='AbouttheTestProcedure'></a>About the Test Procedure

[The test program](./example-app/routes/web.php)

```injectablephp
Route::get('/openapi_test', function () {
    // tokenAndShop cookies are generated when the installation is complete
    $tokenAndStop= $_COOKIE["tokenAndShop"];
    parse_str($tokenAndStop, $tokenAndStop_arr);

    $http = new Client;

    // Rely on cookies to request store information for testing effect
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


##  5. <a name='Abouttheuseoffunctionalfunctions'></a>About the use of functional functions

###  5.1. <a name='GetAccessToken'></a>GetAccessToken
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
###  5.2. <a name='Exchange'></a>Exchange
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

###  5.3. <a name='RefreshToken'></a>RefreshToken

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

###  5.4. <a name='AuthCodeUrl'></a>AuthCodeUrl
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

###  5.5. <a name='Oauth2Middleware'></a>Oauth2Middleware
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
