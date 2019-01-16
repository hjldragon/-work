<?php

namespace GoldenOpenPlat;

class Request
{

    //appkey
    private $appkey;
    //timstamp
    private $timestamp;
    //appsecret
    private $appsecret;
    //请求超时时间
    private $timeout;
    //env
    private $env;

    //发票儿对接host
    private $hosts = [
        "test" => "http://182.254.219.106:8400",
        "prod" => "https://openapi.fapiaoer.cn",
    ];

    //appkey,appsecret  高朋开放平台id secret
    public function __construct($appkey, $appsecret)
    {
        $this->appkey = $appkey;
        $this->appsecret = $appsecret;
        $this->timeout = 7;
        $this->env = "prod";
    }

    //设置超时时间
    public function setTimeout($stamp)
    {
        $this->timeout = intval($stamp);
        return $this;
    }

    //设置环境 默认生产环境
    public function setEnv($env = "prod")
    {
        if (in_array($env, array_keys($this->hosts))) {
            $this->env = $env;
        } else {
            $this->env = "prod";
        }

        return $this;
    }


    /*
     *  签名生成
     *  array data 传输的数据
    */
    public function generateSign($data = [])
    {
        $originStr = $this->appkey . $this->timestamp;
        ksort($data);
        $originStr .= rawurlencode(call_user_func(function () use ($data) {
                $str = "";
                foreach ($data as $k => $v) {
                    if (is_array($v)) {
                        $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                    }
                    $str .= $k . '=' . $v . '&';
                }
                $str = rtrim($str, '&');
                return $str;
            })) . $this->appsecret;
        $sign = strtoupper(md5($originStr));
        return $sign;
    }

    /*
     *  uri示例：?signature=1114154A26439AFBE7EAAB9FEAEE4EF5&timestamp=1525918646&sn=15259186461525&appkey=testappkey111
     *  post请求接口
    */
    public function requestPost($uri, $data)
    {
        $this->timestamp = time();
        $sign = $this->generateSign($data);
        $tail_str = "?signature={$sign}&timestamp={$this->timestamp}&appkey={$this->appkey}";
        return $this->getHttp($this->hosts[$this->env] . $uri . $tail_str, json_encode($data));
    }

    /*
     *  uri示例：?signature=1114154A26439AFBE7EAAB9FEAEE4EF5&timestamp=1525918646&sn=15259186461525&appkey=testappkey111
     *  get请求接口
    */
    public function requestGet($uri, $data)
    {
        $this->timestamp = time();
        $sign = $this->generateSign($data);
        $data['signature'] = $sign;
        $data['timestamp'] = $this->timestamp;
        $data['appkey'] = $this->appkey;
        return $this->getHttp($this->hosts[$this->env] . $uri, $data, 'GET');
    }

    /*
     * docurl
     */
    public function getHttp($url, $datas = array(), $method = 'POST', $contentType = "application/json", $cookies = NULL, $encode = null)
    {
        $result = array(
            'body' => '',
            'error' => '',
            'statusCode' => '');

        $method = strtoupper($method);

        $query = is_array($datas) ? http_build_query($datas, NULL, '&') : $datas;

        if ($method == 'GET' && !empty($query)) {
            $url .= '?' . $query;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        $header = [];

        if ($method == 'POST' && !empty($query)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            //http://www.laruence.com/2011/01/20/1840.html    Expect:100-continue
            $header[] = 'Expect:';
        } elseif ($method == 'DELETE' || $method == 'PUT') {
            curl_setopt($ch, CURLOPT_NOSIGNAL, TRUE);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }
        !empty($cookies) && curl_setopt($ch, CURLOPT_COOKIE, $cookies);
        //gzip压缩
        !empty($encode) && curl_setopt($ch, CURLOPT_ENCODING, $encode);

        if ($contentType) {
            $header[] = "Content-Type: " . $contentType;
        }

        //统一处理header
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        $isHttps = strpos($url, 'https://');
        if ($isHttps === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result['body'] = curl_exec($ch);
        $result['statusCode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['error'] = curl_error($ch);

        curl_close($ch);

        return $result;
    }


}