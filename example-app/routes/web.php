<?php

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;
//use PHPShoplazza\Oauth2Middleware;

//require '../lib/Oauth2Middleware.php';


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});
Route::get('/hello', function () {
    return 'hello worldpanda';
});


Route::get('/openapi_test', function () {


    $tokenAndStop= $_COOKIE["tokenAndShop"];
    parse_str($tokenAndStop, $tokenAndStop_arr);
    if (empty($tokenAndStop_arr['access_token'])){
        header('HTTP/1.1 400 NOT FOUND');
        echo json_encode(array(
            "code"=>400,
            "message"=>"access_token Not found ",
        ));
        exit();
    }
    $http = new Client;
    $req = $http->request("GET",'https://'.$tokenAndStop_arr['shop'].'/openapi/2020-07/shop',[
        'headers'=>[
            'Accept'=>'application/json',
            'Access-Token'=>$tokenAndStop_arr['access_token']
        ]
    ]);
    var_dump($req);
});



//Route::get('/oauth_sdk/app_uri',function ( ){
//    $middleware = new Oauth2Middleware(
//        "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
//        "Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
//        "https://2fec-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
//        array("read_product", "write_product"),
//    );
//
//    return redirect()->away($middleware->OauthRequest());
//});
//
//Route::get('/oauth_sdk/redirect_uri/',function ( ){
//    $middleware = new Oauth2Middleware(
//        "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
//        "Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
//        "https://2fec-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
//        array("read_product", "write_product"),
//    );
//
//    $middleware->OauthCallback();
//});


//Route::domain('')->group(function (){
//    $middleware = new Oauth2Middleware(
//        "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
//        "Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
//        "https://2fec-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
//        array("read_product", "write_product"),
//    );
//
//    Route::get($middleware->requestPath,function($middleware){
//        return redirect()->away($middleware->OauthRequest());
//    });
//
//    Route::get($middleware->callbackPath,function ($middleware  ){
//
//        $middleware->OauthCallback();
//        return;
//    });
//
//});



//Route::get('/open_api/test',function ( ){
//
//});


