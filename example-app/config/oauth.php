<?php
return[
    'clientID' => "beECXaQzYZOvr5DgrSw3ntX4lfZOfoJwDtFMX2N0UOc",
    'clientSecret'=>"Y9Mo9s4fzRxo23dvzFO8h1v5FX5pp3xYKAqGicDuG70",
    'redirectURL'=>"https://1197-43-230-206-233.ngrok.io/oauth_sdk/redirect_uri/",
    'Scopes' => [
        "read_product", "write_product","read_shop","write_shop"
    ],
    'Endpoint'=>[
        "AuthURL"=>"/admin/oauth/authorize",
        "TokenURL"=>"/admin/oauth/token",
    ],
    "doMain" => "myshoplaza.com",
    "requestPath"=>"oauth_sdk/app_uri",
    "callbackPath"=>"oauth_sdk/redirect_uri",
    "funcRewrite" => true,

];
