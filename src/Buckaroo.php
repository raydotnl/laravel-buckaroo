<?php

namespace Raydotnl\LaravelBuckaroo;

class Buckaroo
{
    public function __construct()
    {
    }

    public function __call($name, $arguments)
    {
    }


    /**
     * Generate the authorization header
     *
     * @param $requestHttpMethod
     * @param $requestUri
     * @param $requestTimestamp
     * @param $nonce
     * @param $requestContentBase64String
     * @return string
     */
    private function getAuthorizationHeader($requestHttpMethod, $requestUri, $requestTimestamp, $nonce, $requestContentBase64String): string
    {
        $websiteKey = config('laravel_buckaroo.buckaroo_website_key');
        $secretKey = config('laravel_buckaroo.buckaroo_secret_key');

        $header = [];
        $header[] = 'hmac '.$websiteKey;

        $data = $websiteKey . $requestHttpMethod . $requestUri . $requestTimestamp . $nonce . $requestContentBase64String;
        $header[] = hash_hmac('sha256', $data, $secretKey);

        $header[] = uniqid('', true);
        $header[] = $requestTimestamp;

        return implode(':', $header);
    }

    private function request($methods, $action)
    {
    }
}
