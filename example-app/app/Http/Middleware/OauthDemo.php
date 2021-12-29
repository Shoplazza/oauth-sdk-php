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
                $tmp =$middleware->OauthCallback();

                if  (config('oauth.funcRewrite')) {

                    //进入下一步
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
