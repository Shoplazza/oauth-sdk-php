<?php


namespace  PHPShoplazza;
require 'Oauth2.php';



class Oauth2Middleware extends oauth2
{
    static public  $httpErrorCode = array(
        200 => "HTTP/1.1 200 OK", 
        400 => "HTTP/1.1 400 Bad Request", 
        404 => "HTTP/1.1 404 Not Found", 
    );
    static public $ExpirationTime = 3*24*60*60;
    static public $EndpointError = "OAuth endpoint is not a myshoplazza site.";


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

    //OauthRequest 
    public  function OauthRequest(){

        //Get the QUERY_STRING
        if (empty($_SERVER['QUERY_STRING'])) {
            
            $this->exceptionInfo(400,self::$EndpointError);
        }
        //Query parameters are converted to arrays
        parse_str($_SERVER['QUERY_STRING'],$query_arr);
        //Verify the store
        if  ( $this->ValidShop($query_arr["shop"])) {
            
            $this->exceptionInfo(400,self::$EndpointError);

        }
        $state = $this->GetRandomString(48);
        $values = array(
            "state"=>$state,
        );
        $values_str = http_build_query($values);
        // set state-session
        setcookie('state-session', $values_str, time()+ self::$ExpirationTime);
      
        // 302 redirect /admin/oauth/authorize
        // header('Location:'.$this->AuthCodeUrl($query_arr["shop"],$values),true,302);
        return $this->AuthCodeUrl($query_arr["shop"],$values);
    }


    // callback 
    public  function OauthCallback(){

        //Get the QUERY_STRING
        if (empty($_SERVER['QUERY_STRING'])) {
           
            $this->exceptionInfo(400,"Invalid callback.");

        }
        //Query parameters are converted to arrays
        parse_str($_SERVER['QUERY_STRING'],$query_arr);
        //Verify the store
        if  ( $this->ValidShop($query_arr["shop"])) {
      
            $this->exceptionInfo(400,self::$EndpointError);
        }

        // Verify state-session
        $stateSession = $_COOKIE['state-session'];

        parse_str($stateSession, $state_arr);

        if (empty($state_arr['state'])){
            
            $this->exceptionInfo(400,"State does not exist in the session.");

        }
        if( strcasecmp($state_arr['state'],$query_arr['state'])){
           
            $this->exceptionInfo(400,"State does not match.");

        }
        //Verify hmac
        if (($this->SignatureValid($query_arr['hmac']))){
           
            $this->exceptionInfo(400,"Signature does not match, it may have been tampered with.");

        }

        // Exchange code for token
        $tokenO = $this->Exchange($query_arr["shop"],$query_arr["code"]);
        if (empty($tokenO)){
            
            $this->exceptionInfo(400,"failed to get the token .");

        }

        $token_str = http_build_query($tokenO);

        // set token
        setcookie("oauth2.token",$token_str);



        return array(
            "shop"=> $query_arr["shop"],
            "token" => $tokenO,
        );

    }




    // Access-token handlers can be refactored
    public  function accessTokenHandlerFunc ( $shop , $token )
    {
        //The token can also be stored in db
        $token["shop"] = $shop;
        $token_str = http_build_query($token);

        setcookie("tokenAndShop",$token_str,time()+self::$ExpirationTime,"/");

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
        shuffle($chars);    // Scramble arrays
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    public function  exceptionInfo($errorCode,$errorMsg) 
    {
        header(self::$httpErrorCode[$errorCode]);
        echo json_encode(array(
            "code"=>$errorCode,
            "message"=>$errorMsg ,
        ));
        exit($errorCode);
        
    }
}
