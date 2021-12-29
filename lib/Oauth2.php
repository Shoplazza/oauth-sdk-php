<?php

namespace  PHPShoplazza;

require 'HttpRequestJson.php';
use PHPShoplazza\HttpRequestJson;
define("DefaultDomain",     "myshoplaza.com");


class Oauth2
{

    //应用id
    public string $ClientID ;
    public string $ClientSecret;
    //url
    public string $RedirectURI = "https://app.com/auth/shoplazza/callback";
    //Scope指定可选的请求权限。
    public array $Endpoint =array(
    "AuthURL"=>"/admin/oauth/authorize",
    "TokenURL"=>"/admin/oauth/token",
    );
    public array $Scopes;
    public string $Domain;


    public function __construct(
        string $ClientID,
        string $ClientSecret,
        string $RedirectURI,
        array  $Scopes,
        string $Domain = "myshoplaza.com",
        array $Endpoint = array(
            "AuthURL"=>"/admin/oauth/authorize",
            "TokenURL"=>"/admin/oauth/token",
        )
    ){
        $this->ClientID = $ClientID;
        $this->ClientSecret = $ClientSecret;
        $this->RedirectURI =$RedirectURI;
        $this->Endpoint = $Endpoint;
        $this->Scopes = $Scopes;
        $this->Domain = $Domain;
    }

    /**使用code换取token方法
     *
     * @param string $shop 店铺的url
     * @param string $code
     * @param mixed ...$numbers
     * @return array   返回token或者 是 错误信息
     */
    public  function Exchange(string $shop, string $code,...$numbers):array
    {
        $value  = array(
            "grant_type" => "authorization_code",
            "code" => $code,
        );
        if (!empty($this->RedirectURI)){
            $value["redirect_uri"] = $this->RedirectURI;
        }

        foreach ($numbers as $key => $val) {
            $value[$key] = $val;
        }

        return $this->retrieveToken($shop ,$value);
    }

    /**
    * token过期刷新token方法
    *
    * @param string $shop 店铺的url
    * @param string $token 需要刷新的token
    * @param mixed ...$numbers
    * @return token 返回token或者 是 错误信息
    */
    public  function RefreshToken(string $shop, string $token,...$numbers):array
    {

        $value  = array(
            "grant_type" => "refresh_token",
            "refresh_token" => $token,
        );

        foreach ($numbers as $key => $val) {
            $value[$key] = $val;
        }

         return $this->retrieveToken($shop ,$value);
    }

    /**
     * token过期刷新token方法
     *
     * @param string $shop 店铺的url
     * @param array $value 需要传入的数据
     * @return token 返回token或者 是 错误信息
     */

    private  function retrieveToken(string $shop,array $value):array
    {
        $shopUrl = 'https://'.$shop.$this->Endpoint["TokenURL"];



        return $this->GetAccessToken($shopUrl,$this->ClientID,$this->ClientSecret,$value);
    }

    /**生成授权 code url
     * @param string $shop 店铺的url
     * @param array $value 店铺的url
     * @return  string   返回url的拼接路径
     */
    public  function AuthCodeUrl(string $shop, array $value,...$numbers)
    {
        $authUrl  = 'https://'.$shop.$this->Endpoint["AuthURL"];
        $value['response_type'] = "code";
        $value['client_id'] = $this->ClientID;
        if (!empty($this->RedirectURI)){
            $value["redirect_uri"] = $this->RedirectURI;
        }

        if (sizeof($this->Scopes)> 0){
            $value["scope"]=array_reduce($this->Scopes ,function ($v1,$v2){return $v1.' '.$v2;}) ;
        }
        foreach ($numbers as $key => $val) {
            $value[$key] = $val;
        }
        //拼接一个字符串
        //  PHP_QUERY_RFC3986  ' ' 对应 %20
        //  PHP_QUERY_RFC1738  ' ' 对应 +
        return $authUrl."?".http_build_query($value,"","&",PHP_QUERY_RFC1738);
    }

    public function  ValidShop(string $stop):bool{
        $domain = $this->Domain;
        if (!empty($domain)){
            $domain = DefaultDomain;
        }
        if (preg_match("/^[a-zA-Z0-9-]+.".$domain."$/", $stop)){
            return true;
        }

        return false;

    }
    public  function SignatureValid($hmac ) :bool{


        $signature = base64_encode(hash_hmac('sha256', $this->ClientSecret, true));

        if ($hmac === $signature) {
            return true;
        }


        return  false;
    }

    //获取token
    /** 获取token的方法
     *
     * @param string $tokenURL 店铺的url
     * @param string $clientID 用户id
     * @param string $clientSecret
     * @param array $urlValue
     * @return array  返回对象本身
     */
    public  static function GetAccessToken(string $tokenURL,string $clientID,string $clientSecret, array $urlValue):array
    {

        if (!empty($clientID)){
            $urlValue["client_id"] = $clientID ;
        }

        if (!empty($clientSecret)){
            $urlValue["client_secret"] = $clientSecret ;
        }



        $http_header = array(
//            "Content-Type"=>"application/x-www-form-urlencoded",
        );

        $response = HttpRequestJson::post($tokenURL, $urlValue,$http_header);

        if (empty($response)){
            return array(
                "error"=>"not found token."
            );
        }

        return  $response;
    }
}
