<?php

namespace Raydotnl\LaravelBuckaroo;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use URL;

class Buckaroo
{
    /** @var bool */
    private $debug = false;

    /** @var null|string */
    private $nonce = null;

    /** @var null|Carbon */
    private $timeStamp = null;

    public function __construct()
    {
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    private function debug($key, $value)
    {
        if ($this->debug) {
            dump($key, $value, '===============================');
        }
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
    private function getAuthorizationHeader($requestHttpMethod, $requestUri, $requestData): string
    {
        $websiteKey = config('buckaroo.buckaroo_website_key');
        $secretKey = config('buckaroo.buckaroo_secret_key');
        $timestamp = $this->getTimestamp();
        $nonce = $this->getNonce();

        $header = [];
        $header[] = 'hmac '.$websiteKey;

        $requestContentBase64String = base64_encode(md5($requestData, true));

        $uri = Str::substr($requestUri, 8);
        $uri = strtolower(urlencode($uri));

        $data = $websiteKey . $requestHttpMethod . $uri . $timestamp . $nonce . $requestContentBase64String;

        $header[] = base64_encode(hash_hmac('sha256', $data, $secretKey, true));
        $header[] = $nonce;
        $header[] = $timestamp;

        return implode(':', $header);
    }

    /**
     * @return string
     */
    private function getNonce()
    {
        return Str::random(16);
    }

    /**
     * @return int
     */
    private function getTimestamp()
    {
        if ($this->debug) {
            return 1595847908;
        }

        return now()->timestamp;
    }

    protected function request($requestMethod, $action, $data = null)
    {
        if ($data) {
            $data = json_encode($data);
        }

        $uri = URL::format(config('buckaroo.buckaroo_url'), $action);

        $this->debug('Authorization', $this->getAuthorizationHeader($requestMethod, $uri, $data));

        $http = Http::withHeaders([
            'Authorization' => $this->getAuthorizationHeader($requestMethod, $uri, $data),
            'Channel' => 'web',
        ]);

        if ($requestMethod === 'POST') {
            $response = $http->withBody($data, 'application/json')->post($uri);
        } elseif ($requestMethod === 'GET') {
            $response = $http->get($uri, $data);
        } else {
            throw new \Exception('invalid requestMethod '.$requestMethod.' (valid methods are POST, GET)');
        }

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception('Something went wrong '.$response->status().' '.$response->body());
        }
    }
}
