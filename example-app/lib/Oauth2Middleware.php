<?php


namespace  PHPShoplazza;
require 'Oauth2.php';



class Oauth2Middleware extends oauth2
{
    public  $requestPath= "/oauth_sdk/app_uri"  ;
    public  $callbackPath ="/oauth_sdk/redirect_uri/";

    public function __construct(
        string $ClientID,
        string $ClientSecret,
        string $RedirectURI,
        array $Scopes,
        string $Domain = "myshoplaza.com",
        array $Endpoint = array(
            "AuthURL" => "/admin/oauth/authorize",
            "TokenURL" => "/admin/oauth/token",),
        string $requestPath = "/oauth_sdk/app_uri" ,
        string $callbackPath ="/oauth_sdk/redirect_uri/" )
    {
        $this->requestPath = $requestPath;
        $this->callbackPath = $callbackPath;
        parent::__construct(
            $ClientID,
            $ClientSecret,
            $RedirectURI,
            $Scopes,
            $Domain,
            $Endpoint);
    }

    //request 处理函数
    public  function OauthRequest(){

        //获取查询参数
        if (empty($_SERVER['QUERY_STRING'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(array(
                "code"=>400,
                "message"=>"OAuth endpoint is not a myshoplazza site.",
            ));
            exit();
        }
        //查询参数转化为数组
        parse_str($_SERVER['QUERY_STRING'],$query_arr);
        //验证 shop
        if  ( $this->ValidShop($query_arr["shop"])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(array(
                "code"=>400,
                "message"=>"OAuth endpoint is not a myshoplazza site.",
            ));
            exit();
        }
        $state = $this->GetRandomString(48);
        $values = array(
            "state"=>$state,
        );
        $values_str = http_build_query($values);
        // state设置
        setcookie('state-session', $values_str, time()+3*24*60*60);
        var_dump('$this->AuthCodeUrl($query_arr["shop"],$values)：：：：：');
        var_dump($this->AuthCodeUrl($query_arr["shop"],$values));
        // 302 重定向到 /admin/oauth/authorize
//        header('Location:'.$this->AuthCodeUrl($query_arr["shop"],$values),true,302);
        return $this->AuthCodeUrl($query_arr["shop"],$values);
    }


    // callback 处理函数
    public  function OauthCallback(){

        //获取查询参数
        if (empty($_SERVER['QUERY_STRING'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(array(
                "code"=>400,
                "message"=>"Invalid callback.",
            ));
            exit();
        }
        //查询参数转化为数组
        parse_str($_SERVER['QUERY_STRING'],$query_arr);
        //验证 shop
        if  ( $this->ValidShop($query_arr["shop"])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(array(
                "code"=>400,
                "message"=>"OAuth endpoint is not a myshoplazza site.",
            ));
            exit();
        }

        // state校验
        $stateSession = $_COOKIE['state-session'];

        parse_str($stateSession, $state_arr);



        if (empty($state_arr['state'])){
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(array(
                "code"=>400,
                "message"=>"State does not exist in the session.",
            ));
            exit();
        }
        if( strcasecmp($state_arr['state'],$query_arr['state'])){
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(array(
                "code"=>400,
                "message"=>"State does not match.",
            ));
            exit();
        }
        // hmac校验
        if (($this->SignatureValid($query_arr['hmac']))){
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(array(
                "code"=>400,
                "message"=>"Signature does not match, it may have been tampered with.",
            ));
            exit();
        }

        // 用code换取token
        $tokenO = $this->Exchange($query_arr["shop"],$query_arr["code"]);
        if (empty($tokenO)){
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(array(
                "code"=>400,
                "message"=>"failed to get the token ",
            ));
            exit();
        }

        $token_str = http_build_query($tokenO);

        // 设置token
        setcookie("oauth2.token",$token_str);


        //accessTokenHandlerFunc

        return array(
            "shop"=> $query_arr["shop"],
            "token" => $tokenO,
        );

    }




    // access-token 处理函数 可以重构
    public  function accessTokenHandlerFunc ( $shop , $token )
    {
        //存放token,也可以存入存到db
        $token["shop"] = $shop;
        $token_str = http_build_query($token);

        setcookie("tokenAndShop",$token_str,time()+3*24*60*60,"/");

        header('HTTP/1.1 200 OK',true,200);
        header("Status: 200 OK");
        http_response_code(200);

        echo json_encode(array(
            "code"=>200,
            "message"=>"save access-token success",
        ));
        exit(200);
    }

    private  static function   GetRandomString(int $len = 48):string
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        shuffle($chars);    // 将数组打乱
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }
}
